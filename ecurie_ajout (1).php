<?php
// ecurie_ajout.php : formulaire de saisie d'une nouvelle écurie
// Le formulaire envoie les données à ecurie_insert.php
include("./connexion.php");   // connexion à la base (nécessaire pour la structure commune)
include("./fonctions.php");   // fonction h()

// ----- Début de la page -----
$titre = 'Ajouter une écurie';
include("./entete.php");
?>
<h1>Ajouter une écurie</h1>
<p class="intro">Remplissez le formulaire puis cliquez sur « Enregistrer ». Seul le nom est obligatoire.</p>

<!-- Formulaire envoyé en POST vers ecurie_insert.php -->
<form action="ecurie_insert.php" method="post">
    <label>Nom
        <input type="text" name="nom" maxlength="100" required>
    </label>
    <label>Nationalité (exemple : British)
        <input type="text" name="nationalite" maxlength="50">
    </label>
    <label>Pays (exemple : UK)
        <input type="text" name="pays" maxlength="50">
    </label>
    <input type="submit" value="Enregistrer">
    <a class="btn btn-gris" href="ecuries.php">Annuler</a>
</form>
<?php
// Fermeture de la connexion
$bdd = null;
// Fin de la page
include("./pied.php");
?>
