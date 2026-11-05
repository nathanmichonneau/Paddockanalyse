<?php
// ecuries.php : liste des écuries dans un tableau HTML (SELECT) avec filtre par nationalité
// Même principe que php03.php / php04.php du cours (liste déroulante remplie depuis la base)
include("./connexion.php");
include("./fonctions.php");
$titre = 'Écuries';
include("./entete.php");

// Nationalité choisie dans la liste déroulante (méthode GET, cf. cours 4.5)
$nationalite = '';
if (isset($_GET['nationalite']))
{
    $nationalite = $_GET['nationalite'];
}
?>
<h2>Les écuries</h2>


<form action="ecuries.php" method="get">
    <label>Filtrer par nationalité :
        <select name="nationalite">
            <option value="">Toutes</option>
            <?php
            // Liste des nationalités lue dans la base
            $liste = lire_lignes($bdd, "SELECT DISTINCT constructor_nationality FROM ecurie ORDER BY constructor_nationality");
            foreach ($liste as $ligne)
            {
                if ($ligne['constructor_nationality'] == $nationalite)
                {
                    echo '<option selected value="' . h($ligne['constructor_nationality']) . '">' . h($ligne['constructor_nationality']) . '</option>';
                }
                else
                {
                    echo '<option value="' . h($ligne['constructor_nationality']) . '">' . h($ligne['constructor_nationality']) . '</option>';
                }
            }
            ?>
        </select>
    </label>
    <input type="submit" value="Afficher la liste">
</form>

<?php
// Requête : une ligne par écurie avec son nombre de participations, victoires et points
$debut = "SELECT e.constructorId, e.constructor_name, e.constructor_nationality, e.constructor_home,
                 COUNT(r.resultId) AS participations,
                 COALESCE(SUM(r.position = 1), 0) AS victoires,
                 COALESCE(SUM(r.points), 0) AS points
          FROM ecurie e LEFT JOIN resultat r ON r.constructorId = e.constructorId ";
$fin = " GROUP BY e.constructorId, e.constructor_name, e.constructor_nationality, e.constructor_home
         ORDER BY e.constructor_name";

if ($nationalite == '')
{
    $resultat = $bdd->query($debut . $fin);
}
else
{
    // Requête préparée : la valeur saisie n'est jamais collée directement dans le SQL
    $resultat = $bdd->prepare($debut . " WHERE e.constructor_nationality = ? " . $fin);
    $resultat->execute(array($nationalite));
}

$nb = 0;
?>
<table>
    <tr>
        <th>Nom</th><th>Nationalité</th><th>Pays</th>
        <th>Participations</th><th>Victoires</th><th>Points</th>
        <?php if (isset($_SESSION['admin'])) { echo '<th>Actions</th>'; } ?>
    </tr>
    <?php
    $ligne = $resultat->fetch();
    while ($ligne)
    {
        echo '<tr>';
        echo '<td>' . h($ligne['constructor_name']) . '</td>';
        echo '<td>' . h($ligne['constructor_nationality']) . '</td>';
        echo '<td>' . h($ligne['constructor_home']) . '</td>';
        echo '<td>' . $ligne['participations'] . '</td>';
        echo '<td>' . $ligne['victoires'] . '</td>';
        echo '<td>' . $ligne['points'] . '</td>';
        // Boutons réservés à l'administrateur connecté
        if (isset($_SESSION['admin']))
        {
            echo '<td>';
            echo '<a href="ecurie_modif.php?id=' . $ligne['constructorId'] . '">Modifier</a> ';
            echo '<form action="ecurie_suppr.php" method="post" style="display:inline" onsubmit="return confirm(\'Supprimer cette écurie ?\');">';
            echo '<input type="hidden" name="id" value="' . $ligne['constructorId'] . '">';
            echo '<button type="submit">Supprimer</button>';
            echo '</form>';
            echo '</td>';
        }
        echo '</tr>';
        $nb++;
        $ligne = $resultat->fetch();
    }
    ?>
</table>
<p>Il y a <?php echo $nb; ?> écuries.</p>
<?php
$bdd = null;
include("./pied.php");
?>
