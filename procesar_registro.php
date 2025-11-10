<?php

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST['nombre'];
    $apellido1 = $_POST['apellido1'];
    $apellido2 = $_POST['apellido2'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];


    if ($contrasena !== $confirmar_contrasena) {
        die("Error: Las contraseñas no coinciden.");
    }

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ?");
    $stmt->execute([$correo]);
    if ($stmt->fetch()) {
        die("Error: El correo electrónico ya está registrado.");
    }

    $contrHash = password_hash($contrasena, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO usuario (nombre, apellido1, apellido2, correo, contrHash) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([$nombre, $apellido1, $apellido2, $correo, $contrHash]);

        header("Location: index.html?mensaje=¡Registro exitoso! Ya puedes iniciar sesión.");
        exit;

    } catch (PDOException $e) {
        die("Error al registrar el usuario: ". $e->getMessage());
    }
}
?>