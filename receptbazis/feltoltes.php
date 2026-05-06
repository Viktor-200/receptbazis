<?php
session_start();
include 'adatkapcsolat.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uzenet = "";

function kepFeltoltes($fajl, $elotag) {
    if (empty($fajl['name'])) return "";

    $mappa = "uploads/";
    if (!is_dir($mappa)) mkdir($mappa, 0777, true);

    $utvonal = $mappa . time() . "_" . $elotag . "_" . basename($fajl['name']);
    move_uploaded_file($fajl['tmp_name'], $utvonal);
    return $utvonal;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $indexkep  = kepFeltoltes($_FILES["indexkep"],  "index");
    $receptkep = kepFeltoltes($_FILES["receptkep"], "recept");

    $stmt = $conn->prepare("
        INSERT INTO receptek (felhasznalo_id, cim, leiras, hozzavalok, elkeszitesi_ido, kategoria, indexkep, receptkep, youtube_link)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $uid  = $_SESSION['user_id'];
    $cim  = trim($_POST['cim']);
    $leiras     = trim($_POST['leiras']);
    $hozzavalok = trim($_POST['hozzavalok']);
    $ido        = intval($_POST['elkeszitesi_ido']);
    $kat        = trim($_POST['kategoria']);
    $yt         = trim($_POST['youtube_link']);

    $stmt->bind_param("isssissss", $uid, $cim, $leiras, $hozzavalok, $ido, $kat, $indexkep, $receptkep, $yt);

    if ($stmt->execute()) {
        $uzenet = "<p style='color:#4CAF50;'>Recept sikeresen feltöltve!</p>";
    } else {
        $uzenet = "<p style='color:#f44336;'>Hiba történt a feltöltés során.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recept feltöltése</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .feltolto-doboz {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            color: white;
        }
        .mezo-csoport {
            margin-bottom: 15px;
            text-align: left;
        }
        .mezo-csoport label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .mezo-csoport input,
        .mezo-csoport textarea,
        .mezo-csoport select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: none;
            box-sizing: border-box;
            font-family: 'Quicksand', sans-serif;
        }
        .gomb-sor {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }
    </style>
</head>
<body class="sotettema" style="background-color:#17161B;">

<div class="feltolto-doboz">
    <h2>Új recept megosztása</h2>
    <?= $uzenet ?>

    <form action="feltoltes.php" method="POST" enctype="multipart/form-data">

        <div class="mezo-csoport">
            <label>Recept címe:</label>
            <input type="text" name="cim" required placeholder="Pl. Tejszínes csirkemell">
        </div>

        <div class="mezo-csoport">
            <label>Kategória:</label>
            <select name="kategoria" required>
                <option value="Reggeli">Reggeli</option>
                <option value="Leves">Leves</option>
                <option value="Főfogás">Főfogás</option>
                <option value="Saláta">Vegán / Saláta</option>
                <option value="Desszert">Desszert</option>
                <option value="Ital">Ital</option>
            </select>
        </div>

        <div class="mezo-csoport">
            <label>Elkészítési idő (perc):</label>
            <input type="number" name="elkeszitesi_ido" placeholder="60">
        </div>

        <div class="mezo-csoport">
            <label>Hozzávalók:</label>
            <textarea name="hozzavalok" rows="4" required placeholder="Sorold fel a hozzávalókat..."></textarea>
        </div>

        <div class="mezo-csoport">
            <label>Leírás / Elkészítés:</label>
            <textarea name="leiras" rows="6" required placeholder="Írd le a lépéseket..."></textarea>
        </div>

        <div class="mezo-csoport">
            <label>Indexkép (a főoldalon jelenik meg):</label>
            <input type="file" name="indexkep" accept="image/*">
        </div>

        <div class="mezo-csoport">
            <label>Recept főkép (a recept oldalán jelenik meg):</label>
            <input type="file" name="receptkep" accept="image/*">
        </div>

        <div class="mezo-csoport">
            <label>YouTube videó link:</label>
            <input type="url" name="youtube_link" placeholder="https://youtube.com/...">
        </div>

        <div class="gomb-sor">
            <button type="submit" class="gomb">Recept feltöltése</button>
            <a href="home.php" class="gomb" style="background:#6c757d; text-decoration:none; display:inline-block;">Vissza a főoldalra</a>
        </div>

    </form>
</div>

</body>
</html>
