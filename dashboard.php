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
<!-- Bibliothèques jQuery et HighCharts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>

<h1>Tableau de bord</h1>
<p class="intro">Sept indicateurs calculés à partir de la base MySQL (résultats de course, circuits, écuries et classements). Les graphiques suivent le mode clair ou sombre.</p>

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
// Données calculées par PHP
var D = {
    n1: <?php echo json_encode($noms1); ?>, v1: <?php echo json_encode($valeurs1); ?>,
    n2: <?php echo json_encode($noms2); ?>, p2: <?php echo json_encode($poles2); ?>, v2: <?php echo json_encode($victoires2); ?>,
    d3: <?php echo json_encode($donnees3); ?>,
    n4: <?php echo json_encode($noms4); ?>, v4: <?php echo json_encode($valeurs4); ?>,
    n5: <?php echo json_encode($noms5); ?>, v5: <?php echo json_encode($valeurs5); ?>,
    n6: <?php echo json_encode($noms6); ?>, v6: <?php echo json_encode($valeurs6); ?>,
    n7: <?php echo json_encode($noms7); ?>, v7: <?php echo json_encode($valeurs7); ?>
};

// Couleurs lues dans la feuille de style (clair ou sombre)
function couleurs() {
    var s = getComputedStyle(document.documentElement);
    function v(nom) { return s.getPropertyValue(nom).trim(); }
    var sombre = document.documentElement.getAttribute('data-theme') === 'dark';
    return {
        texte: v('--text'), discret: v('--muted'), trait: v('--border'),
        accent: v('--btn'), neutre: v('--serie-neutre'),
        palette: sombre
            ? ['#ff4d42', '#9aa3b2', '#ff8a82', '#667085', '#c2352c', '#cfd4dc', '#ffb3ad', '#7d8798']
            : ['#d90b00', '#2f3744', '#f0786f', '#7a8699', '#9f0800', '#b8c0cc', '#f4a6a1', '#4b5563']
    };
}

// Options communes à tous les graphiques
function base(c, type, titre, extra) {
    var o = {
        chart: {type: type, backgroundColor: 'transparent', style: {fontFamily: 'inherit'}},
        title: {text: titre, align: 'left', style: {color: c.texte, fontSize: '15px', fontWeight: '600'}},
        credits: {enabled: false},
        legend: {itemStyle: {color: c.texte, fontWeight: '400'}, itemHoverStyle: {color: c.accent}},
        xAxis: {labels: {style: {color: c.discret}}, lineColor: c.trait, tickColor: c.trait},
        yAxis: {labels: {style: {color: c.discret}}, gridLineColor: c.trait, title: {style: {color: c.discret}}},
        tooltip: {style: {fontFamily: 'inherit'}},
        plotOptions: {series: {borderWidth: 0}}
    };
    return $.extend(true, o, extra);
}

function construire() {
    var c = couleurs();
    Highcharts.setOptions({lang: {thousandsSep: ' '}});

    Highcharts.chart('graph1', base(c, 'column', 'Victoires par écurie (top 10)', {
        xAxis: {categories: D.n1}, yAxis: {title: {text: 'Victoires'}}, legend: {enabled: false},
        series: [{name: 'Victoires', color: c.accent, data: D.v1}]
    }));

    Highcharts.chart('graph2', base(c, 'column', 'Poles positions et victoires (top 10 des victoires)', {
        xAxis: {categories: D.n2}, yAxis: {title: {text: 'Nombre'}},
        series: [
            {name: 'Poles (1ère place sur la grille)', color: c.neutre, data: D.p2},
            {name: 'Victoires', color: c.accent, data: D.v2}
        ]
    }));

    Highcharts.chart('graph3', base(c, 'pie', 'Nationalité des écuries', {
        colors: c.palette,
        tooltip: {pointFormat: '{point.y} écuries ({point.percentage:.1f} %)'},
        plotOptions: {pie: {borderWidth: 1, borderColor: 'transparent', dataLabels: {style: {color: c.texte, textOutline: 'none', fontWeight: '400'}}}},
        series: [{name: 'Écuries', data: D.d3}]
    }));

    Highcharts.chart('graph4', base(c, 'bar', 'Nombre de circuits par pays (top 10)', {
        xAxis: {categories: D.n4}, yAxis: {title: {text: 'Circuits'}}, legend: {enabled: false},
        series: [{name: 'Circuits', color: c.neutre, data: D.v4}]
    }));

    Highcharts.chart('graph5', base(c, 'spline', 'Victoires selon la place sur la grille de départ', {
        xAxis: {categories: D.n5}, yAxis: {title: {text: 'Victoires'}}, legend: {enabled: false},
        series: [{name: 'Victoires', color: c.accent, data: D.v5}]
    }));

    Highcharts.chart('graph6', base(c, 'bar', 'Points marqués en course (top 10)', {
        xAxis: {categories: D.n6}, yAxis: {title: {text: 'Points'}}, legend: {enabled: false},
        series: [{name: 'Points', color: c.accent, data: D.v6}]
    }));

    Highcharts.chart('graph7', base(c, 'area', 'Courses terminées en tête du championnat (top 10)', {
        xAxis: {categories: D.n7}, yAxis: {title: {text: 'Nombre de courses'}}, legend: {enabled: false},
        plotOptions: {area: {fillOpacity: 0.25}},
        series: [{name: 'Courses en tête', color: c.neutre, data: D.v7}]
    }));
}

// jQuery : on attend que la page soit chargée, puis on redessine quand le thème change
$(document).ready(construire);
document.addEventListener('themechange', construire);
</script>
<?php include("./pied.php"); ?>
