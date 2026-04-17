<?php
// ════════════════════════════════════════════════════════════
//  SISTEMA D'ALERTES INTEL·LIGENT — High Elo
//  Funcions PHP que comproven condicions i generen alertes
// ════════════════════════════════════════════════════════════

/**
 * Comprova productes amb estoc per sota del mínim configurat.
 * Retorna array d'alertes amb nivell 'critic' (stock = 0) o 'warning' (stock < mínim).
 */
function comprovarEstocBaix(mysqli $conn): array {
    $alertes = [];
    $sql = "
        SELECT p.id_producte, p.nom_comercial, p.quantitat_minima,
               COALESCE(SUM(e.quantitat_disponible), 0) AS estoc_total
        FROM Producte p
        LEFT JOIN Estoc e ON p.id_producte = e.id_producte
        GROUP BY p.id_producte
        HAVING estoc_total < p.quantitat_minima
        ORDER BY estoc_total ASC
    ";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $nivell = ($row['estoc_total'] <= 0) ? 'critic' : 'alt';
            $alertes[] = [
                'tipus'   => 'estoc',
                'nivell'  => $nivell,
                'icona'   => 'fa-solid fa-boxes-stacked',
                'titol'   => 'Estoc baix: ' . htmlspecialchars($row['nom_comercial']),
                'missatge'=> sprintf(
                    "El producte <strong>%s</strong> té <strong>%s</strong> unitats disponibles (mínim recomanat: %s).",
                    htmlspecialchars($row['nom_comercial']),
                    number_format($row['estoc_total'], 2),
                    number_format($row['quantitat_minima'], 2)
                ),
                'accio'   => '?p=productes',
                'accio_text' => 'Gestionar estoc'
            ];
        }
    }
    return $alertes;
}

/**
 * Comprova productes amb data de caducitat dins dels pròxims 30 dies o ja caducats.
 */
function comprovarCaducitats(mysqli $conn): array {
    $alertes = [];
    $avui = date('Y-m-d');
    $limit = date('Y-m-d', strtotime('+30 days'));

    $sql = "
        SELECT e.id_estoc, p.nom_comercial, e.data_caducitat, e.quantitat_disponible, e.unitat_mesura,
               DATEDIFF(e.data_caducitat, CURDATE()) AS dies_restants
        FROM Estoc e
        JOIN Producte p ON e.id_producte = p.id_producte
        WHERE e.data_caducitat IS NOT NULL
          AND e.data_caducitat <= ?
          AND e.quantitat_disponible > 0
        ORDER BY e.data_caducitat ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $dies = (int)$row['dies_restants'];
        if ($dies < 0) {
            $nivell = 'critic';
            $text_dies = 'CADUCAT fa ' . abs($dies) . ' dies';
        } elseif ($dies === 0) {
            $nivell = 'critic';
            $text_dies = 'CADUCA AVUI';
        } elseif ($dies <= 7) {
            $nivell = 'alt';
            $text_dies = "Caduca en $dies dies";
        } else {
            $nivell = 'mitja';
            $text_dies = "Caduca en $dies dies";
        }

        $alertes[] = [
            'tipus'   => 'caducitat',
            'nivell'  => $nivell,
            'icona'   => 'fa-solid fa-calendar-xmark',
            'titol'   => 'Caducitat: ' . htmlspecialchars($row['nom_comercial']),
            'missatge'=> sprintf(
                "<strong>%s</strong> — Lot amb %s %s. <strong>%s</strong> (data: %s).",
                htmlspecialchars($row['nom_comercial']),
                number_format($row['quantitat_disponible'], 2),
                htmlspecialchars($row['unitat_mesura']),
                $text_dies,
                date('d/m/Y', strtotime($row['data_caducitat']))
            ),
            'accio'   => '?p=productes',
            'accio_text' => 'Veure estoc'
        ];
    }
    $stmt->close();
    return $alertes;
}

/**
 * Comprova tractaments pendents (del calendari fitosanitari) que ja han passat
 * o estan programats per als pròxims 7 dies i encara no s'han aplicat.
 */
function comprovarTractamentsPendents(mysqli $conn): array {
    $alertes = [];
    $limit = date('Y-m-d', strtotime('+7 days'));

    $sql = "
        SELECT c.id_calendari, parc.nom AS parcela_nom, c.data_planificada,
               c.plaga_malaltia, c.estat_fenologic, pr.nom_comercial,
               DATEDIFF(c.data_planificada, CURDATE()) AS dies_restants
        FROM Calendari_Fitosanitari c
        JOIN Sector_Cultiu s ON c.id_sector = s.id_sector
        JOIN `Parcel·la` parc ON s.id_parcela = parc.id_parcela
        LEFT JOIN Producte pr ON c.id_producte_recomanat = pr.id_producte
        WHERE c.data_planificada <= ?
        ORDER BY c.data_planificada ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $dies = (int)$row['dies_restants'];
        if ($dies < 0) {
            $nivell = 'critic';
            $text = 'ENDARRERIT ' . abs($dies) . ' dies!';
        } elseif ($dies === 0) {
            $nivell = 'alt';
            $text = "Programat per AVUI";
        } else {
            $nivell = 'mitja';
            $text = "Programat en $dies dies";
        }

        $producte_txt = $row['nom_comercial'] ? htmlspecialchars($row['nom_comercial']) : '<em>sense producte assignat</em>';

        $alertes[] = [
            'tipus'   => 'tractament',
            'nivell'  => $nivell,
            'icona'   => 'fa-solid fa-spray-can-sparkles',
            'titol'   => 'Tractament pendent: ' . htmlspecialchars($row['parcela_nom']),
            'missatge'=> sprintf(
                "Parcel·la <strong>%s</strong> — %s. Plaga/Malaltia: <strong>%s</strong>. Producte: %s. <strong>%s</strong>.",
                htmlspecialchars($row['parcela_nom']),
                htmlspecialchars($row['estat_fenologic'] ?? ''),
                htmlspecialchars($row['plaga_malaltia'] ?? 'No especificada'),
                $producte_txt,
                $text
            ),
            'accio'   => '?p=monitoratge_plagues',
            'accio_text' => 'Veure tractaments'
        ];
    }
    $stmt->close();
    return $alertes;
}

/**
 * Retorna el nombre d'alertes de la taula Alerta amb estat 'Pendent' (alertes del sistema de sensors).
 */
function comprovarAlertesSensors(mysqli $conn): array {
    $alertes = [];
    $result = $conn->query("
        SELECT a.id_alerta, a.tipus_alerta, a.missatge, a.nivell_urgencia, a.data_generada
        FROM Alerta a
        WHERE a.estat = 'Pendent'
        ORDER BY a.data_generada DESC
        LIMIT 10
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $nivell_map = ['Crític' => 'critic', 'Alt' => 'alt', 'Mitjà' => 'mitja', 'Baix' => 'baix'];
            $nivell = $nivell_map[$row['nivell_urgencia']] ?? 'mitja';

            $alertes[] = [
                'tipus'   => 'sensor',
                'nivell'  => $nivell,
                'icona'   => 'fa-solid fa-tower-broadcast',
                'titol'   => 'Alerta sensor: ' . htmlspecialchars($row['tipus_alerta']),
                'missatge'=> htmlspecialchars($row['missatge']) . ' <small>(' . date('d/m/Y H:i', strtotime($row['data_generada'])) . ')</small>',
                'accio'   => '?p=sensors',
                'accio_text' => 'Veure sensors'
            ];
        }
    }
    return $alertes;
}

/**
 * Funció principal que agrupa totes les comprovacions.
 */
function obtenirTotesAlertes(mysqli $conn): array {
    $totes = array_merge(
        comprovarEstocBaix($conn),
        comprovarCaducitats($conn),
        comprovarTractamentsPendents($conn),
        comprovarAlertesSensors($conn)
    );

    // Ordenar per nivell de gravetat
    $ordre = ['critic' => 0, 'alt' => 1, 'mitja' => 2, 'baix' => 3];
    usort($totes, function($a, $b) use ($ordre) {
        return ($ordre[$a['nivell']] ?? 9) - ($ordre[$b['nivell']] ?? 9);
    });

    return $totes;
}

/**
 * Retorna la configuració visual per cada nivell d'alerta.
 */
function obtenirConfigNivell(string $nivell): array {
    $config = [
        'critic' => [
            'label' => 'CRÍTIC',
            'color' => '#dc2626',
            'bg'    => 'linear-gradient(135deg, #fef2f2, #fee2e2)',
            'border'=> '#fca5a5',
            'icon_bg'=> '#fef2f2',
            'badge_bg' => '#dc2626',
            'badge_color' => '#fff',
            'glow'  => 'rgba(220, 38, 38, 0.15)',
        ],
        'alt'    => [
            'label' => 'ALT',
            'color' => '#ea580c',
            'bg'    => 'linear-gradient(135deg, #fff7ed, #ffedd5)',
            'border'=> '#fdba74',
            'icon_bg'=> '#fff7ed',
            'badge_bg' => '#ea580c',
            'badge_color' => '#fff',
            'glow'  => 'rgba(234, 88, 12, 0.12)',
        ],
        'mitja'  => [
            'label' => 'MITJÀ',
            'color' => '#d97706',
            'bg'    => 'linear-gradient(135deg, #fffbeb, #fef3c7)',
            'border'=> '#fcd34d',
            'icon_bg'=> '#fffbeb',
            'badge_bg' => '#d97706',
            'badge_color' => '#fff',
            'glow'  => 'rgba(217, 119, 6, 0.1)',
        ],
        'baix'   => [
            'label' => 'BAIX',
            'color' => '#0284c7',
            'bg'    => 'linear-gradient(135deg, #f0f9ff, #e0f2fe)',
            'border'=> '#7dd3fc',
            'icon_bg'=> '#f0f9ff',
            'badge_bg' => '#0284c7',
            'badge_color' => '#fff',
            'glow'  => 'rgba(2, 132, 199, 0.1)',
        ],
    ];
    return $config[$nivell] ?? $config['mitja'];
}

// ════════════════════════════════════════════════════════════
//  VISTA — Dashboard d'Alertes
// ════════════════════════════════════════════════════════════

$alertes = obtenirTotesAlertes($conn);

// Comptar per nivell
$comptador = ['critic' => 0, 'alt' => 0, 'mitja' => 0, 'baix' => 0];
foreach ($alertes as $a) {
    $comptador[$a['nivell']] = ($comptador[$a['nivell']] ?? 0) + 1;
}
$total = count($alertes);

// Filtrar per tipus si hi ha un filtre actiu
$filtre_tipus = $_GET['filtre'] ?? 'tots';
if ($filtre_tipus !== 'tots') {
    $alertes_filtrades = array_filter($alertes, fn($a) => $a['tipus'] === $filtre_tipus);
} else {
    $alertes_filtrades = $alertes;
}
?>

<div class="section">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:30px;">
        <h3 style="margin:0;">
            <i class="fa-solid fa-shield-halved" style="color:var(--primary); margin-right:8px;"></i>
            Sistema d'Alertes Intel·ligent
        </h3>
        <span style="color:var(--text-muted); font-size:0.9rem;">
            <i class="fa-solid fa-clock"></i> Última comprovació: <?= date('d/m/Y H:i:s') ?>
        </span>
    </div>

    <!-- KPI Cards de resum -->
    <div class="alertes-kpi-grid">
        <div class="alerta-kpi" style="--kpi-accent: #dc2626;">
            <div class="alerta-kpi-icon" style="background:rgba(220,38,38,0.1); color:#dc2626;">
                <i class="fa-solid fa-skull-crossbones"></i>
            </div>
            <div class="alerta-kpi-info">
                <span class="alerta-kpi-count"><?= $comptador['critic'] ?></span>
                <span class="alerta-kpi-label">Crítiques</span>
            </div>
        </div>
        <div class="alerta-kpi" style="--kpi-accent: #ea580c;">
            <div class="alerta-kpi-icon" style="background:rgba(234,88,12,0.1); color:#ea580c;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="alerta-kpi-info">
                <span class="alerta-kpi-count"><?= $comptador['alt'] ?></span>
                <span class="alerta-kpi-label">Altes</span>
            </div>
        </div>
        <div class="alerta-kpi" style="--kpi-accent: #d97706;">
            <div class="alerta-kpi-icon" style="background:rgba(217,119,6,0.1); color:#d97706;">
                <i class="fa-solid fa-exclamation"></i>
            </div>
            <div class="alerta-kpi-info">
                <span class="alerta-kpi-count"><?= $comptador['mitja'] ?></span>
                <span class="alerta-kpi-label">Mitjanes</span>
            </div>
        </div>
        <div class="alerta-kpi" style="--kpi-accent: #0284c7;">
            <div class="alerta-kpi-icon" style="background:rgba(2,132,199,0.1); color:#0284c7;">
                <i class="fa-solid fa-info-circle"></i>
            </div>
            <div class="alerta-kpi-info">
                <span class="alerta-kpi-count"><?= $comptador['baix'] ?></span>
                <span class="alerta-kpi-label">Baixes</span>
            </div>
        </div>
    </div>

    <!-- Filtres ràpids -->
    <div class="alertes-filtres">
        <a href="?p=alertes&filtre=tots" class="filtre-btn <?= $filtre_tipus === 'tots' ? 'active' : '' ?>">
            <i class="fa-solid fa-layer-group"></i> Totes <span class="filtre-count"><?= $total ?></span>
        </a>
        <a href="?p=alertes&filtre=estoc" class="filtre-btn <?= $filtre_tipus === 'estoc' ? 'active' : '' ?>">
            <i class="fa-solid fa-boxes-stacked"></i> Estoc
        </a>
        <a href="?p=alertes&filtre=caducitat" class="filtre-btn <?= $filtre_tipus === 'caducitat' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-xmark"></i> Caducitat
        </a>
        <a href="?p=alertes&filtre=tractament" class="filtre-btn <?= $filtre_tipus === 'tractament' ? 'active' : '' ?>">
            <i class="fa-solid fa-spray-can-sparkles"></i> Tractaments
        </a>
        <a href="?p=alertes&filtre=sensor" class="filtre-btn <?= $filtre_tipus === 'sensor' ? 'active' : '' ?>">
            <i class="fa-solid fa-tower-broadcast"></i> Sensors
        </a>
    </div>

    <!-- Llistat d'alertes -->
    <?php if (count($alertes_filtrades) > 0): ?>
        <div class="alertes-llista">
            <?php foreach ($alertes_filtrades as $i => $alerta):
                $cfg = obtenirConfigNivell($alerta['nivell']);
            ?>
                <div class="alerta-card"
                     style="background:<?= $cfg['bg'] ?>; border-color:<?= $cfg['border'] ?>; --glow:<?= $cfg['glow'] ?>; animation-delay: <?= $i * 0.05 ?>s;">
                    
                    <div class="alerta-card-accent" style="background:<?= $cfg['color'] ?>;"></div>
                    
                    <div class="alerta-card-body">
                        <div class="alerta-card-header">
                            <div class="alerta-card-icon" style="color:<?= $cfg['color'] ?>; background:<?= $cfg['icon_bg'] ?>; border:2px solid <?= $cfg['border'] ?>;">
                                <i class="<?= $alerta['icona'] ?>"></i>
                            </div>
                            <div class="alerta-card-meta">
                                <span class="alerta-badge" style="background:<?= $cfg['badge_bg'] ?>; color:<?= $cfg['badge_color'] ?>;">
                                    <?= $cfg['label'] ?>
                                </span>
                                <h4 class="alerta-card-titol"><?= $alerta['titol'] ?></h4>
                            </div>
                        </div>
                        
                        <p class="alerta-card-missatge"><?= $alerta['missatge'] ?></p>
                        
                        <div class="alerta-card-footer">
                            <span class="alerta-tipus-tag">
                                <?php
                                $tipus_icons = [
                                    'estoc' => '<i class="fa-solid fa-box"></i> Inventari',
                                    'caducitat' => '<i class="fa-solid fa-clock-rotate-left"></i> Caducitat',
                                    'tractament' => '<i class="fa-solid fa-leaf"></i> Fitosanitari',
                                    'sensor' => '<i class="fa-solid fa-satellite-dish"></i> Sensor',
                                ];
                                echo $tipus_icons[$alerta['tipus']] ?? $alerta['tipus'];
                                ?>
                            </span>
                            <a href="<?= $alerta['accio'] ?>" class="alerta-accio-btn" style="color:<?= $cfg['color'] ?>; border-color:<?= $cfg['border'] ?>;">
                                <?= $alerta['accio_text'] ?> <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alertes-buit">
            <i class="fa-solid fa-circle-check"></i>
            <h4>Tot correcte!</h4>
            <p>No hi ha alertes actives en aquesta categoria. El sistema funciona correctament.</p>
        </div>
    <?php endif; ?>
</div>
