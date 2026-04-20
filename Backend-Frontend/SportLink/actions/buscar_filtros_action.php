<?php
/**
 * SportLink - Action_SearchLogic
 * Subcomponente de logica de aplicacion (SDD seccion 5).
 * Coordina la captura de filtros, su validacion y la ejecucion de la consulta
 * a traves del DB_QueryHandler.
 *
 * Funciones expuestas:
 *   - captureFilters(): array
 *   - fnValidateFilters(array $filtrosBusqueda): bool
 *   - fnSearchByFilters(array $filtrosBusqueda): array
 */

require_once __DIR__ . '/db_query_handler.php';

/**
 * captureFilters (SDD figura 2, seccion 4)
 * Recolecta los filtros enviados por el formulario (GET) y los normaliza.
 */
function captureFilters(): array {
    return [
        'tipo'       => trim($_GET['tipo']       ?? 'maestro'),
        'deporte'    => trim($_GET['deporte']    ?? ''),
        'precio_max' => trim($_GET['precio_max'] ?? ''),
        'dias'       => trim($_GET['dias']       ?? ''),
        'ubicacion'  => trim($_GET['ubicacion']  ?? ''),
    ];
}

/**
 * fnValidateFilters (SDD 5.2.2)
 * Sanitiza y valida los datos recibidos.
 * Retorna true si el arreglo esta limpio y listo para construir la consulta.
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
            if (mb_strlen($val) > 80) return false;
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
 * Funcion principal: captura -> valida -> ejecuta -> retorna arreglo de objetos.
 *
 * @param array $filtrosBusqueda  arreglo asociativo (puede venir de captureFilters())
 * @return array  filas de resultados (vacio si nada coincide)
 */
function fnSearchByFilters(array $filtrosBusqueda): array {
    if (!fnValidateFilters($filtrosBusqueda)) return [];

    require __DIR__ . '/../config/conexion.php';
    return fnExecuteSearchQuery($conexion, $filtrosBusqueda);
}
