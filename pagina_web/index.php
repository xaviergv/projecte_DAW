<?php
session_start();

// Connexió
$conn = new mysqli("localhost", "root", "", "Projecte");
if ($conn->connect_error) die("Error de connexió: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

// Estadístiques Inici
$parceles     = $conn->query("SELECT COUNT(*) FROM `Parcel·la`")->fetch_row()[0];
$cultius      = $conn->query("SELECT COUNT(*) FROM Cultiu")->fetch_row()[0];
$superficie   = $conn->query("SELECT IFNULL(SUM(superficie),0) FROM `Parcel·la`")->fetch_row()[0];
$treballadors = $conn->query("SELECT COUNT(*) FROM Treballador")->fetch_row()[0];

// Llistats
$parceles_list     = $conn->query("SELECT id_parcela, nom, superficie, coordenades, textura FROM `Parcel·la` ORDER BY id_parcela DESC");
$cultius_list      = $conn->query("SELECT id_cultiu, nom_comu, nom_cientific, cicle_vegetatiu, qualitats_fruit FROM Cultiu ORDER BY nom_comu");
$treballadors_list = $conn->query("SELECT nif, nom, cognoms FROM Treballador ORDER BY cognoms");

// === ELIMINAR REGISTRE ===
if (isset($_GET['eliminar'])) {
    $tipus = $_GET['tipus'];
    $id = $_GET['id'];

    if ($tipus === 'parcela') {
        $conn->query("DELETE FROM `Parcel·la` WHERE id_parcela = $id");
        $_SESSION['msg'] = "Parcel·la eliminada!";
    }
    if ($tipus === 'cultiu') {
        $conn->query("DELETE FROM Cultiu WHERE id_cultiu = $id");
        $_SESSION['msg'] = "Cultiu eliminat!";
    }
    if ($tipus === 'treballador') {
        $conn->query("DELETE FROM Treballador WHERE nif = '$id'");
        $_SESSION['msg'] = "Treballador eliminat!";
    }

    header("Location: index.php"); exit;
}

// === AFEGIR NOUS ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nova_parcela'])) {
        $nom         = $conn->real_escape_string(trim($_POST['nom_parcela']));
        $superficie  = floatval($_POST['superficie']);
        $coordenades = $conn->real_escape_string(trim($_POST['coordenades']));
        $textura     = $conn->real_escape_string(trim($_POST['textura']));

        if ($nom !== '' && $superficie > 0) {
            $conn->query("INSERT INTO `Parcel·la` (nom, superficie, coordenades, textura) 
                          VALUES ('$nom', $superficie, '$coordenades', '$textura')");
            $_SESSION['msg'] = "Parcel·la '$nom' afegida correctament!";
        } else {
            $_SESSION['err'] = "Nom i superfície són obligatoris";
        }
    }

    if (isset($_POST['nou_cultiu'])) {
        $nom_comu         = $conn->real_escape_string(trim($_POST['nom_comu']));
        $nom_cientific    = $conn->real_escape_string(trim($_POST['nom_cientific']));
        $cicle_vegetatiu  = $conn->real_escape_string(trim($_POST['cicle_vegetatiu']));
        $qualitats_fruit  = $conn->real_escape_string(trim($_POST['qualitats_fruit']));

        if ($nom_comu !== '') {
            $conn->query("INSERT INTO Cultiu (nom_comu, nom_cientific, cicle_vegetatiu, qualitats_fruit) 
                          VALUES ('$nom_comu', '$nom_cientific', '$cicle_vegetatiu', '$qualitats_fruit')");
            $_SESSION['msg'] = "Cultiu '$nom_comu' afegit!";
        }
    }

    if (isset($_POST['nou_treballador'])) {
        $nif     = strtoupper($conn->real_escape_string(trim($_POST['nif'])));
        $nom     = $conn->real_escape_string(trim($_POST['nom']));
        $cognoms = $conn->real_escape_string(trim($_POST['cognoms']));
        if ($nif !== '' && $nom !== '' && $cognoms !== '') {
            $conn->query("INSERT INTO Treballador (nif, nom, cognoms, data_alta) 
                          VALUES ('$nif', '$nom', '$cognoms', NOW())");
            $_SESSION['msg'] = "Treballador $cognoms $nom afegit!";
        }
    }

    header("Location: index.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Elo - ERP Agrícola</title>
    <style>
        body {font-family:system-ui,sans-serif;background:#f8fafc;color:#1e293b;margin:0;}
        .container {max-width:1200px;margin:0 auto;padding:20px;}
        .navbar {background:#166534;color:white;padding:20px;border-radius:15px;text-align:center;margin-bottom:30px;}
        .navbar h1 {margin:0;font-size:2.5rem;}
        .nav-menu {display:flex;justify-content:center;gap:30px;margin-top:15px;flex-wrap:wrap;}
        .nav-menu a {color:white;text-decoration:none;font-weight:600;font-size:1.2rem;}
        .nav-menu a:hover,.nav-menu a.active {text-decoration:underline;font-weight:800;}
        .section {display:none;padding:25px;background:white;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);margin-bottom:20px;}
        .section.active {display:block;}
        table {width:100%;border-collapse:collapse;margin:20px 0;}
        table th,table td {padding:12px;border-bottom:1px solid #ddd;text-align:left;}
        table th {background:#166534;color:white;}
        .grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin:20px 0;}
        .card {background:#f0fdf4;padding:20px;border-radius:12px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.1);border:1px solid #86efac;}
        .card h3 {margin:0;color:#166534;font-size:2.5rem;}
        .btn-nou {background:#16a34a;color:white;border:none;padding:8px 16px;border-radius:8px;cursor:pointer;font-weight:600;font-size:0.95rem;}
        .btn-nou:hover {background:#15803d;}
        .btn-eliminar {background:#dc2626;color:white;border:none;padding:6px 12px;border-radius:8px;cursor:pointer;font-weight:600;font-size:0.9rem;}
        .btn-eliminar:hover {background:#b91c1c;}
        .header {display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
        .msg {background:#d1fae5;color:#065f46;padding:15px;border-radius:10px;margin:20px 0;text-align:center;font-weight:600;}
        .alerta-error {background:#fee2e2;color:#991b1b;padding:15px;border-radius:10px;margin:20px 0;text-align:center;font-weight:600;}
    </style>
</head>
<body>

<div class="container">
    <nav class="navbar">
        <h1>High Elo</h1>
        <div class="nav-menu">
            <a href="#" onclick="show('home')" class="active">Inici</a>
            <a href="#" onclick="show('parceles')">Parcel·les</a>
            <a href="#" onclick="show('cultius')">Cultius</a>
            <a href="#" onclick="show('personal')">Personal</a>
        </div>
    </nav>

    <?php if(isset($_SESSION['msg'])): ?>
        <div class="msg"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['err'])): ?>
        <div class="alerta-error"><?= $_SESSION['err']; unset($_SESSION['err']); ?></div>
    <?php endif; ?>

    <!-- INICI (amb targetes/quadres com abans) -->
    <section id="home" class="section active">
        <h2 style="text-align:center;color:#166534;margin-bottom:30px;">Benvingut a High Elo</h2>
        <div class="grid">
            <div class="card">
                <h3><?= $parceles ?></h3>
                <p>Parcel·les</p>
            </div>
            <div class="card">
                <h3><?= $cultius ?></h3>
                <p>Cultius</p>
            </div>
            <div class="card">
                <h3><?= number_format($superficie, 2) ?> ha</h3>
                <p>Superfície total</p>
            </div>
            <div class="card">
                <h3><?= $treballadors ?></h3>
                <p>Treballadors</p>
            </div>
        </div>
    </section>

    <!-- PARCEL·LES -->
    <section id="parceles" class="section">
        <div class="header">
            <h2 style="color:#166534;margin:0;">Parcel·les</h2>
            <button class="btn-nou" onclick="location.href='#nova-parcela'">+ Nova parcel·la</button>
        </div>

        <a name="nova-parcela"></a>
        <div style="background:#f0fdf4;padding:20px;border-radius:12px;margin-bottom:30px;">
            <h3 style="margin-top:0;color:#166534;">Afegir nova parcel·la</h3>
            <form method="post">
                <input type="hidden" name="nova_parcela" value="1">
                <p><label>Nom:</label><br>
                <input type="text" name="nom_parcela" required style="width:100%;padding:10px;"></p>
                <p><label>Superfície (ha):</label><br>
                <input type="number" step="0.01" name="superficie" required style="width:100%;padding:10px;"></p>
                <p><label>Coordenades:</label><br>
                <input type="text" name="coordenades" placeholder="ex: 41.3851, 2.1734" style="width:100%;padding:10px;"></p>
                <p><label>Textura del sòl:</label><br>
                <input type="text" name="textura" placeholder="ex: argilós, sorrenc" style="width:100%;padding:10px;"></p>
                <button type="submit" class="btn-nou">Afegir parcel·la</button>
            </form>
        </div>

        <table>
            <tr><th>ID</th><th>Nom</th><th>Superfície (ha)</th><th>Coordenades</th><th>Textura</th><th>Acció</th></tr>
            <?php while($p = $parceles_list->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['id_parcela'] ?></td>
                    <td><?= htmlspecialchars($p['nom'] ?? 'Sense nom') ?></td>
                    <td><?= $p['superficie'] ?></td>
                    <td><?= htmlspecialchars($p['coordenades'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['textura'] ?? '-') ?></td>
                    <td>
                        <a href="?eliminar=1&tipus=parcela&id=<?= $p['id_parcela'] ?>" 
                           onclick="return confirm('Segur que vols eliminar la parcel·la <?= htmlspecialchars($p['nom']) ?>?')" 
                           class="btn-eliminar">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>

    <!-- CULTIUS -->
    <section id="cultius" class="section">
        <div class="header">
            <h2 style="color:#166534;margin:0;">Cultius</h2>
            <button class="btn-nou" onclick="location.href='#nou-cultiu'">+ Nou cultiu</button>
        </div>

        <a name="nou-cultiu"></a>
        <div style="background:#f0fdf4;padding:20px;border-radius:12px;margin-bottom:30px;">
            <h3 style="margin-top:0;color:#166534;">Afegir nou cultiu</h3>
            <form method="post">
                <input type="hidden" name="nou_cultiu" value="1">
                <p><label>Nom comú:</label><br>
                <input type="text" name="nom_comu" required style="width:100%;padding:10px;"></p>
                <p><label>Nom científic:</label><br>
                <input type="text" name="nom_cientific" style="width:100%;padding:10px;"></p>
                <p><label>Cicle vegetatiu:</label><br>
                <input type="text" name="cicle_vegetatiu" placeholder="ex: Anual, Biennal..." style="width:100%;padding:10px;"></p>
                <p><label>Qualitats del fruit:</label><br>
                <input type="text" name="qualitats_fruit" placeholder="ex: Dolç, àcid, gran calibre..." style="width:100%;padding:10px;"></p>
                <button type="submit" class="btn-nou">Afegir cultiu</button>
            </form>
        </div>

        <table>
            <tr><th>Nom comú</th><th>Nom científic</th><th>Cicle vegetatiu</th><th>Qualitats del fruit</th><th>Acció</th></tr>
            <?php while($c = $cultius_list->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['nom_comu']) ?></strong></td>
                    <td><?= htmlspecialchars($c['nom_cientific'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['cicle_vegetatiu'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['qualitats_fruit'] ?? '-') ?></td>
                    <td>
                        <a href="?eliminar=1&tipus=cultiu&id=<?= $c['id_cultiu'] ?>" 
                           onclick="return confirm('Segur que vols eliminar el cultiu <?= htmlspecialchars($c['nom_comu']) ?>?')" 
                           class="btn-eliminar">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>

    <!-- PERSONAL -->
    <section id="personal" class="section">
        <div class="header">
            <h2 style="color:#166534;margin:0;">Personal</h2>
            <button class="btn-nou" onclick="location.href='#nou-treballador'">+ Nou treballador</button>
        </div>

        <a name="nou-treballador"></a>
        <div style="background:#f0fdf4;padding:20px;border-radius:12px;margin-bottom:30px;">
            <h3 style="margin-top:0;color:#166534;">Afegir nou treballador</h3>
            <form method="post">
                <input type="hidden" name="nou_treballador" value="1">
                <p><label>NIF:</label><br>
                <input type="text" name="nif" required maxlength="9" style="text-transform:uppercase;"></p>
                <p><label>Nom:</label><br>
                <input type="text" name="nom" required></p>
                <p><label>Cognoms:</label><br>
                <input type="text" name="cognoms" required></p>
                <button type="submit" class="btn-nou">Afegir treballador</button>
            </form>
        </div>

        <table>
            <tr><th>NIF</th><th>Nom complet</th><th>Acció</th></tr>
            <?php while($t = $treballadors_list->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['nif']) ?></strong></td>
                    <td><?= htmlspecialchars($t['cognoms'].' '.$t['nom']) ?></td>
                    <td>
                        <a href="?eliminar=1&tipus=treballador&id=<?= urlencode($t['nif']) ?>" 
                           onclick="return confirm('Segur que vols eliminar el treballador <?= htmlspecialchars($t['cognoms'].' '.$t['nom']) ?>?')" 
                           class="btn-eliminar">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>
</div>

<script>
function show(id){
    document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    document.querySelectorAll('.nav-menu a').forEach(a=>a.classList.remove('active'));
    document.querySelector(`[onclick="show('${id}')"]`).classList.add('active');
}
</script>
</body>
</html>
