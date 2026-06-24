<?php

$host = strtolower(
    preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '')
);

if ($host === 'admin.genpharma.cloud') {
    header('Location: /modules/superadmin/login.php');
    exit;
}

header('Location: /modules/auth/login.php');
exit;
