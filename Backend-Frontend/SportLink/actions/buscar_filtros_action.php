<?php
/**
 * SportLink - Action_SearchLogic
 * Coordina la captura, validacion y ejecucion de la busqueda.
 */

require_once __DIR__ . '/db_query_handler.php';

/**
 * captureFilters
 * Recolecta los filtros del formulario (GET) y los normaliza.
 */
function captureFilters(): array {
    return [
        'tipo'       => trim($_GET['tipo']       ?? 'maestro'),
        'deporte'    => trim($_GET['deporte']    ?? ''),
        'lat'        => trim($_GET['lat']        ?? ''),
        'lng'        => trim($_GET['lng']        ?? ''),
        'precio_max' => trim($_GET['precio_max'] ?? ''),
        'dias'       => trim($_GET['dias']       ?? ''),
        'ubicacion'  => trim($_GET['ubicacion']  ?? ''),
        'dias_arr'   => $_GET['dias_arr']        ?? [] // Para filtros multiples
    ];
}

/**
 * fnValidateFilters (SDD 5.2.2)
 * Sanitiza los datos y permite direcciones largas de geolocalización.
 */
function fnValidateFilters(array &$filtrosBusqueda): bool {
    $tiposPermitidos = ['maestro', 'escuela'];
    if (!in_array($filtrosBusqueda['tipo'] ?? '', $tiposPermitidos, true)) {
        $filtrosBusqueda['tipo'] = 'maestro';
    }

    foreach (['deporte', 'ubicacion', 'dias'] as $campo) {
        $val = $filtrosBusqueda[$campo] ?? '';
        if ($val !== '') {
            $val = preg_replace('/[\\\\;\'"`]/u', '', $val);
            
            // Aumentamos el límite a 250 para direcciones de Nominatim/GPS
            if (mb_strlen($val) > 250) return false; 
            
            $filtrosBusqueda[$campo] = $val;
        }
    }

    $precio = $filtrosBusqueda['precio_max'] ?? '';
    if ($precio !== '') {
        if (!is_numeric($precio) || (float)$precio < 0 || (float)$precio > 1000000) {
            return false;
        }
        $filtrosBusqueda['precio_max'] = (float)$precio;
    }

    return true;
}

/**
 * fnSearchByFilters (SDD 5.2.1)
 */
function fnSearchByFilters(array $filtrosBusqueda): array {
    if (!fnValidateFilters($filtrosBusqueda)) return [];

    require __DIR__ . '/../config/conexion.php';
    return fnExecuteSearchQuery($conexion, $filtrosBusqueda);
}
