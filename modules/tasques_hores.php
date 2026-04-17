<?php
// ════════════════════════════════════════════════════════════
//  SISTEMA DE GESTIÓ DE TASQUES I HORES — High Elo
//  Taules: Tasques_Gestio + Hores_Treball
// ════════════════════════════════════════════════════════════

// Auto-creació de les taules
$conn->query("
    CREATE TABLE IF NOT EXISTS `Tasques_Gestio` (
        `id_tasca` int(11) NOT NULL AUTO_INCREMENT,
        `tipus` varchar(80) NOT NULL,
        `parcela_id` int(11) DEFAULT NULL,
        `data_tasca` date NOT NULL,
        `durada_estimada` decimal(5,2) DEFAULT 0.00 COMMENT 'hores',
        `treballador_id` int(11) DEFAULT NULL,
        `descripcio` text DEFAULT NULL,
        `prioritat` enum('Baixa','Normal','Alta','Urgent') NOT NULL DEFAULT 'Normal',
        `estat` enum('Pendent','En curs','Completada','Cancel·lada') NOT NULL DEFAULT 'Pendent',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_tasca`),
        KEY `fk_tg_parcela` (`parcela_id`),
        KEY `fk_tg_treballador` (`treballador_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
");

$conn->query("
    CREATE TABLE IF NOT EXISTS `Hores_Treball` (
        `id_hora` int(11) NOT NULL AUTO_INCREMENT,
        `treballador_id` int(11) NOT NULL,
        `tasca_id` int(11) DEFAULT NULL,
        `hora_inici` datetime NOT NULL,
        `hora_final` datetime DEFAULT NULL,
        `observacions` text DEFAULT NULL,
        PRIMARY KEY (`id_hora`),
        KEY `fk_ht_treballador` (`treballador_id`),
        KEY `fk_ht_tasca` (`tasca_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
");

// ────────────────────────────────────────────────
// PROCESSAR FORMULARIS
// ────────────────────────────────────────────────

// CREATE — Nova tasca
if (isset($_POST['nova_tasca'])) {
    $tipus = trim($_POST['tipus'] ?? '');
    $parcela_id = !empty($_POST['parcela_id']) ? (int)$_POST['parcela_id'] : null;
    $data_tasca = $_POST['data_tasca'] ?? date('Y-m-d');
    $durada_estimada = (float)($_POST['durada_estimada'] ?? 0);
    $treballador_id = !empty($_POST['treballador_id']) ? (int)$_POST['treballador_id'] : null;
    $descripcio = trim($_POST['descripcio'] ?? '');
    $prioritat = $_POST['prioritat'] ?? 'Normal';

    if ($tipus && $data_tasca) {
        $stmt = $conn->prepare("
            INSERT INTO Tasques_Gestio (tipus, parcela_id, data_tasca, durada_estimada, treballador_id, descripcio, prioritat)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sisdiss", $tipus, $parcela_id, $data_tasca, $durada_estimada, $treballador_id, $descripcio, $prioritat);
        $stmt->execute();
        $stmt->close();
        $_SESSION['msg'] = "Tasca creada correctament!";
    } else {
        $_SESSION['err'] = "El tipus i la data són obligatoris.";
    }
    echo "<script>window.location.href='?p=tasques_hores';</script>";
    exit;
}

// Canviar estat d'una tasca
if (isset($_GET['canviar_estat'])) {
    $id_t = (int)$_GET['canviar_estat'];
    $nou_estat = $_GET['estat'] ?? '';
    $estats_valids = ['Pendent', 'En curs', 'Completada', 'Cancel·lada'];
    if ($id_t > 0 && in_array($nou_estat, $estats_valids)) {
        $stmt = $conn->prepare("UPDATE Tasques_Gestio SET estat = ? WHERE id_tasca = ?");
        $stmt->bind_param("si", $nou_estat, $id_t);
        $stmt->execute();
        $stmt->close();
        $_SESSION['msg'] = "Estat actualitzat a '$nou_estat'.";
    }
    echo "<script>window.location.href='?p=tasques_hores';</script>";
    exit;
}

// Eliminar tasca
if (isset($_GET['eliminar_tasca'])) {
    $id_del = (int)$_GET['id'];
    if ($id_del > 0) {
        $conn->query("DELETE FROM Hores_Treball WHERE tasca_id = $id_del");
        $conn->query("DELETE FROM Tasques_Gestio WHERE id_tasca = $id_del");
        $_SESSION['msg'] = "Tasca #$id_del i registres d'hores associats eliminats.";
    }
    echo "<script>window.location.href='?p=tasques_hores';</script>";
    exit;
}

// REGISTRAR INICI de feina (fitxar entrada)
if (isset($_POST['fitxar_inici'])) {
    $treb_id = (int)($_POST['treballador_id'] ?? 0);
    $tasca_id = !empty($_POST['tasca_id']) ? (int)$_POST['tasca_id'] : null;
    $obs = trim($_POST['observacions'] ?? '');

    if ($treb_id > 0) {
        // Comprovar si ja té un fitxatge obert
        $check = $conn->prepare("SELECT id_hora FROM Hores_Treball WHERE treballador_id = ? AND hora_final IS NULL LIMIT 1");
        $check->bind_param("i", $treb_id);
        $check->execute();
        $check_res = $check->get_result();

        if ($check_res->num_rows > 0) {
            $_SESSION['err'] = "Aquest treballador ja té un fitxatge obert! Primer registra la sortida.";
        } else {
            $ara = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("INSERT INTO Hores_Treball (treballador_id, tasca_id, hora_inici, observacions) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $treb_id, $tasca_id, $ara, $obs);
            $stmt->execute();
            $stmt->close();

            // Posar tasca "En curs" si cal
            if ($tasca_id) {
                $conn->query("UPDATE Tasques_Gestio SET estat = 'En curs' WHERE id_tasca = $tasca_id AND estat = 'Pendent'");
            }
            $_SESSION['msg'] = "Fitxatge d'entrada registrat a les " . date('H:i') . "h.";
        }
        $check->close();
    } else {
        $_SESSION['err'] = "Selecciona un treballador.";
    }
    echo "<script>window.location.href='?p=tasques_hores';</script>";
    exit;
}

// REGISTRAR FINAL de feina (fitxar sortida)
if (isset($_GET['fitxar_sortida'])) {
    $id_hora = (int)$_GET['fitxar_sortida'];
    if ($id_hora > 0) {
        $ara = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("UPDATE Hores_Treball SET hora_final = ? WHERE id_hora = ? AND hora_final IS NULL");
        $stmt->bind_param("si", $ara, $id_hora);
        $stmt->execute();
        $stmt->close();
        $_SESSION['msg'] = "Sortida registrada a les " . date('H:i') . "h.";
    }
    echo "<script>window.location.href='?p=tasques_hores';</script>";
    exit;
}

// Eliminar registre d'hores
if (isset($_GET['eliminar_hora'])) {
    $id_del = (int)$_GET['id'];
    if ($id_del > 0) {
        $conn->query("DELETE FROM Hores_Treball WHERE id_hora = $id_del");
        $_SESSION['msg'] = "Registre d'hores eliminat.";
    }
    echo "<script>window.location.href='?p=tasques_hores';</script>";
    exit;
}

// ────────────────────────────────────────────────
// DADES PER A LA VISTA
// ────────────────────────────────────────────────

// Hores totals per treballador (resum)
$resum_hores = $conn->query("
    SELECT t.id_treballador, t.nom, t.cognoms,
           COUNT(h.id_hora) AS total_registres,
           COALESCE(SUM(
               CASE WHEN h.hora_final IS NOT NULL
               THEN TIMESTAMPDIFF(MINUTE, h.hora_inici, h.hora_final) / 60.0
               ELSE 0 END
           ), 0) AS hores_totals,
           SUM(CASE WHEN h.hora_final IS NULL THEN 1 ELSE 0 END) AS fitxatges_oberts
    FROM Treballador t
    LEFT JOIN Hores_Treball h ON t.id_treballador = h.treballador_id
    WHERE t.actiu = 1
    GROUP BY t.id_treballador
    ORDER BY hores_totals DESC
");

// Fitxatges oberts (en curs)
$fitxatges_oberts = $conn->query("
    SELECT h.id_hora, h.hora_inici, h.observacions,
           t.nom AS treb_nom, t.cognoms AS treb_cognoms,
           tg.tipus AS tasca_tipus, tg.id_tasca
    FROM Hores_Treball h
    JOIN Treballador t ON h.treballador_id = t.id_treballador
    LEFT JOIN Tasques_Gestio tg ON h.tasca_id = tg.id_tasca
    WHERE h.hora_final IS NULL
    ORDER BY h.hora_inici ASC
");
?>

<div class="section">

    <!-- ══════════════════════════════════════════ -->
    <!--  1. HORES TOTALS PER TREBALLADOR          -->
    <!-- ══════════════════════════════════════════ -->
    <h3><i class="fa-solid fa-clock" style="color:var(--primary); margin-right:8px;"></i> Hores totals per treballador</h3>

    <?php if ($resum_hores && $resum_hores->num_rows > 0): ?>
        <div class="hores-resum-grid">
            <?php while ($r = $resum_hores->fetch_assoc()):
                $hores = (float)$r['hores_totals'];
                $h = floor($hores);
                $m = round(($hores - $h) * 60);
                $oberts = (int)$r['fitxatges_oberts'];
            ?>
                <div class="hores-card <?= $oberts > 0 ? 'hores-card-actiu' : '' ?>">
                    <div class="hores-card-avatar">
                        <i class="fa-solid fa-user-hard-hat" style="font-size:1.2rem;"></i>
                    </div>
                    <div class="hores-card-info">
                        <strong><?= htmlspecialchars($r['nom'] . ' ' . $r['cognoms']) ?></strong>
                        <div class="hores-card-stats">
                            <span class="hores-total-badge">
                                <i class="fa-regular fa-clock"></i> <?= $h ?>h <?= $m ?>min
                            </span>
                            <span style="color:var(--text-muted); font-size:0.8rem;">
                                <?= $r['total_registres'] ?> registre<?= $r['total_registres'] != 1 ? 's' : '' ?>
                            </span>
                            <?php if ($oberts > 0): ?>
                                <span class="badge badge-success" style="font-size:0.7rem; animation: pulse 2s infinite;">
                                    <i class="fa-solid fa-circle" style="font-size:0.5rem;"></i> Treballant
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> No hi ha treballadors actius.</p>
    <?php endif; ?>

    <!-- Fitxatges en curs -->
    <?php if ($fitxatges_oberts && $fitxatges_oberts->num_rows > 0): ?>
        <div class="form-section" style="margin-top:24px; border-left:4px solid var(--primary);">
            <h4 style="margin-top:0;"><i class="fa-solid fa-person-running" style="color:var(--primary); margin-right:8px;"></i> Fitxatges en curs — Registrar sortida</h4>
            <div class="table-container" style="margin-bottom:0;">
                <table>
                    <tr>
                        <th>Treballador</th>
                        <th>Tasca</th>
                        <th>Entrada</th>
                        <th>Temps transcorregut</th>
                        <th>Acció</th>
                    </tr>
                    <?php while ($fo = $fitxatges_oberts->fetch_assoc()):
                        $inici = strtotime($fo['hora_inici']);
                        $diff = time() - $inici;
                        $diff_h = floor($diff / 3600);
                        $diff_m = floor(($diff % 3600) / 60);
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($fo['treb_nom'] . ' ' . $fo['treb_cognoms']) ?></strong></td>
                            <td>
                                <?php if ($fo['tasca_tipus']): ?>
                                    <span class="badge badge-info">#<?= $fo['id_tasca'] ?> <?= htmlspecialchars($fo['tasca_tipus']) ?></span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);">Sense tasca</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('H:i', $inici) ?></td>
                            <td>
                                <span class="badge badge-warning" style="font-size:0.85rem;">
                                    <i class="fa-solid fa-stopwatch"></i> <?= $diff_h ?>h <?= $diff_m ?>min
                                </span>
                            </td>
                            <td>
                                <a href="?p=tasques_hores&fitxar_sortida=<?= $fo['id_hora'] ?>"
                                   class="btn btn-red" style="font-size:0.85rem; padding:8px 16px;"
                                   onclick="return confirm('Confirmes la sortida de <?= htmlspecialchars($fo['treb_nom']) ?>?');">
                                    <i class="fa-solid fa-right-from-bracket"></i> Fitxar sortida
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <!-- ══════════════════════════════════════════ -->
    <!--  2. FITXAR ENTRADA (Registrar inici)      -->
    <!-- ══════════════════════════════════════════ -->
    <div class="form-section">
        <h3><i class="fa-solid fa-right-to-bracket" style="color:var(--primary); margin-right:8px;"></i> Fitxar entrada (iniciar jornada/tasca)</h3>
        <form method="post">
            <input type="hidden" name="p" value="tasques_hores">
            <input type="hidden" name="fitxar_inici" value="1">
            <div class="form-grid">
                <div class="form-group">
                    <label>Treballador: <span style="color:var(--danger);">*</span></label>
                    <select name="treballador_id" required>
                        <option value="">Selecciona treballador</option>
                        <?php
                        $trebs = $conn->query("SELECT id_treballador, nom, cognoms FROM Treballador WHERE actiu = 1 ORDER BY nom");
                        while ($t = $trebs->fetch_assoc()) {
                            echo '<option value="' . $t['id_treballador'] . '">' . htmlspecialchars($t['nom'] . ' ' . $t['cognoms']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tasca (opcional):</label>
                    <select name="tasca_id">
                        <option value="">Sense tasca específica</option>
                        <?php
                        $tasques_sel = $conn->query("
                            SELECT tg.id_tasca, tg.tipus, tg.data_tasca, p.nom AS parcela_nom
                            FROM Tasques_Gestio tg
                            LEFT JOIN `Parcel·la` p ON tg.parcela_id = p.id_parcela
                            WHERE tg.estat IN ('Pendent', 'En curs')
                            ORDER BY tg.data_tasca ASC
                        ");
                        if ($tasques_sel) {
                            while ($ts = $tasques_sel->fetch_assoc()) {
                                $label = '#' . $ts['id_tasca'] . ' ' . htmlspecialchars($ts['tipus']);
                                if ($ts['parcela_nom']) $label .= ' (' . htmlspecialchars($ts['parcela_nom']) . ')';
                                $label .= ' - ' . date('d/m', strtotime($ts['data_tasca']));
                                echo '<option value="' . $ts['id_tasca'] . '">' . $label . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Observacions:</label>
                    <input type="text" name="observacions" placeholder="Notes opcionals...">
                </div>
            </div>
            <button type="submit" class="btn" style="background:var(--primary);">
                <i class="fa-solid fa-play"></i> Fitxar entrada ara (<?= date('H:i') ?>)
            </button>
        </form>
    </div>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <!-- ══════════════════════════════════════════ -->
    <!--  3. CREAR NOVA TASCA                      -->
    <!-- ══════════════════════════════════════════ -->
    <div class="form-section">
        <h3><i class="fa-solid fa-clipboard-list" style="color:var(--info); margin-right:8px;"></i> Crear nova tasca</h3>
        <form method="post">
            <input type="hidden" name="p" value="tasques_hores">
            <input type="hidden" name="nova_tasca" value="1">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tipus de tasca: <span style="color:var(--danger);">*</span></label>
                    <select name="tipus" required>
                        <option value="">Selecciona tipus</option>
                        <option value="Poda">Poda</option>
                        <option value="Reg">Reg</option>
                        <option value="Tractament">Tractament fitosanitari</option>
                        <option value="Collita">Collita</option>
                        <option value="Plantació">Plantació</option>
                        <option value="Fertilització">Fertilització</option>
                        <option value="Manteniment">Manteniment maquinària</option>
                        <option value="Neteja">Neteja de camp</option>
                        <option value="Transport">Transport</option>
                        <option value="Altres">Altres</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Parcel·la:</label>
                    <select name="parcela_id">
                        <option value="">Sense parcel·la</option>
                        <?php
                        $parceles = $conn->query("SELECT id_parcela, nom FROM `Parcel·la` ORDER BY nom");
                        if ($parceles) {
                            while ($pa = $parceles->fetch_assoc()) {
                                echo '<option value="' . $pa['id_parcela'] . '">' . htmlspecialchars($pa['nom']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Data: <span style="color:var(--danger);">*</span></label>
                    <input type="date" name="data_tasca" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Durada estimada (hores):</label>
                    <input type="number" name="durada_estimada" step="0.25" min="0" placeholder="ex: 4.5">
                </div>
                <div class="form-group">
                    <label>Assignar a treballador:</label>
                    <select name="treballador_id">
                        <option value="">Sense assignar</option>
                        <?php
                        $trebs = $conn->query("SELECT id_treballador, nom, cognoms FROM Treballador WHERE actiu = 1 ORDER BY nom");
                        while ($t = $trebs->fetch_assoc()) {
                            echo '<option value="' . $t['id_treballador'] . '">' . htmlspecialchars($t['nom'] . ' ' . $t['cognoms']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Prioritat:</label>
                    <select name="prioritat">
                        <option value="Baixa">Baixa</option>
                        <option value="Normal" selected>Normal</option>
                        <option value="Alta">Alta</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label>Descripció:</label>
                    <textarea name="descripcio" rows="2" placeholder="Detalls de la tasca..."></textarea>
                </div>
            </div>
            <button type="submit" class="btn" style="background:var(--info);">
                <i class="fa-solid fa-plus"></i> Crear tasca
            </button>
        </form>
    </div>

    <!-- ══════════════════════════════════════════ -->
    <!--  4. LLISTAT DE TASQUES                    -->
    <!-- ══════════════════════════════════════════ -->
    <h3><i class="fa-solid fa-list-check" style="margin-right:8px; color:var(--text-muted);"></i> Tasques registrades</h3>
    <?php
    $tasques = $conn->query("
        SELECT tg.*, 
               p.nom AS parcela_nom,
               t.nom AS treb_nom, t.cognoms AS treb_cognoms,
               COALESCE(SUM(
                   CASE WHEN h.hora_final IS NOT NULL
                   THEN TIMESTAMPDIFF(MINUTE, h.hora_inici, h.hora_final) / 60.0
                   ELSE 0 END
               ), 0) AS hores_reals
        FROM Tasques_Gestio tg
        LEFT JOIN `Parcel·la` p ON tg.parcela_id = p.id_parcela
        LEFT JOIN Treballador t ON tg.treballador_id = t.id_treballador
        LEFT JOIN Hores_Treball h ON tg.id_tasca = h.tasca_id
        GROUP BY tg.id_tasca
        ORDER BY 
            FIELD(tg.estat, 'En curs', 'Pendent', 'Completada', 'Cancel·lada'),
            FIELD(tg.prioritat, 'Urgent', 'Alta', 'Normal', 'Baixa'),
            tg.data_tasca DESC
    ");

    if ($tasques && $tasques->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Tipus</th>
                    <th>Parcel·la</th>
                    <th>Data</th>
                    <th>Assignat a</th>
                    <th>Prioritat</th>
                    <th>Estat</th>
                    <th>Durada est.</th>
                    <th>Hores reals</th>
                    <th>Accions</th>
                </tr>
                <?php while ($tq = $tasques->fetch_assoc()):
                    // Badges per prioritat
                    $prio_badges = [
                        'Baixa' => 'badge-secondary',
                        'Normal' => 'badge-info',
                        'Alta' => 'badge-warning',
                        'Urgent' => 'badge-danger',
                    ];
                    $prio_b = $prio_badges[$tq['prioritat']] ?? 'badge-secondary';

                    // Badges per estat
                    $estat_badges = [
                        'Pendent' => 'badge-warning',
                        'En curs' => 'badge-info',
                        'Completada' => 'badge-success',
                        'Cancel·lada' => 'badge-danger',
                    ];
                    $estat_b = $estat_badges[$tq['estat']] ?? 'badge-secondary';

                    // Icones per estat
                    $estat_icons = [
                        'Pendent' => 'fa-solid fa-hourglass-start',
                        'En curs' => 'fa-solid fa-spinner fa-spin',
                        'Completada' => 'fa-solid fa-circle-check',
                        'Cancel·lada' => 'fa-solid fa-ban',
                    ];
                    $estat_i = $estat_icons[$tq['estat']] ?? 'fa-solid fa-question';

                    $hores_r = (float)$tq['hores_reals'];
                    $hr_h = floor($hores_r);
                    $hr_m = round(($hores_r - $hr_h) * 60);
                ?>
                    <tr style="<?= $tq['estat'] === 'Cancel·lada' ? 'opacity:0.5;' : '' ?>">
                        <td><strong>#<?= $tq['id_tasca'] ?></strong></td>
                        <td><?= htmlspecialchars($tq['tipus']) ?></td>
                        <td><?= $tq['parcela_nom'] ? htmlspecialchars($tq['parcela_nom']) : '<span style="color:var(--text-muted);">-</span>' ?></td>
                        <td><?= date('d/m/Y', strtotime($tq['data_tasca'])) ?></td>
                        <td>
                            <?php if ($tq['treb_nom']): ?>
                                <i class="fa-solid fa-user" style="color:var(--text-muted); margin-right:4px;"></i>
                                <?= htmlspecialchars($tq['treb_nom'] . ' ' . $tq['treb_cognoms']) ?>
                            <?php else: ?>
                                <span style="color:var(--text-muted);"><i class="fa-solid fa-user-slash"></i> Sense assignar</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?= $prio_b ?>"><?= htmlspecialchars($tq['prioritat']) ?></span></td>
                        <td><span class="badge <?= $estat_b ?>"><i class="<?= $estat_i ?>" style="margin-right:4px;"></i><?= htmlspecialchars($tq['estat']) ?></span></td>
                        <td><?= $tq['durada_estimada'] ? number_format($tq['durada_estimada'], 1) . 'h' : '-' ?></td>
                        <td>
                            <?php if ($hores_r > 0): ?>
                                <span class="badge badge-success" style="font-size:0.85rem;"><?= $hr_h ?>h <?= $hr_m ?>m</span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);">0h</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <?php if ($tq['estat'] === 'Pendent'): ?>
                                <a href="?p=tasques_hores&canviar_estat=<?= $tq['id_tasca'] ?>&estat=En curs"
                                   class="btn btn-icon" style="background:var(--info); font-size:0.75rem; padding:5px 8px;" title="Iniciar">
                                    <i class="fa-solid fa-play"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($tq['estat'] === 'En curs'): ?>
                                <a href="?p=tasques_hores&canviar_estat=<?= $tq['id_tasca'] ?>&estat=Completada"
                                   class="btn btn-icon" style="background:var(--primary); font-size:0.75rem; padding:5px 8px;" title="Completar">
                                    <i class="fa-solid fa-check"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($tq['estat'] !== 'Cancel·lada' && $tq['estat'] !== 'Completada'): ?>
                                <a href="?p=tasques_hores&canviar_estat=<?= $tq['id_tasca'] ?>&estat=Cancel·lada"
                                   class="btn btn-icon" style="background:var(--warning); font-size:0.75rem; padding:5px 8px;" title="Cancel·lar"
                                   onclick="return confirm('Segur que vols cancel·lar aquesta tasca?');">
                                    <i class="fa-solid fa-ban"></i>
                                </a>
                            <?php endif; ?>
                            <a href="?p=tasques_hores&eliminar_tasca=1&id=<?= $tq['id_tasca'] ?>"
                               class="btn btn-red btn-icon" style="font-size:0.75rem; padding:5px 8px;"
                               onclick="return confirm('Eliminar tasca #<?= $tq['id_tasca'] ?> i tots els seus registres d\'hores?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> Encara no hi ha tasques. Crea'n una amb el formulari de dalt.</p>
    <?php endif; ?>

    <hr style="border: 0; height: 1px; background: var(--border-color); margin: 40px 0;">

    <!-- ══════════════════════════════════════════ -->
    <!--  5. HISTORIAL D'HORES                     -->
    <!-- ══════════════════════════════════════════ -->
    <h3><i class="fa-solid fa-timeline" style="margin-right:8px; color:var(--text-muted);"></i> Historial de registres d'hores</h3>
    <?php
    $hores_hist = $conn->query("
        SELECT h.*, 
               t.nom AS treb_nom, t.cognoms AS treb_cognoms,
               tg.tipus AS tasca_tipus, tg.id_tasca AS tasca_num
        FROM Hores_Treball h
        JOIN Treballador t ON h.treballador_id = t.id_treballador
        LEFT JOIN Tasques_Gestio tg ON h.tasca_id = tg.id_tasca
        ORDER BY h.hora_inici DESC
        LIMIT 50
    ");

    if ($hores_hist && $hores_hist->num_rows > 0):
    ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Treballador</th>
                    <th>Tasca</th>
                    <th>Entrada</th>
                    <th>Sortida</th>
                    <th>Durada</th>
                    <th>Observacions</th>
                    <th>Acció</th>
                </tr>
                <?php while ($hh = $hores_hist->fetch_assoc()):
                    $durada_txt = '-';
                    $durada_badge = 'badge-secondary';
                    if ($hh['hora_final']) {
                        $diff_s = strtotime($hh['hora_final']) - strtotime($hh['hora_inici']);
                        $dh = floor($diff_s / 3600);
                        $dm = floor(($diff_s % 3600) / 60);
                        $durada_txt = $dh . 'h ' . $dm . 'min';
                        $durada_badge = 'badge-success';
                    } else {
                        $diff_s = time() - strtotime($hh['hora_inici']);
                        $dh = floor($diff_s / 3600);
                        $dm = floor(($diff_s % 3600) / 60);
                        $durada_txt = $dh . 'h ' . $dm . 'min (en curs)';
                        $durada_badge = 'badge-warning';
                    }
                ?>
                    <tr>
                        <td><strong>#<?= $hh['id_hora'] ?></strong></td>
                        <td><?= htmlspecialchars($hh['treb_nom'] . ' ' . $hh['treb_cognoms']) ?></td>
                        <td>
                            <?php if ($hh['tasca_tipus']): ?>
                                <span class="badge badge-info" style="font-size:0.75rem;">#<?= $hh['tasca_num'] ?> <?= htmlspecialchars($hh['tasca_tipus']) ?></span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($hh['hora_inici'])) ?></td>
                        <td>
                            <?php if ($hh['hora_final']): ?>
                                <?= date('d/m/Y H:i', strtotime($hh['hora_final'])) ?>
                            <?php else: ?>
                                <span class="badge badge-warning" style="font-size:0.75rem;"><i class="fa-solid fa-spinner fa-spin"></i> En curs</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?= $durada_badge ?>" style="font-size:0.85rem;"><?= $durada_txt ?></span></td>
                        <td><small><?= htmlspecialchars($hh['observacions'] ?: '-') ?></small></td>
                        <td>
                            <?php if (!$hh['hora_final']): ?>
                                <a href="?p=tasques_hores&fitxar_sortida=<?= $hh['id_hora'] ?>"
                                   class="btn btn-icon" style="background:var(--primary); font-size:0.75rem; padding:5px 8px;" title="Tancar">
                                    <i class="fa-solid fa-stop"></i>
                                </a>
                            <?php endif; ?>
                            <a href="?p=tasques_hores&eliminar_hora=1&id=<?= $hh['id_hora'] ?>"
                               class="btn btn-red btn-icon" style="font-size:0.75rem; padding:5px 8px;"
                               onclick="return confirm('Eliminar registre d\'hores #<?= $hh['id_hora'] ?>?');" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fa-solid fa-folder-open"></i> No hi ha registres d'hores encara.</p>
    <?php endif; ?>
</div>

<style>
/* Pulsació per als treballadors actius */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>
