<?php
/**
 * Compatibilidad: el panel de alumno ahora vive en buscar.php.
 * Este archivo redirige para no romper enlaces antiguos.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('alumno');
header('Location: buscar.php');
exit();
