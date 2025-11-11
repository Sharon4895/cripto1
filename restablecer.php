<?php
// restablecer.php
// Página que recibe token por GET y muestra el formulario para ingresar nueva contraseña.
require 'db.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
if (!$token) {
    header('Location: index.html?error=Token+inválido');
    exit;
}

// Buscar token válido
$stmt = $pdo->prepare('SELECT pr.id, pr.user_id, pr.expires_at, u.correo FROM password_resets pr JOIN usuario u ON pr.user_id = u.id WHERE pr.token = ?');
$stmt->execute([$token]);
$row = $stmt->fetch();

if (!$row) {
    header('Location: index.html?error=Token+no+válido+o+expirado');
    exit;
}

$expires = new DateTime($row['expires_at']);
if (new DateTime() > $expires) {
    // Token expirado: eliminar y notificar
    $del = $pdo->prepare('DELETE FROM password_resets WHERE id = ?');
    $del->execute([$row['id']]);
    header('Location: index.html?error=Token+expirado');
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Restablecer contraseña</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;background:#f0f2f5;display:grid;place-items:center;min-height:100vh;margin:0}
        .card{background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,.08);width:420px}
        .input{width:100%;padding:.6rem;margin-bottom:1rem;border:1px solid #ddd;border-radius:4px}
        .btn{display:inline-block;padding:.6rem 1rem;background:#007bff;color:#fff;border-radius:4px;border:none}
    </style>
</head>
<body>
    <div class="card">
        <h2>Restablecer contraseña</h2>
        <p>Introduce una nueva contraseña para la cuenta: <strong><?php echo htmlspecialchars($row['correo']); ?></strong></p>
        <form action="procesar_restablecer.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <input class="input" type="password" name="contrasena" placeholder="Nueva contraseña" required>
            <input class="input" type="password" name="confirmar_contrasena" placeholder="Confirmar contraseña" required>
            <button class="btn" type="submit">Restablecer contraseña</button>
        </form>
        <p style="margin-top:1rem"><a href="index.html">Volver al login</a></p>
    </div>
</body>
</html>
