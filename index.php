<?php
// index.php : accueil avec chiffres clés et mini-carte des circuits
require_once __DIR__ . '/outils.php';
$pdo = obtenir_pdo();
$circuits = []; $cles = []; $erreur = null;
try {
    if (!$pdo) throw new Exception("Aucune connexion PDO trouvée dans connexion.php.");
    [$circuits, $sans] = donnees_circuits($pdo);
    $t = trouver_table($pdo, ['constructorId', 'name']);
    if ($t) $cles['Écuries'] = (int) $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $cles['Circuits'] = count($circuits) + $sans;
    $t = trouver_table($pdo, ['raceId', 'year', 'round']);
    if ($t) {
        $cles['Grands Prix'] = (int) $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        $cles['Saisons'] = (int) $pdo->query("SELECT COUNT(DISTINCT year) FROM `$t`")->fetchColumn();
    }
} catch (Throwable $ex) { $erreur = $ex->getMessage(); }

$titre = 'Accueil';
$head_extra = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">';
require __DIR__ . '/haut.php';
?>
<h1>Le championnat du monde de Formule 1 en données</h1>
<?php if ($erreur): ?>
  <p><strong>Erreur :</strong> <?= e($erreur) ?></p>
<?php else: ?>
  <div class="pa-chiffres">
    <?php foreach ($cles as $nom => $n): ?><div><strong><?= number_format($n, 0, ',', ' ') ?></strong><span><?= e($nom) ?></span></div><?php endforeach; ?>
  </div>
  <?php if ($circuits): ?>
    <div id="carte" class="pa-carte mini"></div>
    <p><a href="circuits.php">Ouvrir la carte complète avec les filtres</a></p>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="carte.js"></script>
    <script>paCarte(<?= json_encode($circuits, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>, { mini: true });</script>
  <?php else: ?>
    <p>La carte apparaîtra dès que les coordonnées des circuits seront dans la base (<code>sql/ajout_coordonnees.sql</code>).</p>
  <?php endif; ?>
<?php endif; ?>
<div class="pa-liens">
  <a href="circuits.php">Circuits<small>Carte filtrable par pays et période</small></a>
  <a href="ecuries.php">Écuries<small>Liste, ajout, modification</small></a>
  <a href="comparaison.php">Comparer<small>Deux écuries, saison par saison</small></a>
  <a href="dashboard.php">Graphiques<small>Tableau de bord</small></a>
</div>
<?php require __DIR__ . '/bas.php'; ?>
