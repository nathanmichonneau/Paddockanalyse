<?php
// ecurie_update.php : enregistre les modifications d'une écurie (UPDATE)
include("./connexion.php");
include("./fonctions.php");
$titre = 'Modification d\'une écurie';
include("./entete.php");

$id = isset($_POST['id']) ? $_POST['id'] : 0;
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$nationalite = isset($_POST['nationalite']) ? trim($_POST['nationalite']) : '';
$pays = isset($_POST['pays']) ? trim($_POST['pays']) : '';

echo '<h1>Modification d\'une écurie</h1>';
if ($nom == '')
{
    echo '<p class="msg erreur">Le nom de l\'écurie est obligatoire.</p>';
}
else
{
    try
    {
        $requete = $bdd->prepare("UPDATE ecurie SET constructor_name = ?, constructor_nationality = ?, constructor_home = ? WHERE constructorId = ?");
        $requete->execute(array($nom, $nationalite, $pays, $id));
        echo '<p class="msg succes">L\'écurie « ' . h($nom) . ' » a été correctement modifiée.</p>';
    }
    catch (Exception $e)
    {
        echo '<p class="msg erreur">Attention, la modification a échoué : ' . h($e->getMessage()) . '</p>';
    }
}
$bdd = null;
?>
<p><a class="btn" href="ecuries.php">Retour à la liste des écuries</a></p>
<?php include("./pied.php"); ?>
