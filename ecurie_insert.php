<?php
// ecurie_insert.php : ajoute une écurie dans la base (requête INSERT)
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonction h()

// ----- Lecture des valeurs envoyées par le formulaire (méthode POST) -----
// Valeurs par défaut : vides
$nom = '';
$nationalite = '';
$pays = '';
// trim() enlève les espaces au début et à la fin
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
$titre = 'Ajout d\'une écurie';
include("./entete.php");
?>
<h1>Ajout d'une écurie</h1>
<?php
// Le nom est obligatoire
if ($nom == '')
{
    echo '<p class="erreur">Le nom de l\'écurie est obligatoire.</p>';
}
else
{
    // try : on essaie d'ajouter l'écurie
    try
    {
        // Requête préparée : les ? seront remplacés par les valeurs saisies
        $requete = $bdd->prepare("INSERT INTO ecurie (constructor_name, constructor_nationality, constructor_home) VALUES (?, ?, ?)");
        // On exécute la requête avec les 3 valeurs
        $requete->execute(array($nom, $nationalite, $pays));
        // Message de réussite
        echo '<p class="succes">L\'écurie « ' . h($nom) . ' » a été ajoutée.</p>';
    }
    // catch : si MySQL refuse l'ajout, on affiche l'erreur
    catch (Exception $e)
    {
        echo '<p class="erreur">L\'ajout a échoué : ' . h($e->getMessage()) . '</p>';
    }
}
?>
<!-- Liens pour continuer -->
<p>
    <a class="btn" href="ecuries.php">Retour à la liste des écuries</a>
    <a class="btn btn-gris" href="ecurie_ajout.php">Ajouter une autre écurie</a>
</p>
<?php
// Fermeture de la connexion
$bdd = null;
// Fin de la page
include("./pied.php");
?>
