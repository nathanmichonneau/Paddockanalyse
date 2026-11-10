<?php
// importer_donnees.php : remplit la base MySQL avec les fichiers CSV du dossier data/
// À utiliser APRÈS sql/creation_base.sql, en ouvrant : importer_donnees.php?confirmer=oui
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonction convertir_temps()

// L'import peut durer un peu : on autorise jusqu'à 5 minutes
set_time_limit(300);

// Sécurité : cette page VIDE toutes les tables, il faut donc confirmer dans l'adresse
if (php_sapi_name() != 'cli' && (!isset($_GET['confirmer']) || $_GET['confirmer'] != 'oui'))
{
    // On affiche le message et on arrête la page
    die('<p>Cette page vide puis recharge toute la base. Pour continuer : <a href="importer_donnees.php?confirmer=oui">importer_donnees.php?confirmer=oui</a></p>');
}

// importer_csv() : lit un fichier CSV et insère chaque ligne dans une table
//   $fichier        : chemin du fichier CSV
//   $table          : nom de la table MySQL
//   $colonnes       : liste des colonnes à importer (mêmes noms dans le CSV et dans la table)
//   $colonnes_temps : colonnes contenant un temps à convertir en secondes
function importer_csv($bdd, $fichier, $table, $colonnes, $colonnes_temps)
{
    // On ouvre le fichier en lecture
    $f = fopen($fichier, 'r');
    // La première ligne contient les noms des colonnes
    $entete = fgetcsv($f);
    // On retient la position de chaque colonne : array_flip échange noms et positions
    $position = array_flip($entete);

    // On prépare la requête : INSERT INTO table (a, b, c) VALUES (?, ?, ?)
    $liste = implode(', ', $colonnes);
    $points = implode(', ', array_fill(0, count($colonnes), '?'));
    $requete = $bdd->prepare("INSERT INTO " . $table . " (" . $liste . ") VALUES (" . $points . ")");

    // On regroupe tous les INSERT dans une transaction : c'est beaucoup plus rapide
    $bdd->beginTransaction();
    // Compteur de lignes importées
    $nb = 0;
    // On lit la première ligne de données
    $ligne = fgetcsv($f);
    // Tant qu'il reste une ligne dans le fichier...
    while ($ligne)
    {
        // Tableau qui va contenir les valeurs de cette ligne
        $valeurs = array();
        // Pour chaque colonne à importer...
        for ($i = 0; $i < count($colonnes); $i++)
        {
            // Nom de la colonne
            $nom = $colonnes[$i];
            // Valeur de cette colonne dans la ligne du CSV
            $valeur = $ligne[$position[$nom]];
            // Si c'est une colonne de temps, on la convertit en secondes
            if (in_array($nom, $colonnes_temps))
            {
                $valeur = convertir_temps($valeur);
            }
            // Une valeur vide devient NULL dans la base
            if ($valeur === '')
            {
                $valeur = null;
            }
            // On ajoute la valeur à la liste
            $valeurs[] = $valeur;
        }
        // On exécute l'INSERT avec ces valeurs
        $requete->execute($valeurs);
        // On compte une ligne de plus
        $nb = $nb + 1;
        // On lit la ligne suivante
        $ligne = fgetcsv($f);
    }
    // On valide la transaction
    $bdd->commit();
    // On ferme le fichier
    fclose($f);
    // On renvoie le nombre de lignes importées
    return $nb;
}

// Début de l'affichage du compte rendu
echo "<pre>Import en cours...\n";

// On désactive la vérification des clés étrangères pour vider les tables
$bdd->exec("SET FOREIGN_KEY_CHECKS = 0");
// Liste des tables à vider
$tables = array('classement_ecurie', 'qualification', 'resultat', 'course', 'pilote', 'statut', 'ecurie', 'circuit', 'saison');
// Pour chaque table de la liste...
for ($i = 0; $i < count($tables); $i++)
{
    // ... on la vide
    $bdd->exec("TRUNCATE TABLE " . $tables[$i]);
}
// On réactive la vérification des clés étrangères
$bdd->exec("SET FOREIGN_KEY_CHECKS = 1");

// Import des fichiers : les tables "parents" d'abord (celles auxquelles les autres font référence)
$n = importer_csv($bdd, 'data/cleaned_seasons.csv', 'saison',
    array('year', 'url'), array());
echo "saison : " . $n . " lignes\n";

$n = importer_csv($bdd, 'data/cleaned_circuits.csv', 'circuit',
    array('circuitId', 'circuit_name', 'circuit_location', 'circuit_country', 'circuit_nationality', 'latitude', 'longitude'), array());
echo "circuit : " . $n . " lignes\n";

$n = importer_csv($bdd, 'data/cleaned_constructors.csv', 'ecurie',
    array('constructorId', 'constructor_name', 'constructor_nationality', 'constructor_home'), array());
echo "ecurie : " . $n . " lignes\n";

$n = importer_csv($bdd, 'data/cleaned_races.csv', 'course',
    array('raceId', 'year', 'round', 'circuitId'), array());
echo "course : " . $n . " lignes\n";

$n = importer_csv($bdd, 'data/cleaned_drivers.csv', 'pilote',
    array('driverId', 'driver_code', 'driver_forename', 'driver_surname', 'driver_dob', 'driver_nationality', 'driver_home'), array());
echo "pilote : " . $n . " lignes\n";

$n = importer_csv($bdd, 'data/cleaned_status.csv', 'statut',
    array('statusId', 'status'), array());
echo "statut : " . $n . " lignes\n";

// Puis les tables "enfants"
$n = importer_csv($bdd, 'data/cleaned_results.csv', 'resultat',
    array('resultId', 'raceId', 'driverId', 'constructorId', 'grid_position', 'position', 'points',
          'laps', 'fastestLap', 'fastestLapRank', 'fastestLapTime', 'fastestLapSpeed', 'statusId'),
    array('fastestLapTime'));
echo "resultat : " . $n . " lignes\n";

$n = importer_csv($bdd, 'data/cleaned_qualifying.csv', 'qualification',
    array('qualifyId', 'raceId', 'driverId', 'constructorId', 'number', 'position', 'q1', 'q2', 'q3'),
    array('q1', 'q2', 'q3'));
echo "qualification : " . $n . " lignes\n";

$n = importer_csv($bdd, 'data/cleaned_constructor_standings.csv', 'classement_ecurie',
    array('constructorStandingsId', 'raceId', 'constructorId', 'points', 'position', 'wins'), array());
echo "classement_ecurie : " . $n . " lignes\n";

// Dans le CSV, une vitesse ou un rang de meilleur tour égal à 0 veut dire "donnée manquante"
$bdd->exec("UPDATE resultat SET fastestLapSpeed = NULL WHERE fastestLapSpeed = 0");
$bdd->exec("UPDATE resultat SET fastestLapRank = NULL WHERE fastestLapRank = 0");

// Fin du compte rendu
echo "Import terminé.</pre>";
// Fermeture de la connexion
$bdd = null;
?>
