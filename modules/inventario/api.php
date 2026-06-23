<?php
// ============================================================
// ARCHIVO: farmacia/modules/inventario/api.php
// DESCRIPCION: API REST para el modulo de Inventario
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';
requireApiAuth(['admin', 'gerente', 'cajero']);

$action = $_GET['action'] ?? '';
$db     = getDB();

function resolverCatalogosProducto(PDO $db, string $unidadCodigo, string $afectacionCodigo): array
{
    $stmtUnidad = $db->prepare("
        SELECT id, codigo, descripcion
        FROM public.fe_unidades
        WHERE codigo = :codigo AND estado = TRUE
        LIMIT 1
    ");
    $stmtUnidad->execute([':codigo' => $unidadCodigo]);
    $unidad = $stmtUnidad->fetch();

    if (!$unidad) {
        jsonResponse(['error' => true, 'message' => 'La unidad SUNAT seleccionada no existe'], 422);
    }

    $stmtAfectacion = $db->prepare("
        SELECT id, codigo, descripcion, tipo
        FROM public.fe_tipos_afectacion_igv
        WHERE codigo = :codigo AND estado = TRUE
        LIMIT 1
    ");
    $stmtAfectacion->execute([':codigo' => $afectacionCodigo]);
    $afectacion = $stmtAfectacion->fetch();

    if (!$afectacion) {
        jsonResponse(['error' => true, 'message' => 'La afectacion IGV seleccionada no existe'], 422);
    }

    return [
        'unidad_id' => intval($unidad['id']),
        'unidad_codigo' => $unidad['codigo'],
        'afectacion_id' => intval($afectacion['id']),
        'afectacion_codigo' => $afectacion['codigo'],
        'afectacion_tipo' => $afectacion['tipo'],
    ];
}

switch ($action) {

    case 'stats':
        $row = $db->query("
            SELECT
                COUNT(*) FILTER (WHERE activo = TRUE) AS total_activos,
                COUNT(*) FILTER (WHERE activo = TRUE AND stock = 0) AS agotados,
                COUNT(*) FILTER (WHERE activo = TRUE AND stock > 0 AND stock <= stock_minimo) AS stock_bajo,
                COUNT(*) FILTER (WHERE activo = FALSE) AS inactivos,
                COALESCE(SUM(stock * precio_compra) FILTER (WHERE activo = TRUE), 0) AS valor_inventario
            FROM productos
        ")->fetch();
        echo json_encode($row);
        break;

    case 'listar':
        $q            = '%' . trim($_GET['q'] ?? '') . '%';
        $categoria_id = intval($_GET['categoria_id'] ?? 0);
        $stock_status = $_GET['stock_status'] ?? '';

        $where  = ['(p.nombre ILIKE :q OR p.codigo ILIKE :q OR COALESCE(p.laboratorio, \'\') ILIKE :q OR COALESCE(p.codigo_barras, \'\') ILIKE :q)'];
        $params = [':q' => $q];

        if ($categoria_id > 0) {
            $where[] = 'p.categoria_id = :cat';
            $params[':cat'] = $categoria_id;
        }

        switch ($stock_status) {
            case 'agotado':
                $where[] = 'p.stock = 0';
                break;
            case 'bajo':
                $where[] = 'p.stock > 0 AND p.stock <= p.stock_minimo';
                break;
            case 'ok':
                $where[] = 'p.stock > p.stock_minimo';
                break;
            case 'inactivo':
                $where[] = 'p.activo = FALSE';
                break;
            default:
                $where[] = 'p.activo = TRUE';
                break;
        }

        $sql = "
            SELECT
                p.id,
                p.codigo,
                p.codigo_interno,
                p.codigo_barras,
                p.codigo_sunat,
                p.nombre,
                p.laboratorio,
                p.presentacion,
                p.precio_compra,
                p.precio_venta,
                p.stock,
                p.stock_minimo,
                p.unidad,
                p.unidad_id,
                p.unidad_codigo,
                COALESCE(u.descripcion, 'Unidad') AS unidad_sunat,
                p.afectacion_igv_id,
                p.afectacion_igv_codigo,
                a.tipo AS afectacion_igv_tipo,
                COALESCE(a.descripcion, 'Gravado - Operacion onerosa') AS afectacion_igv,
                p.porcentaje_igv,
                p.incluye_igv,
                p.icbper_activo,
                p.factor_icbper,
                p.product_type,
                p.requiere_receta,
                p.favorito,
                p.activo,
                p.total_vendido,
                p.categoria_id,
                c.nombre AS categoria,
                p.fecha_vencimiento
            FROM productos p
            LEFT JOIN public.categorias c ON c.id = p.categoria_id
            LEFT JOIN public.fe_unidades u ON u.id = p.unidad_id
            LEFT JOIN public.fe_tipos_afectacion_igv a ON a.id = p.afectacion_igv_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.nombre ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        break;

    case 'categorias':
        $rows = $db->query("
            SELECT c.id, c.nombre, COUNT(p.id) AS total_productos
            FROM public.categorias c
            LEFT JOIN productos p ON p.categoria_id = c.id AND p.activo = TRUE
            WHERE c.activo = TRUE
            GROUP BY c.id, c.nombre
            ORDER BY c.nombre
        ")->fetchAll();
        echo json_encode($rows);
        break;

    case 'catalogos_facturacion':
        $unidades = $db->query("
            SELECT id, codigo, descripcion
            FROM public.fe_unidades
            WHERE estado = TRUE
            ORDER BY descripcion
        ")->fetchAll();

        $afectaciones = $db->query("
            SELECT id, codigo, descripcion, tipo
            FROM public.fe_tipos_afectacion_igv
            WHERE estado = TRUE
            ORDER BY codigo
        ")->fetchAll();

        jsonResponse([
            'error' => false,
            'unidades' => $unidades,
            'afectaciones_igv' => $afectaciones,
        ]);

    case 'crear_categoria':
        if (!isAdmin()) jsonResponse(['error' => true, 'message' => 'Solo administradores pueden gestionar categorías'], 403);
        $data   = json_decode(file_get_contents('php://input'), true);
        $nombre = trim($data['nombre'] ?? '');
        if (!$nombre) {
            jsonResponse(['error' => true, 'message' => 'El nombre es requerido'], 400);
        }

        $check = $db->prepare("SELECT id FROM public.categorias WHERE LOWER(nombre) = LOWER(:nombre)");
        $check->execute([':nombre' => $nombre]);
        if ($check->fetch()) {
            jsonResponse(['error' => true, 'message' => 'Ya existe una categoria con ese nombre'], 409);
        }

        $stmt = $db->prepare("INSERT INTO public.categorias (nombre) VALUES (:nombre) RETURNING id, nombre");
        $stmt->execute([':nombre' => $nombre]);
        $cat = $stmt->fetch();
        jsonResponse(['error' => false, 'message' => 'Categoria creada', 'id' => $cat['id'], 'nombre' => $cat['nombre']]);
        break;

    case 'editar_categoria':
        if (!isAdmin()) jsonResponse(['error' => true, 'message' => 'Solo administradores pueden gestionar categorías'], 403);
        $data   = json_decode(file_get_contents('php://input'), true);
        $id     = intval($data['id'] ?? 0);
        $nombre = trim($data['nombre'] ?? '');
        if (!$id || !$nombre) {
            jsonResponse(['error' => true, 'message' => 'Datos invalidos'], 400);
        }

        $check = $db->prepare("SELECT id FROM public.categorias WHERE LOWER(nombre) = LOWER(:nombre) AND id != :id");
        $check->execute([':nombre' => $nombre, ':id' => $id]);
        if ($check->fetch()) {
            jsonResponse(['error' => true, 'message' => 'Ya existe una categoria con ese nombre'], 409);
        }

        $db->prepare("UPDATE public.categorias SET nombre = :nombre WHERE id = :id")
           ->execute([':nombre' => $nombre, ':id' => $id]);
        jsonResponse(['error' => false, 'message' => 'Categoria actualizada', 'id' => $id, 'nombre' => $nombre]);
        break;

    case 'eliminar_categoria':
        if (!isAdmin()) jsonResponse(['error' => true, 'message' => 'Solo administradores pueden gestionar categorías'], 403);
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id) {
            jsonResponse(['error' => true, 'message' => 'ID invalido'], 400);
        }

        $uso = $db->prepare("SELECT COUNT(*) AS total FROM productos WHERE categoria_id = :id AND activo = TRUE");
        $uso->execute([':id' => $id]);
        $total = intval($uso->fetch()['total']);
        if ($total > 0) {
            jsonResponse(['error' => true, 'message' => "No se puede eliminar: $total producto(s) usan esta categoria"], 409);
        }

        $db->prepare("DELETE FROM public.categorias WHERE id = :id")->execute([':id' => $id]);
        jsonResponse(['error' => false, 'message' => 'Categoria eliminada']);
        break;

    case 'crear':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['nombre']) || empty($data['codigo']) || !isset($data['precio_venta'])) {
            jsonResponse(['error' => true, 'message' => 'Campos requeridos: codigo, nombre, precio de venta'], 400);
        }

        $codigo = trim($data['codigo']);
        $check = $db->prepare("SELECT id FROM productos WHERE codigo = :codigo");
        $check->execute([':codigo' => $codigo]);
        if ($check->fetch()) {
            jsonResponse(['error' => true, 'message' => 'Ya existe un producto con ese codigo'], 409);
        }

        $codigoSunat = preg_match('/^\d{8}$/', trim($data['codigo_sunat'] ?? '')) ? trim($data['codigo_sunat']) : '00000000';
        $unidadTexto = trim($data['unidad'] ?? 'unidad') ?: 'unidad';
        $unidadCodigo = strtoupper(trim($data['unidad_codigo'] ?? 'NIU')) ?: 'NIU';
        $afectacionCodigo = trim($data['afectacion_igv_codigo'] ?? '10') ?: '10';
        $porcentajeIgv = round(floatval($data['porcentaje_igv'] ?? 18), 2);
        $incluyeIgv = !array_key_exists('incluye_igv', $data) || (bool) $data['incluye_igv'];
        $codigoBarras = trim($data['codigo_barras'] ?? '');
        $catalogos = resolverCatalogosProducto($db, $unidadCodigo, $afectacionCodigo);
        $esGravado = ($catalogos['afectacion_tipo'] ?? '') === 'GRAV';

        $stmt = $db->prepare("
            INSERT INTO productos
                (codigo, codigo_interno, codigo_barras, codigo_sunat, nombre, categoria_id, laboratorio, presentacion, unidad,
                 unidad_id, unidad_codigo, afectacion_igv_id, afectacion_igv_codigo, porcentaje_igv, incluye_igv,
                 precio_compra, precio_venta, stock, stock_minimo, requiere_receta, favorito, activo, fecha_vencimiento)
            VALUES
                (:codigo, :codigo_interno, :codigo_barras, :codigo_sunat, :nombre, :cat, :lab, :pres, :unidad,
                 :unidad_id, :unidad_codigo, :afectacion_igv_id, :afectacion_igv_codigo, :porcentaje_igv, :incluye_igv,
                 :p_compra, :p_venta, :stock, :stock_min, :receta, :favorito, TRUE, :fvenc)
            RETURNING id
        ");
        $sku = trim($data['sku'] ?? '') ?: $codigo;
        $stmt->execute([
            ':codigo' => $codigo,
            ':codigo_interno' => $sku,
            ':codigo_barras' => $codigoBarras ?: null,
            ':codigo_sunat' => $codigoSunat,
            ':nombre' => trim($data['nombre']),
            ':cat' => $data['categoria_id'] ?: null,
            ':lab' => trim($data['laboratorio'] ?? ''),
            ':pres' => trim($data['presentacion'] ?? ''),
            ':unidad' => $unidadTexto,
            ':unidad_id' => $catalogos['unidad_id'],
            ':unidad_codigo' => $catalogos['unidad_codigo'],
            ':afectacion_igv_id' => $catalogos['afectacion_id'],
            ':afectacion_igv_codigo' => $catalogos['afectacion_codigo'],
            ':porcentaje_igv' => $esGravado ? ($porcentajeIgv > 0 ? $porcentajeIgv : 18) : 0,
            ':incluye_igv' => ($esGravado && $incluyeIgv) ? 'TRUE' : 'FALSE',
            ':p_compra' => floatval($data['precio_compra'] ?? 0),
            ':p_venta' => floatval($data['precio_venta']),
            ':stock' => intval($data['stock'] ?? 0),
            ':stock_min' => intval($data['stock_minimo'] ?? 5),
            ':receta' => !empty($data['requiere_receta']) ? 'TRUE' : 'FALSE',
            ':favorito' => !empty($data['favorito']) ? 'TRUE' : 'FALSE',
            ':fvenc' => $data['fecha_vencimiento'] ?: null,
        ]);
        $id = $stmt->fetch()['id'];
        jsonResponse(['error' => false, 'message' => 'Producto creado correctamente', 'id' => $id]);

    case 'actualizar':
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id || empty($data['nombre']) || empty($data['codigo']) || !isset($data['precio_venta'])) {
            jsonResponse(['error' => true, 'message' => 'Datos invalidos'], 400);
        }

        $codigo = trim($data['codigo']);
        $check = $db->prepare("SELECT id FROM productos WHERE codigo = :codigo AND id != :id");
        $check->execute([':codigo' => $codigo, ':id' => $id]);
        if ($check->fetch()) {
            jsonResponse(['error' => true, 'message' => 'Ya existe otro producto con ese codigo'], 409);
        }

        $codigoSunat = preg_match('/^\d{8}$/', trim($data['codigo_sunat'] ?? '')) ? trim($data['codigo_sunat']) : '00000000';
        $unidadTexto = trim($data['unidad'] ?? 'unidad') ?: 'unidad';
        $unidadCodigo = strtoupper(trim($data['unidad_codigo'] ?? 'NIU')) ?: 'NIU';
        $afectacionCodigo = trim($data['afectacion_igv_codigo'] ?? '10') ?: '10';
        $porcentajeIgv = round(floatval($data['porcentaje_igv'] ?? 18), 2);
        $incluyeIgv = !array_key_exists('incluye_igv', $data) || (bool) $data['incluye_igv'];
        $codigoBarras = trim($data['codigo_barras'] ?? '');
        $catalogos = resolverCatalogosProducto($db, $unidadCodigo, $afectacionCodigo);
        $esGravado = ($catalogos['afectacion_tipo'] ?? '') === 'GRAV';

        $stmt = $db->prepare("
            UPDATE productos SET
                codigo = :codigo,
                codigo_interno = :codigo_interno,
                codigo_barras = :codigo_barras,
                codigo_sunat = :codigo_sunat,
                nombre = :nombre,
                categoria_id = :cat,
                laboratorio = :lab,
                presentacion = :pres,
                unidad = :unidad,
                unidad_id = :unidad_id,
                unidad_codigo = :unidad_codigo,
                afectacion_igv_id = :afectacion_igv_id,
                afectacion_igv_codigo = :afectacion_igv_codigo,
                porcentaje_igv = :porcentaje_igv,
                incluye_igv = :incluye_igv,
                precio_compra = :p_compra,
                precio_venta = :p_venta,
                stock_minimo = :stock_min,
                requiere_receta = :receta,
                favorito = :favorito,
                fecha_vencimiento = :fvenc,
                updated_at = NOW()
            WHERE id = :id
        ");
        $sku = trim($data['sku'] ?? '') ?: $codigo;
        $stmt->execute([
            ':id' => $id,
            ':codigo' => $codigo,
            ':codigo_interno' => $sku,
            ':codigo_barras' => $codigoBarras ?: null,
            ':codigo_sunat' => $codigoSunat,
            ':nombre' => trim($data['nombre']),
            ':cat' => $data['categoria_id'] ?: null,
            ':lab' => trim($data['laboratorio'] ?? ''),
            ':pres' => trim($data['presentacion'] ?? ''),
            ':unidad' => $unidadTexto,
            ':unidad_id' => $catalogos['unidad_id'],
            ':unidad_codigo' => $catalogos['unidad_codigo'],
            ':afectacion_igv_id' => $catalogos['afectacion_id'],
            ':afectacion_igv_codigo' => $catalogos['afectacion_codigo'],
            ':porcentaje_igv' => $esGravado ? ($porcentajeIgv > 0 ? $porcentajeIgv : 18) : 0,
            ':incluye_igv' => ($esGravado && $incluyeIgv) ? 'TRUE' : 'FALSE',
            ':p_compra' => floatval($data['precio_compra'] ?? 0),
            ':p_venta' => floatval($data['precio_venta']),
            ':stock_min' => intval($data['stock_minimo'] ?? 5),
            ':receta' => !empty($data['requiere_receta']) ? 'TRUE' : 'FALSE',
            ':favorito' => !empty($data['favorito']) ? 'TRUE' : 'FALSE',
            ':fvenc' => $data['fecha_vencimiento'] ?: null,
        ]);
        jsonResponse(['error' => false, 'message' => 'Producto actualizado correctamente']);

    case 'ajustar_stock':
        if (!isAdmin()) jsonResponse(['error' => true, 'message' => 'Solo administradores pueden ajustar stock manualmente'], 403);
        $data     = json_decode(file_get_contents('php://input'), true);
        $id       = intval($data['id'] ?? 0);
        $tipo     = $data['tipo'] ?? '';
        $cantidad = intval($data['cantidad'] ?? 0);

        if (!$id || !in_array($tipo, ['entrada', 'salida'], true) || $cantidad <= 0) {
            jsonResponse(['error' => true, 'message' => 'Datos invalidos'], 400);
        }

        if ($tipo === 'salida') {
            $prod = $db->prepare("SELECT stock, nombre FROM productos WHERE id = :id");
            $prod->execute([':id' => $id]);
            $row = $prod->fetch();
            if (!$row) {
                jsonResponse(['error' => true, 'message' => 'Producto no encontrado'], 404);
            }
            if ($row['stock'] < $cantidad) {
                jsonResponse(['error' => true, 'message' => "Stock insuficiente. Disponible: {$row['stock']}"], 422);
            }
        }

        $delta = $tipo === 'entrada' ? $cantidad : -$cantidad;
        $stmt  = $db->prepare("UPDATE productos SET stock = stock + :delta, updated_at = NOW() WHERE id = :id RETURNING stock");
        $stmt->execute([':delta' => $delta, ':id' => $id]);
        $nuevo_stock = $stmt->fetch()['stock'];

        jsonResponse(['error' => false, 'message' => 'Stock ajustado correctamente', 'nuevo_stock' => $nuevo_stock]);

    case 'toggle_activo':
        if (!isAdmin()) jsonResponse(['error' => true, 'message' => 'Solo administradores pueden activar o desactivar productos'], 403);
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id) {
            jsonResponse(['error' => true, 'message' => 'ID invalido'], 400);
        }

        $stmt = $db->prepare("UPDATE productos SET activo = NOT activo, updated_at = NOW() WHERE id = :id RETURNING activo");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        jsonResponse(['error' => false, 'activo' => $row['activo']]);

    default:
        jsonResponse(['error' => true, 'message' => 'Accion no valida'], 404);
}
