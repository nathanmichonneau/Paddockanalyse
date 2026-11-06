<?php
// ecurie_suppr.php : suppression d'une écurie (DELETE)
// Une écurie qui a déjà des résultats ne peut pas être supprimée (clé étrangère) :
// MySQL renvoie une erreur, que l'on affiche proprement.
include("./connexion.php");
include("./fonctions.php");
$titre = 'Suppression d\'une écurie';
include("./entete.php");

echo '<h1>Suppression d\'une écurie</h1>';
if (!isset($_POST['id']))
{
    echo '<p class="msg erreur">Aucune écurie sélectionnée : utilisez le bouton Supprimer de la liste des écuries.</p>';
}
else
{
    try
    {
        $requete = $bdd->prepare("DELETE FROM ecurie WHERE constructorId = ?");
        $requete->execute(array($_POST['id']));
        if ($requete->rowCount() > 0)
        {
            echo '<p class="msg succes">L\'écurie a été supprimée.</p>';
        }
        else
        {
            echo '<p class="msg erreur">Aucune écurie supprimée (identifiant inconnu).</p>';
        }
    }
    catch (Exception $e)
    {
        echo '<p class="msg erreur">Suppression impossible : cette écurie est utilisée par des résultats, des qualifications ou des classements.</p>';
    }
}
$bdd = null;
?>
<p><a class="btn" href="ecuries.php">Retour à la liste des écuries</a></p>
<?php include("./pied.php"); ?>
