<?php
// ecuries.php : liste des écuries (SELECT) avec recherche, filtre par nationalité et tri
// Depuis cette page on peut aussi ajouter, modifier ou supprimer une écurie
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonctions h(), lire_lignes()...

// ----- Lecture des choix de l'utilisateur dans l'adresse (méthode GET) -----
// Valeurs par défaut : rien de choisi, tri par nom
$nationalite = '';
$recherche = '';
$tri = 'nom';
// Si une nationalité a été choisie, on la récupère
if (isset($_GET['nationalite']))
{
    $nationalite = $_GET['nationalite'];
}
// Si un texte a été recherché, on le récupère
if (isset($_GET['recherche']))
{
    $recherche = trim($_GET['recherche']);
}
// Si un tri a été choisi, on le récupère
if (isset($_GET['tri']))
{
    $tri = $_GET['tri'];
}

// ----- Construction de la condition WHERE -----
// $condition contient le texte SQL, $valeurs contient les valeurs qui remplaceront les ?
$condition = '';
$valeurs = array();
// Filtre par nationalité
if ($nationalite != '')
{
    $condition = ' WHERE e.constructor_nationality = ?';
    $valeurs[] = $nationalite;
}
// Recherche dans le nom
if ($recherche != '')
{
    // S'il y a déjà une condition on ajoute AND, sinon on commence par WHERE
    if ($condition == '')
    {
        $condition = ' WHERE ';
    }
    else
    {
        $condition = $condition . ' AND ';
    }
    $condition = $condition . 'e.constructor_name LIKE ?';
    $valeurs[] = '%' . $recherche . '%';
}

// ----- Choix de l'ordre du tableau (seules ces 4 valeurs sont acceptées) -----
$ordre = 'e.constructor_name';
if ($tri == 'victoires')
{
    $ordre = 'victoires DESC';
}
if ($tri == 'points')
{
    $ordre = 'points DESC';
}
if ($tri == 'participations')
{
    $ordre = 'participations DESC';
}

// ----- Requête : une ligne par écurie avec ses participations, victoires et points -----
$requete = $bdd->prepare("SELECT e.constructorId, e.constructor_name, e.constructor_nationality, e.constructor_home,
        COUNT(r.resultId) AS participations,
        COALESCE(SUM(r.position = 1), 0) AS victoires,
        COALESCE(SUM(r.points), 0) AS points
    FROM ecurie e
    LEFT JOIN resultat r ON r.constructorId = e.constructorId
    " . $condition . "
    GROUP BY e.constructorId, e.constructor_name, e.constructor_nationality, e.constructor_home
    ORDER BY " . $ordre);
// On exécute la requête avec les valeurs choisies
$requete->execute($valeurs);

// Liste des nationalités pour la liste déroulante
$nationalites = lire_lignes($bdd, "SELECT DISTINCT constructor_nationality FROM ecurie ORDER BY constructor_nationality");

// ----- Début de la page -----
$titre = 'Écuries';
include("./entete.php");
?>
<h1>Les écuries</h1>
<p class="intro">Participations, victoires et points de chaque écurie. Utilisez les filtres pour affiner la liste.</p>

<!-- Formulaire de filtre (envoyé en GET : les choix apparaissent dans l'adresse) -->
<form action="ecuries.php" method="get" class="filtre">
    <label>Rechercher
        <input type="search" name="recherche" value="<?php echo h($recherche); ?>" placeholder="Nom de l'écurie">
    </label>
    <label>Nationalité
        <select name="nationalite">
            <option value="">Toutes</option>
            <?php
            // Pour chaque nationalité de la base...
            for ($i = 0; $i < count($nationalites); $i++)
            {
                // ... on la sélectionne si c'est celle qui a été choisie
                $selection = '';
                if ($nationalites[$i]['constructor_nationality'] == $nationalite)
                {
                    $selection = ' selected';
                }
                echo '<option value="' . h($nationalites[$i]['constructor_nationality']) . '"' . $selection . '>' . h($nationalites[$i]['constructor_nationality']) . '</option>';
            }
            ?>
        </select>
    </label>
    <label>Trier par
        <select name="tri">
            <option value="nom"<?php if ($tri == 'nom') { echo ' selected'; } ?>>Nom</option>
            <option value="victoires"<?php if ($tri == 'victoires') { echo ' selected'; } ?>>Victoires</option>
            <option value="points"<?php if ($tri == 'points') { echo ' selected'; } ?>>Points</option>
            <option value="participations"<?php if ($tri == 'participations') { echo ' selected'; } ?>>Participations</option>
        </select>
    </label>
    <input type="submit" value="Afficher la liste">
    <a class="btn btn-gris" href="ecuries.php">Réinitialiser</a>
    <a class="btn" href="ecurie_ajout.php">Ajouter une écurie</a>
</form>

<table>
    <tr>
        <th>Écurie</th><th>Nationalité</th><th>Pays</th>
        <th class="num">Participations</th><th class="num">Victoires</th><th class="num">Points</th>
        <th>Actions</th>
    </tr>
    <?php
    // Compteur d'écuries affichées
    $nb = 0;
    // On lit la première ligne du résultat
    $ligne = $requete->fetch();
    // Tant qu'il reste une ligne...
    while ($ligne)
    {
        // Début de la ligne du tableau
        echo '<tr>';
        echo '<td>' . h($ligne['constructor_name']) . '</td>';
        echo '<td>' . h($ligne['constructor_nationality']) . '</td>';
        echo '<td>' . h($ligne['constructor_home']) . '</td>';
        echo '<td class="num">' . $ligne['participations'] . '</td>';
        echo '<td class="num">' . $ligne['victoires'] . '</td>';
        echo '<td class="num">' . $ligne['points'] . '</td>';
        // Colonne des actions : liens vers les autres pages
        echo '<td>';
        echo '<a class="btn btn-petit" href="resultats.php?ecurie=' . $ligne['constructorId'] . '">Résultats</a> ';
        echo '<a class="btn btn-petit" href="comparaison.php?a=' . $ligne['constructorId'] . '">Comparer</a> ';
        echo '<a class="btn btn-petit btn-gris" href="ecurie_modif.php?id=' . $ligne['constructorId'] . '">Modifier</a> ';
        // La suppression est un petit formulaire POST avec une confirmation
        echo '<form action="ecurie_suppr.php" method="post" onsubmit="return confirm(\'Supprimer cette écurie ?\');">';
        echo '<input type="hidden" name="id" value="' . $ligne['constructorId'] . '">';
        echo '<button type="submit" class="btn-petit">Supprimer</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
        // On compte une écurie de plus
        $nb = $nb + 1;
        // On lit la ligne suivante
        $ligne = $requete->fetch();
    }
    ?>
</table>
<p><?php echo $nb; ?> écurie(s) affichée(s).</p>
<?php
// Fermeture de la connexion
$bdd = null;
?>
