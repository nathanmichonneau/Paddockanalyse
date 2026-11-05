<?php
// login.php : formulaire de connexion (variables de session, cf. cours 5.6)
session_start();
$message = '';

// Identifiants de l'administrateur : admin / f1admin2026 (mot de passe stocké chiffré)
$login_admin = 'admin';
$hash_admin = '$2y$10$ZKk.KLSdQs7eJJo4rbMVbufcod1hNMFzPd1qjclkkwfYWUolRIArK';

// Traitement du formulaire
if (isset($_POST['login']))
{
    if ($_POST['login'] == $login_admin && password_verify($_POST['mdp'], $hash_admin))
    {
        $_SESSION['admin'] = $login_admin;
    }
    else
    {
        $message = 'Identifiant ou mot de passe incorrect.';
    }
}

$titre = 'Connexion';
include("./entete.php");
?>
<h2>Connexion administrateur</h2>
<?php
if ($message != '')
{
    echo '<p class="erreur">' . $message . '</p>';
}
if (isset($_SESSION['admin']))
{
    echo '<p class="succes">Vous êtes connecté. Vous pouvez maintenant ajouter, modifier ou supprimer des écuries.</p>';
}
else
{
?>
<form action="login.php" method="post">
    <label>Identifiant : <input type="text" name="login"></label>
    <label>Mot de passe : <input type="password" name="mdp"></label>
    <input type="submit" value="Se connecter">
</form>
<?php
}
include("./pied.php");
?>
