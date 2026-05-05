<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptbázis</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'footer.php'; ?>
<?php include 'adatkapcsolat.php'; ?>

<?php
$kategoria = trim($_GET['kategoria'] ?? '');

$engedelyezett = ['Reggeli', 'Leves', 'Főfogás', 'Saláta', 'Desszert', 'Ital'];
if ($kategoria && !in_array($kategoria, $engedelyezett)) {
    $kategoria = '';
}

if ($kategoria) {
    $stmt = $conn->prepare("SELECT id, cim, kategoria, elkeszitesi_ido, indexkep FROM receptek WHERE kategoria = ? ORDER BY letrehozva DESC");
    $stmt->bind_param("s", $kategoria);
    $stmt->execute();
    $receptek = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $eredmeny = $conn->query("SELECT id, cim, kategoria, elkeszitesi_ido, indexkep FROM receptek ORDER BY RAND() LIMIT 8");
    $receptek = $eredmeny->fetch_all(MYSQLI_ASSOC);
}

$kategoriaFeliratok = [
    'Reggeli'  => 'Reggelik',
    'Leves'    => 'Levesek',
    'Főfogás'  => 'Főfogások',
    'Saláta'   => 'Saláták / Vegán',
    'Desszert' => 'Desszertek',
    'Ital'     => 'Italok',
];
$oldalCim = $kategoria ? ($kategoriaFeliratok[$kategoria] ?? $kategoria) : 'Ajánlásaink';
?>

<div class="receptek-szekció">
    <div class="szekció-fejléc">
        <h2><?= htmlspecialchars($oldalCim) ?></h2>
        <?php if ($kategoria): ?>
            <a href="home.php" style="font-size:13px; color:#fff; text-decoration:none;">← Főoldal</a>
        <?php endif; ?>
    </div>

    <?php if (count($receptek) > 0): ?>
        <div class="recept-racs">
            <?php foreach ($receptek as $r): ?>
                <a href="recept.php?id=<?= $r['id'] ?>" class="recept-kartya">

                    <?php if (!empty($r['indexkep']) && file_exists($r['indexkep'])): ?>
                        <img src="<?= htmlspecialchars($r['indexkep']) ?>"
                             alt="<?= htmlspecialchars($r['cim']) ?>"
                             class="recept-boritokep">
                    <?php else: ?>
                        <div class="kep-helyettesito">🍴</div>
                    <?php endif; ?>

                    <div class="kartya-adatok">
                        <p class="kartya-cim"><?= htmlspecialchars($r['cim']) ?></p>
                        <div class="kartya-meta">
                            <span class="kat-jelveny"><?= htmlspecialchars($r['kategoria']) ?></span>
                            <?php if (!empty($r['elkeszitesi_ido'])): ?>
                                <span class="ido-szoveg">⏱ <?= $r['elkeszitesi_ido'] ?> perc</span>
                            <?php endif; ?>
                        </div>
                    </div>

                </a>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="nincs-recept">
            <p><?= $kategoria ? 'Még nincs recept ebben a kategóriában.' : 'Még nincsenek receptek az adatbázisban.' ?></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="feltoltes.php" class="gomb" style="display:inline-block; margin-top:15px;">
                    Tölts fel egyet!
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<video autoplay muted loop id="videohatter">
    <source src="img/hatter.mp4" type="video/mp4">
</video>

    <style>
        .receptek-szekció {
            max-width: 1200px;
            margin: 130px auto 80px auto;
            padding: 0 20px;
        }
        .szekció-fejléc {
            text-align: center;
            margin-bottom: 30px;
        }
        .szekció-fejléc h2 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 5px 0;
        }
        .recept-racs {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .recept-kartya {
            border-radius: 12px;
            overflow: hidden;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        body.sotettema .recept-kartya {
            background-color: #17161B;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            color: #fff;
        }
        body.vilagostema .recept-kartya {
            background-color: #f5f5f5;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            color: #111;
        }
        .recept-kartya:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.5);
        }
        .recept-boritokep {
            width: 100%;
            height: 170px;
            object-fit: cover;
            display: block;
        }
        .kep-helyettesito {
            width: 100%;
            height: 170px;
            background-color: #2a2a2a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        body.vilagostema .kep-helyettesito { background-color: #e0e0e0; }
        .kartya-adatok {
            padding: 14px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .kartya-cim {
            font-size: 15px;
            font-weight: 700;
            margin: 0;
            line-height: 1.3;
        }
        .kartya-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            margin-top: 4px;
        }
        .kat-jelveny {
            background-color: rgba(94,156,255,0.15);
            color: #5e9cff;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        .ido-szoveg { color: #888; }
        body.vilagostema .ido-szoveg { color: #888; }
        .nincs-recept {
            text-align: center;
            padding: 60px 20px;
            color: #fff;
        }
    </style>

</body>
</html>