<div class="section">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:30px;">
        <h3 style="margin:0;">
            <i class="fa-solid fa-tower-broadcast" style="color:var(--primary); margin-right:8px;"></i>
            Monitorització de Sensors IoT
        </h3>
    </div>

    <?php
    // KPI Sensors
    $q_tot = $conn->query("SELECT COUNT(*) AS total FROM Sensor");
    $tot_sen = $q_tot ? ($q_tot->fetch_assoc()['total'] ?? 0) : 0;
    
    $q_sec = $conn->query("SELECT COUNT(DISTINCT id_sector) AS sectors FROM Sensor");
    $tot_sec = $q_sec ? ($q_sec->fetch_assoc()['sectors'] ?? 0) : 0;

    $q_tip = $conn->query("SELECT COUNT(DISTINCT tipus_sensor) AS tipus FROM Sensor");
    $tot_tip = $q_tip ? ($q_tip->fetch_assoc()['tipus'] ?? 0) : 0;
    ?>
    <div class="dashboard-grid" style="margin-bottom: 30px;">
        <div class="kpi-card">
            <div class="kpi-icon" style="color:var(--info); background:rgba(59,130,246,0.1);"><i class="fa-solid fa-satellite-dish"></i></div>
            <div class="kpi-content">
                <h3>Total Sensors</h3>
                <p class="kpi-value"><?= $tot_sen ?></p>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="color:var(--success); background:rgba(16,185,129,0.1);"><i class="fa-solid fa-map-location-dot"></i></div>
            <div class="kpi-content">
                <h3>Sectors Coberts</h3>
                <p class="kpi-value"><?= $tot_sec ?></p>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="color:var(--warning); background:rgba(245,158,11,0.1);"><i class="fa-solid fa-microchip"></i></div>
            <div class="kpi-content">
                <h3>Tipus de Sensors</h3>
                <p class="kpi-value"><?= $tot_tip ?></p>
            </div>
        </div>
    </div>

    <!-- SECCIÓ SENSORS -->
    <div class="form-section">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0;"><i class="fa-solid fa-wifi" style="color:var(--primary); margin-right:8px;"></i> Afegir nou sensor IoT</h3>
        </div>
        
        <form method="post">
            <input type="hidden" name="p" value="sensors">
            <input type="hidden" name="nou_sensor" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Sector vinculat:</label>
                    <select name="id_sector" required>
                        <option value="">Selecciona sector</option>
                        <?php
                        $sectors = $conn->query("SELECT s.id_sector, CONCAT('Sector ', s.id_sector, ' - ', p.nom) AS descripcio 
                                                 FROM Sector_Cultiu s 
                                                 JOIN `Parcel·la` p ON s.id_parcela = p.id_parcela 
                                                 ORDER BY p.nom");
                        if ($sectors) {
                            while ($s = $sectors->fetch_assoc()) {
                                echo '<option value="' . $s['id_sector'] . '">' . htmlspecialchars($s['descripcio']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tipus de sensor:</label>
                    <select name="tipus_sensor" required>
                        <option value="">Selecciona tipus</option>
                        <option value="Humitat sòl">Humitat sòl</option>
                        <option value="Temperatura sòl">Temperatura sòl</option>
                        <option value="Humitat ambiental">Humitat ambiental</option>
                        <option value="Temperatura ambiental">Temperatura ambiental</option>
                        <option value="pH sòl">pH sòl</option>
                        <option value="Conductivitat elèctrica">Conductivitat elèctrica</option>
                        <option value="Pluviòmetre">Pluviòmetre</option>
                        <option value="Altres">Altres</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ubicació exacta:</label>
                    <input type="text" name="ubicacio" placeholder="ex: Sector 3, fila 5, arbre 12" required>
                </div>

                <div class="form-group">
                    <label>Data d'instal·lació:</label>
                    <input type="date" name="data_instalacio" required>
                </div>

                <div class="form-group full-width">
                    <label>Lectures inicials (opcional):</label>
                    <textarea name="lectures" rows="2" placeholder="ex: Humitat: 45%, Temp: 18.2°C - 15/02/2025 14:30"></textarea>
                </div>
            </div>

            <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Registrar sensor</button>
        </form>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; margin-top: 40px;">
        <h3 style="margin:0;"><i class="fa-solid fa-microchip" style="margin-right:8px; color:var(--text-muted);"></i> Inventari de Sensors</h3>
    </div>
    
    <?php
    $sensors_list = $conn->query("
        SELECT s.id_sensor, s.id_sector, s.tipus_sensor, s.ubicacio, s.data_instalacio, s.lectures, p.nom AS parcela_nom
        FROM Sensor s
        LEFT JOIN Sector_Cultiu sc ON s.id_sector = sc.id_sector
        LEFT JOIN `Parcel·la` p ON sc.id_parcela = p.id_parcela
        ORDER BY s.data_instalacio DESC
    ");

    if ($sensors_list && $sensors_list->num_rows > 0):
    ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 50px;">
            <?php while ($sen = $sensors_list->fetch_assoc()): ?>
                <div style="border: 1px solid var(--border-color); background: #fff; border-radius: 8px; padding: 20px; position:relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <span class="badge badge-secondary" style="font-size: 0.8rem; background: var(--bg-color); color: var(--text-main); border: 1px solid var(--border-color);"><i class="fa-solid fa-satellite-dish" style="color: var(--primary);"></i> <?= htmlspecialchars($sen['tipus_sensor'] ?? '-') ?></span>
                        <small style="color: var(--text-muted);"><i class="fa-solid fa-calendar-day"></i> <?= date('d/m/Y', strtotime($sen['data_instalacio'])) ?></small>
                    </div>
                    <h4 style="margin: 0 0 10px 0; color: var(--text-main);">Sensor #<?= htmlspecialchars($sen['id_sensor']) ?></h4>
                    <p style="margin: 0 0 15px 0; font-size: 0.95rem; color: var(--text-muted); line-height: 1.5;">
                        <i class="fa-solid fa-map-pin" style="margin-right: 5px;"></i> <strong>Sector <?= htmlspecialchars($sen['id_sector']) ?></strong> <br>
                        <span style="font-size: 0.85rem; margin-left: 18px; color: var(--text-light);"><?= htmlspecialchars($sen['parcela_nom'] ?? 'Desconegut') ?></span><br>
                        <span style="margin-left: 18px; font-size: 0.9rem;"><?= htmlspecialchars($sen['ubicacio'] ?? '-') ?></span>
                    </p>
                    <?php if (!empty($sen['lectures'])): ?>
                        <div style="background: var(--bg-color); padding: 10px; border-radius: 6px; font-size: 0.85rem; font-family: monospace; color: var(--text-main); margin-bottom: 15px; border-left: 3px solid var(--info);">
                            <?= nl2br(htmlspecialchars($sen['lectures'])) ?>
                        </div>
                    <?php endif; ?>
                    <div style="text-align: right; margin-top: auto;">
                        <a href="?eliminar=1&tipus=sensor&id=<?= $sen['id_sensor'] ?>&p=sensors"
                           class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquest sensor?');" title="Eliminar sensor" style="padding: 6px 12px; font-size: 0.85rem; border-radius: 4px;">
                            <i class="fa-solid fa-trash-can"></i> Eliminar
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted); margin-bottom:50px;"><i class="fa-solid fa-folder-open"></i> Encara no hi ha sensors actius.</p>
    <?php endif; ?>

    <?php
    if (isset($_POST['nou_sensor'])) {
        $id_sector = (int)($_POST['id_sector'] ?? 0);
        $tipus_sensor = trim($_POST['tipus_sensor'] ?? '');
        $ubicacio = trim($_POST['ubicacio'] ?? '');
        $data_instalacio = $_POST['data_instalacio'] ?? null;
        $lectures = trim($_POST['lectures'] ?? '');

        if ($id_sector > 0 && $tipus_sensor && $ubicacio && $data_instalacio) {
            $stmt = $conn->prepare("INSERT INTO Sensor (id_sector, tipus_sensor, ubicacio, data_instalacio, lectures) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("issss", $id_sector, $tipus_sensor, $ubicacio, $data_instalacio, $lectures);
                $stmt->execute();
                $stmt->close();
                $_SESSION['msg'] = "Sensor instal·lat correctament!";
                echo "<script>window.location.href='index.php?p=sensors';</script>";
            } else {
                $_SESSION['err'] = "Error a la base de dades.";
            }
        } else {
            $_SESSION['err'] = "Tots els camps excepte lectures són obligatoris.";
        }
    }
    ?>
</div>
