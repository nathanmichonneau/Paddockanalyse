<?php
// ecurie_modif.php : affiche le formulaire de modification d'une écurie (comme php08.php du cours)
// L'identifiant de l'écurie arrive par l'URL (méthode GET) : ecurie_modif.php?id=3
include("./connexion.php");
include("./fonctions.php");
$titre = 'Modifier une écurie';
include("./entete.php");
include("./verif_session.php");

$id = 0;
if (isset($_GET['id']))
{
    $id = $_GET['id'];
}

// On lit l'écurie à modifier
$requete = $bdd->prepare("SELECT * FROM ecurie WHERE constructorId = ?");
$requete->execute(array($id));
$e = $requete->fetch();
$bdd = null;

if (!$e)
{
    echo '<p class="erreur">Écurie introuvable.</p>';
}
else
{
?>
<h2>Modification d'une écurie</h2>
<form action="ecurie_update.php" method="post">
    <!-- champ caché : l'identifiant ne se modifie pas (cf. cours 4.1) -->
    <input type="hidden" name="id" value="<?php echo $e['constructorId']; ?>">
    <label>Nom : <input type="text" name="nom" maxlength="100" required value="<?php echo h($e['constructor_name']); ?>"></label>
    <label>Nationalité : <input type="text" name="nationalite" maxlength="50" value="<?php echo h($e['constructor_nationality']); ?>"></label>
    <label>Pays : <input type="text" name="pays" maxlength="50" value="<?php echo h($e['constructor_home']); ?>"></label>
    <input type="submit" value="Modifier">
    <input type="reset" value="Annuler">
</form>
<?php
}
include("./pied.php");
?>
