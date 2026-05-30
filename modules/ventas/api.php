<?php
// ============================================================
// ARCHIVO: farmacia/modules/ventas/api.php
// DESCRIPCIÓN: API REST para el módulo de Ventas
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';
requireApiAuth();

$action = $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {

    // ---- GET: Listar productos activos con stock ----
    case 'productos':
        $stmt = $db->query("
            SELECT p.id, p.codigo, p.nombre, p.precio_venta, p.stock, p.stock_minimo,
                   p.laboratorio, p.presentacion, p.categoria_id, p.favorito,
                   c.nombre AS categoria
            FROM productos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            WHERE p.activo = TRUE
            ORDER BY p.favorito DESC, p.nombre ASC
        ");
        echo json_encode($stmt->fetchAll());
        break;

    // ---- POST: Crear nuevo cliente ----
    case 'crear_cliente':
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);

        $nombres = trim($data['nombres'] ?? '');
        if (!$nombres) jsonResponse(['error' => true, 'message' => 'El nombre es obligatorio'], 400);

        $stmt = $db->prepare("
            INSERT INTO clientes (nombres, apellidos, dni, ruc, telefono, email, direccion, activo)
            VALUES (:nombres, :apellidos, NULLIF(:dni,''), NULLIF(:ruc,''), NULLIF(:telefono,''), NULLIF(:email,''), NULLIF(:direccion,''), TRUE)
            RETURNING id, nombres, apellidos, dni, ruc, telefono, email, direccion
        ");
        $stmt->execute([
            ':nombres'   => $nombres,
            ':apellidos' => trim($data['apellidos'] ?? ''),
            ':dni'       => trim($data['dni']       ?? ''),
            ':ruc'       => trim($data['ruc']       ?? ''),
            ':telefono'  => trim($data['telefono']  ?? ''),
            ':email'     => trim($data['email']     ?? ''),
            ':direccion' => trim($data['direccion'] ?? ''),
        ]);
        $cliente = $stmt->fetch();
        echo json_encode(['error' => false, 'cliente' => $cliente]);
        break;

    // ---- GET: Buscar cliente por DNI, RUC o nombre ----
    case 'buscar_cliente':
        $q = '%' . trim($_GET['q'] ?? '') . '%';
        $stmt = $db->prepare("
            SELECT id, nombres, apellidos, dni, ruc, telefono, email, direccion
            FROM clientes
            WHERE activo = TRUE
              AND (nombres ILIKE :q OR apellidos ILIKE :q OR dni ILIKE :q OR COALESCE(ruc,'') ILIKE :q)
            LIMIT 1
        ");
        $stmt->execute([':q' => $q]);
        $row = $stmt->fetch();
        echo json_encode($row ?: null);
        break;

    // ---- POST: Registrar venta ----
    case 'registrar_venta':
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data || empty($data['items'])) {
            jsonResponse(['error' => true, 'message' => 'Datos inválidos'], 400);
        }

        // Calcular totales
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += $item['cantidad'] * $item['precio'];
        }
        $descuento = $data['descuento'] ?? 0;
        $total     = $subtotal - $descuento;
        $igv       = round($total * 0.18, 2);

        // Verificar caja abierta — obligatorio para registrar ventas
        $cajaStmt = $db->query("SELECT id FROM cajas WHERE estado = 'abierta' LIMIT 1");
        $caja = $cajaStmt->fetch();
        if (!$caja) {
            jsonResponse(['error' => true, 'message' => 'No hay una caja abierta. Debes aperturar la caja antes de registrar ventas.', 'caja_cerrada' => true], 422);
        }
        $cajaId = $caja['id'];

        $tipo_comp = $data['tipo_comprobante'] ?? 'ticket';

        $db->beginTransaction();
        try {
            // Verificar stock y capturar nombre + código de cada producto
            $productos_info = [];
            foreach ($data['items'] as $item) {
                $stockStmt = $db->prepare("SELECT stock, nombre, codigo FROM productos WHERE id = :id AND activo = TRUE");
                $stockStmt->execute([':id' => $item['producto_id']]);
                $prod = $stockStmt->fetch();
                if (!$prod) throw new Exception("Producto ID {$item['producto_id']} no encontrado");
                if ($prod['stock'] < $item['cantidad']) {
                    throw new Exception("Stock insuficiente para: {$prod['nombre']} (disponible: {$prod['stock']})");
                }
                $productos_info[$item['producto_id']] = ['nombre' => $prod['nombre'], 'codigo' => $prod['codigo']];
            }

            // Obtener datos del cliente (necesarios para Nubefact)
            $cliente_info = null;
            if (!empty($data['cliente_id'])) {
                $cliStmt = $db->prepare("SELECT id, nombres, apellidos, dni, ruc, telefono, email, direccion FROM clientes WHERE id = :id");
                $cliStmt->execute([':id' => $data['cliente_id']]);
                $cliente_info = $cliStmt->fetch() ?: null;
            }

            // Validar: factura requiere cliente con RUC
            if ($tipo_comp === 'factura') {
                if (!$cliente_info || empty($cliente_info['ruc'])) {
                    throw new Exception('Para emitir factura debe seleccionar un cliente con RUC registrado');
                }
            }

            // Insertar venta
            $numVenta = generarNumeroVenta($db);
            $stmt = $db->prepare("
                INSERT INTO ventas (numero_venta, cliente_id, caja_id, subtotal, descuento, igv, total,
                                    tipo_pago, tipo_comprobante, estado, vendedor)
                VALUES (:num, :cli, :caja, :sub, :desc, :igv, :total, :pago, :comp, 'completada', 'Administrador')
                RETURNING id
            ");
            $stmt->execute([
                ':num'   => $numVenta,
                ':cli'   => $data['cliente_id'] ?: null,
                ':caja'  => $cajaId,
                ':sub'   => $subtotal,
                ':desc'  => $descuento,
                ':igv'   => $igv,
                ':total' => $total,
                ':pago'  => $data['tipo_pago']  ?? 'efectivo',
                ':comp'  => $tipo_comp,
            ]);
            $ventaId = $stmt->fetch()['id'];

            // Insertar detalles y actualizar stock
            foreach ($data['items'] as $item) {
                $sub = $item['cantidad'] * $item['precio'];
                $db->prepare("
                    INSERT INTO venta_detalles (venta_id, producto_id, cantidad, precio_unitario, subtotal)
                    VALUES (:vid, :pid, :qty, :precio, :sub)
                ")->execute([
                    ':vid'    => $ventaId,
                    ':pid'    => $item['producto_id'],
                    ':qty'    => $item['cantidad'],
                    ':precio' => $item['precio'],
                    ':sub'    => $sub,
                ]);
                $db->prepare("
                    UPDATE productos
                    SET stock = stock - :qty, total_vendido = total_vendido + :qty, updated_at = NOW()
                    WHERE id = :id
                ")->execute([':qty' => $item['cantidad'], ':id' => $item['producto_id']]);
            }

            // Actualizar saldo caja
            if ($cajaId) {
                $db->prepare("UPDATE cajas SET saldo_actual = saldo_actual + :total WHERE id = :id")
                   ->execute([':total' => $total, ':id' => $cajaId]);
            }

            // Asignar número de comprobante electrónico (dentro de la transacción para atomicidad)
            $comp_data = null;
            if (in_array($tipo_comp, ['boleta', 'factura'])) {
                $nStmt = $db->prepare("
                    UPDATE series_comprobantes
                    SET ultimo_numero = ultimo_numero + 1
                    WHERE tipo = :tipo
                    RETURNING ultimo_numero, serie
                ");
                $nStmt->execute([':tipo' => $tipo_comp]);
                $nRow = $nStmt->fetch();
                if (!$nRow) throw new Exception("No hay serie configurada para $tipo_comp. Contacte al administrador.");

                $num_correlativo = intval($nRow['ultimo_numero']);
                $serie           = $nRow['serie'];
                $numero_completo = $serie . '-' . str_pad($num_correlativo, 8, '0', STR_PAD_LEFT);

                $cStmt = $db->prepare("
                    INSERT INTO comprobantes_electronicos (venta_id, tipo, serie, numero, numero_completo, estado_sunat)
                    VALUES (:vid, :tipo, :serie, :num, :nc, 'pendiente')
                    RETURNING id
                ");
                $cStmt->execute([
                    ':vid'   => $ventaId,
                    ':tipo'  => $tipo_comp,
                    ':serie' => $serie,
                    ':num'   => $num_correlativo,
                    ':nc'    => $numero_completo,
                ]);
                $comp_data = [
                    'id'              => $cStmt->fetch()['id'],
                    'tipo'            => $tipo_comp,
                    'serie'           => $serie,
                    'numero'          => $num_correlativo,
                    'numero_completo' => $numero_completo,
                ];
            }

            $db->commit();

            // ---- Post-commit: enviar a Nubefact (no bloquea la venta si falla) ----
            $comprobante_result = null;
            if ($comp_data) {
                require_once '../../config/nubefact.php';
                $items_nf = array_map(fn($i) => array_merge($i, $productos_info[$i['producto_id']]), $data['items']);
                $venta_info = ['numero_venta' => $numVenta, 'tipo_pago' => $data['tipo_pago'] ?? 'efectivo'];
                $comprobante_result = enviar_nubefact($db, $comp_data, $cliente_info, $items_nf, $venta_info);
            }

            echo json_encode([
                'error'         => false,
                'numero_venta'  => $numVenta,
                'venta_id'      => $ventaId,
                'total'         => $total,
                'monto_recibido'=> $data['monto_recibido'] ?? $total,
                'items'         => array_map(fn($i) => array_merge($i, ['qty' => $i['cantidad']]), $data['items']),
                'comprobante'   => $comprobante_result,
            ]);

        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 422);
        }
        break;

    // ---- GET: Historial de ventas ----
    case 'historial':
        $desde  = $_GET['desde']  ?? date('Y-m-d', strtotime('-30 days'));
        $hasta  = $_GET['hasta']  ?? date('Y-m-d');
        $estado = $_GET['estado'] ?? '';
        $q      = '%' . ($_GET['q'] ?? '') . '%';

        $params = [':desde' => $desde, ':hasta' => $hasta . ' 23:59:59', ':q' => $q];
        $estFilter = $estado ? "AND v.estado = :estado" : '';
        if ($estado) $params[':estado'] = $estado;

        $stmt = $db->prepare("
            SELECT v.id, v.numero_venta, v.total, v.subtotal, v.descuento, v.igv,
                   v.tipo_pago, v.tipo_comprobante, v.estado, v.vendedor, v.created_at,
                   COALESCE(c.nombres || ' ' || COALESCE(c.apellidos,''), 'Cliente General') AS cliente,
                   COUNT(vd.id) AS num_items
            FROM ventas v
            LEFT JOIN clientes c ON c.id = v.cliente_id
            LEFT JOIN venta_detalles vd ON vd.venta_id = v.id
            WHERE v.created_at BETWEEN :desde AND :hasta
              AND (v.numero_venta ILIKE :q OR COALESCE(c.nombres,'') ILIKE :q)
              $estFilter
            GROUP BY v.id, c.nombres, c.apellidos
            ORDER BY v.created_at DESC
            LIMIT 200
        ");
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        break;

    // ---- GET: Detalle de una venta ----
    case 'detalle_venta':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $db->prepare("
            SELECT vd.*, p.nombre AS producto_nombre, p.codigo
            FROM venta_detalles vd
            JOIN productos p ON p.id = vd.producto_id
            WHERE vd.venta_id = :id
        ");
        $stmt->execute([':id' => $id]);
        echo json_encode($stmt->fetchAll());
        break;

    // ---- POST: Anular venta ----
    case 'anular_venta':
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $id   = intval($data['id'] ?? 0);

        $db->beginTransaction();
        try {
            // Verificar que esté completada
            $check = $db->prepare("SELECT estado, total, caja_id FROM ventas WHERE id = :id");
            $check->execute([':id' => $id]);
            $venta = $check->fetch();
            if (!$venta) throw new Exception('Venta no encontrada');
            if ($venta['estado'] === 'anulada') throw new Exception('La venta ya está anulada');

            // Revertir stock
            $detalles = $db->prepare("SELECT producto_id, cantidad FROM venta_detalles WHERE venta_id = :id");
            $detalles->execute([':id' => $id]);
            foreach ($detalles->fetchAll() as $det) {
                $db->prepare("UPDATE productos SET stock = stock + :qty WHERE id = :pid")
                   ->execute([':qty' => $det['cantidad'], ':pid' => $det['producto_id']]);
            }

            // Actualizar estado
            $db->prepare("UPDATE ventas SET estado = 'anulada' WHERE id = :id")->execute([':id' => $id]);

            // Descontar de caja
            if ($venta['caja_id']) {
                $db->prepare("UPDATE cajas SET saldo_actual = saldo_actual - :total WHERE id = :cid")
                   ->execute([':total' => $venta['total'], ':cid' => $venta['caja_id']]);
            }

            $db->commit();
            echo json_encode(['error' => false, 'message' => 'Venta anulada correctamente']);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 422);
        }
        break;

    // ---- GET: Estadísticas del día ----
    case 'stats_dia':
        $hoy = date('Y-m-d');
        $row = $db->query("
            SELECT
                COUNT(*) FILTER (WHERE estado = 'completada') AS total_ventas,
                COALESCE(SUM(total) FILTER (WHERE estado = 'completada'), 0) AS ingresos,
                COALESCE(AVG(total) FILTER (WHERE estado = 'completada'), 0) AS ticket_promedio,
                COUNT(*) FILTER (WHERE estado = 'anulada') AS anuladas
            FROM ventas
            WHERE DATE(created_at) = '$hoy'
        ")->fetch();
        echo json_encode($row);
        break;

    // ---- GET: Top productos más vendidos ----
    case 'top_vendidos':
        $limite = intval($_GET['limite'] ?? 10);
        $stmt = $db->prepare("
            SELECT p.id, p.nombre, p.laboratorio, p.total_vendido, p.precio_venta,
                   p.favorito, c.nombre AS categoria
            FROM productos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            WHERE p.activo = TRUE AND p.total_vendido > 0
            ORDER BY p.total_vendido DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode($stmt->fetchAll());
        break;

    // ---- GET: Favoritos ----
    case 'favoritos':
        $stmt = $db->query("
            SELECT p.id, p.nombre, p.laboratorio, p.total_vendido, p.precio_venta, p.stock,
                   c.nombre AS categoria
            FROM productos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            WHERE p.activo = TRUE AND p.favorito = TRUE
            ORDER BY p.total_vendido DESC
        ");
        echo json_encode($stmt->fetchAll());
        break;

    // ---- POST: Toggle favorito ----
    case 'toggle_favorito':
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $id   = intval($data['id'] ?? 0);
        $stmt = $db->prepare("UPDATE productos SET favorito = NOT favorito WHERE id = :id RETURNING favorito");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        echo json_encode(['error' => false, 'favorito' => $row['favorito']]);
        break;

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 404);
}