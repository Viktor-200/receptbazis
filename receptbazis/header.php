<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset();
    session_destroy();
    header("Location: home.php");
    exit();
}

$fejlec_profilkep = 'img/alap.png';
if (isset($_SESSION['user_id'])) {
    if (!isset($conn)) include 'adatkapcsolat.php';
    $stmt = $conn->prepare("SELECT profilkep FROM felhasznalok WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $sor = $stmt->get_result()->fetch_assoc();
    if (!empty($sor['profilkep']) && file_exists($sor['profilkep'])) {
        $fejlec_profilkep = $sor['profilkep'];
    }
}
?>

<header class="header">
    <div class="headertartalom">

        <div class="header-bal">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
            <span class="material-symbols-outlined menugomb" id="menugomb">menu</span>
            <a href="home.php" class="logolink">
                <img src="img/feherlogo.png" alt="Receptbázis" class="logo" id="logo-kep">
            </a>
        </div>

        <div class="kereso-kontener" id="kereso-kontener">
            <div class="kereso-belso">
                <span class="material-symbols-outlined kereso-ikon">search</span>
                <input type="text" id="kereso-mezo" class="kereso-mezo"
                    placeholder="Keresés..." autocomplete="off"
                    aria-label="Keresés" aria-haspopup="listbox" aria-expanded="false">
                <button class="kereso-torlo" id="kereso-torlo" aria-label="Törlés" style="display:none;">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="talalatok-lista" id="talalatok-lista" role="listbox" style="display:none;"></div>
        </div>

        <div class="header-jobb">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div style="display:flex; align-items:center; gap:10px;">
                    <a href="feltoltes.php" class="profillink" title="Recept feltöltése">
                        <span class="material-symbols-outlined menugomb">add</span>
                    </a>
                    <a href="profil.php" class="profillink">
                        <img src="<?= htmlspecialchars($fejlec_profilkep) ?>" alt="Profil" class="profilkep">
                    </a>
                    <a href="?logout=true" class="profillink" title="Kijelentkezés">
                        <span class="material-symbols-outlined menugomb">logout</span>
                    </a>
                </div>
            <?php else: ?>
                <a href="login.php" style="text-decoration:none;">
                    <button class="gomb">Bejelentkezés</button>
                </a>
            <?php endif; ?>
        </div>

    </div>
</header>

<div class="oldalmenu" id="oldalmenu">
    <div class="oldalmenu-fejlec">
        <h2>Menü</h2>
        <span class="material-symbols-outlined bezar-gomb" id="bezargomb">close</span>
    </div>
    <ul class="kategoria-lista">
        <li><a href="home.php?kategoria=Reggeli"><span class="material-symbols-outlined">bakery_dining</span>Reggelik</a></li>
        <li><a href="home.php?kategoria=Leves"><span class="material-symbols-outlined">soup_kitchen</span>Levesek</a></li>
        <li><a href="home.php?kategoria=Főfogás"><span class="material-symbols-outlined">skillet</span>Főfogások</a></li>
        <li><a href="home.php?kategoria=Saláta"><span class="material-symbols-outlined">eco</span>Saláták / Vegán</a></li>
        <li><a href="home.php?kategoria=Desszert"><span class="material-symbols-outlined">icecream</span>Desszertek</a></li>
        <li><a href="home.php?kategoria=Ital"><span class="material-symbols-outlined">wine_bar</span>Italok</a></li>
        <li class="elvalaszto">
            <a href="konyhaifogalmak.php">
                <span class="material-symbols-outlined">menu_book</span>Konyhai fogalmak
            </a>
        </li>
        <li>
            <a id="temavalto-gomb" style="cursor:pointer;">
                <span class="material-symbols-outlined" id="tema-ikon">light_mode</span>
                <span id="tema-szoveg">Világos mód</span>
            </a>
        </li>
    </ul>
</div>

<script>
(function () {
    const mezo      = document.getElementById('kereso-mezo');
    const lista     = document.getElementById('talalatok-lista');
    const torloGomb = document.getElementById('kereso-torlo');

    let timer       = null;
    let aktivIndex  = -1;
    let utolsoQ     = '';

    function esc(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function kiemel(szoveg, q) {
        if (!q) return esc(szoveg);
        const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return esc(szoveg).replace(re, '<mark>$1</mark>');
    }

    function elemHtml(t, q) {
        const kep = t.indexkep
            ? '<div class="talalat-kep"><img src="' + esc(t.indexkep) + '" alt=""></div>'
            : '<div class="talalat-kep">🍴</div>';

        const ido = t.ido ? '<span class="talalat-ido">⏱ ' + t.ido + ' perc</span>' : '';

        return '<a href="recept.php?id=' + t.id + '" class="talalat-elem" role="option">'
            + kep
            + '<div class="talalat-info">'
            +   '<div class="talalat-cim">' + kiemel(t.cim, q) + '</div>'
            +   '<div class="talalat-reszlet">'
            +     '<span class="talalat-kat">' + esc(t.kategoria) + '</span>'
            +     ido
            +     '<span class="talalat-szerzo">' + esc(t.szerzo) + '</span>'
            +   '</div>'
            + '</div>'
            + '</a>';
    }

    function mutat(talalatok, q) {
        aktivIndex = -1;
        if (talalatok.length === 0) {
            lista.innerHTML = '<div class="ures-talalat">Nincs találat: <strong>' + esc(q) + '</strong></div>';
        } else {
            lista.innerHTML = talalatok.map(t => elemHtml(t, q)).join('');
        }
        lista.style.display = 'block';
        mezo.setAttribute('aria-expanded', 'true');
    }

    function elrejt() {
        lista.style.display = 'none';
        mezo.setAttribute('aria-expanded', 'false');
        aktivIndex = -1;
    }

    function keres(q) {
        if (q === utolsoQ) return;
        utolsoQ = q;
        fetch('kereses.php?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(d => mutat(d, q))
            .catch(() => elrejt());
    }

    function aktivSet(i) {
        const elemek = lista.querySelectorAll('.talalat-elem');
        if (!elemek.length) return;
        aktivIndex = i;
        elemek.forEach((el, idx) => el.classList.toggle('aktiv', idx === aktivIndex));
        if (aktivIndex >= 0) elemek[aktivIndex].scrollIntoView({ block: 'nearest' });
    }

    mezo.addEventListener('input', function () {
        const q = mezo.value.trim();
        torloGomb.style.display = q.length > 0 ? 'flex' : 'none';
        clearTimeout(timer);
        if (q.length < 2) { elrejt(); utolsoQ = ''; return; }
        timer = setTimeout(() => keres(q), 220);
    });

    torloGomb.addEventListener('click', function () {
        mezo.value = '';
        torloGomb.style.display = 'none';
        elrejt();
        utolsoQ = '';
        mezo.focus();
    });

    mezo.addEventListener('keydown', function (e) {
        const elemek = lista.querySelectorAll('.talalat-elem');
        if (!elemek.length) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); aktivSet(Math.min(aktivIndex + 1, elemek.length - 1)); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); aktivSet(Math.max(aktivIndex - 1, -1)); }
        else if (e.key === 'Enter' && aktivIndex >= 0) { e.preventDefault(); elemek[aktivIndex].click(); }
        else if (e.key === 'Escape') { elrejt(); }
    });

    mezo.addEventListener('focus', function () {
        const q = mezo.value.trim();
        if (q.length >= 2) { utolsoQ = ''; keres(q); }
    });

    document.addEventListener('click', function (e) {
        if (!document.getElementById('kereso-kontener').contains(e.target)) elrejt();
    });
})();
</script>

<style>
.kereso-kontener {
    position: relative;
    flex: 1;
    min-width: 0;
    max-width: 420px;
    margin: 0 10px;
}
.kereso-belso {
    display: flex;
    align-items: center;
    border-radius: 25px;
    padding: 0 14px;
    height: 40px;
    gap: 8px;
    transition: box-shadow 0.2s;
}
body.sotettema .kereso-belso  { background-color: #2a2930; box-shadow: 0 0 0 1.5px transparent; }
body.vilagostema .kereso-belso { background-color: #efefef; box-shadow: 0 0 0 1.5px transparent; }
.kereso-belso:focus-within { box-shadow: 0 0 0 1.5px #5e9cff; }
.kereso-ikon { font-size: 19px; color: #888; flex-shrink: 0; pointer-events: none; }
.kereso-mezo {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    font-family: 'Quicksand', sans-serif;
    font-size: 14px;
    font-weight: 600;
    min-width: 0;
    width: 100%;
}
body.sotettema .kereso-mezo              { color: #fff; }
body.sotettema .kereso-mezo::placeholder  { color: #666; }
body.vilagostema .kereso-mezo             { color: #111; }
body.vilagostema .kereso-mezo::placeholder { color: #aaa; }
.kereso-torlo {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    color: #666;
    flex-shrink: 0;
    transition: color 0.2s;
}
.kereso-torlo:hover { color: #fff; }
body.vilagostema .kereso-torlo:hover { color: #111; }
.kereso-torlo .material-symbols-outlined { font-size: 18px; }
.talalatok-lista {
    position: absolute;
    top: calc(100% + 8px);
    left: 0; right: 0;
    border-radius: 14px;
    overflow: hidden;
    z-index: 999;
    animation: befade 0.15s ease;
}
body.sotettema .talalatok-lista  { background: #1e1d23; box-shadow: 0 8px 30px rgba(0,0,0,0.6); }
body.vilagostema .talalatok-lista { background: #fff;    box-shadow: 0 8px 30px rgba(0,0,0,0.15); }
@keyframes befade {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.talalat-elem {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    text-decoration: none;
    transition: background 0.15s;
    cursor: pointer;
}
body.sotettema .talalat-elem  { color: #fff; border-bottom: 1px solid #2a2a2a; }
body.vilagostema .talalat-elem { color: #111; border-bottom: 1px solid #eee; }
.talalat-elem:last-child { border-bottom: none; }
body.sotettema .talalat-elem:hover  { background: #2a2930; }
body.vilagostema .talalat-elem:hover { background: #f5f5f5; }
.talalat-elem.aktiv { background: rgba(94,156,255,0.12) !important; }
.talalat-kep {
    width: 42px; height: 42px;
    border-radius: 8px;
    flex-shrink: 0;
    background: #2a2a2a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    overflow: hidden;
}
.talalat-kep img { width: 42px; height: 42px; border-radius: 8px; object-fit: cover; display: block; }
.talalat-info { flex: 1; min-width: 0; }
.talalat-cim {
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.talalat-cim mark { background: none; color: #5e9cff; font-weight: 700; }
.talalat-reszlet { display: flex; align-items: center; gap: 8px; margin-top: 2px; }
.talalat-kat {
    font-size: 11px;
    background: rgba(94,156,255,0.15);
    color: #5e9cff;
    padding: 1px 8px;
    border-radius: 20px;
    font-weight: 600;
}
.talalat-ido    { font-size: 11px; color: #888; }
.talalat-szerzo { font-size: 11px; color: #666; }
.ures-talalat { padding: 18px 16px; text-align: center; font-size: 13px; color: #666; }

@media (max-width: 480px) {
    .kereso-kontener {
        margin: 0 4px;
    }
    .kereso-belso {
        padding: 0 10px;
        height: 36px;
        gap: 4px;
    }
    .kereso-ikon {
        font-size: 17px;
    }
    .kereso-mezo {
        font-size: 13px;
    }
    .talalat-kep {
        width: 34px;
        height: 34px;
    }
    .talalat-kep img {
        width: 34px;
        height: 34px;
    }
    .talalat-ido,
    .talalat-szerzo {
        display: none;
    }
}
</style>