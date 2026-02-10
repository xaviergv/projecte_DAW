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

// === CREACIÓ DE TAULES (SI NO EXISTEIXEN) ===
$conn->query("
    CREATE TABLE IF NOT EXISTS `Sensors` (
        id_sensor INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100),
        tipus VARCHAR(50),
        localitzacio VARCHAR(100),
        estat VARCHAR(20) DEFAULT 'Actiu'
    )
");
$conn->query("
    CREATE TABLE IF NOT EXISTS `Alertes` (
        id_alerta INT AUTO_INCREMENT PRIMARY KEY,
        id_sensor INT,
        missatge TEXT,
        data DATETIME DEFAULT CURRENT_TIMESTAMP,
        nivell VARCHAR(20),
        resolta TINYINT(1) DEFAULT 0,
        FOREIGN KEY (id_sensor) REFERENCES Sensors(id_sensor) ON DELETE CASCADE
    )
");
$conn->query("
    CREATE TABLE IF NOT EXISTS `Producte` (
        id_producte INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100),
        descripcio TEXT,
        preu_unitari DECIMAL(10,2),
        stock_actual DECIMAL(10,2),
        unitat_mesura VARCHAR(20)
    )
");
$conn->query("
    CREATE TABLE IF NOT EXISTS `Absencia` (
        id_absencia INT AUTO_INCREMENT PRIMARY KEY,
        id_treballador INT,
        data_inici DATE,
        data_fi DATE,
        motiu VARCHAR(255),
        estat VARCHAR(50) DEFAULT 'Pendent',
        FOREIGN KEY (id_treballador) REFERENCES Treballador(id_treballador) ON DELETE CASCADE
    )
");
$conn->query("
    CREATE TABLE IF NOT EXISTS `Monitoratge_Plagues` (
        id_monitoratge INT AUTO_INCREMENT PRIMARY KEY,
        id_parcela INT,
        data DATE,
        plaga VARCHAR(100),
        severitat VARCHAR(50),
        observacions TEXT,
        FOREIGN KEY (id_parcela) REFERENCES `Parcel·la`(id_parcela) ON DELETE CASCADE
    )
");
$conn->query("
    CREATE TABLE IF NOT EXISTS `Tractament` (
        id_tractament INT AUTO_INCREMENT PRIMARY KEY,
        id_parcela INT,
        data DATE,
        producte_utilitzat VARCHAR(100),
        quantitat DECIMAL(10,2),
        observacions TEXT,
        FOREIGN KEY (id_parcela) REFERENCES `Parcel·la`(id_parcela) ON DELETE CASCADE
    )
");
$conn->query("
    CREATE TABLE IF NOT EXISTS `Analisi_Nutricional` (
        id_analisi INT AUTO_INCREMENT PRIMARY KEY,
        id_parcela INT,
        data DATE,
        tipus_analisi VARCHAR(100),
        resultat_text TEXT,
        FOREIGN KEY (id_parcela) REFERENCES `Parcel·la`(id_parcela) ON DELETE CASCADE
    )
");
$conn->query("
    CREATE TABLE IF NOT EXISTS `Calendari_Fitosanitari` (
        id_esdeveniment INT AUTO_INCREMENT PRIMARY KEY,
        data_prevista DATE,
        accio VARCHAR(255),
        id_parcela INT,
        realitzat TINYINT(1) DEFAULT 0,
        FOREIGN KEY (id_parcela) REFERENCES `Parcel·la`(id_parcela) ON DELETE CASCADE
    )
");

// Estadístiques per Inici
$parceles_count = $conn->query("SELECT COUNT(*) FROM `Parcel·la`")->fetch_row()[0] ?? 0;
$cultius_count  = $conn->query("SELECT COUNT(*) FROM `Cultiu`")->fetch_row()[0] ?? 0;
$superficie     = $conn->query("SELECT IFNULL(SUM(superficie),0) FROM `Parcel·la`")->fetch_row()[0] ?? 0;
$treballadors_count = $conn->query("SELECT COUNT(*) FROM `Treballador`")->fetch_row()[0] ?? 0;

// Dades per al mapa (només parcel·les amb coordenades vàlides)
$parceles_map = $conn->query("SELECT id_parcela, nom, superficie, coordenades FROM `Parcel·la` WHERE coordenades IS NOT NULL AND coordenades != '' ORDER BY nom");

$parceles_json = [];
while ($p = $parceles_map->fetch_assoc()) {
    if (preg_match('/^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)$/', trim($p['coordenades']), $matches)) {
        $lat = (float)$matches[1];
        $lon = (float)$matches[2];
        if ($lat != 0 && $lon != 0) {
            $parceles_json[] = [
                'lat' => $lat,
                'lon' => $lon,
                'nom' => htmlspecialchars($p['nom']),
                'superficie' => number_format($p['superficie'], 2),
                'id' => $p['id_parcela']
            ];
        }
    }
}
$parceles_json = json_encode($parceles_json);

// Parcel·les existents per comprovar id_parcela
$parceles_ids = [];
$result = $conn->query("SELECT id_parcela FROM `Parcel·la`");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $parceles_ids[] = $row['id_parcela'];
    }
}

// Llistats per Personal
$treballadors_list = $conn->query("SELECT id_treballador, nif, nom, cognoms, telefon, data_alta, data_baixa, actiu FROM `Treballador` ORDER BY cognoms");

$contractes_list = $conn->query("
    SELECT c.id_contracte, c.id_treballador, t.nif, t.nom, t.cognoms, c.categoria_professional, c.salari_brut_anual 
    FROM `Contracte` c 
    JOIN `Treballador` t ON c.id_treballador = t.id_treballador 
    ORDER BY t.cognoms, t.nom
");

$absencies_list = $conn->query("
    SELECT a.id_absencia, a.data_inici, a.data_fi, a.motiu, a.estat, t.nom, t.cognoms
    FROM `Absencia` a
    JOIN `Treballador` t ON a.id_treballador = t.id_treballador
    ORDER BY a.data_inici DESC
");

$tasques_list = $conn->query("
    SELECT id_tasca, nom, descripcio, id_sector, id_parcela, hores_estimades, finalitzada 
    FROM `Tasca` 
    ORDER BY nom
");

$assignacions_list = $conn->query("
    SELECT a.id_assignacio, a.id_tasca, a.id_treballador, t.nif, t.nom, t.cognoms, a.es_cap_equip 
    FROM `Assignacio_Treballador_Tasca` a 
    JOIN `Treballador` t ON a.id_treballador = t.id_treballador 
    ORDER BY a.id_tasca
");

// Sectors per desplegable tasques
$sectors_list = $conn->query("SELECT s.id_sector, p.nom FROM `Sector_Cultiu` s JOIN `Parcel·la` p ON s.id_parcela = p.id_parcela ORDER BY p.nom");

// Llistats per Cultius
$cultius_list = $conn->query("SELECT id_cultiu, nom_comu, nom_cientific, cicle_vegetatiu, qualitats_fruit FROM `Cultiu` ORDER BY nom_comu");

$sectors_cultiu_list = $conn->query("
    SELECT s.id_sector, p.nom AS nom_parcela, s.data_plantacio, s.marc_plantacio_arbres, s.marc_plantacio_files, s.num_arbres, s.previsio_produccio 
    FROM `Sector_Cultiu` s 
    JOIN `Parcel·la` p ON s.id_parcela = p.id_parcela 
    ORDER BY p.nom
");

$files_arbres_list = $conn->query("
    SELECT f.id_fila, f.id_sector, p.nom AS nom_parcela, f.numero_fila, f.coordenades_fila, f.notes 
    FROM `Fila_Arbres` f 
    JOIN `Sector_Cultiu` s ON f.id_sector = s.id_sector 
    JOIN `Parcel·la` p ON s.id_parcela = p.id_parcela 
    ORDER BY p.nom, f.numero_fila
");

// Llistat per Parcel·les
$parceles_list = $conn->query("SELECT id_parcela, nom, superficie, coordenades, textura FROM `Parcel·la` ORDER BY nom");

// Llistats nous
$sensors_list = $conn->query("SELECT * FROM `Sensors` ORDER BY nom");
$alertes_list = $conn->query("SELECT a.*, s.nom AS nom_sensor FROM `Alertes` a LEFT JOIN `Sensors` s ON a.id_sensor = s.id_sensor ORDER BY a.data DESC");
$productes_list = $conn->query("SELECT * FROM `Producte` ORDER BY nom");
$plagues_list = $conn->query("SELECT m.*, p.nom AS nom_parcela FROM `Monitoratge_Plagues` m JOIN `Parcel·la` p ON m.id_parcela = p.id_parcela ORDER BY m.data DESC");
$tractaments_list = $conn->query("SELECT t.*, p.nom AS nom_parcela FROM `Tractament` t JOIN `Parcel·la` p ON t.id_parcela = p.id_parcela ORDER BY t.data DESC");
$analisi_list = $conn->query("SELECT a.*, p.nom AS nom_parcela FROM `Analisi_Nutricional` a JOIN `Parcel·la` p ON a.id_parcela = p.id_parcela ORDER BY a.data DESC");
$calendari_list = $conn->query("SELECT c.*, p.nom AS nom_parcela FROM `Calendari_Fitosanitari` c JOIN `Parcel·la` p ON c.id_parcela = p.id_parcela ORDER BY c.data_prevista ASC");

// === ELIMINAR ===
if (isset($_GET['eliminar'])) {
    $tipus = $_GET['tipus'] ?? '';
    $id = (int)($_GET['id'] ?? 0);

    if ($tipus === 'treballador') {
        $conn->query("DELETE FROM `Treballador` WHERE id_treballador = $id");
        $_SESSION['msg'] = "Treballador eliminat!";
    } elseif ($tipus === 'contracte') {
        $conn->query("DELETE FROM `Contracte` WHERE id_contracte = $id");
        $_SESSION['msg'] = "Contracte eliminat!";
    } elseif ($tipus === 'tasca') {
        $conn->query("DELETE FROM `Tasca` WHERE id_tasca = $id");
        $_SESSION['msg'] = "Tasca eliminada!";
    } elseif ($tipus === 'assignacio') {
        $conn->query("DELETE FROM `Assignacio_Treballador_Tasca` WHERE id_assignacio = $id");
        $_SESSION['msg'] = "Assignació eliminada!";
    } elseif ($tipus === 'cultiu') {
        $conn->query("DELETE FROM `Cultiu` WHERE id_cultiu = $id");
        $_SESSION['msg'] = "Cultiu eliminat!";
    } elseif ($tipus === 'sector_cultiu') {
        $conn->query("DELETE FROM `Sector_Cultiu` WHERE id_sector = $id");
        $_SESSION['msg'] = "Sector cultiu eliminat!";
    } elseif ($tipus === 'fila_arbres') {
        $conn->query("DELETE FROM `Fila_Arbres` WHERE id_fila = $id");
        $_SESSION['msg'] = "Fila d'arbres eliminada!";
    } elseif ($tipus === 'parcela') {
        $check_tasca = $conn->query("SELECT COUNT(*) FROM `Tasca` WHERE id_parcela = $id")->fetch_row()[0] ?? 0;
        $check_sector = $conn->query("SELECT COUNT(*) FROM `Sector_Cultiu` WHERE id_parcela = $id")->fetch_row()[0] ?? 0;

        if ($check_tasca > 0 || $check_sector > 0) {
            $_SESSION['err'] = "No es pot eliminar la parcel·la: hi ha $check_tasca tasca(es) i/o $check_sector sector(s) associats. Elimina'ls primer.";
        } else {
            $conn->query("DELETE FROM `Parcel·la` WHERE id_parcela = $id");
            $_SESSION['msg'] = "Parcel·la eliminada!";
        }
    } elseif ($tipus === 'sensor') {
        $conn->query("DELETE FROM `Sensors` WHERE id_sensor = $id");
        $_SESSION['msg'] = "Sensor eliminat!";
    } elseif ($tipus === 'alerta') {
        $conn->query("DELETE FROM `Alertes` WHERE id_alerta = $id");
        $_SESSION['msg'] = "Alerta eliminada!";
    } elseif ($tipus === 'producte') {
        $conn->query("DELETE FROM `Producte` WHERE id_producte = $id");
        $_SESSION['msg'] = "Producte eliminat!";
    } elseif ($tipus === 'absencia') {
        $conn->query("DELETE FROM `Absencia` WHERE id_absencia = $id");
        $_SESSION['msg'] = "Absència eliminada!";
    } elseif ($tipus === 'plaga') {
        $conn->query("DELETE FROM `Monitoratge_Plagues` WHERE id_monitoratge = $id");
        $_SESSION['msg'] = "Monitoratge eliminat!";
    } elseif ($tipus === 'tractament') {
        $conn->query("DELETE FROM `Tractament` WHERE id_tractament = $id");
        $_SESSION['msg'] = "Tractament eliminat!";
    } elseif ($tipus === 'analisi') {
        $conn->query("DELETE FROM `Analisi_Nutricional` WHERE id_analisi = $id");
        $_SESSION['msg'] = "Anàlisi eliminada!";
    } elseif ($tipus === 'calendari') {
        $conn->query("DELETE FROM `Calendari_Fitosanitari` WHERE id_esdeveniment = $id");
        $_SESSION['msg'] = "Esdeveniment eliminat!";
    }

    header("Location: index.php");
    exit;
}

// === FINALITZAR TASCA ===
if (isset($_GET['finalitzar'])) {
    $id_tasca = (int)($_GET['id'] ?? 0);
    if ($id_tasca > 0) {
        $conn->query("UPDATE `Tasca` SET finalitzada = 1 WHERE id_tasca = $id_tasca");
        $_SESSION['msg'] = "Tasca finalitzada!";
    }
    header("Location: index.php");
    exit;
}

// === AFEGIR ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Treballador
    if (isset($_POST['nou_treballador'])) {
        $nif      = strtoupper(trim($_POST['nif'] ?? ''));
        $nom      = trim($_POST['nom'] ?? '');
        $cognoms  = trim($_POST['cognoms'] ?? '');
        $telefon  = trim($_POST['telefon'] ?? '');
        $data_alta = $_POST['data_alta'] ?: date('Y-m-d');
        $data_baixa = $_POST['data_baixa'] ?: null;
        $actiu    = isset($_POST['actiu']) ? 1 : 0;

        if ($nif && $nom && $cognoms) {
            $stmt = $conn->prepare("INSERT INTO `Treballador` (nif, nom, cognoms, telefon, data_alta, data_baixa, actiu) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $nif, $nom, $cognoms, $telefon, $data_alta, $data_baixa, $actiu);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Treballador afegit!";
        } else {
            $_SESSION['err'] = "NIF, Nom i Cognoms obligatoris";
        }
    }

    // Contracte
    if (isset($_POST['nou_contracte'])) {
        $id_treballador = (int)($_POST['id_treballador'] ?? 0);
        $categoria = trim($_POST['categoria_professional'] ?? '');
        $salari = (float)($_POST['salari_brut_anual'] ?? 0);

        if ($id_treballador > 0 && $categoria && $salari > 0) {
            $check = $conn->prepare("SELECT id_contracte FROM `Contracte` WHERE id_treballador = ?");
            $check->bind_param("i", $id_treballador);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $check->bind_result($id_contracte);
                $check->fetch();
                $stmt = $conn->prepare("UPDATE `Contracte` SET categoria_professional = ?, salari_brut_anual = ? WHERE id_contracte = ?");
                $stmt->bind_param("sdi", $categoria, $salari, $id_contracte);
                $msg = "Contracte actualitzat!";
            } else {
                $stmt = $conn->prepare("INSERT INTO `Contracte` (id_treballador, categoria_professional, salari_brut_anual) VALUES (?, ?, ?)");
                $stmt->bind_param("isd", $id_treballador, $categoria, $salari);
                $msg = "Contracte afegit!";
            }
            $check->close();

            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = $msg;
        } else {
            $_SESSION['err'] = "Treballador, categoria i salari obligatoris";
        }
    }

    // Tasca
    if (isset($_POST['nova_tasca'])) {
        $nom = trim($_POST['nom'] ?? '');
        $descripcio = trim($_POST['descripcio'] ?? '');
        $id_sector = ($_POST['id_sector'] && $_POST['id_sector'] != '0') ? (int)$_POST['id_sector'] : null;
        $id_parcela = (int)($_POST['id_parcela'] ?? 0);
        $hores_estimades = (float)($_POST['hores_estimades'] ?? 0);

        if ($nom && $id_parcela > 0 && in_array($id_parcela, $parceles_ids)) {
            $stmt = $conn->prepare("
                INSERT INTO `Tasca` (nom, descripcio, id_sector, id_parcela, hores_estimades) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssiid", $nom, $descripcio, $id_sector, $id_parcela, $hores_estimades);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Tasca '$nom' afegida!";
        } else {
            $_SESSION['err'] = "Nom i ID Parcel·la vàlid obligatoris";
        }
    }

    // Assignació
    if (isset($_POST['nou_assignacio'])) {
        $id_tasca = (int)($_POST['id_tasca'] ?? 0);
        $id_treballador = (int)($_POST['id_treballador'] ?? 0);
        $es_cap = isset($_POST['es_cap_equip']) ? 1 : 0;

        if ($id_tasca > 0 && $id_treballador > 0) {
            $stmt = $conn->prepare("INSERT INTO `Assignacio_Treballador_Tasca` (id_tasca, id_treballador, es_cap_equip) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $id_tasca, $id_treballador, $es_cap);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Assignació afegida!";
        } else {
            $_SESSION['err'] = "Tasca i treballador obligatoris";
        }
    }

    // Nou cultiu
    if (isset($_POST['nou_cultiu'])) {
        $nom_comu = trim($_POST['nom_comu'] ?? '');
        $nom_cientific = trim($_POST['nom_cientific'] ?? '');
        $cicle = trim($_POST['cicle_vegetatiu'] ?? '');
        $qualitats = trim($_POST['qualitats_fruit'] ?? '');

        if ($nom_comu) {
            $stmt = $conn->prepare("INSERT INTO `Cultiu` (nom_comu, nom_cientific, cicle_vegetatiu, qualitats_fruit) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nom_comu, $nom_cientific, $cicle, $qualitats);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Cultiu afegit!";
        } else {
            $_SESSION['err'] = "Nom comú obligatori";
        }
    }

    // Nou sector cultiu
    if (isset($_POST['nou_sector_cultiu'])) {
        $id_parcela = (int)($_POST['id_parcela'] ?? 0);
        $data_plantacio = $_POST['data_plantacio'] ?: null;
        $marc_arbres = trim($_POST['marc_plantacio_arbres'] ?? '');
        $marc_files = trim($_POST['marc_plantacio_files'] ?? '');
        $num_arbres = (int)($_POST['num_arbres'] ?? 0);
        $previsio = (float)($_POST['previsio_produccio'] ?? 0);

        if ($id_parcela > 0 && in_array($id_parcela, $parceles_ids) && $marc_arbres && $marc_files) {
            $stmt = $conn->prepare("
                INSERT INTO `Sector_Cultiu` (id_parcela, data_plantacio, marc_plantacio_arbres, marc_plantacio_files, num_arbres, previsio_produccio) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssii", $id_parcela, $data_plantacio, $marc_arbres, $marc_files, $num_arbres, $previsio);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Sector cultiu afegit!";
        } else {
            $_SESSION['err'] = "Parcel·la vàlida, marc arbres i marc files obligatoris";
        }
    }

    // Nova fila arbres
    if (isset($_POST['nou_fila_arbres'])) {
        $id_sector = (int)($_POST['id_sector'] ?? 0);
        $numero_fila = (int)($_POST['numero_fila'] ?? 0);
        $coordenades = trim($_POST['coordenades_fila'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($id_sector > 0 && $numero_fila > 0 && $coordenades) {
            $stmt = $conn->prepare("INSERT INTO `Fila_Arbres` (id_sector, numero_fila, coordenades_fila, notes) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $id_sector, $numero_fila, $coordenades, $notes);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Fila d'arbres afegida!";
        } else {
            $_SESSION['err'] = "Sector, número fila i coordenades obligatoris";
        }
    }

    // Nova parcel·la
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
            $_SESSION['msg'] = "Parcel·la '$nom' afegida!";
        } else {
            $_SESSION['err'] = "Nom i superfície obligatoris";
        }
    }

    // Nou sensor
    if (isset($_POST['nou_sensor'])) {
        $nom = trim($_POST['nom'] ?? '');
        $tipus = trim($_POST['tipus'] ?? '');
        $loc = trim($_POST['localitzacio'] ?? '');
        
        if ($nom && $tipus) {
            $stmt = $conn->prepare("INSERT INTO `Sensors` (nom, tipus, localitzacio) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nom, $tipus, $loc);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Sensor afegit!";
        } else {
            $_SESSION['err'] = "Nom i tipus obligatoris";
        }
    }

    // Nova alerta
    if (isset($_POST['nova_alerta'])) {
        $id_sensor = (int)($_POST['id_sensor'] ?? 0);
        $missatge = trim($_POST['missatge'] ?? '');
        $nivell = $_POST['nivell'] ?? 'Baix';

        if ($id_sensor > 0 && $missatge) {
            $stmt = $conn->prepare("INSERT INTO `Alertes` (id_sensor, missatge, nivell) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $id_sensor, $missatge, $nivell);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Alerta creada!";
        }
    }

    // Nou producte
    if (isset($_POST['nou_producte'])) {
        $nom = trim($_POST['nom'] ?? '');
        $desc = trim($_POST['descripcio'] ?? '');
        $preu = (float)($_POST['preu_unitari'] ?? 0);
        $stock = (float)($_POST['stock_actual'] ?? 0);
        $unitat = trim($_POST['unitat_mesura'] ?? '');

        if ($nom) {
            $stmt = $conn->prepare("INSERT INTO `Producte` (nom, descripcio, preu_unitari, stock_actual, unitat_mesura) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdds", $nom, $desc, $preu, $stock, $unitat);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Producte afegit!";
        }
    }

    // Nova absencia
    if (isset($_POST['nova_absencia'])) {
        $id_treballador = (int)($_POST['id_treballador'] ?? 0);
        $data_inici = $_POST['data_inici'] ?? '';
        $data_fi = $_POST['data_fi'] ?? '';
        $motiu = trim($_POST['motiu'] ?? '');

        if ($id_treballador > 0 && $data_inici) {
            $stmt = $conn->prepare("INSERT INTO `Absencia` (id_treballador, data_inici, data_fi, motiu) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $id_treballador, $data_inici, $data_fi, $motiu);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] = "Absència registrada!";
        } else {
            $_SESSION['err'] = "Treballador i Data Inici obligatoris";
        }
    }

    // Nova plaga
    if (isset($_POST['nova_plaga'])) {
        $id_parcela = (int)($_POST['id_parcela'] ?? 0);
        $data = $_POST['data'] ?? date('Y-m-d');
        $plaga = trim($_POST['plaga'] ?? '');
        $severitat = $_POST['severitat'] ?? '';
        $obs = trim($_POST['observacions'] ?? '');

        if ($id_parcela > 0 && $plaga) {
            $stmt = $conn->prepare("INSERT INTO `Monitoratge_Plagues` (id_parcela, data, plaga, severitat, observacions) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $id_parcela, $data, $plaga, $severitat, $obs);
            $stmt->execute();
            $_SESSION['msg'] = "Monitoratge registrat!";
        }
    }

    // Nou tractament
    if (isset($_POST['nou_tractament'])) {
        $id_parcela = (int)($_POST['id_parcela'] ?? 0);
        $data = $_POST['data'] ?? date('Y-m-d');
        $prod = trim($_POST['producte_utilitzat'] ?? '');
        $qt = (float)($_POST['quantitat'] ?? 0);
        $obs = trim($_POST['observacions'] ?? '');

        if ($id_parcela > 0 && $prod) {
            $stmt = $conn->prepare("INSERT INTO `Tractament` (id_parcela, data, producte_utilitzat, quantitat, observacions) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issds", $id_parcela, $data, $prod, $qt, $obs);
            $stmt->execute();
            $_SESSION['msg'] = "Tractament registrat!";
        }
    }

    // Nova analisi
    if (isset($_POST['nova_analisi'])) {
        $id_parcela = (int)($_POST['id_parcela'] ?? 0);
        $data = $_POST['data'] ?? date('Y-m-d');
        $tipus = trim($_POST['tipus_analisi'] ?? '');
        $resultat = trim($_POST['resultat_text'] ?? '');

        if ($id_parcela > 0 && $tipus) {
            $stmt = $conn->prepare("INSERT INTO `Analisi_Nutricional` (id_parcela, data, tipus_analisi, resultat_text) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $id_parcela, $data, $tipus, $resultat);
            $stmt->execute();
            $_SESSION['msg'] = "Anàlisi registrada!";
        }
    }

    // Nou esdeveniment calendari
    if (isset($_POST['nou_esdeveniment'])) {
        $id_parcela = (int)($_POST['id_parcela'] ?? 0);
        $data = $_POST['data_prevista'] ?? date('Y-m-d');
        $accio = trim($_POST['accio'] ?? '');

        if ($id_parcela > 0 && $accio) {
            $stmt = $conn->prepare("INSERT INTO `Calendari_Fitosanitari` (id_parcela, data_prevista, accio) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $id_parcela, $data, $accio);
            $stmt->execute();
            $_SESSION['msg'] = "Esdeveniment al calendari!";
        }
    }

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Elo - ERP Agrícola</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body {font-family:system-ui,sans-serif;background:#f8fafc;color:#1e293b;margin:0;}
        .container {max-width:1400px;margin:0 auto;padding:20px;}
        .navbar {background:#166534;color:white;padding:20px;border-radius:15px;text-align:center;margin-bottom:30px;}
        .navbar h1 {margin:0;font-size:2.5rem;}
        .nav-menu {display:flex;justify-content:center;gap:30px;margin-top:15px;flex-wrap:wrap;}
        .nav-menu a {color:white;text-decoration:none;font-weight:600;font-size:1.2rem;cursor:pointer;}
        .nav-menu a:hover,.nav-menu a.active {text-decoration:underline;font-weight:800;}
        .section {display:none;padding:25px;background:white;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);margin-bottom:20px;}
        .section.active {display:block;}
        table {width:100%;border-collapse:collapse;margin:20px 0;}
        table th,table td {padding:12px;border-bottom:1px solid #ddd;text-align:left;}
        table th {background:#166534;color:white;}
        .grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin:20px 0;}
        .card {background:#f0fdf4;padding:20px;border-radius:12px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.1);border:1px solid #86efac;}
        .card h3 {margin:0;color:#166534;font-size:2.5rem;}
        .btn {background:#16a34a;color:white;border:none;padding:8px 16px;border-radius:8px;cursor:pointer;font-weight:600;font-size:0.95rem;}
        .btn:hover {background:#15803d;}
        .btn-red {background:#dc2626;color:white;}
        .btn-red:hover {background:#b91c1c;}
        .btn-finalitzar {background:#f59e0b;color:white;}
        .btn-finalitzar:hover {background:#d97706;}
        .msg {background:#d1fae5;color:#065f46;padding:15px;border-radius:10px;margin:20px 0;text-align:center;font-weight:600;}
        .err {background:#fee2e2;color:#991b1b;padding:15px;border-radius:10px;margin:20px 0;text-align:center;font-weight:600;}
        #map {height: 600px; border-radius: 12px; border: 1px solid #86efac; box-shadow: 0 4px 15px rgba(0,0,0,0.1);}
        .form-section {background:#f0fdf4;padding:25px;border-radius:12px;margin-bottom:30px;}
        .finalitzada {background:#fee2e2;color:#991b1b;}
        #back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #166534;
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            display: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            z-index: 1000;
            transition: opacity 0.3s;
        }
        #back-to-top:hover {background: #0f4722;}
    </style>
    <script>
        let map = null;

        function show(id) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            document.querySelectorAll('.nav-menu a').forEach(a => a.classList.remove('active'));
            document.querySelector(`a[onclick="show('${id}')"]`).classList.add('active');

            if (id === 'mapa' && !map) {
                setTimeout(() => {
                    map = L.map('map').setView([41.65, 0.88], 10);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(map);

                    const parceles = <?= $parceles_json ?>;

                    if (parceles.length === 0) {
                        L.marker([41.65, 0.88]).addTo(map)
                            .bindPopup('No hi ha parcel·les amb coordenades vàlides encara.');
                        return;
                    }

                    const bounds = L.latLngBounds();

                    parceles.forEach(p => {
                        // Marcador central (pin)
                        const marker = L.marker([p.lat, p.lon]).addTo(map);
                        marker.bindPopup(`
                            <b>${p.nom}</b><br>
                            Superfície: ${p.superficie} ha<br>
                            Coordenades: ${p.lat.toFixed(6)}, ${p.lon.toFixed(6)}<br>
                            <a href="#" onclick="map.setView([${p.lat}, ${p.lon}], 16); return false;">Centrar aquí</a>
                        `);

                        // Cercle vermell al voltant del punt central
                        const radiMetres = 300; // Ajusta aquest valor segons la mida mitjana de les teves parcel·les (en metres)

                        const cercle = L.circle([p.lat, p.lon], {
                            color: 'red',
                            fillColor: '#f03',
                            fillOpacity: 0.2,
                            radius: radiMetres,
                            weight: 2
                        }).addTo(map);

                        cercle.bindPopup(`
                            <b>${p.nom}</b><br>
                            Superfície: ${p.superficie} ha<br>
                            Centre: ${p.lat.toFixed(6)}, ${p.lon.toFixed(6)}
                        `);

                        bounds.extend(cercle.getBounds());
                    });

                    if (bounds.isValid()) {
                        map.fitBounds(bounds, { padding: [60, 60] });
                    }
                }, 300);
            }
        }

        window.onscroll = function() {
            const btn = document.getElementById("back-to-top");
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                btn.style.display = "block";
            } else {
                btn.style.display = "none";
            }
        };

        function scrollToTop() {
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
    </script>
</head>
<body>

<div class="container">
    <nav class="navbar">
        <h1>High Elo</h1>
        <div class="nav-menu">
            <a onclick="show('home')">Inici</a>
            <a onclick="show('parceles')">Parcel·les</a>
            <a onclick="show('cultius')">Cultius</a>
            <a onclick="show('personal')">Personal</a>
            <a onclick="show('sensors')">Sensors</a>
            <a onclick="show('productes')">Productes</a>
            <a onclick="show('sanitat')">Sanitat</a>
            <a onclick="show('mapa')">Mapa</a>
        </div>
    </nav>

    <?php if(isset($_SESSION['msg'])): ?>
        <div class="msg"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['err'])): ?>
        <div class="err"><?= $_SESSION['err']; unset($_SESSION['err']); ?></div>
    <?php endif; ?>

    <!-- INICI -->
    <section id="home" class="section active">
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
    </section>

    <!-- PARCEL·LES -->
    <section id="parceles" class="section">
        <h2>Parcel·les</h2>

        <div class="form-section">
            <h3>Afegir nova parcel·la</h3>
            <form method="post">
                <input type="hidden" name="nou_parcela" value="1">
                Nom: <input type="text" name="nom" required placeholder="ex: Finca la Boscana">  
                Superfície (ha): <input type="number" name="superficie" step="0.01" min="0.01" required placeholder="ex: 2.5">  
                Coordenades: <input type="text" name="coordenades" placeholder="ex: 41.654321, 0.880123">  
                Textura: <input type="text" name="textura" placeholder="ex: argilosa, sorrenca...">  
                <button type="submit" class="btn">Afegir parcel·la</button>
            </form>
        </div>

        <?php
        $parceles_list = $conn->query("SELECT id_parcela, nom, superficie, coordenades, textura FROM `Parcel·la` ORDER BY nom");
        if ($parceles_list && $parceles_list->num_rows > 0): ?>
            <table>
                <tr><th>ID</th><th>Nom</th><th>Superfície (ha)</th><th>Coordenades</th><th>Textura</th><th>Acció</th></tr>
                <?php while($p = $parceles_list->fetch_assoc()): ?>
                    <tr>
                        <td><?= $p['id_parcela'] ?></td>
                        <td><?= htmlspecialchars($p['nom']) ?></td>
                        <td><?= number_format($p['superficie'], 2) ?></td>
                        <td><?= htmlspecialchars($p['coordenades'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($p['textura'] ?? '-') ?></td>
                        <td><a href="?eliminar=1&tipus=parcela&id=<?= $p['id_parcela'] ?>" class="btn btn-red">Eliminar</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p style="text-align:center;color:#64748b;margin:40px 0;">Encara no hi ha parcel·les registrades.</p>
        <?php endif; ?>
    </section>

    <!-- CULTIUS -->
    <section id="cultius" class="section">
        <h2>Cultius</h2>

        <!-- Cultius -->
        <div class="form-section">
            <h3>Afegir nou cultiu</h3>
            <form method="post">
                <input type="hidden" name="nou_cultiu" value="1">
                Nom comú: <input type="text" name="nom_comu" required>  
                Nom científic: <input type="text" name="nom_cientific">  
                Cicle vegetatiu: <input type="text" name="cicle_vegetatiu">  
                Qualitats fruit: <textarea name="qualitats_fruit"></textarea>  
                <button type="submit" class="btn">Afegir cultiu</button>
            </form>
        </div>

        <table>
            <tr><th>ID</th><th>Nom comú</th><th>Nom científic</th><th>Cicle</th><th>Qualitats</th><th>Acció</th></tr>
            <?php while($c = $cultius_list->fetch_assoc()): ?>
                <tr>
                    <td><?= $c['id_cultiu'] ?></td>
                    <td><?= htmlspecialchars($c['nom_comu']) ?></td>
                    <td><?= htmlspecialchars($c['nom_cientific'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['cicle_vegetatiu'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['qualitats_fruit'] ?? '-') ?></td>
                    <td><a href="?eliminar=1&tipus=cultiu&id=<?= $c['id_cultiu'] ?>" class="btn btn-red">Eliminar</a></td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Sector Cultiu -->
        <div style="margin-top:40px;">
            <h2>Sector Cultiu</h2>
            <div class="form-section">
                <form method="post">
                    <input type="hidden" name="nou_sector_cultiu" value="1">
                    Parcel·la: 
                    <select name="id_parcela" required>
                        <option value="">Selecciona</option>
                        <?php $parceles_list->data_seek(0); while($p = $parceles_list->fetch_assoc()): ?>
                            <option value="<?= $p['id_parcela'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                        <?php endwhile; ?>
                    </select><br>
                    Data plantació: <input type="date" name="data_plantacio"><br>
                    Marc arbres: <input type="text" name="marc_plantacio_arbres" required placeholder="ex: 5x5 m"><br>
                    Marc files: <input type="text" name="marc_plantacio_files" required placeholder="ex: 4x4 m"><br>
                    Nº arbres: <input type="number" name="num_arbres" min="0" required><br>
                    Previsió producció (kg/ha): <input type="number" name="previsio_produccio" step="0.01" min="0" required><br>
                    <button type="submit" class="btn">Afegir sector</button>
                </form>
            </div>

            <table>
                <tr><th>ID Sector</th><th>Parcel·la</th><th>Data plantació</th><th>Marc arbres</th><th>Marc files</th><th>Nº arbres</th><th>Previsió</th><th>Acció</th></tr>
                <?php $sectors_cultiu_list->data_seek(0); while($s = $sectors_cultiu_list->fetch_assoc()): ?>
                    <tr>
                        <td><?= $s['id_sector'] ?></td>
                        <td><?= htmlspecialchars($s['nom_parcela']) ?></td>
                        <td><?= $s['data_plantacio'] ? date('d/m/Y', strtotime($s['data_plantacio'])) : '-' ?></td>
                        <td><?= htmlspecialchars($s['marc_plantacio_arbres']) ?></td>
                        <td><?= htmlspecialchars($s['marc_plantacio_files']) ?></td>
                        <td><?= $s['num_arbres'] ?></td>
                        <td><?= number_format($s['previsio_produccio'], 2) ?></td>
                        <td><a href="?eliminar=1&tipus=sector_cultiu&id=<?= $s['id_sector'] ?>" class="btn btn-red">Eliminar</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Fila Arbres -->
        <div style="margin-top:40px;">
            <h2>Fila Arbres</h2>
            <div class="form-section">
                <form method="post">
                    <input type="hidden" name="nou_fila_arbres" value="1">
                    Sector: 
                    <select name="id_sector" required>
                        <option value="">Selecciona</option>
                        <?php $sectors_cultiu_list->data_seek(0); while($s = $sectors_cultiu_list->fetch_assoc()): ?>
                            <option value="<?= $s['id_sector'] ?>"><?= htmlspecialchars($s['nom_parcela'] . ' (ID: ' . $s['id_sector'] . ')') ?></option>
                        <?php endwhile; ?>
                    </select><br>
                    Número fila: <input type="number" name="numero_fila" min="1" required><br>
                    Coordenades fila: <input type="text" name="coordenades_fila" required placeholder="ex: 41.123, 1.456"><br>
                    Notes: <textarea name="notes"></textarea><br>
                    <button type="submit" class="btn">Afegir fila</button>
                </form>
            </div>

            <table>
                <tr><th>ID Fila</th><th>Sector</th><th>Número fila</th><th>Coordenades</th><th>Notes</th><th>Acció</th></tr>
                <?php while($f = $files_arbres_list->fetch_assoc()): ?>
                    <tr>
                        <td><?= $f['id_fila'] ?></td>
                        <td><?= htmlspecialchars($f['nom_parcela']) ?></td>
                        <td><?= $f['numero_fila'] ?></td>
                        <td><?= htmlspecialchars($f['coordenades_fila'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($f['notes'] ?? '-') ?></td>
                        <td><a href="?eliminar=1&tipus=fila_arbres&id=<?= $f['id_fila'] ?>" class="btn btn-red">Eliminar</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </section>

    <!-- PERSONAL -->
    <section id="personal" class="section">
        <h2>Personal</h2>

        <!-- Treballadors -->
        <div class="form-section">
            <h3>Afegir treballador</h3>
            <form method="post">
                <input type="hidden" name="nou_treballador" value="1">
                NIF: <input type="text" name="nif" required>  
                Nom: <input type="text" name="nom" required>  
                Cognoms: <input type="text" name="cognoms" required>  
                Telèfon: <input type="text" name="telefon">  
                <button type="submit" class="btn">Afegir</button>
            </form>
        </div>

        <table>
            <tr><th>NIF</th><th>Nom complet</th><th>Telèfon</th><th>Acció</th></tr>
            <?php $treballadors_list->data_seek(0); while($t = $treballadors_list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($t['nif']) ?></td>
                    <td><?= htmlspecialchars($t['nom'] . ' ' . $t['cognoms']) ?></td>
                    <td><?= htmlspecialchars($t['telefon'] ?? '-') ?></td>
                    <td><a href="?eliminar=1&tipus=treballador&id=<?= $t['id_treballador'] ?>" class="btn btn-red">Eliminar</a></td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Contractes -->
        <div style="margin-top:40px;">
            <h2>Contractes</h2>
            <div class="form-section">
                <form method="post">
                    <input type="hidden" name="nou_contracte" value="1">
                    Treballador: 
                    <select name="id_treballador" required>
                        <option value="">Selecciona</option>
                        <?php $treballadors_list->data_seek(0); while($t = $treballadors_list->fetch_assoc()): ?>
                            <option value="<?= $t['id_treballador'] ?>">
                                <?= htmlspecialchars($t['nif'] . ' - ' . $t['nom'] . ' ' . $t['cognoms']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select><br>
                    Categoria: <input type="text" name="categoria_professional" required><br>
                    Salari anual: <input type="number" name="salari_brut_anual" step="0.01" required><br>
                    <button type="submit" class="btn">Guardar / Actualitzar</button>
                </form>
            </div>

            <table>
                <tr><th>Treballador</th><th>Categoria</th><th>Salari</th><th>Acció</th></tr>
                <?php while($c = $contractes_list->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nif'] . ' - ' . $c['nom'] . ' ' . $c['cognoms']) ?></td>
                        <td><?= htmlspecialchars($c['categoria_professional']) ?></td>
                        <td><?= number_format($c['salari_brut_anual'], 2) ?> €</td>
                        <td><a href="?eliminar=1&tipus=contracte&id=<?= $c['id_contracte'] ?>" class="btn btn-red">Eliminar</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Tasques -->
        <div style="margin-top:40px;">
            <h2>Tasques</h2>
            <div class="form-section">
                <form method="post">
                    <input type="hidden" name="nova_tasca" value="1">
                    Nom tasca: <input type="text" name="nom" required>  
                    Descripció: <textarea name="descripcio"></textarea>  
                    ID Parcel·la: <input type="number" name="id_parcela" required min="1">  
                    Sector (opcional): 
                    <select name="id_sector">
                        <option value="">Sense sector</option>
                        <?php $sectors_list->data_seek(0); while($s = $sectors_list->fetch_assoc()): ?>
                            <option value="<?= $s['id_sector'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
                        <?php endwhile; ?>
                    </select><br>
                    Hores estimades: <input type="number" name="hores_estimades" step="0.01" required>  
                    <button type="submit" class="btn">Afegir tasca</button>
                </form>
            </div>

            <table>
                <tr><th>ID</th><th>Nom</th><th>Descripció</th><th>ID Sector</th><th>ID Parcel·la</th><th>Hores</th><th>Estat</th><th>Accions</th></tr>
                <?php while($t = $tasques_list->fetch_assoc()): ?>
                    <tr class="<?= $t['finalitzada'] ? 'finalitzada' : '' ?>">
                        <td><?= $t['id_tasca'] ?></td>
                        <td><?= htmlspecialchars($t['nom']) ?></td>
                        <td><?= htmlspecialchars($t['descripcio'] ?? '-') ?></td>
                        <td><?= $t['id_sector'] ?: '-' ?></td>
                        <td><?= $t['id_parcela'] ?: '-' ?></td>
                        <td><?= $t['hores_estimades'] ?: '0' ?></td>
                        <td><?= $t['finalitzada'] ? 'Finalitzada' : 'Pendent' ?></td>
                        <td>
                            <?php if (!$t['finalitzada']): ?>
                                <a href="?finalitzar=1&id=<?= $t['id_tasca'] ?>" class="btn btn-finalitzar">Finalitzar</a>
                            <?php endif; ?>
                            <a href="?eliminar=1&tipus=tasca&id=<?= $t['id_tasca'] ?>" class="btn btn-red">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Assignacions -->
        <div style="margin-top:40px;">
            <h2>Assignacions</h2>
            <div class="form-section">
                <form method="post">
                    <input type="hidden" name="nou_assignacio" value="1">
                    Tasca: 
                    <select name="id_tasca" required>
                        <option value="">Selecciona tasca</option>
                        <?php $tasques_list->data_seek(0); while($t = $tasques_list->fetch_assoc()): ?>
                            <option value="<?= $t['id_tasca'] ?>">
                                <?= htmlspecialchars($t['nom'] . ' (ID: ' . $t['id_tasca'] . ')') ?>
                            </option>
                        <?php endwhile; ?>
                    </select><br>
                    Treballador: 
                    <select name="id_treballador" required>
                        <option value="">Selecciona</option>
                        <?php $treballadors_list->data_seek(0); while($t = $treballadors_list->fetch_assoc()): ?>
                            <option value="<?= $t['id_treballador'] ?>">
                                <?= htmlspecialchars($t['nif'] . ' - ' . $t['nom'] . ' ' . $t['cognoms']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select><br>
                    <label><input type="checkbox" name="es_cap_equip"> És cap d'equip</label><br>
                    <button type="submit" class="btn">Afegir assignació</button>
                </form>
            </div>

            <table>
                <tr><th>ID</th><th>Tasca</th><th>Treballador</th><th>Cap equip?</th><th>Acció</th></tr>
                <?php while($a = $assignacions_list->fetch_assoc()): ?>
                    <tr>
                        <td><?= $a['id_assignacio'] ?></td>
                        <td><?= $a['id_tasca'] ?></td>
                        <td><?= htmlspecialchars($a['nif'] . ' - ' . $a['nom'] . ' ' . $a['cognoms']) ?></td>
                        <td><?= $a['es_cap_equip'] ? 'Sí' : 'No' ?></td>
                        <td><a href="?eliminar=1&tipus=assignacio&id=<?= $a['id_assignacio'] ?>" class="btn btn-red">Eliminar</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
            </table>
        </div>

        <!-- Absències -->
        <div style="margin-top:40px;">
            <h2>Absències</h2>
            <div class="form-section">
                <form method="post">
                    <input type="hidden" name="nova_absencia" value="1">
                    Treballador: 
                    <select name="id_treballador" required>
                        <option value="">Selecciona</option>
                        <?php $treballadors_list->data_seek(0); while($t = $treballadors_list->fetch_assoc()): ?>
                            <option value="<?= $t['id_treballador'] ?>">
                                <?= htmlspecialchars($t['nif'] . ' - ' . $t['nom'] . ' ' . $t['cognoms']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select><br>
                    Data inici: <input type="date" name="data_inici" required>
                    Data fi: <input type="date" name="data_fi">
                    Motiu: <input type="text" name="motiu" placeholder="ex: Baixa mèdica, Vacances..."><br>
                    <button type="submit" class="btn">Registrar absència</button>
                </form>
            </div>

            <table>
                <tr><th>Data Inici</th><th>Data Fi</th><th>Treballador</th><th>Motiu</th><th>Estat</th><th>Acció</th></tr>
                <?php while($a = $absencies_list->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($a['data_inici'])) ?></td>
                        <td><?= $a['data_fi'] ? date('d/m/Y', strtotime($a['data_fi'])) : '-' ?></td>
                        <td><?= htmlspecialchars($a['nom'] . ' ' . $a['cognoms']) ?></td>
                        <td><?= htmlspecialchars($a['motiu'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($a['estat']) ?></td>
                        <td><a href="?eliminar=1&tipus=absencia&id=<?= $a['id_absencia'] ?>" class="btn btn-red">Eliminar</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </section>

    <!-- SENSORS + ALERTA -->
    <section id="sensors" class="section">
        <h2>Sensors i Alertes</h2>
        <div class="grid">
            <!-- Sensors -->
            <div>
                <h3>Sensors</h3>
                <div class="form-section">
                    <form method="post">
                        <input type="hidden" name="nou_sensor" value="1">
                        Nom: <input type="text" name="nom" required placeholder="Sensor Humitat 1">
                        Tipus: <input type="text" name="tipus" required placeholder="Humitat, Temperatura...">
                        Loc: <input type="text" name="localitzacio" placeholder="Parcel·la 1">
                        <button type="submit" class="btn">Afegir Sensor</button>
                    </form>
                </div>
                <table>
                    <tr><th>Nom</th><th>Tipus</th><th>Estat</th><th>Acció</th></tr>
                    <?php while($s = $sensors_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['nom']) ?></td>
                            <td><?= htmlspecialchars($s['tipus']) ?></td>
                            <td><?= htmlspecialchars($s['estat']) ?></td>
                            <td><a href="?eliminar=1&tipus=sensor&id=<?= $s['id_sensor'] ?>" class="btn btn-red">X</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- Alertes -->
            <div>
                <h3>Alertes Actives</h3>
                <div class="form-section">
                    <form method="post">
                        <input type="hidden" name="nova_alerta" value="1">
                        Sensor: 
                        <select name="id_sensor" required>
                            <option value="">Selecciona</option>
                            <?php $sensors_list->data_seek(0); while($s = $sensors_list->fetch_assoc()): ?>
                                <option value="<?= $s['id_sensor'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        Missatge: <input type="text" name="missatge" required>
                        Nivell: 
                        <select name="nivell">
                            <option value="Baix">Baix</option>
                            <option value="Mitjà">Mitjà</option>
                            <option value="Alt">Alt</option>
                        </select>
                        <button type="submit" class="btn">Crear Alerta</button>
                    </form>
                </div>
                <table>
                    <tr><th>Data</th><th>Sensor</th><th>Missatge</th><th>Niv</th><th>Acció</th></tr>
                    <?php while($a = $alertes_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($a['data'])) ?></td>
                            <td><?= htmlspecialchars($a['nom_sensor'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($a['missatge']) ?></td>
                            <td style="color:<?= $a['nivell']=='Alt'?'red':($a['nivell']=='Mitjà'?'orange':'green') ?>">
                                <?= htmlspecialchars($a['nivell']) ?>
                            </td>
                            <td><a href="?eliminar=1&tipus=alerta&id=<?= $a['id_alerta'] ?>" class="btn btn-red">X</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </section>

    <!-- PRODUCTES + ESTOC -->
    <section id="productes" class="section">
        <h2>Productes i Estoc</h2>
        <div class="form-section">
            <h3>Nou Producte</h3>
            <form method="post">
                <input type="hidden" name="nou_producte" value="1">
                Nom: <input type="text" name="nom" required>
                Desc: <input type="text" name="descripcio">
                Preu (€): <input type="number" name="preu_unitari" step="0.01">
                Estoc: <input type="number" name="stock_actual" step="0.01" required>
                Unitat: <input type="text" name="unitat_mesura" placeholder="kg, L, unitats...">
                <button type="submit" class="btn">Afegir Producte</button>
            </form>
        </div>
        <table>
            <tr><th>Producte</th><th>Descripció</th><th>Preu</th><th>Estoc</th><th>Acció</th></tr>
            <?php while($p = $productes_list->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nom']) ?></td>
                    <td><?= htmlspecialchars($p['descripcio'] ?? '-') ?></td>
                    <td><?= number_format($p['preu_unitari'], 2) ?> €</td>
                    <td style="font-weight:bold;color:<?= $p['stock_actual']<10 ? 'red':'green' ?>">
                        <?= number_format($p['stock_actual'], 2) ?> <?= htmlspecialchars($p['unitat_mesura']) ?>
                    </td>
                    <td><a href="?eliminar=1&tipus=producte&id=<?= $p['id_producte'] ?>" class="btn btn-red">Eliminar</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>

    <!-- SANITAT -->
    <section id="sanitat" class="section">
        <h2>Sanitat Vegetal</h2>
        
        <div class="grid">
            <!-- Monitoratge Plagues -->
            <div>
                <h3>Monitoratge Plagues</h3>
                <div class="form-section">
                    <form method="post">
                        <input type="hidden" name="nova_plaga" value="1">
                        Parcel·la: 
                        <select name="id_parcela" required>
                            <option value="">Selecciona</option>
                            <?php $parceles_list->data_seek(0); while($p = $parceles_list->fetch_assoc()): ?>
                                <option value="<?= $p['id_parcela'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        Plaga: <input type="text" name="plaga" required>
                        Severitat: <select name="severitat"><option>Baixa</option><option>Mitjana</option><option>Alta</option></select>
                        <button type="submit" class="btn">Registrar</button>
                    </form>
                </div>
                <table>
                    <tr><th>Data</th><th>Parcel·la</th><th>Plaga</th><th>Sev.</th><th>Acció</th></tr>
                    <?php while($m = $plagues_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d/m', strtotime($m['data'])) ?></td>
                            <td><?= htmlspecialchars($m['nom_parcela']) ?></td>
                            <td><?= htmlspecialchars($m['plaga']) ?></td>
                            <td><?= htmlspecialchars($m['severitat']) ?></td>
                            <td><a href="?eliminar=1&tipus=plaga&id=<?= $m['id_monitoratge'] ?>" class="btn btn-red">X</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- Tractaments -->
            <div>
                <h3>Aplicació Tractament</h3>
                <div class="form-section">
                    <form method="post">
                        <input type="hidden" name="nou_tractament" value="1">
                        Parcel·la: 
                        <select name="id_parcela" required>
                            <option value="">Selecciona</option>
                            <?php $parceles_list->data_seek(0); while($p = $parceles_list->fetch_assoc()): ?>
                                <option value="<?= $p['id_parcela'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        Producte: <input type="text" name="producte_utilitzat" required>
                        Quantitat: <input type="number" name="quantitat" step="0.01">
                        <button type="submit" class="btn">Afegir</button>
                    </form>
                </div>
                <table>
                    <tr><th>Data</th><th>Parcel·la</th><th>Producte</th><th>Qt.</th><th>Acció</th></tr>
                    <?php while($t = $tractaments_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d/m', strtotime($t['data'])) ?></td>
                            <td><?= htmlspecialchars($t['nom_parcela']) ?></td>
                            <td><?= htmlspecialchars($t['producte_utilitzat']) ?></td>
                            <td><?= $t['quantitat'] ?></td>
                            <td><a href="?eliminar=1&tipus=tractament&id=<?= $t['id_tractament'] ?>" class="btn btn-red">X</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>

        <div class="grid">
            <!-- Analisi Nutricional -->
            <div>
                <h3>Anàlisi Nutricional</h3>
                <div class="form-section">
                    <form method="post">
                        <input type="hidden" name="nova_analisi" value="1">
                        Parcel·la: 
                        <select name="id_parcela" required>
                            <option value="">Selecciona</option>
                            <?php $parceles_list->data_seek(0); while($p = $parceles_list->fetch_assoc()): ?>
                                <option value="<?= $p['id_parcela'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        Tipus: <input type="text" name="tipus_analisi" placeholder="Sòl, Fulla..." required>
                        Resultat: <textarea name="resultat_text" placeholder="Resum resultats..."></textarea>
                        <button type="submit" class="btn">Afegir</button>
                    </form>
                </div>
                <table>
                    <tr><th>Data</th><th>Parcel·la</th><th>Tipus</th><th>Resultat</th><th>Acció</th></tr>
                    <?php while($a = $analisi_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d/m', strtotime($a['data'])) ?></td>
                            <td><?= htmlspecialchars($a['nom_parcela']) ?></td>
                            <td><?= htmlspecialchars($a['tipus_analisi']) ?></td>
                            <td><?= htmlspecialchars($a['resultat_text'] ?? '-') ?></td>
                            <td><a href="?eliminar=1&tipus=analisi&id=<?= $a['id_analisi'] ?>" class="btn btn-red">X</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- Calendari -->
            <div>
                <h3>Calendari Fitosanitari</h3>
                <div class="form-section">
                    <form method="post">
                        <input type="hidden" name="nou_esdeveniment" value="1">
                        Parcel·la: 
                        <select name="id_parcela" required>
                            <option value="">Selecciona</option>
                            <?php $parceles_list->data_seek(0); while($p = $parceles_list->fetch_assoc()): ?>
                                <option value="<?= $p['id_parcela'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        Data: <input type="date" name="data_prevista" required>
                        Acció: <input type="text" name="accio" required>
                        <button type="submit" class="btn">Afegir</button>
                    </form>
                </div>
                <table>
                    <tr><th>Data</th><th>Parcel·la</th><th>Acció</th><th>Acció</th></tr>
                    <?php while($c = $calendari_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d/m', strtotime($c['data_prevista'])) ?></td>
                            <td><?= htmlspecialchars($c['nom_parcela']) ?></td>
                            <td><?= htmlspecialchars($c['accio']) ?></td>
                            <td><a href="?eliminar=1&tipus=calendari&id=<?= $c['id_esdeveniment'] ?>" class="btn btn-red">X</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </section>

    <!-- MAPA -->
    <section id="mapa" class="section">
        <h2>Mapa de les parcel·les</h2>
        <div id="map"></div>
    </section>
</div>

<!-- Botó Tornar a dalt -->
<button id="back-to-top" onclick="scrollToTop()">↑</button>

</body>
</html>
