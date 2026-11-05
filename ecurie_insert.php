<?php
// ecurie_insert.php : insertion d'une écurie dans la base (INSERT, comme php06.php du cours)
include("./connexion.php");
include("./fonctions.php");
$titre = 'Ajout d\'une écurie';
include("./entete.php");
include("./verif_session.php");

// Récupération des valeurs du formulaire
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
        // Requête préparée : les ? sont remplacés par les valeurs saisies
        $requete = $bdd->prepare("INSERT INTO ecurie (constructor_name, constructor_nationality, constructor_home) VALUES (?, ?, ?)");
        $requete->execute(array($nom, $nationalite, $pays));
        echo '<p class="succes">L\'écurie a été correctement ajoutée.</p>';
    }
    catch (Exception $e)
    {
        echo '<p class="erreur">Attention, l\'ajout de l\'écurie a échoué : ' . h($e->getMessage()) . '</p>';
    }
}
$bdd = null;
?>
<p><a href="ecuries.php">Retour à la liste des écuries</a></p>
<?php include("./pied.php"); ?>
