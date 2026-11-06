<?php
// circuits.php : carte Leaflet / OpenStreetMap des circuits (recherche + filtre par pays)
require_once __DIR__ . '/connexion.php';

// Retrouve automatiquement la connexion PDO créée par connexion.php
$pdo = null;
foreach ($GLOBALS as $valeur) {
    if ($valeur instanceof PDO) { $pdo = $valeur; break; }
}

function champ(array $ligne, array $noms) {
    foreach ($noms as $n) {
        if (isset($ligne[$n]) && $ligne[$n] !== '') return $ligne[$n];
    }
    return '';
}

$circuits = [];
$sans_coord = 0;
$erreur = null;
try {
    if (!$pdo) throw new Exception("Aucune connexion PDO trouvée dans connexion.php.");
    $lignes = $pdo->query('SELECT * FROM circuit')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($lignes as $l) {
        if (!is_numeric($l['latitude'] ?? null) || !is_numeric($l['longitude'] ?? null)) { $sans_coord++; continue; }
        $circuits[] = [
            'id'   => $l['circuitId'] ?? null,
            'nom'  => champ($l, ['name', 'circuitName', 'nom']),
            'ville'=> champ($l, ['location', 'ville', 'locality']),
            'pays' => champ($l, ['country', 'pays']),
            'lat'  => (float) $l['latitude'],
            'lng'  => (float) $l['longitude'],
        ];
    }
} catch (Throwable $e) {
    $erreur = $e->getMessage();
}

$titre = 'Circuits';
require_once __DIR__ . '/entete.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
  .carte-barre { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; margin: 1rem 0; }
  .carte-barre input, .carte-barre select { padding: .5rem; min-width: 12rem; }
  .carte-zone { display: grid; grid-template-columns: 1fr 18rem; gap: 1rem; }
  #carte { height: 560px; border: 1px solid rgba(128,128,128,.4); border-radius: 6px; }
  #liste-circuits { height: 560px; overflow-y: auto; margin: 0; padding: 0; list-style: none; border: 1px solid rgba(128,128,128,.4); border-radius: 6px; }
  #liste-circuits li { padding: .5rem .75rem; cursor: pointer; border-bottom: 1px solid rgba(128,128,128,.25); }
  #liste-circuits li:hover, #liste-circuits li:focus { background: rgba(128,128,128,.18); outline: none; }
  #liste-circuits small { display: block; opacity: .7; }
  @media (max-width: 800px) { .carte-zone { grid-template-columns: 1fr; } #carte, #liste-circuits { height: 380px; } }
</style>

<h1>Carte des circuits</h1>

<?php if ($erreur): ?>
  <p><strong>Erreur :</strong> <?= htmlspecialchars($erreur) ?></p>
  <p>Vérifie que la table <code>circuit</code> existe (<code>sql/creation_base.sql</code> puis <code>importer_donnees.php?confirmer=oui</code>).</p>
<?php elseif (!$circuits): ?>
  <p>Aucun circuit n’a de coordonnées. Exécute <code>sql/ajout_coordonnees.sql</code> dans phpMyAdmin (onglet SQL), puis recharge la page.</p>
<?php else: ?>
  <div class="carte-barre">
    <input type="search" id="recherche" placeholder="Rechercher un circuit ou une ville" aria-label="Rechercher">
    <select id="pays" aria-label="Filtrer par pays"><option value="">Tous les pays</option></select>
    <span id="compteur"></span>
    <?php if ($sans_coord): ?><span><?= (int) $sans_coord ?> circuit(s) sans coordonnées</span><?php endif; ?>
  </div>
  <div class="carte-zone">
    <div id="carte"></div>
    <ul id="liste-circuits"></ul>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
  const CIRCUITS = <?= json_encode($circuits, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  const carte = L.map('carte').setView([30, 10], 2);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18, attribution: '© contributeurs <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(carte);

  const couche = L.layerGroup().addTo(carte);
  const liste = document.getElementById('liste-circuits');
  const selPays = document.getElementById('pays');
  const champRecherche = document.getElementById('recherche');

  [...new Set(CIRCUITS.map(c => c.pays).filter(Boolean))].sort((a, b) => a.localeCompare(b))
    .forEach(p => selPays.add(new Option(p, p)));

  function afficher() {
    const q = champRecherche.value.trim().toLowerCase();
    const pays = selPays.value;
    couche.clearLayers();
    liste.innerHTML = '';
    const visibles = CIRCUITS.filter(c =>
      (!pays || c.pays === pays) && (!q || (c.nom + ' ' + c.ville + ' ' + c.pays).toLowerCase().includes(q)));

    visibles.forEach(c => {
      const marqueur = L.circleMarker([c.lat, c.lng], { radius: 7, weight: 2, fillOpacity: .7 })
        .bindPopup('<strong>' + esc(c.nom) + '</strong><br>' + esc(c.ville) + (c.ville && c.pays ? ', ' : '') + esc(c.pays))
        .addTo(couche);
      const li = document.createElement('li');
      li.tabIndex = 0;
      li.innerHTML = esc(c.nom) + '<small>' + esc([c.ville, c.pays].filter(Boolean).join(', ')) + '</small>';
      const aller = () => { carte.flyTo([c.lat, c.lng], 12); marqueur.openPopup(); };
      li.addEventListener('click', aller);
      li.addEventListener('keydown', e => { if (e.key === 'Enter') aller(); });
      liste.appendChild(li);
    });

    document.getElementById('compteur').textContent = visibles.length + ' circuit(s) affiché(s)';
    if (visibles.length) carte.fitBounds(visibles.map(c => [c.lat, c.lng]), { padding: [30, 30], maxZoom: 6 });
  }

  champRecherche.addEventListener('input', afficher);
  selPays.addEventListener('change', afficher);
  afficher();
  setTimeout(() => carte.invalidateSize(), 200);
  </script>
<?php endif; ?>

<?php require_once __DIR__ . '/pied.php'; ?>
