<?php
// ecurie_modif.php : formulaire de modification d'une écurie
// L'identifiant arrive par l'URL (méthode GET) : ecurie_modif.php?id=3
include("./connexion.php");
include("./fonctions.php");
$titre = 'Modifier une écurie';
include("./entete.php");

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
    echo '<h1>Modifier une écurie</h1>';
    echo '<p class="msg erreur">Écurie introuvable.</p>';
    echo '<p><a class="btn" href="ecuries.php">Retour à la liste des écuries</a></p>';
}
else
{
?>
<h1>Modifier l'écurie « <?php echo h($e['constructor_name']); ?> »</h1>
<form action="ecurie_update.php" method="post" class="formulaire">
    <!-- champ caché : l'identifiant ne se modifie pas -->
    <input type="hidden" name="id" value="<?php echo (int)$e['constructorId']; ?>">
    <div class="champ"><label><span>Nom</span><input type="text" name="nom" maxlength="100" required value="<?php echo h($e['constructor_name']); ?>"></label></div>
    <div class="champ"><label><span>Nationalité</span><input type="text" name="nationalite" maxlength="50" value="<?php echo h($e['constructor_nationality']); ?>"></label></div>
    <div class="champ"><label><span>Pays</span><input type="text" name="pays" maxlength="50" value="<?php echo h($e['constructor_home']); ?>"></label></div>
    <input type="submit" class="btn" value="Enregistrer les modifications">
    <a class="btn btn-sec" href="ecuries.php">Annuler</a>
</form>
<?php
}
include("./pied.php");
?>
