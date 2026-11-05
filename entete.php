<?php
// entete.php : début de page commun (menu + feuille de style)
// La session doit être démarrée avant tout affichage (cf. cours 5.6)
if (session_status() == PHP_SESSION_NONE)
{
    session_start();
}
// Chaque page peut définir $titre avant d'inclure ce fichier
if (!isset($titre))
{
    $titre = 'Formule 1';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $titre; ?> - F1 Data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>F1 Data</h1>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="ecuries.php">Écuries</a>
        <a href="resultats.php">Résultats</a>
        <a href="dashboard.php">Tableau de bord</a>
        <?php if (isset($_SESSION['admin'])) { ?>
            <a href="ecurie_ajout.php">Ajouter une écurie</a>
            <a href="logout.php">Déconnexion</a>
        <?php } else { ?>
            <a href="login.php">Connexion</a>
        <?php } ?>
    </nav>
</header>
<main>
