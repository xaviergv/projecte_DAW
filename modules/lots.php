<?php
// ────────────────────────────────────────────────
// AUTO-CREACIÓ DE LA TAULA (si no existeix)
// ────────────────────────────────────────────────
$conn->query("
    CREATE TABLE IF NOT EXISTS `Lot` (
        `id_lot` int(11) NOT NULL AUTO_INCREMENT,
        `codi_lot` varchar(30) NOT NULL,
        `id_collita` int(11) NOT NULL,
        `id_parcela` int(11) NOT NULL,
        `data_collita` date NOT NULL,
        `qualitat` varchar(50) DEFAULT NULL,
        `observacions` text DEFAULT NULL,
        PRIMARY KEY (`id_lot`),
        UNIQUE KEY `codi_lot` (`codi_lot`),
        KEY `id_collita` (`id_collita`),
        KEY `id_parcela` (`id_parcela`),
        CONSTRAINT `lot_ibfk_collita` FOREIGN KEY (`id_collita`) REFERENCES `Collita` (`id_collita`) ON DELETE CASCADE,
        CONSTRAINT `lot_ibfk_parcela` FOREIGN KEY (`id_parcela`) REFERENCES `Parcel·la` (`id_parcela`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
");

// ────────────────────────────────────────────────
// FUNCIÓ: Generar codi de lot únic (LOT-YYYY-NNNN)
// ────────────────────────────────────────────────
function generarCodiLot($conn) {
    $any = date('Y');
    $prefix = "LOT-$any-";
    $res = $conn->query("SELECT codi_lot FROM Lot WHERE codi_lot LIKE '$prefix%' ORDER BY codi_lot DESC LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $ultim = $res->fetch_assoc()['codi_lot'];
        $num = (int)substr($ultim, strlen($prefix)) + 1;
    } else {
        $num = 1;
    }
    return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
}

// ────────────────────────────────────────────────
// PROCESSAMENT POST
// ────────────────────────────────────────────────

if (isset($_POST['nou_lot'])) {
    $id_collita   = (int)($_POST['id_collita'] ?? 0);
    $qualitat     = trim($_POST['qualitat'] ?? '');
    $observacions = trim($_POST['observacions'] ?? '');

    if ($id_collita > 0) {
        // Obtenim les dades de la collita per auto-omplir
        $col = $conn->query("SELECT id_parcela, data_inici FROM Collita WHERE id_collita = $id_collita")->fetch_assoc();
        if ($col) {
            $id_parcela = $col['id_parcela'];
            $data_collita = $col['data_inici'];
            $codi_lot = generarCodiLot($conn);
            $qualitat_esc = $conn->real_escape_string($qualitat);
            $obs_esc = $conn->real_escape_string($observacions);

            $conn->query("
                INSERT INTO Lot (codi_lot, id_collita, id_parcela, data_collita, qualitat, observacions)
                VALUES ('$codi_lot', $id_collita, $id_parcela, '$data_collita', '$qualitat_esc', '$obs_esc')
            ");
            $_SESSION['msg'] = "Lot <strong>$codi_lot</strong> creat correctament!";
        } else {
            $_SESSION['err'] = "Collita no trobada";
        }
    } else {
        $_SESSION['err'] = "Cal seleccionar una collita";
    }
}

// ────────────────────────────────────────────────
// MODE DETALL (vista traçabilitat)
// ────────────────────────────────────────────────
$detall = null;
if (isset($_GET['detall_lot']) && (int)$_GET['detall_lot'] > 0) {
    $id_det = (int)$_GET['detall_lot'];
    $res = $conn->query("
        SELECT l.*, 
               c.varietat, c.quantitat, c.unitat, c.treballadors, c.temperatura, c.humitat, 
               c.estat_fruit, c.incidencies AS inc_collita, c.data_inici, c.data_final,
               p.nom AS nom_parcela, p.superficie, p.coordenades, p.textura
        FROM Lot l
        JOIN Collita c ON l.id_collita = c.id_collita
        JOIN Parcel·la p ON l.id_parcela = p.id_parcela
        WHERE l.id_lot = $id_det
    ");
    if ($res && $res->num_rows > 0) {
        $detall = $res->fetch_assoc();
    }
}
?>

<div class="section">

<?php if ($detall): ?>
    <!-- ════════════════════════════════════════════
         VISTA DETALL: TRAÇABILITAT DEL LOT
         ════════════════════════════════════════════ -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:15px;">
        <div>
            <h2 style="margin:0; font-size:1.6rem;">
                <i class="fa-solid fa-barcode" style="color:var(--primary); margin-right:10px;"></i>
                Traçabilitat: <?= htmlspecialchars($detall['codi_lot']) ?>
            </h2>
            <p style="color:var(--text-muted); margin:5px 0 0;">Fitxa completa de traçabilitat del lot</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <a href="?p=lots" class="btn" style="background:white; border:1px solid var(--border-color); color:var(--text-main); box-shadow:none;">
                <i class="fa-solid fa-arrow-left"></i> Tornar al llistat
            </a>
        </div>
    </div>

    <!-- QR Code -->
    <div style="display:flex; gap:30px; flex-wrap:wrap; margin-bottom:30px;">
        <div class="form-section" style="text-align:center; min-width:200px; flex:0 0 auto;">
            <h3 style="margin-bottom:15px;"><i class="fa-solid fa-qrcode" style="color:var(--primary); margin-right:8px;"></i> Codi QR</h3>
            <?php
            $qr_data = urlencode("LOT: " . $detall['codi_lot'] . " | Parcela: " . $detall['nom_parcela'] . " | Varietat: " . $detall['varietat'] . " | Data: " . $detall['data_collita'] . " | Qualitat: " . $detall['qualitat']);
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=$qr_data";
            ?>
            <img src="<?= $qr_url ?>" alt="QR del lot <?= htmlspecialchars($detall['codi_lot']) ?>" 
                 style="border-radius:12px; border:2px solid var(--border-color); padding:8px; background:white;">
            <p style="color:var(--text-muted); font-size:0.85rem; margin-top:10px;">
                Escaneja per veure la informació del lot
            </p>
            <a href="<?= $qr_url ?>" download="QR-<?= $detall['codi_lot'] ?>.png" class="btn btn-icon" style="margin-top:8px; font-size:0.85rem;">
                <i class="fa-solid fa-download"></i> Descarregar QR
            </a>
        </div>

        <!-- Dades del Lot -->
        <div class="form-section" style="flex:1; min-width:300px;">
            <h3><i class="fa-solid fa-tag" style="color:var(--warning); margin-right:8px;"></i> Dades del Lot</h3>
            <div class="form-grid" style="margin-bottom:0;">
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">CODI LOT</label>
                    <span style="font-size:1.3rem; font-weight:700; color:var(--primary);"><?= htmlspecialchars($detall['codi_lot']) ?></span>
                </div>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">DATA COLLITA</label>
                    <span style="font-weight:600;"><?= date('d/m/Y', strtotime($detall['data_collita'])) ?></span>
                </div>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">QUALITAT</label>
                    <?php
                    $qc = 'badge-secondary';
                    if ($detall['qualitat'] === 'Premium') $qc = 'badge-success';
                    elseif ($detall['qualitat'] === 'Primera') $qc = 'badge-info';
                    elseif ($detall['qualitat'] === 'Segona') $qc = 'badge-warning';
                    elseif ($detall['qualitat'] === 'Industrial') $qc = 'badge-danger';
                    ?>
                    <span class="badge <?= $qc ?>" style="font-size:0.9rem;"><?= htmlspecialchars($detall['qualitat'] ?: '-') ?></span>
                </div>
                <div class="form-group full-width">
                    <label style="color:var(--text-muted); font-size:0.8rem;">OBSERVACIONS DEL LOT</label>
                    <span><?= nl2br(htmlspecialchars($detall['observacions'] ?: 'Cap observació')) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Informació de la Collita -->
    <div style="display:flex; gap:30px; flex-wrap:wrap;">
        <div class="form-section" style="flex:1; min-width:300px;">
            <h3><i class="fa-solid fa-wheat-awn" style="color:var(--primary-dark); margin-right:8px;"></i> Dades de la Collita</h3>
            <div class="form-grid" style="margin-bottom:0;">
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">VARIETAT</label>
                    <span style="font-weight:600;"><?= htmlspecialchars($detall['varietat']) ?></span>
                </div>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">QUANTITAT</label>
                    <span class="badge badge-success" style="font-size:0.9rem;"><?= number_format($detall['quantitat'], 2) ?> <?= htmlspecialchars($detall['unitat']) ?></span>
                </div>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">PERÍODE</label>
                    <span><?= date('d/m/Y', strtotime($detall['data_inici'])) ?>
                    <?php if ($detall['data_final']): ?> → <?= date('d/m/Y', strtotime($detall['data_final'])) ?><?php endif; ?></span>
                </div>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">TREBALLADORS</label>
                    <span><i class="fa-solid fa-user-group" style="color:var(--text-muted); margin-right:5px;"></i><?= $detall['treballadors'] ?: '-' ?></span>
                </div>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">TEMPERATURA</label>
                    <span><?= $detall['temperatura'] !== null ? $detall['temperatura'] . '°C' : '-' ?></span>
                </div>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">HUMITAT</label>
                    <span><?= $detall['humitat'] !== null ? $detall['humitat'] . '%' : '-' ?></span>
                </div>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">ESTAT FRUIT</label>
                    <?php
                    $ef = $detall['estat_fruit'];
                    $efc = 'badge-secondary';
                    if ($ef === 'Excel·lent') $efc = 'badge-success';
                    elseif ($ef === 'Bo') $efc = 'badge-info';
                    elseif ($ef === 'Acceptable') $efc = 'badge-warning';
                    elseif ($ef === 'Deficient' || $ef === 'Malmès') $efc = 'badge-danger';
                    ?>
                    <span class="badge <?= $efc ?>"><?= htmlspecialchars($ef ?: '-') ?></span>
                </div>
            </div>
        </div>

        <!-- Informació de la Parcel·la -->
        <div class="form-section" style="flex:1; min-width:300px;">
            <h3><i class="fa-solid fa-map-location-dot" style="color:var(--info); margin-right:8px;"></i> Dades de la Parcel·la</h3>
            <div class="form-grid" style="margin-bottom:0;">
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">NOM</label>
                    <span style="font-weight:600; font-size:1.1rem;"><?= htmlspecialchars($detall['nom_parcela']) ?></span>
                </div>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">SUPERFÍCIE</label>
                    <span><?= number_format($detall['superficie'], 2) ?> ha</span>
                </div>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">COORDENADES</label>
                    <span><i class="fa-solid fa-location-dot" style="color:var(--danger); margin-right:5px;"></i><?= htmlspecialchars($detall['coordenades'] ?: '-') ?></span>
                </div>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-size:0.8rem;">TEXTURA SÒL</label>
                    <span><?= htmlspecialchars($detall['textura'] ?: '-') ?></span>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ════════════════════════════════════════════
         FORMULARI + LLISTAT DE LOTS
         ════════════════════════════════════════════ -->

    <!-- Formulari per crear lot -->
    <div class="form-section">
        <h3><i class="fa-solid fa-barcode" style="color:var(--primary); margin-right:8px;"></i> Crear nou lot de traçabilitat</h3>
        <p style="color:var(--text-muted); margin-bottom:20px; font-size:0.9rem;">
            <i class="fa-solid fa-circle-info" style="margin-right:5px;"></i> 
            El codi de lot es genera automàticament (format: <strong>LOT-<?= date('Y') ?>-XXXX</strong>). 
            La parcel·la i la data s'associen automàticament des de la collita seleccionada.
        </p>
        <form method="post">
            <input type="hidden" name="p" value="lots">
            <input type="hidden" name="nou_lot" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Collita associada: <span style="color:var(--danger);">*</span></label>
                    <select name="id_collita" required>
                        <option value="">Selecciona collita</option>
                        <?php
                        $collites = $conn->query("
                            SELECT c.id_collita, c.varietat, c.data_inici, c.quantitat, c.unitat, p.nom AS parcela
                            FROM Collita c
                            JOIN Parcel·la p ON c.id_parcela = p.id_parcela
                            ORDER BY c.data_inici DESC
                        ");
                        while ($co = $collites->fetch_assoc()) {
                            echo '<option value="' . $co['id_collita'] . '">'
                                . htmlspecialchars($co['parcela']) . ' — ' 
                                . htmlspecialchars($co['varietat']) . ' (' 
                                . date('d/m/Y', strtotime($co['data_inici'])) . ') — '
                                . number_format($co['quantitat'], 0) . ' ' . $co['unitat']
                                . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Qualitat:</label>
                    <select name="qualitat">
                        <option value="">Selecciona</option>
                        <option value="Premium">Premium</option>
                        <option value="Primera">Primera</option>
                        <option value="Segona">Segona</option>
                        <option value="Industrial">Industrial</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label>Observacions:</label>
                    <textarea name="observacions" rows="2" placeholder="ex: Lot destinat a exportació, calibre gran..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Generar lot</button>
        </form>
    </div>

    <!-- Llistat de lots -->
    <h3><i class="fa-solid fa-list-ul" style="margin-right:8px; color:var(--text-muted);"></i> Registre de lots</h3>
    <?php
    $lots = $conn->query("
        SELECT l.*, c.varietat, c.quantitat, c.unitat, p.nom AS nom_parcela
        FROM Lot l
        JOIN Collita c ON l.id_collita = c.id_collita
        JOIN Parcel·la p ON l.id_parcela = p.id_parcela
        ORDER BY l.id_lot DESC
    ");

    if ($lots && $lots->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>Codi Lot</th>
                    <th>Parcel·la</th>
                    <th>Varietat</th>
                    <th>Data collita</th>
                    <th>Quantitat</th>
                    <th>Qualitat</th>
                    <th>Accions</th>
                </tr>
                <?php while ($l = $lots->fetch_assoc()): ?>
                    <tr>
                        <td><strong style="color:var(--primary); font-size:0.95rem;"><?= htmlspecialchars($l['codi_lot']) ?></strong></td>
                        <td><span class="badge badge-info"><i class="fa-solid fa-map-location"></i> <?= htmlspecialchars($l['nom_parcela']) ?></span></td>
                        <td><?= htmlspecialchars($l['varietat']) ?></td>
                        <td><?= date('d/m/Y', strtotime($l['data_collita'])) ?></td>
                        <td><span class="badge badge-success"><?= number_format($l['quantitat'], 2) ?> <?= htmlspecialchars($l['unitat']) ?></span></td>
                        <td>
                            <?php
                            $qc = 'badge-secondary';
                            if ($l['qualitat'] === 'Premium') $qc = 'badge-success';
                            elseif ($l['qualitat'] === 'Primera') $qc = 'badge-info';
                            elseif ($l['qualitat'] === 'Segona') $qc = 'badge-warning';
                            elseif ($l['qualitat'] === 'Industrial') $qc = 'badge-danger';
                            echo "<span class='badge $qc'>" . htmlspecialchars($l['qualitat'] ?: '-') . "</span>";
                            ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="?p=lots&detall_lot=<?= $l['id_lot'] ?>"
                               class="btn btn-icon" style="background:var(--info); color:#fff; padding:8px 12px; font-size:0.85rem;" title="Veure detall">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="?p=lots&eliminar=lot&id=<?= $l['id_lot'] ?>"
                               class="btn btn-red btn-icon" onclick="return confirm('Segur que vols eliminar aquest lot?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha lots registrats. Crea'n un associat a una collita!</p>
    <?php endif; ?>

<?php endif; ?>
</div>
