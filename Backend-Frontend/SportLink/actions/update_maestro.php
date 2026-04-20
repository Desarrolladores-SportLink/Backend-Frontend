<?php
/**
 * fnProfileMgmt - Actualiza el perfil del entrenador (maestro)
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('maestro');
require __DIR__ . '/../config/conexion.php';

$id           = current_user_id();
$especialidad = trim($_POST['especialidad'] ?? '');
$experiencia  = trim($_POST['experiencia']  ?? '') ?: null;
$precio       = ($_POST['precio'] ?? '') !== '' ? (float)$_POST['precio'] : null;
$ubicacion    = trim($_POST['ubicacion']    ?? '') ?: null;
$telefono     = trim($_POST['telefono']     ?? '') ?: null;
$descripcion  = trim($_POST['descripcion']  ?? '') ?: null;
$dias         = !empty($_POST['dias_arr']) ? implode(',', array_map('trim', $_POST['dias_arr'])) : null;

$ok = pg_query_params(
    $conexion,
    "UPDATE maestro
        SET especialidad = $1,
            experiencia  = $2,
            precio       = $3,
            ubicacion    = $4,
            telefono     = $5,
            descripcion  = $6,
            dias         = $7
      WHERE id_usuario = $8",
    [$especialidad, $experiencia, $precio, $ubicacion, $telefono, $descripcion, $dias, $id]
);

$_SESSION['mensaje_perfil'] = $ok ? 'Tu perfil se actualizo correctamente.' : 'Error al guardar los datos.';
header('Location: ' . (base_url() . '/views/maestro.php'));
exit();
