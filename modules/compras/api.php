<?php
// ============================================================
// ARCHIVO: farmacia/modules/compras/api.php
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';
requireApiAuth(['admin', 'gerente', 'cajero']);

$action = $_GET['action'] ?? '';
$db     = getDB();

function generarNumeroOrden(PDO $db): string {
    $stmt = $db->query("SELECT COUNT(*) AS total FROM ordenes_compra WHERE DATE(created_at) = CURRENT_DATE");
    $num  = str_pad(($stmt->fetch()['total'] + 1), 4, '0', STR_PAD_LEFT);
    return 'OC' . date('Ymd') . '-' . $num;
}

switch ($action) {

    // ----------------------------------------------------------------
    // STATS
    // ----------------------------------------------------------------
    case 'stats':
        try {
            $row = $db->query("
                SELECT
                    (SELECT COUNT(*) FROM ordenes_compra WHERE estado NOT IN ('cancelada','recibida')) AS ordenes_activas,
                    (SELECT COUNT(*) FROM ordenes_compra WHERE estado = 'pendiente')                  AS ordenes_pendientes,
                    (SELECT COALESCE(SUM(monto_pendiente),0) FROM cuentas_por_pagar WHERE estado IN ('pendiente','parcial')) AS total_por_pagar,
                    (SELECT COUNT(*) FROM cuentas_por_pagar WHERE estado IN ('pendiente','parcial'))  AS cuentas_pendientes,
                    (SELECT COUNT(*) FROM cuentas_por_pagar WHERE estado = 'vencido')                AS cuentas_vencidas,
                    (SELECT COALESCE(SUM(total),0) FROM ordenes_compra
                        WHERE created_at >= date_trunc('month', CURRENT_DATE))                       AS compras_mes
            ")->fetch();
            echo json_encode($row);
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => 'Tablas no inicializadas. Ejecuta la migración.', 'detail' => $e->getMessage()], 500);
        }
        break;

    // ----------------------------------------------------------------
    // ÓRDENES DE COMPRA
    // ----------------------------------------------------------------
    case 'ordenes_listar':
        try {
            $estado = $_GET['estado'] ?? '';
            $where  = $estado ? "WHERE oc.estado = :estado" : '';
            $params = $estado ? [':estado' => $estado] : [];
            $stmt   = $db->prepare("
                SELECT oc.id, oc.numero_orden, oc.estado, oc.tipo_pago, oc.dias_credito,
                       oc.subtotal, oc.igv, oc.costo_envio, oc.total, oc.fecha_entrega, oc.observaciones, oc.created_at,
                       p.razon_social AS proveedor, p.ruc,
                       COUNT(d.id) AS total_items
                FROM ordenes_compra oc
                LEFT JOIN proveedores p ON p.id = oc.proveedor_id
                LEFT JOIN orden_compra_detalles d ON d.orden_id = oc.id
                {$where}
                GROUP BY oc.id, p.razon_social, p.ruc
                ORDER BY oc.created_at DESC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => 'Error al listar órdenes: ' . $e->getMessage()], 500);
        }
        break;

    case 'orden_detalle':
        $id   = intval($_GET['id'] ?? 0);
        $oc   = $db->prepare("
            SELECT oc.*, p.razon_social AS proveedor, p.ruc, p.telefono AS prov_tel
            FROM ordenes_compra oc
            LEFT JOIN proveedores p ON p.id = oc.proveedor_id
            WHERE oc.id = :id
        ");
        $oc->execute([':id' => $id]);
        $orden = $oc->fetch();
        if (!$orden) jsonResponse(['error' => true, 'message' => 'Orden no encontrada'], 404);

        $items = $db->prepare("
            SELECT d.*, pr.nombre AS producto_nombre, pr.codigo
            FROM orden_compra_detalles d
            LEFT JOIN productos pr ON pr.id = d.producto_id
            WHERE d.orden_id = :id
        ");
        $items->execute([':id' => $id]);
        $orden['items'] = $items->fetchAll();
        echo json_encode($orden);
        break;

    case 'orden_crear':
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['proveedor_id']) || empty($d['items'])) {
            jsonResponse(['error' => true, 'message' => 'Proveedor e ítems son requeridos'], 400);
        }

        $subtotal = 0;
        foreach ($d['items'] as $item) {
            $subtotal += floatval($item['cantidad']) * floatval($item['precio_unitario']);
        }
        // Ordenes de compra simples: sin IGV. El costo de envio se suma al
        // total de la orden (y por lo tanto a la cuenta por pagar / ingreso),
        // pero NO se reparte entre los items ni afecta su precio_compra.
        $costoEnvio = floatval($d['costo_envio'] ?? 0);
        $total = $subtotal + $costoEnvio;

        $tipoPago = $d['tipo_pago'] ?? 'efectivo';

        $db->beginTransaction();
        try {
            // Registrar una Orden de Compra es simple e inmediato: no hay
            // paso de "Aprobar"/"Recibir mercadería" (se eliminaron, no
            // tenian quien los aprobara en la practica) -- el stock se suma
            // de una vez, junto con el Ingreso en Almacen y, si es a
            // credito, la Cuenta por Pagar, que antes se generaban recien
            // al "recibir".
            $ins = $db->prepare("
                INSERT INTO ordenes_compra
                    (numero_orden, proveedor_id, usuario_id, estado, tipo_pago, dias_credito,
                     subtotal, igv, costo_envio, total, numero_factura, observaciones, fecha_entrega)
                VALUES (:num, :pid, :uid, 'recibida', :tp, :dc, :sub, 0, :env, :tot, :nf, :obs, :fe)
                RETURNING id
            ");
            $ins->execute([
                ':num' => generarNumeroOrden($db),
                ':pid' => intval($d['proveedor_id']),
                ':uid' => sesionId(),
                ':tp'  => $tipoPago,
                ':dc'  => intval($d['dias_credito'] ?? 0),
                ':sub' => $subtotal,
                ':env' => $costoEnvio,
                ':tot' => $total,
                ':nf'  => trim($d['numero_factura'] ?? '') ?: null,
                ':obs' => trim($d['observaciones'] ?? ''),
                ':fe'  => $d['fecha_entrega'] ?: null,
            ]);
            $orden_id = $ins->fetch()['id'];

            // Ingreso en Almacen que respalda la compra (mismo numero de
            // formato que ya usaba orden_recibir).
            $stmt_cnt = $db->query("SELECT COUNT(*) AS total FROM ingresos WHERE DATE(created_at) = CURRENT_DATE");
            $num_ing  = 'I' . date('Ymd') . '-' . str_pad($stmt_cnt->fetch()['total'] + 1, 4, '0', STR_PAD_LEFT);
            $insIng = $db->prepare("
                INSERT INTO ingresos (numero_ingreso, proveedor_id, usuario_id, total, estado, tipo_pago, orden_compra_id, observaciones)
                VALUES (:num, :pid, :uid, :tot, 'completado', :tp, :oid, :obs)
                RETURNING id
            ");
            $insIng->execute([
                ':num' => $num_ing,
                ':pid' => intval($d['proveedor_id']),
                ':uid' => sesionId(),
                ':tot' => $total,
                ':tp'  => $tipoPago,
                ':oid' => $orden_id,
                ':obs' => trim($d['observaciones'] ?? ''),
            ]);
            $ingreso_id = $insIng->fetch()['id'];

            foreach ($d['items'] as $item) {
                $sub_item   = floatval($item['cantidad']) * floatval($item['precio_unitario']);
                $productoId = intval($item['producto_id'] ?? 0) ?: null;
                $db->prepare("
                    INSERT INTO orden_compra_detalles
                        (orden_id, producto_id, descripcion, unidad_medida, cantidad, precio_unitario, subtotal)
                    VALUES (:oid, :pid, :desc, :um, :qty, :pu, :sub)
                ")->execute([
                    ':oid'  => $orden_id,
                    ':pid'  => $productoId,
                    ':desc' => trim($item['descripcion'] ?? ''),
                    ':um'   => trim($item['unidad_medida'] ?? ''),
                    ':qty'  => intval($item['cantidad']),
                    ':pu'   => floatval($item['precio_unitario']),
                    ':sub'  => $sub_item,
                ]);

                if ($productoId) {
                    $db->prepare("
                        INSERT INTO ingreso_detalles (ingreso_id, producto_id, cantidad, precio_unitario, subtotal)
                        VALUES (:iid, :pid, :qty, :pu, :sub)
                    ")->execute([
                        ':iid' => $ingreso_id,
                        ':pid' => $productoId,
                        ':qty' => intval($item['cantidad']),
                        ':pu'  => floatval($item['precio_unitario']),
                        ':sub' => $sub_item,
                    ]);
                    $db->prepare("UPDATE productos SET stock = stock + :qty, precio_compra = :pu WHERE id = :id")
                       ->execute([
                           ':qty' => intval($item['cantidad']),
                           ':pu'  => floatval($item['precio_unitario']),
                           ':id'  => $productoId,
                       ]);
                }
            }

            if ($tipoPago === 'credito') {
                $diasCredito = intval($d['dias_credito'] ?? 0);
                $vence = $diasCredito ? date('Y-m-d', strtotime("+{$diasCredito} days")) : null;
                $db->prepare("
                    INSERT INTO cuentas_por_pagar
                        (proveedor_id, ingreso_id, orden_compra_id, numero_doc, monto_total, monto_pendiente, estado, fecha_vencimiento)
                    VALUES (:pid, :iid, :oid, :doc, :mt, :mp, 'pendiente', :fv)
                ")->execute([
                    ':pid' => intval($d['proveedor_id']),
                    ':iid' => $ingreso_id,
                    ':oid' => $orden_id,
                    ':doc' => trim($d['numero_factura'] ?? ''),
                    ':mt'  => $total,
                    ':mp'  => $total,
                    ':fv'  => $vence,
                ]);
            }

            $db->commit();
            $num_orden = $db->prepare("SELECT numero_orden FROM ordenes_compra WHERE id = :id");
            $num_orden->execute([':id' => $orden_id]);
            jsonResponse(['error' => false, 'message' => 'Orden creada. Stock actualizado.', 'id' => $orden_id, 'numero' => $num_orden->fetchColumn()]);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => true, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        break;

    case 'orden_cambiar_estado':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = intval($d['id'] ?? 0);
        $estados_validos = ['borrador', 'pendiente', 'aprobada', 'cancelada'];
        if (!$id || !in_array($d['estado'] ?? '', $estados_validos)) {
            jsonResponse(['error' => true, 'message' => 'Estado no válido'], 400);
        }
        $db->prepare("UPDATE ordenes_compra SET estado = :e WHERE id = :id")
           ->execute([':e' => $d['estado'], ':id' => $id]);
        jsonResponse(['error' => false, 'message' => 'Estado actualizado']);
        break;

    // ----------------------------------------------------------------
    // CUENTAS POR PAGAR
    // ----------------------------------------------------------------
    case 'cuentas_listar':
        // Actualizar vencidas antes de listar
        $db->exec("
            UPDATE cuentas_por_pagar
            SET estado = 'vencido'
            WHERE fecha_vencimiento < CURRENT_DATE AND estado IN ('pendiente','parcial')
        ");
        $stmt = $db->query("
            SELECT cpp.id, cpp.numero_doc, cpp.monto_total, cpp.monto_pagado, cpp.monto_pendiente,
                   cpp.estado, cpp.fecha_vencimiento, cpp.created_at,
                   p.razon_social AS proveedor, p.ruc,
                   oc.numero_orden, i.numero_ingreso,
                   CASE WHEN cpp.fecha_vencimiento < CURRENT_DATE AND cpp.estado IN ('pendiente','parcial')
                        THEN TRUE ELSE FALSE END AS vencida
            FROM cuentas_por_pagar cpp
            LEFT JOIN proveedores p  ON p.id  = cpp.proveedor_id
            LEFT JOIN ordenes_compra oc ON oc.id = cpp.orden_compra_id
            LEFT JOIN ingresos i     ON i.id  = cpp.ingreso_id
            ORDER BY cpp.fecha_vencimiento ASC NULLS LAST, cpp.created_at DESC
        ");
        echo json_encode($stmt->fetchAll());
        break;

    case 'cuenta_detalle':
        $id   = intval($_GET['id'] ?? 0);
        $cpp  = $db->prepare("
            SELECT cpp.*, p.razon_social AS proveedor
            FROM cuentas_por_pagar cpp
            LEFT JOIN proveedores p ON p.id = cpp.proveedor_id
            WHERE cpp.id = :id
        ");
        $cpp->execute([':id' => $id]);
        $cuenta = $cpp->fetch();
        if (!$cuenta) jsonResponse(['error' => true, 'message' => 'Cuenta no encontrada'], 404);

        $pagos = $db->prepare("SELECT * FROM pagos_proveedor WHERE cuenta_id = :id ORDER BY created_at DESC");
        $pagos->execute([':id' => $id]);
        $cuenta['pagos'] = $pagos->fetchAll();
        echo json_encode($cuenta);
        break;

    case 'registrar_pago':
        $d = json_decode(file_get_contents('php://input'), true);
        $cuenta_id = intval($d['cuenta_id'] ?? 0);
        $monto     = floatval($d['monto'] ?? 0);
        if (!$cuenta_id || $monto <= 0) {
            jsonResponse(['error' => true, 'message' => 'Datos inválidos'], 400);
        }

        $cpp = $db->prepare("SELECT * FROM cuentas_por_pagar WHERE id = :id");
        $cpp->execute([':id' => $cuenta_id]);
        $cuenta = $cpp->fetch();
        if (!$cuenta) jsonResponse(['error' => true, 'message' => 'Cuenta no encontrada'], 404);
        if ($monto > floatval($cuenta['monto_pendiente'])) {
            jsonResponse(['error' => true, 'message' => 'El monto excede el pendiente'], 400);
        }

        $db->beginTransaction();
        try {
            $db->prepare("
                INSERT INTO pagos_proveedor (cuenta_id, monto, metodo_pago, referencia, usuario_id, notas)
                VALUES (:cid, :m, :mp, :ref, :uid, :notas)
            ")->execute([
                ':cid'   => $cuenta_id,
                ':m'     => $monto,
                ':mp'    => $d['metodo_pago'] ?? 'efectivo',
                ':ref'   => trim($d['referencia'] ?? ''),
                ':uid'   => sesionId(),
                ':notas' => trim($d['notas'] ?? ''),
            ]);

            $nuevo_pagado    = floatval($cuenta['monto_pagado']) + $monto;
            $nuevo_pendiente = floatval($cuenta['monto_total'])  - $nuevo_pagado;
            $nuevo_estado    = $nuevo_pendiente <= 0 ? 'pagado' : 'parcial';

            $db->prepare("
                UPDATE cuentas_por_pagar
                SET monto_pagado = :mp, monto_pendiente = :mpend, estado = :est
                WHERE id = :id
            ")->execute([':mp' => $nuevo_pagado, ':mpend' => max(0, $nuevo_pendiente), ':est' => $nuevo_estado, ':id' => $cuenta_id]);

            $db->commit();
            jsonResponse(['error' => false, 'message' => 'Pago registrado', 'estado' => $nuevo_estado]);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => true, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        break;

    // ----------------------------------------------------------------
    // CUENTAS POR COBRAR
    // ----------------------------------------------------------------
    case 'stats_cobrar':
        try {
            $row = $db->query("
                SELECT
                    (SELECT COUNT(*) FROM cuentas_por_cobrar WHERE estado IN ('pendiente','parcial'))           AS pendientes,
                    (SELECT COALESCE(SUM(monto_pendiente),0) FROM cuentas_por_cobrar WHERE estado IN ('pendiente','parcial')) AS total_por_cobrar,
                    (SELECT COUNT(*) FROM cuentas_por_cobrar WHERE estado = 'vencido')                          AS vencidas
            ")->fetch();
            echo json_encode($row);
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => 'Tablas no inicializadas. Ejecuta la migración.'], 500);
        }
        break;

    case 'cuentas_cobrar_listar':
        try {
            $db->exec("
                UPDATE cuentas_por_cobrar
                SET estado = 'vencido'
                WHERE fecha_vencimiento < CURRENT_DATE AND estado IN ('pendiente','parcial')
            ");
            $estado = $_GET['estado'] ?? '';
            $where  = $estado ? "WHERE cpc.estado = :estado" : '';
            $params = $estado ? [':estado' => $estado] : [];
            $stmt   = $db->prepare("
                SELECT cpc.id, cpc.numero_doc, cpc.monto_total, cpc.monto_pagado, cpc.monto_pendiente,
                       cpc.estado, cpc.fecha_vencimiento, cpc.notas, cpc.created_at,
                       TRIM(COALESCE(cl.nombres,'') || ' ' || COALESCE(cl.apellidos,'')) AS cliente,
                       cl.dni, cl.telefono,
                       v.numero_venta
                FROM cuentas_por_cobrar cpc
                LEFT JOIN clientes cl ON cl.id = cpc.cliente_id
                LEFT JOIN ventas   v  ON v.id  = cpc.venta_id
                {$where}
                ORDER BY cpc.fecha_vencimiento ASC NULLS LAST, cpc.created_at DESC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'cuenta_cobrar_detalle':
        $id = intval($_GET['id'] ?? 0);
        if (!$id) jsonResponse(['error' => true, 'message' => 'ID inválido'], 400);
        try {
            $cpc = $db->prepare("
                SELECT cpc.*, TRIM(COALESCE(cl.nombres,'') || ' ' || COALESCE(cl.apellidos,'')) AS cliente
                FROM cuentas_por_cobrar cpc
                LEFT JOIN clientes cl ON cl.id = cpc.cliente_id
                WHERE cpc.id = :id
            ");
            $cpc->execute([':id' => $id]);
            $cuenta = $cpc->fetch();
            if (!$cuenta) jsonResponse(['error' => true, 'message' => 'Cuenta no encontrada'], 404);
            $cobros = $db->prepare("SELECT * FROM cobros_cliente WHERE cuenta_id = :id ORDER BY created_at DESC");
            $cobros->execute([':id' => $id]);
            $cuenta['cobros'] = $cobros->fetchAll();
            echo json_encode($cuenta);
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    case 'registrar_cobro':
        $d         = json_decode(file_get_contents('php://input'), true);
        $cuenta_id = intval($d['cuenta_id'] ?? 0);
        $monto     = floatval($d['monto'] ?? 0);
        if (!$cuenta_id || $monto <= 0) jsonResponse(['error' => true, 'message' => 'Datos inválidos'], 400);
        $cpc = $db->prepare("SELECT * FROM cuentas_por_cobrar WHERE id = :id");
        $cpc->execute([':id' => $cuenta_id]);
        $cuenta = $cpc->fetch();
        if (!$cuenta) jsonResponse(['error' => true, 'message' => 'Cuenta no encontrada'], 404);
        if ($monto > floatval($cuenta['monto_pendiente'])) {
            jsonResponse(['error' => true, 'message' => 'El monto excede el saldo pendiente'], 400);
        }
        $db->beginTransaction();
        try {
            $db->prepare("
                INSERT INTO cobros_cliente (cuenta_id, monto, metodo_pago, referencia, usuario_id, notas)
                VALUES (:cid, :m, :mp, :ref, :uid, :notas)
            ")->execute([
                ':cid'   => $cuenta_id,
                ':m'     => $monto,
                ':mp'    => $d['metodo_pago'] ?? 'efectivo',
                ':ref'   => trim($d['referencia'] ?? ''),
                ':uid'   => sesionId(),
                ':notas' => trim($d['notas'] ?? ''),
            ]);
            $nuevo_pagado    = floatval($cuenta['monto_pagado']) + $monto;
            $nuevo_pendiente = floatval($cuenta['monto_total'])  - $nuevo_pagado;
            $nuevo_estado    = $nuevo_pendiente <= 0.001 ? 'pagado' : 'parcial';
            $db->prepare("
                UPDATE cuentas_por_cobrar
                SET monto_pagado = :mp, monto_pendiente = :mpend, estado = :est
                WHERE id = :id
            ")->execute([':mp' => $nuevo_pagado, ':mpend' => max(0, $nuevo_pendiente), ':est' => $nuevo_estado, ':id' => $cuenta_id]);
            $db->commit();
            jsonResponse(['error' => false, 'message' => 'Cobro registrado correctamente', 'estado' => $nuevo_estado]);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => true, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        break;

    case 'cuenta_cobrar_crear':
        $d          = json_decode(file_get_contents('php://input'), true);
        $cliente_id = intval($d['cliente_id'] ?? 0);
        $monto      = floatval($d['monto_total'] ?? 0);
        if (!$cliente_id || $monto <= 0) jsonResponse(['error' => true, 'message' => 'Cliente y monto son requeridos'], 400);
        try {
            $db->prepare("
                INSERT INTO cuentas_por_cobrar
                    (cliente_id, venta_id, numero_doc, monto_total, monto_pendiente, estado, fecha_vencimiento, notas)
                VALUES (:cid, :vid, :doc, :mt, :mp, 'pendiente', :fv, :notas)
            ")->execute([
                ':cid'   => $cliente_id,
                ':vid'   => intval($d['venta_id'] ?? 0) ?: null,
                ':doc'   => trim($d['numero_doc'] ?? ''),
                ':mt'    => $monto,
                ':mp'    => $monto,
                ':fv'    => $d['fecha_vencimiento'] ?: null,
                ':notas' => trim($d['notas'] ?? ''),
            ]);
            jsonResponse(['error' => false, 'message' => 'Cuenta por cobrar registrada']);
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        break;

    case 'clientes_lista':
        try {
            $stmt = $db->query("SELECT id, nombres, apellidos, dni FROM clientes WHERE activo = TRUE AND id != 1 ORDER BY nombres ASC");
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
        break;

    // ----------------------------------------------------------------
    // PROVEEDORES (para selectores)
    // ----------------------------------------------------------------
    case 'proveedores':
        $stmt = $db->query("SELECT id, razon_social, ruc FROM proveedores WHERE activo = TRUE ORDER BY razon_social ASC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'productos_buscar':
        $q    = trim($_GET['q'] ?? '');
        $stmt = $db->prepare("
            SELECT id, codigo, nombre, unidad, precio_compra, precio_venta, stock
            FROM productos
            WHERE activo = TRUE AND (nombre ILIKE :q OR codigo ILIKE :q)
            ORDER BY nombre ASC LIMIT 20
        ");
        $stmt->execute([':q' => "%{$q}%"]);
        echo json_encode($stmt->fetchAll());
        break;

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 404);
}
