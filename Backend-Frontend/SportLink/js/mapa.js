/* ================================================================
   SportLink - Módulo de Mapas e Interacción Física (Plan B: Leaflet)
   Subcomponente de lógica de aplicación (SDD sección 5).
   Maneja la captura de coordenadas y geocodificación inversa.
   ================================================================ */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Referencias a los elementos del DOM definidos en buscar.php
    const latHidden = document.getElementById('latHidden');
    const lngHidden = document.getElementById('lngHidden');
    const inputUbicacion = document.getElementById('inputUbicacion');
    const btnGeo = document.getElementById('btnGeo');

    // Salir si no estamos en la página de búsqueda (evita errores en consola)
    if (!latHidden) return;

    // 2. Configuración inicial del mapa (Centrado en CUCEI, Guadalajara)
    // Coordenadas iniciales por defecto
    const inicialLat = 20.654;
    const inicialLng = -103.325;

    // Inicializar el mapa de Leaflet en el contenedor div id="map" [cite: 438]
    const map = L.map('map').setView([inicialLat, inicialLng], 14);

    // Cargar los cuadros (tiles) del mapa de OpenStreetMap 
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // 3. Crear marcador interactivo y arrastrable [cite: 141]
    const marker = L.marker([inicialLat, inicialLng], {
        draggable: true
    }).addTo(map);

    // 4. Función de Geocodificación Inversa (Nominatim API)
    // Traduce coordenadas a una dirección legible para el usuario [cite: 33]
    async function updateLocationInfo(lat, lng) {
        // Actualizar campos ocultos para enviar al backend [cite: 127]
        latHidden.value = lat;
        lngHidden.value = lng;

        try {
            // Petición gratuita a la API de Nominatim
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`);
            const data = await response.json();

            if (data.display_name) {
                // Ponemos la dirección real en el cuadro de texto para el alumno
                inputUbicacion.value = data.display_name;
            } else {
                inputUbicacion.value = `Ubicación: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
            }
        } catch (error) {
            console.error("Error en geocodificación:", error);
            inputUbicacion.value = "Ubicación seleccionada en el mapa";
        }
    }

    // --- EVENTOS INTERACTIVOS ---

    // Al terminar de arrastrar el marcador manualmente [cite: 818]
    marker.on('dragend', function (e) {
        const position = marker.getLatLng();
        updateLocationInfo(position.lat, position.lng);
    });

    // Al hacer clic en cualquier punto del mapa, mover el marcador ahí [cite: 565]
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateLocationInfo(e.latlng.lat, e.latlng.lng);
    });

    // 5. Lógica del Botón GPS (Geolocalización del navegador) [cite: 141, 818]
    if (btnGeo) {
        btnGeo.addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert("Tu navegador no soporta geolocalización.");
                return;
            }

            // Cambiar estado visual mientras se obtiene la señal [cite: 673]
            btnGeo.innerText = "⌛";
            btnGeo.disabled = true;

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const { latitude, longitude } = pos.coords;

                    // Centrar mapa y mover marcador a la posición real del alumno [cite: 818]
                    map.setView([latitude, longitude], 16);
                    marker.setLatLng([latitude, longitude]);

                    // Actualizar inputs y dirección
                    updateLocationInfo(latitude, longitude);

                    btnGeo.innerText = "✅";
                    btnGeo.disabled = false;
                },
                (err) => {
                    btnGeo.innerText = "📍";
                    btnGeo.disabled = false;
                    alert("No se pudo acceder al GPS: " + err.message);
                },
                { enableHighAccuracy: true, timeout: 5000 }
            );
        });
    }
});