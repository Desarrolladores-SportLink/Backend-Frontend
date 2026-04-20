<?php
session_start();
include("../config/conexion.php");

// Seguridad: Si no hay sesión activa o el rol no es alumno, lo regresamos al login
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Consultamos los datos específicos del alumno uniendo la tabla usuario y alumno (JOIN)
$query = "SELECT u.nombre, u.apellidos, u.correo, u.username, a.codigo_estudiante, a.edad, a.telefono 
          FROM usuario u 
          INNER JOIN alumno a ON u.id_usuario = a.id_usuario 
          WHERE u.id_usuario = $1";

$result = pg_query_params($conexion, $query, array($id_usuario));
$datos_alumno = pg_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Alumno | SportLink</title>
    <link rel="stylesheet" href="../styles/view.css">
    <style>
        /* Estilos rápidos para el panel */
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; color: #333; margin: 0; }
        .dashboard { max-width: 800px; margin: 50px auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .logout-btn { background-color: #ff4d4d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; float: right; }
    </style>
</head>
<body>

    <div class="dashboard">
        <a href="../actions/logout.php" class="logout-btn">Cerrar Sesión</a>
        
        <h2>¡Hola, <?php echo htmlspecialchars($datos_alumno['nombre']); ?>! 🏃‍♂️</h2>
        <p>Bienvenido a tu panel de SportLink. Aquí están los datos de tu perfil:</p>

        <hr>

        <ul>
            <li><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($datos_alumno['nombre'] . ' ' . $datos_alumno['apellidos']); ?></li>
            <li><strong>Usuario:</strong> @<?php echo htmlspecialchars($datos_alumno['username']); ?></li>
            <li><strong>Correo:</strong> <?php echo htmlspecialchars($datos_alumno['correo']); ?></li>
            <li><strong>Código de Estudiante:</strong> <?php echo htmlspecialchars($datos_alumno['codigo_estudiante'] ?? 'No registrado'); ?></li>
            <li><strong>Edad:</strong> <?php echo htmlspecialchars($datos_alumno['edad'] ?? 'No registrada'); ?> años</li>
            <li><strong>Teléfono:</strong> <?php echo htmlspecialchars($datos_alumno['telefono'] ?? 'No registrado'); ?></li>
        </ul>

        <hr>
        
        <button style="padding: 10px; background-color: #ff7a00; color: white; border: none; border-radius: 5px;">Editar Mi Perfil</button>
    </div>

</body>
</html>