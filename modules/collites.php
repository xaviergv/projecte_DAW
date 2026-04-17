<?php
// ────────────────────────────────────────────────
// AUTO-CREACIÓ DE LA TAULA (si no existeix)
// ────────────────────────────────────────────────
$conn->query("
    CREATE TABLE IF NOT EXISTS `Collita` (
        `id_collita` int(11) NOT NULL AUTO_INCREMENT,
        `data_inici` date NOT NULL,
        `data_final` date DEFAULT NULL,
        `id_parcela` int(11) NOT NULL,
        `varietat` varchar(100) NOT NULL,
        `quantitat` decimal(10,2) NOT NULL DEFAULT 0.00,
        `unitat` varchar(30) NOT NULL DEFAULT 'kg',
        `treballadors` int(11) DEFAULT 0,
        `temperatura` decimal(5,2) DEFAULT NULL,
        `humitat` decimal(5,2) DEFAULT NULL,
        `estat_fruit` varchar(100) DEFAULT NULL,
        `incidencies` text DEFAULT NULL,
        PRIMARY KEY (`id_collita`),
        KEY `id_parcela` (`id_parcela`),
        CONSTRAINT `collita_ibfk_parcela` FOREIGN KEY (`id_parcela`) REFERENCES `Parcel·la` (`id_parcela`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
");

// ────────────────────────────────────────────────
// PROCESSAMENT POST
// ────────────────────────────────────────────────

// Afegir nova collita
if (isset($_POST['nou_collita'])) {
    $data_inici   = $_POST['data_inici'] ?? null;
    $data_final   = !empty($_POST['data_final']) ? $_POST['data_final'] : null;
    $id_parcela   = (int)($_POST['id_parcela'] ?? 0);
    $varietat      = trim($_POST['varietat'] ?? '');
    $quantitat     = (float)($_POST['quantitat'] ?? 0);
    $unitat        = trim($_POST['unitat'] ?? 'kg');
    $treballadors  = (int)($_POST['treballadors'] ?? 0);
    $temperatura   = !empty($_POST['temperatura']) ? (float)$_POST['temperatura'] : null;
    $humitat       = !empty($_POST['humitat']) ? (float)$_POST['humitat'] : null;
    $estat_fruit   = trim($_POST['estat_fruit'] ?? '');
    $incidencies   = trim($_POST['incidencies'] ?? '');

    if ($data_inici && $id_parcela > 0 && $varietat && $quantitat > 0) {
        $data_final_sql = $data_final ? "'$data_final'" : "NULL";
        $temperatura_sql = $temperatura !== null ? $temperatura : "NULL";
        $humitat_sql = $humitat !== null ? $humitat : "NULL";
        $varietat_esc = $conn->real_escape_string($varietat);
        $unitat_esc = $conn->real_escape_string($unitat);
        $estat_fruit_esc = $conn->real_escape_string($estat_fruit);
        $incidencies_esc = $conn->real_escape_string($incidencies);

        $conn->query("
            INSERT INTO Collita 
            (data_inici, data_final, id_parcela, varietat, quantitat, unitat, treballadors, temperatura, humitat, estat_fruit, incidencies)
            VALUES ('$data_inici', $data_final_sql, $id_parcela, '$varietat_esc', $quantitat, '$unitat_esc', $treballadors, $temperatura_sql, $humitat_sql, '$estat_fruit_esc', '$incidencies_esc')
        ");
        $_SESSION['msg'] = "Collita registrada correctament!";
    } else {
        $_SESSION['err'] = "Data inici, parcel·la, varietat i quantitat són obligatoris";
    }
}

// Actualitzar collita existent
if (isset($_POST['editar_collita'])) {
    $id_collita    = (int)($_POST['id_collita'] ?? 0);
    $data_inici    = $_POST['data_inici'] ?? null;
    $data_final    = !empty($_POST['data_final']) ? $_POST['data_final'] : null;
    $id_parcela    = (int)($_POST['id_parcela'] ?? 0);
    $varietat       = trim($_POST['varietat'] ?? '');
    $quantitat      = (float)($_POST['quantitat'] ?? 0);
    $unitat         = trim($_POST['unitat'] ?? 'kg');
    $treballadors   = (int)($_POST['treballadors'] ?? 0);
    $temperatura    = !empty($_POST['temperatura']) ? (float)$_POST['temperatura'] : null;
    $humitat        = !empty($_POST['humitat']) ? (float)$_POST['humitat'] : null;
    $estat_fruit    = trim($_POST['estat_fruit'] ?? '');
    $incidencies    = trim($_POST['incidencies'] ?? '');

    if ($id_collita > 0 && $data_inici && $id_parcela > 0 && $varietat && $quantitat > 0) {
        $data_final_sql = $data_final ? "'$data_final'" : "NULL";
        $temperatura_sql = $temperatura !== null ? $temperatura : "NULL";
        $humitat_sql = $humitat !== null ? $humitat : "NULL";
        $varietat_esc = $conn->real_escape_string($varietat);
        $unitat_esc = $conn->real_escape_string($unitat);
        $estat_fruit_esc = $conn->real_escape_string($estat_fruit);
        $incidencies_esc = $conn->real_escape_string($incidencies);

        $conn->query("
            UPDATE Collita SET
                data_inici = '$data_inici',
                data_final = $data_final_sql,
                id_parcela = $id_parcela,
                varietat = '$varietat_esc',
                quantitat = $quantitat,
                unitat = '$unitat_esc',
                treballadors = $treballadors,
                temperatura = $temperatura_sql,
                humitat = $humitat_sql,
                estat_fruit = '$estat_fruit_esc',
                incidencies = '$incidencies_esc'
            WHERE id_collita = $id_collita
        ");
        $_SESSION['msg'] = "Collita actualitzada correctament!";
    } else {
        $_SESSION['err'] = "Camps obligatoris incomplets";
    }
}

// Determinar si estem en mode edició
$editant = null;
if (isset($_GET['editar_collita']) && (int)$_GET['editar_collita'] > 0) {
    $id_edit = (int)$_GET['editar_collita'];
    $res = $conn->query("SELECT * FROM Collita WHERE id_collita = $id_edit");
    if ($res && $res->num_rows > 0) {
        $editant = $res->fetch_assoc();
    }
}
?>

<div class="section">

    <!-- ────────────────────────────────────────────────
         FORMULARI: Afegir / Editar Collita
         ──────────────────────────────────────────────── -->
    <div class="form-section">
        <h3>
            <?php if ($editant): ?>
                <i class="fa-solid fa-pen-to-square" style="color:var(--warning); margin-right:8px;"></i> Editar collita #<?= $editant['id_collita'] ?>
            <?php else: ?>
                <i class="fa-solid fa-wheat-awn" style="color:var(--primary); margin-right:8px;"></i> Registrar nova collita
            <?php endif; ?>
        </h3>
        <form method="post">
            <input type="hidden" name="p" value="collites">
            <?php if ($editant): ?>
                <input type="hidden" name="editar_collita" value="1">
                <input type="hidden" name="id_collita" value="<?= $editant['id_collita'] ?>">
            <?php else: ?>
                <input type="hidden" name="nou_collita" value="1">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label>Parcel·la: <span style="color:var(--danger);">*</span></label>
                    <select name="id_parcela" required>
                        <option value="">Selecciona parcel·la</option>
                        <?php
                        $parceles = $conn->query("SELECT id_parcela, nom FROM Parcel·la ORDER BY nom");
                        while ($pr = $parceles->fetch_assoc()) {
                            $sel = ($editant && $editant['id_parcela'] == $pr['id_parcela']) ? 'selected' : '';
                            echo '<option value="' . $pr['id_parcela'] . '" ' . $sel . '>' . htmlspecialchars($pr['nom']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Varietat: <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="varietat" required placeholder="ex: Golden Delicious"
                           value="<?= htmlspecialchars($editant['varietat'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Data inici collita: <span style="color:var(--danger);">*</span></label>
                    <input type="date" name="data_inici" required
                           value="<?= $editant['data_inici'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Data final collita:</label>
                    <input type="date" name="data_final"
                           value="<?= $editant['data_final'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Quantitat recollida: <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="quantitat" step="0.01" min="0" required placeholder="ex: 1500"
                           value="<?= $editant['quantitat'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Unitat:</label>
                    <select name="unitat">
                        <?php
                        $unitats = ['kg', 'tones', 'caixes', 'litres', 'unitats'];
                        foreach ($unitats as $u) {
                            $sel = ($editant && ($editant['unitat'] ?? '') === $u) ? 'selected' : '';
                            echo "<option value=\"$u\" $sel>" . ucfirst($u) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Treballadors assignats:</label>
                    <input type="number" name="treballadors" min="0" placeholder="ex: 5"
                           value="<?= $editant['treballadors'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Temperatura (°C):</label>
                    <input type="number" name="temperatura" step="0.1" placeholder="ex: 22.5"
                           value="<?= $editant['temperatura'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Humitat (%):</label>
                    <input type="number" name="humitat" step="0.1" min="0" max="100" placeholder="ex: 65"
                           value="<?= $editant['humitat'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Estat del fruit:</label>
                    <select name="estat_fruit">
                        <option value="">Selecciona</option>
                        <?php
                        $estats = ['Excel·lent', 'Bo', 'Acceptable', 'Deficient', 'Malmès'];
                        foreach ($estats as $e) {
                            $sel = ($editant && ($editant['estat_fruit'] ?? '') === $e) ? 'selected' : '';
                            echo "<option value=\"$e\" $sel>$e</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label>Incidències / Observacions:</label>
                    <textarea name="incidencies" rows="2" placeholder="ex: Pluja intensa durant la recollida..."><?= htmlspecialchars($editant['incidencies'] ?? '') ?></textarea>
                </div>
            </div>

            <?php if ($editant): ?>
                <button type="submit" class="btn" style="background:var(--warning); color:#fff;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar canvis
                </button>
                <a href="?p=collites" class="btn" style="background:white; border:1px solid var(--border-color); color:var(--text-main); margin-left:10px; box-shadow:none;">
                    <i class="fa-solid fa-xmark"></i> Cancel·lar
                </a>
            <?php else: ?>
                <button type="submit" class="btn">
                    <i class="fa-solid fa-plus"></i> Registrar collita
                </button>
            <?php endif; ?>
        </form>
    </div>


    <!-- ────────────────────────────────────────────────
         LLISTAT DE COLLITES
         ──────────────────────────────────────────────── -->
    <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Historial de collites</h3>
    <?php
    $collites = $conn->query("
        SELECT c.*, p.nom AS nom_parcela
        FROM Collita c
        JOIN Parcel·la p ON c.id_parcela = p.id_parcela
        ORDER BY c.data_inici DESC
    ");

    if ($collites && $collites->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Parcel·la</th>
                    <th>Varietat</th>
                    <th>Període</th>
                    <th>Quantitat</th>
                    <th>Treballadors</th>
                    <th>Temp. / Hum.</th>
                    <th>Estat fruit</th>
                    <th>Incidències</th>
                    <th>Accions</th>
                </tr>
                <?php while ($c = $collites->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $c['id_collita'] ?></strong></td>
                        <td><span class="badge badge-info"><i class="fa-solid fa-map-location"></i> <?= htmlspecialchars($c['nom_parcela']) ?></span></td>
                        <td><?= htmlspecialchars($c['varietat']) ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($c['data_inici'])) ?>
                            <?php if ($c['data_final']): ?>
                                <br><small style="color:var(--text-muted);">→ <?= date('d/m/Y', strtotime($c['data_final'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-success" style="font-size:0.85rem;"><?= number_format($c['quantitat'], 2) ?> <?= htmlspecialchars($c['unitat']) ?></span></td>
                        <td>
                            <?php if ($c['treballadors'] > 0): ?>
                                <i class="fa-solid fa-user-group" style="color:var(--text-muted); margin-right:4px;"></i><?= $c['treballadors'] ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($c['temperatura'] !== null): ?>
                                <i class="fa-solid fa-temperature-half" style="color:var(--danger); margin-right:3px;"></i><?= $c['temperatura'] ?>°C
                            <?php endif; ?>
                            <?php if ($c['humitat'] !== null): ?>
                                <br><i class="fa-solid fa-droplet" style="color:var(--info); margin-right:3px;"></i><?= $c['humitat'] ?>%
                            <?php endif; ?>
                            <?php if ($c['temperatura'] === null && $c['humitat'] === null): ?>-<?php endif; ?>
                        </td>
                        <td>
                            <?php
                            if ($c['estat_fruit']) {
                                $ec = 'badge-secondary';
                                if ($c['estat_fruit'] === 'Excel·lent') $ec = 'badge-success';
                                elseif ($c['estat_fruit'] === 'Bo') $ec = 'badge-info';
                                elseif ($c['estat_fruit'] === 'Acceptable') $ec = 'badge-warning';
                                elseif ($c['estat_fruit'] === 'Deficient' || $c['estat_fruit'] === 'Malmès') $ec = 'badge-danger';
                                echo "<span class='badge $ec'>" . htmlspecialchars($c['estat_fruit']) . "</span>";
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td style="max-width:200px; white-space:normal;">
                            <small><?= nl2br(htmlspecialchars($c['incidencies'] ?? '-')) ?></small>
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="?p=collites&editar_collita=<?= $c['id_collita'] ?>"
                               class="btn btn-icon" style="background:var(--warning); color:#fff; padding:8px 12px; font-size:0.85rem;" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <a href="?p=collites&eliminar=collita&id=<?= $c['id_collita'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquesta collita?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha collites registrades. Utilitza el formulari per afegir-ne una!</p>
    <?php endif; ?>
</div>
