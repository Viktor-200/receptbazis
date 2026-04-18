<?php
session_start();
include 'adatkapcsolat.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recept_torles']) && isset($_SESSION['user_id'])) {
    $torlesId = intval($_POST['recept_torles']);
    $stmt = $conn->prepare("DELETE FROM receptek WHERE id = ? AND felhasznalo_id = ?");
    $stmt->bind_param("ii", $torlesId, $_SESSION['user_id']);
    $stmt->execute();
    header("Location: profil.php");
    exit();
}

$profilId = (isset($_GET['id']) && is_numeric($_GET['id'])) ? intval($_GET['id']) : null;
$sajatProfil = ($profilId === null) || (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $profilId);

if ($profilId === null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $celId = $_SESSION['user_id'];
} else {
    $celId = $profilId;
}

$stmt = $conn->prepare("SELECT id, felhasznalonev, email, regisztracio_ideje, profilkep, is_admin FROM felhasznalok WHERE id = ?");
$stmt->bind_param("i", $celId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: home.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, cim, kategoria, letrehozva FROM receptek WHERE felhasznalo_id = ? ORDER BY letrehozva DESC");
$stmt->bind_param("i", $celId);
$stmt->execute();
$receptek = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("
    SELECT kommentek.szoveg, kommentek.datum, receptek.cim AS recept_cim, receptek.id AS recept_id
    FROM kommentek
    JOIN receptek ON kommentek.recept_id = receptek.id
    WHERE kommentek.felhasznalo_id = ?
    ORDER BY kommentek.datum DESC
");
$stmt->bind_param("i", $celId);
$stmt->execute();
$kommentek = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$profilkepUrl = (!empty($user['profilkep']) && file_exists($user['profilkep']))
    ? $user['profilkep']
    : 'img/alap.png';

$oldalCim = $sajatProfil ? 'Profil – Receptbázis' : htmlspecialchars($user['felhasznalonev']) . ' profilja – Receptbázis';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $oldalCim ?></title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>
</head>
<body class="sotettema">

<?php include 'header.php'; ?>

<div class="profil-oldal">

    <?php if (!$sajatProfil): ?>
        <a href="javascript:history.back()" class="vissza-link">
            <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
            Vissza
        </a>
    <?php endif; ?>

    <div class="profil-doboz">
        <img src="<?= htmlspecialchars($profilkepUrl) ?>" alt="Profilkép">
        <div class="profil-jobb">
            <div class="profil-adatok">
                <h2><?= htmlspecialchars($user['felhasznalonev']) ?></h2>

                <?php if ($sajatProfil): ?>
                    <p>E-mail: <span><?= htmlspecialchars($user['email']) ?></span></p>
                <?php else: ?>
                    <span class="nyilvanos-badge">
                        <span class="material-symbols-outlined" style="font-size:14px;">public</span>
                        Nyilvános profil
                    </span>
                <?php endif; ?>

                <p>Regisztrált: <span><?= date('Y. m. d.', strtotime($user['regisztracio_ideje'])) ?></span></p>
                <p><?= count($receptek) ?> recept &nbsp;·&nbsp; <?= count($kommentek) ?> komment</p>
            </div>

            <?php if ($sajatProfil): ?>
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
                <a href="beallitasok.php" class="beallitasok-gomb">
                    <span class="material-symbols-outlined">settings</span>
                    Beállítások
                </a>
                <?php if (!empty($user['is_admin'])): ?>
                    <a href="admin.php" class="admin-gomb">
                        <span class="material-symbols-outlined">admin_panel_settings</span>
                        Admin panel
                    </a>
                <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="szekcio-cim">
        <?= $sajatProfil ? 'Receptjeim' : htmlspecialchars($user['felhasznalonev']) . ' receptjei' ?>
    </div>
    <div class="lista">
        <?php if (count($receptek) > 0): ?>
            <?php foreach ($receptek as $r): ?>
                <div class="lista-sor">
                    <div>
                        <a href="recept.php?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['cim']) ?></a>
                        <span class="kat-badge"><?= htmlspecialchars($r['kategoria']) ?></span>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span class="datum"><?= date('Y. m. d.', strtotime($r['letrehozva'])) ?></span>
                        <?php if ($sajatProfil): ?>
                            <form method="POST" action="profil.php" onsubmit="return confirm('Biztosan törlöd ezt a receptet?')">
                                <input type="hidden" name="recept_torles" value="<?= $r['id'] ?>">
                                <button type="submit" class="torlo-gomb" title="Törlés">✕</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="ures-uzenet">
                <?= $sajatProfil ? 'Még nem töltöttél fel receptet.' : 'Még nincs feltöltött recept.' ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="szekcio-cim">
        <?= $sajatProfil ? 'Kommentjeim' : htmlspecialchars($user['felhasznalonev']) . ' kommentjei' ?>
    </div>
    <div class="lista">
        <?php if (count($kommentek) > 0): ?>
            <?php foreach ($kommentek as $k): ?>
                <div class="lista-sor">
                    <div class="komment-blokk">
                        <span class="komment-elonezet"><?= htmlspecialchars($k['szoveg']) ?></span>
                        <a href="recept.php?id=<?= $k['recept_id'] ?>" class="komment-link">
                            → <?= htmlspecialchars($k['recept_cim']) ?>
                        </a>
                    </div>
                    <span class="datum"><?= date('Y. m. d.', strtotime($k['datum'])) ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="ures-uzenet">
                <?= $sajatProfil ? 'Még nem írtál kommentet.' : 'Még nincs komment.' ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include 'footer.php'; ?>

    <style>
        .profil-oldal {
            max-width: 750px;
            margin: 130px auto 80px auto;
            padding: 0 20px;
        }
        .profil-doboz {
            border-radius: 12px;
            padding: 28px 30px;
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 30px;
        }
        body.sotettema .profil-doboz  { background-color: #17161B; box-shadow: 0 4px 20px rgba(0,0,0,0.4); }
        body.vilagostema .profil-doboz { background-color: #f5f5f5; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .profil-doboz img {
            width: 80px; height: 80px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }
        .profil-jobb {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .profil-adatok h2 { margin: 0 0 6px 0; font-size: 22px; }
        .profil-adatok p  { margin: 3px 0; font-size: 14px; color: #aaa; }
        .profil-adatok p span { color: #fff; }
        body.vilagostema .profil-adatok p      { color: #666; }
        body.vilagostema .profil-adatok p span { color: #111; }
        .beallitasok-gomb {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(94,156,255,0.12);
            color: #5e9cff;
            border: 1.5px solid rgba(94,156,255,0.35);
            border-radius: 25px;
            padding: 7px 16px;
            font-family: 'Quicksand', sans-serif;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .beallitasok-gomb:hover { background: rgba(94,156,255,0.22); border-color: #5e9cff; }
        .beallitasok-gomb .material-symbols-outlined { font-size: 17px; }
        .admin-gomb {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 160, 94, 0.12);
            color: #ff6b6b;
            border: 1.5px solid #ff6b6b;
            border-radius: 25px;
            padding: 7px 16px;
            font-family: 'Quicksand', sans-serif;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
            white-space: nowrap;
            margin-top: 8px;
        }
        .admin-gomb:hover { background: rgba(255, 160, 94, 0.22); border-color: #ff6b6b; }
        .admin-gomb .material-symbols-outlined { font-size: 17px; }
        .nyilvanos-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            background: rgba(94,156,255,0.12);
            color: #5e9cff;
            border-radius: 20px;
            padding: 3px 10px;
            margin-top: 5px;
        }
        .szekcio-cim { font-size: 16px; font-weight: 700; margin-bottom: 10px; color: #5e9cff; }
        .lista { border-radius: 12px; overflow: hidden; margin-bottom: 35px; }
        body.sotettema .lista  { background-color: #17161B; box-shadow: 0 4px 20px rgba(0,0,0,0.4); }
        body.vilagostema .lista { background-color: #f5f5f5; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .lista-sor {
            padding: 14px 20px;
            border-bottom: 1px solid #2a2a2a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
        }
        body.vilagostema .lista-sor { border-bottom-color: #e0e0e0; }
        .lista-sor:last-child { border-bottom: none; }
        .lista-sor a { color: #fff; text-decoration: none; font-weight: 600; }
        body.vilagostema .lista-sor a { color: #222; }
        .lista-sor a:hover { color: #5e9cff; }
        .kat-badge {
            font-size: 11px;
            background-color: rgba(94,156,255,0.15);
            color: #5e9cff;
            padding: 3px 10px;
            border-radius: 20px;
            margin-left: 10px;
            font-weight: 600;
        }
        .datum { font-size: 12px; color: #666; white-space: nowrap; margin-left: 15px; }
        .komment-blokk { display: flex; flex-direction: column; gap: 4px; }
        .komment-elonezet { color: #ccc; font-size: 13px; }
        body.vilagostema .komment-elonezet { color: #444; }
        .komment-link { font-size: 12px; color: #5e9cff; text-decoration: none; }
        .komment-link:hover { text-decoration: underline; }
        .ures-uzenet { padding: 20px; text-align: center; color: #555; font-size: 14px; }
        body.vilagostema .ures-uzenet { color: #aaa; }
        .torlo-gomb {
            background: none;
            border: 1px solid #444;
            color: #666;
            font-size: 12px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            transition: color 0.2s, border-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .torlo-gomb:hover { color: #f44336; border-color: #f44336; }
        .vissza-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #aaa;
            text-decoration: none;
            margin-bottom: 20px;
        }
        .vissza-link:hover { color: #5e9cff; }
    </style>

</body>
</html>
