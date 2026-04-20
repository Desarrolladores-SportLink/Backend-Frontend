<?php
/**
 * SportLink - Panel de la escuela deportiva
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('escuela');
require __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = current_user_id();
$r  = pg_query_params(
    $conexion,
    "SELECT u.nombre AS representante,
            e.nombre_escuela, e.direccion,
            e.deporte, e.deportes_ofrecidos,
            e.precio, e.mensualidad,
            e.ubicacion, e.telefono, e.descripcion, e.dias, e.red_social
     FROM usuario u
     INNER JOIN escuela e ON u.id_usuario = e.id_usuario
     WHERE u.id_usuario = $1",
    [$id]
);
$d = pg_fetch_assoc($r) ?: [];
$dias_sel = array_filter(array_map('trim', explode(',', $d['dias'] ?? '')));

$page_title = 'Panel de la escuela';
$active     = 'panel';
include __DIR__ . '/../includes/header.php';
?>

<div class="container container--narrow">
    <?= flash('mensaje_escuela', 'success') ?>

    <header class="profile-header">
        <div class="avatar-lg"><?= e(initials($d['nombre_escuela'] ?? 'Escuela')) ?></div>
        <div>
            <span class="tag tag--escuela">Escuela deportiva</span>
            <h1><?= e($d['nombre_escuela'] ?? 'Tu escuela') ?></h1>
            <p>Representante: <?= e($d['representante'] ?? '') ?></p>
        </div>
        <a class="btn btn--ghost" href="perfil_publico.php?id=<?= (int)$id ?>&tipo=escuela">Ver vista publica</a>
    </header>

    <article class="card">
        <h3 class="card-title">Informacion del centro deportivo</h3>
        <form method="POST" action="../actions/update_escuela.php">
            <div class="grid grid--2">
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Nombre de la escuela / club</label>
                    <input type="text" name="nombre_escuela" value="<?= e($d['nombre_escuela'] ?? '') ?>" required>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Direccion fisica</label>
                    <input type="text" name="direccion" value="<?= e($d['direccion'] ?? '') ?>" placeholder="Calle, numero, colonia">
                </div>
                <div class="form-group">
                    <label>Deporte principal</label>
                    <input type="text" name="deporte" value="<?= e($d['deporte'] ?? '') ?>" placeholder="Ej. Futbol">
                </div>
                <div class="form-group">
                    <label>Mensualidad ($)</label>
                    <input type="number" step="0.01" name="mensualidad" value="<?= e($d['mensualidad'] ?? '') ?>">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Otros deportes que ofrecen</label>
                    <input type="text" name="deportes_ofrecidos" value="<?= e($d['deportes_ofrecidos'] ?? '') ?>" placeholder="Ej. Basquetbol, Natacion, Voleibol">
                </div>
                <div class="form-group">
                    <label>Telefono</label>
                    <input type="tel" name="telefono" value="<?= e($d['telefono'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Ubicacion / Zona</label>
                    <input type="text" name="ubicacion" value="<?= e($d['ubicacion'] ?? '') ?>" placeholder="Colonia o zona">
                </div>

                <div class="form-group" style="grid-column:1/-1;" id="redes-container-escuela">
                    <label>Enlaces de Redes Sociales</label>
                    <?php 
                    $redes_arr = array_filter(array_map('trim', explode(',', $d['red_social'] ?? '')));
                    if (empty($redes_arr)): ?>
                        <input type="text" name="red_social[]" value="" placeholder="Ej. https://facebook.com/tu_escuela" style="margin-bottom: 8px;">
                    <?php else: 
                        foreach ($redes_arr as $red): ?>
                            <input type="text" name="red_social[]" value="<?= e($red) ?>" placeholder="Ej. https://facebook.com/tu_escuela" style="margin-bottom: 8px;">
                    <?php endforeach; 
                    endif; ?>
                    <button type="button" class="btn btn--ghost btn--sm" onclick="addRedSocialEscuela()" style="margin-top: 4px;">+ Agregar otra red</button>
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label>Dias de operacion</label>
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
                    <label>Descripcion</label>
                    <textarea name="descripcion" rows="4" placeholder="Cuentale a los alumnos sobre tu escuela..."><?= e($d['descripcion'] ?? '') ?></textarea>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn--primary">Actualizar informacion</button>
            </div>
        </form>
    </article>
</div>

<script>
function addRedSocialEscuela() {
    const container = document.getElementById('redes-container-escuela');
    const btn = container.querySelector('button');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'red_social[]';
    input.placeholder = 'Ej. https://instagram.com/tu_escuela';
    input.style.marginBottom = '8px';
    container.insertBefore(input, btn);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
