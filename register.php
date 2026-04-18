<?php
require_once 'config/db.php';

if (isset($_SESSION['usuari_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!empty($nom) && !empty($email) && !empty($password) && !empty($password_confirm)) {
        if ($password !== $password_confirm) {
            $error = "Les contrasenyes no coincideixen.";
        } else {
            // Check if email already exists
            $stmt_check = $conn->prepare("SELECT id_usuari FROM Usuaris WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();
            
            if ($stmt_check->num_rows > 0) {
                $error = "Aquest correu ja està registrat.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_insert = $conn->prepare("INSERT INTO Usuaris (nom, email, password) VALUES (?, ?, ?)");
                $stmt_insert->bind_param("sss", $nom, $email, $hashed_password);
                
                if ($stmt_insert->execute()) {
                    $success = "Compte creat correctament. Ara pots iniciar sessió.";
                } else {
                    $error = "Error al registrar l'usuari. Torna-ho a intentar.";
                }
                $stmt_insert->close();
            }
            $stmt_check->close();
        }
    } else {
        $error = "Si us plau, omple tots els camps.";
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registre - High Elo</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page-body">
    <div class="login-container">
        <h2><i class="fa-solid fa-user-plus"></i> Registre</h2>
        <p style="color: var(--text-muted); margin-bottom: 20px;">Crea un nou compte d'usuari</p>
        
        <?php if ($error): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-msg"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="nom">Nom complet</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div class="form-group">
                <label for="email">Correu electrònic</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contrasenya</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirma Contrasenya</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit" class="btn btn-login"><i class="fa-solid fa-check"></i> Registrar-se</button>
        </form>
        
        <div class="register-link">
            Ja tens compte? <a href="login.php">Inicia sessió aquí</a>
        </div>
    </div>
</body>
</html>
