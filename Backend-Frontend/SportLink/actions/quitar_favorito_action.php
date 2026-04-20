<?php
/**
 * fnFavoritesMgmt (baja) - Elimina un favorito del alumno
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('alumno');
require __DIR__ . '/../config/conexion.php';

$id_proveedor = (int)($_POST['id_proveedor'] ?? 0);
$id_alumno    = current_user_id();

if ($id_proveedor > 0) {
    pg_query_params(
        $conexion,
        "DELETE FROM favorito WHERE id_alumno = $1 AND id_proveedor = $2",
        [$id_alumno, $id_proveedor]
    );
}

$_SESSION['mensaje'] = 'Eliminado de favoritos.';
$ref = $_SERVER['HTTP_REFERER'] ?? (base_url().'/views/favoritos.php');
header('Location: ' . $ref);
exit();
