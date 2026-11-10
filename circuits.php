<?php
// circuits.php : carte des circuits (Leaflet / OpenStreetMap) + tableau avec le nombre de Grands Prix
include("./connexion.php");   // connexion à la base ($bdd)
include("./fonctions.php");   // fonctions h(), lire_lignes()...

// ----- Lecture des choix de l'utilisateur dans l'adresse (méthode GET) -----
$pays = '';
$idChoisi = 0;
// Pays choisi dans la liste déroulante
if (isset($_GET['pays']))
{
    $pays = $_GET['pays'];
}
// Circuit à mettre en valeur sur la carte (lien « Voir sur la carte »)
if (isset($_GET['id']))
{
    $idChoisi = (int)$_GET['id'];
    // Quand on veut voir un circuit précis, on n'applique pas le filtre par pays
    $pays = '';
}

// ----- Condition WHERE : seulement si un pays est choisi -----
$condition = '';
$valeurs = array();
if ($pays != '')
{
    $condition = ' WHERE ci.circuit_country = ?';
    $valeurs[] = $pays;
}

// ----- Requête : un circuit par ligne, avec son nombre de Grands Prix et la période -----
$requete = $bdd->prepare("SELECT ci.circuitId, ci.circuit_name, ci.circuit_location, ci.circuit_country,
        ci.latitude, ci.longitude,
        COUNT(co.raceId) AS nb_gp, MIN(co.year) AS premiere, MAX(co.year) AS derniere
    FROM circuit ci
    LEFT JOIN course co ON co.circuitId = ci.circuitId
    " . $condition . "
    GROUP BY ci.circuitId, ci.circuit_name, ci.circuit_location, ci.circuit_country, ci.latitude, ci.longitude
    ORDER BY ci.circuit_name");
$requete->execute($valeurs);

// On range les circuits dans un tableau (pour le tableau HTML et pour la carte)
$circuits = array();
$ligne = $requete->fetch();
while ($ligne)
{
    $circuits[] = $ligne;
    $ligne = $requete->fetch();
}

// ----- Données envoyées au JavaScript de la carte -----
$marqueurs = array();
for ($i = 0; $i < count($circuits); $i++)
{
    // Un circuit n'est placé sur la carte que s'il a des coordonnées
    if ($circuits[$i]['latitude'] != null && $circuits[$i]['longitude'] != null)
    {
        $marqueurs[] = array(
            'id' => (int)$circuits[$i]['circuitId'],
            'nom' => $circuits[$i]['circuit_name'],
            'ville' => $circuits[$i]['circuit_location'],
            'pays' => $circuits[$i]['circuit_country'],
            'lat' => (float)$circuits[$i]['latitude'],
            'lng' => (float)$circuits[$i]['longitude'],
            'gp' => (int)$circuits[$i]['nb_gp']
        );
    }
}

// Liste des pays pour la liste déroulante
$listePays = lire_lignes($bdd, "SELECT DISTINCT circuit_country FROM circuit ORDER BY circuit_country");

// ----- Début de la page -----
$titre = 'Circuits';
include("./entete.php");
?>
<h1>Les circuits</h1>
<p class="intro">Tous les circuits où un Grand Prix du championnat du monde a eu lieu. Cliquez sur un marqueur pour voir le détail.</p>

<!-- Formulaire de filtre par pays -->
<form action="circuits.php" method="get" class="filtre">
    <label>Pays
        <select name="pays">
            <option value="">Tous les pays</option>
            <?php
            // Pour chaque pays de la base...
            for ($i = 0; $i < count($listePays); $i++)
            {
                // ... on le sélectionne si c'est celui qui a été choisi
                $selection = '';
                if ($listePays[$i]['circuit_country'] == $pays)
                {
                    $selection = ' selected';
                }
                echo '<option value="' . h($listePays[$i]['circuit_country']) . '"' . $selection . '>' . h($listePays[$i]['circuit_country']) . '</option>';
            }
            ?>
        </select>
    </label>
    <input type="submit" value="Filtrer">
    <a class="btn btn-gris" href="circuits.php">Réinitialiser</a>
</form>

<p><?php echo count($circuits); ?> circuit(s) affiché(s).</p>

<!-- Feuille de style et programme de la bibliothèque Leaflet (la carte) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Zone où la carte sera dessinée -->
<section id="carte"></section>

<script>
// Liste des circuits calculée par PHP
var circuits = <?php echo json_encode($marqueurs); ?>;
// Identifiant du circuit à mettre en valeur (0 = aucun)
var idChoisi = <?php echo $idChoisi; ?>;

// On crée la carte dans la zone "carte", centrée sur le monde
var carte = L.map('carte').setView([30, 10], 2);

// On ajoute le fond de carte OpenStreetMap
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '© contributeurs OpenStreetMap'
}).addTo(carte);

// Liste des positions (pour cadrer la carte) et marqueur du circuit choisi
var positions = [];
var marqueurChoisi = null;

// Pour chaque circuit...
for (var i = 0; i < circuits.length; i++)
{
    // ... on lit le circuit
    var c = circuits[i];
    // ... on prépare le texte de la bulle d'information
    var texte = '<strong>' + c.nom + '</strong><br>' + c.ville + ', ' + c.pays + '<br>' + c.gp + ' Grand(s) Prix';
    // ... on place un marqueur sur la carte
    var marqueur = L.marker([c.lat, c.lng]).addTo(carte);
    // ... on associe la bulle au marqueur
    marqueur.bindPopup(texte);
    // ... on retient la position
    positions.push([c.lat, c.lng]);
    // ... si c'est le circuit demandé dans l'adresse, on le retient
    if (c.id == idChoisi)
    {
        marqueurChoisi = marqueur;
    }
}

// Si un circuit précis est demandé : on zoome dessus et on ouvre sa bulle
if (marqueurChoisi != null)
{
    carte.setView(marqueurChoisi.getLatLng(), 10);
    marqueurChoisi.openPopup();
}
// Sinon, on cadre la carte pour voir tous les marqueurs
else if (positions.length > 0)
{
    carte.fitBounds(positions, {padding: [30, 30], maxZoom: 6});
}
</script>

<!-- Tableau des circuits -->
<table>
    <tr>
        <th>Circuit</th><th>Ville</th><th>Pays</th>
        <th class="num">Grands Prix</th><th>Période</th><th>Carte</th>
    </tr>
    <?php
    // Pour chaque circuit...
    for ($i = 0; $i < count($circuits); $i++)
    {
        // ... on prépare le texte de la période (vide si aucun Grand Prix)
        $periode = '-';
        if ($circuits[$i]['nb_gp'] > 0)
        {
            $periode = $circuits[$i]['premiere'] . ' - ' . $circuits[$i]['derniere'];
        }
        // ... on affiche la ligne
        echo '<tr>';
        echo '<td>' . h($circuits[$i]['circuit_name']) . '</td>';
        echo '<td>' . h($circuits[$i]['circuit_location']) . '</td>';
        echo '<td>' . h($circuits[$i]['circuit_country']) . '</td>';
        echo '<td class="num">' . $circuits[$i]['nb_gp'] . '</td>';
        echo '<td>' . $periode . '</td>';
        echo '<td><a href="circuits.php?id=' . $circuits[$i]['circuitId'] . '#carte">Voir sur la carte</a></td>';
        echo '</tr>';
    }
    ?>
</table>
<?php
// Fermeture de la connexion
$bdd = null;
// Fin de la page
include("./pied.php");
?>
