<?php
// documentation.php : source des données, modèle de la base, cas d'utilisation
include("./connexion.php");
include("./fonctions.php");
$titre = 'Documentation';
include("./entete.php");

// Nombre de lignes réellement présentes dans chaque table
$tables = array(
    array('circuit', 'cleaned_circuits.csv', 'Circuits du championnat avec leur position GPS (carte)'),
    array('ecurie', 'cleaned_constructors.csv', 'Écuries (constructeurs) : table modifiable depuis le site'),
    array('saison', 'cleaned_seasons.csv', 'Saisons du championnat'),
    array('resultat', 'cleaned_results.csv', 'Un pilote dans une course : grille, arrivée, points, meilleur tour'),
    array('qualification', 'cleaned_qualifying.csv', 'Temps de qualification Q1, Q2, Q3'),
    array('classement_ecurie', 'cleaned_constructor_standings.csv', 'Classement des constructeurs après chaque course')
);
$nbLignes = array();
foreach ($tables as $t)
{
    $ligne = $bdd->query("SELECT COUNT(*) AS nb FROM " . $t[0])->fetch();
    $nbLignes[$t[0]] = $ligne['nb'];
}
$bdd = null;
?>
<div class="doc">
<h1>Documentation</h1>
<p class="intro">Projet web réalisé en BUT Science des Données (2<sup>e</sup> année, semestre 3) : une application PHP / MySQL qui présente le championnat du monde de Formule 1.</p>

<h2>Source des données</h2>
<p>
    Les données viennent du jeu de données OpenData « Formula 1 World Championship » publié sur Kaggle,
    lui-même issu de la base Ergast. Les fichiers CSV ont été nettoyés (temps convertis en secondes,
    valeurs manquantes remplacées par NULL) puis importés dans MySQL par le script <code>importer_donnees.php</code>.
</p>
<div class="table-wrap">
<table>
    <thead><tr><th>Table MySQL</th><th>Fichier source</th><th>Contenu</th><th class="num">Lignes</th></tr></thead>
    <tbody>
    <?php
    foreach ($tables as $t)
    {
        echo '<tr><td><code>' . $t[0] . '</code></td><td>' . $t[1] . '</td><td>' . $t[2] . '</td>';
        echo '<td class="num">' . number_format($nbLignes[$t[0]], 0, ',', ' ') . '</td></tr>';
    }
    ?>
    </tbody>
</table>
</div>

<h2>Modèle de données</h2>
<p>
    Les tables <code>resultat</code>, <code>qualification</code> et <code>classement_ecurie</code> sont reliées à
    <code>ecurie</code> par la clé étrangère <code>constructorId</code>. Pour cette raison, une écurie qui possède déjà des
    résultats ne peut pas être supprimée : MySQL refuse l'opération et le site affiche un message explicatif.
</p>
<p>
    Les tables <code>circuit</code> et <code>saison</code> sont indépendantes. Le jeu de données utilisé ne contient pas de table des
    courses ni des pilotes : les colonnes <code>raceId</code> et <code>driverId</code> sont donc des identifiants simples, sans liaison
    avec une autre table, ce qui explique leur affichage brut dans la page Résultats.
</p>

<h2>Indicateurs du tableau de bord</h2>
<ul>
    <li>Victoires par écurie (top 10)</li>
    <li>Poles positions et victoires par écurie</li>
    <li>Nationalité des écuries</li>
    <li>Nombre de circuits par pays</li>
    <li>Victoires selon la place sur la grille de départ</li>
    <li>Points marqués en course par écurie</li>
    <li>Courses terminées en tête du championnat des constructeurs</li>
</ul>

<h2>Cas d'utilisation</h2>
<p>Le site n'a pas d'espace de connexion : tous les visiteurs disposent des mêmes fonctionnalités, y compris l'ajout, la modification et la suppression d'écuries.</p>
<div class="diagramme">
    <img src="uml/diagramme_cas_utilisation.svg" alt="Diagramme de cas d'utilisation : le visiteur consulte l'accueil, la carte, les écuries, les résultats, le tableau de bord et la documentation, et peut ajouter, modifier ou supprimer une écurie.">
</div>

<h2>Technologies</h2>
<ul>
    <li>PHP avec PDO et requêtes préparées, base MySQL hébergée chez alwaysdata</li>
    <li>HTML, CSS (thème clair et sombre) et JavaScript, sans framework</li>
    <li>Highcharts et jQuery pour les graphiques</li>
    <li>Leaflet et OpenStreetMap pour la cartographie des circuits</li>
    <li>Déploiement automatique depuis GitHub grâce à GitHub Actions</li>
</ul>
</div>
<?php include("./pied.php"); ?>
