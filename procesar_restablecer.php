<?php
// procesar_restablecer.php
// Maneja el POST desde restablecer.php para actualizar la contraseña según token.
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$token = isset($_POST['token']) ? $_POST['token'] : '';
$contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';
$confirm = isset($_POST['confirmar_contrasena']) ? $_POST['confirmar_contrasena'] : '';

if (!$token || !$contrasena || !$confirm) {
    header('Location: index.html?error=Datos+incompletos');
    exit;
}

if ($contrasena !== $confirm) {
    header('Location: restablecer.php?token=' . urlencode($token) . '&error=Las+contraseñas+no+coinciden');
    exit;
}

// Buscar token válido
$stmt = $pdo->prepare('SELECT pr.id, pr.user_id, pr.expires_at FROM password_resets pr WHERE pr.token = ?');
$stmt->execute([$token]);
$row = $stmt->fetch();

if (!$row) {
    header('Location: index.html?error=Token+no+válido');
    exit;
}

$expires = new DateTime($row['expires_at']);
if (new DateTime() > $expires) {
    // Token expirado, eliminar y notificar
    $del = $pdo->prepare('DELETE FROM password_resets WHERE id = ?');
    $del->execute([$row['id']]);
    header('Location: index.html?error=Token+expirado');
    exit;
}

// Actualizar contraseña del usuario
$hash = password_hash($contrasena, PASSWORD_DEFAULT);
$upd = $pdo->prepare('UPDATE usuario SET contrHash = ? WHERE id = ?');
$upd->execute([$hash, $row['user_id']]);

// Eliminar todos los tokens existentes para este usuario
$del = $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?');
$del->execute([$row['user_id']]);

header('Location: index.html?mensaje=Contraseña+actualizada+con+éxito.+Ahora+puedes+iniciar+sesión.');
exit;

?>
