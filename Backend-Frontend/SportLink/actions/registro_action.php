<?php
/**
 * fnUserRegister - Crea una cuenta nueva (alumno / maestro / escuela)
 * Usa BCRYPT para encriptar la contrasena.
 */
session_start();
require __DIR__ . '/../config/conexion.php';

$nombre    = trim($_POST['nombre']    ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$username  = trim($_POST['username']  ?? '');
$correo    = trim($_POST['correo']    ?? '');
$rol       = $_POST['rol']            ?? '';

if ($nombre === '' || $apellidos === '' || $username === '' || $correo === '' || $rol === '') {
    $_SESSION['mensaje'] = 'Todos los campos basicos son obligatorios.';
    header('Location: ../registro.php');
    exit();
}
if (!in_array($rol, ['alumno','maestro','escuela'], true)) {
    $_SESSION['mensaje'] = 'Rol invalido.';
    header('Location: ../registro.php');
    exit();
}

$password_encriptada = password_hash($_POST['password'], PASSWORD_BCRYPT);

$result_usuario = pg_query_params(
    $conexion,
    "INSERT INTO usuario (nombre, apellidos, username, correo, password, rol)
     VALUES ($1,$2,$3,$4,$5,$6) RETURNING id_usuario",
    [$nombre, $apellidos, $username, $correo, $password_encriptada, $rol]
);

if (!$result_usuario || !($row = pg_fetch_assoc($result_usuario))) {
    $_SESSION['mensaje'] = 'No se pudo crear la cuenta. El usuario o correo podrian estar en uso.';
    header('Location: ../registro.php');
    exit();
}

$id_usuario = (int)$row['id_usuario'];

if ($rol === 'alumno') {
    pg_query_params(
        $conexion,
        "INSERT INTO alumno (id_usuario, codigo_estudiante, edad) VALUES ($1,$2,$3)",
        [$id_usuario,
         trim($_POST['codigo_estudiante'] ?? '') ?: null,
         ($_POST['edad'] ?? '') !== '' ? (int)$_POST['edad'] : null]
    );
} elseif ($rol === 'maestro') {
    pg_query_params(
        $conexion,
        "INSERT INTO maestro (id_usuario, especialidad, telefono) VALUES ($1,$2,$3)",
        [$id_usuario,
         trim($_POST['deporte']  ?? '') ?: null,
         trim($_POST['telefono'] ?? '') ?: null]
    );
} elseif ($rol === 'escuela') {
    pg_query_params(
        $conexion,
        "INSERT INTO escuela (id_usuario, nombre_escuela, deporte, telefono) VALUES ($1,$2,$3,$4)",
        [$id_usuario,
         trim($_POST['nombre_escuela'] ?? '') ?: ($nombre.' '.$apellidos),
         trim($_POST['deporte']  ?? '') ?: null,
         trim($_POST['telefono'] ?? '') ?: null]
    );
}

$_SESSION['mensaje'] = 'Cuenta creada exitosamente. Inicia sesion!';
header('Location: ../login.php');
exit();
