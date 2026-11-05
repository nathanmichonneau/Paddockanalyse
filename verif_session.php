<?php
// verif_session.php : à inclure dans les pages réservées (ajout / modif / suppression)
// Si l'utilisateur n'est pas connecté, on affiche un message et on arrête la page
if (!isset($_SESSION['admin']))
{
    echo '<p class="erreur">Accès réservé : <a href="login.php">connectez-vous</a> pour modifier les données.</p>';
    include("./pied.php");
    exit;
}
?>
