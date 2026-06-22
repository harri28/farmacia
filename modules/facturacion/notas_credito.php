<?php
// Redirige a la pestaña de Notas de crédito dentro del módulo unificado
$origen = isset($_GET['origen']) ? '&origen=' . urlencode($_GET['origen']) : '';
header('Location: index.php?tab=notas' . $origen);
exit;
