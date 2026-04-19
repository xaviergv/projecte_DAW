<div class="section">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="margin:0;"><i class="fa-solid fa-seedling" style="color:var(--primary); margin-right:8px;"></i> Visió General de Cultius i Sectors</h3>
    </div>

    <!-- KPIs -->
    <div class="dashboard-grid" style="margin-bottom: 30px;">
        <div class="kpi-card">
            <?php
            $q_cultius = $conn->query("SELECT COUNT(*) AS t FROM Cultiu");
            $total_cultius = $q_cultius ? $q_cultius->fetch_assoc()['t'] : 0;
            ?>
            <div class="kpi-icon" style="color:var(--success); background:rgba(16,185,129,0.1);"><i class="fa-solid fa-leaf"></i></div>
            <div class="kpi-content">
                <h3>Total Cultius</h3>
                <p class="kpi-value"><?= $total_cultius ?></p>
            </div>
        </div>
        <div class="kpi-card">
            <?php
            $q_sectors = $conn->query("SELECT COUNT(*) AS t FROM Sector_Cultiu");
            $total_sectors = $q_sectors ? $q_sectors->fetch_assoc()['t'] : 0;
            ?>
            <div class="kpi-icon" style="color:var(--warning); background:rgba(245,158,11,0.1);"><i class="fa-solid fa-chart-pie"></i></div>
            <div class="kpi-content">
                <h3>Sectors Actius</h3>
                <p class="kpi-value"><?= $total_sectors ?></p>
            </div>
        </div>
        <div class="kpi-card">
            <?php
            $q_arbres = $conn->query("SELECT SUM(num_arbres) AS t FROM Sector_Cultiu");
            $total_arbres = $q_arbres ? $q_arbres->fetch_assoc()['t'] : 0;
            ?>
            <div class="kpi-icon" style="color:var(--primary); background:rgba(14,165,233,0.1);"><i class="fa-solid fa-tree"></i></div>
            <div class="kpi-content">
                <h3>Total Arbres</h3>
                <p class="kpi-value"><?= number_format((float)$total_arbres, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>

    <div style="display:flex; gap:30px; flex-wrap:wrap;">
        <div style="flex:1; min-width:300px;">
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
        </div>
    </div>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <div style="display:flex; gap:30px; flex-wrap:wrap;">
        <div style="flex:1; min-width:300px;">
            <!-- AFEGIR NOVA VARIETAT -->
            <div class="form-section">
                <h3><i class="fa-solid fa-seedling" style="color:var(--primary); margin-right:8px;"></i> Afegir nova varietat</h3>
                <form method="post">
                    <input type="hidden" name="p" value="cultius">
                    <input type="hidden" name="nova_varietat" value="1">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Cultiu Associat:</label>
                            <select name="id_cultiu" required>
                                <option value="">Selecciona cultiu</option>
                                <?php
                                $cultius_list = $conn->query("SELECT id_cultiu, nom_comu FROM Cultiu ORDER BY nom_comu");
                                while ($c = $cultius_list->fetch_assoc()) {
                                    echo '<option value="' . $c['id_cultiu'] . '">' . htmlspecialchars($c['nom_comu']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Nom de la varietat:</label>
                            <input type="text" name="nom_varietat" required placeholder="ex: Golden Delicious">
                        </div>

                        <div class="form-group full-width">
                            <label>Característiques:</label>
                            <textarea name="caracteristiques" rows="2" placeholder="ex: Pell groga, polpa cruixent..."></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Afegir varietat</button>
                </form>
            </div>

            <?php
            // Guardar nova varietat
            if (isset($_POST['nova_varietat'])) {
                $id_cultiu = (int)($_POST['id_cultiu'] ?? 0);
                $nom_varietat = trim($_POST['nom_varietat'] ?? '');
                $caracteristiques = trim($_POST['caracteristiques'] ?? '');

                if ($id_cultiu > 0 && $nom_varietat) {
                    $stmt = $conn->prepare("INSERT INTO Varietat (id_cultiu, nom_varietat, caracteristiques) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $id_cultiu, $nom_varietat, $caracteristiques);
                    $stmt->execute();
                    $stmt->close();
                    $_SESSION['msg'] = "Varietat afegida correctament!";
                } else {
                    $_SESSION['err'] = "El cultiu i el nom de la varietat són obligatoris";
                }
            }
            
            // Eliminar varietat
            if (isset($_GET['eliminar_varietat'])) {
                $id = (int)$_GET['id'];
                $conn->query("DELETE FROM Varietat WHERE id_varietat = $id");
                $_SESSION['msg'] = "Varietat eliminada!";
                echo "<script>window.location.href='index.php?p=cultius';</script>";
                exit;
            }
            ?>

            <!-- Llistat de varietats -->
            <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Llistat de varietats</h3>
            <?php
            $varietats = $conn->query("
                SELECT v.id_varietat, v.nom_varietat, v.caracteristiques, c.nom_comu AS cultiu 
                FROM Varietat v 
                JOIN Cultiu c ON v.id_cultiu = c.id_cultiu 
                ORDER BY c.nom_comu, v.nom_varietat
            ");
            if ($varietats->num_rows > 0):
            ?>
                <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Cultiu</th>
                            <th>Varietat</th>
                            <th>Característiques</th>
                            <th>Acció</th>
                        </tr>
                        <?php while ($v = $varietats->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $v['id_varietat'] ?></strong></td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($v['cultiu']) ?></span></td>
                                <td><?= htmlspecialchars($v['nom_varietat']) ?></td>
                                <td><small><?= htmlspecialchars($v['caracteristiques'] ?? '-') ?></small></td>
                                <td>
                                    <a href="?p=cultius&eliminar_varietat=1&id=<?= $v['id_varietat'] ?>"
                                       class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquesta varietat?');" title="Eliminar">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            <?php else: ?>
                <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha varietats registrades.</p>
            <?php endif; ?>
        </div>
    </div>

    <div style="display:flex; gap:30px; flex-wrap:wrap;">
        <div style="flex:1; min-width:300px;">
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
    </div>
</div>
