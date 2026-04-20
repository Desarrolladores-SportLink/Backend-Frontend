<?php
/**
 * fnProfileMgmt - Actualiza el perfil del alumno
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('alumno');
require __DIR__ . '/../config/conexion.php';

$id              = current_user_id();
$codigo          = trim($_POST['codigo_estudiante'] ?? '') ?: null;
$edad            = ($_POST['edad'] ?? '') !== '' ? (int)$_POST['edad'] : null;
$telefono        = trim($_POST['telefono'] ?? '') ?: null;
$deporte_interes = trim($_POST['deporte_interes'] ?? '') ?: null;

$ok = pg_query_params(
    $conexion,
    "UPDATE alumno
        SET codigo_estudiante = $1,
            edad              = $2,
            telefono          = $3,
            deporte_interes   = $4
      WHERE id_usuario = $5",
    [$codigo, $edad, $telefono, $deporte_interes, $id]
);

$_SESSION['mensaje'] = $ok ? 'Perfil actualizado correctamente.' : 'Error al actualizar.';
header('Location: ' . (base_url() . '/views/alumno_perfil.php'));
exit();
