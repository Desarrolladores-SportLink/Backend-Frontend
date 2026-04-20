<?php
session_start();
include("../config/conexion.php");

// Recibimos el username en lugar del correo
$username = trim($_POST['username']); 
$password = $_POST['password'];

// 1. Buscamos al usuario por su username
$query = "SELECT * FROM usuario WHERE username = $1";
$result = pg_query_params($conexion, $query, array($username));

if ($row = pg_fetch_assoc($result)) { 
    // 2. Verificamos la contraseña encriptada
    if (password_verify($password, $row['password'])) { 

        // 3. Credenciales correctas: Guardamos datos en sesión
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['nombre_completo'] = $row['nombre'] . " " . $row['apellidos'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['rol'] = $row['rol'];

        // 4. Redirigimos según rol
        if ($row['rol'] == 'alumno') {
            header("Location: ../views/alumno.php");
        } elseif ($row['rol'] == 'maestro') {
            header("Location: ../views/maestro.php");
        } elseif ($row['rol'] == 'escuela') {
            header("Location: ../views/escuela.php");
        }
        exit();

    } else {
        $_SESSION['mensaje'] = "Contraseña incorrecta.";
        header("Location: ../login.php");
        exit();
    }
} else {
    $_SESSION['mensaje'] = "El nombre de usuario no existe.";
    header("Location: ../login.php");
    exit();
}
?>