<?php
/**
 * SportLink - UI_Search (Vista de búsqueda con filtros)
 * Componente final optimizado con geolocalización y cálculo de distancia.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_role('alumno'); // Seguridad: solo alumnos acceden al buscador

require_once __DIR__ . '/../actions/buscar_filtros_action.php';
require_once __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../config/conexion.php';

// Ejecución de la lógica de búsqueda (Action -> DB Handler)
$filtros    = captureFilters();
$resultados = fnSearchByFilters($filtros);
$dias       = dias_disponibles();

$page_title = 'Buscar';
$active     = 'buscar';
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container">
    <div class="search-layout">

        <aside class="card">
            <h3 class="card-title">Filtros de búsqueda</h3>
            <p class="card-subtitle">Refina los resultados según tus preferencias</p>

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
                    <label>Presupuesto máximo (MXN)</label>
                    <input type="number" name="precio_max" value="<?= e($filtros['precio_max']) ?>" placeholder="Ej. 500">
                </div>

                <div class="form-group">
                    <label>Ubicación</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" name="ubicacion" id="inputUbicacion" value="<?= e($filtros['ubicacion']) ?>" placeholder="Ciudad, colonia o zona" style="flex: 1;">
                        <button type="button" class="btn btn--ghost" id="btnGeo" title="Usar mi ubicación actual" style="padding: 0 12px;">
                            📍
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mapa de búsqueda</label>
                    <div id="map" style="width: 100%; height: 280px; border-radius: var(--radius-sm); border: 1px solid var(--border); margin-top: 8px; z-index: 1;"></div>
                </div>

                <div class="form-group">
                    <label>Días de entrenamiento</label>
                    <div class="chip-group">
                        <?php foreach ($dias as $d):
                            $isActive = (isset($filtros['dias_arr']) && in_array($d, $filtros['dias_arr'])); ?>
                            <label class="chip <?= $isActive?'active':'' ?>">
                                <input type="checkbox" name="dias_arr[]" value="<?= e($d) ?>" <?= $isActive?'checked':'' ?>>
                                <?= e($d) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <input type="hidden" name="lat" id="latHidden" value="<?= e($filtros['lat'] ?? '') ?>">
                <input type="hidden" name="lng" id="lngHidden" value="<?= e($filtros['lng'] ?? '') ?>">

                <button type="submit" class="btn btn--primary btn--block">Aplicar filtros</button>
                <a href="buscar.php" class="btn btn--ghost btn--block" style="margin-top:8px;">Limpiar búsqueda</a>
            </form>
        </aside>

        <section>
            <div class="flex-between" style="margin-bottom:14px;">
                <h2 class="mb-0">
                    <?= count($resultados) ?> resultado<?= count($resultados)===1?'':'s' ?>
                </h2>
                <span class="text-muted" style="font-size:13px;">
                    Mostrando <?= $filtros['tipo']==='escuela'?'escuelas':'entrenadores' ?> 
                </span>
            </div>

            <?php if (empty($resultados)): ?>
                <div class="card empty-state">
                    <h3>Sin resultados</h3>
                    <p>No encontramos servicios que coincidan. Prueba a relajar los filtros o mover el mapa.</p>
                </div>
            <?php else: ?>
                <?php foreach ($resultados as $row): ?>
                    <article class="result-card">
                        <div class="result-avatar">
                            <?php if (!empty($row['foto'])): ?>
                                <img src="<?= e(base_url().'/uploads/'.$row['foto']) ?>" alt="">
                            <?php else: ?>
                                <?= e(initials($row['nombre'])) ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="result-body">
                            <div class="flex-between">
                                <span class="tag tag--<?= e($row['tipo']) ?>">
                                    <?= $row['tipo']==='escuela'?'Escuela':'Entrenador' ?>
                                </span>
                                
                                <?php if (isset($row['distancia'])): ?>
                                    <span class="distancia-tag">
                                        📍 A <?= $row['distancia'] ?> km de ti
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="result-name"><?= e($row['nombre']) ?></h3>
                            
                            <div class="result-meta">
                                <span>&#127942; <?= e($row['deporte'] ?: 'Varios') ?></span>
                                <span>&#128205; <?= e($row['ubicacion'] ?: 'Consultar dirección') ?></span>
                            </div>
                            
                            <div class="result-price">
                                <?= format_money($row['precio']) ?> 
                                <span class="text-light" style="font-size:12px;">/ <?= $row['tipo']==='escuela'?'mes':'clase' ?></span>
                            </div>
                        </div>

                        <div class="result-actions">
                            <a class="btn btn--primary btn--sm" 
                               href="perfil_publico.php?id=<?= $row['id_usuario'] ?>&tipo=<?= $row['tipo'] ?>">
                               Ver perfil
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../js/mapa.js"></script>
<script src="../js/app.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
