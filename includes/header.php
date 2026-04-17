<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Elo Administració</title>
    <!-- FontAwesome per les icones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS extern -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <!-- Icona d'arbre (leaf) per la temàtica agrària, al costat de High Elo -->
            <h1><i class="fa-solid fa-leaf"></i> <span>High Elo</span></h1>
        </div>
        <ul class="sidebar-menu">
            <li><a href="?p=home" class="<?= $p === 'home' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a></li>
            <li><a href="?p=parceles" class="<?= $p === 'parceles' ? 'active' : '' ?>"><i class="fa-solid fa-map"></i> <span>Parcel·les</span></a></li>
            <li><a href="?p=cultius" class="<?= $p === 'cultius' ? 'active' : '' ?>"><i class="fa-solid fa-seedling"></i> <span>Cultius i Sectors</span></a></li>
            <li><a href="?p=personal" class="<?= $p === 'personal' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> <span>Personal</span></a></li>
            <li><a href="?p=productes" class="<?= $p === 'productes' ? 'active' : '' ?>"><i class="fa-solid fa-box-open"></i> <span>Productes i Estoc</span></a></li>
            <li><a href="?p=monitoratge_plagues" class="<?= $p === 'monitoratge_plagues' ? 'active' : '' ?>"><i class="fa-solid fa-bug"></i> <span>Tractaments</span></a></li>
            <li><a href="?p=sensors" class="<?= $p === 'sensors' ? 'active' : '' ?>"><i class="fa-solid fa-tower-broadcast"></i> <span>Sensors i Alertes</span></a></li>
            <li><a href="?p=collites" class="<?= $p === 'collites' ? 'active' : '' ?>"><i class="fa-solid fa-wheat-awn"></i> <span>Collites</span></a></li>
            <li><a href="?p=lots" class="<?= $p === 'lots' ? 'active' : '' ?>"><i class="fa-solid fa-barcode"></i> <span>Traçabilitat</span></a></li>
            <li><a href="?p=dashboard" class="<?= $p === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-pie"></i> <span>Estadístiques</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        
        <?php if ($msg): ?>
            <div class="msg"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if ($err): ?>
            <div class="err"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($err) ?></div>
        <?php endif; ?>
