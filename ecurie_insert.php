<?php
// ecurie_insert.php : insertion d'une écurie dans la base (INSERT)
include("./connexion.php");
include("./fonctions.php");
$titre = 'Ajout d\'une écurie';
include("./entete.php");

// Récupération des valeurs du formulaire
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$nationalite = isset($_POST['nationalite']) ? trim($_POST['nationalite']) : '';
$pays = isset($_POST['pays']) ? trim($_POST['pays']) : '';

echo '<h1>Ajout d\'une écurie</h1>';
if ($nom == '')
{
    echo '<p class="msg erreur">Le nom de l\'écurie est obligatoire.</p>';
}
else
{
    try
    {
        // Requête préparée : les ? sont remplacés par les valeurs saisies
        $requete = $bdd->prepare("INSERT INTO ecurie (constructor_name, constructor_nationality, constructor_home) VALUES (?, ?, ?)");
        $requete->execute(array($nom, $nationalite, $pays));
        echo '<p class="msg succes">L\'écurie « ' . h($nom) . ' » a été correctement ajoutée.</p>';
    }
    catch (Exception $e)
    {
        echo '<p class="msg erreur">Attention, l\'ajout de l\'écurie a échoué : ' . h($e->getMessage()) . '</p>';
    }
}
$bdd = null;
?>
<p><a class="btn" href="ecuries.php">Retour à la liste des écuries</a> <a class="btn btn-sec" href="ecurie_ajout.php">Ajouter une autre écurie</a></p>
<?php include("./pied.php"); ?>
