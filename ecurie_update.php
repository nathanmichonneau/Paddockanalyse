<?php
// ecurie_update.php : enregistre les modifications d'une écurie (UPDATE, comme php09.php du cours)
include("./connexion.php");
include("./fonctions.php");
$titre = 'Modification d\'une écurie';
include("./entete.php");
include("./verif_session.php");

$id = $_POST['id'];
$nom = trim($_POST['nom']);
$nationalite = trim($_POST['nationalite']);
$pays = trim($_POST['pays']);

if ($nom == '')
{
    echo '<p class="erreur">Le nom de l\'écurie est obligatoire.</p>';
}
else
{
    try
    {
        $requete = $bdd->prepare("UPDATE ecurie SET constructor_name = ?, constructor_nationality = ?, constructor_home = ? WHERE constructorId = ?");
        $requete->execute(array($nom, $nationalite, $pays, $id));
        echo '<p class="succes">L\'écurie a été correctement modifiée.</p>';
    }
    catch (Exception $e)
    {
        echo '<p class="erreur">Attention, la modification a échoué : ' . h($e->getMessage()) . '</p>';
    }
}
$bdd = null;
?>
<p><a href="ecuries.php">Retour à la liste des écuries</a></p>
<?php include("./pied.php"); ?>
