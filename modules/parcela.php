<div class="section">
    <!-- Formulari per afegir nova parcel·la -->
    <div class="form-section">
        <h3><i class="fa-solid fa-map-location-dot" style="color:var(--primary); margin-right:8px;"></i> Afegir nova parcel·la</h3>
        <form method="post">
            <input type="hidden" name="p" value="parceles">
            <input type="hidden" name="nou_parcela" value="1">

            <div style="display:flex; flex-wrap:wrap; gap:30px; margin-top:20px;">
                <!-- Dades de camp (esquerra) -->
                <div style="flex:1; min-width:300px; display:flex; flex-direction:column; gap:20px;">
                    <div class="form-group">
                        <label>Nom de la parcel·la:</label>
                        <input type="text" name="nom" required placeholder="ex: Can Xifra">
                    </div>
                    <div class="form-group">
                        <label>Superfície (ha):</label>
                        <input type="number" name="superficie" step="0.01" min="0" required placeholder="ex: 2.5">
                    </div>
                    <div class="form-group">
                        <label>Textura del sòl:</label>
                        <select name="textura">
                            <option value="">Selecciona</option>
                            <option value="Argilosa">Argilosa</option>
                            <option value="Franca">Franca</option>
                            <option value="Arenosa">Arenosa</option>
                            <option value="Llimosa">Llimosa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Coordenades detectades:</label>
                        <input type="text" name="coordenades" id="coordInput" placeholder="S'omplirà automàticament des del mapa..." readonly style="background:#f1f5f9; color:var(--text-muted); font-size:0.85rem;">
                    </div>
                    <div style="margin-top:auto; padding-top:10px;">
                        <button type="submit" class="btn" style="width:100%; justify-content:center;"><i class="fa-solid fa-plus"></i> Guardar nova parcel·la</button>
                    </div>
                </div>

                <!-- Mapa interactiu (dreta) -->
                <div style="flex:1.5; min-width:350px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600;"><i class="fa-solid fa-earth-europe" style="color:var(--primary);"></i> Ubicació de la Finca</label>
                    
                    <!-- Leaflet CSS & JS -->
                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
                    
                    <div id="mapParcela" style="height: 380px; width: 100%; border-radius: 12px; border: 2px solid var(--border-color); box-shadow:0 4px 6px rgba(0,0,0,0.05); margin-bottom: 10px; z-index: 1;"></div>
                    <small style="color:var(--text-muted); display:inline-block; margin-top:5px; line-height:1.4;"><i class="fa-solid fa-paintbrush" style="color:var(--primary);"></i> Utilitza la barra d'eines del mapa a l'esquerra per dibuixar directament un Rectangle o Polígon sobre el camp, o clica el marcador puntual.</small>

                    <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var map = L.map('mapParcela').setView([41.5, 1.5], 8);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);

                        var drawnItems = new L.FeatureGroup();
                        map.addLayer(drawnItems);

                        var drawControl = new L.Control.Draw({
                            draw: {
                                polyline: false,
                                polygon: {
                                    allowIntersection: false,
                                    drawError: { color: '#e1e100', message: '<strong>Error:</strong> no es poden creuar línies!' },
                                    shapeOptions: { color: '#10b981' }
                                },
                                circle: false,
                                circlemarker: false,
                                marker: true,
                                rectangle: { shapeOptions: { color: '#10b981' } }
                            },
                            edit: { featureGroup: drawnItems }
                        });
                        map.addControl(drawControl);

                        map.on(L.Draw.Event.CREATED, function (e) {
                            drawnItems.clearLayers(); 
                            var layer = e.layer;
                            drawnItems.addLayer(layer);
                            
                            if (e.layerType === 'marker') {
                                var latlng = layer.getLatLng();
                                document.getElementById('coordInput').value = latlng.lat.toFixed(5) + ', ' + latlng.lng.toFixed(5);
                            } else {
                                var latlngs = layer.getLatLngs()[0];
                                var simpleCoords = latlngs.map(l => '[' + l.lat.toFixed(5) + ',' + l.lng.toFixed(5) + ']').join(',');
                                document.getElementById('coordInput').value = '[' + simpleCoords + ']';
                            }
                        });
                        setTimeout(function() { map.invalidateSize(); }, 300);
                    });
                    </script>
                </div>
            </div>
        </form>
    </div>

    <?php
    // Afegir nova parcel·la
    if (isset($_POST['nou_parcela'])) {
        $nom         = trim($_POST['nom'] ?? '');
        $superficie  = (float)($_POST['superficie'] ?? 0);
        $coordenades = trim($_POST['coordenades'] ?? '');
        $textura     = trim($_POST['textura'] ?? '');

        if ($nom && $superficie > 0) {
            $stmt = $conn->prepare("
                INSERT INTO Parcel·la 
                (nom, superficie, coordenades, textura)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("sdss", $nom, $superficie, $coordenades, $textura);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Parcel·la afegida correctament!";
        } else {
            $_SESSION['err'] = "Nom i superfície són obligatoris";
        }
    }

    // Eliminar parcel·la
    if (isset($_GET['eliminar_parcela'])) {
        $id_parcela = (int)($_GET['id'] ?? 0);
        if ($id_parcela > 0) {
            $conn->query("DELETE FROM Parcel·la WHERE id_parcela = $id_parcela");
            $_SESSION['msg'] = "Parcel·la eliminada correctament!";
        }
        header("Location: index.php?p=parceles");
        exit;
    }
    ?>

    <!-- Llistat de parcel·les amb botó Eliminar -->
    <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Llistat de parcel·les</h3>
    <?php
    $parceles = $conn->query("SELECT id_parcela, nom, superficie, coordenades, textura FROM Parcel·la ORDER BY nom");
    if ($parceles->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Superfície (ha)</th>
                    <th>Coordenades</th>
                    <th>Textura</th>
                    <th>Acció</th>
                </tr>
                <?php while ($pr = $parceles->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $pr['id_parcela'] ?></strong></td>
                        <td><?= htmlspecialchars($pr['nom']) ?></td>
                        <td><span class="badge badge-success"><?= number_format($pr['superficie'], 2) ?> ha</span></td>
                        <td><i class="fa-solid fa-location-dot" style="color:var(--text-muted); margin-right:5px;"></i> <?= htmlspecialchars($pr['coordenades'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($pr['textura'] ?? '-') ?></td>
                        <td>
                            <a href="?p=parceles&eliminar_parcela=1&id=<?= $pr['id_parcela'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminaraquesta parcel·la?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha parcel·les registrades.</p>
    <?php endif; ?>
</div>
