<?php
/**
 * fnProfileMgmt - Actualiza el perfil de la escuela
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('escuela');
require __DIR__ . '/../config/conexion.php';

$id                 = current_user_id();
$nombre_escuela     = trim($_POST['nombre_escuela']     ?? '');
$direccion          = trim($_POST['direccion']          ?? '');
$deporte            = trim($_POST['deporte']            ?? '') ?: null;
$deportes_ofrecidos = trim($_POST['deportes_ofrecidos'] ?? '') ?: null;
$mensualidad        = ($_POST['mensualidad'] ?? '') !== '' ? (float)$_POST['mensualidad'] : null;
$ubicacion          = trim($_POST['ubicacion']          ?? '') ?: null;
$telefono           = trim($_POST['telefono']           ?? '') ?: null;
$descripcion        = trim($_POST['descripcion']        ?? '') ?: null;
$dias               = !empty($_POST['dias_arr']) ? implode(',', array_map('trim', $_POST['dias_arr'])) : null;

// Manejo de redes social
$redes_post = $_POST['red_social'] ?? [];
$red_social = null;
if (is_array($redes_post)) {
    $redes_limpias = array_filter(array_map('trim', $redes_post));
    if (!empty($redes_limpias)) {
        $red_social = implode(',', $redes_limpias);
    }
}

$ok = pg_query_params(
    $conexion,
    "UPDATE escuela
        SET nombre_escuela     = $1,
            direccion          = $2,
            deporte            = $3,
            deportes_ofrecidos = $4,
            mensualidad        = $5,
            precio             = $5,
            ubicacion          = $6,
            telefono           = $7,
            descripcion        = $8,
            dias               = $9,
            red_social         = $10
      WHERE id_usuario = $11",
    [$nombre_escuela, $direccion, $deporte, $deportes_ofrecidos,
     $mensualidad, $ubicacion, $telefono, $descripcion, $dias, $red_social, $id]
);

$_SESSION['mensaje_escuela'] = $ok ? 'Datos de la escuela actualizados.' : 'Error al actualizar.';
header('Location: ' . (base_url() . '/views/escuela.php'));
exit();
