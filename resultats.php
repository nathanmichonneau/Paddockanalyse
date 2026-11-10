<?php
// fonctions.php : petites fonctions utilisées par plusieurs pages (comme Outils.inc du cours)

// h() : protège un texte avant de l'afficher dans une page HTML
function h($texte)
{
    // On transforme la valeur en texte (la base peut renvoyer NULL)
    $texte = (string)$texte;
    // htmlspecialchars remplace < > & " ' par des codes sans danger
    return htmlspecialchars($texte, ENT_QUOTES, 'UTF-8');
}

// lire_lignes() : exécute un SELECT et renvoie toutes les lignes dans un tableau
function lire_lignes($bdd, $requete)
{
    // Tableau vide qui va recevoir les lignes
    $lignes = array();
    // On exécute la requête
    $resultat = $bdd->query($requete);
    // On lit la première ligne
    $ligne = $resultat->fetch();
    // Tant qu'il reste une ligne...
    while ($ligne)
    {
        // ... on l'ajoute à la fin du tableau
        $lignes[] = $ligne;
        // ... et on lit la ligne suivante
        $ligne = $resultat->fetch();
    }
    // On renvoie le tableau complet
    return $lignes;
}

// lire_valeur() : exécute un SELECT qui renvoie une seule valeur (ex : un COUNT) et la renvoie
function lire_valeur($bdd, $requete)
{
    // On exécute la requête
    $resultat = $bdd->query($requete);
    // On lit la ligne sous forme de tableau numéroté : la valeur est dans la case 0
    $ligne = $resultat->fetch(PDO::FETCH_NUM);
    // Si la requête n'a renvoyé aucune ligne, on renvoie NULL
    if (!$ligne)
    {
        return null;
    }
    // Sinon on renvoie la valeur
    return $ligne[0];
}

// convertir_temps() : transforme "0 days 00:01:27.452000" en secondes (87.452)
// Renvoie NULL si le temps est vide ou égal à zéro (donnée manquante)
function convertir_temps($texte)
{
    // Si le texte est vide, il n'y a pas de temps
    if ($texte === null || $texte === '')
    {
        return null;
    }
    // On coupe le texte à chaque espace : "0", "days", "00:01:27.452000"
    $morceaux = explode(' ', $texte);
    // Si le texte n'a pas 3 morceaux, il n'est pas dans le format attendu
    if (count($morceaux) != 3)
    {
        return null;
    }
    // On coupe la partie "00:01:27.452000" à chaque ":" : heures, minutes, secondes
    $parties = explode(':', $morceaux[2]);
    // On calcule le total en secondes
    $secondes = $parties[0] * 3600 + $parties[1] * 60 + $parties[2];
    // Un temps de zéro seconde veut dire "donnée manquante"
    if ($secondes == 0)
    {
        return null;
    }
    // On renvoie le temps en secondes
    return $secondes;
}

// afficher_temps() : transforme 87.452 secondes en "1:27.452"
function afficher_temps($secondes)
{
    // Pas de temps connu : on affiche un tiret
    if ($secondes === null)
    {
        return '-';
    }
    // Nombre de minutes entières
    $minutes = floor($secondes / 60);
    // Secondes restantes
    $reste = $secondes - $minutes * 60;
    // On assemble : minutes, deux-points, secondes sur 6 caractères (ex : 27.452)
    return $minutes . ':' . sprintf('%06.3f', $reste);
}
?>
