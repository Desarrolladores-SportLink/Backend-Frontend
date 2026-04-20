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
            m.ubicacion, m.telefono, m.descripcion, m.dias, m.red_social
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
                    <label>Años de experiencia</label>
                    <input type="text" name="experiencia" value="<?= e($d['experiencia'] ?? '') ?>" placeholder="Ej. 5 años">
                </div>
                <div class="form-group">
                    <label>Precio por clase (MXN)</label>
                    <input type="number" step="0.01" name="precio" value="<?= e($d['precio'] ?? '') ?>" placeholder="Ej. 250.00">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" value="<?= e($d['telefono'] ?? '') ?>">
                </div>
                
                <div class="form-group" style="grid-column:1/-1;" id="redes-container">
                    <label>Enlaces de Redes Sociales</label>
                    <?php 
                    $redes_arr = array_filter(array_map('trim', explode(',', $d['red_social'] ?? '')));
                    if (empty($redes_arr)): ?>
                        <input type="text" name="red_social[]" value="" placeholder="Ej. https://instagram.com/tu_perfil" style="margin-bottom: 8px;">
                    <?php else: 
                        foreach ($redes_arr as $red): ?>
                            <input type="text" name="red_social[]" value="<?= e($red) ?>" placeholder="Ej. https://instagram.com/tu_perfil" style="margin-bottom: 8px;">
                    <?php endforeach; 
                    endif; ?>
                    <button type="button" class="btn btn--ghost btn--sm" onclick="addRedSocial()" style="margin-top: 4px;">+ Agregar otra red</button>
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label>Ubicación / Zona de clases</label>
                    <input type="text" name="ubicacion" value="<?= e($d['ubicacion'] ?? '') ?>" placeholder="Ej. Parque Metropolitano, Zapopan">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Días disponibles</label>
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
                    <label>Descripción / Sobre ti</label>
                    <textarea name="descripcion" rows="4" placeholder="Cuéntanos tu método de enseñanza..."><?= e($d['descripcion'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn--primary">Guardar perfil</button>
            </div>
        </form>
    </article>
</div>

<script>
function addRedSocial() {
    const container = document.getElementById('redes-container');
    const btn = container.querySelector('button');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'red_social[]';
    input.placeholder = 'Ej. https://facebook.com/tu_perfil';
    input.style.marginBottom = '8px';
    container.insertBefore(input, btn);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
