<?php
require_once 'config/db.php';

// Comprovar si l'usuari està autenticat
if (!isset($_SESSION['usuari_id'])) {
    header("Location: login.php");
    exit;
}

// Pestanya activa
$p = $_GET['p'] ?? 'home';


// ────────────────────────────────────────────────
// ELIMINACIONS I RESOLUCIONS
if (isset($_GET['resoldre_alerta'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE Alerta SET estat = 'Resolta' WHERE id_alerta = $id");
    $_SESSION['msg'] = "Alerta resolta i arxivada!";
    $target_p = urlencode($_GET['p'] ?? 'home');
    header("Location: index.php?p=$target_p");
    exit;
}

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
            case 'inventari':   $conn->query("DELETE FROM Inventari WHERE id_inventari = $id"); $_SESSION['msg'] = "Entrada d'inventari eliminada!"; break;
            case 'tasca_gestio': $conn->query("DELETE FROM Hores_Treball WHERE tasca_id = $id"); $conn->query("DELETE FROM Tasques_Gestio WHERE id_tasca = $id"); $_SESSION['msg'] = "Tasca eliminada!"; break;
            case 'hora_treball': $conn->query("DELETE FROM Hores_Treball WHERE id_hora = $id"); $_SESSION['msg'] = "Registre d'hores eliminat!"; break;
            case 'qualitat':     $conn->query("DELETE FROM Qualitat_Fruita WHERE id_qualitat = $id"); $_SESSION['msg'] = "Control de qualitat eliminat!"; break;
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
            $q_sup = $conn->query("SELECT SUM(superficie) as t FROM Parcel·la"); $sup = $q_sup ? round($q_sup->fetch_assoc()['t'], 2) : 0;
            $q_cu = $conn->query("SELECT COUNT(*) as t FROM Cultiu"); $cu = $q_cu ? $q_cu->fetch_assoc()['t'] : 0;
            
            $q_tr = $conn->query("SELECT COUNT(*) as t FROM Treballador WHERE actiu=1"); $tr = $q_tr ? $q_tr->fetch_assoc()['t'] : 0;
            $q_al = $conn->query("SELECT COUNT(*) as t FROM Alerta WHERE estat = 'Pendent'"); $al = $q_al ? $q_al->fetch_assoc()['t'] : 0;
            $q_es = $conn->query("SELECT SUM(quantitat) as t FROM Inventari"); $es = $q_es ? round($q_es->fetch_assoc()['t'], 2) : 0;
            
            $q_tq = $conn->query("SELECT COUNT(*) as t FROM Tasques_Gestio WHERE estat = 'Pendent'"); $tq = $q_tq ? $q_tq->fetch_assoc()['t'] : 0;
            ?>

            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
                <div class="kpi-card" style="border-bottom: 3px solid var(--primary);">
                    <div class="kpi-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                    <div class="kpi-content">
                        <h3>Total Parcel·les</h3>
                        <p class="kpi-value"><?= $pc ?></p>
                        <small style="color:var(--text-muted);"><?= $sup ?> ha Totals</small>
                    </div>
                </div>
                <div class="kpi-card" style="border-bottom: 3px solid var(--success);">
                    <div class="kpi-icon" style="color:var(--success); background:rgba(16,185,129,0.1);"><i class="fa-solid fa-seedling"></i></div>
                    <div class="kpi-content">
                        <h3>Cultius en actiu</h3>
                        <p class="kpi-value"><?= $cu ?></p>
                        <small style="color:var(--text-muted);">Tipologies gestionades</small>
                    </div>
                </div>
                <div class="kpi-card" style="border-bottom: 3px solid var(--info);">
                    <div class="kpi-icon" style="color:var(--info); background:rgba(59,130,246,0.1);"><i class="fa-solid fa-users-gear"></i></div>
                    <div class="kpi-content">
                        <h3>Treballadors</h3>
                        <p class="kpi-value"><?= $tr ?></p>
                        <small style="color:var(--text-muted);">En plantilla (actius)</small>
                    </div>
                </div>
                <div class="kpi-card" style="border-bottom: 3px solid #8b5cf6;">
                    <div class="kpi-icon" style="color:#8b5cf6; background:rgba(139,92,246,0.1);"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <div class="kpi-content">
                        <h3>Material en Estoc</h3>
                        <p class="kpi-value"><?= $es ?></p>
                        <small style="color:var(--text-muted);">Litres/Kg disponibles</small>
                    </div>
                </div>
                <div class="kpi-card" style="border-bottom: 3px solid #f97316;">
                    <div class="kpi-icon" style="color:#f97316; background:rgba(249,115,22,0.1);"><i class="fa-solid fa-list-check"></i></div>
                    <div class="kpi-content">
                        <h3>Tasques Pendents</h3>
                        <p class="kpi-value"><?= $tq ?></p>
                        <small style="color:var(--text-muted);">A l'espera de resolució</small>
                    </div>
                </div>
                <div class="kpi-card" style="border-bottom: 3px solid var(--danger);">
                    <div class="kpi-icon" style="color:var(--danger); background:rgba(239,68,68,0.1);"><i class="fa-solid fa-bell"></i></div>
                    <div class="kpi-content">
                        <h3>Alertes Intel·ligents</h3>
                        <p class="kpi-value"><?= $al ?></p>
                        <small style="color:var(--text-muted);">Requereixen atenció</small>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:30px; flex-wrap:wrap;">
                <div class="section" style="flex:2; min-width:400px; margin-bottom:0;">
                    <h2><i class="fa-solid fa-house-chimney"></i> Benvingut a l'ecosistema High Elo</h2>
                    <p style="color: var(--text-muted); line-height: 1.6; font-size: 1.05rem;">
                        L'administració de dades i el flux de treball de la finca està completament sincronitzada al teu panell esquerre.
                        Navega a través dels diferents sectors per obtenir visualitzacions cartogràfiques de les parcel·les, aplicar productes de l'inventari, vigilar alarmes ambientals en temps real o monitoritzar la productivitat.
                    </p>
                    <div style="margin-top: 30px; display:flex; gap:15px; flex-wrap:wrap;">
                        <a href="?p=inventari" class="btn"><i class="fa-solid fa-boxes-stacked"></i> Gestió de l'Inventari</a>
                        <a href="?p=sensors" class="btn" style="background: white; border: 1px solid var(--border-color); color: var(--text-main); box-shadow: none;"><i class="fa-solid fa-tower-broadcast"></i> Tauler de Sensors</a>
                        <a href="?p=dashboard" class="btn" style="background: white; border: 1px solid var(--border-color); color: var(--text-main); box-shadow: none;"><i class="fa-solid fa-chart-pie"></i> Anàlisi Global</a>
                    </div>
                </div>
                
                <div class="form-section" style="flex:1; min-width:300px; margin-bottom:0; background:#fff;">
                    <h3 style="margin-top:0;"><i class="fa-solid fa-bolt" style="color:#f59e0b; margin-right:8px;"></i> Accessos ràpids</h3>
                    <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:12px;">
                        <li><a href="?p=parceles" style="display:flex; justify-content:space-between; text-decoration:none; color:var(--text-main); padding:12px; border-radius:8px; background:#f8fafc; border:1px solid var(--border-color); font-weight:500; transition:all 0.2s;"><span><i class="fa-solid fa-map" style="width:24px; color:var(--primary);"></i> Cartografia Finca</span> <i class="fa-solid fa-arrow-right" style="color:var(--text-muted); font-size:0.8rem;"></i></a></li>
                        <li><a href="?p=tasques_hores" style="display:flex; justify-content:space-between; text-decoration:none; color:var(--text-main); padding:12px; border-radius:8px; background:#f8fafc; border:1px solid var(--border-color); font-weight:500; transition:all 0.2s;"><span><i class="fa-solid fa-clipboard-check" style="width:24px; color:var(--primary);"></i> Operativa i Hores</span> <i class="fa-solid fa-arrow-right" style="color:var(--text-muted); font-size:0.8rem;"></i></a></li>
                        <li><a href="?p=alertes" style="display:flex; justify-content:space-between; text-decoration:none; color:var(--text-main); padding:12px; border-radius:8px; background:#f8fafc; border:1px solid var(--border-color); font-weight:500; transition:all 0.2s;"><span><i class="fa-solid fa-shield-halved" style="width:24px; color:var(--primary);"></i> Tauler d'Alertes</span> <i class="fa-solid fa-arrow-right" style="color:var(--text-muted); font-size:0.8rem;"></i></a></li>
                    </ul>
                </div>
            </div>
            
            <?php
            // Obtenir últimes alertes (les més recents, independentment de l'estat per poder-les veure al dashboard)
            $alertes_q = $conn->query("
                SELECT id_alerta, tipus_alerta, nivell_urgencia, data_generada, estat 
                FROM Alerta 
                ORDER BY data_generada DESC
                LIMIT 5
            ");

            // Obtenir últimes tasques. Fem servir Tasques_Gestio, i si no hi ha dades, mirem la taula Tasca clàssica
            $tasques_q = $conn->query("
                SELECT id_tasca, tipus, prioritat, data_tasca, treballador_id, estat 
                FROM Tasques_Gestio 
                ORDER BY data_tasca DESC 
                LIMIT 5
            ");
            if (!$tasques_q || $tasques_q->num_rows === 0) {
                // Fallback a l'antiga taula Tasca si Tasques_Gestio està buida
                $tasques_q = $conn->query("
                    SELECT id_tasca, nom as tipus, 'Normal' as prioritat, IFNULL(data_inici_prevista, CURDATE()) as data_tasca, 0 as treballador_id, IF(finalitzada=1, 'Completada', 'Pendent') as estat 
                    FROM Tasca 
                    ORDER BY id_tasca DESC 
                    LIMIT 5
                ");
            }
            ?>

            <div style="display:flex; gap:30px; flex-wrap:wrap; margin-top:30px;">
                <!-- TARGETES ALERTES -->
                <div class="form-section" style="flex:1; min-width:350px; margin-bottom:0; background:#fff; padding:25px; display:flex; flex-direction:column; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                        <h3 style="margin:0; font-size:1.1rem;"><i class="fa-solid fa-bell" style="color:var(--danger); margin-right:8px;"></i> Alertes Prioritàries</h3>
                        <a href="?p=alertes" style="font-size:0.85rem; color:var(--primary); font-weight:600; text-decoration:none;">Veure Totes</a>
                    </div>
                    <?php if($alertes_q && $alertes_q->num_rows > 0): ?>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php while($a = $alertes_q->fetch_assoc()): 
                            $bg = '#f1f5f9'; $c = '#64748b';
                            if($a['nivell_urgencia']=='Crític') { $bg='#fef2f2'; $c='#ef4444'; }
                            else if($a['nivell_urgencia']=='Alt') { $bg='#fff7ed'; $c='#f97316'; }
                            else if($a['nivell_urgencia']=='Mitjà') { $bg='#fefce8'; $c='#eab308'; }
                        ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; border-radius:8px; border:1px solid #f1f5f9; background:#fafafa; transition:all 0.2s;">
                                <div>
                                    <h4 style="margin:0 0 4px 0; font-size:0.95rem; display:flex; align-items:center; gap:8px;">
                                        <?= htmlspecialchars($a['tipus_alerta']) ?>
                                        <span style="font-size:0.65rem; padding:2px 6px; border-radius:4px; font-weight:700; background:<?= $bg ?>; color:<?= $c ?>;"><?= mb_strtoupper($a['nivell_urgencia']) ?></span>
                                        <?php if($a['estat'] === 'Resolta'): ?>
                                            <span style="font-size:0.65rem; padding:2px 6px; border-radius:4px; font-weight:700; background:#dcfce7; color:#16a34a;">RESOLTA</span>
                                        <?php endif; ?>
                                    </h4>
                                    <small style="color:var(--text-muted);"><i class="fa-regular fa-clock"></i> <?= date('d/m/y H:i', strtotime($a['data_generada'])) ?></small>
                                </div>
                                <?php if($a['estat'] !== 'Resolta'): ?>
                                    <a href="?resoldre_alerta=1&id=<?= $a['id_alerta'] ?>&p=home" title="Marcar com a resolta" class="btn" style="padding:6px 12px; font-size:0.8rem; background:white; color:var(--text-main); border:1px solid #e2e8f0; box-shadow:none;"><i class="fa-solid fa-check" style="color:var(--success);"></i></a>
                                <?php else: ?>
                                    <i class="fa-solid fa-check-double" style="color:var(--success); font-size:1.2rem; margin-right:10px;"></i>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align:center; padding:40px 0; margin:auto;">
                            <i class="fa-regular fa-circle-check" style="font-size:2.5rem; color:#cbd5e1; margin-bottom:12px;"></i>
                            <p style="margin:0; color:var(--text-muted); font-size:0.95rem;">No hi ha cap alerta pendent.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TARGETES TASQUES -->
                <div class="form-section" style="flex:1; min-width:350px; margin-bottom:0; background:#fff; padding:25px; display:flex; flex-direction:column; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                        <h3 style="margin:0; font-size:1.1rem;"><i class="fa-solid fa-list-check" style="color:#f97316; margin-right:8px;"></i> Pròximes Tasques</h3>
                        <a href="?p=tasques_hores" style="font-size:0.85rem; color:var(--primary); font-weight:600; text-decoration:none;">Veure Tauler</a>
                    </div>
                    <?php if($tasques_q && $tasques_q->num_rows > 0): ?>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php while($tq = $tasques_q->fetch_assoc()): 
                            $dt = date('d/m', strtotime($tq['data_tasca']));
                            $es_avui = ($tq['data_tasca'] == date('Y-m-d'));
                        ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; border-radius:8px; border:1px solid <?= $es_avui ? '#bbf7d0' : '#f1f5f9' ?>; background:<?= $es_avui ? '#f0fdf4' : '#fafafa' ?>; transition:all 0.2s;">
                                <div style="display:flex; align-items:center; gap:15px;">
                                    <div style="background:<?= $es_avui ? '#10b981' : '#e2e8f0' ?>; color:<?= $es_avui ? '#fff' : 'var(--text-muted)' ?>; font-weight:700; font-size:0.8rem; padding:6px 0; border-radius:6px; text-align:center; min-width:48px;">
                                        <?= $es_avui ? 'AVUI' : $dt ?>
                                    </div>
                                    <div>
                                        <h4 style="margin:0 0 4px 0; font-size:0.95rem; color:var(--text-main); display:flex; align-items:center; gap:8px;">
                                            <?= htmlspecialchars($tq['tipus']) ?>
                                            <?php if(isset($tq['estat']) && $tq['estat'] === 'Completada'): ?>
                                                <span style="font-size:0.65rem; padding:2px 4px; border-radius:4px; background:#dcfce7; color:#16a34a; font-weight:bold;">FET</span>
                                            <?php endif; ?>
                                        </h4>
                                        <small style="color:var(--text-muted);">Prioritat: <?= $tq['prioritat'] ?></small>
                                    </div>
                                </div>
                                <a href="?p=tasques_hores" style="color:#cbd5e1; font-size:1.1rem;"><i class="fa-solid fa-angle-right"></i></a>
                            </div>
                        <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align:center; padding:40px 0; margin:auto;">
                            <i class="fa-solid fa-mug-hot" style="font-size:2.5rem; color:#cbd5e1; margin-bottom:12px;"></i>
                            <p style="margin:0; color:var(--text-muted); font-size:0.95rem;">L'equip no té tasques pendents.</p>
                        </div>
                    <?php endif; ?>
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
                        'tasques_hores' => '<i class="fa-solid fa-clipboard-check" style="margin-right:10px; color:var(--primary);"></i> Gestió de Tasques i Hores',
                        'productes' => '<i class="fa-solid fa-box-open" style="margin-right:10px; color:var(--primary);"></i> Productes Fitosanitaris i Estoc',
                        'inventari' => '<i class="fa-solid fa-warehouse" style="margin-right:10px; color:var(--primary);"></i> Gestió d\'Inventari',
                        'monitoratge_plagues' => '<i class="fa-solid fa-bug" style="margin-right:10px; color:var(--primary);"></i> Tractaments i Plagues',
                        'sensors' => '<i class="fa-solid fa-tower-broadcast" style="margin-right:10px; color:var(--primary);"></i> Sensors i Alertes Intel·ligents',
                    'alertes' => '<i class="fa-solid fa-shield-halved" style="margin-right:10px; color:var(--primary);"></i> Sistema d\'Alertes',
                    'collites' => '<i class="fa-solid fa-wheat-awn" style="margin-right:10px; color:var(--primary);"></i> Registre de Collites',
                    'qualitat' => '<i class="fa-solid fa-microscope" style="margin-right:10px; color:var(--primary);"></i> Control de Qualitat de Fruita',
                    'lots' => '<i class="fa-solid fa-barcode" style="margin-right:10px; color:var(--primary);"></i> Traçabilitat de Lots',
                    'mapa' => '<i class="fa-solid fa-earth-europe" style="margin-right:10px; color:var(--primary);"></i> Mapa de Parcel·les',
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
                case 'tasques_hores': include 'modules/tasques_hores.php'; break;

                case 'inventari': include 'modules/inventari.php'; break;
                case 'monitoratge_plagues': include 'modules/monitoratge_plagues.php'; break;
                case 'sensors': include 'modules/sensors.php'; break;
                case 'alertes': include 'modules/alertes.php'; break;
                case 'collites': include 'modules/collites.php'; break;
                case 'qualitat': include 'modules/qualitat.php'; break;
                case 'lots': include 'modules/lots.php'; break;
                case 'mapa': include 'modules/mapa.php'; break;
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
