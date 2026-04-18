<?php
header('Content-Type: application/json; charset=utf-8');
include 'adatkapcsolat.php';

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit();
}

$minta = '%' . $conn->real_escape_string($q) . '%';

$sql = "
    SELECT
        receptek.id,
        receptek.cim,
        receptek.kategoria,
        receptek.elkeszitesi_ido,
        receptek.indexkep,
        felhasznalok.felhasznalonev
    FROM receptek
    JOIN felhasznalok ON receptek.felhasznalo_id = felhasznalok.id
    WHERE receptek.cim       LIKE '$minta'
       OR receptek.kategoria LIKE '$minta'
       OR receptek.hozzavalok LIKE '$minta'
    ORDER BY
        CASE
            WHEN receptek.cim      LIKE '$minta' THEN 0
            WHEN receptek.kategoria LIKE '$minta' THEN 1
            ELSE 2
        END,
        receptek.letrehozva DESC
    LIMIT 8
";

$eredmeny = $conn->query($sql);
$lista = [];

while ($sor = $eredmeny->fetch_assoc()) {
    $lista[] = [
        'id'        => (int) $sor['id'],
        'cim'       => $sor['cim'],
        'kategoria' => $sor['kategoria'],
        'ido'       => $sor['elkeszitesi_ido'],
        'indexkep'  => $sor['indexkep'],
        'szerzo'    => $sor['felhasznalonev'],
    ];
}

echo json_encode($lista, JSON_UNESCAPED_UNICODE);