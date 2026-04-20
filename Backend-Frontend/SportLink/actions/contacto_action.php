<?php
/**
 * fnContactInfo / IContactInfo - Envia un mensaje de contacto del alumno
 * hacia un proveedor. Persiste en mensaje_contacto.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('alumno');
require __DIR__ . '/../config/conexion.php';

$id_proveedor = (int)($_POST['id_proveedor'] ?? 0);
$tipo         = $_POST['tipo'] ?? '';
$asunto       = trim($_POST['asunto']  ?? '');
$mensaje      = trim($_POST['mensaje'] ?? '');
$id_alumno    = current_user_id();

if ($id_proveedor <= 0
    || !in_array($tipo, ['maestro','escuela'], true)
    || $mensaje === '') {
    $_SESSION['error'] = 'Mensaje invalido.';
    header('Location: ' . (base_url() . "/views/perfil_publico.php?id={$id_proveedor}&tipo={$tipo}"));
    exit();
}

pg_query_params(
    $conexion,
    "INSERT INTO mensaje_contacto (id_alumno, id_proveedor, tipo, asunto, mensaje)
     VALUES ($1,$2,$3,$4,$5)",
    [$id_alumno, $id_proveedor, $tipo, $asunto, $mensaje]
);

$_SESSION['mensaje'] = 'Tu mensaje fue enviado.';
header('Location: ' . (base_url() . "/views/perfil_publico.php?id={$id_proveedor}&tipo={$tipo}"));
exit();
