<?php
// procesar_recuperar.php
// Recibe POST { correo } y crea un token de recuperación. Intenta enviar email, si falla muestra token (entorno local).

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: recuperar_contrasena.html');
    exit;
}

$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
if ($correo === '') {
    header('Location: recuperar_contrasena.html?error=Introduce+tu+correo');
    exit;
}

// Buscar usuario
$stmt = $pdo->prepare('SELECT id, nombre FROM usuario WHERE correo = ?');
$stmt->execute([$correo]);
$user = $stmt->fetch();

// No revelar si el correo existe o no. Si existe, generar token.
if ($user) {
    $user_id = $user['id'];

    // Crear tabla password_resets si no existe (simple esquema)
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (token),
        INDEX (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Generar token seguro
    try {
        $token = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        // Fallback
        $token = bin2hex(openssl_random_pseudo_bytes(32));
    }

    $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

    // Guardar token en la BD
    $ins = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
    $ins->execute([$user_id, $token, $expiresAt]);

    // Preparar enlace
    $resetLink = sprintf("http://%s/cripto1/restablecer.php?token=%s", $_SERVER['HTTP_HOST'], $token);

    // Intentar enviar correo (nota: en XAMPP local mail() probablemente no esté configurado)
    $to = $correo;
    $subject = 'Recuperación de contraseña';
    $message = "Hola " . ($user['nombre'] ?? '') . ",\n\n" .
        "Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.\n" .
        "Haz clic en el siguiente enlace (o cópialo en tu navegador) para restablecer la contraseña:\n\n" .
        $resetLink . "\n\n" .
        "Este enlace expirará en 1 hora. Si no solicitaste este cambio, ignora este correo.\n\n" .
        "--\n";

    $headers = 'From: no-reply@' . $_SERVER['HTTP_HOST'] . "\r\n" .
               'Reply-To: no-reply@' . $_SERVER['HTTP_HOST'] . "\r\n" .
               'X-Mailer: PHP/' . phpversion();

    $mailSent = false;
    // Suppress warnings from mail() in local dev
    try {
        $mailSent = @mail($to, $subject, $message, $headers);
    } catch (Exception $e) {
        $mailSent = false;
    }

    if ($mailSent) {
        // Mensaje genérico para no filtrar existencia de correos
        header('Location: index.html?mensaje=Si+el+correo+existe,+se+han+enviado+instrucciones.');
        exit;
    } else {
        // En entorno local, mostrar el token/enlace para poder probar el flujo.
        // Página simple que muestra el enlace (NO recomendado en producción).
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>Instrucciones de recuperación</title>
            <style>body{font-family:Arial,Helvetica,sans-serif;background:#f0f2f5;display:grid;place-items:center;min-height:100vh;margin:0} .card{background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,.08);width:520px}</style>
        </head>
        <body>
            <div class="card">
                <h2>Enlace de recuperación (entorno local)</h2>
                <p>Como el servidor de correo no está configurado, aquí tienes el enlace para restablecer la contraseña:</p>
                <p><a href="<?php echo htmlspecialchars($resetLink); ?>"><?php echo htmlspecialchars($resetLink); ?></a></p>
                <p>El enlace expirará el <?php echo htmlspecialchars($expiresAt); ?>.</p>
                <p><a href="index.html">Volver al login</a></p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

} else {
    // No existe el usuario: redirigir con mensaje genérico
    header('Location: index.html?mensaje=Si+el+correo+existe,+se+han+enviado+instrucciones.');
    exit;
}

?>
