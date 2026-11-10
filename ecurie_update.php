<?php
// documentation.php : source des données, modèle de la base, cas d'utilisation
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonctions h(), lire_valeur()

// Liste des tables : nom de la table, fichier CSV d'origine, description
$tables = array(
    array('saison', 'cleaned_seasons.csv', 'Les saisons du championnat (une ligne par année)'),
    array('circuit', 'cleaned_circuits.csv', 'Les circuits, avec leur position GPS (carte)'),
    array('ecurie', 'cleaned_constructors.csv', 'Les écuries : table modifiable depuis le site'),
    array('course', 'cleaned_races.csv', 'Les Grands Prix : année, manche et circuit'),
    array('pilote', 'cleaned_drivers.csv', 'Les pilotes : nom, date de naissance, nationalité'),
    array('statut', 'cleaned_status.csv', 'Les statuts d\'arrivée (Finished, Engine, Accident...)'),
    array('resultat', 'cleaned_results.csv', 'Un pilote dans une course : grille, arrivée, points, meilleur tour'),
    array('qualification', 'cleaned_qualifying.csv', 'Les temps de qualification Q1, Q2 et Q3'),
    array('classement_ecurie', 'cleaned_constructor_standings.csv', 'Le classement des constructeurs après chaque course')
);

// Pour chaque table, on compte les lignes réellement présentes dans la base
$nbLignes = array();
for ($i = 0; $i < count($tables); $i++)
{
    $nbLignes[$i] = lire_valeur($bdd, "SELECT COUNT(*) FROM " . $tables[$i][0]);
}

// ----- Début de la page -----
$titre = 'Documentation';
include("./entete.php");
?>
<h1>Documentation</h1>
<p class="intro">
    Projet web réalisé en BUT Science des Données (2<sup>e</sup> année, semestre 3) :
    une application PHP / MySQL / JavaScript qui présente le championnat du monde de Formule 1.
</p>

<h2>Source des données</h2>
<p>
    Les données viennent du jeu de données OpenData « Formula 1 World Championship » publié sur Kaggle
    (issu de la base Ergast). Les fichiers CSV ont été nettoyés (temps convertis en secondes, valeurs manquantes
    remplacées par NULL) puis importés dans MySQL par le script <code>importer_donnees.php</code>.
</p>
<table>
    <tr><th>Table MySQL</th><th>Fichier source</th><th>Contenu</th><th class="num">Lignes</th></tr>
    <?php
    // Pour chaque table de la liste...
    for ($i = 0; $i < count($tables); $i++)
    {
        echo '<tr>';
        echo '<td><code>' . $tables[$i][0] . '</code></td>';
        echo '<td>' . $tables[$i][1] . '</td>';
        echo '<td>' . h($tables[$i][2]) . '</td>';
        echo '<td class="num">' . number_format($nbLignes[$i], 0, ',', ' ') . '</td>';
        echo '</tr>';
    }
    ?>
</table>

<h2>Modèle de données</h2>
<ul>
    <li>Une <code>course</code> se déroule sur un <code>circuit</code> et pendant une <code>saison</code>.</li>
    <li>Un <code>resultat</code> relie une <code>course</code>, un <code>pilote</code>, une <code>ecurie</code> et un <code>statut</code>.</li>
    <li>Les tables <code>qualification</code> et <code>classement_ecurie</code> sont reliées à la <code>course</code> et à l'<code>ecurie</code>.</li>
</ul>
<p>
    Ces liens sont des clés étrangères : une écurie qui a déjà des résultats ne peut donc pas être supprimée
    (le site affiche alors un message explicatif). Seules les écuries ajoutées à la main, sans résultat, peuvent être supprimées.
</p>

<h2>Pages du site</h2>
<ul>
    <li><a href="ecuries.php">Écuries</a> : liste avec recherche, filtre et tri ; ajout, modification et suppression.</li>
    <li><a href="circuits.php">Circuits</a> : carte Leaflet / OpenStreetMap et nombre de Grands Prix.</li>
    <li><a href="resultats.php">Résultats</a> : les courses d'une écurie, saison par saison.</li>
    <li><a href="comparaison.php">Comparer</a> : deux écuries face à face, avec un graphique.</li>
    <li><a href="dashboard.php">Tableau de bord</a> : 8 graphiques Highcharts.</li>
</ul>

<h2>Cas d'utilisation</h2>
<p>Le site n'a pas d'espace de connexion : il n'y a qu'un seul acteur, le visiteur, qui peut tout consulter et gérer les écuries.</p>
<p class="diagramme">
    <img src="uml/diagramme_cas_utilisation.svg" alt="Diagramme de cas d'utilisation : le visiteur consulte l'accueil, les écuries, les circuits, les résultats, la comparaison, le tableau de bord et la documentation, et peut ajouter, modifier ou supprimer une écurie.">
</p>

<h2>Technologies</h2>
<ul>
    <li>PHP (PDO, requêtes préparées) et base MySQL hébergée chez alwaysdata</li>
    <li>HTML et CSS (site adapté aux téléphones)</li>
    <li>JavaScript : jQuery et Highcharts pour les graphiques, Leaflet pour la carte</li>
    <li>Déploiement automatique depuis GitHub grâce à GitHub Actions</li>
</ul>
<?php
// Fermeture de la connexion
$bdd = null;
// Fin de la page
include("./pied.php");
?>
