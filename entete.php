<?php
// entete.php : début de page commun (menu, thème, feuille de style)
// Chaque page peut définir $titre (titre de l'onglet) et $head_extra (balises supplémentaires dans <head>)
if (!isset($titre))
{
    $titre = 'Formule 1';
}
if (!isset($head_extra))
{
    $head_extra = '';
}
$page = basename($_SERVER['SCRIPT_NAME']);
if (strpos($page, 'ecurie_') === 0)
{
    $page = 'ecuries.php';   // les pages d'ajout / modification restent rattachées à « Écuries »
}
$menu = array(
    'index.php' => 'Accueil',
    'ecuries.php' => 'Écuries',
    'circuits.php' => 'Circuits',
    'resultats.php' => 'Résultats',
    'dashboard.php' => 'Tableau de bord',
    'documentation.php' => 'Documentation'
);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'); ?> - F1 Data</title>
    <script>
    // Thème appliqué avant l'affichage pour éviter un flash
    try {
        var t = localStorage.getItem('theme');
        if (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) { t = 'dark'; }
        if (t) { document.documentElement.setAttribute('data-theme', t); }
    } catch (e) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <?php echo $head_extra; ?>
    <script src="js/app.js" defer></script>
</head>
<body>
<header class="site-header">
    <div class="barre">
        <a class="logo" href="index.php">F1 Data</a>
        <nav class="menu" aria-label="Navigation principale">
            <?php
            foreach ($menu as $fichier => $libelle)
            {
                $classe = '';
                if ($fichier == $page)
                {
                    $classe = ' class="actif" aria-current="page"';
                }
                echo '<a href="' . $fichier . '"' . $classe . '>' . $libelle . '</a>';
            }
            ?>
        </nav>
        <button type="button" class="bouton-theme" id="boutonTheme">Mode sombre</button>
    </div>
</header>
<main>
