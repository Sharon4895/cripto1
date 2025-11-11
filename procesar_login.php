<?php
// procesar_login.php

session_start(); // 1. Iniciar la sesión
require 'db.php'; // 2. Conexión a la BD

// Detectar si la petición es AJAX (fetch) o normal
$isAjax = false;
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $isAjax = true;
}
if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
    $isAjax = true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $correo = isset($_POST['correo']) ? $_POST['correo'] : '';
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

    // 3. Buscar al usuario por correo
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();

    // 4. Verificar si el usuario existe y la contraseña es correcta
    if ($usuario && password_verify($contrasena, $usuario['contrHash'])) {
        
        // 5. Si todo es correcto, guardar datos en la sesión
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'message' => 'Login correcto']);
            exit;
        }

        // 6. Redirigir a la página principal
        header("Location: principal.php");
        exit;

    } else {
        // 7. Si hay un error, redirigir de vuelta al login con un mensaje
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas.']);
            exit;
        }

        header("Location: index.html?error=Credenciales incorrectas. Inténtalo de nuevo.");
        exit;
    }
}
?>