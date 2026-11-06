<?php
// outils.php : fonctions communes (connexion, détection des tables, données des circuits)
require_once __DIR__ . '/connexion.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

// Retrouve la connexion PDO créée par connexion.php, quel que soit le nom de la variable
function obtenir_pdo() {
    foreach ($GLOBALS as $v) {
        if ($v instanceof PDO) { $v->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); return $v; }
    }
    return null;
}

// Cherche la table qui contient toutes les colonnes données (les noms de tables peuvent varier)
function trouver_table(PDO $pdo, array $cols) {
    $cols = array_map('strtolower', $cols);
    $st = $pdo->prepare("SELECT table_name FROM information_schema.columns
        WHERE table_schema = DATABASE() AND LOWER(column_name) IN (" . implode(',', array_fill(0, count($cols), '?')) . ")
        GROUP BY table_name HAVING COUNT(DISTINCT LOWER(column_name)) = ? ORDER BY table_name");
    $st->execute(array_merge($cols, [count($cols)]));
    $t = $st->fetchColumn();
    return $t ? str_replace('`', '', $t) : null;
}

function champ(array $ligne, array $noms) {
    foreach ($noms as $n) { if (isset($ligne[$n]) && $ligne[$n] !== '') return $ligne[$n]; }
    return '';
}

// Circuits avec coordonnées + statistiques (nombre de Grands Prix, années, décennies)
function donnees_circuits(PDO $pdo) {
    $stats = [];
    $tc = trouver_table($pdo, ['raceId', 'year', 'circuitId']);
    if ($tc) {
        foreach ($pdo->query("SELECT circuitId AS id, COUNT(*) AS nb, MIN(year) AS a0, MAX(year) AS a1,
                GROUP_CONCAT(DISTINCT year) AS ans FROM `$tc` GROUP BY circuitId") as $s) { $stats[$s['id']] = $s; }
    }
    $res = []; $sans = 0;
    foreach ($pdo->query('SELECT * FROM circuit')->fetchAll(PDO::FETCH_ASSOC) as $l) {
        if (!is_numeric($l['latitude'] ?? null) || !is_numeric($l['longitude'] ?? null)) { $sans++; continue; }
        $s = $stats[$l['circuitId'] ?? ''] ?? null;
        $dec = [];
        if ($s) { foreach (explode(',', $s['ans']) as $a) { $dec[intdiv((int) $a, 10) * 10] = true; } }
        $res[] = [
            'id' => $l['circuitId'] ?? null,
            'nom' => champ($l, ['name', 'circuitName', 'nom']),
            'ville' => champ($l, ['location', 'ville', 'locality']),
            'pays' => champ($l, ['country', 'pays']),
            'lat' => (float) $l['latitude'], 'lng' => (float) $l['longitude'],
            'nb' => $s ? (int) $s['nb'] : 0, 'a0' => $s ? (int) $s['a0'] : null, 'a1' => $s ? (int) $s['a1'] : null,
            'dec' => array_keys($dec),
        ];
    }
    return [$res, $sans];
}
