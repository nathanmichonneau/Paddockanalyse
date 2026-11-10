<?php
// entete.php : début commun à TOUTES les pages (titre de l'onglet + menu)
// Avant de l'inclure, chaque page définit la variable $titre

// Si la page n'a pas défini de titre, on en met un par défaut
if (!isset($titre))
{
    $titre = 'Paddock Analyse';
}

// Nom du fichier de la page en cours (exemple : ecuries.php)
$page = basename($_SERVER['SCRIPT_NAME']);

// Les pages ecurie_ajout.php, ecurie_modif.php... font partie de la rubrique « Écuries »
if (substr($page, 0, 7) == 'ecurie_')
{
    $page = 'ecuries.php';
}

// lien_menu() : affiche un lien du menu ; le lien de la page en cours est mis en valeur
function lien_menu($fichier, $texte, $page)
{
    // Si c'est la page en cours, on ajoute la classe "actif"
    if ($fichier == $page)
    {
        echo '<a class="actif" href="' . $fichier . '">' . $texte . '</a>';
    }
    else
    {
        echo '<a href="' . $fichier . '">' . $texte . '</a>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <!-- Pour que le site s'adapte aux téléphones -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h($titre); ?> - Paddock Analyse</title>
    <!-- Feuille de style commune à toutes les pages -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <a class="logo" href="index.php">Paddock Analyse</a>
    <nav>
        <?php
        // Le même menu s'affiche sur toutes les pages
        lien_menu('index.php', 'Accueil', $page);
        lien_menu('ecuries.php', 'Écuries', $page);
        lien_menu('circuits.php', 'Circuits', $page);
        lien_menu('resultats.php', 'Résultats', $page);
        lien_menu('comparaison.php', 'Comparer', $page);
        lien_menu('dashboard.php', 'Tableau de bord', $page);
        lien_menu('documentation.php', 'Documentation', $page);
        ?>
    </nav>
</header>
<main>
