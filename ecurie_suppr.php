<?php
// ecurie_suppr.php : suppression d'une écurie (DELETE)
// Une écurie qui a déjà des résultats ne peut pas être supprimée (clé étrangère) :
// MySQL renvoie une erreur, que l'on affiche proprement.
include("./connexion.php");
include("./fonctions.php");
$titre = 'Suppression d\'une écurie';
include("./entete.php");
include("./verif_session.php");

$id = $_POST['id'];

try
{
    $requete = $bdd->prepare("DELETE FROM ecurie WHERE constructorId = ?");
    $requete->execute(array($id));
    if ($requete->rowCount() > 0)
    {
        echo '<p class="succes">L\'écurie a été supprimée.</p>';
    }
    else
    {
        echo '<p class="erreur">Aucune écurie supprimée (identifiant inconnu).</p>';
    }
}
catch (Exception $e)
{
    echo '<p class="erreur">Suppression impossible : cette écurie est utilisée par des résultats, des qualifications ou des classements.</p>';
}
$bdd = null;
?>
<p><a href="ecuries.php">Retour à la liste des écuries</a></p>
<?php include("./pied.php"); ?>
