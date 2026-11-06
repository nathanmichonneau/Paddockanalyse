<?php
// index.php : page d'accueil = contexte du projet, chiffres clés de la base et mini-carte des circuits
include("./connexion.php");
include("./fonctions.php");
$titre = 'Accueil';
$head_extra = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">';
include("./entete.php");

// Chiffres clés : une requête SELECT par indicateur
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

$circuits = lire_circuits($bdd);
$bdd = null;
?>
<h1>Le championnat du monde de Formule 1</h1>
<p class="intro">
    Cette application présente les données du championnat du monde de Formule 1
    (jeu de données OpenData « Formula 1 World Championship » issu de Kaggle) :
    écuries, résultats, circuits et indicateurs. La liste des écuries peut être consultée et modifiée par tous les visiteurs.
</p>

<ul class="chiffres">
    <li class="chiffre"><b><?php echo $anneeDebut . '–' . $anneeFin; ?></b><span>Période (<?php echo $nbSaisons; ?> saisons)</span></li>
    <li class="chiffre"><b><?php echo number_format($nbCourses, 0, ',', ' '); ?></b><span>Courses</span></li>
    <li class="chiffre"><b><?php echo number_format($nbEcuries, 0, ',', ' '); ?></b><span>Écuries</span></li>
    <li class="chiffre"><b><?php echo number_format($nbPilotes, 0, ',', ' '); ?></b><span>Pilotes</span></li>
    <li class="chiffre"><b><?php echo number_format($nbCircuits, 0, ',', ' '); ?></b><span>Circuits</span></li>
    <li class="chiffre"><b><?php echo number_format($nbResultats, 0, ',', ' '); ?></b><span>Résultats enregistrés</span></li>
</ul>

<h2>Où se sont disputées les courses</h2>
<?php if ($circuits === null) { ?>
    <p class="msg erreur">La carte nécessite les coordonnées GPS des circuits : exécutez le script <code>sql/ajout_coordonnees.sql</code> dans phpMyAdmin.</p>
<?php } else { ?>
    <div id="carteAccueil" class="carte carte-mini" role="img" aria-label="Carte des circuits de Formule 1"></div>
    <p><a href="circuits.php">Ouvrir la carte complète avec recherche et filtre par pays</a></p>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script src="js/carte.js"></script>
    <script>
        creerCarte('carteAccueil', <?php echo json_encode($circuits, JSON_HEX_TAG | JSON_HEX_AMP); ?>, {rayon: 5});
    </script>
<?php } ?>

<h2>Que peut-on faire ?</h2>
<ul>
    <li><a href="ecuries.php">Écuries</a> : liste avec victoires et points, recherche, tri, ajout, modification et suppression.</li>
    <li><a href="circuits.php">Circuits</a> : carte interactive et liste des circuits par pays.</li>
    <li><a href="resultats.php">Résultats</a> : choisir une écurie et afficher ses résultats en course.</li>
    <li><a href="dashboard.php">Tableau de bord</a> : 7 indicateurs sous forme de graphiques.</li>
    <li><a href="documentation.php">Documentation</a> : source des données, modèle de la base et cas d'utilisation.</li>
</ul>
<?php include("./pied.php"); ?>
