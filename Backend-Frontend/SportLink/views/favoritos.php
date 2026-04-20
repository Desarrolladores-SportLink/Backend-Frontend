<?php
/**
 * SportLink - Lista de favoritos del alumno
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('alumno');
require __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../actions/db_query_handler.php';
require_once __DIR__ . '/../includes/helpers.php';

$favs = fnGetFavoritosAlumno($conexion, current_user_id());

$page_title = 'Favoritos';
$active     = 'favoritos';
include __DIR__ . '/../includes/header.php';
?>

<div class="container container--narrow">
    <?= flash('mensaje', 'success') ?>
    <h1>Tus favoritos</h1>
    <p class="text-muted">Entrenadores y escuelas que guardaste para volver a contactar.</p>

    <?php if (empty($favs)): ?>
        <div class="card empty-state">
            <h3>Aun no tienes favoritos</h3>
            <p>Marca con la estrella los perfiles que te interesen y apareceran aqui.</p>
            <a class="btn btn--primary" href="buscar.php">Ir a buscar</a>
        </div>
    <?php else: foreach ($favs as $f):
        [$prom, $totalRes] = get_rating_summary($conexion, (int)$f['id_proveedor']); ?>
        <article class="result-card">
            <div class="result-avatar"><?= e(initials($f['nombre'])) ?></div>
            <div class="result-body">
                <span class="tag tag--<?= e($f['tipo']) ?>"><?= $f['tipo']==='escuela'?'Escuela':'Entrenador' ?></span>
                <h3 class="result-name"><?= e($f['nombre']) ?></h3>
                <div class="result-meta">
                    <span>&#127942; <?= e($f['deporte'] ?? 'Sin especialidad') ?></span>
                    <span>&#128205; <?= e($f['ubicacion'] ?? 'Sin ubicacion') ?></span>
                    <?php if ($totalRes>0): ?><span><?= render_stars($prom) ?> (<?= $totalRes ?>)</span><?php endif; ?>
                </div>
                <div class="result-price"><?= format_money($f['precio']) ?></div>
            </div>
            <div class="result-actions">
                <a class="btn btn--primary btn--sm" href="perfil_publico.php?id=<?= (int)$f['id_proveedor'] ?>&tipo=<?= e($f['tipo']) ?>">Ver perfil</a>
                <form method="POST" action="../actions/quitar_favorito_action.php">
                    <input type="hidden" name="id_proveedor" value="<?= (int)$f['id_proveedor'] ?>">
                    <button class="btn btn--danger btn--sm btn--block" type="submit">Quitar</button>
                </form>
            </div>
        </article>
    <?php endforeach; endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
