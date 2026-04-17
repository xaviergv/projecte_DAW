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


    <!-- 2. APLICACIÓ DE TRACTAMENT -->
    <div class="form-section">
        <h3><i class="fa-solid fa-syringe" style="color:var(--info); margin-right:8px;"></i> Afegir nova aplicació de tractament</h3>
        <form method="post">
            <input type="hidden" name="p" value="monitoratge_plagues">
            <input type="hidden" name="nou_aplicacio" value="1">

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
                    <label>Fila (opcional):</label>
                    <input type="number" name="id_fila" min="0">
                </div>

                <div class="form-group">
                    <label>Producte:</label>
                    <select name="id_producte" required>
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
                    <label>Data i hora:</label>
                    <input type="datetime-local" name="data_hora" required>
                </div>

                <div class="form-group">
                    <label>Quantitat aplicada (L o Kg):</label>
                    <input type="number" name="quantitat_aplicada" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label>Mètode d'aplicació:</label>
                    <input type="text" name="metode_aplicacio" placeholder="ex: Polvorització">
                </div>

                <div class="form-group full-width">
                    <label>Observacions:</label>
                    <textarea name="observacions" rows="2"></textarea>
                </div>
            </div>

            <button type="submit" class="btn" style="background:var(--info); color:#fff;"><i class="fa-solid fa-spray-can-sparkles"></i> Afegir tractament</button>
        </form>
    </div>

    <?php
    if (isset($_POST['nou_aplicacio'])) {
        $id_sector          = (int)($_POST['id_sector'] ?? 0);
        $id_fila            = (int)($_POST['id_fila'] ?? 0);
        $id_producte        = (int)($_POST['id_producte'] ?? 0);
        $data_hora          = $_POST['data_hora'] ?? null;
        $quantitat_aplicada = (float)($_POST['quantitat_aplicada'] ?? 0);
        $metode_aplicacio   = trim($_POST['metode_aplicacio'] ?? '');
        $observacions       = trim($_POST['observacions'] ?? '');

        if ($id_sector > 0 && $id_producte > 0 && $data_hora && $quantitat_aplicada > 0) {
            $stmt = $conn->prepare("
                INSERT INTO Aplicacio_Tractament 
                (id_sector, id_fila, id_producte, data_hora, quantitat_aplicada, metode_aplicacio, observacions)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiisdss", $id_sector, $id_fila, $id_producte, $data_hora, $quantitat_aplicada, $metode_aplicacio, $observacions);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Aplicació de tractament registrada correctament!";
        } else {
            $_SESSION['err'] = "Sector, producte, data/hora i quantitat són obligatoris";
        }
    }
    ?>

    <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Llistat d'aplicacions de tractament</h3>
    <?php
    $aplicacions = $conn->query("
        SELECT a.id_aplicacio, p.nom AS parcela, a.id_fila, a.data_hora, pr.nom_comercial AS producte, 
               a.quantitat_aplicada, a.metode_aplicacio, a.observacions
        FROM Aplicacio_Tractament a
        JOIN Sector_Cultiu s ON a.id_sector = s.id_sector
        JOIN Parcel·la p ON s.id_parcela = p.id_parcela
        JOIN Producte pr ON a.id_producte = pr.id_producte
        ORDER BY a.data_hora DESC
    ");
    if ($aplicacions->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Parcel·la</th>
                    <th>Fila</th>
                    <th>Data/Hora</th>
                    <th>Producte</th>
                    <th>Quantitat</th>
                    <th>Mètode</th>
                    <th>Acció</th>
                </tr>
                <?php while ($a = $aplicacions->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $a['id_aplicacio'] ?></strong></td>
                        <td><?= htmlspecialchars($a['parcela']) ?></td>
                        <td><?= $a['id_fila'] ?: '-' ?></td>
                        <td><?= $a['data_hora'] ?></td>
                        <td><span class="badge badge-info"><?= htmlspecialchars($a['producte']) ?></span></td>
                        <td><?= number_format($a['quantitat_aplicada'], 2) ?></td>
                        <td><?= htmlspecialchars($a['metode_aplicacio'] ?? '-') ?></td>
                        <td>
                            <a href="?p=monitoratge_plagues&eliminar=aplicacio&id=<?= $a['id_aplicacio'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquesta aplicació?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha aplicacions de tractament.</p>
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
