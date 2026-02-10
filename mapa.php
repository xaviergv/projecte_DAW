<h2>Mapa de les parcel·les</h2>
<div id="map"></div>

<script>
    // Carregar el mapa
    let map = L.map('map').setView([41.65, 1.15], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    const parceles = <?= $parceles_json ?>;

    parceles.forEach(p => {
        L.marker([p.lat, p.lon]).addTo(map)
            .bindPopup(`<b>${p.nom}</b><br>Superfície: ${p.superficie} ha`);
    });

    if (parceles.length > 0) {
        const group = L.featureGroup(parceles.map(p => L.marker([p.lat, p.lon])));
        map.fitBounds(group.getBounds());
    }
</script>
