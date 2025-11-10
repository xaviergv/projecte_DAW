<?php
$servername = "192.168.20.55";  // ← IP DE LA VM
$username   = "web_user";  // ← l'usuari que vas crear
$password   = "pirineus";
$dbname     = "Projecte";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexió fallida: " . $conn->connect_error);
}

echo "Connectat correctament a la base de dades de la VM! IP: 192.168.20.55";
?>