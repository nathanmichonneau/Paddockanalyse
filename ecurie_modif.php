<?php
// connexion.php : connexion à la base MySQL (inclus au début de chaque page)

// Adresse du serveur MySQL (hébergement alwaysdata)
$serveur = 'mysql-saebdd.alwaysdata.net';
// Nom de la base de données
$base = 'saebdd_php';
// Nom de l'utilisateur MySQL
$user = 'saebdd';
// Mot de passe de l'utilisateur MySQL
$mdp = 'SAEAlixNathan$';

// try : on essaie de se connecter
try
{
    // On crée la connexion PDO ($bdd) avec les informations ci-dessus
    $bdd = new PDO('mysql:host=' . $serveur . ';dbname=' . $base . ';charset=utf8mb4', $user, $mdp);
    // En cas d'erreur SQL, PHP lève une exception (on pourra l'attraper avec try / catch)
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Chaque ligne lue est un tableau associatif : $ligne['nom_de_colonne']
    $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
// catch : si la connexion échoue, on arrive ici
catch (Exception $e)
{
    // On affiche un message d'erreur et on arrête la page
    die('Erreur de connexion à la base : ' . $e->getMessage());
}
?>
