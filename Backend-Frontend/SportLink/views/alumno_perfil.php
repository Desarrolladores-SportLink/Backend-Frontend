<?php
/**
 * SportLink - Perfil del alumno (visualizar y editar)
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('alumno');
require __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = current_user_id();
$r  = pg_query_params(
    $conexion,
    "SELECT u.nombre, u.apellidos, u.correo, u.username,
            a.codigo_estudiante, a.edad, a.telefono, a.deporte_interes
     FROM usuario u
     INNER JOIN alumno a ON u.id_usuario = a.id_usuario
     WHERE u.id_usuario = $1",
    [$id]
);
$d = pg_fetch_assoc($r) ?: [];

$page_title = 'Mi perfil';
$active     = 'perfil';
include __DIR__ . '/../includes/header.php';
?>

<div class="container container--narrow">
    <?= flash('mensaje', 'success') ?>

    <header class="profile-header">
        <div class="avatar-lg"><?= e(initials(($d['nombre'] ?? '').' '.($d['apellidos'] ?? ''))) ?></div>
        <div>
            <span class="tag">Alumno</span>
            <h1><?= e(($d['nombre'] ?? '').' '.($d['apellidos'] ?? '')) ?></h1>
            <p>@<?= e($d['username'] ?? '') ?> &middot; <?= e($d['correo'] ?? '') ?></p>
        </div>
        <div></div>
    </header>

    <article class="card">
        <h3 class="card-title">Editar perfil</h3>
        <form method="POST" action="../actions/update_alumno.php">
            <div class="grid grid--2">
                <div class="form-group">
                    <label>Codigo de estudiante</label>
                    <input type="text" name="codigo_estudiante" value="<?= e($d['codigo_estudiante'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Edad</label>
                    <input type="number" name="edad" value="<?= e($d['edad'] ?? '') ?>" min="6" max="120">
                </div>
                <div class="form-group">
                    <label>Telefono</label>
                    <input type="tel" name="telefono" value="<?= e($d['telefono'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Deporte de interes</label>
                    <input type="text" name="deporte_interes" value="<?= e($d['deporte_interes'] ?? '') ?>" placeholder="Ej. Natacion, Futbol">
                </div>
            </div>
            <button type="submit" class="btn btn--primary">Guardar cambios</button>
        </form>
    </article>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
