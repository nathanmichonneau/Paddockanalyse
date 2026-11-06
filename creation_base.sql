// app.js : thème clair/sombre + recherche et tri dans les tableaux
(function () {
    var racine = document.documentElement;

    function themeActuel() {
        return racine.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }
    function majBouton() {
        var b = document.getElementById('boutonTheme');
        if (b) {
            b.textContent = themeActuel() === 'dark' ? 'Mode clair' : 'Mode sombre';
        }
    }
    function appliquerTheme(t) {
        racine.setAttribute('data-theme', t);
        try { localStorage.setItem('theme', t); } catch (e) {}
        majBouton();
        document.dispatchEvent(new CustomEvent('themechange', { detail: t }));
    }

    // ---------- Tableaux : tri ----------
    function valeurCellule(td) {
        if (td.getAttribute('data-tri') !== null) { return td.getAttribute('data-tri'); }
        return td.textContent.trim();
    }
    function estNombre(v) { return /^-?\d+([.,]\d+)?$/.test(v); }
    function estVide(v) { return v === '' || v === '-'; }

    function trier(table, col, sens) {
        var tbody = table.tBodies[0];
        var lignes = Array.prototype.slice.call(tbody.rows);
        lignes.sort(function (a, b) {
            var va = valeurCellule(a.cells[col]);
            var vb = valeurCellule(b.cells[col]);
            if (estVide(va) && estVide(vb)) { return 0; }
            if (estVide(va)) { return 1; }      // les valeurs vides restent à la fin
            if (estVide(vb)) { return -1; }
            var r;
            if (estNombre(va) && estNombre(vb)) {
                r = parseFloat(va.replace(',', '.')) - parseFloat(vb.replace(',', '.'));
            } else {
                r = va.localeCompare(vb, 'fr', { numeric: true, sensitivity: 'base' });
            }
            return r * sens;
        });
        lignes.forEach(function (l) { tbody.appendChild(l); });
    }

    function initTri(table) {
        var ths = table.tHead.rows[0].cells;
        Array.prototype.forEach.call(ths, function (th, i) {
            if (th.hasAttribute('data-nosort')) { return; }
            th.setAttribute('tabindex', '0');
            function declencher() {
                var sens = th.getAttribute('aria-sort') === 'ascending' ? -1 : 1;
                Array.prototype.forEach.call(ths, function (x) { x.removeAttribute('aria-sort'); });
                th.setAttribute('aria-sort', sens === 1 ? 'ascending' : 'descending');
                trier(table, i, sens);
            }
            th.addEventListener('click', declencher);
            th.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); declencher(); }
            });
        });
    }

    // ---------- Tableaux : recherche ----------
    function initRecherche(champ) {
        var table = document.getElementById(champ.getAttribute('data-table'));
        if (!table) { return; }
        var compteur = document.querySelector('[data-compteur="' + table.id + '"]');
        function filtrer() {
            var q = champ.value.trim().toLowerCase();
            var visibles = 0;
            Array.prototype.forEach.call(table.tBodies[0].rows, function (l) {
                var ok = q === '' || l.textContent.toLowerCase().indexOf(q) !== -1;
                l.hidden = !ok;
                if (ok) { visibles++; }
            });
            if (compteur) {
                var unite = compteur.getAttribute('data-unite') || 'ligne';
                compteur.textContent = visibles + ' ' + unite + (visibles > 1 ? 's' : '');
            }
        }
        champ.addEventListener('input', filtrer);
        filtrer();
    }

    document.addEventListener('DOMContentLoaded', function () {
        majBouton();
        var b = document.getElementById('boutonTheme');
        if (b) {
            b.addEventListener('click', function () {
                appliquerTheme(themeActuel() === 'dark' ? 'light' : 'dark');
            });
        }
        Array.prototype.forEach.call(document.querySelectorAll('table.sortable'), initTri);
        Array.prototype.forEach.call(document.querySelectorAll('input[data-table]'), initRecherche);
    });
})();
