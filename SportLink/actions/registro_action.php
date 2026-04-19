<?php
include("../config/conexion.php");

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$password = $_POST['password'];
$rol = $_POST['rol'];

// Insertar en tabla usuario
$query = "INSERT INTO usuario (nombre, correo, password, rol) 
          VALUES ($1, $2, $3, $4) RETURNING id_usuario";

$result = pg_query_params($conexion, $query, array($nombre, $correo, $password, $rol));

if ($row = pg_fetch_assoc($result)) {

    $id_usuario = $row['id_usuario'];

    // Insertar según rol
    if ($rol == 'alumno') {
        pg_query($conexion, "INSERT INTO alumno (id_usuario) VALUES ($id_usuario)");
    }

    if ($rol == 'maestro') {
        pg_query($conexion, "INSERT INTO maestro (id_usuario) VALUES ($id_usuario)");
    }

    if ($rol == 'escuela') {
        pg_query($conexion, "INSERT INTO escuela (id_usuario) VALUES ($id_usuario)");
    }

    echo "Registro exitoso 🎉";
    echo "<br><a href='../login.php'>Ir a login</a>";

} else {
    echo "Error al registrar";
}
?>