<div class="section">
    <!-- AFEGIR NOU CULTIU -->
    <div class="form-section">
        <h3><i class="fa-solid fa-circle-plus" style="color:var(--primary); margin-right:8px;"></i> Afegir nou cultiu</h3>
        <form method="post">
            <input type="hidden" name="p" value="cultius">
            <input type="hidden" name="nou_cultiu" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Nom comú:</label>
                    <input type="text" name="nom_comu" required placeholder="ex: Poma">
                </div>

                <div class="form-group">
                    <label>Nom científic:</label>
                    <input type="text" name="nom_cientific" placeholder="ex: Malus domestica">
                </div>

                <div class="form-group">
                    <label>Cicle vegetatiu:</label>
                    <input type="text" name="cicle_vegetatiu" placeholder="ex: Anual / Perenne">
                </div>

                <div class="form-group">
                    <label>Pol·linització:</label>
                    <input type="text" name="pol·linitzacio" placeholder="ex: Creuada / Autòctona">
                </div>

                <div class="form-group full-width">
                    <label>Qualitats del fruit:</label>
                    <textarea name="qualitats_fruit" rows="2" placeholder="ex: Dolç, cruixent..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Afegir cultiu</button>
        </form>
    </div>

    <?php
    // Guardar nou cultiu
    if (isset($_POST['nou_cultiu'])) {
        $nom_comu         = trim($_POST['nom_comu'] ?? '');
        $nom_cientific    = trim($_POST['nom_cientific'] ?? '');
        $cicle_vegetatiu  = trim($_POST['cicle_vegetatiu'] ?? '');
        $qualitats_fruit  = trim($_POST['qualitats_fruit'] ?? '');
        $pol_linitzacio   = trim($_POST['pol·linitzacio'] ?? '');

        if ($nom_comu) {
            $stmt = $conn->prepare("
                INSERT INTO Cultiu 
                (nom_comu, nom_cientific, cicle_vegetatiu, qualitats_fruit, pol·linitzacio)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssss", $nom_comu, $nom_cientific, $cicle_vegetatiu, $qualitats_fruit, $pol_linitzacio);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Cultiu afegit correctament!";
        } else {
            $_SESSION['err'] = "El nom comú és obligatori";
        }
    }

    // Eliminar cultiu
    if (isset($_GET['eliminar_cultiu'])) {
        $id = (int)$_GET['id'];
        $conn->query("DELETE FROM Cultiu WHERE id_cultiu = $id");
        $_SESSION['msg'] = "Cultiu eliminat!";
        header("Location: index.php?p=cultius");
        exit;
    }
    ?>

    <!-- Llistat de cultius -->
    <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Llistat de cultius</h3>
    <?php
    $cultius = $conn->query("SELECT * FROM Cultiu ORDER BY nom_comu");
    if ($cultius->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nom comú</th>
                    <th>Nom científic</th>
                    <th>Cicle vegetatiu</th>
                    <th>Qualitats fruit</th>
                    <th>Pol·linització</th>
                    <th>Acció</th>
                </tr>
                <?php while ($c = $cultius->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $c['id_cultiu'] ?></strong></td>
                        <td><?= htmlspecialchars($c['nom_comu']) ?></td>
                        <td><?= htmlspecialchars($c['nom_cientific'] ?? '-') ?></td>
                        <td><span class="badge badge-secondary"><?= htmlspecialchars($c['cicle_vegetatiu'] ?? '-') ?></span></td>
                        <td><?= nl2br(htmlspecialchars($c['qualitats_fruit'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars($c['pol·linitzacio'] ?? '-') ?></td>
                        <td>
                            <a href="?p=cultius&eliminar_cultiu=1&id=<?= $c['id_cultiu'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquest cultiu?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha cultius registrats.</p>
    <?php endif; ?>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <!-- AFEGIR NOU SECTOR -->
    <div class="form-section">
        <h3><i class="fa-solid fa-chart-pie" style="color:var(--warning); margin-right:8px;"></i> Afegir nou sector</h3>
        <form method="post">
            <input type="hidden" name="p" value="cultius">
            <input type="hidden" name="nou_sector" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Parcel·la:</label>
                    <select name="id_parcela" required>
                        <option value="">Selecciona parcel·la</option>
                        <?php
                        $parceles = $conn->query("SELECT id_parcela, nom FROM Parcel·la ORDER BY nom");
                        while ($pr = $parceles->fetch_assoc()) {
                            echo '<option value="' . $pr['id_parcela'] . '">' . htmlspecialchars($pr['nom']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Varietat:</label>
                    <select name="id_varietat" required>
                        <option value="">Selecciona varietat</option>
                        <?php
                        $varietats = $conn->query("SELECT id_varietat, nom_varietat FROM Varietat ORDER BY nom_varietat");
                        while ($v = $varietats->fetch_assoc()) {
                            echo '<option value="' . $v['id_varietat'] . '">' . htmlspecialchars($v['nom_varietat']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data plantació:</label>
                    <input type="date" name="data_plantacio">
                </div>

                <div class="form-group">
                    <label>Marc files:</label>
                    <input type="text" name="marc_plantacio_files" placeholder="ex: 5x2">
                </div>

                <div class="form-group">
                    <label>Marc arbres:</label>
                    <input type="text" name="marc_plantacio_arbres" placeholder="ex: 6x7">
                </div>

                <div class="form-group">
                    <label>Nº d'arbres:</label>
                    <input type="number" name="num_arbres" min="0">
                </div>

                <div class="form-group">
                    <label>Previsió prod.:</label>
                    <input type="text" name="previsio_produccio" placeholder="ex: 13000 kg">
                </div>

                <div class="form-group">
                    <label>Coordenades sector:</label>
                    <input type="text" name="coordenades_sector" placeholder="ex: 41.3851, 2.1734">
                </div>
            </div>

            <button type="submit" class="btn" style="background:var(--warning); color:#fff;"><i class="fa-solid fa-plus"></i> Afegir sector</button>
        </form>
    </div>

    <?php
    // Guardar nou sector
    if (isset($_POST['nou_sector'])) {
        $id_parcela       = (int)($_POST['id_parcela'] ?? 0);
        $id_varietat      = (int)($_POST['id_varietat'] ?? 0);
        $data_plantacio   = $_POST['data_plantacio'] ?? null;
        $marc_files       = trim($_POST['marc_plantacio_files'] ?? '');
        $marc_arbres      = trim($_POST['marc_plantacio_arbres'] ?? '');
        $num_arbres       = (int)($_POST['num_arbres'] ?? 0);
        $previsio         = trim($_POST['previsio_produccio'] ?? '');
        $coordenades      = trim($_POST['coordenades_sector'] ?? '');

        if ($id_parcela > 0 && $id_varietat > 0) {
            $stmt = $conn->prepare("
                INSERT INTO Sector_Cultiu 
                (id_parcela, id_varietat, data_plantacio, marc_plantacio_files, marc_plantacio_arbres, 
                 num_arbres, previsio_produccio, coordenades_sector)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iisssiss", $id_parcela, $id_varietat, $data_plantacio, $marc_files, $marc_arbres, 
                              $num_arbres, $previsio, $coordenades);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Sector afegit correctament!";
        } else {
            $_SESSION['err'] = "Cal seleccionar parcel·la i varietat";
        }
    }
    ?>

    <!-- Llistat de sectors -->
    <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Llistat de sectors vinculats</h3>
    <?php
    $sectors = $conn->query("
        SELECT s.id_sector, p.nom AS parcela, v.nom_varietat AS varietat, 
               s.data_plantacio, s.marc_plantacio_files, s.marc_plantacio_arbres, 
               s.num_arbres, s.origen_material, s.sistema_formacio, s.inversio_inicial, 
               s.previsio_produccio, s.coordenades_sector
        FROM Sector_Cultiu s
        JOIN Parcel·la p ON s.id_parcela = p.id_parcela
        JOIN Varietat v ON s.id_varietat = v.id_varietat
        ORDER BY p.nom, v.nom_varietat
    ");

    if ($sectors->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID Sector</th>
                    <th>Parcel·la</th>
                    <th>Varietat</th>
                    <th>Data plantació</th>
                    <th>Marc files</th>
                    <th>Marc arbres</th>
                    <th>Nº arbres</th>
                    <th>Previsió producció</th>
                    <th>Coordenades</th>
                </tr>
                <?php while ($s = $sectors->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $s['id_sector'] ?></strong></td>
                        <td><span class="badge badge-info"><i class="fa-solid fa-map-location"></i> <?= htmlspecialchars($s['parcela']) ?></span></td>
                        <td><?= htmlspecialchars($s['varietat']) ?></td>
                        <td><?= htmlspecialchars($s['data_plantacio'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($s['marc_plantacio_files'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($s['marc_plantacio_arbres'] ?? '-') ?></td>
                        <td><?= $s['num_arbres'] ?? '-' ?></td>
                        <td><?= htmlspecialchars($s['previsio_produccio'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($s['coordenades_sector'] ?? '-') ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha sectors registrats.</p>
    <?php endif; ?>
</div>
