<?php
/**
 * SportLink - Punto de entrada
 * Redirige al panel correspondiente segun el rol o al login si no hay sesion.
 */
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

switch ($_SESSION['rol'] ?? '') {
    case 'alumno':  header('Location: views/buscar.php');  break;
    case 'maestro': header('Location: views/maestro.php'); break;
    case 'escuela': header('Location: views/escuela.php'); break;
    default:
        session_destroy();
        header('Location: login.php');
}
exit();
