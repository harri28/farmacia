<?php
require_once '../../config/database.php';
requireAuth(['admin', 'gerente', 'cajero']);

$id = intval($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); exit('ID requerido'); }

$db = getDB();

$stmt = $db->prepare("
    SELECT oc.*, p.razon_social AS proveedor, p.ruc AS prov_ruc, p.telefono AS prov_tel,
           p.email AS prov_email, p.direccion AS prov_direccion
    FROM ordenes_compra oc
    LEFT JOIN proveedores p ON p.id = oc.proveedor_id
    WHERE oc.id = :id
");
$stmt->execute([':id' => $id]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$orden) { http_response_code(404); exit('Orden no encontrada'); }

$items_stmt = $db->prepare("
    SELECT d.*, pr.nombre AS producto_nombre, pr.codigo AS producto_codigo
    FROM orden_compra_detalles d
    LEFT JOIN productos pr ON pr.id = d.producto_id
    WHERE d.orden_id = :id
    ORDER BY d.id
");
$items_stmt->execute([':id' => $id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// search_path includes public, so public.sucursales is accessible
$suc_stmt = $db->prepare("SELECT nombre, direccion, telefono FROM public.sucursales WHERE id = :id LIMIT 1");
$suc_stmt->execute([':id' => $_SESSION['sucursal_id']]);
$suc = $suc_stmt->fetch(PDO::FETCH_ASSOC) ?: ['nombre' => '', 'direccion' => '', 'telefono' => ''];

$tipo_pago_label = [
    'efectivo' => 'Efectivo', 'credito' => 'Crédito', 'transferencia' => 'Transferencia',
][$orden['tipo_pago']] ?? $orden['tipo_pago'];

$fecha = $orden['fecha_emision'] ?? $orden['created_at'] ?? date('Y-m-d');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Orden de Compra <?= htmlspecialchars($orden['numero_orden']) ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 12px; color: #111; background: #fff; padding: 32px; }
  h1 { font-size: 20px; font-weight: 700; }
  h2 { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
  .header { display: flex; justify-content: space-between; margin-bottom: 28px; }
  .header-left p { color: #555; margin-top: 2px; }
  .header-right { text-align: right; }
  .header-right .doc-title { font-size: 18px; font-weight: 700; color: #1d4ed8; }
  .header-right .doc-num { font-size: 13px; color: #444; }
  .header-right .doc-date { font-size: 11px; color: #888; margin-top: 4px; }
  hr { border: none; border-top: 1px solid #e5e7eb; margin: 16px 0; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
  .info-box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 14px; }
  .info-box p { margin-top: 4px; color: #444; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  thead th { background: #f3f4f6; padding: 9px 12px; font-size: 11px; text-transform: uppercase;
             letter-spacing: .03em; font-weight: 700; text-align: left; border-bottom: 1.5px solid #d1d5db; }
  tbody td { padding: 9px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
  tbody tr:last-child td { border-bottom: none; }
  .text-right { text-align: right; }
  .totals { margin-left: auto; width: 260px; margin-top: 4px; }
  .totals table { margin-bottom: 0; }
  .totals table td { padding: 5px 10px; border: none; }
  .totals table .total-final td { font-weight: 700; font-size: 13px; border-top: 1.5px solid #111; }
  .obs-box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 14px; margin-top: 16px; }
  .footer-sign { margin-top: 48px; display: flex; justify-content: space-around; }
  .sign-box { text-align: center; width: 180px; }
  .sign-line { border-top: 1px solid #111; margin-bottom: 6px; }
  .sign-label { font-size: 11px; color: #555; }
  @media print {
    body { padding: 16px; }
    .no-print { display: none; }
  }
</style>
</head>
<body onload="window.print()">

<div class="no-print" style="margin-bottom:16px">
  <button onclick="window.print()" style="padding:8px 18px;background:#1d4ed8;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px">
    🖨 Imprimir
  </button>
</div>

<div class="header">
  <div class="header-left">
    <h1><?= htmlspecialchars($suc['nombre']) ?></h1>
    <?php if ($suc['direccion']): ?>
    <p><?= htmlspecialchars($suc['direccion']) ?></p>
    <?php endif; ?>
    <?php if ($suc['telefono']): ?>
    <p>Tel: <?= htmlspecialchars($suc['telefono']) ?></p>
    <?php endif; ?>
  </div>
  <div class="header-right">
    <div class="doc-title">ORDEN DE COMPRA</div>
    <div class="doc-num"><?= htmlspecialchars($orden['numero_orden']) ?></div>
    <div class="doc-date">Fecha: <?= date('d/m/Y', strtotime($fecha)) ?></div>
    <?php if ($orden['fecha_entrega']): ?>
    <div class="doc-date">Entrega esperada: <?= date('d/m/Y', strtotime($orden['fecha_entrega'])) ?></div>
    <?php endif; ?>
  </div>
</div>

<hr>

<div class="info-grid">
  <div class="info-box">
    <h2>Proveedor</h2>
    <p><strong><?= htmlspecialchars($orden['proveedor'] ?? '—') ?></strong></p>
    <?php if ($orden['prov_ruc']): ?><p>RUC: <?= htmlspecialchars($orden['prov_ruc']) ?></p><?php endif; ?>
    <?php if ($orden['prov_direccion']): ?><p><?= htmlspecialchars($orden['prov_direccion']) ?></p><?php endif; ?>
    <?php if ($orden['prov_tel']): ?><p>Tel: <?= htmlspecialchars($orden['prov_tel']) ?></p><?php endif; ?>
    <?php if ($orden['prov_email']): ?><p><?= htmlspecialchars($orden['prov_email']) ?></p><?php endif; ?>
  </div>
  <div class="info-box">
    <h2>Condiciones</h2>
    <p>Tipo de pago: <strong><?= htmlspecialchars($tipo_pago_label) ?></strong></p>
    <?php if ($orden['tipo_pago'] === 'credito' && $orden['dias_credito']): ?>
    <p>Días de crédito: <strong><?= intval($orden['dias_credito']) ?></strong></p>
    <?php endif; ?>
    <p>IGV incluido: <strong><?= ($orden['con_igv'] == 't' || $orden['con_igv'] === true || $orden['con_igv'] == 1) ? 'Sí' : 'No' ?></strong></p>
    <p>Estado: <strong><?= htmlspecialchars($orden['estado'] ?? '—') ?></strong></p>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th style="width:4%">#</th>
      <th style="width:20%">Código</th>
      <th style="width:38%">Descripción</th>
      <th style="width:10%">U.M.</th>
      <th class="text-right" style="width:13%">Cantidad</th>
      <th class="text-right" style="width:15%">P. Unitario</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($items)): ?>
    <tr><td colspan="6" style="text-align:center;color:#888;padding:20px">Sin ítems</td></tr>
    <?php else: foreach ($items as $i => $it): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= htmlspecialchars($it['producto_codigo'] ?? '—') ?></td>
      <td><?= htmlspecialchars($it['producto_nombre'] ?? $it['descripcion'] ?? '—') ?>
          <?php if ($it['producto_nombre'] && $it['descripcion']): ?>
          <br><span style="color:#666;font-size:11px"><?= htmlspecialchars($it['descripcion']) ?></span>
          <?php endif; ?>
      </td>
      <td><?= htmlspecialchars($it['unidad_medida'] ?? '—') ?></td>
      <td class="text-right"><?= intval($it['cantidad']) ?></td>
      <td class="text-right"><?= $it['precio_unitario'] > 0 ? 'S/ ' . number_format(floatval($it['precio_unitario']), 2) : '—' ?></td>
    </tr>
    <?php endforeach; endif; ?>
  </tbody>
</table>

<?php if ($orden['subtotal'] > 0): ?>
<div class="totals">
  <table>
    <tr><td>Subtotal:</td><td class="text-right">S/ <?= number_format(floatval($orden['subtotal']), 2) ?></td></tr>
    <?php if ($orden['con_igv'] == 't' || $orden['con_igv'] === true || $orden['con_igv'] == 1): ?>
    <tr><td>IGV (18%):</td><td class="text-right">S/ <?= number_format(floatval($orden['igv']), 2) ?></td></tr>
    <?php endif; ?>
    <?php if (floatval($orden['costo_envio'] ?? 0) > 0): ?>
    <tr><td>Envío:</td><td class="text-right">S/ <?= number_format(floatval($orden['costo_envio']), 2) ?></td></tr>
    <?php endif; ?>
    <tr class="total-final"><td>Total:</td><td class="text-right">S/ <?= number_format(floatval($orden['total']), 2) ?></td></tr>
  </table>
</div>
<?php endif; ?>

<?php if (!empty($orden['observaciones'])): ?>
<div class="obs-box">
  <h2>Observaciones</h2>
  <p style="margin-top:6px"><?= nl2br(htmlspecialchars($orden['observaciones'])) ?></p>
</div>
<?php endif; ?>

<div class="footer-sign">
  <div class="sign-box">
    <div class="sign-line"></div>
    <div class="sign-label">Firma del solicitante</div>
  </div>
  <div class="sign-box">
    <div class="sign-line"></div>
    <div class="sign-label">Firma de autorización</div>
  </div>
  <div class="sign-box">
    <div class="sign-line"></div>
    <div class="sign-label">Proveedor</div>
  </div>
</div>

</body>
</html>
