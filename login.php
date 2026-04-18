<?php
require_once 'config/db.php';

if (isset($_SESSION['usuari_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id_usuari, nom, password FROM Usuaris WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['usuari_id'] = $user['id_usuari'];
                $_SESSION['usuari_nom'] = $user['nom'];
                header("Location: index.php");
                exit;
            } else {
                $error = "Contrasenya incorrecta.";
            }
        } else {
            $error = "No s'ha trobat cap usuari amb aquest correu.";
        }
        $stmt->close();
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
    <title>Inicia Sessió - High Elo</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page-body">
    <div class="login-container">
        <h2><i class="fa-solid fa-seedling"></i> High Elo</h2>
        <p style="color: var(--text-muted); margin-bottom: 20px;">Inicia sessió al panell d'administració</p>
        
        <?php if ($error): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Correu electrònic</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contrasenya</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-login"><i class="fa-solid fa-right-to-bracket"></i> Entrar</button>
        </form>
        
        <div class="register-link">
            No tens compte? <a href="register.php">Registra't aquí</a>
        </div>
    </div>
</body>
</html>
