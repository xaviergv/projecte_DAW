<?php
// ════════════════════════════════════════════════════════════
//  SISTEMA D'INVENTARI — High Elo
//  CRUD complet amb relació a Producte + stock actual
// ════════════════════════════════════════════════════════════

// Auto-creació de la taula Inventari
$conn->query("
    CREATE TABLE IF NOT EXISTS `Inventari` (
        `id_inventari` int(11) NOT NULL AUTO_INCREMENT,
        `producte_id`  int(11) NOT NULL,
        `quantitat`    decimal(10,2) NOT NULL DEFAULT 0.00,
        `unitat_mesura` varchar(20) NOT NULL DEFAULT 'L',
        `data_compra`  date DEFAULT NULL,
        `caducitat`    date DEFAULT NULL,
        `proveidor`    varchar(150) DEFAULT NULL,
        `preu_unitari` decimal(10,2) DEFAULT NULL,
        `numero_lot`   varchar(50) DEFAULT NULL,
        `ubicacio`     varchar(100) DEFAULT NULL,
        `observacions` text DEFAULT NULL,
        `created_at`   datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_inventari`),
        KEY `fk_inventari_producte` (`producte_id`),
        CONSTRAINT `fk_inventari_producte` FOREIGN KEY (`producte_id`) 
            REFERENCES `Producte` (`id_producte`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
");

// ────────────────────────────────────────────────
// PROCESSAR FORMULARIS (CREATE / UPDATE)
// ────────────────────────────────────────────────

// CREATE — Afegir entrada d'inventari
if (isset($_POST['nou_inventari'])) {
    $producte_id   = (int)($_POST['producte_id'] ?? 0);
    $quantitat     = (float)($_POST['quantitat'] ?? 0);
    $unitat_mesura = trim($_POST['unitat_mesura'] ?? 'L');
    $data_compra   = $_POST['data_compra'] ?? null;
    $caducitat     = !empty($_POST['caducitat']) ? $_POST['caducitat'] : null;
    $proveidor     = trim($_POST['proveidor'] ?? '');
    $preu_unitari  = !empty($_POST['preu_unitari']) ? (float)$_POST['preu_unitari'] : null;
    $numero_lot    = trim($_POST['numero_lot'] ?? '');
    $ubicacio      = trim($_POST['ubicacio'] ?? '');
    $observacions  = trim($_POST['observacions'] ?? '');

    if ($producte_id > 0 && $quantitat > 0 && $data_compra) {
        $stmt = $conn->prepare("
            INSERT INTO Inventari 
            (producte_id, quantitat, unitat_mesura, data_compra, caducitat, proveidor, preu_unitari, numero_lot, ubicacio, observacions)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("idsssdssss",
            $producte_id, $quantitat, $unitat_mesura, $data_compra,
            $caducitat, $proveidor, $preu_unitari, $numero_lot, $ubicacio, $observacions
        );
        $stmt->execute();
        $stmt->close();
        $_SESSION['msg'] = "Entrada d'inventari afegida correctament! +" . number_format($quantitat, 2) . " $unitat_mesura";
    } else {
        $_SESSION['err'] = "Producte, quantitat i data de compra són obligatoris.";
    }
    echo "<script>window.location.href='?p=inventari';</script>";
    exit;
}

// UPDATE — Editar entrada d'inventari
if (isset($_POST['editar_inventari'])) {
    $id_inventari  = (int)($_POST['id_inventari'] ?? 0);
    $producte_id   = (int)($_POST['producte_id'] ?? 0);
    $quantitat     = (float)($_POST['quantitat'] ?? 0);
    $unitat_mesura = trim($_POST['unitat_mesura'] ?? 'L');
    $data_compra   = $_POST['data_compra'] ?? null;
    $caducitat     = !empty($_POST['caducitat']) ? $_POST['caducitat'] : null;
    $proveidor     = trim($_POST['proveidor'] ?? '');
    $preu_unitari  = !empty($_POST['preu_unitari']) ? (float)$_POST['preu_unitari'] : null;
    $numero_lot    = trim($_POST['numero_lot'] ?? '');
    $ubicacio      = trim($_POST['ubicacio'] ?? '');
    $observacions  = trim($_POST['observacions'] ?? '');

    if ($id_inventari > 0 && $producte_id > 0 && $quantitat >= 0) {
        $stmt = $conn->prepare("
            UPDATE Inventari SET
                producte_id = ?, quantitat = ?, unitat_mesura = ?, data_compra = ?,
                caducitat = ?, proveidor = ?, preu_unitari = ?, numero_lot = ?,
                ubicacio = ?, observacions = ?
            WHERE id_inventari = ?
        ");
        $stmt->bind_param("idssssdsssi",
            $producte_id, $quantitat, $unitat_mesura, $data_compra,
            $caducitat, $proveidor, $preu_unitari, $numero_lot, $ubicacio, $observacions,
            $id_inventari
        );
        $stmt->execute();
        $stmt->close();
        $_SESSION['msg'] = "Entrada d'inventari #$id_inventari actualitzada correctament!";
    } else {
        $_SESSION['err'] = "Dades invàlides per a l'actualització.";
    }
    echo "<script>window.location.href='?p=inventari';</script>";
    exit;
}

// DELETE — Processat aquí per evitar conflictes amb el switch d'index.php
if (isset($_GET['eliminar_inventari'])) {
    $id_del = (int)($_GET['id'] ?? 0);
    if ($id_del > 0) {
        $conn->query("DELETE FROM Inventari WHERE id_inventari = $id_del");
        $_SESSION['msg'] = "Entrada d'inventari #$id_del eliminada.";
    }
    echo "<script>window.location.href='?p=inventari';</script>";
    exit;
}

// ────────────────────────────────────────────────
// DADES PER A LA VISTA
// ────────────────────────────────────────────────

// Stock actual per producte (resum)
$stock_resum = $conn->query("
    SELECT p.id_producte, p.nom_comercial, p.tipus, p.quantitat_minima,
           COALESCE(SUM(i.quantitat), 0) AS stock_total,
           MIN(i.caducitat) AS propera_caducitat,
           COUNT(i.id_inventari) AS num_lots
    FROM Producte p
    LEFT JOIN Inventari i ON p.id_producte = i.producte_id
    GROUP BY p.id_producte
    ORDER BY p.nom_comercial ASC
");

// Determinar si estem en mode edició
$editant = null;
if (isset($_GET['editar']) && (int)$_GET['editar'] > 0) {
    $id_edit = (int)$_GET['editar'];
    $res_edit = $conn->query("SELECT * FROM Inventari WHERE id_inventari = $id_edit");
    if ($res_edit && $res_edit->num_rows > 0) {
        $editant = $res_edit->fetch_assoc();
    }
}
?>

<div class="section">

    <!-- ══════════════════════════════════════════ -->
    <!--  1. RESUM STOCK ACTUAL PER PRODUCTE       -->
    <!-- ══════════════════════════════════════════ -->
    <h3><i class="fa-solid fa-warehouse" style="color:var(--primary); margin-right:8px;"></i> Stock actual per producte</h3>
    
    <?php if ($stock_resum && $stock_resum->num_rows > 0): ?>
        <div class="inventari-stock-grid">
            <?php while ($s = $stock_resum->fetch_assoc()):
                $stock = (float)$s['stock_total'];
                $minim = (float)$s['quantitat_minima'];
                $percentatge = $minim > 0 ? min(100, ($stock / $minim) * 100) : ($stock > 0 ? 100 : 0);
                
                // Definir color segons nivell de stock
                if ($stock <= 0) {
                    $stock_class = 'stock-critic';
                    $bar_color = '#dc2626';
                } elseif ($stock < $minim) {
                    $stock_class = 'stock-baix';
                    $bar_color = '#f59e0b';
                } else {
                    $stock_class = 'stock-ok';
                    $bar_color = '#10b981';
                }

                // Caducitat propera
                $cad_text = '';
                if ($s['propera_caducitat']) {
                    $dies_cad = (int)((strtotime($s['propera_caducitat']) - time()) / 86400);
                    if ($dies_cad < 0) $cad_text = '<span style="color:#dc2626; font-weight:600;"><i class="fa-solid fa-skull"></i> CADUCAT</span>';
                    elseif ($dies_cad <= 30) $cad_text = '<span style="color:#f59e0b;"><i class="fa-solid fa-clock"></i> ' . $dies_cad . 'd</span>';
                    else $cad_text = '<span style="color:var(--text-muted);"><i class="fa-regular fa-calendar"></i> ' . date('d/m/Y', strtotime($s['propera_caducitat'])) . '</span>';
                }

                // Badge per tipus
                $tipus_badge = 'badge-secondary';
                if ($s['tipus'] == 'Fertilitzant') $tipus_badge = 'badge-success';
                if ($s['tipus'] == 'Insecticida') $tipus_badge = 'badge-danger';
                if ($s['tipus'] == 'Fungicida') $tipus_badge = 'badge-info';
                if ($s['tipus'] == 'Herbicida') $tipus_badge = 'badge-warning';
            ?>
                <div class="stock-card <?= $stock_class ?>">
                    <div class="stock-card-header">
                        <div>
                            <h4 style="margin:0 0 4px 0; font-size:1rem;"><?= htmlspecialchars($s['nom_comercial']) ?></h4>
                            <span class="badge <?= $tipus_badge ?>" style="font-size:0.7rem;"><?= htmlspecialchars($s['tipus']) ?></span>
                        </div>
                        <div class="stock-numero">
                            <span class="stock-valor"><?= number_format($stock, 1) ?></span>
                            <small style="color:var(--text-muted);">/ <?= number_format($minim, 0) ?> mín</small>
                        </div>
                    </div>
                    <div class="stock-barra-fons">
                        <div class="stock-barra" style="width:<?= $percentatge ?>%; background:<?= $bar_color ?>;"></div>
                    </div>
                    <div class="stock-card-footer">
                        <span style="font-size:0.8rem; color:var(--text-muted);">
                            <i class="fa-solid fa-cubes"></i> <?= $s['num_lots'] ?> lot<?= $s['num_lots'] != 1 ? 's' : '' ?>
                        </span>
                        <?= $cad_text ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> No hi ha productes registrats.</p>
    <?php endif; ?>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <!-- ══════════════════════════════════════════ -->
    <!--  2. FORMULARI CREATE / UPDATE              -->
    <!-- ══════════════════════════════════════════ -->
    <div class="form-section">
        <?php if ($editant): ?>
            <h3><i class="fa-solid fa-pen-to-square" style="color:var(--info); margin-right:8px;"></i> Editar entrada d'inventari #<?= $editant['id_inventari'] ?></h3>
        <?php else: ?>
            <h3><i class="fa-solid fa-plus-circle" style="color:var(--primary); margin-right:8px;"></i> Afegir nova entrada d'inventari</h3>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="p" value="inventari">
            <?php if ($editant): ?>
                <input type="hidden" name="editar_inventari" value="1">
                <input type="hidden" name="id_inventari" value="<?= $editant['id_inventari'] ?>">
            <?php else: ?>
                <input type="hidden" name="nou_inventari" value="1">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label>Producte: <span style="color:var(--danger);">*</span></label>
                    <select name="producte_id" required>
                        <option value="">Selecciona producte</option>
                        <?php
                        $prods = $conn->query("SELECT id_producte, nom_comercial, tipus FROM Producte ORDER BY nom_comercial");
                        while ($pr = $prods->fetch_assoc()) {
                            $sel = ($editant && $editant['producte_id'] == $pr['id_producte']) ? 'selected' : '';
                            echo '<option value="' . $pr['id_producte'] . '" ' . $sel . '>' 
                                 . htmlspecialchars($pr['nom_comercial']) . ' (' . htmlspecialchars($pr['tipus']) . ')</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Quantitat: <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="quantitat" step="0.01" min="0" required
                           value="<?= $editant ? $editant['quantitat'] : '' ?>" placeholder="ex: 50.00">
                </div>

                <div class="form-group">
                    <label>Unitat de mesura:</label>
                    <select name="unitat_mesura">
                        <?php
                        $unitats = ['L', 'kg', 'g', 'ml', 'unitats'];
                        foreach ($unitats as $u) {
                            $sel = ($editant && $editant['unitat_mesura'] == $u) ? 'selected' : '';
                            echo "<option value=\"$u\" $sel>$u</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data de compra: <span style="color:var(--danger);">*</span></label>
                    <input type="date" name="data_compra" required
                           value="<?= $editant ? $editant['data_compra'] : date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label>Data de caducitat:</label>
                    <input type="date" name="caducitat"
                           value="<?= $editant ? $editant['caducitat'] : '' ?>">
                </div>

                <div class="form-group">
                    <label>Proveïdor:</label>
                    <input type="text" name="proveidor" placeholder="ex: AgroQuímica S.L."
                           value="<?= $editant ? htmlspecialchars($editant['proveidor']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Preu unitari (€):</label>
                    <input type="number" name="preu_unitari" step="0.01" min="0" placeholder="ex: 12.50"
                           value="<?= $editant ? $editant['preu_unitari'] : '' ?>">
                </div>

                <div class="form-group">
                    <label>Número de lot:</label>
                    <input type="text" name="numero_lot" placeholder="ex: LOT-2026-04A"
                           value="<?= $editant ? htmlspecialchars($editant['numero_lot']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Ubicació magatzem:</label>
                    <input type="text" name="ubicacio" placeholder="ex: Nau A - Prestatge 3"
                           value="<?= $editant ? htmlspecialchars($editant['ubicacio']) : '' ?>">
                </div>

                <div class="form-group full-width">
                    <label>Observacions:</label>
                    <textarea name="observacions" rows="2" placeholder="Notes addicionals..."><?= $editant ? htmlspecialchars($editant['observacions']) : '' ?></textarea>
                </div>
            </div>

            <div style="display:flex; gap:10px;">
                <?php if ($editant): ?>
                    <button type="submit" class="btn" style="background:var(--info);">
                        <i class="fa-solid fa-floppy-disk"></i> Desar canvis
                    </button>
                    <a href="?p=inventari" class="btn btn-red">
                        <i class="fa-solid fa-xmark"></i> Cancel·lar
                    </a>
                <?php else: ?>
                    <button type="submit" class="btn">
                        <i class="fa-solid fa-plus"></i> Afegir entrada
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ══════════════════════════════════════════ -->
    <!--  3. LLISTAT COMPLET D'INVENTARI (READ)    -->
    <!-- ══════════════════════════════════════════ -->
    <h3><i class="fa-solid fa-clipboard-list" style="margin-right:8px; color:var(--text-muted);"></i> Registre complet d'inventari</h3>
    <?php
    $inventari = $conn->query("
        SELECT i.*, p.nom_comercial, p.tipus
        FROM Inventari i
        JOIN Producte p ON i.producte_id = p.id_producte
        ORDER BY i.data_compra DESC, i.id_inventari DESC
    ");

    if ($inventari && $inventari->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Producte</th>
                    <th>Quantitat</th>
                    <th>Data compra</th>
                    <th>Caducitat</th>
                    <th>Proveïdor</th>
                    <th>Lot</th>
                    <th>Ubicació</th>
                    <th>Preu unit.</th>
                    <th>Accions</th>
                </tr>
                <?php while ($inv = $inventari->fetch_assoc()):
                    // Color de caducitat
                    $cad_style = '';
                    if ($inv['caducitat']) {
                        $dies = (int)((strtotime($inv['caducitat']) - time()) / 86400);
                        if ($dies < 0) $cad_style = 'color:#dc2626; font-weight:600;';
                        elseif ($dies <= 30) $cad_style = 'color:#f59e0b; font-weight:600;';
                    }

                    // Badge tipus
                    $tb = 'badge-secondary';
                    if ($inv['tipus'] == 'Fertilitzant') $tb = 'badge-success';
                    if ($inv['tipus'] == 'Insecticida') $tb = 'badge-danger';
                    if ($inv['tipus'] == 'Fungicida') $tb = 'badge-info';
                    if ($inv['tipus'] == 'Herbicida') $tb = 'badge-warning';
                ?>
                    <tr<?= ($inv['quantitat'] <= 0) ? ' style="opacity:0.5;"' : '' ?>>
                        <td><strong>#<?= $inv['id_inventari'] ?></strong></td>
                        <td>
                            <?= htmlspecialchars($inv['nom_comercial']) ?>
                            <br><span class="badge <?= $tb ?>" style="font-size:0.65rem;"><?= htmlspecialchars($inv['tipus']) ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $inv['quantitat'] <= 0 ? 'badge-danger' : 'badge-success' ?>" style="font-size:0.85rem;">
                                <?= number_format($inv['quantitat'], 2) ?> <?= htmlspecialchars($inv['unitat_mesura']) ?>
                            </span>
                        </td>
                        <td><?= $inv['data_compra'] ? date('d/m/Y', strtotime($inv['data_compra'])) : '-' ?></td>
                        <td style="<?= $cad_style ?>">
                            <?php if ($inv['caducitat']): ?>
                                <?= date('d/m/Y', strtotime($inv['caducitat'])) ?>
                                <?php if (isset($dies) && $dies < 0): ?>
                                    <br><small><i class="fa-solid fa-skull"></i> Caducat</small>
                                <?php elseif (isset($dies) && $dies <= 30): ?>
                                    <br><small><i class="fa-solid fa-clock"></i> <?= $dies ?>d restants</small>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($inv['proveidor'] ?: '-') ?></td>
                        <td><small><?= htmlspecialchars($inv['numero_lot'] ?: '-') ?></small></td>
                        <td><small><?= htmlspecialchars($inv['ubicacio'] ?: '-') ?></small></td>
                        <td><?= $inv['preu_unitari'] ? number_format($inv['preu_unitari'], 2) . ' €' : '-' ?></td>
                        <td style="white-space:nowrap;">
                            <a href="?p=inventari&editar=<?= $inv['id_inventari'] ?>" 
                               class="btn btn-icon" style="background:var(--info); font-size:0.8rem; padding:6px 10px;" title="Editar">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            <a href="?p=inventari&eliminar_inventari=1&id=<?= $inv['id_inventari'] ?>" 
                               class="btn btn-red btn-icon" style="font-size:0.8rem; padding:6px 10px;"
                               onclick="return confirm('Segur que vols eliminar l\'entrada d\'inventari #<?= $inv['id_inventari'] ?>?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> L'inventari està buit. Afegeix la primera entrada amb el formulari de dalt.</p>
    <?php endif; ?>
</div>
