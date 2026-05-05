<?php
include 'adatkapcsolat.php';
session_start();

$hiba  = "";
$siker = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nev    = $_POST['felhasznalonev'];
    $email  = $_POST['email'];
    $jelszo = $_POST['jelszo'];
    $jelszo2 = $_POST['jelszo_megerosit'];

    if ($jelszo !== $jelszo2) {
        $hiba = "A két jelszó nem egyezik meg!";
    } else {

        $stmt = $conn->prepare("SELECT id FROM felhasznalok WHERE felhasznalonev = ? OR email = ?");
        $stmt->bind_param("ss", $nev, $email);
        $stmt->execute();
        $foglalt = $stmt->get_result()->num_rows > 0;

        if ($foglalt) {
            $hiba = "Ez a felhasználónév vagy e-mail már foglalt!";
        } else {
            $hash = password_hash($jelszo, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO felhasznalok (felhasznalonev, email, jelszo) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nev, $email, $hash);
            if ($stmt->execute()) {
                $siker = "Sikeres regisztráció! Most már bejelentkezhetsz.";
            } else {
                $hiba = "Hiba történt a mentés során!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció – Receptbázis</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="reg-doboz sotettema">
    <h2 style="margin-bottom:20px;">Regisztráció</h2>

    <?php if ($hiba): ?>
        <p style="color:#ff5e5e; font-weight:bold;"><?= htmlspecialchars($hiba) ?></p>
    <?php endif; ?>
    <?php if ($siker): ?>
        <p style="color:#4CAF50; font-weight:bold;"><?= htmlspecialchars($siker) ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <input type="text"     name="felhasznalonev"  placeholder="Felhasználónév" required>
        <input type="email"    name="email"            placeholder="E-mail cím"     required>
        <input type="password" name="jelszo"           placeholder="Jelszó"         required>
        <input type="password" name="jelszo_megerosit" placeholder="Jelszó újra"    required>
        <button type="submit" class="gomb" style="width:100%; margin-top:10px;">Regisztráció</button>
    </form>

    <p style="font-size:0.9em; margin-top:20px;">
        Már van fiókod?
        <a href="login.php" style="color:#5e9cff; text-decoration:none;">Jelentkezz be!</a>
    </p>
    <p style="font-size:0.8em; margin-top:10px;">
        <a href="home.php" style="color:#ccc; text-decoration:none;">Vissza a főoldalra</a>
    </p>
</div>

    <style>
        body {
            background-color: #17161B;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .reg-doboz {
            width: 100%;
            max-width: 350px;
            text-align: center;
        }
        .reg-doboz input {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1.5px solid #2a2930;
            box-sizing: border-box;
            font-family: 'Quicksand', sans-serif;
            font-size: 15px;
            font-weight: 600;
            background-color: #2a2930;
            color: #fff;
            outline: none;
            -webkit-appearance: none;
        }
        .reg-doboz input:focus {
            border-color: #5e9cff;
        }
        .reg-doboz input::placeholder {
            color: #666;
        }
        .gomb {
            -webkit-appearance: none;
            appearance: none;
        }
    </style>

</body>
</html>
