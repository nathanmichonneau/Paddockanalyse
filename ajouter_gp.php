<?php
// ajouter_gp.php : formulaire pour ajouter un Grand Prix avec son vainqueur
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonctions h(), lire_lignes()

$message = '';

// ============================================================
// 1. Si le formulaire a été envoyé (méthode POST), on traite les données
// ============================================================
if (isset($_POST['annee']))
{
    // On lit les valeurs et on les convertit en nombres
    $annee = (int)$_POST['annee'];
    $manche = (int)$_POST['manche'];
    $circuit = (int)$_POST['circuit'];
    $pilote = (int)$_POST['pilote'];
    $ecurie = (int)$_POST['ecurie'];
    $grille = (int)$_POST['grille'];
    $tours = (int)$_POST['tours'];
    $points = (float)$_POST['points'];

    // Meilleur tour : minutes + secondes -> secondes (NULL si on n'a rien rempli)
    $temps = null;
    if ($_POST['secondes'] != '')
    {
        $temps = (int)$_POST['minutes'] * 60 + (float)$_POST['secondes'];
    }

    // On vérifie que cette manche n'existe pas déjà pour cette année
    $verif = $bdd->prepare("SELECT COUNT(*) AS nb FROM course WHERE year = ? AND round = ?");
    $verif->execute(array($annee, $manche));
    $ligne = $verif->fetch();

    if ($manche < 1)
    {
        $message = 'Erreur : le numéro de manche doit être au moins 1.';
    }
    elseif ($ligne['nb'] > 0)
    {
        $message = 'Erreur : cette manche existe déjà pour cette année.';
    }
    else
    {
        // Les id ne sont pas automatiques : on prend le plus grand + 1
        $ligne = $bdd->query("SELECT MAX(raceId) AS m FROM course")->fetch();
        $idCourse = $ligne['m'] + 1;
        $ligne = $bdd->query("SELECT MAX(resultId) AS m FROM resultat")->fetch();
        $idResultat = $ligne['m'] + 1;

        // try / catch : si une des deux insertions échoue, on annule tout
        try
        {
            $bdd->beginTransaction();

            // Insertion du Grand Prix
            $req = $bdd->prepare("INSERT INTO course (raceId, year, round, circuitId) VALUES (?, ?, ?, ?)");
            $req->execute(array($idCourse, $annee, $manche, $circuit));

            // Insertion du vainqueur (position 1, statut 1 = Finished)
            $req = $bdd->prepare("INSERT INTO resultat
                (resultId, raceId, driverId, constructorId, grid_position, position, points, laps, fastestLapTime, statusId)
                VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, 1)");
            $req->execute(array($idResultat, $idCourse, $pilote, $ecurie, $grille, $points, $tours, $temps));

            $bdd->commit();
            $message = 'Grand Prix ajouté !';
        }
        catch (Exception $e)
        {
            $bdd->rollBack();
            $message = 'Erreur : ' . $e->getMessage();
        }
    }
}

// ============================================================
// 2. Listes pour les menus déroulants
// ============================================================
$saisons = lire_lignes($bdd, "SELECT year FROM saison ORDER BY year DESC");
$circuits = lire_lignes($bdd, "SELECT circuitId, circuit_name FROM circuit ORDER BY circuit_name");
$pilotes = lire_lignes($bdd, "SELECT driverId, driver_forename, driver_surname FROM pilote ORDER BY driver_surname");
$ecuries = lire_lignes($bdd, "SELECT constructorId, constructor_name FROM ecurie ORDER BY constructor_name");

// ----- Début de la page -----
$titre = 'Ajouter un Grand Prix';
include("./entete.php");
?>
<h1>Ajouter un Grand Prix</h1>
<p class="intro">Choisissez la saison, le circuit et renseignez le vainqueur de la course.</p>

<?php
// Message de réussite ou d'erreur
if ($message != '')
{
    echo '<p><strong>' . h($message) . '</strong></p>';
}
?>

<form action="ajouter_gp.php" method="post" class="filtre">

    <h2>Le Grand Prix</h2>
    <label>Saison
        <select name="annee">
            <?php
            foreach ($saisons as $s)
            {
                echo '<option value="' . $s['year'] . '">' . $s['year'] . '</option>';
            }
            ?>
        </select>
    </label>

    <label>N° de manche
        <input type="number" name="manche" min="1" required>
    </label>

    <label>Circuit
        <select name="circuit">
            <?php
            foreach ($circuits as $c)
            {
                echo '<option value="' . $c['circuitId'] . '">' . h($c['circuit_name']) . '</option>';
            }
            ?>
        </select>
    </label>

    <h2>Le vainqueur</h2>
    <label>Pilote
        <select name="pilote">
            <?php
            foreach ($pilotes as $p)
            {
                echo '<option value="' . $p['driverId'] . '">' . h($p['driver_forename'] . ' ' . $p['driver_surname']) . '</option>';
            }
            ?>
        </select>
    </label>

    <label>Écurie
        <select name="ecurie">
            <?php
            foreach ($ecuries as $e)
            {
                echo '<option value="' . $e['constructorId'] . '">' . h($e['constructor_name']) . '</option>';
            }
            ?>
        </select>
    </label>

    <label>Place sur la grille
        <input type="number" name="grille" min="1" required>
    </label>

    <label>Nombre de tours
        <input type="number" name="tours" min="1" required>
    </label>

    <label>Points marqués
        <input type="number" name="points" step="0.5" min="0" value="25" required>
    </label>

    <label>Meilleur tour (minutes)
        <input type="number" name="minutes" min="0" value="1">
    </label>

    <label>Meilleur tour (secondes, ex : 27.452)
        <input type="number" name="secondes" step="0.001" min="0" max="59.999">
    </label>

    <input type="submit" value="Ajouter le Grand Prix">
</form>
<?php
// Fermeture de la connexion
$bdd = null;
?>
