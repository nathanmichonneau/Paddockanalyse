<?php
// ------------------------------------------------------------
// fonctions.php : petite bibliothèque de fonctions (comme Outils.inc du cours)
// ------------------------------------------------------------

// Protège l'affichage d'un texte venant de la base (évite d'injecter du HTML)
function h($texte)
{
    return htmlspecialchars($texte, ENT_QUOTES, 'UTF-8');
}

// Exécute une requête SELECT et renvoie toutes les lignes dans un tableau
function lire_lignes($bdd, $requete)
{
    $lignes = array();
    $resultat = $bdd->query($requete);
    $ligne = $resultat->fetch();
    while ($ligne)
    {
        $lignes[] = $ligne;
        $ligne = $resultat->fetch();
    }
    return $lignes;
}

// Convertit un temps du CSV ("0 days 00:01:27.452000") en secondes (87.452)
// Renvoie NULL si le temps est vide ou égal à 0 (donnée manquante)
function convertir_temps($texte)
{
    if ($texte === null || $texte === '')
    {
        return null;
    }
    // On récupère heures, minutes et secondes à la fin du texte
    if (preg_match('/(\d+):(\d+):(\d+(\.\d+)?)$/', $texte, $m))
    {
        $secondes = $m[1] * 3600 + $m[2] * 60 + $m[3];
        if ($secondes == 0)
        {
            return null;
        }
        return $secondes;
    }
    return null;
}

// Affiche des secondes sous la forme m:ss.mmm (ex : 87.452 -> 1:27.452)
function afficher_temps($secondes)
{
    if ($secondes === null)
    {
        return '-';
    }
    $minutes = floor($secondes / 60);
    $reste = $secondes - $minutes * 60;
    return $minutes . ':' . sprintf('%06.3f', $reste);
}
?>
