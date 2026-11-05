<?php
// ------------------------------------------------------------
// connexion.php : connexion à la base MySQL (inclus par toutes les pages)
// Hébergement alwaysdata : remplacer COMPTE, MOTDEPASSE (et le nom de la base si besoin)
// ------------------------------------------------------------
$serveur = 'mysql-COMPTE.alwaysdata.net';   // hôte MySQL indiqué dans l'admin alwaysdata
$base    = 'COMPTE_f1';                     // nom complet de la base (préfixé par le compte)
$user    = 'COMPTE';                        // utilisateur MySQL (ou COMPTE_nom)
$mdp     = 'MOTDEPASSE';                    // mot de passe MySQL

try
{
    $bdd = new PDO('mysql:host=' . $serveur . ';dbname=' . $base . ';charset=utf8mb4', $user, $mdp);
    // Affiche les erreurs SQL sous forme d'exceptions (pratique pour les try/catch)
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (Exception $e)
{
    // Message d'erreur si problème de connexion
    die('Erreur de connexion : ' . $e->getMessage());
}
?>
