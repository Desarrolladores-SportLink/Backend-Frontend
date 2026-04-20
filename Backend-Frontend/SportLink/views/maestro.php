<?php
/**
 * SportLink - Panel del entrenador (maestro)
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('maestro');
require __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = current_user_id();
$r  = pg_query_params(
    $conexion,
    "SELECT u.nombre, u.apellidos,
            m.especialidad, m.experiencia, m.precio,
            m.ubicacion, m.telefono, m.descripcion, m.dias
     FROM usuario u
     INNER JOIN maestro m ON u.id_usuario = m.id_usuario
     WHERE u.id_usuario = $1",
    [$id]
);
$d = pg_fetch_assoc($r) ?: [];
$dias_sel = array_filter(array_map('trim', explode(',', $d['dias'] ?? '')));

$page_title = 'Panel del entrenador';
$active     = 'panel';
include __DIR__ . '/../includes/header.php';
?>

<div class="container container--narrow">
    <?= flash('mensaje_perfil', 'success') ?>

    <header class="profile-header">
        <div class="avatar-lg"><?= e(initials(($d['nombre'] ?? '').' '.($d['apellidos'] ?? ''))) ?></div>
        <div>
            <span class="tag tag--maestro">Entrenador</span>
            <h1><?= e(($d['nombre'] ?? '').' '.($d['apellidos'] ?? '')) ?></h1>
            <p>Completa tu perfil profesional para que los alumnos puedan encontrarte.</p>
        </div>
        <a class="btn btn--ghost" href="perfil_publico.php?id=<?= (int)$id ?>&tipo=maestro">Ver vista publica</a>
    </header>

    <article class="card">
        <h3 class="card-title">Editar perfil profesional</h3>
        <form method="POST" action="../actions/update_maestro.php">
            <div class="grid grid--2">
                <div class="form-group">
                    <label>Deporte / Especialidad</label>
                    <input type="text" name="especialidad" value="<?= e($d['especialidad'] ?? '') ?>" required placeholder="Ej. Tenis, Natacion">
                </div>
                <div class="form-group">
                    <label>Anos de experiencia</label>
                    <input type="text" name="experiencia" value="<?= e($d['experiencia'] ?? '') ?>" placeholder="Ej. 5 anos">
                </div>
                <div class="form-group">
                    <label>Precio por clase (MXN)</label>
                    <input type="number" step="0.01" name="precio" value="<?= e($d['precio'] ?? '') ?>" placeholder="Ej. 250.00">
                </div>
                <div class="form-group">
                    <label>Telefono</label>
                    <input type="tel" name="telefono" value="<?= e($d['telefono'] ?? '') ?>">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Ubicacion / Zona de clases</label>
                    <input type="text" name="ubicacion" value="<?= e($d['ubicacion'] ?? '') ?>" placeholder="Ej. Parque Metropolitano, Zapopan">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Dias disponibles</label>
                    <div class="chip-group">
                        <?php foreach (dias_disponibles() as $dia):
                            $on = in_array($dia, $dias_sel, true); ?>
                            <label class="chip <?= $on?'active':'' ?>">
                                <input type="checkbox" name="dias_arr[]" value="<?= e($dia) ?>" <?= $on?'checked':'' ?>>
                                <?= e($dia) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="dias" value="<?= e($d['dias'] ?? '') ?>">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Descripcion / Sobre ti</label>
                    <textarea name="descripcion" rows="4" placeholder="Cuentanos tu metodo de ensenanza..."><?= e($d['descripcion'] ?? '') ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn--primary">Guardar perfil</button>
        </form>
    </article>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
