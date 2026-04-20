<?php
/**
 * SportLink - DB_QueryHandler
 * Subcomponente de persistencia (SDD seccion 5, interfaz iBEtoDB).
 *
 * Encapsula la construccion y ejecucion de consultas SQL sobre PostgreSQL.
 * Toda la logica de acceso a datos del modulo de busqueda vive aqui para
 * mantener separadas las responsabilidades (UI_Search / Action_SearchLogic / DB_QueryHandler).
 */

/**
 * fnExecuteSearchQuery (SDD 5.2.3)
 * Ejecuta la busqueda dinamica sobre maestro o escuela aplicando los filtros
 * previamente validados.
 *
 * @param resource $conexion
 * @param array    $validFilters  ['tipo','deporte','precio_max','dias','ubicacion']
 * @return array   filas obtenidas (puede ser arreglo vacio)
 */
function fnExecuteSearchQuery($conexion, array $validFilters): array {
    $tipo = $validFilters['tipo'] ?? 'maestro';
    $params = [];
    $i = 1;

    if ($tipo === 'escuela') {
        $sql = "SELECT u.id_usuario,
                       e.nombre_escuela AS nombre,
                       COALESCE(e.deporte, e.deportes_ofrecidos) AS deporte,
                       COALESCE(e.precio, e.mensualidad) AS precio,
                       COALESCE(e.ubicacion, e.direccion) AS ubicacion,
                       e.telefono,
                       e.dias,
                       e.descripcion,
                       e.foto,
                       'escuela' AS tipo
                FROM usuario u
                INNER JOIN escuela e ON u.id_usuario = e.id_usuario
                WHERE 1=1";

        if (!empty($validFilters['deporte'])) {
            $sql .= " AND (e.deporte ILIKE $".$i." OR e.deportes_ofrecidos ILIKE $".$i.")";
            $params[] = '%'.$validFilters['deporte'].'%';
            $i++;
        }
        if (isset($validFilters['precio_max']) && $validFilters['precio_max'] !== '') {
            $sql .= " AND COALESCE(e.precio, e.mensualidad, 0) <= $".$i;
            $params[] = $validFilters['precio_max'];
            $i++;
        }
        if (!empty($validFilters['ubicacion'])) {
            $sql .= " AND (e.ubicacion ILIKE $".$i." OR e.direccion ILIKE $".$i.")";
            $params[] = '%'.$validFilters['ubicacion'].'%';
            $i++;
        }
        if (!empty($validFilters['dias'])) {
            $sql .= " AND e.dias ILIKE $".$i;
            $params[] = '%'.$validFilters['dias'].'%';
            $i++;
        }
    } else {
        $sql = "SELECT u.id_usuario,
                       u.nombre,
                       m.especialidad AS deporte,
                       m.precio,
                       m.ubicacion,
                       m.telefono,
                       m.dias,
                       m.descripcion,
                       m.experiencia,
                       m.foto,
                       'maestro' AS tipo
                FROM usuario u
                INNER JOIN maestro m ON u.id_usuario = m.id_usuario
                WHERE 1=1";

        if (!empty($validFilters['deporte'])) {
            $sql .= " AND m.especialidad ILIKE $".$i;
            $params[] = '%'.$validFilters['deporte'].'%';
            $i++;
        }
        if (isset($validFilters['precio_max']) && $validFilters['precio_max'] !== '') {
            $sql .= " AND m.precio <= $".$i;
            $params[] = $validFilters['precio_max'];
            $i++;
        }
        if (!empty($validFilters['ubicacion'])) {
            $sql .= " AND m.ubicacion ILIKE $".$i;
            $params[] = '%'.$validFilters['ubicacion'].'%';
            $i++;
        }
        if (!empty($validFilters['dias'])) {
            $sql .= " AND m.dias ILIKE $".$i;
            $params[] = '%'.$validFilters['dias'].'%';
            $i++;
        }
    }

    $sql .= " ORDER BY u.fecha_registro DESC LIMIT 100";

    $result = pg_query_params($conexion, $sql, $params);
    if (!$result) return [];

    $rows = [];
    while ($r = pg_fetch_assoc($result)) $rows[] = $r;
    return $rows;
}

/**
 * Devuelve los datos completos del proveedor (maestro o escuela) por id.
 */
function fnGetProveedorById($conexion, int $id, string $tipo): ?array {
    if ($tipo === 'escuela') {
        $sql = "SELECT u.id_usuario, u.nombre AS representante,
                       e.nombre_escuela, e.direccion,
                       COALESCE(e.deporte, e.deportes_ofrecidos) AS deporte,
                       e.deportes_ofrecidos,
                       COALESCE(e.precio, e.mensualidad) AS precio,
                       e.mensualidad, e.ubicacion, e.telefono,
                       e.descripcion, e.dias, e.foto
                FROM usuario u INNER JOIN escuela e ON u.id_usuario = e.id_usuario
                WHERE u.id_usuario = $1";
    } else {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellidos,
                       m.especialidad, m.experiencia, m.precio,
                       m.ubicacion, m.telefono, m.descripcion, m.dias, m.foto
                FROM usuario u INNER JOIN maestro m ON u.id_usuario = m.id_usuario
                WHERE u.id_usuario = $1";
    }
    $r = pg_query_params($conexion, $sql, [$id]);
    if (!$r || pg_num_rows($r) === 0) return null;
    return pg_fetch_assoc($r);
}

/**
 * Lista las resenas de un proveedor (maestro o escuela) con nombre del autor.
 */
function fnGetResenasProveedor($conexion, int $id_proveedor): array {
    $r = pg_query_params($conexion,
        "SELECT r.calificacion, r.comentario, r.fecha,
                u.nombre AS autor, u.apellidos AS autor_ap
         FROM resena r
         INNER JOIN usuario u ON u.id_usuario = r.id_alumno
         WHERE r.id_proveedor = $1
         ORDER BY r.fecha DESC",
        [$id_proveedor]
    );
    $out = [];
    if ($r) while ($row = pg_fetch_assoc($r)) $out[] = $row;
    return $out;
}

/**
 * Lista los favoritos guardados por un alumno (datos enriquecidos).
 */
function fnGetFavoritosAlumno($conexion, int $id_alumno): array {
    $r = pg_query_params($conexion,
        "SELECT f.id_proveedor, f.tipo,
                CASE WHEN f.tipo = 'escuela' THEN e.nombre_escuela ELSE u.nombre END AS nombre,
                CASE WHEN f.tipo = 'escuela'
                     THEN COALESCE(e.deporte, e.deportes_ofrecidos)
                     ELSE m.especialidad END AS deporte,
                CASE WHEN f.tipo = 'escuela'
                     THEN COALESCE(e.precio, e.mensualidad)
                     ELSE m.precio END AS precio,
                CASE WHEN f.tipo = 'escuela'
                     THEN COALESCE(e.ubicacion, e.direccion)
                     ELSE m.ubicacion END AS ubicacion,
                CASE WHEN f.tipo = 'escuela' THEN e.telefono ELSE m.telefono END AS telefono
         FROM favorito f
         INNER JOIN usuario u ON u.id_usuario = f.id_proveedor
         LEFT JOIN maestro m  ON m.id_usuario = f.id_proveedor
         LEFT JOIN escuela e  ON e.id_usuario = f.id_proveedor
         WHERE f.id_alumno = $1
         ORDER BY f.fecha DESC",
        [$id_alumno]
    );
    $out = [];
    if ($r) while ($row = pg_fetch_assoc($r)) $out[] = $row;
    return $out;
}
