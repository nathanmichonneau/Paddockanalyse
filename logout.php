<?php
// logout.php : supprime les variables de session (cf. cours 5.6 : session_unset)
session_start();
session_unset();
session_destroy();

$titre = 'Déconnexion';
include("./entete.php");
?>
<h2>Déconnexion</h2>
<p class="succes">Vous êtes déconnecté.</p>
<?php include("./pied.php"); ?>
