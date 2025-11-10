<?php
// 1. Iniciar la sesión
session_start();

// 2. Verificar si el usuario está logueado
// Si 'user_id' no existe en la sesión, significa que no ha iniciado sesión.
if (!isset($_SESSION['user_id'])) {
    // 3. Redirigir al login
    header("Location: login.html");
    exit; // Asegurarse de que el script se detenga después de redirigir
}

// 4. Si llegamos aquí, el usuario está logueado.
// Podemos recuperar su nombre de la sesión para saludarlo.
$nombre_usuario = $_SESSION['nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            display: grid;
            place-items: center;
            min-height: 100vh;
            margin: 0;
            text-align: center;
        }

        .welcome-container {
            background-color: #ffffff;
            padding: 3rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 500px;
        }

        .welcome-container h2 {
            color: #333;
            margin-bottom: 1rem;
        }

        .welcome-container p {
            color: #555;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .logout-button {
            display: inline-block; /* Para que ocupe el ancho del texto */
            margin-top: 2rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            background-color: #dc3545; /* Color rojo para "cerrar sesión" */
            color: white;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none; /* Quitar subrayado del enlace */
            transition: background-color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

    <div class="welcome-container">
        <h2>¡Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?>!</h2>
        
        <p>Has iniciado sesión correctamente en tu aplicación.</p>
        <p>Este contenido solo puede ser visto por usuarios autenticados.</p>
        
        <a href="logout.php" class="logout-button">Cerrar Sesión</a>
    </div>

</body>
</html>