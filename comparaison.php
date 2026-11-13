<?php
// comparaison.php : compare deux écuries saison par saison (points ou victoires)
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonctions h(), lire_lignes(), lire_valeur()

// lire_saisons() : renvoie, pour une écurie, les points et les victoires de chaque saison
// Le tableau est classé par année : $saisons[2020]['points'], $saisons[2020]['victoires']
function lire_saisons($bdd, $idEcurie)
{
    // Tableau vide qui va contenir les saisons
    $saisons = array();
    // Requête : une ligne par année avec la somme des points et le nombre de victoires
    $requete = $bdd->prepare("SELECT c.year AS annee, SUM(r.points) AS points, SUM(r.position = 1) AS victoires
        FROM resultat r
        JOIN course c ON c.raceId = r.raceId
        WHERE r.constructorId = ?
        GROUP BY c.year
        ORDER BY c.year");
    $requete->execute(array($idEcurie));
    // On lit les lignes une par une
    $ligne = $requete->fetch();
    while ($ligne)
    {
        // On range les valeurs sous la case de l'année
        $saisons[$ligne['annee']] = array('points' => (float)$ligne['points'], 'victoires' => (int)$ligne['victoires']);
        $ligne = $requete->fetch();
    }
    return $saisons;
}

// ----- Liste des écuries pour les deux listes déroulantes -----
$listeEcuries = lire_lignes($bdd, "SELECT constructorId, constructor_name FROM ecurie ORDER BY constructor_name");

// ----- Lecture des choix dans l'adresse (méthode GET) -----
$a = 0;
$b = 0;
$indicateur = 'points';
if (isset($_GET['a']))
{
    $a = (int)$_GET['a'];
}
if (isset($_GET['b']))
{
    $b = (int)$_GET['b'];
}
if (isset($_GET['indicateur']) && $_GET['indicateur'] == 'victoires')
{
    $indicateur = 'victoires';
}

// ----- Valeurs par défaut : Ferrari contre McLaren -----
if ($a == 0)
{
    $a = lire_valeur($bdd, "SELECT constructorId FROM ecurie WHERE constructor_name = 'Ferrari' LIMIT 1");
}
if ($b == 0)
{
    $b = lire_valeur($bdd, "SELECT constructorId FROM ecurie WHERE constructor_name = 'McLaren' LIMIT 1");
}

// ----- On cherche le nom de chaque écurie dans la liste (et on vérifie qu'elle existe) -----
$nomA = '';
$nomB = '';
for ($i = 0; $i < count($listeEcuries); $i++)
{
    if ($listeEcuries[$i]['constructorId'] == $a)
    {
        $nomA = $listeEcuries[$i]['constructor_name'];
    }
    if ($listeEcuries[$i]['constructorId'] == $b)
    {
        $nomB = $listeEcuries[$i]['constructor_name'];
    }
}

// ----- Début de la page -----
$titre = 'Comparer deux écuries';
include("./entete.php");
?>
<h1>Comparer deux écuries</h1>
<p class="intro">Choisissez deux écuries et un indicateur pour comparer leurs saisons.</p>

<!-- Formulaire de choix (envoyé en GET) -->
<form action="comparaison.php" method="get" class="filtre">
    <label>Écurie A
        <select name="a">
            <?php
            for ($i = 0; $i < count($listeEcuries); $i++)
            {
                $selection = '';
                if ($listeEcuries[$i]['constructorId'] == $a)
                {
                    $selection = ' selected';
                }
                echo '<option value="' . $listeEcuries[$i]['constructorId'] . '"' . $selection . '>' . h($listeEcuries[$i]['constructor_name']) . '</option>';
            }
            ?>
        </select>
    </label>
    <label>Écurie B
        <select name="b">
            <?php
            for ($i = 0; $i < count($listeEcuries); $i++)
            {
                $selection = '';
                if ($listeEcuries[$i]['constructorId'] == $b)
                {
                    $selection = ' selected';
                }
                echo '<option value="' . $listeEcuries[$i]['constructorId'] . '"' . $selection . '>' . h($listeEcuries[$i]['constructor_name']) . '</option>';
            }
            ?>
        </select>
    </label>
    <label>Indicateur
        <select name="indicateur">
            <option value="points"<?php if ($indicateur == 'points') { echo ' selected'; } ?>>Points par saison</option>
            <option value="victoires"<?php if ($indicateur == 'victoires') { echo ' selected'; } ?>>Victoires par saison</option>
        </select>
    </label>
    <input type="submit" value="Comparer">
</form>

<?php
// Si une des deux écuries n'existe pas, on affiche une erreur
if ($nomA == '' || $nomB == '')
{
    echo '<p class="erreur">Choisissez deux écuries existantes.</p>';
}
elseif ($a == $b)
{
    echo '<p class="erreur">Choisissez deux écuries différentes pour pouvoir les comparer.</p>';
}
else
{
    // ----- Données de chaque écurie -----
    $saisonsA = lire_saisons($bdd, $a);
    $saisonsB = lire_saisons($bdd, $b);

    // ----- Première et dernière année où l'une des deux écuries a couru -----
    $requete = $bdd->prepare("SELECT MIN(c.year) AS debut, MAX(c.year) AS fin
        FROM resultat r JOIN course c ON c.raceId = r.raceId
        WHERE r.constructorId = ? OR r.constructorId = ?");
    $requete->execute(array($a, $b));
    $periode = $requete->fetch();

    // Si aucune des deux écuries n'a de résultat
    if ($periode['debut'] == null)
    {
        echo '<p class="erreur">Ces deux écuries n\'ont aucun résultat enregistré.</p>';
    }
    else
    {
        // ----- On parcourt chaque année de la période -----
        $annees = array();          // années affichées sur le graphique
        $serieA = array();          // valeurs de l'écurie A (NULL si elle n'a pas couru)
        $serieB = array();          // valeurs de l'écurie B
        $lignesTableau = array();   // lignes du tableau détaillé
        $totalPointsA = 0;
        $totalPointsB = 0;
        $totalVictoiresA = 0;
        $totalVictoiresB = 0;
        $nbSaisonsA = 0;
        $nbSaisonsB = 0;

        // On convertit les années en nombres entiers
        $debut = (int)$periode['debut'];
        $fin = (int)$periode['fin'];

        for ($an = $debut; $an <= $fin; $an++)
        {
            // L'année est ajoutée au graphique
            $annees[] = $an;

            // Valeurs de l'écurie A pour cette année (NULL par défaut)
            $valeurA = null;
            if (isset($saisonsA[$an]))
            {
                $valeurA = $saisonsA[$an][$indicateur];
                $totalPointsA = $totalPointsA + $saisonsA[$an]['points'];
                $totalVictoiresA = $totalVictoiresA + $saisonsA[$an]['victoires'];
                $nbSaisonsA = $nbSaisonsA + 1;
            }
            $serieA[] = $valeurA;

            // Valeurs de l'écurie B pour cette année (NULL par défaut)
            $valeurB = null;
            if (isset($saisonsB[$an]))
            {
                $valeurB = $saisonsB[$an][$indicateur];
                $totalPointsB = $totalPointsB + $saisonsB[$an]['points'];
                $totalVictoiresB = $totalVictoiresB + $saisonsB[$an]['victoires'];
                $nbSaisonsB = $nbSaisonsB + 1;
            }
            $serieB[] = $valeurB;

            // Si au moins une des deux écuries a couru cette année, on ajoute une ligne au tableau
            if (isset($saisonsA[$an]) || isset($saisonsB[$an]))
            {
                $lignesTableau[] = $an;
            }
        }

        // Texte de l'axe vertical du graphique
        $texteIndicateur = 'Points par saison';
        if ($indicateur == 'victoires')
        {
            $texteIndicateur = 'Victoires par saison';
        }
        ?>
        <!-- Bilan des deux écuries -->
        <table>
            <tr><th>Bilan</th><th><?php echo h($nomA); ?></th><th><?php echo h($nomB); ?></th></tr>
            <tr><td>Saisons disputées</td><td><?php echo $nbSaisonsA; ?></td><td><?php echo $nbSaisonsB; ?></td></tr>
            <tr><td>Victoires</td><td><?php echo $totalVictoiresA; ?></td><td><?php echo $totalVictoiresB; ?></td></tr>
            <tr><td>Points</td><td><?php echo $totalPointsA; ?></td><td><?php echo $totalPointsB; ?></td></tr>
            <tr><td>Voir les résultats</td>
                <td><a href="resultats.php?ecurie=<?php echo $a; ?>">Résultats de <?php echo h($nomA); ?></a></td>
                <td><a href="resultats.php?ecurie=<?php echo $b; ?>">Résultats de <?php echo h($nomB); ?></a></td></tr>
        </table>

        <!-- Graphique (Highcharts + jQuery) -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <section id="graphique-comparaison"></section>
        <script>
        // Quand la page est chargée (jQuery), on dessine le graphique
        $(document).ready(function ()
        {
            Highcharts.chart('graphique-comparaison', {
                chart: {type: 'line'},
                title: {text: '<?php echo $texteIndicateur; ?>'},
                xAxis: {categories: <?php echo json_encode($annees); ?>, title: {text: 'Saison'}},
                yAxis: {title: {text: '<?php echo $texteIndicateur; ?>'}, min: 0},
                credits: {enabled: false},
                series: [
                    {name: <?php echo json_encode($nomA); ?>, color: '#e10600', data: <?php echo json_encode($serieA); ?>},
                    {name: <?php echo json_encode($nomB); ?>, color: '#15151e', data: <?php echo json_encode($serieB); ?>}
                ]
            });
        });
        </script>

        <!-- Tableau détaillé saison par saison -->
        <h2>Saison par saison</h2>
        <table>
            <tr>
                <th>Saison</th>
                <th class="num">Points <?php echo h($nomA); ?></th><th class="num">Victoires <?php echo h($nomA); ?></th>
                <th class="num">Points <?php echo h($nomB); ?></th><th class="num">Victoires <?php echo h($nomB); ?></th>
            </tr>
            <?php
            // Pour chaque année où au moins une écurie a couru...
            for ($i = 0; $i < count($lignesTableau); $i++)
            {
                $an = $lignesTableau[$i];
                // Valeurs affichées : un tiret si l'écurie n'a pas couru cette année-là
                $ptsA = '-';
                $vicA = '-';
                $ptsB = '-';
                $vicB = '-';
                if (isset($saisonsA[$an]))
                {
                    $ptsA = $saisonsA[$an]['points'];
                    $vicA = $saisonsA[$an]['victoires'];
                }
                if (isset($saisonsB[$an]))
                {
                    $ptsB = $saisonsB[$an]['points'];
                    $vicB = $saisonsB[$an]['victoires'];
                }
                echo '<tr><td>' . $an . '</td>';
                echo '<td class="num">' . $ptsA . '</td><td class="num">' . $vicA . '</td>';
                echo '<td class="num">' . $ptsB . '</td><td class="num">' . $vicB . '</td></tr>';
            }
            ?>
        </table>
        <?php
    }
}
// Fermeture de la connexion
$bdd = null;
?>
