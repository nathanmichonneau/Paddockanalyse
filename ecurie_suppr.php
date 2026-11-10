<?php
// ecurie_suppr.php : supprime une écurie (requête DELETE)
// Une écurie qui a déjà des résultats ne peut pas être supprimée (clé étrangère)
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonction h()

// ----- Lecture de l'identifiant envoyé par le bouton Supprimer (méthode POST) -----
$id = 0;
if (isset($_POST['id']))
{
    $id = $_POST['id'];
}

// ----- Début de la page -----
$titre = 'Suppression d\'une écurie';
include("./entete.php");
?>
<h1>Suppression d'une écurie</h1>
<?php
// Si on arrive sur la page sans passer par le bouton Supprimer
if ($id == 0)
{
    echo '<p class="erreur">Aucune écurie sélectionnée : utilisez le bouton « Supprimer » de la liste des écuries.</p>';
}
else
{
    // On compte les résultats de cette écurie
    $requete = $bdd->prepare("SELECT COUNT(*) AS nb FROM resultat WHERE constructorId = ?");
    $requete->execute(array($id));
    $ligne = $requete->fetch();
    $nbResultats = $ligne['nb'];

    // Une écurie avec des résultats ne peut pas être supprimée
    if ($nbResultats > 0)
    {
        echo '<p class="erreur">Suppression impossible : cette écurie a ' . $nbResultats . ' résultat(s) de course enregistré(s).</p>';
    }
    else
    {
        // try : on essaie de supprimer l'écurie
        try
        {
            $requete = $bdd->prepare("DELETE FROM ecurie WHERE constructorId = ?");
            $requete->execute(array($id));
            // rowCount() donne le nombre de lignes supprimées
            if ($requete->rowCount() > 0)
            {
                echo '<p class="succes">L\'écurie a été supprimée.</p>';
            }
            else
            {
                echo '<p class="erreur">Aucune écurie supprimée : identifiant inconnu.</p>';
            }
        }
        // catch : par exemple si l'écurie est utilisée dans une autre table
        catch (Exception $e)
        {
            echo '<p class="erreur">Suppression impossible : cette écurie est utilisée dans d\'autres tables.</p>';
        }
    }
}
?>
<p><a class="btn" href="ecuries.php">Retour à la liste des écuries</a></p>
<?php
// Fermeture de la connexion
$bdd = null;
// Fin de la page
include("./pied.php");
?>
