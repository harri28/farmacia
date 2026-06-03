<?php
// Usage: php create_superadmin.php [username] [password]
require __DIR__ . '/../config/database.php';
$username = $argv[1] ?? 'tempadmin';
$password = $argv[2] ?? 'TempPass123!';
$nombre = 'Super';
 $apellido = 'Admin';
try {
    $db = getDB();
    // check existing
    $stmt = $db->prepare('SELECT id FROM public.superadmins WHERE username = :u');
    $stmt->execute([':u' => $username]);
    if ($stmt->fetch()) {
        echo "User '$username' already exists.\n";
        exit(0);
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins = $db->prepare('INSERT INTO public.superadmins (nombre, apellido, username, password_hash, email, activo) VALUES (:n, :a, :u, :p, :e, TRUE) RETURNING id');
    $ins->execute([':n' => $nombre, ':a' => $apellido, ':u' => $username, ':p' => $hash, ':e' => null]);
    $id = $ins->fetchColumn();
    if ($id) {
        echo "Created superadmin: username=$username password=$password\n";
        echo "ID: $id\n";
    } else {
        echo "Insert failed.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
