<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'adatkapcsolat.php';

$uid = $_SESSION['user_id'];
$uzenetek = [];

$stmt = $conn->prepare("SELECT felhasznalonev, email, profilkep FROM felhasznalok WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['felhasznalonev'])) {
        $ujNev = trim($_POST['felhasznalonev']);
        if ($ujNev !== $user['felhasznalonev']) {
            $stmt = $conn->prepare("SELECT id FROM felhasznalok WHERE felhasznalonev = ? AND id != ?");
            $stmt->bind_param("si", $ujNev, $uid);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $uzenetek[] = ['hiba', 'Ez a felhasználónév már foglalt.'];
            } else {
                $conn->prepare("UPDATE felhasznalok SET felhasznalonev = ? WHERE id = ?")->bind_param("si", $ujNev, $uid);
                $stmt2 = $conn->prepare("UPDATE felhasznalok SET felhasznalonev = ? WHERE id = ?");
                $stmt2->bind_param("si", $ujNev, $uid);
                $stmt2->execute();
                $_SESSION['user_name'] = $ujNev;
                $user['felhasznalonev'] = $ujNev;
                $uzenetek[] = ['siker', 'Felhasználónév sikeresen frissítve.'];
            }
        }
    }

    if (!empty($_POST['email'])) {
        $ujEmail = trim($_POST['email']);
        if ($ujEmail !== $user['email']) {
            $stmt = $conn->prepare("SELECT id FROM felhasznalok WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $ujEmail, $uid);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $uzenetek[] = ['hiba', 'Ez az e-mail cím már foglalt.'];
            } else {
                $stmt2 = $conn->prepare("UPDATE felhasznalok SET email = ? WHERE id = ?");
                $stmt2->bind_param("si", $ujEmail, $uid);
                $stmt2->execute();
                $user['email'] = $ujEmail;
                $uzenetek[] = ['siker', 'E-mail cím sikeresen frissítve.'];
            }
        }
    }

    if (!empty($_POST['jelszo_regi'])) {
        $stmt = $conn->prepare("SELECT jelszo FROM felhasznalok WHERE id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $meglevoHash = $stmt->get_result()->fetch_assoc()['jelszo'];

        if (!password_verify($_POST['jelszo_regi'], $meglevoHash)) {
            $uzenetek[] = ['hiba', 'A jelenlegi jelszó helytelen.'];
        } elseif (strlen($_POST['jelszo_uj'] ?? '') < 6) {
            $uzenetek[] = ['hiba', 'Az új jelszó legalább 6 karakter legyen.'];
        } elseif ($_POST['jelszo_uj'] !== ($_POST['jelszo_uj2'] ?? '')) {
            $uzenetek[] = ['hiba', 'A két új jelszó nem egyezik.'];
        } else {
            $ujHash = password_hash($_POST['jelszo_uj'], PASSWORD_DEFAULT);
            $stmt2  = $conn->prepare("UPDATE felhasznalok SET jelszo = ? WHERE id = ?");
            $stmt2->bind_param("si", $ujHash, $uid);
            $stmt2->execute();
            $uzenetek[] = ['siker', 'Jelszó sikeresen megváltoztatva.'];
        }
    }

    if (!empty($_FILES['profilkep']['name'])) {
        $fajl = $_FILES['profilkep'];
        $ext  = strtolower(pathinfo($fajl['name'], PATHINFO_EXTENSION));
        $engedelyezett = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $engedelyezett) || $fajl['size'] > 3 * 1024 * 1024) {
            $uzenetek[] = ['hiba', 'Hiba a feltöltés során. Ellenőrizd a fájl típusát (max 3 MB, jpg/png/gif/webp).'];
        } else {
            $mappa = 'uploads/profilkepek/';
            if (!is_dir($mappa)) mkdir($mappa, 0777, true);

            if (!empty($user['profilkep']) && file_exists($user['profilkep'])) {
                unlink($user['profilkep']);
            }

            $ujUtvonal = $mappa . $uid . '_' . time() . '.' . $ext;
            move_uploaded_file($fajl['tmp_name'], $ujUtvonal);

            $stmt2 = $conn->prepare("UPDATE felhasznalok SET profilkep = ? WHERE id = ?");
            $stmt2->bind_param("si", $ujUtvonal, $uid);
            $stmt2->execute();
            $user['profilkep'] = $ujUtvonal;
            $uzenetek[] = ['siker', 'Profilkép sikeresen frissítve.'];
        }
    }

    $_SESSION['beallitas_uzenetek'] = $uzenetek;
    header("Location: beallitasok.php");
    exit();
}

if (isset($_SESSION['beallitas_uzenetek'])) {
    $uzenetek = $_SESSION['beallitas_uzenetek'];
    unset($_SESSION['beallitas_uzenetek']);
}

$profilkepUrl = (!empty($user['profilkep']) && file_exists($user['profilkep']))
    ? $user['profilkep']
    : 'img/alap.png';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beállítások – Receptbázis</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>
</head>
<body class="sotettema">

<?php include 'header.php'; ?>
<?php include 'footer.php'; ?>

<div class="beallitasok-oldal">

    <a href="profil.php" class="vissza-link">
        <span class="material-symbols-outlined" style="font-size:17px;">arrow_back</span>
        Vissza a profilhoz
    </a>

    <div class="oldal-cim">
        <span class="material-symbols-outlined" style="font-size:26px; color:#5e9cff;">settings</span>
        <h1>Fiókbeállítások</h1>
    </div>

    <?php if (!empty($uzenetek)): ?>
        <div class="visszajelzok">
            <?php foreach ($uzenetek as [$tipus, $szoveg]): ?>
                <div class="visszajelzo <?= $tipus ?>">
                    <span class="material-symbols-outlined">
                        <?= $tipus === 'siker' ? 'check_circle' : 'error' ?>
                    </span>
                    <?= htmlspecialchars($szoveg) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="kartya">
        <p class="kartya-fejlec">
            <span class="material-symbols-outlined">face</span>
            Profilkép
        </p>
        <form method="POST" action="beallitasok.php" enctype="multipart/form-data">
            <div class="profilkep-blokk">
                <div class="profilkep-kor" onclick="document.getElementById('profilkep-input').click()">
                    <img src="<?= htmlspecialchars($profilkepUrl) ?>" alt="Profilkép" id="profilkep-elonezet">
                    <div class="profilkep-hover-reteg">
                        <span class="material-symbols-outlined">photo_camera</span>
                        <span>Csere</span>
                    </div>
                </div>
                <span class="fajlnev-szoveg" id="fajlnev-szoveg">Kattints a képre a cseréhez</span>
            </div>
            <input type="file" name="profilkep" id="profilkep-input" accept="image/*">
            <button type="submit" class="gomb mentes-gomb">Profilkép mentése</button>
        </form>
    </div>

    <div class="kartya">
        <p class="kartya-fejlec">
            <span class="material-symbols-outlined">badge</span>
            Felhasználónév
        </p>
        <form method="POST" action="beallitasok.php">
            <div class="mezo">
                <label>Új felhasználónév</label>
                <input type="text" name="felhasznalonev"
                       value="<?= htmlspecialchars($user['felhasznalonev']) ?>"
                       required maxlength="50">
            </div>
            <button type="submit" class="gomb mentes-gomb">Mentés</button>
        </form>
    </div>

    <div class="kartya">
        <p class="kartya-fejlec">
            <span class="material-symbols-outlined">mail</span>
            E-mail cím
        </p>
        <form method="POST" action="beallitasok.php">
            <div class="mezo">
                <label>Új e-mail cím</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($user['email']) ?>"
                       required maxlength="100">
            </div>
            <button type="submit" class="gomb mentes-gomb">Mentés</button>
        </form>
    </div>

    <div class="kartya">
        <p class="kartya-fejlec">
            <span class="material-symbols-outlined">lock</span>
            Jelszó módosítása
        </p>
        <form method="POST" action="beallitasok.php">
            <div class="mezo">
                <label>Jelenlegi jelszó</label>
                <input type="password" name="jelszo_regi" required placeholder="••••••••">
            </div>
            <div class="mezo">
                <label>Új jelszó</label>
                <input type="password" name="jelszo_uj" required placeholder="••••••••" minlength="6">
            </div>
            <div class="mezo">
                <label>Új jelszó mégegyszer</label>
                <input type="password" name="jelszo_uj2" required placeholder="••••••••" minlength="6">
            </div>
            <button type="submit" class="gomb mentes-gomb">Jelszó módosítása</button>
        </form>
    </div>

</div>

<script>
document.getElementById('profilkep-input').addEventListener('change', function () {
    const f = this.files[0];
    if (!f) return;
    document.getElementById('fajlnev-szoveg').textContent = f.name;
    const reader = new FileReader();
    reader.onload = e => document.getElementById('profilkep-elonezet').src = e.target.result;
    reader.readAsDataURL(f);
});
</script>
    <style>
        .beallitasok-oldal {
            max-width: 600px;
            margin: 130px auto 80px auto;
            padding: 0 20px;
        }
        .oldal-cim {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }
        .oldal-cim h1 { font-size: 22px; font-weight: 700; margin: 0; }
        .profilkep-blokk {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            margin-bottom: 8px;
        }
        .profilkep-kor {
            position: relative;
            width: 100px;
            height: 100px;
            cursor: pointer;
        }
        .profilkep-kor img {
            width: 100px; height: 100px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            transition: filter 0.2s;
        }
        .profilkep-kor:hover img { filter: brightness(0.6); }
        .profilkep-hover-reteg {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
            pointer-events: none;
            gap: 2px;
        }
        .profilkep-kor:hover .profilkep-hover-reteg { opacity: 1; }
        .profilkep-hover-reteg .material-symbols-outlined { font-size: 28px; color: #fff; }
        .profilkep-hover-reteg span:last-child { font-size: 11px; color: #fff; font-weight: 700; }
        .kartya {
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
        }
        body.sotettema .kartya  { background-color: #17161B; box-shadow: 0 4px 15px rgba(0,0,0,0.4); }
        body.vilagostema .kartya { background-color: #f5f5f5; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .kartya-fejlec {
            font-size: 13px;
            font-weight: 700;
            color: #5e9cff;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 16px 0;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .kartya-fejlec .material-symbols-outlined { font-size: 18px; }
        .mezo { margin-bottom: 14px; }
        .mezo label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #888;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .mezo input[type="text"],
        .mezo input[type="email"],
        .mezo input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1.5px solid transparent;
            font-family: 'Quicksand', sans-serif;
            font-size: 14px;
            font-weight: 600;
            box-sizing: border-box;
            transition: border-color 0.2s;
            outline: none;
        }
        body.sotettema .mezo input  { background-color: #2a2930; color: #fff; }
        body.vilagostema .mezo input { background-color: #e8e8e8; color: #111; }
        .mezo input:focus { border-color: #5e9cff; }
        .mentes-gomb { width: 100%; margin-top: 18px; padding: 11px; font-size: 14px; font-weight: 700; border-radius: 10px; }
        .visszajelzok { margin-bottom: 18px; display: flex; flex-direction: column; gap: 8px; }
        .visszajelzo {
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .visszajelzo.siker { background: rgba(76,175,80,0.15); color: #4CAF50; border: 1px solid rgba(76,175,80,0.3); }
        .visszajelzo.hiba  { background: rgba(244,67,54,0.15);  color: #f44336; border: 1px solid rgba(244,67,54,0.3); }
        .visszajelzo .material-symbols-outlined { font-size: 17px; }
        .vissza-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #aaa;
            text-decoration: none;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .vissza-link:hover { color: #5e9cff; }
        #profilkep-input { display: none; }
        .fajlnev-szoveg { font-size: 12px; color: #888; text-align: center; }
    </style>

</body>
</html>
