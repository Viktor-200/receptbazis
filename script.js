document.addEventListener('DOMContentLoaded', () => {
    const menuGomb  = document.getElementById('menugomb');
    const bezarGomb = document.getElementById('bezargomb');
    const oldalmenu = document.getElementById('oldalmenu');

    const temaGomb   = document.getElementById('temavalto-gomb');
    const temaIkon   = document.getElementById('tema-ikon');
    const temaSzoveg = document.getElementById('tema-szoveg');

    if (menuGomb && oldalmenu) {
        menuGomb.addEventListener('click', e => {
            e.stopPropagation();
            oldalmenu.classList.add('nyitva');
        });
    }

    if (bezarGomb) {
        bezarGomb.addEventListener('click', () => oldalmenu.classList.remove('nyitva'));
    }

    document.addEventListener('click', e => {
        if (oldalmenu.classList.contains('nyitva')
            && !oldalmenu.contains(e.target)
            && e.target !== menuGomb) {
            oldalmenu.classList.remove('nyitva');
        }
    });

    function sotetTema() {
        const logo = document.getElementById('logo-kep');
        document.body.classList.remove('vilagostema');
        document.body.classList.add('sotettema');
        temaIkon.textContent   = 'light_mode';
        temaSzoveg.textContent = 'Világos mód';
        if (logo) logo.src = 'img/feketelogo.png';
    }

    function vilagosTema() {
        const logo = document.getElementById('logo-kep');
        document.body.classList.remove('sotettema');
        document.body.classList.add('vilagostema');
        temaIkon.textContent   = 'moon_stars';
        temaSzoveg.textContent = 'Sötét mód';
        if (logo) logo.src = 'img/feherlogo.png';
    }

    function temaAlkalmaz(t) {
        t === 'vilagostema' ? vilagosTema() : sotetTema();
        localStorage.setItem('tema', t);
    }

    if (temaGomb) {
        temaGomb.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            const jelenlegi = document.body.classList.contains('vilagostema') ? 'vilagostema' : 'sotettema';
            temaAlkalmaz(jelenlegi === 'sotettema' ? 'vilagostema' : 'sotettema');
        });
    }

    temaAlkalmaz(localStorage.getItem('tema') || 'sotettema');
});

function orissit() {
    const most = new Date();
    const szoveg = [most.getHours(), most.getMinutes(), most.getSeconds()]
        .map(n => String(n).padStart(2, '0'))
        .join(':');

    const el = document.getElementById('valosidejuora');
    if (el) el.textContent = szoveg;
}

orissit();
setInterval(orissit, 1000);
