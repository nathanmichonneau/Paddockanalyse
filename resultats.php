<?php
// resultats.php : choisir une écurie (puis une saison) et afficher ses résultats de course
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonctions h(), lire_lignes(), afficher_temps()

// ----- Lecture des choix de l'utilisateur dans l'adresse (méthode GET) -----
$idEcurie = 0;
$annee = 0;
// Écurie choisie (0 = aucune pour l'instant)
if (isset($_GET['ecurie']))
{
    $idEcurie = (int)$_GET['ecurie'];
}
// Saison choisie (0 = toutes les saisons)
if (isset($_GET['annee']))
{
    $annee = (int)$_GET['annee'];
}

// Liste de toutes les écuries pour la liste déroulante
$listeEcuries = lire_lignes($bdd, "SELECT constructorId, constructor_name FROM ecurie ORDER BY constructor_name");

// ----- Si une écurie est choisie, on lit ses informations -----
$ecurie = false;
if ($idEcurie != 0)
{
    $requete = $bdd->prepare("SELECT constructorId, constructor_name, constructor_nationality FROM ecurie WHERE constructorId = ?");
    $requete->execute(array($idEcurie));
    // fetch() renvoie la ligne, ou false si l'écurie n'existe pas
    $ecurie = $requete->fetch();
}

// ----- Début de la page -----
$titre = 'Résultats';
include("./entete.php");
?>
<h1>Résultats d'une écurie</h1>
<p class="intro">Choisissez une écurie pour voir ses résultats course par course : pilote, place sur la grille, arrivée, points et statut.</p>

<!-- Étape 1 : choix de l'écurie -->
<form action="resultats.php" method="get" class="filtre">
    <label>Écurie
        <select name="ecurie">
            <?php
            // Pour chaque écurie de la base...
            for ($i = 0; $i < count($listeEcuries); $i++)
            {
                // ... on la sélectionne si c'est celle qui a été choisie
                $selection = '';
                if ($listeEcuries[$i]['constructorId'] == $idEcurie)
                {
                    $selection = ' selected';
                }
                echo '<option value="' . $listeEcuries[$i]['constructorId'] . '"' . $selection . '>' . h($listeEcuries[$i]['constructor_name']) . '</option>';
            }
            ?>
        </select>
    </label>
    <input type="submit" value="Afficher les résultats">
</form>

<?php
// On n'affiche la suite que si l'écurie existe
if ($ecurie)
{
    // ----- Liste des saisons de cette écurie -----
    $requete = $bdd->prepare("SELECT DISTINCT c.year FROM resultat r JOIN course c ON c.raceId = r.raceId WHERE r.constructorId = ? ORDER BY c.year DESC");
    $requete->execute(array($idEcurie));
    $saisons = array();
    $ligne = $requete->fetch();
    while ($ligne)
    {
        $saisons[] = $ligne['year'];
        $ligne = $requete->fetch();
    }

    // ----- Condition WHERE commune aux statistiques et au tableau -----
    $condition = ' WHERE r.constructorId = ?';
    $valeurs = array($idEcurie);
    // Si une saison est choisie, on ajoute une condition sur l'année
    if ($annee != 0)
    {
        $condition = $condition . ' AND c.year = ?';
        $valeurs[] = $annee;
    }

    // ----- Statistiques de l'écurie (sur la saison choisie ou sur toutes) -----
    $requete = $bdd->prepare("SELECT COUNT(*) AS participations, COALESCE(SUM(r.position = 1), 0) AS victoires, COALESCE(SUM(r.points), 0) AS points
        FROM resultat r JOIN course c ON c.raceId = r.raceId" . $condition);
    $requete->execute($valeurs);
    $stats = $requete->fetch();

    // ----- Résultats détaillés -----
    // Sans saison choisie, on limite à 50 lignes pour garder une page lisible
    $limite = '';
    if ($annee == 0)
    {
        $limite = ' LIMIT 50';
    }
    $requete = $bdd->prepare("SELECT c.year, c.round, ci.circuitId, ci.circuit_name,
            p.driver_forename, p.driver_surname, q.position AS qualif,
            r.grid_position, r.position, r.points, s.status, r.fastestLapTime
        FROM resultat r
        JOIN course c ON c.raceId = r.raceId
        JOIN circuit ci ON ci.circuitId = c.circuitId
        JOIN pilote p ON p.driverId = r.driverId
        JOIN statut s ON s.statusId = r.statusId
        LEFT JOIN qualification q ON q.raceId = r.raceId AND q.driverId = r.driverId
        " . $condition . "
        ORDER BY c.year DESC, c.round DESC, r.position" . $limite);
    $requete->execute($valeurs);
    ?>
    <h2><?php echo h($ecurie['constructor_name']); ?> (<?php echo h($ecurie['constructor_nationality']); ?>)</h2>

    <!-- Étape 2 : choix de la saison (l'écurie est transmise dans un champ caché) -->
    <form action="resultats.php" method="get" class="filtre">
        <input type="hidden" name="ecurie" value="<?php echo $idEcurie; ?>">
        <label>Saison
            <select name="annee">
                <option value="0">Toutes (50 derniers résultats)</option>
                <?php
                // Pour chaque saison de l'écurie...
                for ($i = 0; $i < count($saisons); $i++)
                {
                    // ... on la sélectionne si c'est celle qui a été choisie
                    $selection = '';
                    if ($saisons[$i] == $annee)
                    {
                        $selection = ' selected';
                    }
                    echo '<option value="' . $saisons[$i] . '"' . $selection . '>' . $saisons[$i] . '</option>';
                }
                ?>
            </select>
        </label>
        <input type="submit" value="Afficher la saison">
        <a class="btn btn-gris" href="ecuries.php">Retour aux écuries</a>
        <a class="btn" href="comparaison.php?a=<?php echo $idEcurie; ?>">Comparer cette écurie</a>
    </form>

    <!-- Statistiques -->
    <section class="cartes">
        <p class="carte"><span class="nombre"><?php echo $stats['participations']; ?></span>Participations</p>
        <p class="carte"><span class="nombre"><?php echo $stats['victoires']; ?></span>Victoires</p>
        <p class="carte"><span class="nombre"><?php echo $stats['points']; ?></span>Points</p>
    </section>

    <?php
    // Si l'écurie n'a aucun résultat (écurie ajoutée à la main par exemple)
    if ($stats['participations'] == 0)
    {
        echo '<p class="erreur">Aucun résultat enregistré pour cette écurie.</p>';
    }
    else
    {
    ?>
    <table>
        <tr>
            <th>Saison</th><th>Manche</th><th>Grand Prix (circuit)</th><th>Pilote</th>
            <th class="num">Qualif.</th><th class="num">Grille</th><th class="num">Arrivée</th>
            <th class="num">Points</th><th>Statut</th><th class="num">Meilleur tour</th>
        </tr>
        <?php
        // On lit la première ligne du résultat
        $ligne = $requete->fetch();
        // Tant qu'il reste une ligne...
        while ($ligne)
        {
            // Position de qualification : un tiret si elle est inconnue
            $qualif = '-';
            if ($ligne['qualif'] != null)
            {
                $qualif = $ligne['qualif'];
            }
            // Place sur la grille : 0 veut dire départ depuis les stands
            $grille = $ligne['grid_position'];
            if ($grille == 0)
            {
                $grille = 'Stand';
            }
            // On affiche la ligne
            echo '<tr>';
            echo '<td>' . $ligne['year'] . '</td>';
            echo '<td>' . $ligne['round'] . '</td>';
            echo '<td><a href="circuits.php?id=' . $ligne['circuitId'] . '#carte">' . h($ligne['circuit_name']) . '</a></td>';
            echo '<td>' . h($ligne['driver_forename'] . ' ' . $ligne['driver_surname']) . '</td>';
            echo '<td class="num">' . $qualif . '</td>';
            echo '<td class="num">' . $grille . '</td>';
            echo '<td class="num">' . $ligne['position'] . '</td>';
            echo '<td class="num">' . $ligne['points'] . '</td>';
            echo '<td>' . h($ligne['status']) . '</td>';
            echo '<td class="num">' . afficher_temps($ligne['fastestLapTime']) . '</td>';
            echo '</tr>';
            // On lit la ligne suivante
            $ligne = $requete->fetch();
        }
        ?>
    </table>
    <?php
    }
}
// Fermeture de la connexion
$bdd = null;
?>
