<?php
// index.php : page d'accueil = contexte du projet + chiffres clés de la base
include("./connexion.php");
include("./fonctions.php");
$titre = 'Accueil';
include("./entete.php");

// Chiffres clés : une requête SELECT par indicateur (comme TD4)
$ligne = $bdd->query("SELECT COUNT(*) AS nb FROM ecurie")->fetch();
$nbEcuries = $ligne['nb'];

$ligne = $bdd->query("SELECT COUNT(*) AS nb FROM circuit")->fetch();
$nbCircuits = $ligne['nb'];

$ligne = $bdd->query("SELECT MIN(year) AS debut, MAX(year) AS fin, COUNT(*) AS nb FROM saison")->fetch();
$anneeDebut = $ligne['debut'];
$anneeFin = $ligne['fin'];
$nbSaisons = $ligne['nb'];

$ligne = $bdd->query("SELECT COUNT(DISTINCT raceId) AS nbCourses, COUNT(DISTINCT driverId) AS nbPilotes, COUNT(*) AS nbResultats FROM resultat")->fetch();
$nbCourses = $ligne['nbCourses'];
$nbPilotes = $ligne['nbPilotes'];
$nbResultats = $ligne['nbResultats'];
$bdd = null;
?>
<h2>Le championnat du monde de Formule 1 (TEST V2)</h2>
<p>
    Cette application web présente les données du championnat du monde de Formule 1
    (jeu de données OpenData « Formula 1 World Championship » issu de Kaggle).
    Elle permet de consulter les écuries et leurs résultats, de gérer la liste des écuries
    (ajout, modification, suppression) et de visualiser des indicateurs dans un tableau de bord.
</p>

<ul class="cartes">
    <li class="carte"><span class="nombre"><?php echo $anneeDebut . ' - ' . $anneeFin; ?></span>Période couverte (<?php echo $nbSaisons; ?> saisons)</li>
    <li class="carte"><span class="nombre"><?php echo $nbCourses; ?></span>Courses</li>
    <li class="carte"><span class="nombre"><?php echo $nbEcuries; ?></span>Écuries</li>
    <li class="carte"><span class="nombre"><?php echo $nbPilotes; ?></span>Pilotes</li>
    <li class="carte"><span class="nombre"><?php echo $nbCircuits; ?></span>Circuits</li>
    <li class="carte"><span class="nombre"><?php echo $nbResultats; ?></span>Résultats enregistrés</li>
</ul>

<h2>Que peut-on faire ?</h2>
<ul>
    <li><a href="ecuries.php">Écuries</a> : liste des écuries avec leurs victoires et leurs points (filtre par nationalité).</li>
    <li><a href="resultats.php">Résultats</a> : choisir une écurie et afficher ses résultats en course.</li>
    <li><a href="dashboard.php">Tableau de bord</a> : 7 indicateurs sous forme de graphiques.</li>
    <li><a href="login.php">Connexion</a> : réservé à l'administrateur pour modifier la liste des écuries.</li>
</ul>
<?php include("./pied.php"); ?>
