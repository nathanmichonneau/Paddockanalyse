<?php
// ------------------------------------------------------------
// importer_donnees.php : importe les fichiers CSV du dossier data/ dans MySQL
// Utilisation : ouvrir cette page dans le navigateur (après creation_base.sql)
//               ou en ligne de commande : php importer_donnees.php
// ------------------------------------------------------------
include("./connexion.php");
include("./fonctions.php");
set_time_limit(300);

// Importe un fichier CSV dans une table
//  $colonnes       : colonnes du CSV à importer (mêmes noms que dans la table)
//  $colonnes_temps : colonnes à convertir en secondes (voir fonctions.php)
function importer_csv($bdd, $fichier, $table, $colonnes, $colonnes_temps)
{
    $f = fopen($fichier, 'r');
    // Première ligne = noms des colonnes : on retient la position de chacune
    $entete = fgetcsv($f);
    $position = array_flip($entete);

    // Requête préparée : INSERT INTO table (a,b,c) VALUES (?,?,?)
    $liste = implode(',', $colonnes);
    $points = implode(',', array_fill(0, count($colonnes), '?'));
    $requete = $bdd->prepare("INSERT INTO $table ($liste) VALUES ($points)");

    $bdd->beginTransaction();
    $nb = 0;
    $ligne = fgetcsv($f);
    while ($ligne)
    {
        $valeurs = array();
        for ($i = 0; $i < count($colonnes); $i++)
        {
            $nom = $colonnes[$i];
            $valeur = $ligne[$position[$nom]];
            if (in_array($nom, $colonnes_temps))
            {
                $valeur = convertir_temps($valeur);
            }
            // Une valeur vide devient NULL dans la base
            if ($valeur === '')
            {
                $valeur = null;
            }
            $valeurs[] = $valeur;
        }
        $requete->execute($valeurs);
        $nb++;
        $ligne = fgetcsv($f);
    }
    $bdd->commit();
    fclose($f);
    return $nb;
}

echo "<pre>Import en cours...\n";

// On vide les tables (dans l'ordre inverse des clés étrangères)
$bdd->exec("SET FOREIGN_KEY_CHECKS = 0");
$tables = array('classement_ecurie', 'qualification', 'resultat', 'saison', 'ecurie', 'circuit');
for ($i = 0; $i < count($tables); $i++)
{
    $bdd->exec("TRUNCATE TABLE " . $tables[$i]);
}
$bdd->exec("SET FOREIGN_KEY_CHECKS = 1");

// Import des 6 fichiers (les écuries d'abord, car les autres tables y font référence)
$n = importer_csv($bdd, 'data/cleaned_circuits.csv', 'circuit',
    array('circuitId', 'circuit_name', 'circuit_location', 'circuit_country', 'circuit_nationality'), array());
echo "circuit : $n lignes\n";

$n = importer_csv($bdd, 'data/cleaned_constructors.csv', 'ecurie',
    array('constructorId', 'constructor_name', 'constructor_nationality', 'constructor_home'), array());
echo "ecurie : $n lignes\n";

$n = importer_csv($bdd, 'data/cleaned_seasons.csv', 'saison',
    array('year', 'url'), array());
echo "saison : $n lignes\n";

$n = importer_csv($bdd, 'data/cleaned_results.csv', 'resultat',
    array('resultId', 'raceId', 'driverId', 'constructorId', 'grid_position', 'position', 'points',
          'laps', 'fastestLap', 'fastestLapRank', 'fastestLapTime', 'fastestLapSpeed', 'statusId'),
    array('fastestLapTime'));
echo "resultat : $n lignes\n";

$n = importer_csv($bdd, 'data/cleaned_qualifying.csv', 'qualification',
    array('qualifyId', 'raceId', 'driverId', 'constructorId', 'number', 'position', 'q1', 'q2', 'q3'),
    array('q1', 'q2', 'q3'));
echo "qualification : $n lignes\n";

$n = importer_csv($bdd, 'data/cleaned_constructor_standings.csv', 'classement_ecurie',
    array('constructorStandingsId', 'raceId', 'constructorId', 'points', 'position', 'wins'), array());
echo "classement_ecurie : $n lignes\n";

// Les valeurs 0 de vitesse / rang du meilleur tour signifient "donnée manquante"
$bdd->exec("UPDATE resultat SET fastestLapSpeed = NULL WHERE fastestLapSpeed = 0");
$bdd->exec("UPDATE resultat SET fastestLapRank = NULL WHERE fastestLapRank = 0");

echo "Import terminé.</pre>";
$bdd = null;
?>
