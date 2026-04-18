<?php
session_start();
include 'adatkapcsolat.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT is_admin FROM felhasznalok WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$sajat = $stmt->get_result()->fetch_assoc();

if (empty($sajat['is_admin'])) {
    header("Location: home.php");
    exit();
}

$uzenet = '';
$hiba   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cel_id'])) {
    $celId  = intval($_POST['cel_id']);
    $ujNev  = trim($_POST['felhasznalonev']);
    $ujEmail = trim($_POST['email']);
    $ujJelszo = $_POST['jelszo'] ?? '';

    $stmt = $conn->prepare("SELECT id FROM felhasznalok WHERE felhasznalonev = ? AND id != ?");
    $stmt->bind_param("si", $ujNev, $celId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $hiba = "Ez a felhasználónév már foglalt.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM felhasznalok WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $ujEmail, $celId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $hiba = "Ez az e-mail cím már foglalt.";
        } else {
            $stmt = $conn->prepare("UPDATE felhasznalok SET felhasznalonev = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $ujNev, $ujEmail, $celId);
            $stmt->execute();

            if (!empty($ujJelszo)) {
                $hash = password_hash($ujJelszo, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare("UPDATE felhasznalok SET jelszo = ? WHERE id = ?");
                $stmt2->bind_param("si", $hash, $celId);
                $stmt2->execute();
            }

            $uzenet = "Felhasználó adatai sikeresen frissítve.";
        }
    }
}

$felhasznalok = $conn->query("SELECT id, felhasznalonev, email, regisztracio_ideje, is_admin FROM felhasznalok ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);

$szerkesztettId = (isset($_GET['id']) && is_numeric($_GET['id'])) ? intval($_GET['id']) : null;
$szerkesztett   = null;

if ($szerkesztettId) {
    $stmt = $conn->prepare("SELECT id, felhasznalonev, email FROM felhasznalok WHERE id = ?");
    $stmt->bind_param("i", $szerkesztettId);
    $stmt->execute();
    $szerkesztett = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel – Receptbázis</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>
</head>
<body class="sotettema">

<?php include 'header.php'; ?>
<?php include 'footer.php'; ?>

<div class="admin-oldal">

    <a href="profil.php" class="vissza-link">
        <span class="material-symbols-outlined" style="font-size:17px;">arrow_back</span>
        Vissza a profilhoz
    </a>

    <div class="oldal-cim">
        <span class="material-symbols-outlined">admin_panel_settings</span>
        <h1>Admin panel</h1>
    </div>

    <div class="kartya">
        <p class="kartya-cim">Felhasználók</p>
        <table class="user-tabla">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Felhasználónév</th>
                    <th>E-mail</th>
                    <th>Regisztrált</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($felhasznalok as $u): ?>
                    <tr>
                        <td style="color:#555;"><?= $u['id'] ?></td>
                        <td>
                            <?= htmlspecialchars($u['felhasznalonev']) ?>
                            <?php if ($u['is_admin']): ?>
                                <span class="admin-badge">admin</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:#888;"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="reg-datum"><?= date('Y. m. d.', strtotime($u['regisztracio_ideje'])) ?></td>
                        <td>
                            <a href="admin.php?id=<?= $u['id'] ?>" class="szerkeszt-gomb">
                                <span class="material-symbols-outlined">edit</span>
                                Szerkesztés
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($szerkesztett): ?>
        <div class="kartya">
            <p class="kartya-cim">
                Szerkesztés: <?= htmlspecialchars($szerkesztett['felhasznalonev']) ?>
            </p>

            <?php if ($uzenet): ?>
                <div class="uzenet-siker"><?= htmlspecialchars($uzenet) ?></div>
            <?php endif; ?>
            <?php if ($hiba): ?>
                <div class="uzenet-hiba"><?= htmlspecialchars($hiba) ?></div>
            <?php endif; ?>

            <form method="POST" action="admin.php?id=<?= $szerkesztett['id'] ?>">
                <input type="hidden" name="cel_id" value="<?= $szerkesztett['id'] ?>">

                <div class="mezo">
                    <label>Felhasználónév</label>
                    <input type="text" name="felhasznalonev"
                           value="<?= htmlspecialchars($szerkesztett['felhasznalonev']) ?>"
                           required maxlength="50">
                </div>

                <div class="mezo">
                    <label>E-mail cím</label>
                    <input type="email" name="email"
                           value="<?= htmlspecialchars($szerkesztett['email']) ?>"
                           required maxlength="100">
                </div>

                <div class="mezo">
                    <label>Új jelszó <span style="font-weight:400; text-transform:none; color:#555;">(hagyd üresen ha nem változtatod)</span></label>
                    <input type="password" name="jelszo" placeholder="••••••••">
                </div>

                <button type="submit" class="gomb mentes-gomb">Mentés</button>
            </form>
        </div>
    <?php endif; ?>

</div>
    <style>
        .admin-oldal {
            max-width: 800px;
            margin: 130px auto 80px auto;
            padding: 0 20px;
        }
        .oldal-cim {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
        }
        .oldal-cim h1 { font-size: 22px; font-weight: 700; margin: 0; }
        .oldal-cim .material-symbols-outlined { font-size: 26px; color: #ff6b6b; }
        .kartya {
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }
        body.sotettema .kartya  { background-color: #17161B; box-shadow: 0 4px 15px rgba(0,0,0,0.4); }
        body.vilagostema .kartya { background-color: #f5f5f5; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .kartya-cim {
            font-size: 13px;
            font-weight: 700;
            color: #ff6b6b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 16px 0;
        }
        .user-tabla { width: 100%; border-collapse: collapse; font-size: 14px; }
        .user-tabla th {
            text-align: left;
            padding: 10px 14px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #666;
            border-bottom: 1px solid #2a2a2a;
        }
        body.vilagostema .user-tabla th { border-bottom-color: #ddd; }
        .user-tabla td {
            padding: 12px 14px;
            border-bottom: 1px solid #1a1a1a;
            vertical-align: middle;
        }
        body.vilagostema .user-tabla td { border-bottom-color: #eee; }
        .user-tabla tr:last-child td { border-bottom: none; }
        .admin-badge {
            font-size: 10px;
            background: rgba(255,107,107,0.15);
            color: #ff6b6b;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 700;
            margin-left: 8px;
        }
        .szerkeszt-gomb {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            background: rgba(94,156,255,0.12);
            color: #5e9cff;
            border: 1px solid rgba(94,156,255,0.3);
            border-radius: 20px;
            padding: 4px 12px;
            text-decoration: none;
            font-family: 'Quicksand', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .szerkeszt-gomb:hover { background: rgba(94,156,255,0.22); }
        .szerkeszt-gomb .material-symbols-outlined { font-size: 14px; }
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
        .mezo input {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1.5px solid transparent;
            font-family: 'Quicksand', sans-serif;
            font-size: 14px;
            font-weight: 600;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }
        body.sotettema .mezo input  { background-color: #2a2930; color: #fff; }
        body.vilagostema .mezo input { background-color: #e8e8e8; color: #111; }
        .mezo input:focus { border-color: #5e9cff; }
        .mentes-gomb {
            width: 100%;
            margin-top: 18px;
            padding: 11px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 10px;
        }
        .uzenet-siker {
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            background: rgba(76,175,80,0.15);
            color: #4CAF50;
            border: 1px solid rgba(76,175,80,0.3);
            margin-bottom: 16px;
        }
        .uzenet-hiba {
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            background: rgba(244,67,54,0.15);
            color: #f44336;
            border: 1px solid rgba(244,67,54,0.3);
            margin-bottom: 16px;
        }
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
        .reg-datum { font-size: 12px; color: #555; }
    </style>
</body>
</html>
