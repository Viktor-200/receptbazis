<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konyhai fogalmak – Receptbázis</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .fogalmak-tartalom {
            padding: 20px;
            max-width: 1000px;
            margin: 130px auto 80px auto;
        }
        .fogalmak-tartalom h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }
        .fogalmak-tabla {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Quicksand', sans-serif;
            background-color: #222;
            color: #fff;
        }
        .fogalmak-tabla th,
        .fogalmak-tabla td {
            border: 1px solid #444;
            padding: 15px;
            text-align: left;
        }
        .fogalmak-tabla th {
            background-color: #333;
            color: #5e9cff;
        }
        .fogalmak-tabla tr:nth-child(even) { background-color: #2a2a2a; }
        .fogalmak-tabla tr:hover { background-color: #383838; }
        .fogalom-neve { color: #5e9cff; font-weight: bold; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'footer.php'; ?>

<?php
$fogalmak = [
    "Abálás"               => "Forráspont alatti (kb. 95 °C-os) hőkezelés. Leggyakrabban szalonnát, hurkaféléket abálunk.",
    "Al dente"             => "„Fogkeményre\" főzött állag, főként tésztákra használjuk.",
    "Angolos-ra sütés"     => "Olyan hússütési mód, ahol csak a hús külső része sül át, belül nyers marad.",
    "Aszpik"               => "Zselésítő anyag (csontból, bőrből vagy zselatinból).",
    "Bardírozás"           => "Húsok szalonnaszeletekkel való beborítása sütés előtt a kiszáradás ellen.",
    "Bécsi bundázás"       => "Liszt → tojás → zsemlemorzsa, majd sütés.",
    "Blansírozás"          => "Rövid forrázás, majd lehűtés fertőtlenítésre vagy előkészítésre.",
    "Bő zsiradékban sütés" => "Az alapanyagot teljesen ellepi a forró zsiradék (pl. rántott hús).",
    "Buggyantás"           => "Héj nélküli tojás készítése enyhén ecetes, gyöngyöző vízben.",
    "Csőben sütés"         => "Előfőzött étel sütőben való rápirítása (gratinírozás).",
    "Darabolás"            => "Alapanyagok méretre vágása az egyenletes hőkezeléshez.",
    "Derítés"              => "Levesek tisztítása tojásfehérjével a kristálytiszta léért.",
    "Elősütés"             => "Hús gyors átsütése kevés zsiradékban pörzsréteg képzésére.",
    "Fehér rántás"         => "Nem pirított liszt és zsiradék keveréke világos mártásokhoz.",
    "Fényezés"             => "Felületek fényessé tétele vajjal, zsírral vagy aszpikkal.",
    "Filé"                 => "Csont nélküli, letisztított húsrész.",
    "Flambírozás"          => "Étel leöntése alkohollal és meggyújtása az aroma megőrzéséért.",
    "Gőzölés"              => "Kíméletes puhítás csak forró gőz használatával.",
    "Gratinírozás"         => "Pirított felső réteg kialakítása sütőben.",
    "Gyors sűrítés"        => "Liszttel elkevert vaj hozzáadása forrásban lévő ételhez.",
    "Habarás"              => "Liszt tejtermékkel elkeverve sűrítés céljából.",
    "Juliennere vágás"     => "Gyufaszál vastagságú, hosszúkás darabolási forma.",
    "Kiforralás"           => "Liszt ízének eltüntetése további forralással.",
    "Klopfolás"            => "Hússzeletek rostjainak lazítása húsverővel.",
    "Montírozás"           => "Vaj hozzákeverése kész ételhez az állag javítására (forralás nélkül).",
    "Passzírozás"          => "Főtt alapanyag áttörése szitán a magok/héj eltávolítására.",
    "Párolás"              => "Kevés zsiradékon pirítás, majd fedő alatt, kevés lében puhítás.",
    "Rántás"               => "Liszt és zsiradék megpirítása, majd folyadékkal felöntése.",
    "Smizírozás"           => "Formák belső falának bevonása aszpikkal díszítéshez.",
    "Világos rántás"       => "Enyhén pirított lisztből készült sűrítőanyag.",
];
?>

<main class="fogalmak-tartalom">
    <h1>Konyhai Fogalmak</h1>
    <table class="fogalmak-tabla">
        <tbody>
            <?php foreach ($fogalmak as $nev => $leiras): ?>
                <tr>
                    <td><span class="fogalom-neve"><?= htmlspecialchars($nev) ?></span></td>
                    <td><?= htmlspecialchars($leiras) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>
