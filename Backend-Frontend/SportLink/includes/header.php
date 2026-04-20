<?php
/**
 * SportLink - Cabecera comun (navbar + apertura <body>)
 * Requisitos antes de incluir:
 *   - session_start() ya invocado (auth_check.php lo hace)
 *   - Opcional: $page_title, $active (clave del link activo)
 */
require_once __DIR__ . '/helpers.php';

$page_title = $page_title ?? 'SportLink';
$active     = $active     ?? '';
$rol        = $_SESSION['rol'] ?? null;
$base       = function_exists('base_url') ? base_url() : '';

$menu = [];
if ($rol === 'alumno') {
    $menu = [
        'buscar'    => ['label' => 'Buscar',    'href' => $base . '/views/buscar.php'],
        'favoritos' => ['label' => 'Favoritos', 'href' => $base . '/views/favoritos.php'],
        'perfil'    => ['label' => 'Mi perfil', 'href' => $base . '/views/alumno_perfil.php'],
    ];
} elseif ($rol === 'maestro') {
    $menu = [
        'panel'  => ['label' => 'Mi panel',   'href' => $base . '/views/maestro.php'],
        'publico'=> ['label' => 'Vista publica', 'href' => $base . '/views/perfil_publico.php?id=' . (int)($_SESSION['id_usuario'] ?? 0) . '&tipo=maestro'],
    ];
} elseif ($rol === 'escuela') {
    $menu = [
        'panel'  => ['label' => 'Mi escuela', 'href' => $base . '/views/escuela.php'],
        'publico'=> ['label' => 'Vista publica', 'href' => $base . '/views/perfil_publico.php?id=' . (int)($_SESSION['id_usuario'] ?? 0) . '&tipo=escuela'],
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | SportLink</title>
    <link rel="stylesheet" href="<?= e($base) ?>/styles/app.css">
    <link rel="icon" type="image/png" href="<?= e($base) ?>/styles/logo%20sin%20fondo.png">
</head>
<body>
    <nav class="navbar">
        <a class="brand" href="<?= e($base) ?>/index.php">
            <img src="<?= e($base) ?>/styles/logo%20sin%20fondo.png" alt="SportLink">
            <span>Sport<span class="dot">Link</span></span>
        </a>
        <div class="nav-links">
            <?php foreach ($menu as $key => $item): ?>
                <a href="<?= e($item['href']) ?>" class="<?= $active === $key ? 'active' : '' ?>"><?= e($item['label']) ?></a>
            <?php endforeach; ?>
            <?php if (!empty($_SESSION['id_usuario'])): ?>
                <span class="avatar" title="<?= e(current_user_name()) ?>"><?= e(initials(current_user_name())) ?></span>
                <a href="<?= e($base) ?>/logout.php">Salir</a>
            <?php else: ?>
                <a href="<?= e($base) ?>/login.php">Entrar</a>
            <?php endif; ?>
        </div>
    </nav>
    <main>
