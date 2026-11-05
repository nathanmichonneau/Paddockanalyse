<?php
// resultats.php : choix d'une écurie dans une liste déroulante, puis affichage de ses résultats
// (même principe que ListeServ.html / php04.php du cours, regroupés dans un seul fichier)
include("./connexion.php");
include("./fonctions.php");
$titre = 'Résultats';
include("./entete.php");

// Écurie choisie (0 = aucune pour l'instant)
$idEcurie = 0;
if (isset($_POST['ecurie']))
{
    $idEcurie = $_POST['ecurie'];
}
?>
<h2>Résultats d'une écurie</h2>

<form action="resultats.php" method="post">
    <label>Sélectionnez l'écurie souhaitée :
        <select name="ecurie">
            <?php
            // Seules les écuries qui ont au moins un résultat sont proposées
            $liste = lire_lignes($bdd, "SELECT DISTINCT e.constructorId, e.constructor_name FROM ecurie e JOIN resultat r ON r.constructorId = e.constructorId ORDER BY e.constructor_name");
            foreach ($liste as $ligne)
            {
                if ($ligne['constructorId'] == $idEcurie)
                {
                    echo '<option selected value="' . $ligne['constructorId'] . '">' . h($ligne['constructor_name']) . '</option>';
                }
                else
                {
                    echo '<option value="' . $ligne['constructorId'] . '">' . h($ligne['constructor_name']) . '</option>';
                }
            }
            ?>
        </select>
    </label>
    <input type="submit" value="Afficher les résultats">
</form>

<?php
// On vérifie que l'écurie existe avant d'afficher quoi que ce soit
$ecurieExiste = false;
if ($idEcurie != 0)
{
    $requete = $bdd->prepare("SELECT constructor_name FROM ecurie WHERE constructorId = ?");
    $requete->execute(array($idEcurie));
    $ecurie = $requete->fetch();
    if ($ecurie)
    {
        $ecurieExiste = true;
    }
}
if ($idEcurie != 0 && $ecurieExiste)
{
    // Les 100 derniers résultats de l'écurie (les plus récents ont les raceId les plus grands)
    $requete = $bdd->prepare("SELECT raceId, driverId, grid_position, position, points, laps, fastestLapTime
                              FROM resultat WHERE constructorId = ?
                              ORDER BY raceId DESC, position LIMIT 100");
    $requete->execute(array($idEcurie));
    ?>
    <h2><?php echo h($ecurie['constructor_name']); ?></h2>
    <p>Les 100 résultats les plus récents.</p>
    <table>
        <tr><th>Course (raceId)</th><th>Pilote (driverId)</th><th>Place sur la grille</th><th>Arrivée</th><th>Points</th><th>Tours</th><th>Meilleur tour</th></tr>
        <?php
        $nb = 0;
        $ligne = $requete->fetch();
        while ($ligne)
        {
            echo '<tr>';
            echo '<td>' . $ligne['raceId'] . '</td>';
            echo '<td>' . $ligne['driverId'] . '</td>';
            echo '<td>' . $ligne['grid_position'] . '</td>';
            echo '<td>' . $ligne['position'] . '</td>';
            echo '<td>' . $ligne['points'] . '</td>';
            echo '<td>' . $ligne['laps'] . '</td>';
            echo '<td>' . afficher_temps($ligne['fastestLapTime']) . '</td>';
            echo '</tr>';
            $nb++;
            $ligne = $requete->fetch();
        }
        ?>
    </table>
    <p>Il y a <?php echo $nb; ?> résultats affichés.</p>
    <?php
}
$bdd = null;
include("./pied.php");
?>
