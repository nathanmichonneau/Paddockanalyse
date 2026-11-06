<?php
// ecuries.php : liste des écuries (SELECT) avec filtre par nationalité, recherche et tri dans le tableau
// Ajout, modification et suppression sont ouverts à tous les visiteurs.
include("./connexion.php");
include("./fonctions.php");
$titre = 'Écuries';
include("./entete.php");

// Nationalité choisie dans la liste déroulante (méthode GET)
$nationalite = '';
if (isset($_GET['nationalite']))
{
    $nationalite = $_GET['nationalite'];
}
?>
<h1>Les écuries</h1>
<p class="intro">Participations, victoires et points de chaque écurie. Cliquez sur un titre de colonne pour trier.</p>

<form action="ecuries.php" method="get" class="outils">
    <div class="champ-outil">
        <label for="recherche">Rechercher</label>
        <input type="search" id="recherche" data-table="tableEcuries" placeholder="Nom, pays...">
    </div>
    <div class="champ-outil">
        <label for="nationalite">Nationalité</label>
        <select name="nationalite" id="nationalite" onchange="this.form.submit()">
            <option value="">Toutes</option>
            <?php
            // Liste des nationalités lue dans la base
            $liste = lire_lignes($bdd, "SELECT DISTINCT constructor_nationality FROM ecurie ORDER BY constructor_nationality");
            foreach ($liste as $ligne)
            {
                $selection = '';
                if ($ligne['constructor_nationality'] == $nationalite)
                {
                    $selection = ' selected';
                }
                echo '<option value="' . h($ligne['constructor_nationality']) . '"' . $selection . '>' . h($ligne['constructor_nationality']) . '</option>';
            }
            ?>
        </select>
    </div>
    <noscript><input type="submit" class="btn btn-sec" value="Filtrer"></noscript>
    <span class="compteur" data-compteur="tableEcuries" data-unite="écurie"></span>
    <a class="btn droite" href="ecurie_ajout.php">Ajouter une écurie</a>
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
?>
<div class="table-wrap">
<table class="sortable" id="tableEcuries">
    <thead>
        <tr>
            <th>Nom</th><th>Nationalité</th><th>Pays</th>
            <th class="num">Participations</th><th class="num">Victoires</th><th class="num">Points</th>
            <th data-nosort>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $ligne = $resultat->fetch();
    while ($ligne)
    {
        echo '<tr>';
        echo '<td>' . h($ligne['constructor_name']) . '</td>';
        echo '<td>' . h($ligne['constructor_nationality']) . '</td>';
        echo '<td>' . h($ligne['constructor_home']) . '</td>';
        echo '<td class="num">' . $ligne['participations'] . '</td>';
        echo '<td class="num">' . $ligne['victoires'] . '</td>';
        echo '<td class="num">' . $ligne['points'] . '</td>';
        echo '<td class="actions">';
        echo '<a class="btn btn-sec btn-sm" href="ecurie_modif.php?id=' . (int)$ligne['constructorId'] . '">Modifier</a> ';
        // Confirmation avant suppression (le nom est protégé pour être mis dans du JavaScript)
        $message = 'Supprimer l\'écurie ' . $ligne['constructor_name'] . ' ?';
        echo '<form action="ecurie_suppr.php" method="post" onsubmit="return confirm(' . h(json_encode($message)) . ');">';
        echo '<input type="hidden" name="id" value="' . (int)$ligne['constructorId'] . '">';
        echo '<button type="submit" class="btn btn-danger btn-sm">Supprimer</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
        $ligne = $resultat->fetch();
    }
    ?>
    </tbody>
</table>
</div>
<?php
$bdd = null;
include("./pied.php");
?>
