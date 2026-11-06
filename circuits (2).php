<?php
// circuits.php : carte interactive des circuits avec filtres (pays, décennie, nombre de Grands Prix)
require_once __DIR__ . '/outils.php';
$pdo = obtenir_pdo();
$circuits = []; $sans = 0; $erreur = null;
try {
    if (!$pdo) throw new Exception("Aucune connexion PDO trouvée dans connexion.php.");
    [$circuits, $sans] = donnees_circuits($pdo);
} catch (Throwable $ex) { $erreur = $ex->getMessage(); }

$titre = 'Circuits';
$head_extra = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">';
require __DIR__ . '/haut.php';
?>
<h1>Carte des circuits</h1>
<?php if ($erreur): ?>
  <p><strong>Erreur :</strong> <?= e($erreur) ?></p>
<?php elseif (!$circuits): ?>
  <p>Aucun circuit n’a de coordonnées. Exécute <code>sql/ajout_coordonnees.sql</code> dans phpMyAdmin (onglet SQL), puis recharge la page.</p>
<?php else: ?>
  <div class="pa-barre">
    <label>Recherche<input type="search" id="recherche" placeholder="Circuit, ville ou pays"></label>
    <label>Pays<select id="pays"><option value="">Tous</option></select></label>
    <label>Période<select id="decennie"><option value="">Toutes</option></select></label>
    <label>Grands Prix minimum : <span id="min-gp-val">0</span><input type="range" id="min-gp" min="0" max="80" value="0"></label>
    <label>Taille des cercles<select id="taille"><option value="gp">Nombre de Grands Prix</option><option value="fixe">Identique</option></select></label>
    <button type="button" class="pa-btn" id="reinit">Réinitialiser</button>
  </div>
  <p><span id="compteur"></span><?= $sans ? ' · ' . (int) $sans . ' circuit(s) sans coordonnées' : '' ?></p>
  <div class="pa-zone">
    <div id="carte" class="pa-carte"></div>
    <ul id="liste-circuits" class="pa-liste"></ul>
  </div>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="carte.js"></script>
  <script>
    document.getElementById('taille').value = 'gp';
    paCarte(<?= json_encode($circuits, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>);
  </script>
<?php endif; ?>
<?php require __DIR__ . '/bas.php'; ?>
