<div class="section">
    <h2>Sensors i Alertes</h2>

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
    $missatges_js = str_replace(["\r\n", "\r", "\n"], '<br>', $missatges_js); // Assegurem <br> per salts

    if ($num_pendents > 0):
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '⚠️ Alertes pendents!',
                    html: `<strong>Hi ha ${<?= $num_pendents ?>} alerta/es pendent/s:</strong><br><br>${'<?= $missatges_js ?>'}`,
                    icon: 'warning',
                    confirmButtonText: 'He vist les alertes – Marca com vistes',
                    showCancelButton: true,
                    cancelButtonText: 'Tancar (no marcar)',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#dc3545',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Envia el formulari per marcar com vistes
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
    <h3>Sensors</h3>

    <div class="form-section">
        <h4>Afegir nou sensor</h4>
        <form method="post">
            <input type="hidden" name="p" value="sensors">
            <input type="hidden" name="nou_sensor" value="1">

            <label>ID Sector:</label>
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

            <label>Ubicació exacta:</label>
            <input type="text" name="ubicacio" placeholder="ex: Sector 3, fila 5, arbre 12" required>

            <label>Data d'instal·lació:</label>
            <input type="date" name="data_instalacio" required>

            <label>Últimes lectures (opcional):</label>
            <textarea name="lectures" rows="3" placeholder="ex: Humitat: 45%, Temp: 18.2°C - 15/02/2025 14:30"></textarea>

            <button type="submit" class="btn">Afegir sensor</button>
        </form>
    </div>

    <?php
    $sensors_list = $conn->query("
        SELECT id_sensor, id_sector, tipus_sensor, ubicacio, data_instalacio, lectures
        FROM Sensor
        ORDER BY data_instalacio DESC
    ");

    if ($sensors_list && $sensors_list->num_rows > 0):
    ?>
        <table>
            <tr>
                <th>ID Sensor</th>
                <th>ID Sector</th>
                <th>Tipus sensor</th>
                <th>Ubicació</th>
                <th>Data instal·lació</th>
                <th>Últimes lectures</th>
                <th>Acció</th>
            </tr>
            <?php while ($sen = $sensors_list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($sen['id_sensor'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($sen['id_sector'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($sen['tipus_sensor'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($sen['ubicacio'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($sen['data_instalacio'] ?? '-') ?></td>
                    <td><?= nl2br(htmlspecialchars($sen['lectures'] ?? '-')) ?></td>
                    <td>
                        <a href="?eliminar=1&tipus=sensor&id=<?= $sen['id_sensor'] ?>&p=sensors"
                           class="btn btn-red" onclick="return confirm('Segur que vols eliminar aquest sensor?');">
                            Eliminar
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Encara no hi ha sensors registrats.</p>
    <?php endif; ?>


    <!-- SECCIÓ ALERTES -->
    <h3 style="margin-top:60px;">Llistat d'alertes</h3>

    <?php
    $alertes = $conn->query("
        SELECT id_alerta, id_sector, tipus_alerta, data_generada, nivell_urgencia,
               missatge, canal_notificacio, id_usuari_destinatari, estat
        FROM Alerta
        ORDER BY data_generada DESC
    ");

    if ($alertes && $alertes->num_rows > 0):
    ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Sector</th>
                <th>Tipus</th>
                <th>Data generada</th>
                <th>Urgència</th>
                <th>Missatge</th>
                <th>Canal</th>
                <th>Destinatari</th>
                <th>Estat</th>
                <th>Acció</th>
            </tr>
            <?php while ($al = $alertes->fetch_assoc()): ?>
                <tr>
                    <td><?= $al['id_alerta'] ?></td>
                    <td><?= htmlspecialchars($al['id_sector'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($al['tipus_alerta']) ?></td>
                    <td><?= $al['data_generada'] ?></td>
                    <td style="color: <?= $al['nivell_urgencia'] === 'Crític' ? 'red' : ($al['nivell_urgencia'] === 'Alt' ? 'orange' : 'black') ?>">
                        <?= $al['nivell_urgencia'] ?>
                    </td>
                    <td><?= nl2br(htmlspecialchars($al['missatge'])) ?></td>
                    <td><?= htmlspecialchars($al['canal_notificacio']) ?></td>
                    <td><?= $al['id_usuari_destinatari'] ?? '-' ?></td>
                    <td><?= $al['estat'] ?></td>
                    <td>
                        <a href="?eliminar=1&tipus=alerta&id=<?= $al['id_alerta'] ?>&p=sensors"
                           class="btn btn-red" onclick="return confirm('Segur que vols eliminar aquesta alerta?');">
                            Eliminar
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Encara no hi ha alertes creades.</p>
    <?php endif; ?>
</div>
