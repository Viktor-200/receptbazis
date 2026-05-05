<?php
include 'adatkapcsolat.php';
session_start();

$hiba = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nev    = $_POST['felhasznalonev'];
    $jelszo = $_POST['jelszo'];

    $stmt = $conn->prepare("SELECT id, felhasznalonev, jelszo FROM felhasznalok WHERE felhasznalonev = ?");
    $stmt->bind_param("s", $nev);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($jelszo, $user['jelszo'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['felhasznalonev'];
        header("Location: home.php");
        exit();
    } else {
        $hiba = $user ? "Hibás jelszó!" : "Nincs ilyen felhasználó!";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés – Receptbázis</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-doboz sotettema">
    <h2 style="margin-bottom:20px;">Bejelentkezés</h2>

    <?php if ($hiba): ?>
        <p style="color:#ff5e5e; font-weight:bold;"><?= htmlspecialchars($hiba) ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="text"     name="felhasznalonev" placeholder="Felhasználónév" required>
        <input type="password" name="jelszo"         placeholder="Jelszó"         required>
        <button type="submit" class="gomb" style="width:100%; margin-top:10px;">Belépés</button>
    </form>

    <p style="font-size:0.9em; margin-top:20px;">
        Még nincs fiókod?
        <a href="register.php" style="color:#5e9cff; text-decoration:none;">Regisztrálj!</a>
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
        .login-doboz {
            width: 100%;
            max-width: 350px;
            text-align: center;
        }
        .login-doboz input {
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
        .login-doboz input:focus {
            border-color: #5e9cff;
        }
        .login-doboz input::placeholder {
            color: #666;
        }
        .gomb {
            -webkit-appearance: none;
            appearance: none;
        }
    </style>

</body>
</html>
