<?php
// menu.php : barre de navigation + bouton thème clair/sombre (peut aussi être inclus dans entete.php)
$liens = ['index.php' => 'Accueil', 'circuits.php' => 'Circuits', 'ecuries.php' => 'Écuries',
          'comparaison.php' => 'Comparer', 'dashboard.php' => 'Graphiques', 'documentation.php' => 'Documentation'];
$courant = basename($_SERVER['SCRIPT_NAME']);
?>
<header class="pa-nav">
  <a class="pa-logo" href="index.php">Paddock Analyse</a>
  <nav aria-label="Navigation principale">
    <?php foreach ($liens as $url => $nom): ?>
      <a href="<?= $url ?>"<?= $url === $courant ? ' aria-current="page"' : '' ?>><?= $nom ?></a>
    <?php endforeach; ?>
    <button id="pa-theme" type="button" aria-label="Basculer entre thème clair et sombre">Thème</button>
  </nav>
</header>
<script>
document.getElementById('pa-theme').addEventListener('click', function () {
  var t = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
  document.documentElement.dataset.theme = t;
  try { localStorage.setItem('pa-theme', t); } catch (e) {}
  window.dispatchEvent(new Event('pa-theme'));
});
</script>
