<?php
// dashboard.php : tableau de bord de 7 indicateurs (PHP + jQuery + HighCharts, cf. TD4)
// Principe : PHP lit les données dans MySQL et les range dans des tableaux,
// puis le JavaScript de HighCharts affiche les graphiques.
include("./connexion.php");
include("./fonctions.php");
$titre = 'Tableau de bord';
include("./entete.php");

// ------------------------------------------------------------
// 1) Nombre de victoires par écurie (top 10)
// ------------------------------------------------------------
$lignes = lire_lignes($bdd, "SELECT e.constructor_name AS nom, COUNT(*) AS valeur
    FROM resultat r JOIN ecurie e ON e.constructorId = r.constructorId
    WHERE r.position = 1
    GROUP BY e.constructorId, e.constructor_name
    ORDER BY valeur DESC LIMIT 10");
$noms1 = array();
$valeurs1 = array();
foreach ($lignes as $ligne)
{
    $noms1[] = $ligne['nom'];
    $valeurs1[] = (int)$ligne['valeur'];
}

// ------------------------------------------------------------
// 2) Poles positions et victoires par écurie (top 10 des victoires) : 2 séries comme graph2.php
// ------------------------------------------------------------
$lignes = lire_lignes($bdd, "SELECT e.constructor_name AS nom,
        SUM(r.grid_position = 1) AS poles, SUM(r.position = 1) AS victoires
    FROM resultat r JOIN ecurie e ON e.constructorId = r.constructorId
    GROUP BY e.constructorId, e.constructor_name
    ORDER BY victoires DESC LIMIT 10");
$noms2 = array();
$poles2 = array();
$victoires2 = array();
foreach ($lignes as $ligne)
{
    $noms2[] = $ligne['nom'];
    $poles2[] = (int)$ligne['poles'];
    $victoires2[] = (int)$ligne['victoires'];
}

// ------------------------------------------------------------
// 3) Répartition des écuries par nationalité (on regroupe au-delà de 7 nationalités)
// ------------------------------------------------------------
$lignes = lire_lignes($bdd, "SELECT constructor_nationality AS nom, COUNT(*) AS valeur
    FROM ecurie GROUP BY constructor_nationality ORDER BY valeur DESC");
$donnees3 = array();
$autres = 0;
for ($i = 0; $i < count($lignes); $i++)
{
    if ($i < 7)
    {
        $donnees3[] = array('name' => $lignes[$i]['nom'], 'y' => (int)$lignes[$i]['valeur']);
    }
    else
    {
        $autres = $autres + (int)$lignes[$i]['valeur'];
    }
}
if ($autres > 0)
{
    $donnees3[] = array('name' => 'Autres', 'y' => $autres);
}

// ------------------------------------------------------------
// 4) Nombre de circuits par pays (top 10)
// ------------------------------------------------------------
$lignes = lire_lignes($bdd, "SELECT circuit_country AS nom, COUNT(*) AS valeur
    FROM circuit GROUP BY circuit_country ORDER BY valeur DESC LIMIT 10");
$noms4 = array();
$valeurs4 = array();
foreach ($lignes as $ligne)
{
    $noms4[] = $ligne['nom'];
    $valeurs4[] = (int)$ligne['valeur'];
}

// ------------------------------------------------------------
// 5) Victoires selon la place sur la grille de départ (places 1 à 10)
// ------------------------------------------------------------
$lignes = lire_lignes($bdd, "SELECT grid_position AS nom, COUNT(*) AS valeur
    FROM resultat WHERE position = 1 AND grid_position BETWEEN 1 AND 10
    GROUP BY grid_position ORDER BY grid_position");
$noms5 = array();
$valeurs5 = array();
foreach ($lignes as $ligne)
{
    $noms5[] = 'Place ' . $ligne['nom'];
    $valeurs5[] = (int)$ligne['valeur'];
}

// ------------------------------------------------------------
// 6) Points marqués en course par écurie (top 10)
// ------------------------------------------------------------
$lignes = lire_lignes($bdd, "SELECT e.constructor_name AS nom, SUM(r.points) AS valeur
    FROM resultat r JOIN ecurie e ON e.constructorId = r.constructorId
    GROUP BY e.constructorId, e.constructor_name
    ORDER BY valeur DESC LIMIT 10");
$noms6 = array();
$valeurs6 = array();
foreach ($lignes as $ligne)
{
    $noms6[] = $ligne['nom'];
    $valeurs6[] = (float)$ligne['valeur'];
}

// ------------------------------------------------------------
// 7) Nombre de fois où l'écurie était leader du championnat après une course (top 10)
// ------------------------------------------------------------
$lignes = lire_lignes($bdd, "SELECT e.constructor_name AS nom, COUNT(*) AS valeur
    FROM classement_ecurie c JOIN ecurie e ON e.constructorId = c.constructorId
    WHERE c.position = 1
    GROUP BY e.constructorId, e.constructor_name
    ORDER BY valeur DESC LIMIT 10");
$noms7 = array();
$valeurs7 = array();
foreach ($lignes as $ligne)
{
    $noms7[] = $ligne['nom'];
    $valeurs7[] = (int)$ligne['valeur'];
}

// Fermeture de la connexion
$bdd = null;
?>
<!-- Bibliothèques jQuery et HighCharts (récupérées sur Internet, cf. TD4) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>

<h2>Tableau de bord</h2>
<p>Indicateurs calculés à partir de la base MySQL (résultats de course, circuits, écuries et classements).</p>

<section class="grille">
    <figure class="graphique" id="graph1"></figure>
    <figure class="graphique" id="graph2"></figure>
    <figure class="graphique" id="graph3"></figure>
    <figure class="graphique" id="graph4"></figure>
    <figure class="graphique" id="graph5"></figure>
    <figure class="graphique" id="graph6"></figure>
    <figure class="graphique" id="graph7"></figure>
</section>

<script>
// jQuery : on attend que la page soit chargée avant de créer les graphiques
$(document).ready(function ()
{
    // Graphique 1 : colonnes - victoires par écurie
    Highcharts.chart('graph1', {
        chart: {type: 'column'},
        title: {text: 'Victoires par écurie (top 10)'},
        xAxis: {categories: <?php echo json_encode($noms1); ?>},
        yAxis: {title: {text: 'Victoires'}},
        series: [{name: 'Victoires', color: '#e10600', data: <?php echo json_encode($valeurs1); ?>}]
    });

    // Graphique 2 : colonnes avec 2 séries - poles et victoires
    Highcharts.chart('graph2', {
        chart: {type: 'column'},
        title: {text: 'Poles positions et victoires (top 10 des victoires)'},
        xAxis: {categories: <?php echo json_encode($noms2); ?>},
        yAxis: {title: {text: 'Nombre'}},
        series: [
            {name: 'Poles (1ère place sur la grille)', color: '#15151e', data: <?php echo json_encode($poles2); ?>},
            {name: 'Victoires', color: '#e10600', data: <?php echo json_encode($victoires2); ?>}
        ]
    });

    // Graphique 3 : camembert - nationalité des écuries
    Highcharts.chart('graph3', {
        chart: {type: 'pie'},
        title: {text: 'Nationalité des écuries'},
        tooltip: {pointFormat: '{point.y} écuries ({point.percentage:.1f} %)'},
        series: [{name: 'Écuries', data: <?php echo json_encode($donnees3); ?>}]
    });

    // Graphique 4 : barres horizontales - circuits par pays
    Highcharts.chart('graph4', {
        chart: {type: 'bar'},
        title: {text: 'Nombre de circuits par pays (top 10)'},
        xAxis: {categories: <?php echo json_encode($noms4); ?>},
        yAxis: {title: {text: 'Circuits'}},
        series: [{name: 'Circuits', color: '#15151e', data: <?php echo json_encode($valeurs4); ?>}]
    });

    // Graphique 5 : courbe - victoires selon la place sur la grille
    Highcharts.chart('graph5', {
        chart: {type: 'spline'},
        title: {text: 'Victoires selon la place sur la grille de départ'},
        xAxis: {categories: <?php echo json_encode($noms5); ?>},
        yAxis: {title: {text: 'Victoires'}},
        series: [{name: 'Victoires', color: '#e10600', data: <?php echo json_encode($valeurs5); ?>}]
    });

    // Graphique 6 : barres horizontales - points marqués
    Highcharts.chart('graph6', {
        chart: {type: 'bar'},
        title: {text: 'Points marqués en course (top 10)'},
        xAxis: {categories: <?php echo json_encode($noms6); ?>},
        yAxis: {title: {text: 'Points'}},
        series: [{name: 'Points', color: '#e10600', data: <?php echo json_encode($valeurs6); ?>}]
    });

    // Graphique 7 : aires - courses passées en tête du championnat
    Highcharts.chart('graph7', {
        chart: {type: 'area'},
        title: {text: 'Courses terminées en tête du championnat (top 10)'},
        xAxis: {categories: <?php echo json_encode($noms7); ?>},
        yAxis: {title: {text: 'Nombre de courses'}},
        series: [{name: 'Courses en tête', color: '#15151e', data: <?php echo json_encode($valeurs7); ?>}]
    });
});
</script>
<?php include("./pied.php"); ?>
