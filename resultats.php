<?php
// resultats.php : choix d'une écurie dans une liste déroulante, puis affichage de ses résultats
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
<h1>Résultats d'une écurie</h1>
<p class="intro">Choisissez une écurie pour afficher ses 100 résultats les plus récents.</p>

<form action="resultats.php" method="post" class="outils">
    <div class="champ-outil">
        <label for="ecurie">Écurie</label>
        <select name="ecurie" id="ecurie" onchange="this.form.submit()">
            <?php
            // Seules les écuries qui ont au moins un résultat sont proposées
            $liste = lire_lignes($bdd, "SELECT DISTINCT e.constructorId, e.constructor_name FROM ecurie e JOIN resultat r ON r.constructorId = e.constructorId ORDER BY e.constructor_name");
            foreach ($liste as $ligne)
            {
                $selection = '';
                if ($ligne['constructorId'] == $idEcurie)
                {
                    $selection = ' selected';
                }
                echo '<option value="' . (int)$ligne['constructorId'] . '"' . $selection . '>' . h($ligne['constructor_name']) . '</option>';
            }
            ?>
        </select>
    </div>
    <input type="submit" class="btn" value="Afficher les résultats">
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
    <div class="outils">
        <div class="champ-outil">
            <label for="rechercheResultats">Rechercher</label>
            <input type="search" id="rechercheResultats" data-table="tableResultats" placeholder="Course, pilote...">
        </div>
        <span class="compteur" data-compteur="tableResultats" data-unite="résultat"></span>
    </div>
    <div class="table-wrap">
    <table class="sortable" id="tableResultats">
        <thead>
            <tr>
                <th class="num">Course (raceId)</th><th class="num">Pilote (driverId)</th><th class="num">Place sur la grille</th>
                <th class="num">Arrivée</th><th class="num">Points</th><th class="num">Tours</th><th class="num">Meilleur tour</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $ligne = $requete->fetch();
        while ($ligne)
        {
            $tri = '';
            if ($ligne['fastestLapTime'] !== null)
            {
                $tri = $ligne['fastestLapTime'];
            }
            echo '<tr>';
            echo '<td class="num">' . $ligne['raceId'] . '</td>';
            echo '<td class="num">' . $ligne['driverId'] . '</td>';
            echo '<td class="num">' . $ligne['grid_position'] . '</td>';
            echo '<td class="num">' . $ligne['position'] . '</td>';
            echo '<td class="num">' . $ligne['points'] . '</td>';
            echo '<td class="num">' . $ligne['laps'] . '</td>';
            echo '<td class="num" data-tri="' . $tri . '">' . afficher_temps($ligne['fastestLapTime']) . '</td>';
            echo '</tr>';
            $ligne = $requete->fetch();
        }
        ?>
        </tbody>
    </table>
    </div>
    <?php
}
$bdd = null;
include("./pied.php");
?>
