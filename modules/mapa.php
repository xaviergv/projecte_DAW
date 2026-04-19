<?php
// Obtenir totes les parcel·les amb coordenades vàlides
$parceles_mapa = $conn->query("SELECT p.id_parcela, p.nom, p.superficie, p.coordenades, p.textura,
    (SELECT COUNT(*) FROM Sector_Cultiu s WHERE s.id_parcela = p.id_parcela) as num_cultius
    FROM `Parcel·la` p
    WHERE p.coordenades IS NOT NULL AND p.coordenades != ''
    ORDER BY p.nom");

$total_parceles = $conn->query("SELECT COUNT(*) as t FROM `Parcel·la`")->fetch_assoc()['t'];
$total_amb_coords = $conn->query("SELECT COUNT(*) as t FROM `Parcel·la` WHERE coordenades IS NOT NULL AND coordenades != ''")->fetch_assoc()['t'];
$total_superficie = $conn->query("SELECT COALESCE(SUM(superficie),0) as t FROM `Parcel·la`")->fetch_assoc()['t'];

// Preparar markers per JS
$markers = [];
while ($pr = $parceles_mapa->fetch_assoc()) {
    $c = trim($pr['coordenades']);
    if (preg_match('/^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)$/', $c, $m)) {
        $markers[] = [
            'type'       => 'marker',
            'lat'        => (float)$m[1],
            'lon'        => (float)$m[2],
            'nom'        => $pr['nom'],
            'superficie' => (float)$pr['superficie'],
            'textura'    => $pr['textura'] ?? '-',
            'cultius'    => (int)$pr['num_cultius'],
            'id'         => (int)$pr['id_parcela']
        ];
    }
    else if (strpos($c, '[') === 0) {
        $arr = json_decode($c, true);
        if (is_array($arr) && count($arr) > 0) {
            $sumLat = 0; $sumLon = 0;
            foreach ($arr as $pt) { $sumLat += (float)$pt[0]; $sumLon += (float)$pt[1]; }
            $markers[] = [
                'type'       => 'polygon',
                'lat'        => $sumLat / count($arr),
                'lon'        => $sumLon / count($arr),
                'coords'     => $arr,
                'nom'        => $pr['nom'],
                'superficie' => (float)$pr['superficie'],
                'textura'    => $pr['textura'] ?? '-',
                'cultius'    => (int)$pr['num_cultius'],
                'id'         => (int)$pr['id_parcela']
            ];
        }
    }
}
$markers_json = json_encode($markers);
?>

<!-- KPIs del mapa -->
<div class="mapa-kpi-grid">
    <div class="mapa-kpi">
        <div class="mapa-kpi-icon" style="background: rgba(16,185,129,0.1); color: var(--primary);">
            <i class="fa-solid fa-map-location-dot"></i>
        </div>
        <div class="mapa-kpi-info">
            <span class="mapa-kpi-count"><?= $total_parceles ?></span>
            <span class="mapa-kpi-label">Total Parcel·les</span>
        </div>
    </div>
    <div class="mapa-kpi">
        <div class="mapa-kpi-icon" style="background: rgba(59,130,246,0.1); color: var(--info);">
            <i class="fa-solid fa-location-dot"></i>
        </div>
        <div class="mapa-kpi-info">
            <span class="mapa-kpi-count"><?= $total_amb_coords ?></span>
            <span class="mapa-kpi-label">Geolocalitzades</span>
        </div>
    </div>
    <div class="mapa-kpi">
        <div class="mapa-kpi-icon" style="background: rgba(245,158,11,0.1); color: var(--warning);">
            <i class="fa-solid fa-expand"></i>
        </div>
        <div class="mapa-kpi-info">
            <span class="mapa-kpi-count"><?= number_format($total_superficie, 1) ?> <small style="font-size:0.7rem; font-weight:500;">ha</small></span>
            <span class="mapa-kpi-label">Superfície Total</span>
        </div>
    </div>
    <div class="mapa-kpi">
        <div class="mapa-kpi-icon" style="background: rgba(139,92,246,0.1); color: #8b5cf6;">
            <i class="fa-solid fa-satellite-dish"></i>
        </div>
        <div class="mapa-kpi-info">
            <span class="mapa-kpi-count"><?= $total_amb_coords > 0 ? round(($total_amb_coords / $total_parceles) * 100) : 0 ?>%</span>
            <span class="mapa-kpi-label">Cobertura GPS</span>
        </div>
    </div>
</div>

<!-- Mapa principal -->
<div class="section mapa-section">
    <h2><i class="fa-solid fa-earth-europe" style="color:var(--primary);"></i> Mapa Interactiu de Parcel·les</h2>

    <!-- Controls del mapa -->
    <div class="mapa-controls">
        <div class="mapa-controls-left">
            <button class="mapa-ctrl-btn active" id="btnAllMarkers" onclick="showAllMarkers()">
                <i class="fa-solid fa-layer-group"></i> Totes
            </button>
            <button class="mapa-ctrl-btn" id="btnFitBounds" onclick="fitAllMarkers()">
                <i class="fa-solid fa-maximize"></i> Ajustar vista
            </button>
        </div>
        <div class="mapa-controls-right">
            <select id="mapStyle" class="mapa-select" onchange="changeMapStyle(this.value)">
                <option value="streets">🗺️ Carrers</option>
                <option value="satellite">🛰️ Satèl·lit</option>
                <option value="terrain">⛰️ Terreny</option>
                <option value="dark">🌑 Fosc</option>
            </select>
        </div>
    </div>

    <!-- Contenidor del mapa -->
    <div id="map" class="mapa-container"></div>

    <!-- Llegenda -->
    <div class="mapa-legend">
        <div class="mapa-legend-item">
            <span class="mapa-legend-dot" style="background: #10b981;"></span>
            <span>Parcel·la amb cultius</span>
        </div>
        <div class="mapa-legend-item">
            <span class="mapa-legend-dot" style="background: #f59e0b;"></span>
            <span>Parcel·la sense cultius</span>
        </div>
        <div class="mapa-legend-item">
            <span class="mapa-legend-dot" style="background: #6366f1;"></span>
            <span>Punt seleccionat</span>
        </div>
    </div>
</div>

<!-- Llista de parcel·les al mapa -->
<?php if (count($markers) > 0): ?>
<div class="section">
    <h2><i class="fa-solid fa-list-check" style="color:var(--primary);"></i> Parcel·les al Mapa</h2>
    <div class="mapa-parceles-grid">
        <?php foreach ($markers as $mk): ?>
        <div class="mapa-parcela-card" data-lat="<?= $mk['lat'] ?>" data-lon="<?= $mk['lon'] ?>" 
             onclick="flyToParcel(<?= $mk['lat'] ?>, <?= $mk['lon'] ?>, '<?= addslashes($mk['nom']) ?>')">
            <div class="mapa-parcela-card-header">
                <div class="mapa-parcela-icon <?= $mk['cultius'] > 0 ? 'has-cultius' : 'no-cultius' ?>">
                    <i class="fa-solid <?= $mk['cultius'] > 0 ? 'fa-seedling' : 'fa-map-pin' ?>"></i>
                </div>
                <div class="mapa-parcela-info">
                    <strong><?= htmlspecialchars($mk['nom']) ?></strong>
                    <small><i class="fa-solid fa-location-dot"></i> <?= $mk['lat'] ?>, <?= $mk['lon'] ?></small>
                </div>
            </div>
            <div class="mapa-parcela-card-footer">
                <span class="badge badge-success"><?= number_format($mk['superficie'], 2) ?> ha</span>
                <span class="badge <?= $mk['cultius'] > 0 ? 'badge-info' : 'badge-secondary' ?>">
                    <?= $mk['cultius'] ?> cultiu<?= $mk['cultius'] !== 1 ? 's' : '' ?>
                </span>
                <?php if ($mk['textura'] !== '-'): ?>
                <span class="badge badge-warning"><?= htmlspecialchars($mk['textura']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div class="section">
    <div class="alertes-buit">
        <i class="fa-solid fa-map-location-dot"></i>
        <h4>Cap parcel·la geolocalitzada</h4>
        <p>Afegeix coordenades a les teves parcel·les des de la <a href="?p=parceles" style="color:var(--primary); font-weight:600;">secció de Parcel·les</a> per veure-les al mapa.</p>
    </div>
</div>
<?php endif; ?>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(function() {
    // Dades dels markers
    var parceles = <?= $markers_json ?>;

    // Tile layers disponibles
    var tileLayers = {
        streets: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }),
        satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '&copy; Esri',
            maxZoom: 18
        }),
        terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenTopoMap',
            maxZoom: 17
        }),
        dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CartoDB',
            maxZoom: 19
        })
    };

    // Inicialitzar mapa
    var defaultCenter = [41.3851, 2.1734];
    var defaultZoom = 13;

    if (parceles.length > 0) {
        defaultCenter = [parceles[0].lat, parceles[0].lon];
    }

    var map = L.map('map', {
        zoomControl: false
    }).setView(defaultCenter, defaultZoom);

    // Zoom control a dalt a la dreta
    L.control.zoom({ position: 'topright' }).addTo(map);

    // Escala
    L.control.scale({ imperial: false, position: 'bottomleft' }).addTo(map);

    var currentLayer = tileLayers.streets;
    currentLayer.addTo(map);

    // Custom icons
    function createIcon(color) {
        return L.divIcon({
            className: 'custom-marker',
            html: '<div style="background:' + color + '; width:32px; height:32px; border-radius:50% 50% 50% 0; transform:rotate(-45deg); border:3px solid white; box-shadow:0 3px 10px rgba(0,0,0,0.3); position:relative;"><div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%) rotate(45deg); color:white; font-size:12px;"><i class="fa-solid fa-seedling"></i></div></div>',
            iconSize: [32, 42],
            iconAnchor: [16, 42],
            popupAnchor: [0, -42]
        });
    }

    var greenIcon = createIcon('#10b981');
    var yellowIcon = createIcon('#f59e0b');
    var purpleIcon = createIcon('#6366f1');

    // Afegir markers
    var markersArray = [];
    var markersGroup = L.featureGroup();

    parceles.forEach(function(p) {
        var icon = p.cultius > 0 ? greenIcon : yellowIcon;
        var popupContent = 
            '<div style="font-family:Inter,sans-serif; min-width:200px;">' +
                '<div style="font-size:1.05rem; font-weight:700; color:#1e293b; margin-bottom:8px; display:flex; align-items:center; gap:6px;">' +
                    '<i class="fa-solid fa-map-pin" style="color:#10b981;"></i> ' + p.nom +
                '</div>' +
                '<div style="display:grid; grid-template-columns:1fr 1fr; gap:6px 12px; font-size:0.85rem;">' +
                    '<div><span style="color:#64748b;">Superfície</span><br><strong>' + p.superficie.toFixed(2) + ' ha</strong></div>' +
                    '<div><span style="color:#64748b;">Textura</span><br><strong>' + p.textura + '</strong></div>' +
                    '<div><span style="color:#64748b;">Cultius actius</span><br><strong>' + p.cultius + '</strong></div>' +
                    '<div><span style="color:#64748b;">Coordenades</span><br><strong style="font-size:0.75rem;">' + p.lat.toFixed(4) + ', ' + p.lon.toFixed(4) + '</strong></div>' +
                '</div>' +
                '<div style="margin-top:10px; padding-top:8px; border-top:1px solid #e2e8f0;">' +
                    '<a href="?p=parceles" style="color:#10b981; font-weight:600; font-size:0.82rem; text-decoration:none; display:flex; align-items:center; gap:4px;">' +
                        '<i class="fa-solid fa-arrow-right"></i> Veure detalls de la parcel·la' +
                    '</a>' +
                '</div>' +
            '</div>';

        if (p.type === 'polygon' && p.coords) {
            var poly = L.polygon(p.coords, {color: (p.cultius > 0 ? '#10b981' : '#f59e0b'), weight: 3, fillOpacity: 0.2})
                .bindPopup(popupContent, { maxWidth: 280, className: 'custom-popup' });
            poly._parcelData = p;
            markersGroup.addLayer(poly);
        }

        var marker = L.marker([p.lat, p.lon], { icon: icon })
            .bindPopup(popupContent, { maxWidth: 280, className: 'custom-popup' });

        marker._parcelData = p;
        markersArray.push(marker);
        markersGroup.addLayer(marker);
    });

    markersGroup.addTo(map);

    // Ajustar vista a tots els markers
    if (parceles.length > 0) {
        map.fitBounds(markersGroup.getBounds().pad(0.15));
    }

    // Funcions globals
    window.fitAllMarkers = function() {
        if (parceles.length > 0) {
            map.fitBounds(markersGroup.getBounds().pad(0.15));
        }
    };

    window.showAllMarkers = function() {
        window.fitAllMarkers();
    };

    window.changeMapStyle = function(style) {
        map.removeLayer(currentLayer);
        currentLayer = tileLayers[style] || tileLayers.streets;
        currentLayer.addTo(map);
    };

    window.flyToParcel = function(lat, lon, nom) {
        map.flyTo([lat, lon], 16, { duration: 1.5 });
        
        // Obrir popup del marker corresponent
        markersArray.forEach(function(m) {
            if (m._parcelData.lat === lat && m._parcelData.lon === lon) {
                setTimeout(function() {
                    m.openPopup();
                    // Canviar icona temporalment
                    m.setIcon(purpleIcon);
                    setTimeout(function() {
                        m.setIcon(m._parcelData.cultius > 0 ? greenIcon : yellowIcon);
                    }, 3000);
                }, 800);
            }
        });

        // Destacar la targeta
        document.querySelectorAll('.mapa-parcela-card').forEach(function(card) {
            card.classList.remove('mapa-card-active');
        });
        event.currentTarget.classList.add('mapa-card-active');
    };

    // Invalidar mida del mapa quan la finestra canvia
    setTimeout(function() { map.invalidateSize(); }, 200);
    window.addEventListener('resize', function() { map.invalidateSize(); });
})();
</script>
