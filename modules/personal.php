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

    <div class="dashboard-grid" style="margin-bottom: 30px;">
        <div class="kpi-card">
            <?php
            $q_trebs = $conn->query("SELECT COUNT(*) AS t FROM Treballador WHERE actiu = 1");
            $trebs_actius = $q_trebs ? $q_trebs->fetch_assoc()['t'] : 0;
            ?>
            <div class="kpi-icon" style="color:var(--info); background:rgba(59,130,246,0.1);"><i class="fa-solid fa-users"></i></div>
            <div class="kpi-content">
                <h3>Treballadors Actius</h3>
                <p class="kpi-value"><?= $trebs_actius ?></p>
            </div>
        </div>
        <div class="kpi-card">
            <?php
            $q_abs = $conn->query("SELECT COUNT(*) AS t FROM Absencia WHERE data_fi >= CURDATE() OR data_fi IS NULL");
            $abs_actives = $q_abs ? $q_abs->fetch_assoc()['t'] : 0;
            ?>
            <div class="kpi-icon" style="color:var(--danger); background:rgba(239,68,68,0.1);"><i class="fa-solid fa-user-minus"></i></div>
            <div class="kpi-content">
                <h3>Absències Actives</h3>
                <p class="kpi-value"><?= $abs_actives ?></p>
            </div>
        </div>
    </div>

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
            echo "<script>window.location.href='index.php?p=personal';</script>";
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
            echo "<script>window.location.href='index.php?p=personal';</script>";
        }
    }
    ?>
</div>
