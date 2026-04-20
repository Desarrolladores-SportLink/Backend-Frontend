<?php
/**
 * fnFavoritesMgmt (alta) - Guarda un proveedor en favoritos del alumno
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('alumno');
require __DIR__ . '/../config/conexion.php';

$id_proveedor = (int)($_POST['id_proveedor'] ?? 0);
$tipo         = $_POST['tipo'] ?? '';
$id_alumno    = current_user_id();

if ($id_proveedor <= 0 || !in_array($tipo, ['maestro','escuela'], true)) {
    $_SESSION['error'] = 'Datos invalidos.';
    redirect_to('/views/buscar.php');
}

pg_query_params(
    $conexion,
    "INSERT INTO favorito (id_alumno, id_proveedor, tipo) VALUES ($1,$2,$3)
     ON CONFLICT (id_alumno, id_proveedor) DO NOTHING",
    [$id_alumno, $id_proveedor, $tipo]
);

$_SESSION['mensaje'] = 'Agregado a tus favoritos.';
$ref = $_SERVER['HTTP_REFERER'] ?? (base_url().'/views/buscar.php');
header('Location: ' . $ref);
exit();
