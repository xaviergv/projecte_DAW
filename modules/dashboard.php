<?php
// Obtenir filtres si existeixen
$filtre_any = isset($_GET['any']) && $_GET['any'] !== '' ? (int)$_GET['any'] : null;
$filtre_parcela = isset($_GET['id_parcela']) && $_GET['id_parcela'] !== '' ? (int)$_GET['id_parcela'] : null;

// Llistat per als filtres
$anys_disponibles = [];
$res_anys = $conn->query("SELECT DISTINCT YEAR(data_inici) as any_collita FROM Collita ORDER BY any_collita DESC");
while ($row = $res_anys->fetch_assoc()) {
    if ($row['any_collita']) $anys_disponibles[] = $row['any_collita'];
}

$parceles_disponibles = [];
$res_parc = $conn->query("SELECT id_parcela, nom FROM Parcel·la ORDER BY nom");
while ($row = $res_parc->fetch_assoc()) {
    $parceles_disponibles[] = $row;
}

// ────────────────────────────────────────────────
// GRÀFICA 1: Producció per Parcel·la
// ────────────────────────────────────────────────
$condicio_any = $filtre_any ? " AND YEAR(c.data_inici) = $filtre_any" : "";
$condicio_parcela_1 = $filtre_parcela ? " AND c.id_parcela = $filtre_parcela" : ""; // Opcional aplicar-ho o no, aplicar-ho ho filtrarà a 1 parcel·la

$sql_parceles = "
    SELECT p.nom as nom_parcela, SUM(c.quantitat) as total_kg 
    FROM Collita c
    JOIN Parcel·la p ON c.id_parcela = p.id_parcela
    WHERE 1=1 $condicio_any $condicio_parcela_1
    GROUP BY p.id_parcela
    ORDER BY total_kg DESC
";
$res_p = $conn->query($sql_parceles);
$data_parceles_labels = [];
$data_parceles_values = [];
if ($res_p) {
    while ($row = $res_p->fetch_assoc()) {
        $data_parceles_labels[] = $row['nom_parcela'];
        $data_parceles_values[] = (float)$row['total_kg'];
    }
}

// ────────────────────────────────────────────────
// GRÀFICA 2: Producció per Any
// ────────────────────────────────────────────────
$condicio_parcela = $filtre_parcela ? " AND c.id_parcela = $filtre_parcela" : "";
$condicio_any_2 = $filtre_any ? " AND YEAR(c.data_inici) = $filtre_any" : ""; // Opcional, si filtrem per any, la gràfica d'anys només en mostrarà 1.

$sql_anys = "
    SELECT YEAR(c.data_inici) as any_collita, SUM(c.quantitat) as total_kg 
    FROM Collita c
    WHERE data_inici IS NOT NULL $condicio_parcela $condicio_any_2
    GROUP BY YEAR(c.data_inici)
    ORDER BY any_collita ASC
";
$res_a = $conn->query($sql_anys);
$data_anys_labels = [];
$data_anys_values = [];
if ($res_a) {
    while ($row = $res_a->fetch_assoc()) {
        $data_anys_labels[] = $row['any_collita'];
        $data_anys_values[] = (float)$row['total_kg'];
    }
}

// ────────────────────────────────────────────────
// DADES KPI
// ────────────────────────────────────────────────
$kpi_where = "WHERE 1=1";
if ($filtre_any) $kpi_where .= " AND YEAR(data_inici) = $filtre_any";
if ($filtre_parcela) $kpi_where .= " AND id_parcela = $filtre_parcela";

$res_kpi = $conn->query("SELECT SUM(quantitat) as t_kg, COUNT(id_collita) as t_collites, MAX(data_inici) as ultima_collita FROM Collita $kpi_where");
$row_kpi = $res_kpi ? $res_kpi->fetch_assoc() : null;
$total_kg = $row_kpi['t_kg'] ?? 0;
$total_collites = $row_kpi['t_collites'] ?? 0;
$ultima_collita = $row_kpi['ultima_collita'] ? date('d/m/Y', strtotime($row_kpi['ultima_collita'])) : '-';

// Dades personal actiu (global)
$res_pers = $conn->query("SELECT COUNT(*) as t FROM Treballador WHERE actiu=1");
$total_treb = $res_pers ? $res_pers->fetch_assoc()['t'] : 0;

?>

<div class="section">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:15px;">
        <h3 style="margin:0;"><i class="fa-solid fa-chart-line" style="color:var(--primary); margin-right:8px;"></i> Dashboard d'Anàlisi de Producció</h3>
    </div>

    <!-- Formulari de Filtres -->
    <div class="form-section" style="margin-bottom: 30px;">
        <h4 style="margin-top:0;"><i class="fa-solid fa-filter" style="color:var(--text-muted); margin-right:8px;"></i> Filtres de cerca</h4>
        <form method="get" action="index.php">
            <input type="hidden" name="p" value="dashboard">
            <div class="form-grid" style="align-items: end;">
                <div class="form-group">
                    <label>Filtrar per Any:</label>
                    <select name="any">
                        <option value="">Tots els anys</option>
                        <?php foreach ($anys_disponibles as $any): ?>
                            <option value="<?= $any ?>" <?= $filtre_any === (int)$any ? 'selected' : '' ?>><?= $any ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Filtrar per Parcel·la:</label>
                    <select name="id_parcela">
                        <option value="">Totes les parcel·les</option>
                        <?php foreach ($parceles_disponibles as $p_item): ?>
                            <option value="<?= $p_item['id_parcela'] ?>" <?= $filtre_parcela === (int)$p_item['id_parcela'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p_item['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="display:flex; gap:10px;">
                    <button type="submit" class="btn"><i class="fa-solid fa-magnifying-glass"></i> Aplicar Filtres</button>
                    <a href="?p=dashboard" class="btn btn-red"><i class="fa-solid fa-xmark"></i> Netejar</a>
                </div>
            </div>
        </form>
    </div>

    <!-- KPIs -->
    <div class="dashboard-grid" style="margin-bottom:30px;">
        <div class="kpi-card" style="border-bottom: 4px solid var(--success);">
            <div class="kpi-icon" style="color:var(--success); background:rgba(16,185,129,0.1);"><i class="fa-solid fa-weight-hanging"></i></div>
            <div class="kpi-content">
                <h3>Producció Total</h3>
                <p class="kpi-value"><?= number_format($total_kg, 2, ',', '.') ?> <span style="font-size:1rem; color:var(--text-muted);">kg</span></p>
                <small style="color:var(--text-muted);">Segons filtres aplicats</small>
            </div>
        </div>
        <div class="kpi-card" style="border-bottom: 4px solid var(--primary);">
            <div class="kpi-icon" style="color:var(--primary); background:rgba(14,165,233,0.1);"><i class="fa-solid fa-truck-ramp-box"></i></div>
            <div class="kpi-content">
                <h3>Registres de Collita</h3>
                <p class="kpi-value"><?= $total_collites ?></p>
                <small style="color:var(--text-muted);">Última: <?= $ultima_collita ?></small>
            </div>
        </div>
        <div class="kpi-card" style="border-bottom: 4px solid var(--warning);">
            <div class="kpi-icon" style="color:var(--warning); background:rgba(245,158,11,0.1);"><i class="fa-solid fa-users"></i></div>
            <div class="kpi-content">
                <h3>Força de Treball</h3>
                <p class="kpi-value"><?= $total_treb ?></p>
                <small style="color:var(--text-muted);">Usuaris actius</small>
            </div>
        </div>
    </div>

    <!-- Contenidor de les gràfiques -->
    <div style="display:flex; gap:30px; flex-wrap:wrap;">
        
        <!-- Gràfica Parcel·les -->
        <div class="form-section" style="flex:1; min-width:300px; text-align:center;">
            <h4>Producció Total per Parcel·la (kg)</h4>
            <?php if (count($data_parceles_labels) > 0): ?>
                <canvas id="chartParceles" style="max-height: 350px;"></canvas>
            <?php else: ?>
                <p style="color:var(--text-muted); padding:30px 0;">No hi ha dades per mostrar amb aquests filtres.</p>
            <?php endif; ?>
        </div>

        <!-- Gràfica Anys -->
        <div class="form-section" style="flex:1; min-width:300px; text-align:center;">
            <h4>Producció per Any (kg)</h4>
            <?php if (count($data_anys_labels) > 0): ?>
                <canvas id="chartAnys" style="max-height: 350px;"></canvas>
            <?php else: ?>
                <p style="color:var(--text-muted); padding:30px 0;">No hi ha dades per mostrar amb aquests filtres.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Càrrega de la llibreria Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // Configuració de colors per donar un aspecte pro
    const colors = [
        'rgba(16, 185, 129, 0.7)', // emerald
        'rgba(59, 130, 246, 0.7)', // blue
        'rgba(245, 158, 11, 0.7)', // amber
        'rgba(139, 92, 246, 0.7)', // violet
        'rgba(239, 68, 68, 0.7)',  // red
        'rgba(6, 182, 212, 0.7)',  // cyan
        'rgba(236, 72, 153, 0.7)'  // pink
    ];
    const borderColors = colors.map(c => c.replace('0.7', '1'));

    // --- GRÀFICA 1: Parcel·les ---
    <?php if (count($data_parceles_labels) > 0): ?>
    const ctxParceles = document.getElementById('chartParceles').getContext('2d');
    new Chart(ctxParceles, {
        type: 'pie', // El Doughnut o Pie queda bé per mostrar proporcions de producció per parcel·la
        data: {
            labels: <?= json_encode($data_parceles_labels) ?>,
            datasets: [{
                label: ' Producció (kg)',
                data: <?= json_encode($data_parceles_values) ?>,
                backgroundColor: colors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
    <?php endif; ?>

    // --- GRÀFICA 2: Anys ---
    <?php if (count($data_anys_labels) > 0): ?>
    const ctxAnys = document.getElementById('chartAnys').getContext('2d');
    new Chart(ctxAnys, {
        type: 'bar', // Útil per veure l'evolució per anys
        data: {
            labels: <?= json_encode($data_anys_labels) ?>,
            datasets: [{
                label: ' Producció Total (kg)',
                data: <?= json_encode($data_anys_values) ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
    <?php endif; ?>

});
</script>
