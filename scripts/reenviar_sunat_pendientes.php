<?php
// ============================================================
// ARCHIVO: farmacia/scripts/reenviar_sunat_pendientes.php
// DESCRIPCION: Worker que reintenta enviar a SUNAT los comprobantes
//              que no obtuvieron CDR en el intento inicial.
//
//              Diseno:
//                - Se corre via cron del VPS cada 5 minutos.
//                - Itera por todos los esquemas sucursal activos.
//                - Busca comprobantes_electronicos con enlace_del_cdr vacio
//                  y reenvio_proximo_at <= NOW() y reenvio_intentos < 20.
//                - Para cada uno, intenta enviar_sunat() de nuevo.
//                - Si acepta -> enlace_del_cdr queda poblado por enviar_sunat,
//                  el comprobante ya no vuelve a ser candidato.
//                - Si falla -> incrementa reenvio_intentos, programa el proximo
//                  intento a NOW() + 5 min.
//                - Al llegar al tope (20 intentos) -> reenvio_proximo_at = NULL
//                  (ya no se vuelve a tomar automaticamente; requiere accion
//                  manual con el boton "Reenviar a SUNAT").
//                - Lock por archivo con flock() para evitar que dos corridas
//                  se superpongan (puede pasar si una corrida tarda mas de 5
//                  min debido a timeouts de SUNAT de 60s cada uno).
//
// USO (manual): php scripts/reenviar_sunat_pendientes.php
// USO (cron en VPS):
//   */5 * * * * /usr/bin/php /var/www/farmacia/scripts/reenviar_sunat_pendientes.php >> /var/log/farmacia/reenviar_sunat.log 2>&1
// ============================================================

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Solo CLI\n");
}

date_default_timezone_set('America/Lima');

$MAX_INTENTOS   = 20;
$INTERVALO_MIN  = 5;   // minutos entre reintentos

// --- Lock de ejecucion ---
$lockFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'farmacia_reenviar_sunat.lock';
$lockFp   = fopen($lockFile, 'c');
if (!$lockFp || !flock($lockFp, LOCK_EX | LOCK_NB)) {
    _log('Otra corrida sigue viva, salgo sin hacer nada.');
    exit(0);
}

// --- Deps ---
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/sunat.php';

// La misma logica de "cargar comprobante / cliente / items" vive en el
// api de facturacion; se incluye para reutilizar los helpers.
require_once __DIR__ . '/../modules/facturacion/_sunat_resend_helpers.php';

$pdo = getDB();

// --- Iterar sucursales activas ---
$sucursales = $pdo->query("
    SELECT id, tenant_id, nombre, schema_name
    FROM public.sucursales
    WHERE COALESCE(activo, TRUE) = TRUE
    ORDER BY tenant_id, id
")->fetchAll();

$totales = ['sucursales' => 0, 'procesados' => 0, 'exitosos' => 0, 'fallidos' => 0, 'definitivos' => 0];

foreach ($sucursales as $suc) {
    $schema = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $suc['schema_name']));
    if ($schema === '') continue;

    // Setear contexto para enviar_sunat (lee $_SESSION['tenant_id'])
    $_SESSION['tenant_id']       = (int) $suc['tenant_id'];
    $_SESSION['sucursal_id']     = (int) $suc['id'];
    $_SESSION['sucursal_schema'] = $schema;

    try {
        $pdo->exec("SET search_path TO {$schema}, public");
    } catch (Throwable $e) {
        _log("[{$schema}] no se pudo SET search_path: " . $e->getMessage());
        continue;
    }

    // Candidatos: sin CDR, con reenvio_proximo_at vencido, bajo el tope
    try {
        $stmt = $pdo->prepare("
            SELECT id, numero_completo, reenvio_intentos
            FROM comprobantes_electronicos
            WHERE COALESCE(enlace_del_cdr, '') = ''
              AND reenvio_proximo_at IS NOT NULL
              AND reenvio_proximo_at <= NOW()
              AND COALESCE(reenvio_intentos, 0) < :tope
            ORDER BY reenvio_proximo_at ASC
            LIMIT 50
        ");
        $stmt->execute([':tope' => $MAX_INTENTOS]);
        $candidatos = $stmt->fetchAll();
    } catch (Throwable $e) {
        _log("[{$schema}] fallo query de candidatos: " . $e->getMessage());
        continue;
    }

    if (!$candidatos) continue;

    $totales['sucursales']++;
    _log("[{$schema}] " . count($candidatos) . " comprobante(s) a reintentar");

    foreach ($candidatos as $cand) {
        $compId  = (int) $cand['id'];
        $intento = (int) $cand['reenvio_intentos'] + 1;
        $totales['procesados']++;

        try {
            // Reutiliza la logica del endpoint reenviar_sunat
            $resultado = reenviarSunatComprobante($pdo, $compId);

            if (!empty($resultado['ok'])) {
                $totales['exitosos']++;
                _log("  [{$cand['numero_completo']}] OK en intento #{$intento}");
                // enviar_sunat ya seteo enlace_del_cdr; limpiar programacion
                $pdo->prepare("
                    UPDATE comprobantes_electronicos
                    SET reenvio_intentos    = :n,
                        reenvio_ultimo_at   = NOW(),
                        reenvio_proximo_at  = NULL,
                        reenvio_ultimo_error = NULL
                    WHERE id = :id
                ")->execute([':n' => $intento, ':id' => $compId]);
                continue;
            }

            // Fallo: evaluar si fue transitorio o permanente
            $responseCode = $resultado['response_code'] ?? null;
            $mensaje      = (string) ($resultado['mensaje'] ?? 'Error desconocido');

            if ($responseCode !== null && $responseCode !== '' && (string) $responseCode !== '0') {
                // Rechazo permanente por SUNAT (response_code != 0) --> no reintentar
                $pdo->prepare("
                    UPDATE comprobantes_electronicos
                    SET reenvio_intentos    = :n,
                        reenvio_ultimo_at   = NOW(),
                        reenvio_proximo_at  = NULL,
                        reenvio_ultimo_error = :err
                    WHERE id = :id
                ")->execute([
                    ':n' => $intento, ':id' => $compId,
                    ':err' => "SUNAT rechazo (code {$responseCode}): {$mensaje}",
                ]);
                $totales['definitivos']++;
                _log("  [{$cand['numero_completo']}] RECHAZO permanente code={$responseCode}");
                continue;
            }

            // Transitorio: reprogramar o abandonar si llega al tope
            if ($intento >= $MAX_INTENTOS) {
                $pdo->prepare("
                    UPDATE comprobantes_electronicos
                    SET reenvio_intentos    = :n,
                        reenvio_ultimo_at   = NOW(),
                        reenvio_proximo_at  = NULL,
                        reenvio_ultimo_error = :err
                    WHERE id = :id
                ")->execute([
                    ':n' => $intento, ':id' => $compId,
                    ':err' => "Max {$MAX_INTENTOS} intentos alcanzado. Ultimo error: {$mensaje}",
                ]);
                $totales['definitivos']++;
                _log("  [{$cand['numero_completo']}] AGOTADO despues de {$intento} intentos");
            } else {
                $pdo->prepare("
                    UPDATE comprobantes_electronicos
                    SET reenvio_intentos    = :n,
                        reenvio_ultimo_at   = NOW(),
                        reenvio_proximo_at  = NOW() + make_interval(mins => :min),
                        reenvio_ultimo_error = :err
                    WHERE id = :id
                ")->execute([
                    ':n' => $intento, ':min' => $INTERVALO_MIN,
                    ':err' => $mensaje, ':id' => $compId,
                ]);
                $totales['fallidos']++;
                _log("  [{$cand['numero_completo']}] fallo intento #{$intento}, reagendado en {$INTERVALO_MIN}min");
            }
        } catch (Throwable $e) {
            // Error inesperado: cuenta como transitorio y reagenda
            $msg = substr($e->getMessage(), 0, 500);
            $pdo->prepare("
                UPDATE comprobantes_electronicos
                SET reenvio_intentos    = :n,
                    reenvio_ultimo_at   = NOW(),
                    reenvio_proximo_at  = NOW() + make_interval(mins => :min),
                    reenvio_ultimo_error = :err
                WHERE id = :id
            ")->execute([
                ':n' => $intento, ':min' => $INTERVALO_MIN,
                ':err' => "Excepcion: {$msg}", ':id' => $compId,
            ]);
            $totales['fallidos']++;
            _log("  [{$cand['numero_completo']}] EXCEPCION: {$msg}");
        }
    }
}

_log(sprintf(
    'Resumen: %d sucursal(es) con pendientes | procesados=%d exitosos=%d fallidos=%d definitivos=%d',
    $totales['sucursales'], $totales['procesados'], $totales['exitosos'], $totales['fallidos'], $totales['definitivos']
));

flock($lockFp, LOCK_UN);
fclose($lockFp);
@unlink($lockFile);
exit(0);

// -----------------------------------------------------------
function _log(string $msg): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
}
