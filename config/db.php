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
