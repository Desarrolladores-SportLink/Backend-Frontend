<?php
/**
 * SportLink - Helpers de presentacion y utilidades
 */

if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $ini = '';
    foreach ($parts as $p) {
        if ($p !== '') $ini .= mb_strtoupper(mb_substr($p, 0, 1));
        if (mb_strlen($ini) >= 2) break;
    }
    return $ini ?: 'SL';
}

function flash($key = 'mensaje', $type = 'info'): string {
    if (empty($_SESSION[$key])) return '';
    $msg = $_SESSION[$key];
    unset($_SESSION[$key]);
    return '<div class="toast toast--' . e($type) . '">' . e($msg) . '</div>';
}

function dias_disponibles(): array {
    return ['Lun','Mar','Mie','Jue','Vie','Sab','Dom'];
}

function format_money($n): string {
    if ($n === null || $n === '') return 'N/D';
    return '$' . number_format((float)$n, 2);
}

function render_stars(float $rating, int $max = 5): string {
    $filled = (int)round($rating);
    $html = '<span class="stars">';
    for ($i = 1; $i <= $max; $i++) {
        $html .= $i <= $filled ? '&#9733;' : '<span style="color:#CBD5E1">&#9733;</span>';
    }
    $html .= '</span>';
    return $html;
}

/**
 * Devuelve [promedio, total] de resenas para un proveedor.
 */
function get_rating_summary($conexion, int $id_proveedor): array {
    $r = pg_query_params(
        $conexion,
        "SELECT COALESCE(AVG(calificacion),0)::float AS prom, COUNT(*)::int AS total
         FROM resena WHERE id_proveedor = $1",
        [$id_proveedor]
    );
    if ($r && $row = pg_fetch_assoc($r)) {
        return [(float)$row['prom'], (int)$row['total']];
    }
    return [0.0, 0];
}

/**
 * Verifica si un alumno marco como favorito a un proveedor.
 */
function is_favorito($conexion, int $id_alumno, int $id_proveedor): bool {
    $r = pg_query_params(
        $conexion,
        "SELECT 1 FROM favorito WHERE id_alumno = $1 AND id_proveedor = $2 LIMIT 1",
        [$id_alumno, $id_proveedor]
    );
    return $r && pg_num_rows($r) > 0;
}
