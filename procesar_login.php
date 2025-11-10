<?php
// procesar_login.php

session_start(); // 1. Iniciar la sesión
require 'db.php'; // 2. Conexión a la BD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // 3. Buscar al usuario por correo
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();

    // 4. Verificar si el usuario existe y la contraseña es correcta
    if ($usuario && password_verify($contrasena, $usuario['contrHash'])) {
        
        // 5. Si todo es correcto, guardar datos en la sesión
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        
        // 6. Redirigir a la página principal
        header("Location: principal.php");
        exit;

    } else {
        // 7. Si hay un error, redirigir de vuelta al login con un mensaje
        header("Location: index.html?error=Credenciales incorrectas. Inténtalo de nuevo.");
        exit;
    }
}
?>