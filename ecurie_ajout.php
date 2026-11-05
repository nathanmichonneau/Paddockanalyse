<?php
// ecurie_ajout.php : formulaire de saisie d'une écurie (comme php05.php du cours)
// Le formulaire appelle ecurie_insert.php
$titre = 'Ajouter une écurie';
include("./entete.php");
include("./verif_session.php");
?>
<h2>Saisie d'une écurie</h2>
<form action="ecurie_insert.php" method="post">
    <label>Nom : <input type="text" name="nom" maxlength="100" required></label>
    <label>Nationalité (ex : British) : <input type="text" name="nationalite" maxlength="50"></label>
    <label>Pays (ex : UK) : <input type="text" name="pays" maxlength="50"></label>
    <input type="submit" value="Enregistrer">
    <input type="reset" value="Annuler">
</form>
<?php include("./pied.php"); ?>
