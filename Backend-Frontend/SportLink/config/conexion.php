<?php
$host = "localhost";
$port = "5432";
$dbname = "sportlink";
$user = "postgres";
$password = "Hissutto"; // la que usas en pgAdmin

$conexion = pg_connect("
    host=$host 
    port=$port 
    dbname=$dbname 
    user=$user 
    password=$password
");

if (!$conexion) {
    die("Error de conexión a la base de datos");
}

// echo "Conectado correctamente"; // solo para prueba
?>