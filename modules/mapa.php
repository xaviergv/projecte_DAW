<div class="section">
    <h2>Mapa de les parcel·les</h2>

    <div id="map" style="height: 600px; border: 1px solid #ccc;"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <script>
        var map = L.map('map').setView([41.3851, 2.1734], 13); // Centre per defecte (Barcelona, pots canviar-ho)

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        <?php
        $parceles = $conn->query("SELECT id_parcela, nom, coordenades FROM Parcel·la WHERE coordenades IS NOT NULL AND coordenades != ''");
        while ($pr = $parceles->fetch_assoc()) {
            if (preg_match('/^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)$/', trim($pr['coordenades']), $m)) {
                $lat = $m[1];
                $lon = $m[2];
                echo "L.marker([$lat, $lon]).addTo(map).bindPopup('{$pr['nom']}');";
            }
        }
        ?>
    </script>
</div>
