<?php
// dashboard.php : tableau de bord de 8 indicateurs (PHP + jQuery + Highcharts)
// Principe : PHP lit les données dans MySQL et les range dans des tableaux,
// puis le JavaScript de Highcharts dessine les graphiques.
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonctions h(), lire_lignes()

// ============================================================
// Graphique 1 : nombre de victoires par écurie (top 10)
// ============================================================
$lignes = lire_lignes($bdd, "SELECT e.constructor_name AS nom, COUNT(*) AS valeur
    FROM resultat r
    JOIN ecurie e ON e.constructorId = r.constructorId
    WHERE r.position = 1
    GROUP BY e.constructorId, e.constructor_name
    ORDER BY valeur DESC
    LIMIT 10");
// Deux tableaux : les noms (axe horizontal) et les valeurs (hauteur des colonnes)
$noms1 = array();
$valeurs1 = array();
for ($i = 0; $i < count($lignes); $i++)
{
    $noms1[] = $lignes[$i]['nom'];
    $valeurs1[] = (int)$lignes[$i]['valeur'];
}

// ============================================================
// Graphique 2 : poles positions et victoires par écurie (2 séries)
// ============================================================
$lignes = lire_lignes($bdd, "SELECT e.constructor_name AS nom,
        SUM(r.grid_position = 1) AS poles,
        SUM(r.position = 1) AS victoires
    FROM resultat r
    JOIN ecurie e ON e.constructorId = r.constructorId
    GROUP BY e.constructorId, e.constructor_name
    ORDER BY victoires DESC
    LIMIT 10");
$noms2 = array();
$poles2 = array();
$victoires2 = array();
for ($i = 0; $i < count($lignes); $i++)
{
    $noms2[] = $lignes[$i]['nom'];
    $poles2[] = (int)$lignes[$i]['poles'];
    $victoires2[] = (int)$lignes[$i]['victoires'];
}

// ============================================================
// Graphique 3 : nationalité des écuries (camembert)
// ============================================================
$lignes = lire_lignes($bdd, "SELECT constructor_nationality AS nom, COUNT(*) AS valeur
    FROM ecurie
    GROUP BY constructor_nationality
    ORDER BY valeur DESC");
// Pour un camembert, chaque part est un tableau avec un nom (name) et une valeur (y)
$donnees3 = array();
// Les nationalités au-delà des 7 premières sont regroupées dans « Autres »
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

// ============================================================
// Graphique 4 : nombre de circuits par pays (top 10)
// ============================================================
$lignes = lire_lignes($bdd, "SELECT circuit_country AS nom, COUNT(*) AS valeur
    FROM circuit
    GROUP BY circuit_country
    ORDER BY valeur DESC
    LIMIT 10");
$noms4 = array();
$valeurs4 = array();
for ($i = 0; $i < count($lignes); $i++)
{
    $noms4[] = $lignes[$i]['nom'];
    $valeurs4[] = (int)$lignes[$i]['valeur'];
}

// ============================================================
// Graphique 5 : victoires selon la place sur la grille de départ
// ============================================================
$lignes = lire_lignes($bdd, "SELECT grid_position AS nom, COUNT(*) AS valeur
    FROM resultat
    WHERE position = 1 AND grid_position BETWEEN 1 AND 10
    GROUP BY grid_position
    ORDER BY grid_position");
$noms5 = array();
$valeurs5 = array();
for ($i = 0; $i < count($lignes); $i++)
{
    $noms5[] = 'Place ' . $lignes[$i]['nom'];
    $valeurs5[] = (int)$lignes[$i]['valeur'];
}

// ============================================================
// Graphique 6 : points marqués en course par écurie (top 10)
// ============================================================
$lignes = lire_lignes($bdd, "SELECT e.constructor_name AS nom, SUM(r.points) AS valeur
    FROM resultat r
    JOIN ecurie e ON e.constructorId = r.constructorId
    GROUP BY e.constructorId, e.constructor_name
    ORDER BY valeur DESC
    LIMIT 10");
$noms6 = array();
$valeurs6 = array();
for ($i = 0; $i < count($lignes); $i++)
{
    $noms6[] = $lignes[$i]['nom'];
    $valeurs6[] = (float)$lignes[$i]['valeur'];
}

// ============================================================
// Graphique 7 : nombre de courses terminées en tête du championnat des constructeurs
// ============================================================
$lignes = lire_lignes($bdd, "SELECT e.constructor_name AS nom, COUNT(*) AS valeur
    FROM classement_ecurie c
    JOIN ecurie e ON e.constructorId = c.constructorId
    WHERE c.position = 1
    GROUP BY e.constructorId, e.constructor_name
    ORDER BY valeur DESC
    LIMIT 10");
$noms7 = array();
$valeurs7 = array();
for ($i = 0; $i < count($lignes); $i++)
{
    $noms7[] = $lignes[$i]['nom'];
    $valeurs7[] = (int)$lignes[$i]['valeur'];
}

// ============================================================
// Graphique 8 : nombre de Grands Prix par saison
// ============================================================
$lignes = lire_lignes($bdd, "SELECT year AS nom, COUNT(*) AS valeur
    FROM course
    GROUP BY year
    ORDER BY year");
$noms8 = array();
$valeurs8 = array();
for ($i = 0; $i < count($lignes); $i++)
{
    $noms8[] = (string)$lignes[$i]['nom'];
    $valeurs8[] = (int)$lignes[$i]['valeur'];
}

// ----- Début de la page -----
$titre = 'Tableau de bord';
include("./entete.php");
?>
<h1>Tableau de bord</h1>
<p class="intro">
    Huit indicateurs calculés à partir de la base MySQL.
    Pour le détail d'une écurie, consultez la page <a href="ecuries.php">Écuries</a> ou <a href="comparaison.php">Comparer</a> ;
    pour les circuits, la page <a href="circuits.php">Circuits</a>.
</p>

<!-- Bibliothèques jQuery et Highcharts (chargées depuis Internet) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>

<!-- Les 8 zones où seront dessinés les graphiques -->
<section class="grille">
    <figure class="graphique" id="graph1"></figure>
    <figure class="graphique" id="graph2"></figure>
    <figure class="graphique" id="graph3"></figure>
    <figure class="graphique" id="graph4"></figure>
    <figure class="graphique" id="graph5"></figure>
    <figure class="graphique" id="graph6"></figure>
    <figure class="graphique" id="graph7"></figure>
    <figure class="graphique" id="graph8"></figure>
</section>

<script>
// jQuery : on attend que la page soit entièrement chargée avant de dessiner
$(document).ready(function ()
{
    // ----- Graphique 1 : colonnes -----
    Highcharts.chart('graph1', {
        chart: {type: 'column'},
        title: {text: 'Victoires par écurie (top 10)'},
        xAxis: {categories: <?php echo json_encode($noms1); ?>},
        yAxis: {title: {text: 'Victoires'}},
        legend: {enabled: false},
        credits: {enabled: false},
        series: [{name: 'Victoires', color: '#e10600', data: <?php echo json_encode($valeurs1); ?>}]
    });

    // ----- Graphique 2 : colonnes avec deux séries -----
    Highcharts.chart('graph2', {
        chart: {type: 'column'},
        title: {text: 'Poles positions et victoires (top 10)'},
        xAxis: {categories: <?php echo json_encode($noms2); ?>},
        yAxis: {title: {text: 'Nombre'}},
        credits: {enabled: false},
        series: [
            {name: 'Poles (1ère place sur la grille)', color: '#15151e', data: <?php echo json_encode($poles2); ?>},
            {name: 'Victoires', color: '#e10600', data: <?php echo json_encode($victoires2); ?>}
        ]
    });

    // ----- Graphique 3 : camembert -----
    Highcharts.chart('graph3', {
        chart: {type: 'pie'},
        title: {text: 'Nationalité des écuries'},
        tooltip: {pointFormat: '{point.y} écuries ({point.percentage:.1f} %)'},
        credits: {enabled: false},
        series: [{name: 'Écuries', data: <?php echo json_encode($donnees3); ?>}]
    });

    // ----- Graphique 4 : barres horizontales -----
    Highcharts.chart('graph4', {
        chart: {type: 'bar'},
        title: {text: 'Nombre de circuits par pays (top 10)'},
        xAxis: {categories: <?php echo json_encode($noms4); ?>},
        yAxis: {title: {text: 'Circuits'}},
        legend: {enabled: false},
        credits: {enabled: false},
        series: [{name: 'Circuits', color: '#15151e', data: <?php echo json_encode($valeurs4); ?>}]
    });

    // ----- Graphique 5 : courbe -----
    Highcharts.chart('graph5', {
        chart: {type: 'spline'},
        title: {text: 'Victoires selon la place sur la grille'},
        xAxis: {categories: <?php echo json_encode($noms5); ?>},
        yAxis: {title: {text: 'Victoires'}},
        legend: {enabled: false},
        credits: {enabled: false},
        series: [{name: 'Victoires', color: '#e10600', data: <?php echo json_encode($valeurs5); ?>}]
    });

    // ----- Graphique 6 : barres horizontales -----
    Highcharts.chart('graph6', {
        chart: {type: 'bar'},
        title: {text: 'Points marqués en course (top 10)'},
        xAxis: {categories: <?php echo json_encode($noms6); ?>},
        yAxis: {title: {text: 'Points'}},
        legend: {enabled: false},
        credits: {enabled: false},
        series: [{name: 'Points', color: '#e10600', data: <?php echo json_encode($valeurs6); ?>}]
    });

    // ----- Graphique 7 : aires -----
    Highcharts.chart('graph7', {
        chart: {type: 'area'},
        title: {text: 'Courses terminées en tête du championnat (top 10)'},
        xAxis: {categories: <?php echo json_encode($noms7); ?>},
        yAxis: {title: {text: 'Nombre de courses'}},
        legend: {enabled: false},
        credits: {enabled: false},
        series: [{name: 'Courses en tête', color: '#15151e', data: <?php echo json_encode($valeurs7); ?>}]
    });

    // ----- Graphique 8 : ligne -----
    Highcharts.chart('graph8', {
        chart: {type: 'line'},
        title: {text: 'Nombre de Grands Prix par saison'},
        xAxis: {categories: <?php echo json_encode($noms8); ?>, tickInterval: 5},
        yAxis: {title: {text: 'Grands Prix'}, min: 0},
        legend: {enabled: false},
        credits: {enabled: false},
        series: [{name: 'Grands Prix', color: '#e10600', data: <?php echo json_encode($valeurs8); ?>}]
    });
});
</script>
<?php
// Fermeture de la connexion
$bdd = null;
?>
