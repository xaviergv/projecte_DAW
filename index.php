<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexió
$conn = new mysqli("localhost", "root", "", "Projecte");
if ($conn->connect_error) {
    die("Error de connexió: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Pestanya activa (per defecte 'home' si no hi ha paràmetre)
$p = $_GET['p'] ?? 'home';

// ────────────────────────────────────────────────
// Estadístiques per la pàgina d'inici
$parceles_count = $conn->query("SELECT COUNT(*) FROM `Parcel·la`")->fetch_row()[0] ?? 0;
$cultius_count  = $conn->query("SELECT COUNT(*) FROM `Cultiu`")->fetch_row()[0] ?? 0;
$superficie     = $conn->query("SELECT IFNULL(SUM(superficie),0) FROM `Parcel·la`")->fetch_row()[0] ?? 0;
$treballadors_count = $conn->query("SELECT COUNT(*) FROM `Treballador`")->fetch_row()[0] ?? 0;

// ────────────────────────────────────────────────
// Dades per al mapa (només quan calgui)
$parceles_json = '[]';
if ($p === 'mapa') {
    $parceles_map = $conn->query("SELECT id_parcela, nom, superficie, coordenades FROM `Parcel·la` WHERE coordenades IS NOT NULL AND coordenades != '' ORDER BY nom");
    $parceles_json = [];
    while ($row = $parceles_map->fetch_assoc()) {
        if (preg_match('/^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)$/', trim($row['coordenades']), $m)) {
            $lat = (float)$m[1];
            $lon = (float)$m[2];
            if ($lat != 0 && $lon != 0) {
                $parceles_json[] = [
                    'lat' => $lat,
                    'lon' => $lon,
                    'nom' => htmlspecialchars($row['nom']),
                    'superficie' => number_format($row['superficie'], 2)
                ];
            }
        }
    }
    $parceles_json = json_encode($parceles_json);
}

// ────────────────────────────────────────────────
// TOTES LES CONSULTES (per a les vistes)
$parceles_list     = $conn->query("SELECT id_parcela, nom, superficie, coordenades, textura FROM `Parcel·la` ORDER BY nom");
$cultius_select    = $conn->query("SELECT id_cultiu, nom_comu FROM `Cultiu` ORDER BY nom_comu");
$varietats_list    = $conn->query("SELECT v.id_varietat, c.nom_comu AS cultiu, v.nom_varietat, v.caracteristiques FROM `Varietat` v JOIN `Cultiu` c ON v.id_cultiu = c.id_cultiu ORDER BY c.nom_comu, v.nom_varietat");
$cultius_list      = $conn->query("SELECT id_cultiu, nom_comu, nom_cientific, cicle_vegetatiu, qualitats_fruit FROM `Cultiu` ORDER BY nom_comu");
$parceles_select   = $conn->query("SELECT id_parcela, nom FROM `Parcel·la` ORDER BY nom");
$sectors_select    = $conn->query("SELECT s.id_sector, p.nom AS nom_parcela FROM `Sector_Cultiu` s JOIN `Parcel·la` p ON s.id_parcela = p.id_parcela ORDER BY p.nom");
$sectors_cultiu_list = $conn->query("SELECT s.id_sector, p.nom AS nom_parcela, s.data_plantacio, s.marc_plantacio_arbres, s.marc_plantacio_files, s.num_arbres, s.previsio_produccio FROM `Sector_Cultiu` s JOIN `Parcel·la` p ON s.id_parcela = p.id_parcela ORDER BY p.nom");
$files_arbres_list = $conn->query("SELECT f.id_fila, f.id_sector, p.nom AS nom_parcela, f.numero_fila, f.coordenades_fila, f.notes FROM `Fila_Arbres` f JOIN `Sector_Cultiu` s ON f.id_sector = s.id_sector JOIN `Parcel·la` p ON s.id_parcela = p.id_parcela ORDER BY p.nom, f.numero_fila");

$treballadors_list = $conn->query("SELECT id_treballador, nif, nom, cognoms, telefon FROM `Treballador` ORDER BY cognoms");
$absencies_list    = $conn->query("SELECT a.id_absencia, t.nom, t.cognoms, a.tipus, a.data_inici, a.data_fi, a.aprovada FROM `Absencia` a JOIN `Treballador` t ON a.id_treballador = t.id_treballador ORDER BY a.data_inici DESC");
$contractes_list   = $conn->query("SELECT c.id_contracte, t.nif, t.nom, t.cognoms, c.categoria_professional, c.salari_brut_anual FROM `Contracte` c JOIN `Treballador` t ON c.id_treballador = t.id_treballador ORDER BY t.cognoms");
$tasques_list      = $conn->query("SELECT id_tasca, nom, descripcio, id_sector, id_parcela, hores_estimades, finalitzada FROM `Tasca` ORDER BY nom");
$assignacions_list = $conn->query("SELECT a.id_assignacio, a.id_tasca, t.nif, t.nom, t.cognoms, a.es_cap_equip FROM `Assignacio_Treballador_Tasca` a JOIN `Treballador` t ON a.id_treballador = t.id_treballador ORDER BY a.id_tasca");
$sectors_tasques   = $conn->query("SELECT s.id_sector, p.nom FROM `Sector_Cultiu` s JOIN `Parcel·la` p ON s.id_parcela = p.id_parcela ORDER BY p.nom");

// ────────────────────────────────────────────────
// PROCESSAMENT DE TOTES LES ACCIONS (POST i GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = $_POST['p'] ?? 'parceles';

    // Parcel·la
    if (isset($_POST['nou_parcela'])) {
        $nom = trim($_POST['nom'] ?? '');
        $superficie = (float)($_POST['superficie'] ?? 0);
        $coordenades = trim($_POST['coordenades'] ?? '');
        $textura = trim($_POST['textura'] ?? '');

        if ($nom && $superficie > 0) {
            $stmt = $conn->prepare("INSERT INTO `Parcel·la` (nom, superficie, coordenades, textura) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdss", $nom, $superficie, $coordenades, $textura);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Parcel·la afegida correctament!";
        } else {
            $_SESSION['err'] = "Nom i superfície obligatoris";
        }
    }

    // ... (afegeix aquí la resta de blocs POST: varietat, cultiu, sector, fila, treballador, absencia, contracte, tasca, assignacio)
    // Pots copiar-los exactament dels missatges anteriors

    header("Location: index.php?p=$p");
    exit;
}

// ────────────────────────────────────────────────
// ELIMINAR / FINALITZAR / APROVAR (GET)
if (isset($_GET['eliminar'])) {
    $tipus = $_GET['tipus'] ?? '';
    $id = (int)($_GET['id'] ?? 0);

    if ($tipus === 'parcela') $conn->query("DELETE FROM `Parcel·la` WHERE id_parcela = $id");
    elseif ($tipus === 'varietat') $conn->query("DELETE FROM `Varietat` WHERE id_varietat = $id");
    elseif ($tipus === 'cultiu') $conn->query("DELETE FROM `Cultiu` WHERE id_cultiu = $id");
    elseif ($tipus === 'sector') $conn->query("DELETE FROM `Sector_Cultiu` WHERE id_sector = $id");
    elseif ($tipus === 'fila') $conn->query("DELETE FROM `Fila_Arbres` WHERE id_fila = $id");
    elseif ($tipus === 'treballador') $conn->query("DELETE FROM `Treballador` WHERE id_treballador = $id");
    elseif ($tipus === 'absencia') $conn->query("DELETE FROM `Absencia` WHERE id_absencia = $id");
    elseif ($tipus === 'contracte') $conn->query("DELETE FROM `Contracte` WHERE id_contracte = $id");
    elseif ($tipus === 'tasca') $conn->query("DELETE FROM `Tasca` WHERE id_tasca = $id");
    elseif ($tipus === 'assignacio') $conn->query("DELETE FROM `Assignacio_Treballador_Tasca` WHERE id_assignacio = $id");

    $_SESSION['msg'] = ucfirst($tipus) . " eliminat!";
    header("Location: index.php?p=$p");
    exit;
}

if (isset($_GET['finalitzar'])) {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $conn->query("UPDATE `Tasca` SET finalitzada = 1 WHERE id_tasca = $id");
        $_SESSION['msg'] = "Tasca finalitzada!";
    }
    header("Location: index.php?p=$p");
    exit;
}

if (isset($_GET['aprovar_absencia'])) {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $conn->query("UPDATE `Absencia` SET aprovada = 1 WHERE id_absencia = $id");
        $_SESSION['msg'] = "Absència aprovada!";
    }
    header("Location: index.php?p=$p");
    exit;
}

// ────────────────────────────────────────────────
// Missatges
$msg = $_SESSION['msg'] ?? '';
$err = $_SESSION['err'] ?? '';
unset($_SESSION['msg'], $_SESSION['err']);

// Passar variables als inclosos
extract(get_defined_vars());
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Elo</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body {font-family:system-ui,sans-serif;background:#f8fafc;color:#1e293b;margin:0;padding:0;}
        .container {max-width:1400px;margin:0 auto;padding:20px;}
        .navbar {background:#166534;color:white;padding:20px;text-align:center;border-radius:0 0 15px 15px;margin-bottom:30px;}
        .navbar h1 {margin:0;font-size:2.5rem;}
        .nav-menu {display:flex;justify-content:center;gap:40px;margin-top:15px;flex-wrap:wrap;}
        .nav-menu a {color:white;text-decoration:none;font-weight:600;font-size:1.2rem;cursor:pointer;padding:8px 16px;border-radius:8px;}
        .nav-menu a:hover, .nav-menu a.active {background:#15803d;}
        .section {background:white;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);padding:25px;margin-bottom:30px;}
        table {width:100%;border-collapse:collapse;margin:20px 0;}
        table th, table td {padding:12px;border-bottom:1px solid #ddd;text-align:left;}
        table th {background:#166534;color:white;}
        .form-section {background:#f0fdf4;padding:25px;border-radius:12px;margin-bottom:30px;}
        .btn {background:#16a34a;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:600;}
        .btn:hover {background:#15803d;}
        .btn-red {background:#dc2626;}
        .btn-red:hover {background:#b91c1c;}
        .btn-aprovar {background:#f59e0b;color:white;}
        .btn-aprovar:hover {background:#d97706;}
        .msg {background:#d1fae5;color:#065f46;padding:15px;border-radius:10px;margin:20px 0;text-align:center;font-weight:600;}
        .err {background:#fee2e2;color:#991b1b;padding:15px;border-radius:10px;margin:20px 0;text-align:center;font-weight:600;}
        input, select, textarea {width:100%;padding:10px;margin:8px 0 16px;border:1px solid #ccc;border-radius:6px;box-sizing:border-box;}
        #map {height: 600px; border: 1px solid #86efac; border-radius: 12px; margin-top: 20px;}
        h2 {color:#166534;margin-top:40px;}
        .finalitzada {background:#e6ffe6;}
        .grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin:20px 0;}
        .card {background:#f0fdf4;padding:20px;border-radius:12px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.1);border:1px solid #86efac;}
        .card h3 {margin:0;color:#166534;font-size:2.5rem;}
    </style>
</head>
<body>

<div class="container">
    <nav class="navbar">
        <h1>High Elo</h1>
        <div class="nav-menu">
            <a href="?p=home" class="<?= $p === 'home' ? 'active' : '' ?>">Inici</a>
            <a href="?p=parceles" class="<?= $p === 'parceles' ? 'active' : '' ?>">Parcel·les</a>
            <a href="?p=cultius"   class="<?= $p === 'cultius'   ? 'active' : '' ?>">Cultius</a>
            <a href="?p=personal"  class="<?= $p === 'personal'  ? 'active' : '' ?>">Personal</a>
            <a href="?p=mapa"      class="<?= $p === 'mapa'      ? 'active' : '' ?>">Mapa</a>
        </div>
    </nav>

    <?php if ($msg): ?>
        <div class="msg"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($err): ?>
        <div class="err"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <?php if ($p === 'home'): ?>
        <!-- PÀGINA D'INICI -->
        <div class="section">
            <h2 style="text-align:center;color:#166534;margin-bottom:30px;">Benvingut a High Elo</h2>
            <div class="grid">
                <div class="card">
                    <h3><?= $parceles_count ?></h3>
                    <p>Parcel·les</p>
                </div>
                <div class="card">
                    <h3><?= $cultius_count ?></h3>
                    <p>Cultius</p>
                </div>
                <div class="card">
                    <h3><?= number_format($superficie, 2) ?> ha</h3>
                    <p>Superfície total</p>
                </div>
                <div class="card">
                    <h3><?= $treballadors_count ?></h3>
                    <p>Treballadors</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php
        switch ($p) {
            case 'cultius':
                include 'cultius.php';
                break;
            case 'personal':
                include 'personal.php';
                break;
            case 'mapa':
                include 'mapa.php';
                break;
            default:
                include 'parcela.php';
                break;
        }
        ?>
    <?php endif; ?>
</div>

</body>
</html>
