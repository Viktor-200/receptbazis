<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_nev  = "receptbazis";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_nev);

if ($conn->connect_error) {
    die("Adatbázis kapcsolódási hiba: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
