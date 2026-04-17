<?php
require_once 'config/db.php';

// Pestanya activa
$p = $_GET['p'] ?? 'home';


// ────────────────────────────────────────────────
// ELIMINACIONS
if (isset($_GET['eliminar'])) {
    $tipus = $_GET['eliminar'] ?? '';
    $id    = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        switch ($tipus) {
            case 'parcela':     $conn->query("DELETE FROM Parcel·la WHERE id_parcela = $id"); $_SESSION['msg'] = "Parcel·la eliminada!"; break;
            case 'cultiu':      $conn->query("DELETE FROM Cultiu WHERE id_cultiu = $id"); $_SESSION['msg'] = "Cultiu eliminat!"; break;
            case 'treballador': $conn->query("DELETE FROM Treballador WHERE id_treballador = $id"); $_SESSION['msg'] = "Treballador eliminat!"; break;
            case 'absencia':    $conn->query("DELETE FROM Absencia WHERE id_absencia = $id"); $_SESSION['msg'] = "Absència eliminada!"; break;
            case 'producte':    $conn->query("DELETE FROM Producte WHERE id_producte = $id"); $_SESSION['msg'] = "Producte eliminat!"; break;
            case 'estoc':       $conn->query("DELETE FROM Estoc WHERE id_estoc = $id"); $_SESSION['msg'] = "Estoc eliminat!"; break;
            case 'monitoratge': $conn->query("DELETE FROM Monitoratge_Plagues WHERE id_monitoratge = $id"); $_SESSION['msg'] = "Observació eliminada!"; break;
            case 'aplicacio':   $conn->query("DELETE FROM Aplicacio_Tractament WHERE id_aplicacio = $id"); $_SESSION['msg'] = "Aplicació eliminada!"; break;
            case 'analisi':     $conn->query("DELETE FROM Analisi_Nutricional WHERE id_analisi = $id"); $_SESSION['msg'] = "Anàlisi eliminada!"; break;
            case 'calendari':   $conn->query("DELETE FROM Calendari_Fitosanitari WHERE id_calendari = $id"); $_SESSION['msg'] = "Entrada eliminada!"; break;
            case 'fitxatge':    $conn->query("DELETE FROM Fitxatge WHERE id_fitxatge = $id"); $_SESSION['msg'] = "Fitxatge eliminat!"; break;
            case 'sensor':      $conn->query("DELETE FROM Sensor WHERE id_sensor = $id"); $_SESSION['msg'] = "Sensor eliminat!"; break;
            case 'alerta':      $conn->query("DELETE FROM Alerta WHERE id_alerta = $id"); $_SESSION['msg'] = "Alerta eliminada!"; break;
            case 'collita':     $conn->query("DELETE FROM Collita WHERE id_collita = $id"); $_SESSION['msg'] = "Collita eliminada!"; break;
            case 'lot':         $conn->query("DELETE FROM Lot WHERE id_lot = $id"); $_SESSION['msg'] = "Lot eliminat!"; break;
        }
    }

    header("Location: index.php?p=$p");
    exit;
}

// Missatges
$msg = $_SESSION['msg'] ?? '';
$err = $_SESSION['err'] ?? '';
unset($_SESSION['msg'], $_SESSION['err']);

// ────────────────────────────────────────────────
// HEADER (sidebar, head, css)
include 'includes/header.php';
?>

        <?php if ($p === 'home'): ?>
            <div class="top-bar">
                <h2 class="page-title">Panell de Control General</h2>
            </div>
            
            <?php
            // Extreure dades en temps real pel Dashboard
            $q_pc = $conn->query("SELECT COUNT(*) as t FROM Parcel·la"); $pc = $q_pc ? $q_pc->fetch_assoc()['t'] : 0;
            $q_tr = $conn->query("SELECT COUNT(*) as t FROM Treballador"); $tr = $q_tr ? $q_tr->fetch_assoc()['t'] : 0;
            $q_al = $conn->query("SELECT COUNT(*) as t FROM Alerta WHERE estat = 'Pendent'"); $al = $q_al ? $q_al->fetch_assoc()['t'] : 0;
            $q_es = $conn->query("SELECT SUM(quantitat_disponible) as t FROM Estoc"); $es = $q_es ? round($q_es->fetch_assoc()['t']) : 0;
            ?>

            <div class="dashboard-grid">
                <div class="kpi-card">
                    <div class="kpi-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                    <div class="kpi-content">
                        <h3>Total Parcel·les</h3>
                        <p class="kpi-value"><?= $pc ?></p>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon" style="color:var(--info); background:rgba(59,130,246,0.1);"><i class="fa-solid fa-users-gear"></i></div>
                    <div class="kpi-content">
                        <h3>Treballadors</h3>
                        <p class="kpi-value"><?= $tr ?></p>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon" style="color:var(--warning); background:rgba(245,158,11,0.1);"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <div class="kpi-content">
                        <h3>Volum Estoc (U.)</h3>
                        <p class="kpi-value"><?= $es ?></p>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon" style="color:var(--danger); background:rgba(239,68,68,0.1);"><i class="fa-solid fa-bell"></i></div>
                    <div class="kpi-content">
                        <h3>Alertes Pendents</h3>
                        <p class="kpi-value"><?= $al ?></p>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2><i class="fa-solid fa-house-chimney"></i> Benvingut a l'ecosistema High Elo</h2>
                <p style="color: var(--text-muted); line-height: 1.6; font-size: 1.05rem;">
                    L'administració de dades i el flux de treball de la finca està completament sincronitzada al teu panell esquerre.<br>
                    Navega a través dels diferents sectors per obtenir visualitzacions en graella de les parcel·les, aplicar productes en estoc, vigilar alarmes ambientals en temps real o monitoritzar el fitxatge dinàmic dels treballadors.
                </p>
                <div style="margin-top: 35px;">
                    <a href="?p=productes" class="btn"><i class="fa-solid fa-cart-flatbed"></i> Gestió d'Estoc</a>
                    <a href="?p=sensors" class="btn" style="background: white; border: 1px solid var(--border-color); color: var(--text-main); margin-left: 10px; box-shadow: none;"><i class="fa-solid fa-tower-broadcast"></i> Tauler de Sensors</a>
                </div>
            </div>
            
        <?php else: ?>
            <div class="top-bar">
                <h2 class="page-title">
                    <?php
                    $titols = [
                        'parceles' => '<i class="fa-solid fa-map" style="margin-right:10px; color:var(--primary);"></i> Gestió de Parcel·les',
                        'cultius' => '<i class="fa-solid fa-seedling" style="margin-right:10px; color:var(--primary);"></i> Cultius i Sectors',
                        'personal' => '<i class="fa-solid fa-users" style="margin-right:10px; color:var(--primary);"></i> Recursos Humans',
                        'productes' => '<i class="fa-solid fa-box-open" style="margin-right:10px; color:var(--primary);"></i> Productes Fitosanitaris i Estoc',
                        'monitoratge_plagues' => '<i class="fa-solid fa-bug" style="margin-right:10px; color:var(--primary);"></i> Tractaments i Plagues',
                        'sensors' => '<i class="fa-solid fa-tower-broadcast" style="margin-right:10px; color:var(--primary);"></i> Sensors i Alertes Intel·ligents',
                    'collites' => '<i class="fa-solid fa-wheat-awn" style="margin-right:10px; color:var(--primary);"></i> Registre de Collites',
                    'lots' => '<i class="fa-solid fa-barcode" style="margin-right:10px; color:var(--primary);"></i> Traçabilitat de Lots',
                    'dashboard' => '<i class="fa-solid fa-chart-pie" style="margin-right:10px; color:var(--primary);"></i> Anàlisi de Producció'
                    ];
                    echo $titols[$p] ?? 'Secció Desconeguda';
                    ?>
                </h2>
            </div>

            <?php
            // Incloem el fitxer que pertoca a cada secció
            switch ($p) {
                case 'parceles': include 'modules/parcela.php'; break;
                case 'cultius': include 'modules/cultius.php'; break;
                case 'personal': include 'modules/personal.php'; break;
                case 'productes': include 'modules/productes.php'; break;
                case 'monitoratge_plagues': include 'modules/monitoratge_plagues.php'; break;
                case 'sensors': include 'modules/sensors.php'; break;
                case 'collites': include 'modules/collites.php'; break;
                case 'lots': include 'modules/lots.php'; break;
                case 'dashboard': include 'modules/dashboard.php'; break;
                default: 
                    echo '<div class="section">
                            <h2><i class="fa-solid fa-circle-exclamation"></i> Pàgina no trobada</h2>
                            <p>La secció sol·licitada no existeix dins la llista del sistema.</p>
                          </div>'; 
                    break;
            }
            ?>
        <?php endif; ?>

<?php
// ────────────────────────────────────────────────
// FOOTER (scripts, tancament)
include 'includes/footer.php';
?>
