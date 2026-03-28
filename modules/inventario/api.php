<?php
// ============================================================
// ARCHIVO: farmacia/modules/inventario/api.php
// DESCRIPCIÓN: API REST para el módulo de Inventario
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/database.php';

$action = $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {

    // ---- GET: Estadísticas de inventario ----
    case 'stats':
        $row = $db->query("
            SELECT
                COUNT(*) FILTER (WHERE activo = TRUE)                                        AS total_activos,
                COUNT(*) FILTER (WHERE activo = TRUE AND stock = 0)                          AS agotados,
                COUNT(*) FILTER (WHERE activo = TRUE AND stock > 0 AND stock <= stock_minimo) AS stock_bajo,
                COUNT(*) FILTER (WHERE activo = FALSE)                                       AS inactivos,
                COALESCE(SUM(stock * precio_compra) FILTER (WHERE activo = TRUE), 0)         AS valor_inventario
            FROM productos
        ")->fetch();
        echo json_encode($row);
        break;

    // ---- GET: Listar productos con filtros ----
    case 'listar':
        $q            = '%' . trim($_GET['q'] ?? '') . '%';
        $categoria_id = intval($_GET['categoria_id'] ?? 0);
        $stock_status = $_GET['stock_status'] ?? '';

        $where  = ['(p.nombre ILIKE :q OR p.codigo ILIKE :q OR COALESCE(p.laboratorio,\'\') ILIKE :q)'];
        $params = [':q' => $q];

        if ($categoria_id > 0) {
            $where[] = 'p.categoria_id = :cat';
            $params[':cat'] = $categoria_id;
        }

        switch ($stock_status) {
            case 'agotado':  $where[] = 'p.stock = 0'; break;
            case 'bajo':     $where[] = 'p.stock > 0 AND p.stock <= p.stock_minimo'; break;
            case 'ok':       $where[] = 'p.stock > p.stock_minimo'; break;
            case 'inactivo': $where[] = 'p.activo = FALSE'; break;
            default:         $where[] = 'p.activo = TRUE'; break;
        }

        $sql = "
            SELECT p.id, p.codigo, p.nombre, p.laboratorio, p.presentacion,
                   p.precio_compra, p.precio_venta, p.stock, p.stock_minimo,
                   p.requiere_receta, p.favorito, p.activo, p.total_vendido,
                   p.categoria_id, c.nombre AS categoria
            FROM productos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.nombre ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        break;

    // ---- GET: Categorías para el formulario ----
    case 'categorias':
        $rows = $db->query("SELECT id, nombre FROM categorias WHERE activo = TRUE ORDER BY nombre")->fetchAll();
        echo json_encode($rows);
        break;

    // ---- POST: Crear producto ----
    case 'crear':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['nombre']) || empty($data['codigo']) || !isset($data['precio_venta'])) {
            jsonResponse(['error' => true, 'message' => 'Campos requeridos: código, nombre, precio de venta'], 400);
        }

        // Verificar código único
        $check = $db->prepare("SELECT id FROM productos WHERE codigo = :codigo");
        $check->execute([':codigo' => trim($data['codigo'])]);
        if ($check->fetch()) {
            jsonResponse(['error' => true, 'message' => 'Ya existe un producto con ese código'], 409);
        }

        $stmt = $db->prepare("
            INSERT INTO productos
                (codigo, nombre, categoria_id, laboratorio, presentacion,
                 precio_compra, precio_venta, stock, stock_minimo,
                 requiere_receta, favorito, activo)
            VALUES
                (:codigo, :nombre, :cat, :lab, :pres,
                 :p_compra, :p_venta, :stock, :stock_min,
                 :receta, :favorito, TRUE)
            RETURNING id
        ");
        $stmt->execute([
            ':codigo'    => trim($data['codigo']),
            ':nombre'    => trim($data['nombre']),
            ':cat'       => $data['categoria_id']  ?: null,
            ':lab'       => trim($data['laboratorio']  ?? ''),
            ':pres'      => trim($data['presentacion'] ?? ''),
            ':p_compra'  => floatval($data['precio_compra'] ?? 0),
            ':p_venta'   => floatval($data['precio_venta']),
            ':stock'     => intval($data['stock'] ?? 0),
            ':stock_min' => intval($data['stock_minimo'] ?? 5),
            ':receta'    => isset($data['requiere_receta']) && $data['requiere_receta'] ? 'TRUE' : 'FALSE',
            ':favorito'  => isset($data['favorito']) && $data['favorito'] ? 'TRUE' : 'FALSE',
        ]);
        $id = $stmt->fetch()['id'];
        jsonResponse(['error' => false, 'message' => 'Producto creado correctamente', 'id' => $id]);

    // ---- POST: Actualizar producto ----
    case 'actualizar':
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id || empty($data['nombre']) || empty($data['codigo']) || !isset($data['precio_venta'])) {
            jsonResponse(['error' => true, 'message' => 'Datos inválidos'], 400);
        }

        // Verificar código único (excluyendo el producto actual)
        $check = $db->prepare("SELECT id FROM productos WHERE codigo = :codigo AND id != :id");
        $check->execute([':codigo' => trim($data['codigo']), ':id' => $id]);
        if ($check->fetch()) {
            jsonResponse(['error' => true, 'message' => 'Ya existe otro producto con ese código'], 409);
        }

        $stmt = $db->prepare("
            UPDATE productos SET
                codigo       = :codigo,
                nombre       = :nombre,
                categoria_id = :cat,
                laboratorio  = :lab,
                presentacion = :pres,
                precio_compra  = :p_compra,
                precio_venta   = :p_venta,
                stock_minimo   = :stock_min,
                requiere_receta = :receta,
                favorito       = :favorito,
                updated_at     = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':id'        => $id,
            ':codigo'    => trim($data['codigo']),
            ':nombre'    => trim($data['nombre']),
            ':cat'       => $data['categoria_id']  ?: null,
            ':lab'       => trim($data['laboratorio']  ?? ''),
            ':pres'      => trim($data['presentacion'] ?? ''),
            ':p_compra'  => floatval($data['precio_compra'] ?? 0),
            ':p_venta'   => floatval($data['precio_venta']),
            ':stock_min' => intval($data['stock_minimo'] ?? 5),
            ':receta'    => isset($data['requiere_receta']) && $data['requiere_receta'] ? 'TRUE' : 'FALSE',
            ':favorito'  => isset($data['favorito']) && $data['favorito'] ? 'TRUE' : 'FALSE',
        ]);
        jsonResponse(['error' => false, 'message' => 'Producto actualizado correctamente']);

    // ---- POST: Ajustar stock ----
    case 'ajustar_stock':
        $data     = json_decode(file_get_contents('php://input'), true);
        $id       = intval($data['id'] ?? 0);
        $tipo     = $data['tipo'] ?? '';       // 'entrada' | 'salida'
        $cantidad = intval($data['cantidad'] ?? 0);

        if (!$id || !in_array($tipo, ['entrada', 'salida']) || $cantidad <= 0) {
            jsonResponse(['error' => true, 'message' => 'Datos inválidos'], 400);
        }

        // Verificar stock suficiente para salida
        if ($tipo === 'salida') {
            $prod = $db->prepare("SELECT stock, nombre FROM productos WHERE id = :id");
            $prod->execute([':id' => $id]);
            $row = $prod->fetch();
            if (!$row) jsonResponse(['error' => true, 'message' => 'Producto no encontrado'], 404);
            if ($row['stock'] < $cantidad) {
                jsonResponse(['error' => true, 'message' => "Stock insuficiente. Disponible: {$row['stock']}"], 422);
            }
        }

        $delta = $tipo === 'entrada' ? $cantidad : -$cantidad;
        $stmt  = $db->prepare("UPDATE productos SET stock = stock + :delta, updated_at = NOW() WHERE id = :id RETURNING stock");
        $stmt->execute([':delta' => $delta, ':id' => $id]);
        $nuevo_stock = $stmt->fetch()['stock'];

        jsonResponse(['error' => false, 'message' => 'Stock ajustado correctamente', 'nuevo_stock' => $nuevo_stock]);

    // ---- POST: Activar / desactivar producto ----
    case 'toggle_activo':
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id) jsonResponse(['error' => true, 'message' => 'ID inválido'], 400);

        $stmt = $db->prepare("UPDATE productos SET activo = NOT activo, updated_at = NOW() WHERE id = :id RETURNING activo");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        jsonResponse(['error' => false, 'activo' => $row['activo']]);

    default:
        jsonResponse(['error' => true, 'message' => 'Acción no válida'], 404);
}
