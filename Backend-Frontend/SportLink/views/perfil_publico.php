<?php
/**
 * SportLink - Perfil publico (entrenador / escuela)
 * Implementa las extensiones del flujo de busqueda (SDD seccion 4):
 *   - Visualizacion de perfil
 *   - Agregar a favoritos
 *   - Boton de contacto
 *   - Dejar resena
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_login();
require __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../actions/db_query_handler.php';
require_once __DIR__ . '/../includes/helpers.php';

$id   = isset($_GET['id'])   ? (int)$_GET['id']   : 0;
$tipo = $_GET['tipo'] ?? 'maestro';
if (!in_array($tipo, ['maestro','escuela'], true)) $tipo = 'maestro';

$datos = fnGetProveedorById($conexion, $id, $tipo);
if (!$datos) {
    $_SESSION['mensaje'] = 'El perfil solicitado no existe.';
    redirect_to('/views/buscar.php');
}

$nombreShow = $tipo === 'escuela' ? ($datos['nombre_escuela'] ?? 'Escuela') : ($datos['nombre'] ?? 'Entrenador');
[$prom, $totalRes] = get_rating_summary($conexion, $id);
$resenas  = fnGetResenasProveedor($conexion, $id);
$favorito = current_role() === 'alumno' ? is_favorito($conexion, current_user_id(), $id) : false;

$page_title = $nombreShow;
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <?= flash('mensaje', 'success') ?>
    <?= flash('error',   'error') ?>

    <!-- Cabecera -->
    <header class="profile-header">
        <div class="avatar-lg">
            <?php if (!empty($datos['foto'])): ?>
                <img src="<?= e(base_url().'/uploads/'.$datos['foto']) ?>" alt="">
            <?php else: ?>
                <?= e(initials($nombreShow)) ?>
            <?php endif; ?>
        </div>
        <div>
            <span class="tag tag--<?= e($tipo) ?>"><?= $tipo==='escuela'?'Escuela':'Entrenador' ?></span>
            <h1><?= e($nombreShow) ?></h1>
            <p style="font-size:15px;">
                &#127942; <?= e($datos['especialidad'] ?? $datos['deporte'] ?? 'Sin especialidad') ?>
                &nbsp;&middot;&nbsp;
                &#128205; <?= e($datos['ubicacion'] ?? $datos['direccion'] ?? 'Sin ubicacion') ?>
            </p>
            <p style="margin-top:10px;">
                <?= render_stars($prom) ?>
                <span style="color:#CBD5E1; margin-left:6px;"><?= number_format($prom,1) ?> &middot; <?= $totalRes ?> resena<?= $totalRes===1?'':'s' ?></span>
            </p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:32px; font-weight:800; color:var(--primary);"><?= format_money($datos['precio'] ?? null) ?></div>
            <div style="color:#CBD5E1; font-size:13px;">/ <?= $tipo==='escuela'?'mes':'clase' ?></div>
        </div>
    </header>

    <div class="grid grid--2">

        <!-- Detalle del proveedor -->
        <section>
            <article class="card">
                <h3 class="card-title">Acerca</h3>
                <p><?= nl2br(e($datos['descripcion'] ?? 'El usuario aun no ha agregado una descripcion.')) ?></p>

                <div class="grid grid--3" style="margin-top:18px;">
                    <?php if (!empty($datos['experiencia'])): ?>
                        <div>
                            <small class="text-muted">Experiencia</small>
                            <p class="mb-0" style="font-weight:600;"><?= e($datos['experiencia']) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($datos['dias'])): ?>
                        <div>
                            <small class="text-muted">Dias</small>
                            <p class="mb-0" style="font-weight:600;"><?= e($datos['dias']) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($datos['telefono'])): ?>
                        <div>
                            <small class="text-muted">Telefono</small>
                            <p class="mb-0" style="font-weight:600;"><?= e($datos['telefono']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <!-- Resenas -->
            <article class="card mt-24">
                <h3 class="card-title">Resenas (<?= $totalRes ?>)</h3>

                <?php if (current_role() === 'alumno'): ?>
                    <form method="POST" action="../actions/guardar_resena_action.php" style="border-bottom:1px solid var(--border); padding-bottom:16px; margin-bottom:8px;">
                        <input type="hidden" name="id_proveedor" value="<?= $id ?>">
                        <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
                        <label style="font-weight:600; font-size:14px;">Tu calificacion</label>
                        <div class="rating-select" style="margin:8px 0; flex-direction:row-reverse; justify-content:flex-end;">
                            <?php for ($s=5; $s>=1; $s--): ?>
                                <input type="radio" id="r-<?= $s ?>" name="calificacion" value="<?= $s ?>" <?= $s===5?'checked':'' ?>>
                                <label for="r-<?= $s ?>">&#9733;</label>
                            <?php endfor; ?>
                        </div>
                        <textarea name="comentario" placeholder="Comparte tu experiencia..." required></textarea>
                        <button type="submit" class="btn btn--primary" style="margin-top:8px;">Publicar resena</button>
                    </form>
                <?php endif; ?>

                <?php if (empty($resenas)): ?>
                    <p class="text-muted" style="margin-top:14px;">Aun no hay resenas. &iexcl;Se el primero en opinar!</p>
                <?php else: foreach ($resenas as $r): ?>
                    <div class="review">
                        <div class="review-head">
                            <div>
                                <div class="review-author"><?= e($r['autor'].' '.$r['autor_ap']) ?></div>
                                <?= render_stars((float)$r['calificacion']) ?>
                            </div>
                            <span class="review-date"><?= e(date('d/m/Y', strtotime($r['fecha']))) ?></span>
                        </div>
                        <p class="review-body"><?= nl2br(e($r['comentario'])) ?></p>
                    </div>
                <?php endforeach; endif; ?>
            </article>
        </section>

        <!-- Acciones -->
        <aside>
            <article class="card">
                <h3 class="card-title">Acciones</h3>

                <?php if (current_role() === 'alumno'): ?>
                    <form method="POST"
                          action="../actions/<?= $favorito?'quitar_favorito_action.php':'guardar_favorito_action.php' ?>"
                          style="margin-bottom:10px;">
                        <input type="hidden" name="id_proveedor" value="<?= $id ?>">
                        <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
                        <button class="btn <?= $favorito?'btn--ghost':'btn--primary' ?> btn--block" type="submit">
                            <?= $favorito ? '&#9733; Quitar de favoritos' : '&#9734; Agregar a favoritos' ?>
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (!empty($datos['telefono'])): ?>
                    <a class="btn btn--ghost btn--block" href="tel:<?= e($datos['telefono']) ?>" style="margin-bottom:10px;">
                        &#128222; Llamar
                    </a>
                    <a class="btn btn--ghost btn--block" target="_blank"
                       href="https://wa.me/<?= e(preg_replace('/\D+/','', $datos['telefono'])) ?>" style="margin-bottom:10px;">
                        &#128172; WhatsApp
                    </a>
                <?php endif; ?>

                <?php if (current_role() === 'alumno'): ?>
                    <button class="btn btn--primary btn--block" type="button"
                            onclick="document.getElementById('contactBox').style.display='block'; this.style.display='none';">
                        Enviar mensaje
                    </button>

                    <form id="contactBox" style="display:none; margin-top:14px;"
                          method="POST" action="../actions/contacto_action.php">
                        <input type="hidden" name="id_proveedor" value="<?= $id ?>">
                        <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
                        <div class="form-group">
                            <label>Asunto</label>
                            <input type="text" name="asunto" required>
                        </div>
                        <div class="form-group">
                            <label>Mensaje</label>
                            <textarea name="mensaje" required></textarea>
                        </div>
                        <button type="submit" class="btn btn--primary btn--block">Enviar</button>
                    </form>
                <?php endif; ?>
            </article>

            <article class="card mt-24">
                <h3 class="card-title">Ubicacion</h3>
                <p class="mb-0"><?= e($datos['ubicacion'] ?? $datos['direccion'] ?? 'No registrada') ?></p>
                <?php
                $loc = trim($datos['ubicacion'] ?? $datos['direccion'] ?? '');
                if ($loc !== ''):
                    $q = urlencode($loc); ?>
                    <a class="btn btn--ghost btn--block" style="margin-top:12px;"
                       target="_blank" href="https://www.google.com/maps/search/?api=1&query=<?= $q ?>">
                        Abrir en Google Maps
                    </a>
                <?php endif; ?>
            </article>
        </aside>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
