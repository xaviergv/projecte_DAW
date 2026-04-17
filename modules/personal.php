<div class="section">
    <!-- 1. Afegir Treballador -->
    <div class="form-section">
        <h3><i class="fa-solid fa-user-plus" style="color:var(--primary); margin-right:8px;"></i> Afegir nou treballador</h3>
        <form method="post">
            <input type="hidden" name="p" value="personal">
            <input type="hidden" name="nou_treballador" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>NIF:</label>
                    <input type="text" name="nif" required placeholder="ex: 12345678Z">
                </div>

                <div class="form-group">
                    <label>Nom:</label>
                    <input type="text" name="nom" required>
                </div>

                <div class="form-group">
                    <label>Cognoms:</label>
                    <input type="text" name="cognoms" required>
                </div>

                <div class="form-group">
                    <label>Telèfon:</label>
                    <input type="text" name="telefon" placeholder="ex: 600000000">
                </div>
            </div>

            <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Afegir treballador</button>
        </form>
    </div>

    <h3><i class="fa-solid fa-users" style="margin-right:8px; color:var(--text-muted);"></i> Llistat de treballadors</h3>
    <?php
    $treballadors = $conn->query("SELECT * FROM Treballador ORDER BY cognoms, nom");
    if ($treballadors->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>NIF</th>
                    <th>Nom complet</th>
                    <th>Telèfon</th>
                    <th>Acció</th>
                </tr>
                <?php while ($t = $treballadors->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?= $t['id_treballador'] ?></strong></td>
                    <td><span class="badge badge-secondary"><?= htmlspecialchars($t['nif']) ?></span></td>
                    <td><?= htmlspecialchars($t['nom'].' '.$t['cognoms']) ?></td>
                    <td><i class="fa-solid fa-phone" style="color:var(--text-muted); font-size:0.8rem; margin-right:5px;"></i> <?= htmlspecialchars($t['telefon'] ?? '-') ?></td>
                    <td>
                        <a href="?p=personal&eliminar=treballador&id=<?= $t['id_treballador'] ?>" 
                           class="btn btn-red btn-icon" onclick="return confirm('Segur?');" title="Eliminar">
                           <i class="fa-solid fa-trash-can"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha treballadors.</p>
    <?php endif; ?>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <!-- 2. Absència -->
    <div class="form-section">
        <h3><i class="fa-solid fa-user-clock" style="color:var(--warning); margin-right:8px;"></i> Registrar absència</h3>
        <form method="post">
            <input type="hidden" name="p" value="personal">
            <input type="hidden" name="nou_absencia" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Treballador:</label>
                    <select name="id_treballador" required>
                        <option value="">Selecciona treballador</option>
                        <?php
                        $treb = $conn->query("SELECT id_treballador, CONCAT(nom, ' ', cognoms) AS nom FROM Treballador ORDER BY cognoms");
                        while ($t = $treb->fetch_assoc()) {
                            echo '<option value="' . $t['id_treballador'] . '">' . htmlspecialchars($t['nom']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tipus d'absència:</label>
                    <select name="tipus" required>
                        <option value="Vacances">Vacances</option>
                        <option value="Malaltia">Malaltia</option>
                        <option value="Permís">Permís</option>
                        <option value="Altres">Altres</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data inici:</label>
                    <input type="date" name="data_inici" required>
                </div>

                <div class="form-group">
                    <label>Data fi:</label>
                    <input type="date" name="data_fi">
                </div>
            </div>

            <button type="submit" class="btn" style="background:var(--warning); color:#fff;"><i class="fa-solid fa-calendar-plus"></i> Registrar absència</button>
        </form>
    </div>

    <h3><i class="fa-solid fa-clock-rotate-left" style="margin-right:8px; color:var(--text-muted);"></i> Llistat d'absències</h3>
    <?php
    $abs = $conn->query("SELECT a.id_absencia, t.nom, t.cognoms, a.tipus, a.data_inici, a.data_fi FROM Absencia a JOIN Treballador t ON a.id_treballador = t.id_treballador ORDER BY a.data_inici DESC");
    if ($abs->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Treballador</th>
                    <th>Tipus</th>
                    <th>Inici</th>
                    <th>Fi</th>
                    <th>Acció</th>
                </tr>
                <?php while ($a = $abs->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?= $a['id_absencia'] ?></strong></td>
                    <td><?= htmlspecialchars($a['nom'].' '.$a['cognoms']) ?></td>
                    <td>
                        <?php
                        $badgeClass = 'badge-secondary';
                        if ($a['tipus'] == 'Vacances') $badgeClass = 'badge-success';
                        if ($a['tipus'] == 'Malaltia') $badgeClass = 'badge-danger';
                        if ($a['tipus'] == 'Permís') $badgeClass = 'badge-info';
                        echo "<span class='badge $badgeClass'>" . htmlspecialchars($a['tipus']) . "</span>";
                        ?>
                    </td>
                    <td><?= $a['data_inici'] ?></td>
                    <td><?= $a['data_fi'] ?? '-' ?></td>
                    <td>
                        <a href="?p=personal&eliminar=absencia&id=<?= $a['id_absencia'] ?>" 
                           class="btn btn-red btn-icon" onclick="return confirm('Segur?');" title="Eliminar">
                           <i class="fa-solid fa-trash-can"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> No hi ha absències registrades.</p>
    <?php endif; ?>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <div class="form-grid" style="gap:30px;">
        <!-- 3. Tasca -->
        <div class="form-section" style="margin-bottom:0;">
            <h3><i class="fa-solid fa-clipboard-list" style="color:var(--primary); margin-right:8px;"></i> Afegir nova tasca</h3>
            <form method="post">
                <input type="hidden" name="p" value="personal">
                <input type="hidden" name="nou_tasca" value="1">

                <div class="form-group">
                    <label>Nom de la tasca:</label>
                    <input type="text" name="nom" required>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label>Hores estimades:</label>
                    <input type="number" name="hores_estimades" step="0.5">
                </div>

                <div class="form-group full-width" style="margin-top:15px;">
                    <label>Descripció:</label>
                    <textarea name="descripcio" rows="2"></textarea>
                </div>

                <button type="submit" class="btn" style="margin-top:20px;"><i class="fa-solid fa-plus"></i> Crear tasca</button>
            </form>
        </div>

        <!-- 4. Assignació -->
        <div class="form-section" style="margin-bottom:0;">
            <h3><i class="fa-solid fa-user-tag" style="color:var(--info); margin-right:8px;"></i> Assignar tasca</h3>
            <form method="post">
                <input type="hidden" name="p" value="personal">
                <input type="hidden" name="nou_assignacio" value="1">

                <div class="form-group">
                    <label>Tasca:</label>
                    <select name="id_tasca" required>
                        <option value="">Selecciona tasca</option>
                        <?php
                        $tasques = $conn->query("SELECT id_tasca, nom FROM Tasca ORDER BY nom");
                        while ($t = $tasques->fetch_assoc()) {
                            echo '<option value="' . $t['id_tasca'] . '">' . htmlspecialchars($t['nom']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label>Treballador:</label>
                    <select name="id_treballador" required>
                        <option value="">Selecciona treballador</option>
                        <?php
                        $treballadors = $conn->query("SELECT id_treballador, CONCAT(nom, ' ', cognoms) AS nom FROM Treballador ORDER BY cognoms");
                        while ($t = $treballadors->fetch_assoc()) {
                            echo '<option value="' . $t['id_treballador'] . '">' . htmlspecialchars($t['nom']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="btn" style="background:var(--info); color:#fff; margin-top:20px;"><i class="fa-solid fa-link"></i> Assignar</button>
            </form>
        </div>
    </div>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <!-- 5. Fitxatge -->
    <div class="form-section">
        <h3><i class="fa-solid fa-fingerprint" style="color:var(--primary-dark); margin-right:8px;"></i> Registrar Fitxatge</h3>
        <form method="post">
            <input type="hidden" name="p" value="personal">
            <input type="hidden" name="nou_fitxatge" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Treballador:</label>
                    <select name="id_treballador" required>
                        <option value="">Selecciona treballador</option>
                        <?php
                        $treb = $conn->query("SELECT id_treballador, CONCAT(nom, ' ', cognoms) AS nom FROM Treballador ORDER BY cognoms");
                        while ($t = $treb->fetch_assoc()) {
                            echo '<option value="' . $t['id_treballador'] . '">' . htmlspecialchars($t['nom']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tasca (opcional):</label>
                    <select name="id_tasca">
                        <option value="">Sense tasca</option>
                        <?php
                        $tasques = $conn->query("SELECT id_tasca, nom FROM Tasca ORDER BY nom");
                        while ($t = $tasques->fetch_assoc()) {
                            echo '<option value="' . $t['id_tasca'] . '">' . htmlspecialchars($t['nom']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Entrada (Data i hora):</label>
                    <input type="datetime-local" name="data_hora_entrada" required>
                </div>

                <div class="form-group">
                    <label>Sortida (opcional):</label>
                    <input type="datetime-local" name="data_hora_sortida">
                </div>

                <div class="form-group">
                    <label>Latitud:</label>
                    <input type="text" name="latitud" placeholder="ex: 41.6328570">
                </div>

                <div class="form-group">
                    <label>Longitud:</label>
                    <input type="text" name="longitud" placeholder="ex: 0.8012350">
                </div>

                <div class="form-group full-width">
                    <label>Observacions:</label>
                    <textarea name="observacions" rows="2"></textarea>
                </div>
            </div>

            <button type="submit" class="btn" style="background:var(--primary-dark);"><i class="fa-solid fa-fingerprint"></i> Registrar fitxatge</button>
        </form>
    <?php
    if (isset($_POST['nou_treballador'])) {
        $nif = trim($_POST['nif'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $cognoms = trim($_POST['cognoms'] ?? '');
        $telefon = trim($_POST['telefon'] ?? '');
        if ($nif && $nom && $cognoms) {
            $stmt = $conn->prepare("INSERT INTO Treballador (nif, nom, cognoms, telefon, actiu, data_alta) VALUES (?, ?, ?, ?, 1, CURDATE())");
            $stmt->bind_param("ssss", $nif, $nom, $cognoms, $telefon);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Treballador afegit correctament!";
        } else {
            $_SESSION['err'] = "L'NIF, nom i cognoms són obligatoris";
        }
    }

    if (isset($_POST['nou_absencia'])) {
        $id_treballador = (int)($_POST['id_treballador'] ?? 0);
        $tipus = trim($_POST['tipus'] ?? '');
        $data_inici = $_POST['data_inici'] ?? null;
        $data_fi = !empty($_POST['data_fi']) ? $_POST['data_fi'] : null;
        if ($id_treballador > 0 && $data_inici) {
            $stmt = $conn->prepare("INSERT INTO Absencia (id_treballador, tipus, data_inici, data_fi) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $id_treballador, $tipus, $data_inici, $data_fi);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Absència registrada correctament!";
        }
    }

    if (isset($_POST['nou_tasca'])) {
        $nom = trim($_POST['nom'] ?? '');
        $descripcio = trim($_POST['descripcio'] ?? '');
        $hores_estimades = (float)($_POST['hores_estimades'] ?? 0);
        if ($nom) {
            $stmt = $conn->prepare("INSERT INTO Tasca (nom, descripcio, hores_estimades, finalitzada) VALUES (?, ?, ?, 0)");
            $stmt->bind_param("ssd", $nom, $descripcio, $hores_estimades);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Tasca creada correctament!";
        }
    }

    if (isset($_POST['nou_assignacio'])) {
        $id_tasca = (int)($_POST['id_tasca'] ?? 0);
        $id_treballador = (int)($_POST['id_treballador'] ?? 0);
        if ($id_tasca > 0 && $id_treballador > 0) {
            $stmt = $conn->prepare("INSERT INTO Assignacio_Treballador_Tasca (id_tasca, id_treballador, es_cap_equip) VALUES (?, ?, 0)");
            $stmt->bind_param("ii", $id_tasca, $id_treballador);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Treballador assignat correctament a la tasca!";
        }
    }

    if (isset($_POST['nou_fitxatge'])) {
        $id_treballador   = (int)($_POST['id_treballador'] ?? 0);
        $data_hora_entrada = $_POST['data_hora_entrada'] ?? null;
        $data_hora_sortida = !empty($_POST['data_hora_sortida']) ? $_POST['data_hora_sortida'] : null;
        $latitud          = trim($_POST['latitud'] ?? '');
        $longitud         = trim($_POST['longitud'] ?? '');
        $id_tasca         = (int)($_POST['id_tasca'] ?? 0);
        $observacions     = trim($_POST['observacions'] ?? '');

        if ($id_treballador > 0 && $data_hora_entrada) {
            $stmt = $conn->prepare("
                INSERT INTO Fitxatge 
                (id_treballador, data_hora_entrada, data_hora_sortida, latitud, longitud, id_tasca, observacions)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssdis", $id_treballador, $data_hora_entrada, $data_hora_sortida, $latitud, $longitud, $id_tasca, $observacions);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Fitxatge registrat correctament!";
        } else {
            $_SESSION['err'] = "Treballador i data/hora d'entrada són obligatoris";
        }
    }
    ?>
</div>
