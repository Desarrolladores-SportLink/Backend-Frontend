<?php
/**
 * SportLink - UI_Search (vista de busqueda con filtros)
 * Subcomponente de presentacion (SDD seccion 5).
 * Implementa el flujo:  showSearchForm -> captureFilters -> fnSearchByFilters -> fnRenderSearchResults
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('alumno');

require_once __DIR__ . '/../actions/buscar_filtros_action.php';
require_once __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../config/conexion.php';

$filtros    = captureFilters();
$resultados = fnSearchByFilters($filtros);
$dias       = dias_disponibles();

$page_title = 'Buscar';
$active     = 'buscar';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="search-layout">

        <!-- Panel de filtros -->
        <aside class="card">
            <h3 class="card-title">Filtros de busqueda</h3>
            <p class="card-subtitle">Refina los resultados segun tus preferencias</p>

            <form method="GET" id="formFiltros">
                <div class="form-group">
                    <label>Tipo de servicio</label>
                    <select name="tipo">
                        <option value="maestro" <?= $filtros['tipo']==='maestro'?'selected':'' ?>>Entrenadores</option>
                        <option value="escuela" <?= $filtros['tipo']==='escuela'?'selected':'' ?>>Escuelas deportivas</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Deporte</label>
                    <input type="text" name="deporte" value="<?= e($filtros['deporte']) ?>" placeholder="Ej. Futbol, Tenis, Box">
                </div>

                <div class="form-group">
                    <label>Presupuesto maximo (MXN)</label>
                    <input type="number" name="precio_max" value="<?= e($filtros['precio_max']) ?>" placeholder="Ej. 500" min="0" step="50">
                </div>

                <div class="form-group">
                    <label>Ubicacion</label>
                    <input type="text" name="ubicacion" value="<?= e($filtros['ubicacion']) ?>" placeholder="Ciudad, colonia o zona">
                </div>

                <div class="form-group">
                    <label>Dias de entrenamiento</label>
                    <div class="chip-group" data-chip-group="dias">
                        <?php foreach ($dias as $d):
                            $isActive = stripos($filtros['dias'], $d) !== false; ?>
                            <label class="chip <?= $isActive?'active':'' ?>">
                                <input type="checkbox" name="dias_arr[]" value="<?= e($d) ?>" <?= $isActive?'checked':'' ?>>
                                <?= e($d) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="dias" id="diasHidden" value="<?= e($filtros['dias']) ?>">
                </div>

                <button type="submit" class="btn btn--primary btn--block">Aplicar filtros</button>
                <a href="buscar.php" class="btn btn--ghost btn--block" style="margin-top:8px;">Limpiar</a>
            </form>
        </aside>

        <!-- Resultados -->
        <section>
            <div class="flex-between" style="margin-bottom:14px;">
                <h2 class="mb-0">
                    <?= count($resultados) ?> resultado<?= count($resultados)===1?'':'s' ?>
                </h2>
                <span class="text-muted" style="font-size:13px;">
                    Mostrando <?= $filtros['tipo']==='escuela'?'escuelas':'entrenadores' ?>
                    <?= $filtros['deporte']!=='' ? 'de '.e($filtros['deporte']) : '' ?>
                </span>
            </div>

            <?php if (empty($resultados)): ?>
                <div class="card empty-state">
                    <h3>Sin resultados</h3>
                    <p>Prueba a relajar los filtros o cambiar el tipo de servicio.</p>
                </div>
            <?php else: ?>
                <?php foreach ($resultados as $row):
                    [$prom, $totalRes] = get_rating_summary($conexion, (int)$row['id_usuario']);
                    $favorito = is_favorito($conexion, current_user_id(), (int)$row['id_usuario']);
                    $tipo     = $row['tipo'];
                ?>
                    <article class="result-card">
                        <div class="result-avatar">
                            <?php if (!empty($row['foto'])): ?>
                                <img src="<?= e(base_url().'/uploads/'.$row['foto']) ?>" alt="">
                            <?php else: ?>
                                <?= e(initials($row['nombre'])) ?>
                            <?php endif; ?>
                        </div>
                        <div class="result-body">
                            <span class="tag tag--<?= e($tipo) ?>"><?= $tipo==='escuela'?'Escuela':'Entrenador' ?></span>
                            <h3 class="result-name"><?= e($row['nombre']) ?></h3>
                            <div class="result-meta">
                                <span>&#127942; <?= e($row['deporte'] ?: 'Sin especialidad') ?></span>
                                <span>&#128205; <?= e($row['ubicacion'] ?: 'Ubicacion no indicada') ?></span>
                                <?php if (!empty($row['dias'])): ?>
                                    <span>&#128197; <?= e($row['dias']) ?></span>
                                <?php endif; ?>
                                <?php if ($totalRes > 0): ?>
                                    <span><?= render_stars($prom) ?> <span class="text-light">(<?= $totalRes ?>)</span></span>
                                <?php endif; ?>
                            </div>
                            <div class="result-price"><?= format_money($row['precio']) ?> <span class="text-light" style="font-size:12px; font-weight:500;">/ <?= $tipo==='escuela'?'mes':'clase' ?></span></div>
                        </div>
                        <div class="result-actions">
                            <a class="btn btn--primary btn--sm"
                               href="perfil_publico.php?id=<?= (int)$row['id_usuario'] ?>&tipo=<?= e($tipo) ?>">Ver perfil</a>
                            <form method="POST" action="../actions/<?= $favorito?'quitar_favorito_action.php':'guardar_favorito_action.php' ?>">
                                <input type="hidden" name="id_proveedor" value="<?= (int)$row['id_usuario'] ?>">
                                <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
                                <button class="btn btn--ghost btn--sm btn--block" type="submit">
                                    <?= $favorito?'&#9733; Guardado':'&#9734; Favorito' ?>
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
