<?php
// index.php : page d'accueil = chiffres clés, top 5 des écuries et liens vers les autres pages
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonctions h(), lire_lignes(), lire_valeur()

// ----- Chiffres clés : un SELECT par chiffre -----
$nbSaisons = lire_valeur($bdd, "SELECT COUNT(*) FROM saison");
$premiereAnnee = lire_valeur($bdd, "SELECT MIN(year) FROM saison");
$derniereAnnee = lire_valeur($bdd, "SELECT MAX(year) FROM saison");
$nbCourses = lire_valeur($bdd, "SELECT COUNT(*) FROM course");
$nbEcuries = lire_valeur($bdd, "SELECT COUNT(*) FROM ecurie");
$nbPilotes = lire_valeur($bdd, "SELECT COUNT(*) FROM pilote");
$nbCircuits = lire_valeur($bdd, "SELECT COUNT(*) FROM circuit");

// ----- Les 5 écuries qui ont le plus de victoires -----
$top5 = lire_lignes($bdd, "SELECT e.constructorId, e.constructor_name, COUNT(*) AS victoires
    FROM resultat r
    JOIN ecurie e ON e.constructorId = r.constructorId
    WHERE r.position = 1
    GROUP BY e.constructorId, e.constructor_name
    ORDER BY victoires DESC
    LIMIT 5");

// ----- Début de la page -----
$titre = 'Accueil';
include("./entete.php");
?>
<h1>Le championnat du monde de Formule 1 en données</h1>
<p class="intro">
    Paddock Analyse présente les données de la Formule 1 depuis <?php echo $premiereAnnee; ?> :
    écuries, circuits, pilotes et résultats de course. Vous pouvez consulter les données,
    comparer deux écuries, voir les circuits sur une carte et gérer la liste des écuries.
</p>

<!-- Chiffres clés -->
<section class="cartes">
    <p class="carte"><span class="nombre"><?php echo $premiereAnnee . ' - ' . $derniereAnnee; ?></span>Période (<?php echo $nbSaisons; ?> saisons)</p>
    <p class="carte"><span class="nombre"><?php echo $nbCourses; ?></span>Grands Prix</p>
    <p class="carte"><span class="nombre"><?php echo $nbEcuries; ?></span>Écuries</p>
    <p class="carte"><span class="nombre"><?php echo $nbPilotes; ?></span>Pilotes</p>
    <p class="carte"><span class="nombre"><?php echo $nbCircuits; ?></span>Circuits</p>
</section>

<!-- Top 5 des écuries -->
<h2>Les 5 écuries les plus victorieuses</h2>
<table>
    <tr><th>Écurie</th><th class="num">Victoires</th><th>Pour en savoir plus</th></tr>
    <?php
    // Pour chaque écurie du top 5...
    for ($i = 0; $i < count($top5); $i++)
    {
        // ... on affiche une ligne avec un lien vers ses résultats
        echo '<tr>';
        echo '<td>' . h($top5[$i]['constructor_name']) . '</td>';
        echo '<td class="num">' . $top5[$i]['victoires'] . '</td>';
        echo '<td><a href="resultats.php?ecurie=' . $top5[$i]['constructorId'] . '">Voir ses résultats</a></td>';
        echo '</tr>';
    }
    ?>
</table>

<!-- Liens vers les autres pages -->
<h2>Explorer le site</h2>
<section class="liens">
    <a href="ecuries.php">Écuries<small>Liste, recherche, ajout, modification, suppression</small></a>
    <a href="circuits.php">Circuits<small>Carte du monde et nombre de Grands Prix</small></a>
    <a href="resultats.php">Résultats<small>Les courses d'une écurie, saison par saison</small></a>
    <a href="comparaison.php">Comparer<small>Deux écuries face à face</small></a>
    <a href="dashboard.php">Tableau de bord<small>8 graphiques sur la Formule 1</small></a>
    <a href="documentation.php">Documentation<small>Source des données et modèle de la base</small></a>
</section>
<?php
// Fermeture de la connexion
$bdd = null;
// Fin de la page
include("./pied.php");
?>
