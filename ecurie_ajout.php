<?php
// ecurie_ajout.php : formulaire de saisie d'une écurie, qui appelle ecurie_insert.php
$titre = 'Ajouter une écurie';
include("./entete.php");
?>
<h1>Ajouter une écurie</h1>
<form action="ecurie_insert.php" method="post" class="formulaire">
    <div class="champ"><label><span>Nom</span><input type="text" name="nom" maxlength="100" required></label></div>
    <div class="champ"><label><span>Nationalité (ex : British)</span><input type="text" name="nationalite" maxlength="50"></label></div>
    <div class="champ"><label><span>Pays (ex : UK)</span><input type="text" name="pays" maxlength="50"></label></div>
    <input type="submit" class="btn" value="Enregistrer">
    <a class="btn btn-sec" href="ecuries.php">Annuler</a>
</form>
<?php include("./pied.php"); ?>
