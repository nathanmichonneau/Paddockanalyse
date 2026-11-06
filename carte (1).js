// carte.js : carte Leaflet des circuits. Utilisée par circuits.php (avec filtres) et index.php (mini-carte).
function paCarte(circuits, opts) {
  opts = opts || {};
  const $ = id => document.getElementById(id);
  const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const carte = L.map(opts.id || 'carte', { scrollWheelZoom: !opts.mini }).setView([30, 10], 2);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18, attribution: '© contributeurs <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(carte);
  const couche = L.layerGroup().addTo(carte);
  const liste = $('liste-circuits');

  // Options des filtres (pays, décennies) construites à partir des données
  if ($('pays')) [...new Set(circuits.map(c => c.pays).filter(Boolean))].sort((a, b) => a.localeCompare(b))
    .forEach(p => $('pays').add(new Option(p, p)));
  if ($('decennie')) [...new Set(circuits.flatMap(c => c.dec))].sort()
    .forEach(d => $('decennie').add(new Option('Années ' + d, d)));
  if ($('min-gp')) $('min-gp').max = Math.max(1, ...circuits.map(c => c.nb));

  const val = id => ($(id) ? $(id).value : '');
  const rayon = c => val('taille') === 'gp' ? 5 + Math.sqrt(c.nb) * 2.4 : 7;
  const periode = c => c.nb ? c.nb + ' Grand' + (c.nb > 1 ? 's' : '') + ' Prix, de ' + c.a0 + ' à ' + c.a1 : 'Aucun Grand Prix enregistré';

  function afficher() {
    const q = val('recherche').trim().toLowerCase(), pays = val('pays'), dec = val('decennie'), min = Number(val('min-gp') || 0);
    couche.clearLayers();
    if (liste) liste.innerHTML = '';
    const visibles = circuits.filter(c =>
      (!pays || c.pays === pays) && (!dec || c.dec.includes(Number(dec))) && c.nb >= min &&
      (!q || (c.nom + ' ' + c.ville + ' ' + c.pays).toLowerCase().includes(q)));

    visibles.forEach(c => {
      const m = L.circleMarker([c.lat, c.lng], { radius: rayon(c), weight: 2, color: '#d8200f', fillColor: '#d8200f', fillOpacity: .55 })
        .bindPopup('<strong>' + esc(c.nom) + '</strong><br>' + esc([c.ville, c.pays].filter(Boolean).join(', ')) + '<br>' + esc(periode(c)))
        .addTo(couche);
      if (!liste) return;
      const li = document.createElement('li');
      li.tabIndex = 0;
      li.innerHTML = esc(c.nom) + '<small>' + esc([c.ville, c.pays].filter(Boolean).join(', ')) + ' · ' + esc(c.nb) + ' GP</small>';
      const aller = () => { carte.flyTo([c.lat, c.lng], 12); m.openPopup(); };
      li.addEventListener('click', aller);
      li.addEventListener('keydown', e => { if (e.key === 'Enter') aller(); });
      liste.appendChild(li);
    });
    if ($('compteur')) $('compteur').textContent = visibles.length + ' circuit(s) affiché(s)';
    if ($('min-gp-val')) $('min-gp-val').textContent = min;
    if (visibles.length) carte.fitBounds(visibles.map(c => [c.lat, c.lng]), { padding: [30, 30], maxZoom: opts.mini ? 3 : 6 });
  }

  ['recherche', 'pays', 'decennie', 'min-gp', 'taille'].forEach(id => { if ($(id)) $(id).addEventListener(id === 'recherche' || id === 'min-gp' ? 'input' : 'change', afficher); });
  if ($('reinit')) $('reinit').addEventListener('click', () => {
    ['recherche', 'pays', 'decennie', 'min-gp'].forEach(id => { if ($(id)) $(id).value = ''; });
    if ($('taille')) $('taille').value = 'gp';
    afficher();
  });
  afficher();
  setTimeout(() => carte.invalidateSize(), 200);
}
