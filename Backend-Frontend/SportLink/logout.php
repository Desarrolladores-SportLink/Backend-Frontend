<?php
/**
 * SportLink - Cierre de sesion
 * Disponible en la raiz tal como lo solicita la SWA (seccion 5.4).
 */
session_start();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit();
