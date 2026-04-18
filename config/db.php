<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexió a la base de dades
$conn = new mysqli("localhost", "root", "", "Projecte");
if ($conn->connect_error) {
    die("Error de connexió: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Crear taula d'usuaris si no existeix
$sql_usuaris = "CREATE TABLE IF NOT EXISTS Usuaris (
    id_usuari INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    data_registre TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_usuaris);
