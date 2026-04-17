<div class="section">
    <!-- Popup automàtic per alertes pendents -->
    <?php
    $pendents = $conn->query("
        SELECT COUNT(*) AS total, 
               GROUP_CONCAT(missatge SEPARATOR '\\n\\n') AS missatges
        FROM Alerta 
        WHERE estat = 'Pendent'
    ");
    $row = $pendents->fetch_assoc();
    $num_pendents = $row['total'] ?? 0;
    $missatges = $row['missatges'] ?? '';

    // Escapem tot per JavaScript
    $missatges_js = addslashes(nl2br($missatges));
    $missatges_js = str_replace(["\r\n", "\r", "\n"], '<br>', $missatges_js);

    if ($num_pendents > 0):
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '⚠️ Alertes pendents!',
                    html: `<strong>Hi ha ${<?= $num_pendents ?>} alerta/es pendent/s:</strong><br><br>${'<?= $missatges_js ?>'}`,
                    icon: 'warning',
                    confirmButtonText: '<i class="fa-solid fa-check"></i> He vist les alertes – Marca com vistes',
                    showCancelButton: true,
                    cancelButtonText: '<i class="fa-solid fa-xmark"></i> Tancar (no marcar)',
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#ef4444',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '';
                        form.innerHTML = `
                            <input type="hidden" name="p" value="sensors">
                            <input type="hidden" name="marcar_vistes" value="1">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        </script>
    <?php endif; ?>

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
                        $sectors = $conn->query("SELECT id_sector, CONCAT('Sector ', id_sector, ' - ', p.nom) AS descripcio 
                                                 FROM Sector_Cultiu s 
                                                 JOIN Parcel·la p ON s.id_parcela = p.id_parcela 
                                                 ORDER BY p.nom");
                        while ($s = $sectors->fetch_assoc()) {
                            echo '<option value="' . $s['id_sector'] . '">' . htmlspecialchars($s['descripcio']) . '</option>';
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

    <h3><i class="fa-solid fa-microchip" style="margin-right:8px; color:var(--text-muted);"></i> Inventari de Sensors</h3>
    <?php
    $sensors_list = $conn->query("
        SELECT id_sensor, id_sector, tipus_sensor, ubicacio, data_instalacio, lectures
        FROM Sensor
        ORDER BY data_instalacio DESC
    ");

    if ($sensors_list && $sensors_list->num_rows > 0):
    ?>
        <div class="table-container" style="margin-bottom: 50px;">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Sector</th>
                    <th>Tipus sensor</th>
                    <th>Ubicació</th>
                    <th>Data instal.</th>
                    <th>Lectures recents</th>
                    <th>Acció</th>
                </tr>
                <?php while ($sen = $sensors_list->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= htmlspecialchars($sen['id_sensor'] ?? '-') ?></strong></td>
                        <td>Sector <?= htmlspecialchars($sen['id_sector'] ?? '-') ?></td>
                        <td><span class="badge badge-secondary"><i class="fa-solid fa-satellite-dish"></i> <?= htmlspecialchars($sen['tipus_sensor'] ?? '-') ?></span></td>
                        <td><?= htmlspecialchars($sen['ubicacio'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($sen['data_instalacio'] ?? '-') ?></td>
                        <td><small><?= nl2br(htmlspecialchars($sen['lectures'] ?? '-')) ?></small></td>
                        <td>
                            <a href="?eliminar=1&tipus=sensor&id=<?= $sen['id_sensor'] ?>&p=sensors"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquest sensor?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted); margin-bottom:50px;"><i class="fa-solid fa-folder-open"></i> Encara no hi ha sensors actius.</p>
    <?php endif; ?>


    <!-- SECCIÓ ALERTES -->
    <h3><i class="fa-solid fa-bell" style="margin-right:8px; color:var(--danger);"></i> Registre d'Alertes del Sistema</h3>

    <?php
    $alertes = $conn->query("
        SELECT id_alerta, id_sector, tipus_alerta, data_generada, nivell_urgencia,
               missatge, canal_notificacio, id_usuari_destinatari, estat
        FROM Alerta
        ORDER BY data_generada DESC
    ");

    if ($alertes && $alertes->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Sector</th>
                    <th>Tipus d'Alerta</th>
                    <th>Generada</th>
                    <th>Urgència</th>
                    <th>Missatge</th>
                    <th>Estat</th>
                    <th>Acció</th>
                </tr>
                <?php while ($al = $alertes->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $al['id_alerta'] ?></strong></td>
                        <td>Sector <?= htmlspecialchars($al['id_sector'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($al['tipus_alerta']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($al['data_generada'])) ?></td>
                        <td>
                            <?php
                            $u = $al['nivell_urgencia'];
                            if($u === 'Crític') echo "<span class='badge badge-danger'><i class='fa-solid fa-radiation'></i> Crític</span>";
                            elseif($u === 'Alt') echo "<span class='badge badge-warning'><i class='fa-solid fa-triangle-exclamation'></i> Alt</span>";
                            else echo "<span class='badge badge-success'><i class='fa-solid fa-circle-info'></i> Baix/Normal</span>";
                            ?>
                        </td>
                        <td style="max-width: 250px; white-space: normal;"><small><?= nl2br(htmlspecialchars($al['missatge'])) ?></small></td>
                        <td>
                            <?php if ($al['estat'] === 'Pendent'): ?>
                                <span class="badge badge-warning">Pendent</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Resolta</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?eliminar=1&tipus=alerta&id=<?= $al['id_alerta'] ?>&p=sensors"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquesta alerta?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-shield-check"></i> Tot correcte. No s'han detectat alertes.</p>
    <?php
    endif;
    if (isset($_POST['nou_sensor'])) {
        $id_sector = (int)($_POST['id_sector'] ?? 0);
        $tipus_sensor = trim($_POST['tipus_sensor'] ?? '');
        $ubicacio = trim($_POST['ubicacio'] ?? '');
        $data_instalacio = $_POST['data_instalacio'] ?? null;
        $lectures = trim($_POST['lectures'] ?? '');

        if ($id_sector > 0 && $tipus_sensor && $ubicacio && $data_instalacio) {
            $stmt = $conn->prepare("INSERT INTO Sensor (id_sector, tipus_sensor, ubicacio, data_instalacio, lectures) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $id_sector, $tipus_sensor, $ubicacio, $data_instalacio, $lectures);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Sensor instal·lat correctament!";
        } else {
            $_SESSION['err'] = "Tots els camps excepte lectures són obligatoris.";
        }
    }

    if (isset($_POST['marcar_vistes'])) {
        $conn->query("UPDATE Alerta SET estat = 'Resolta' WHERE estat = 'Pendent'");
        $_SESSION['msg'] = "Alertes marcades com a vistes.";
    }
    ?>
</div>
