<?php
session_start();
include 'adatkapcsolat.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: home.php");
    exit();
}

$receptId = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT receptek.*, felhasznalok.felhasznalonev, felhasznalok.id AS szerzo_id
    FROM receptek
    JOIN felhasznalok ON receptek.felhasznalo_id = felhasznalok.id
    WHERE receptek.id = ?
");
$stmt->bind_param("i", $receptId);
$stmt->execute();
$recept = $stmt->get_result()->fetch_assoc();

if (!$recept) {
    header("Location: home.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $szoveg   = trim($_POST['szoveg'] ?? '');
    $parentId = (isset($_POST['parent_id']) && is_numeric($_POST['parent_id']))
        ? intval($_POST['parent_id'])
        : null;

    if ($szoveg !== '') {
        if ($parentId) {
            $stmt2 = $conn->prepare("INSERT INTO kommentek (recept_id, felhasznalo_id, szoveg, parent_id) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("iisi", $receptId, $_SESSION['user_id'], $szoveg, $parentId);
        } else {
            $stmt2 = $conn->prepare("INSERT INTO kommentek (recept_id, felhasznalo_id, szoveg) VALUES (?, ?, ?)");
            $stmt2->bind_param("iis", $receptId, $_SESSION['user_id'], $szoveg);
        }
        $stmt2->execute();
        header("Location: recept.php?id=$receptId#kommentek");
        exit();
    }
}

$stmt = $conn->prepare("
    SELECT kommentek.id, kommentek.szoveg, kommentek.datum, kommentek.parent_id,
           felhasznalok.felhasznalonev, felhasznalok.id AS felhasznalo_id
    FROM kommentek
    JOIN felhasznalok ON kommentek.felhasznalo_id = felhasznalok.id
    WHERE kommentek.recept_id = ?
    ORDER BY kommentek.datum ASC
");
$stmt->bind_param("i", $receptId);
$stmt->execute();
$osszes_komment = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$kommentekById = [];
$gyokerek = [];

foreach ($osszes_komment as &$k) {
    $k['valaszok'] = [];
    $kommentekById[$k['id']] = &$k;
}
unset($k);

foreach ($kommentekById as $id => &$k) {
    if ($k['parent_id'] === null) {
        $gyokerek[] = &$k;
    } elseif (isset($kommentekById[$k['parent_id']])) {
        $kommentekById[$k['parent_id']]['valaszok'][] = &$k;
    }
}
unset($k);

$ytEmbed = null;
if (!empty($recept['youtube_link'])) {
    preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $recept['youtube_link'], $m);
    if (isset($m[1])) $ytEmbed = 'https://www.youtube.com/embed/' . $m[1];
}

function kommentKiir($k, $receptId, $bejelentkezve, $melyseg = 0) {
    $bal = $melyseg > 0 ? 'margin-left:' . min($melyseg * 30, 60) . 'px;' : '';
    $kid = (int) $k['id'];
    ?>
    <div class="komment-elem<?= $melyseg > 0 ? ' valasz-elem' : '' ?>" id="komment-<?= $kid ?>" style="<?= $bal ?>">
        <?php if ($melyseg > 0): ?><div class="valasz-jelzo">↳</div><?php endif; ?>
        <div class="komment-fejlec">
            <a href="profil.php?id=<?= (int) $k['felhasznalo_id'] ?>" class="komment-nev">
                <?= htmlspecialchars($k['felhasznalonev']) ?>
            </a>
            <span class="komment-datum"><?= date('Y. m. d.', strtotime($k['datum'])) ?></span>
        </div>
        <p class="komment-szoveg"><?= htmlspecialchars($k['szoveg']) ?></p>

        <?php if ($bejelentkezve && $melyseg < 2): ?>
            <button class="valasz-gomb" onclick="valaszToggle(<?= $kid ?>)">↩ Válasz</button>
            <div id="valasz-<?= $kid ?>" style="display:none; margin-top:8px;">
                <form method="POST" action="recept.php?id=<?= $receptId ?>">
                    <input type="hidden" name="parent_id" value="<?= $kid ?>">
                    <textarea name="szoveg" rows="2" placeholder="Válaszod..." required
                        style="width:100%; padding:8px; border-radius:8px; border:none;
                               font-family:'Quicksand',sans-serif; font-size:13px;
                               resize:vertical; box-sizing:border-box;
                               background:#2a2a2a; color:#fff;"></textarea>
                    <div style="display:flex; gap:8px; margin-top:6px;">
                        <button type="submit" class="gomb" style="padding:5px 14px; font-size:12px;">Küldés</button>
                        <button type="button" class="gomb" style="padding:5px 14px; font-size:12px; background:#555;"
                            onclick="valaszToggle(<?= $kid ?>)">Mégse</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php foreach ($k['valaszok'] as $valasz): ?>
        <?php kommentKiir($valasz, $receptId, $bejelentkezve, $melyseg + 1); ?>
    <?php endforeach; ?>
    <?php
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recept['cim']) ?> – Receptbázis</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>
    <style>
        .recept-oldal {
            max-width: 800px;
            margin: 130px auto 80px auto;
            padding: 0 20px;
        }
        .recept-boritokep {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 14px;
            display: block;
            margin-bottom: 25px;
        }
        .recept-cim { font-size: 28px; font-weight: 700; margin: 0 0 12px 0; line-height: 1.3; }
        body.sotettema .recept-cim  { color: #fff; }
        body.vilagostema .recept-cim { color: #111; }
        .recept-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
            font-size: 13px;
        }
        .meta-elem { display: flex; align-items: center; gap: 6px; color: #aaa; }
        .meta-elem a { color: #aaa; text-decoration: none; }
        .meta-elem a:hover { color: #5e9cff; }
        body.vilagostema .meta-elem, body.vilagostema .meta-elem a { color: #777; }
        .meta-elem .material-symbols-outlined { font-size: 17px; color: #5e9cff; }
        .kat-badge {
            background-color: rgba(94,156,255,0.15);
            color: #5e9cff;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }
        .blokk {
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }
        body.sotettema .blokk  { background-color: #17161B; box-shadow: 0 4px 15px rgba(0,0,0,0.4); }
        body.vilagostema .blokk { background-color: #f5f5f5; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .blokk-cim {
            font-size: 16px;
            font-weight: 700;
            color: #5e9cff;
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .blokk-cim .material-symbols-outlined { font-size: 20px; }
        .hozzavalok-lista { list-style: none; padding: 0; margin: 0; }
        .hozzavalok-lista li {
            padding: 7px 0;
            border-bottom: 1px solid #2a2a2a;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        body.sotettema .hozzavalok-lista li  { color: #ccc; border-bottom-color: #2a2a2a; }
        body.vilagostema .hozzavalok-lista li { color: #222; border-bottom-color: #e0e0e0; }
        .hozzavalok-lista li:last-child { border-bottom: none; }
        .hozzavalok-lista li::before { content: "•"; color: #5e9cff; font-size: 18px; line-height: 1; }
        .leiras { font-size: 14px; line-height: 1.8; white-space: pre-line; margin: 0; }
        body.sotettema .leiras  { color: #ccc; }
        body.vilagostema .leiras { color: #222; }
        .yt-iframe { width: 100%; aspect-ratio: 16/9; border: none; border-radius: 12px; display: block; }
        .komment-lista { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
        .komment-elem { border-radius: 10px; padding: 12px 16px; }
        body.sotettema .komment-elem  { background-color: #1e1d23; }
        body.vilagostema .komment-elem { background-color: #ececec; }
        .valasz-elem { border-left: 3px solid #5e9cff; }
        body.sotettema .valasz-elem  { background-color: #1a1924; }
        body.vilagostema .valasz-elem { background-color: #e4e4f0; }
        .valasz-jelzo { font-size: 16px; color: #5e9cff; margin-bottom: 4px; }
        .komment-fejlec { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .komment-nev { font-weight: 700; font-size: 13px; color: #5e9cff; text-decoration: none; }
        .komment-nev:hover { opacity: 0.75; }
        .komment-datum { font-size: 11px; }
        body.sotettema .komment-datum  { color: #aaa; }
        body.vilagostema .komment-datum { color: #555; }
        .komment-szoveg { font-size: 14px; line-height: 1.5; margin: 0 0 6px 0; }
        body.sotettema .komment-szoveg  { color: #ccc; }
        body.vilagostema .komment-szoveg { color: #222; }
        .valasz-gomb {
            background: none;
            border: 1px solid #444;
            color: #888;
            font-size: 11px;
            font-family: 'Quicksand', sans-serif;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            cursor: pointer;
            transition: color 0.2s, border-color 0.2s;
            margin-top: 4px;
        }
        .valasz-gomb:hover { color: #5e9cff; border-color: #5e9cff; }
        .komment-urlap textarea {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-family: 'Quicksand', sans-serif;
            font-size: 14px;
            resize: vertical;
            box-sizing: border-box;
            margin-bottom: 10px;
        }
        body.sotettema .komment-urlap textarea  { background-color: #2a2a2a; color: #fff; }
        body.vilagostema .komment-urlap textarea { background-color: #e0e0e0; color: #111; }
        .nincs-komment { font-size: 14px; color: #666; text-align: center; padding: 15px 0; }
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
        .nem-bejelentkezett { font-size: 14px; color: #888; text-align: center; padding: 10px 0; }
        .nem-bejelentkezett a { color: #5e9cff; text-decoration: none; }
    </style>
</head>
<body class="sotettema">

<?php include 'header.php'; ?>

<div class="recept-oldal">

    <a href="home.php" class="vissza-link">
        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
        Vissza a főoldalra
    </a>

    <h1 class="recept-cim"><?= htmlspecialchars($recept['cim']) ?></h1>

    <div class="recept-meta">
        <span class="kat-badge"><?= htmlspecialchars($recept['kategoria']) ?></span>

        <?php if (!empty($recept['elkeszitesi_ido'])): ?>
            <span class="meta-elem">
                <span class="material-symbols-outlined">timer</span>
                <?= $recept['elkeszitesi_ido'] ?> perc
            </span>
        <?php endif; ?>

        <span class="meta-elem">
            <span class="material-symbols-outlined">person</span>
            <a href="profil.php?id=<?= (int) $recept['szerzo_id'] ?>">
                <?= htmlspecialchars($recept['felhasznalonev']) ?>
            </a>
        </span>

        <span class="meta-elem">
            <span class="material-symbols-outlined">calendar_today</span>
            <?= date('Y. m. d.', strtotime($recept['letrehozva'])) ?>
        </span>
    </div>

    <?php if ($ytEmbed): ?>
        <iframe class="yt-iframe" src="<?= $ytEmbed ?>" allowfullscreen style="margin-bottom:20px;"></iframe>
    <?php elseif (!empty($recept['receptkep']) && file_exists($recept['receptkep'])): ?>
        <img src="<?= htmlspecialchars($recept['receptkep']) ?>" alt="<?= htmlspecialchars($recept['cim']) ?>" class="recept-boritokep">
    <?php elseif (!empty($recept['indexkep']) && file_exists($recept['indexkep'])): ?>
        <img src="<?= htmlspecialchars($recept['indexkep']) ?>" alt="<?= htmlspecialchars($recept['cim']) ?>" class="recept-boritokep">
    <?php endif; ?>

    <div class="blokk">
        <h2 class="blokk-cim">
            <span class="material-symbols-outlined">grocery</span>
            Hozzávalók
        </h2>
        <ul class="hozzavalok-lista">
            <?php foreach (explode("\n", trim($recept['hozzavalok'])) as $sor): ?>
                <?php $sor = trim($sor); if ($sor === '') continue; ?>
                <li><?= htmlspecialchars($sor) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="blokk">
        <h2 class="blokk-cim">
            <span class="material-symbols-outlined">skillet</span>
            Elkészítés
        </h2>
        <p class="leiras"><?= htmlspecialchars($recept['leiras']) ?></p>
    </div>

    <div class="blokk" id="kommentek">
        <h2 class="blokk-cim">
            <span class="material-symbols-outlined">chat_bubble</span>
            Kommentek (<?= count($osszes_komment) ?>)
        </h2>

        <?php if (count($gyokerek) > 0): ?>
            <div class="komment-lista">
                <?php foreach ($gyokerek as $k): ?>
                    <?php kommentKiir($k, $receptId, isset($_SESSION['user_id'])); ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="nincs-komment">Még nincs komment ehhez a recepthez.</p>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" action="recept.php?id=<?= $receptId ?>" class="komment-urlap" style="margin-top:10px;">
                <textarea name="szoveg" rows="3" placeholder="Írd ide a kommentedet..." required></textarea>
                <button type="submit" class="gomb">Küldés</button>
            </form>
        <?php else: ?>
            <p class="nem-bejelentkezett">
                <a href="login.php">Jelentkezz be</a> a kommenteléshez.
            </p>
        <?php endif; ?>
    </div>

</div>

<?php include 'footer.php'; ?>

<script>
function valaszToggle(kid) {
    const div = document.getElementById('valasz-' + kid);
    if (!div) return;
    div.style.display = div.style.display === 'none' ? 'block' : 'none';
    if (div.style.display === 'block') div.querySelector('textarea').focus();
}
</script>

</body>
</html>
