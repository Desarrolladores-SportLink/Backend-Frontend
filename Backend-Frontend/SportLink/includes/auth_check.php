<?php
/**
 * SportLink - Verificacion de sesion y rol
 * Uso:
 *   require_once __DIR__ . '/../includes/auth_check.php';
 *   require_login();              // exige sesion (cualquier rol)
 *   require_role('alumno');       // exige sesion + rol especifico
 *   require_role(['maestro','escuela']);  // exige uno de varios roles
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function base_url(): string {
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if (strpos($script, '/views') !== false || strpos($script, '/actions') !== false) {
        $script = dirname($script);
    }
    return rtrim($script, '/');
}

function redirect_to(string $path): void {
    header('Location: ' . base_url() . $path);
    exit();
}

function require_login(): void {
    if (!isset($_SESSION['id_usuario'])) {
        $_SESSION['mensaje'] = 'Debes iniciar sesion para continuar.';
        redirect_to('/login.php');
    }
}

function require_role($roles): void {
    require_login();
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($_SESSION['rol'] ?? '', $allowed, true)) {
        $_SESSION['mensaje'] = 'No tienes permiso para acceder a esa seccion.';
        redirect_to('/login.php');
    }
}

function current_user_id(): ?int {
    return isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : null;
}

function current_role(): ?string {
    return $_SESSION['rol'] ?? null;
}

function current_user_name(): string {
    return $_SESSION['nombre_completo'] ?? ($_SESSION['username'] ?? 'Usuario');
}
