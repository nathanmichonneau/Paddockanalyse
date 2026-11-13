<?php
// ecurie_update.php : enregistre les modifications d'une écurie (requête UPDATE)
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonction h()

// ----- Lecture des valeurs envoyées par le formulaire (méthode POST) -----
$id = 0;
$nom = '';
$nationalite = '';
$pays = '';
if (isset($_POST['id']))
{
    $id = $_POST['id'];
}
if (isset($_POST['nom']))
{
    $nom = trim($_POST['nom']);
}
if (isset($_POST['nationalite']))
{
    $nationalite = trim($_POST['nationalite']);
}
if (isset($_POST['pays']))
{
    $pays = trim($_POST['pays']);
}

// ----- Début de la page -----
$titre = 'Modification d\'une écurie';
include("./entete.php");
?>
<h1>Modification d'une écurie</h1>
<?php
// Le nom est obligatoire
if ($nom == '')
{
    echo '<p class="erreur">Le nom de l\'écurie est obligatoire.</p>';
}
else
{
    // try : on essaie de modifier l'écurie
    try
    {
        // Requête préparée : les ? seront remplacés par les valeurs
        $requete = $bdd->prepare("UPDATE ecurie SET constructor_name = ?, constructor_nationality = ?, constructor_home = ? WHERE constructorId = ?");
        // On exécute la requête
        $requete->execute(array($nom, $nationalite, $pays, $id));
        // Message de réussite
        echo '<p class="succes">L\'écurie « ' . h($nom) . ' » a été modifiée.</p>';
    }
    // catch : si MySQL refuse la modification, on affiche l'erreur
    catch (Exception $e)
    {
        echo '<p class="erreur">La modification a échoué : ' . h($e->getMessage()) . '</p>';
    }
}
?>
<!-- Liens pour continuer -->
<p>
    <a class="btn" href="ecuries.php">Retour à la liste des écuries</a>
    <a class="btn btn-gris" href="resultats.php?ecurie=<?php echo (int)$id; ?>">Voir les résultats de cette écurie</a>
</p>
<?php
// Fermeture de la connexion
$bdd = null;
?>
