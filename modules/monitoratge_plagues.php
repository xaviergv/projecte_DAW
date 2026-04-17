<?php
// ────────────────────────────────────────────────
// AUTO-CREACIÓ DE LA TAULA TRACTAMENTS_OFICIALS
// ────────────────────────────────────────────────
$conn->query("
    CREATE TABLE IF NOT EXISTS `TractamentsOficials` (
        `id_tractament` int(11) NOT NULL AUTO_INCREMENT,
        `data` date NOT NULL,
        `parcela_id` int(11) NOT NULL,
        `producte_id` int(11) NOT NULL,
        `dosi` decimal(10,2) NOT NULL,
        `operari` varchar(150) DEFAULT NULL,
        `maquina` varchar(100) DEFAULT NULL,
        `termini_seguretat` int(11) DEFAULT 0,
        `observacions` text DEFAULT NULL,
        PRIMARY KEY (`id_tractament`),
        KEY `parcela_id` (`parcela_id`),
        KEY `producte_id` (`producte_id`),
        CONSTRAINT `fk_tractament_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `Parcel·la` (`id_parcela`) ON DELETE CASCADE,
        CONSTRAINT `fk_tractament_producte` FOREIGN KEY (`producte_id`) REFERENCES `Producte` (`id_producte`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
");
?>
<div class="section">
    <!-- 1. MONITORATGE DE PLAGUES -->
    <div class="form-section">
        <h3><i class="fa-solid fa-spider" style="color:var(--primary); margin-right:8px;"></i> Afegir observació de plagues</h3>
        <form method="post">
            <input type="hidden" name="p" value="monitoratge_plagues">
            <input type="hidden" name="nou_monitoratge" value="1">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Sector vinculat:</label>
                    <select name="id_sector" required>
                        <option value="">Selecciona sector</option>
                        <?php
                        $sectors = $conn->query("SELECT id_sector FROM Sector_Cultiu ORDER BY id_sector");
                        while ($s = $sectors->fetch_assoc()) {
                            echo '<option value="' . $s['id_sector'] . '">Sector ' . $s['id_sector'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Fila (opcional):</label>
                    <input type="number" name="id_fila" min="0">
                </div>

                <div class="form-group">
                    <label>Data d'observació:</label>
                    <input type="date" name="data_observacio" required>
                </div>

                <div class="form-group">
                    <label>Tipus de plaga:</label>
                    <input type="text" name="tipus_plaga" required placeholder="ex: Mosca de la fruita">
                </div>

                <div class="form-group">
                    <label>Nivell de població:</label>
                    <select name="nivell_poblacio" required>
                        <option value="">Selecciona</option>
                        <option value="Baix">Baix</option>
                        <option value="Mitjà">Mitjà</option>
                        <option value="Alt">Alt</option>
                        <option value="Molt alt">Molt alt</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tipus de trampa utilitzada:</label>
                    <input type="text" name="tipus_trampa" placeholder="ex: Trampa groga">
                </div>

                <div class="form-group full-width">
                    <label>Observacions:</label>
                    <textarea name="observacions" rows="2"></textarea>
                </div>
            </div>

            <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Registrar observació</button>
        </form>
    </div>

    <?php
    if (isset($_POST['nou_monitoratge'])) {
        $id_sector       = (int)($_POST['id_sector'] ?? 0);
        $id_fila         = (int)($_POST['id_fila'] ?? 0);
        $data_observacio = $_POST['data_observacio'] ?? null;
        $tipus_plaga     = trim($_POST['tipus_plaga'] ?? '');
        $nivell_poblacio = trim($_POST['nivell_poblacio'] ?? '');
        $tipus_trampa    = trim($_POST['tipus_trampa'] ?? '');
        $observacions    = trim($_POST['observacions'] ?? '');

        if ($id_sector > 0 && $data_observacio && $tipus_plaga && $nivell_poblacio) {
            $stmt = $conn->prepare("INSERT INTO Monitoratge_Plagues (id_sector, id_fila, data_observacio, tipus_plaga, nivell_poblacio, tipus_trampa, observacions) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss", $id_sector, $id_fila, $data_observacio, $tipus_plaga, $nivell_poblacio, $tipus_trampa, $observacions);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Observació afegida correctament!";
        } else {
            $_SESSION['err'] = "Sector, data, tipus plaga i nivell són obligatoris";
        }
    }
    ?>

    <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Històric de monitoratge de plagues</h3>
    <?php
    $monitoratges = $conn->query("
        SELECT m.id_monitoratge, p.nom AS parcela, m.id_fila, m.data_observacio, 
               m.tipus_plaga, m.nivell_poblacio, m.tipus_trampa, m.observacions
        FROM Monitoratge_Plagues m
        JOIN Sector_Cultiu s ON m.id_sector = s.id_sector
        JOIN Parcel·la p ON s.id_parcela = p.id_parcela
        ORDER BY m.data_observacio DESC
    ");
    if ($monitoratges->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Parcel·la</th>
                    <th>Fila</th>
                    <th>Data</th>
                    <th>Tipus plaga</th>
                    <th>Nivell</th>
                    <th>Trampa</th>
                    <th>Acció</th>
                </tr>
                <?php while ($m = $monitoratges->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $m['id_monitoratge'] ?></strong></td>
                        <td><?= htmlspecialchars($m['parcela']) ?></td>
                        <td><?= $m['id_fila'] ?: '-' ?></td>
                        <td><?= $m['data_observacio'] ?></td>
                        <td><?= htmlspecialchars($m['tipus_plaga']) ?></td>
                        <td>
                            <?php
                                $n = $m['nivell_poblacio'];
                                $c = 'badge-secondary';
                                if($n == 'Baix') $c = 'badge-success';
                                if($n == 'Mitjà') $c = 'badge-warning';
                                if($n == 'Alt' || $n == 'Molt alt') $c = 'badge-danger';
                                echo "<span class='badge $c'>$n</span>";
                            ?>
                        </td>
                        <td><?= htmlspecialchars($m['tipus_trampa'] ?? '-') ?></td>
                        <td>
                            <a href="?p=monitoratge_plagues&eliminar=monitoratge&id=<?= $m['id_monitoratge'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquesta observació?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha observacions de plagues.</p>
    <?php endif; ?>


    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <!-- 2. QUADERN D'EXPLOTACIÓ (TRACTAMENTS) -->
    <div class="form-section">
        <h3><i class="fa-solid fa-book-open" style="color:var(--info); margin-right:8px;"></i> Quadern d'Explotació (Tractament Oficial)</h3>
        <p style="color:var(--text-muted); margin-bottom:20px; font-size:0.9rem;">
            Registre simulat per complir amb les normatives de seguretat fito-sanitària. Es limitarà l'aplicació en base a les dosis toxicològiques permeses.
        </p>

        <form method="post">
            <input type="hidden" name="p" value="monitoratge_plagues">
            <input type="hidden" name="nou_tractament_oficial" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Data d'aplicació: <span style="color:var(--danger);">*</span></label>
                    <input type="date" name="data" required>
                </div>

                <div class="form-group">
                    <label>Parcel·la: <span style="color:var(--danger);">*</span></label>
                    <select name="parcela_id" required>
                        <option value="">Selecciona parcel·la</option>
                        <?php
                        $parceles_t = $conn->query("SELECT id_parcela, nom FROM Parcel·la ORDER BY nom");
                        while ($p_item = $parceles_t->fetch_assoc()) {
                            echo '<option value="' . $p_item['id_parcela'] . '">' . htmlspecialchars($p_item['nom']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Producte fitosanitari: <span style="color:var(--danger);">*</span></label>
                    <select name="producte_id" required>
                        <option value="">Selecciona producte</option>
                        <?php
                        $prods = $conn->query("SELECT id_producte, nom_comercial FROM Producte ORDER BY nom_comercial");
                        while ($pr = $prods->fetch_assoc()) {
                            echo '<option value="' . $pr['id_producte'] . '">' . htmlspecialchars($pr['nom_comercial']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Dosi Aplicada (ex: L/ha o Kg/ha): <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="dosi" step="0.01" min="0" required placeholder="Ex. 12.5">
                </div>

                <div class="form-group">
                    <label>Operari Aplicador / Carnet:</label>
                    <input type="text" name="operari" placeholder="Nom operari o codi carnet">
                </div>

                <div class="form-group">
                    <label>Maquinària / Equip:</label>
                    <input type="text" name="maquina" placeholder="Ex. Tractor amb atomitzador ROMA">
                </div>

                <div class="form-group">
                    <label>Termini de Seguretat (Dies):</label>
                    <input type="number" name="termini_seguretat" min="0" value="0">
                </div>

                <div class="form-group full-width">
                    <label>Observacions:</label>
                    <textarea name="observacions" rows="2" placeholder="Incidències meteorològiques o derivades..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn" style="background:var(--info); color:#fff;"><i class="fa-solid fa-signature"></i> Desar registre oficial</button>
        </form>
    </div>

    <?php
    if (isset($_POST['nou_tractament_oficial'])) {
        $data               = $_POST['data'] ?? null;
        $parcela_id         = (int)($_POST['parcela_id'] ?? 0);
        $producte_id        = (int)($_POST['producte_id'] ?? 0);
        $dosi               = (float)($_POST['dosi'] ?? 0);
        $operari            = trim($_POST['operari'] ?? '');
        $maquina            = trim($_POST['maquina'] ?? '');
        $termini_seguretat  = (int)($_POST['termini_seguretat'] ?? 0);
        $observacions       = trim($_POST['observacions'] ?? '');

        // LÍMIT SIMULAT DE DOSI
        $limit_max = 50.00;

        if ($parcela_id > 0 && $producte_id > 0 && $data && $dosi > 0) {
            if ($dosi > $limit_max) {
                $_SESSION['err'] = "REBUTJAT: La dosi introduïda ($dosi) assoleix límits de toxicitat i supera la barrera oficial legal de $limit_max permesos. Operació interrompuda per protocol CEE.";
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO TractamentsOficials 
                    (data, parcela_id, producte_id, dosi, operari, maquina, termini_seguretat, observacions)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("siidssis", $data, $parcela_id, $producte_id, $dosi, $operari, $maquina, $termini_seguretat, $observacions);
                $stmt->execute();
                $stmt->close();
                $_SESSION['msg'] = "Registre afegit satisfactòriament al Quadern d'Explotació.";
            }
        } else {
            $_SESSION['err'] = "Dades incorrectes. Assegura't d'entrar la data, parcela, producte i dosi correctament.";
        }
    }
    
    // Si estem en procés d'eliminar un tractament del quadern
    if (isset($_GET['eliminar']) && $_GET['eliminar'] === 'tractament_oficial') {
        $id_of = (int)$_GET['id'];
        $conn->query("DELETE FROM TractamentsOficials WHERE id_tractament = $id_of");
        $_SESSION['msg'] = "Registre esborrat correctament.";
        // Simulem el refresc mitjançant el script frontend, perquè el redirect no falli per sortida output
        echo "<script>window.location.href='?p=monitoratge_plagues';</script>";
        exit;
    }
    ?>

    <h3><i class="fa-solid fa-list-check" style="margin-right:8px; color:var(--text-muted);"></i> Vista global (Quadern d'Explotació)</h3>
    <?php
    $tractaments = $conn->query("
        SELECT t.id_tractament, t.data, parc.nom AS parcela_nom, pr.nom_comercial AS producte_nom, 
               t.dosi, t.operari, t.maquina, t.termini_seguretat, t.observacions
        FROM TractamentsOficials t
        JOIN Parcel·la parc ON t.parcela_id = parc.id_parcela
        JOIN Producte pr ON t.producte_id = pr.id_producte
        ORDER BY t.data DESC, t.id_tractament DESC
    ");

    if ($tractaments && $tractaments->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>Reg #</th>
                    <th>Data</th>
                    <th>Parcel·la</th>
                    <th>Producte</th>
                    <th>Dosi</th>
                    <th>Operari / Màquina</th>
                    <th>Termini Seg.</th>
                    <th>Observacions</th>
                    <th>Acció</th>
                </tr>
                <?php while ($t = $tractaments->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $t['id_tractament'] ?></strong></td>
                        <td><span style="font-weight: 500;"><?= date('d/m/Y', strtotime($t['data'])) ?></span></td>
                        <td><span class="badge badge-info"><i class="fa-solid fa-map-location"></i> <?= htmlspecialchars($t['parcela_nom']) ?></span></td>
                        <td><span class="badge badge-secondary"><?= htmlspecialchars($t['producte_nom']) ?></span></td>
                        <td><span class="badge badge-warning"><?= number_format($t['dosi'], 2) ?></span></td>
                        <td>
                            <small>Op: <?= htmlspecialchars($t['operari'] ?: '-') ?><br>Maq: <?= htmlspecialchars($t['maquina'] ?: '-') ?></small>
                        </td>
                        <td>
                            <?php if ($t['termini_seguretat'] > 0): ?>
                                <span style="color:var(--danger); font-weight: 600;"><i class="fa-solid fa-clock"></i> <?= $t['termini_seguretat'] ?> d</span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);">0 dies</span>
                            <?php endif; ?>
                        </td>
                        <td style="max-width: 150px;"><small><?= nl2br(htmlspecialchars($t['observacions'] ?? '-')) ?></small></td>
                        <td>
                            <a href="?p=monitoratge_plagues&eliminar=tractament_oficial&id=<?= $t['id_tractament'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('ATENCIÓ A L\'AUDITORIA: Voleu eliminar definitivament aquest registre oficial del Quadern?');" title="Esborrar registre">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> El quadern d'explotació està completament buit.</p>
    <?php endif; ?>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">


    <!-- 3. ANÀLISI NUTRICIONAL -->
    <div class="form-section">
        <h3><i class="fa-solid fa-flask-vial" style="color:var(--primary-dark); margin-right:8px;"></i> Afegir nova anàlisi nutricional</h3>
        <form method="post">
            <input type="hidden" name="p" value="monitoratge_plagues">
            <input type="hidden" name="nou_analisi" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Sector:</label>
                    <select name="id_sector" required>
                        <option value="">Selecciona sector</option>
                        <?php
                        $sectors = $conn->query("SELECT id_sector FROM Sector_Cultiu ORDER BY id_sector");
                        while ($s = $sectors->fetch_assoc()) {
                            echo '<option value="' . $s['id_sector'] . '">Sector ' . $s['id_sector'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tipus d'anàlisi:</label>
                    <select name="tipus_analisi" required>
                        <option value="">Selecciona tipus</option>
                        <option value="sol">Sòl</option>
                        <option value="aigua">Aigua</option>
                        <option value="foliar">Foliar</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data de l'anàlisi:</label>
                    <input type="date" name="data_analisi" required>
                </div>

                <div class="form-group full-width">
                    <label>Resultats:</label>
                    <textarea name="resultats" rows="3" placeholder="ex: pH: 7.1, Nitrogen: 45 mg/kg..."></textarea>
                </div>

                <div class="form-group full-width">
                    <label>Tendències / Notes:</label>
                    <textarea name="tendencies" rows="2"></textarea>
                </div>
            </div>

            <button type="submit" class="btn" style="background:var(--primary-dark);"><i class="fa-solid fa-vial-circle-check"></i> Registrar anàlisi</button>
        </form>
    </div>

    <?php
    if (isset($_POST['nou_analisi'])) {
        $id_sector      = (int)($_POST['id_sector'] ?? 0);
        $tipus_analisi  = trim($_POST['tipus_analisi'] ?? '');
        $data_analisi   = $_POST['data_analisi'] ?? null;
        $resultats      = trim($_POST['resultats'] ?? '');
        $tendencies     = trim($_POST['tendencies'] ?? '');

        if ($id_sector > 0 && $tipus_analisi && $data_analisi) {
            $stmt = $conn->prepare("
                INSERT INTO Analisi_Nutricional 
                (id_sector, tipus_analisi, data_analisi, resultats, tendencies)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issss", $id_sector, $tipus_analisi, $data_analisi, $resultats, $tendencies);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Anàlisi nutricional afegida correctament!";
        } else {
            $_SESSION['err'] = "Sector, tipus d'anàlisi i data són obligatoris";
        }
    }
    ?>

    <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Llistat d'anàlisis nutricionals</h3>
    <?php
    $analisis = $conn->query("
        SELECT an.id_analisi, p.nom AS parcela, an.tipus_analisi, an.data_analisi, 
               an.resultats, an.tendencies
        FROM Analisi_Nutricional an
        JOIN Sector_Cultiu s ON an.id_sector = s.id_sector
        JOIN Parcel·la p ON s.id_parcela = p.id_parcela
        ORDER BY an.data_analisi DESC
    ");
    if ($analisis->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Parcel·la</th>
                    <th>Tipus</th>
                    <th>Data</th>
                    <th>Resultats</th>
                    <th>Acció</th>
                </tr>
                <?php while ($an = $analisis->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $an['id_analisi'] ?></strong></td>
                        <td><?= htmlspecialchars($an['parcela']) ?></td>
                        <td><span class="badge badge-secondary" style="text-transform: capitalize;"><?= htmlspecialchars($an['tipus_analisi']) ?></span></td>
                        <td><?= $an['data_analisi'] ?></td>
                        <td><small><?= nl2br(htmlspecialchars($an['resultats'] ?? '-')) ?></small></td>
                        <td>
                            <a href="?p=monitoratge_plagues&eliminar=analisi&id=<?= $an['id_analisi'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquest anàlisi?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha anàlisis nutricionals.</p>
    <?php endif; ?>


    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">


    <!-- 4. CALENDARI FITOSANITARI -->
    <div class="form-section">
        <h3><i class="fa-solid fa-calendar-alt" style="color:var(--warning); margin-right:8px;"></i> Afegir entrada al calendari fitosanitari</h3>
        <form method="post">
            <input type="hidden" name="p" value="monitoratge_plagues">
            <input type="hidden" name="nou_calendari" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Sector:</label>
                    <select name="id_sector" required>
                        <option value="">Selecciona sector</option>
                        <?php
                        $sectors = $conn->query("SELECT id_sector FROM Sector_Cultiu ORDER BY id_sector");
                        while ($s = $sectors->fetch_assoc()) {
                            echo '<option value="' . $s['id_sector'] . '">Sector ' . $s['id_sector'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data planificada:</label>
                    <input type="date" name="data_planificada" required>
                </div>

                <div class="form-group">
                    <label>Estat fenològic:</label>
                    <input type="text" name="estat_fenologic" placeholder="ex: Floració, Fruitació">
                </div>

                <div class="form-group">
                    <label>Plaga / Malaltia previnguda:</label>
                    <input type="text" name="plaga_malaltia" placeholder="ex: Mildiu, Botritis">
                </div>

                <div class="form-group">
                    <label>Producte recomanat (Opcional):</label>
                    <select name="id_producte_recomanat">
                        <option value="">Cap / No actiu a l'estoc</option>
                        <?php
                        $prods = $conn->query("SELECT id_producte, nom_comercial FROM Producte ORDER BY nom_comercial");
                        while ($pr = $prods->fetch_assoc()) {
                            echo '<option value="' . $pr['id_producte'] . '">' . htmlspecialchars($pr['nom_comercial']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label>Notes estratègiques:</label>
                    <textarea name="notes" rows="2"></textarea>
                </div>
            </div>

            <button type="submit" class="btn" style="background:var(--warning); color:#fff;"><i class="fa-regular fa-calendar-plus"></i> Programar tractament</button>
        </form>
    </div>

    <?php
    if (isset($_POST['nou_calendari'])) {
        $id_sector             = (int)($_POST['id_sector'] ?? 0);
        $data_planificada      = $_POST['data_planificada'] ?? null;
        $estat_fenologic       = trim($_POST['estat_fenologic'] ?? '');
        $plaga_malaltia        = trim($_POST['plaga_malaltia'] ?? '');
        $id_producte_recomanat = (int)($_POST['id_producte_recomanat'] ?? 0);
        $notes                 = trim($_POST['notes'] ?? '');

        if ($id_sector > 0 && $data_planificada) {
            $stmt = $conn->prepare("
                INSERT INTO Calendari_Fitosanitari 
                (id_sector, data_planificada, estat_fenologic, plaga_malaltia, id_producte_recomanat, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssis", $id_sector, $data_planificada, $estat_fenologic, $plaga_malaltia, $id_producte_recomanat, $notes);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Entrada al calendari fitosanitari afegida correctament!";
        } else {
            $_SESSION['err'] = "Sector i data planificada són obligatoris";
        }
    }
    ?>

    <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Programació Fitosanitària</h3>
    <?php
    $calendari = $conn->query("
        SELECT c.id_calendari, p.nom AS parcela, c.data_planificada, c.estat_fenologic, 
               c.plaga_malaltia, pr.nom_comercial AS producte_recomanat, c.notes
        FROM Calendari_Fitosanitari c
        JOIN Sector_Cultiu s ON c.id_sector = s.id_sector
        JOIN Parcel·la p ON s.id_parcela = p.id_parcela
        LEFT JOIN Producte pr ON c.id_producte_recomanat = pr.id_producte
        ORDER BY c.data_planificada ASC
    ");
    if ($calendari->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Parcel·la</th>
                    <th>Data planificada</th>
                    <th>Estat fenològic</th>
                    <th>Prevenció</th>
                    <th>Producte</th>
                    <th>Acció</th>
                </tr>
                <?php while ($c = $calendari->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $c['id_calendari'] ?></strong></td>
                        <td><?= htmlspecialchars($c['parcela']) ?></td>
                        <td><?= $c['data_planificada'] ?></td>
                        <td><?= htmlspecialchars($c['estat_fenologic'] ?? '-') ?></td>
                        <td><span class="badge badge-warning"><?= htmlspecialchars($c['plaga_malaltia'] ?? '-') ?></span></td>
                        <td>
                            <?php if(!empty($c['producte_recomanat'])): ?>
                                <span class="badge badge-info"><?= htmlspecialchars($c['producte_recomanat']) ?></span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?p=monitoratge_plagues&eliminar=calendari&id=<?= $c['id_calendari'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquesta dada de calendari?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> No hi ha cap planificació fitosanitària activa.</p>
    <?php endif; ?>
</div>
