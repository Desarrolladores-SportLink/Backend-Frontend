<?php
session_start();
include("../config/conexion.php");

$correo= $_POST ['correo'];
$password= $_POST ['password'];

//buscar usuario
$query= "SELECT * FROM usuario WHERE correo= $1";
$result = pg_query_params($conexion, $query, array($correo));

if ($row = pg_fetch_assoc($result)) { 
    if ($password == $row['password']) { 

        // Guardar sesión
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['rol'] = $row['rol'];

        // Redirigir según rol
        if ($row['rol'] == 'alumno') {
            header("Location: ../views/alumno.php");
        } elseif ($row['rol'] == 'maestro') {
            header("Location: ../views/maestro.php");
        } elseif ($row['rol'] == 'escuela') {
            header("Location: ../views/escuela.php");
        }

    }
    else {
        echo "Contraseña incorrecta";
    }
}
else {
    echo "Usuario no encontrado";
}


?>