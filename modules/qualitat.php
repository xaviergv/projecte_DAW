<?php
// ════════════════════════════════════════════════════════════
//  CONTROL DE QUALITAT DE FRUITA — High Elo
//  Taula Qualitat_Fruita amb relació a Collita
// ════════════════════════════════════════════════════════════

// Auto-creació de la taula
$conn->query("
    CREATE TABLE IF NOT EXISTS `Qualitat_Fruita` (
        `id_qualitat` int(11) NOT NULL AUTO_INCREMENT,
        `collita_id`  int(11) NOT NULL,
        `mida`        enum('Molt petita','Petita','Mitjana','Gran','Molt gran') NOT NULL DEFAULT 'Mitjana',
        `color`       varchar(80) NOT NULL DEFAULT '',
        `defectes`    text DEFAULT NULL,
        `sabor`       enum('Excel·lent','Bo','Acceptable','Mediocre','Dolent') NOT NULL DEFAULT 'Bo',
        `textura`     enum('Ferma','Cruixent','Suau','Farinosa','Tova') NOT NULL DEFAULT 'Ferma',
        `grau_brix`   decimal(4,1) DEFAULT NULL COMMENT 'º Brix (dolçor)',
        `calibre_mm`  decimal(5,1) DEFAULT NULL COMMENT 'diàmetre en mm',
        `pes_mitja`   decimal(6,1) DEFAULT NULL COMMENT 'pes mitjà en grams',
        `pct_primera` decimal(5,2) DEFAULT NULL COMMENT '% categoria primera',
        `pct_segona`  decimal(5,2) DEFAULT NULL COMMENT '% categoria segona',
        `pct_descart` decimal(5,2) DEFAULT NULL COMMENT '% descart',
        `inspector`   varchar(100) DEFAULT NULL,
        `data_control` date NOT NULL,
        `observacions` text DEFAULT NULL,
        `created_at`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_qualitat`),
        KEY `fk_qual_collita` (`collita_id`),
        CONSTRAINT `fk_qual_collita` FOREIGN KEY (`collita_id`)
            REFERENCES `Collita` (`id_collita`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
");

// ────────────────────────────────────────────────
// FUNCIONS DE QUALITAT
// ────────────────────────────────────────────────

/**
 * Calcula la puntuació global de qualitat (0-100) a partir dels camps.
 */
function calcularPuntuacioQualitat(array $q): int {
    $punts = 0;

    // Mida (20 punts)
    $mida_map = ['Molt petita' => 4, 'Petita' => 10, 'Mitjana' => 20, 'Gran' => 18, 'Molt gran' => 12];
    $punts += $mida_map[$q['mida']] ?? 10;

    // Sabor (30 punts)
    $sabor_map = ['Excel·lent' => 30, 'Bo' => 24, 'Acceptable' => 16, 'Mediocre' => 8, 'Dolent' => 2];
    $punts += $sabor_map[$q['sabor']] ?? 15;

    // Textura (20 punts)
    $textura_map = ['Cruixent' => 20, 'Ferma' => 18, 'Suau' => 14, 'Tova' => 8, 'Farinosa' => 4];
    $punts += $textura_map[$q['textura']] ?? 10;

    // Defectes (20 punts — menys defectes = més punts)
    $defectes = trim($q['defectes'] ?? '');
    if (empty($defectes) || strtolower($defectes) === 'cap' || strtolower($defectes) === 'sense defectes') {
        $punts += 20;
    } elseif (mb_strlen($defectes) < 30) {
        $punts += 12;
    } elseif (mb_strlen($defectes) < 80) {
        $punts += 6;
    } else {
        $punts += 2;
    }

    // % primera categoria (10 punts)
    if ($q['pct_primera'] !== null) {
        $punts += min(10, round((float)$q['pct_primera'] / 10));
    } else {
        $punts += 5;
    }

    return min(100, max(0, $punts));
}

/**
 * Retorna la configuració visual per una puntuació donada.
 */
function obtenirNivellQualitat(int $puntuacio): array {
    if ($puntuacio >= 85) return ['label' => 'Excel·lent', 'color' => '#059669', 'bg' => '#d1fae5', 'icon' => 'fa-solid fa-star', 'emoji' => '🌟'];
    if ($puntuacio >= 70) return ['label' => 'Bo', 'color' => '#0284c7', 'bg' => '#dbeafe', 'icon' => 'fa-solid fa-thumbs-up', 'emoji' => '👍'];
    if ($puntuacio >= 50) return ['label' => 'Acceptable', 'color' => '#d97706', 'bg' => '#fef3c7', 'icon' => 'fa-solid fa-hand', 'emoji' => '⚠️'];
    if ($puntuacio >= 30) return ['label' => 'Mediocre', 'color' => '#ea580c', 'bg' => '#ffedd5', 'icon' => 'fa-solid fa-thumbs-down', 'emoji' => '👎'];
    return ['label' => 'Dolent', 'color' => '#dc2626', 'bg' => '#fee2e2', 'icon' => 'fa-solid fa-circle-xmark', 'emoji' => '❌'];
}

// ────────────────────────────────────────────────
// PROCESSAR FORMULARIS
// ────────────────────────────────────────────────

// CREATE
if (isset($_POST['nova_qualitat'])) {
    $collita_id   = (int)($_POST['collita_id'] ?? 0);
    $mida         = $_POST['mida'] ?? 'Mitjana';
    $color        = trim($_POST['color'] ?? '');
    $defectes     = trim($_POST['defectes'] ?? '');
    $sabor        = $_POST['sabor'] ?? 'Bo';
    $textura      = $_POST['textura'] ?? 'Ferma';
    $grau_brix    = !empty($_POST['grau_brix']) ? (float)$_POST['grau_brix'] : null;
    $calibre_mm   = !empty($_POST['calibre_mm']) ? (float)$_POST['calibre_mm'] : null;
    $pes_mitja    = !empty($_POST['pes_mitja']) ? (float)$_POST['pes_mitja'] : null;
    $pct_primera  = !empty($_POST['pct_primera']) ? (float)$_POST['pct_primera'] : null;
    $pct_segona   = !empty($_POST['pct_segona']) ? (float)$_POST['pct_segona'] : null;
    $pct_descart  = !empty($_POST['pct_descart']) ? (float)$_POST['pct_descart'] : null;
    $inspector    = trim($_POST['inspector'] ?? '');
    $data_control = $_POST['data_control'] ?? date('Y-m-d');
    $observacions = trim($_POST['observacions'] ?? '');

    if ($collita_id > 0 && $color && $data_control) {
        $stmt = $conn->prepare("
            INSERT INTO Qualitat_Fruita
            (collita_id, mida, color, defectes, sabor, textura, grau_brix, calibre_mm, pes_mitja,
             pct_primera, pct_segona, pct_descart, inspector, data_control, observacions)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssssddddddsss",
            $collita_id, $mida, $color, $defectes, $sabor, $textura,
            $grau_brix, $calibre_mm, $pes_mitja,
            $pct_primera, $pct_segona, $pct_descart,
            $inspector, $data_control, $observacions
        );
        $stmt->execute();
        $stmt->close();
        $_SESSION['msg'] = "Control de qualitat registrat correctament!";
    } else {
        $_SESSION['err'] = "Collita, color i data de control són obligatoris.";
    }
    echo "<script>window.location.href='?p=qualitat';</script>";
    exit;
}

// DELETE
if (isset($_GET['eliminar_qualitat'])) {
    $id_del = (int)($_GET['id'] ?? 0);
    if ($id_del > 0) {
        $conn->query("DELETE FROM Qualitat_Fruita WHERE id_qualitat = $id_del");
        $_SESSION['msg'] = "Registre de qualitat #$id_del eliminat.";
    }
    echo "<script>window.location.href='?p=qualitat';</script>";
    exit;
}

// ────────────────────────────────────────────────
// DADES PER A LA VISTA
// ────────────────────────────────────────────────

// Detall d'una collita específica (si ve per GET)
$detall_collita = null;
if (isset($_GET['collita']) && (int)$_GET['collita'] > 0) {
    $id_c = (int)$_GET['collita'];
    $res_c = $conn->query("
        SELECT c.*, p.nom AS parcela_nom
        FROM Collita c
        JOIN `Parcel·la` p ON c.id_parcela = p.id_parcela
        WHERE c.id_collita = $id_c
    ");
    if ($res_c && $res_c->num_rows > 0) {
        $detall_collita = $res_c->fetch_assoc();
    }
}
?>

<div class="section">

    <!-- ══════════════════════════════════════════ -->
    <!--  1. DETALL DE COLLITA + QUALITAT          -->
    <!-- ══════════════════════════════════════════ -->
    <?php if ($detall_collita):
        // Obtenir controls de qualitat d'aquesta collita
        $quals = $conn->query("
            SELECT * FROM Qualitat_Fruita
            WHERE collita_id = " . $detall_collita['id_collita'] . "
            ORDER BY data_control DESC
        ");
    ?>
        <div class="form-section" style="border-left:4px solid var(--primary); margin-bottom:35px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:15px;">
                <div>
                    <h3 style="margin:0 0 8px 0;">
                        <i class="fa-solid fa-apple-whole" style="color:var(--primary); margin-right:8px;"></i>
                        Collita #<?= $detall_collita['id_collita'] ?> — <?= htmlspecialchars($detall_collita['varietat']) ?>
                    </h3>
                    <p style="color:var(--text-muted); margin:0;">
                        <span class="badge badge-info"><i class="fa-solid fa-map-location"></i> <?= htmlspecialchars($detall_collita['parcela_nom']) ?></span>
                        &nbsp;
                        <i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($detall_collita['data_inici'])) ?>
                        <?= $detall_collita['data_final'] ? ' → ' . date('d/m/Y', strtotime($detall_collita['data_final'])) : '' ?>
                        &nbsp;
                        <span class="badge badge-success"><?= number_format($detall_collita['quantitat'], 1) ?> <?= htmlspecialchars($detall_collita['unitat']) ?></span>
                    </p>
                </div>
                <a href="?p=qualitat" class="btn" style="background:white; border:1px solid var(--border-color); color:var(--text-main); box-shadow:none;">
                    <i class="fa-solid fa-arrow-left"></i> Tornar
                </a>
            </div>

            <?php if ($quals && $quals->num_rows > 0): ?>
                <div class="qualitat-detail-grid" style="margin-top:20px;">
                    <?php while ($q = $quals->fetch_assoc()):
                        $puntuacio = calcularPuntuacioQualitat($q);
                        $nivell = obtenirNivellQualitat($puntuacio);
                    ?>
                        <div class="qualitat-detail-card" style="border-color:<?= $nivell['color'] ?>;">
                            <div class="qualitat-score-ring" style="--score-color:<?= $nivell['color'] ?>; --score-pct:<?= $puntuacio ?>%;">
                                <div class="qualitat-score-inner">
                                    <span class="qualitat-score-num"><?= $puntuacio ?></span>
                                    <small>/100</small>
                                </div>
                            </div>
                            <div class="qualitat-detail-info">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                                    <span style="background:<?= $nivell['bg'] ?>; color:<?= $nivell['color'] ?>; padding:3px 12px; border-radius:9999px; font-weight:700; font-size:0.8rem;">
                                        <i class="<?= $nivell['icon'] ?>"></i> <?= $nivell['label'] ?>
                                    </span>
                                    <small style="color:var(--text-muted);"><?= date('d/m/Y', strtotime($q['data_control'])) ?></small>
                                </div>
                                <div class="qualitat-attrs">
                                    <span><i class="fa-solid fa-ruler" style="color:var(--info);"></i> <strong>Mida:</strong> <?= htmlspecialchars($q['mida']) ?></span>
                                    <span><i class="fa-solid fa-palette" style="color:var(--warning);"></i> <strong>Color:</strong> <?= htmlspecialchars($q['color']) ?></span>
                                    <span><i class="fa-solid fa-lemon" style="color:#d97706;"></i> <strong>Sabor:</strong> <?= htmlspecialchars($q['sabor']) ?></span>
                                    <span><i class="fa-solid fa-hand-holding" style="color:var(--primary);"></i> <strong>Textura:</strong> <?= htmlspecialchars($q['textura']) ?></span>
                                    <?php if ($q['grau_brix'] !== null): ?>
                                        <span><i class="fa-solid fa-droplet" style="color:#7c3aed;"></i> <strong>ºBrix:</strong> <?= $q['grau_brix'] ?></span>
                                    <?php endif; ?>
                                    <?php if ($q['calibre_mm'] !== null): ?>
                                        <span><i class="fa-solid fa-circle" style="color:var(--text-muted);"></i> <strong>Calibre:</strong> <?= $q['calibre_mm'] ?>mm</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($q['defectes']): ?>
                                    <div style="margin-top:6px; padding:6px 10px; background:#fef2f2; border-radius:6px; font-size:0.85rem; color:#991b1b;">
                                        <i class="fa-solid fa-bug"></i> <strong>Defectes:</strong> <?= htmlspecialchars($q['defectes']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($q['pct_primera'] !== null): ?>
                                    <div style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;">
                                        <span class="badge badge-success" style="font-size:0.75rem;">1a: <?= number_format($q['pct_primera'],1) ?>%</span>
                                        <?php if ($q['pct_segona'] !== null): ?>
                                            <span class="badge badge-warning" style="font-size:0.75rem;">2a: <?= number_format($q['pct_segona'],1) ?>%</span>
                                        <?php endif; ?>
                                        <?php if ($q['pct_descart'] !== null): ?>
                                            <span class="badge badge-danger" style="font-size:0.75rem;">Descart: <?= number_format($q['pct_descart'],1) ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="?p=qualitat&eliminar_qualitat=1&id=<?= $q['id_qualitat'] ?>"
                               class="btn btn-red btn-icon" style="font-size:0.75rem; padding:5px 8px; align-self:flex-start;"
                               onclick="return confirm('Eliminar registre de qualitat?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="color:var(--text-muted); margin-top:15px;"><i class="fa-solid fa-clipboard-question"></i> Aquesta collita no té cap control de qualitat. Afegeix-ne un amb el formulari de sota.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════ -->
    <!--  2. FORMULARI DE REGISTRE DE QUALITAT     -->
    <!-- ══════════════════════════════════════════ -->
    <div class="form-section">
        <h3><i class="fa-solid fa-clipboard-check" style="color:var(--primary); margin-right:8px;"></i> Registrar control de qualitat</h3>
        <form method="post">
            <input type="hidden" name="p" value="qualitat">
            <input type="hidden" name="nova_qualitat" value="1">
            <div class="form-grid">
                <div class="form-group">
                    <label>Collita: <span style="color:var(--danger);">*</span></label>
                    <select name="collita_id" required>
                        <option value="">Selecciona collita</option>
                        <?php
                        $collites = $conn->query("
                            SELECT c.id_collita, c.varietat, c.data_inici, c.quantitat, c.unitat, p.nom AS parcela_nom
                            FROM Collita c
                            JOIN `Parcel·la` p ON c.id_parcela = p.id_parcela
                            ORDER BY c.data_inici DESC
                        ");
                        if ($collites) {
                            while ($col = $collites->fetch_assoc()) {
                                $sel = ($detall_collita && $detall_collita['id_collita'] == $col['id_collita']) ? 'selected' : '';
                                $label = '#' . $col['id_collita'] . ' ' . htmlspecialchars($col['varietat'])
                                    . ' (' . htmlspecialchars($col['parcela_nom']) . ', '
                                    . date('d/m/Y', strtotime($col['data_inici'])) . ', '
                                    . number_format($col['quantitat'],0) . $col['unitat'] . ')';
                                echo "<option value=\"{$col['id_collita']}\" $sel>$label</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data del control: <span style="color:var(--danger);">*</span></label>
                    <input type="date" name="data_control" required value="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label>Mida del fruit:</label>
                    <select name="mida">
                        <option value="Molt petita">Molt petita</option>
                        <option value="Petita">Petita</option>
                        <option value="Mitjana" selected>Mitjana</option>
                        <option value="Gran">Gran</option>
                        <option value="Molt gran">Molt gran</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Color: <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="color" required placeholder="ex: Vermell intens, Groc daurat...">
                </div>

                <div class="form-group">
                    <label>Sabor:</label>
                    <select name="sabor">
                        <option value="Excel·lent">🌟 Excel·lent</option>
                        <option value="Bo" selected>👍 Bo</option>
                        <option value="Acceptable">⚠️ Acceptable</option>
                        <option value="Mediocre">👎 Mediocre</option>
                        <option value="Dolent">❌ Dolent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Textura:</label>
                    <select name="textura">
                        <option value="Cruixent">Cruixent</option>
                        <option value="Ferma" selected>Ferma</option>
                        <option value="Suau">Suau</option>
                        <option value="Tova">Tova</option>
                        <option value="Farinosa">Farinosa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Grau Brix (ºBx dolçor):</label>
                    <input type="number" name="grau_brix" step="0.1" min="0" max="30" placeholder="ex: 14.5">
                </div>

                <div class="form-group">
                    <label>Calibre (mm):</label>
                    <input type="number" name="calibre_mm" step="0.1" min="0" placeholder="ex: 72.0">
                </div>

                <div class="form-group">
                    <label>Pes mitjà (g):</label>
                    <input type="number" name="pes_mitja" step="0.1" min="0" placeholder="ex: 185.0">
                </div>

                <div class="form-group">
                    <label>% Categoria Primera:</label>
                    <input type="number" name="pct_primera" step="0.1" min="0" max="100" placeholder="ex: 75.0">
                </div>

                <div class="form-group">
                    <label>% Categoria Segona:</label>
                    <input type="number" name="pct_segona" step="0.1" min="0" max="100" placeholder="ex: 20.0">
                </div>

                <div class="form-group">
                    <label>% Descart:</label>
                    <input type="number" name="pct_descart" step="0.1" min="0" max="100" placeholder="ex: 5.0">
                </div>

                <div class="form-group">
                    <label>Inspector / Responsable:</label>
                    <input type="text" name="inspector" placeholder="Nom del responsable">
                </div>

                <div class="form-group full-width">
                    <label>Defectes observats:</label>
                    <textarea name="defectes" rows="2" placeholder="ex: Taques lleus, 3% amb clivella, petites marques de granís..."></textarea>
                </div>

                <div class="form-group full-width">
                    <label>Observacions addicionals:</label>
                    <textarea name="observacions" rows="2" placeholder="Notes generals del control..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn">
                <i class="fa-solid fa-microscope"></i> Registrar control de qualitat
            </button>
        </form>
    </div>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <!-- ══════════════════════════════════════════ -->
    <!--  3. RESUM DE COLLITES AMB QUALITAT        -->
    <!-- ══════════════════════════════════════════ -->
    <h3><i class="fa-solid fa-ranking-star" style="margin-right:8px; color:var(--text-muted);"></i> Resum de qualitat per collita</h3>
    <?php
    $resum = $conn->query("
        SELECT c.id_collita, c.varietat, c.data_inici, c.quantitat, c.unitat, c.estat_fruit,
               p.nom AS parcela_nom,
               COUNT(q.id_qualitat) AS total_controls,
               GROUP_CONCAT(q.id_qualitat) AS ids_qualitat
        FROM Collita c
        JOIN `Parcel·la` p ON c.id_parcela = p.id_parcela
        LEFT JOIN Qualitat_Fruita q ON c.id_collita = q.collita_id
        GROUP BY c.id_collita
        ORDER BY c.data_inici DESC
    ");

    if ($resum && $resum->num_rows > 0):
    ?>
        <div class="qualitat-resum-grid">
            <?php while ($r = $resum->fetch_assoc()):
                $has_qualitat = (int)$r['total_controls'] > 0;

                // Calcular puntuació mitjana si hi ha controls
                $avg_punt = 0;
                if ($has_qualitat) {
                    $ids = $r['ids_qualitat'];
                    $q_data = $conn->query("SELECT * FROM Qualitat_Fruita WHERE id_qualitat IN ($ids)");
                    $sum = 0; $cnt = 0;
                    while ($qr = $q_data->fetch_assoc()) {
                        $sum += calcularPuntuacioQualitat($qr);
                        $cnt++;
                    }
                    $avg_punt = $cnt > 0 ? round($sum / $cnt) : 0;
                }
                $nivell = $has_qualitat ? obtenirNivellQualitat($avg_punt) : null;

                // Estat fruit badge
                $ef_badge = 'badge-secondary';
                if ($r['estat_fruit'] === 'Excel·lent') $ef_badge = 'badge-success';
                elseif ($r['estat_fruit'] === 'Bo') $ef_badge = 'badge-info';
                elseif ($r['estat_fruit'] === 'Acceptable') $ef_badge = 'badge-warning';
                elseif ($r['estat_fruit'] === 'Deficient' || $r['estat_fruit'] === 'Malmès') $ef_badge = 'badge-danger';
            ?>
                <div class="qualitat-resum-card <?= !$has_qualitat ? 'qualitat-sense-dades' : '' ?>">
                    <div class="qualitat-resum-header">
                        <div>
                            <h4 style="margin:0 0 4px 0; font-size:1rem;">
                                #<?= $r['id_collita'] ?> <?= htmlspecialchars($r['varietat']) ?>
                            </h4>
                            <div style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
                                <span class="badge badge-info" style="font-size:0.7rem;"><?= htmlspecialchars($r['parcela_nom']) ?></span>
                                <small style="color:var(--text-muted);"><?= date('d/m/Y', strtotime($r['data_inici'])) ?></small>
                                <span class="badge badge-success" style="font-size:0.7rem;"><?= number_format($r['quantitat'],0) ?> <?= $r['unitat'] ?></span>
                            </div>
                        </div>
                        <?php if ($has_qualitat): ?>
                            <div class="qualitat-mini-score" style="background:<?= $nivell['bg'] ?>; color:<?= $nivell['color'] ?>;">
                                <i class="<?= $nivell['icon'] ?>"></i>
                                <span><?= $avg_punt ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="qualitat-resum-footer">
                        <?php if ($has_qualitat): ?>
                            <span style="font-size:0.8rem; color:var(--text-muted);">
                                <i class="fa-solid fa-clipboard-list"></i> <?= $r['total_controls'] ?> control<?= $r['total_controls'] > 1 ? 's' : '' ?>
                                &nbsp;·&nbsp;
                                <span style="color:<?= $nivell['color'] ?>; font-weight:600;"><?= $nivell['label'] ?></span>
                            </span>
                        <?php else: ?>
                            <span style="font-size:0.8rem; color:var(--text-muted);"><i class="fa-solid fa-clipboard-question"></i> Sense control</span>
                        <?php endif; ?>
                        <a href="?p=qualitat&collita=<?= $r['id_collita'] ?>" class="btn btn-icon" style="background:var(--primary); font-size:0.8rem; padding:6px 12px;">
                            <i class="fa-solid fa-eye"></i> Detall
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> No hi ha collites registrades. Primer registra una collita a la secció de Collites.</p>
    <?php endif; ?>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <!-- ══════════════════════════════════════════ -->
    <!--  4. HISTORIAL COMPLET DE CONTROLS         -->
    <!-- ══════════════════════════════════════════ -->
    <h3><i class="fa-solid fa-table-list" style="margin-right:8px; color:var(--text-muted);"></i> Historial de controls de qualitat</h3>
    <?php
    $historial = $conn->query("
        SELECT q.*, c.varietat, c.data_inici AS data_collita, p.nom AS parcela_nom
        FROM Qualitat_Fruita q
        JOIN Collita c ON q.collita_id = c.id_collita
        JOIN `Parcel·la` p ON c.id_parcela = p.id_parcela
        ORDER BY q.data_control DESC
        LIMIT 50
    ");

    if ($historial && $historial->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Collita</th>
                    <th>Data control</th>
                    <th>Mida</th>
                    <th>Color</th>
                    <th>Sabor</th>
                    <th>Textura</th>
                    <th>Defectes</th>
                    <th>Qualitat</th>
                    <th>Accions</th>
                </tr>
                <?php while ($h = $historial->fetch_assoc()):
                    $punt = calcularPuntuacioQualitat($h);
                    $niv = obtenirNivellQualitat($punt);
                ?>
                    <tr>
                        <td><strong>#<?= $h['id_qualitat'] ?></strong></td>
                        <td>
                            <strong><?= htmlspecialchars($h['varietat']) ?></strong>
                            <br><small style="color:var(--text-muted);"><?= htmlspecialchars($h['parcela_nom']) ?></small>
                        </td>
                        <td><?= date('d/m/Y', strtotime($h['data_control'])) ?></td>
                        <td><?= htmlspecialchars($h['mida']) ?></td>
                        <td><?= htmlspecialchars($h['color']) ?></td>
                        <td>
                            <?php
                            $sabor_badges = ['Excel·lent'=>'badge-success','Bo'=>'badge-info','Acceptable'=>'badge-warning','Mediocre'=>'badge-danger','Dolent'=>'badge-danger'];
                            $sb = $sabor_badges[$h['sabor']] ?? 'badge-secondary';
                            echo "<span class='badge $sb'>{$h['sabor']}</span>";
                            ?>
                        </td>
                        <td><?= htmlspecialchars($h['textura']) ?></td>
                        <td style="max-width:150px; white-space:normal;">
                            <?php if ($h['defectes']): ?>
                                <small style="color:#991b1b;"><?= htmlspecialchars(mb_substr($h['defectes'], 0, 60)) ?><?= mb_strlen($h['defectes']) > 60 ? '...' : '' ?></small>
                            <?php else: ?>
                                <span class="badge badge-success" style="font-size:0.7rem;">Cap defecte</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span style="background:<?= $niv['bg'] ?>; color:<?= $niv['color'] ?>; padding:4px 10px; border-radius:8px; font-weight:700; font-size:0.9rem;">
                                    <?= $punt ?>
                                </span>
                                <span style="color:<?= $niv['color'] ?>; font-size:0.75rem; font-weight:600;"><?= $niv['label'] ?></span>
                            </div>
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="?p=qualitat&collita=<?= $h['collita_id'] ?>"
                               class="btn btn-icon" style="background:var(--info); font-size:0.75rem; padding:5px 8px;" title="Veure detall">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="?p=qualitat&eliminar_qualitat=1&id=<?= $h['id_qualitat'] ?>"
                               class="btn btn-red btn-icon" style="font-size:0.75rem; padding:5px 8px;"
                               onclick="return confirm('Eliminar control de qualitat #<?= $h['id_qualitat'] ?>?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha controls de qualitat.</p>
    <?php endif; ?>
</div>
