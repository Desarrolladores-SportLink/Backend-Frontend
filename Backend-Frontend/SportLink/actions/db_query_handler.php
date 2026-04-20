<?php
/**
 * SportLink - DB_QueryHandler
 * Subcomponente de persistencia (SDD seccion 5, interfaz iBEtoDB).
 * Encapsula la construccion y ejecucion de consultas SQL sobre PostgreSQL.
 */

/**
 * fnExecuteSearchQuery (SDD 5.2.3)
 * Ejecuta la busqueda dinamica aplicando filtros y calculando proximidad.
 * Maneja direcciones largas de Nominatim simplificándolas automáticamente.
 */
function fnExecuteSearchQuery($conexion, array $validFilters): array {
    $tipo = $validFilters['tipo'] ?? 'maestro';
    
    // Normalización de coordenadas para asegurar cálculos matemáticos válidos
    $latUser = (isset($validFilters['lat']) && $validFilters['lat'] !== '') ? (float)$validFilters['lat'] : null;
    $lngUser = (isset($validFilters['lng']) && $validFilters['lng'] !== '') ? (float)$validFilters['lng'] : null;
    
    // Limpieza de direcciones largas (ej. de Nominatim) para mejorar coincidencias de texto
    $ubicacionRaw = $validFilters['ubicacion'] ?? '';
    $ubicacionBusqueda = $ubicacionRaw;
    if (strlen($ubicacionRaw) > 40 && strpos($ubicacionRaw, ',') !== false) {
        $partes = explode(',', $ubicacionRaw);
        $ubicacionBusqueda = trim($partes[0]); 
    }

    $params = [];
    $i = 1;

    // 1. Lógica de Proximidad (Fórmula de Haversine)
    $distSql = "";
    if ($latUser !== null && $lngUser !== null) {
        // LEAST/GREATEST previene errores de precisión que harían fallar a acos()
        $distSql = ", (6371 * acos(LEAST(1, GREATEST(-1, cos(radians($".$i.")) * cos(radians(t.latitud)) * cos(radians(t.longitud) - radians($".($i+1).")) + sin(radians($".$i.")) * sin(radians(t.latitud)))))) AS distancia";
        $params[] = $latUser;
        $params[] = $lngUser;
        $i += 2;
    }

    // 2. Construcción de la consulta base (Usando alias 't' para el perfil)
    if ($tipo === 'escuela') {
        $sql = "SELECT u.id_usuario, u.correo, t.nombre_escuela AS nombre, 
                       COALESCE(t.deporte, t.deportes_ofrecidos) AS deporte,
                       COALESCE(t.precio, t.mensualidad) AS precio,
                       COALESCE(t.ubicacion, t.direccion) AS ubicacion,
                       t.telefono, t.red_social, t.dias, t.descripcion, t.foto,
                       'escuela' AS tipo $distSql
                FROM usuario u
                INNER JOIN escuela AS t ON u.id_usuario = t.id_usuario
                WHERE 1=1";

        if (!empty($validFilters['deporte'])) {
            $sql .= " AND (t.deporte ILIKE $".$i." OR t.deportes_ofrecidos ILIKE $".$i.")";
            $params[] = '%'.$validFilters['deporte'].'%';
            $i++;
        }
    } else {
        $sql = "SELECT u.id_usuario, u.nombre, u.correo,
                       t.especialidad AS deporte, t.precio, t.ubicacion,
                       t.telefono, t.red_social, t.dias, t.descripcion,
                       t.experiencia, t.foto, 'maestro' AS tipo $distSql
                FROM usuario u
                INNER JOIN maestro AS t ON u.id_usuario = t.id_usuario
                WHERE 1=1";

        if (!empty($validFilters['deporte'])) {
            $sql .= " AND t.especialidad ILIKE $".$i;
            $params[] = '%'.$validFilters['deporte'].'%';
            $i++;
        }
    }

    // 3. Filtro de Presupuesto Máximo
    if (isset($validFilters['precio_max']) && $validFilters['precio_max'] !== '') {
        $sql .= " AND COALESCE(t.precio, 0) <= $".$i;
        $params[] = $validFilters['precio_max'];
        $i++;
    }

    // 4. Lógica de Ubicación por Texto (Solo si NO hay coordenadas)
    if ($latUser === null || $lngUser === null) { 
        if (!empty($ubicacionBusqueda) && $ubicacionBusqueda !== 'Cerca de mi ubicación actual') {
            $sql .= " AND (t.ubicacion ILIKE $".$i." OR t.direccion ILIKE $".$i.")";
            $params[] = '%'.$ubicacionBusqueda.'%';
            $i++;
        }
    }

    // 5. Filtro de Días disponibles
    if (!empty($validFilters['dias'])) {
        $sql .= " AND t.dias ILIKE $".$i;
        $params[] = '%'.$validFilters['dias'].'%';
        $i++;
    }

    // 6. Ordenamiento: Prioridad a cercanía si hay coordenadas
    if ($latUser !== null && $lngUser !== null) {
        $sql .= " ORDER BY distancia ASC";
    } else {
        $sql .= " ORDER BY u.id_usuario DESC";
    }

    $sql .= " LIMIT 50";

    $result = pg_query_params($conexion, $sql, $params);
    if (!$result) return [];

    $rows = [];
    while ($r = pg_fetch_assoc($result)) {
        if (isset($r['distancia'])) {
            $r['distancia'] = round((float)$r['distancia'], 2);
        }
        $rows[] = $r;
    }
    return $rows;
}

/**
 * Devuelve los datos completos del proveedor (maestro o escuela) por id.
 */
function fnGetProveedorById($conexion, int $id, string $tipo): ?array {
    if ($tipo === 'escuela') {
        $sql = "SELECT u.id_usuario, u.nombre AS representante, u.correo,
                       e.nombre_escuela, e.direccion,
                       COALESCE(e.deporte, e.deportes_ofrecidos) AS deporte,
                       e.deportes_ofrecidos,
                       COALESCE(e.precio, e.mensualidad) AS precio,
                       e.mensualidad, e.ubicacion, e.telefono,
                       e.descripcion, e.dias, e.foto, e.red_social
                FROM usuario u INNER JOIN escuela e ON u.id_usuario = e.id_usuario
                WHERE u.id_usuario = $1";
    } else {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellidos, u.correo,
                       m.especialidad, m.experiencia, m.precio,
                       m.ubicacion, m.telefono, m.descripcion, m.dias, m.foto, m.red_social
                FROM usuario u INNER JOIN maestro m ON u.id_usuario = m.id_usuario
                WHERE u.id_usuario = $1";
    }
    $r = pg_query_params($conexion, $sql, [$id]);
    if (!$r || pg_num_rows($r) === 0) return null;
    return pg_fetch_assoc($r);
}

/**
 * Lista las resenas de un proveedor con nombre del autor.
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
