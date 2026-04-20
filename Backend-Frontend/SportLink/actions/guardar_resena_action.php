<?php
/**
 * fnReviewsSys - Crea o actualiza una resena de un alumno hacia un proveedor.
 * Restriccion: 1 resena por (alumno, proveedor) - upsert via ON CONFLICT.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('alumno');
require __DIR__ . '/../config/conexion.php';

$id_proveedor = (int)($_POST['id_proveedor'] ?? 0);
$tipo         = $_POST['tipo'] ?? '';
$cal          = (int)($_POST['calificacion'] ?? 0);
$comentario   = trim($_POST['comentario'] ?? '');
$id_alumno    = current_user_id();

if ($id_proveedor <= 0
    || !in_array($tipo, ['maestro','escuela'], true)
    || $cal < 1 || $cal > 5
    || $comentario === '') {
    $_SESSION['error'] = 'Datos de la resena invalidos.';
    $ref = $_SERVER['HTTP_REFERER'] ?? (base_url().'/views/buscar.php');
    header('Location: ' . $ref);
    exit();
}

pg_query_params(
    $conexion,
    "INSERT INTO resena (id_alumno, id_proveedor, tipo, calificacion, comentario)
     VALUES ($1,$2,$3,$4,$5)
     ON CONFLICT (id_alumno, id_proveedor)
     DO UPDATE SET calificacion = EXCLUDED.calificacion,
                   comentario   = EXCLUDED.comentario,
                   fecha        = CURRENT_TIMESTAMP",
    [$id_alumno, $id_proveedor, $tipo, $cal, $comentario]
);

$_SESSION['mensaje'] = 'Tu resena fue publicada.';
header('Location: ' . (base_url() . "/views/perfil_publico.php?id={$id_proveedor}&tipo={$tipo}"));
exit();
