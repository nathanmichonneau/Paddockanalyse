<?php
// ecurie_modif.php : formulaire pour modifier une écurie
// L'identifiant arrive dans l'adresse : ecurie_modif.php?id=3
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonction h()

// ----- Lecture de l'identifiant dans l'adresse (méthode GET) -----
$id = 0;
if (isset($_GET['id']))
{
    $id = $_GET['id'];
}

// ----- On lit l'écurie à modifier -----
$requete = $bdd->prepare("SELECT * FROM ecurie WHERE constructorId = ?");
$requete->execute(array($id));
// fetch() renvoie la ligne, ou false si l'écurie n'existe pas
$ecurie = $requete->fetch();

// ----- Début de la page -----
$titre = 'Modifier une écurie';
include("./entete.php");

// Si l'écurie n'existe pas, on affiche une erreur
if (!$ecurie)
{
    echo '<h1>Modifier une écurie</h1>';
    echo '<p class="erreur">Écurie introuvable.</p>';
    echo '<p><a class="btn" href="ecuries.php">Retour à la liste des écuries</a></p>';
}
else
{
?>
<h1>Modifier l'écurie « <?php echo h($ecurie['constructor_name']); ?> »</h1>
<p class="intro">Changez les informations puis cliquez sur « Enregistrer les modifications ».</p>

<!-- Formulaire envoyé en POST vers ecurie_update.php -->
<form action="ecurie_update.php" method="post">
    <!-- Champ caché : il transmet l'identifiant sans que l'utilisateur puisse le modifier -->
    <input type="hidden" name="id" value="<?php echo (int)$ecurie['constructorId']; ?>">
    <label>Nom
        <input type="text" name="nom" maxlength="100" required value="<?php echo h($ecurie['constructor_name']); ?>">
    </label>
    <label>Nationalité
        <input type="text" name="nationalite" maxlength="50" value="<?php echo h($ecurie['constructor_nationality']); ?>">
    </label>
    <label>Pays
        <input type="text" name="pays" maxlength="50" value="<?php echo h($ecurie['constructor_home']); ?>">
    </label>
    <input type="submit" value="Enregistrer les modifications">
    <a class="btn btn-gris" href="ecuries.php">Annuler</a>
</form>
<?php
}
// Fermeture de la connexion
$bdd = null;
// Fin de la page
include("./pied.php");
?>
