<?php
// comparaison.php : compare deux écuries (points, victoires, classement par saison)
require_once __DIR__ . '/outils.php';
$pdo = obtenir_pdo();

$erreur = null; $ecuries = []; $T = [];
try {
    if (!$pdo) throw new Exception("Aucune connexion PDO trouvée dans connexion.php.");
    $T['classement'] = trouver_table($pdo, ['constructorId', 'raceId', 'points', 'wins']);
    $T['course']     = trouver_table($pdo, ['raceId', 'year', 'round']);
    $T['ecurie']     = trouver_table($pdo, ['constructorId', 'name']);
    foreach ($T as $role => $table) {
        if (!$table) {
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            throw new Exception("Table « $role » introuvable. Tables de la base : " . implode(', ', $tables)
                . ". Envoie cette liste (et les colonnes de ces tables) pour adapter la page.");
        }
    }
    $ecuries = $pdo->query("SELECT e.constructorId AS id, e.name AS nom FROM `{$T['ecurie']}` e
        WHERE EXISTS (SELECT 1 FROM `{$T['classement']}` s WHERE s.constructorId = e.constructorId)
        ORDER BY e.name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $erreur = $e->getMessage();
}

// Classement final de chaque saison pour une écurie (points cumulés au dernier Grand Prix disputé)
function saisons(PDO $pdo, array $T, int $id) {
    $sql = "SELECT r.year AS annee, s.points, s.wins AS victoires, s.position
        FROM `{$T['classement']}` s
        JOIN `{$T['course']}` r ON r.raceId = s.raceId
        JOIN (SELECT r2.year AS y, MAX(r2.round) AS maxr
              FROM `{$T['classement']}` s2 JOIN `{$T['course']}` r2 ON r2.raceId = s2.raceId
              WHERE s2.constructorId = :id2 GROUP BY r2.year) m ON m.y = r.year AND m.maxr = r.round
        WHERE s.constructorId = :id1 ORDER BY r.year";
    $st = $pdo->prepare($sql);
    $st->execute([':id1' => $id, ':id2' => $id]);
    $res = [];
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $l) {
        $res[] = ['annee' => (int) $l['annee'], 'points' => (float) $l['points'],
                  'victoires' => (int) $l['victoires'], 'position' => $l['position'] === null ? null : (int) $l['position']];
    }
    return $res;
}

function resume(PDO $pdo, array $T, int $id, array $ecuries) {
    $nom = '';
    foreach ($ecuries as $e) { if ((int) $e['id'] === $id) $nom = $e['nom']; }
    $s = saisons($pdo, $T, $id);
    $pos = array_filter(array_column($s, 'position'), fn($p) => $p !== null && $p > 0);
    return [
        'nom' => $nom, 'saisons' => $s,
        'kpi' => [
            'saisons'   => count($s),
            'points'    => array_sum(array_column($s, 'points')),
            'victoires' => array_sum(array_column($s, 'victoires')),
            'titres'    => count(array_filter($s, fn($x) => $x['position'] === 1)),
            'meilleure' => $pos ? min($pos) : null,
            'premiere'  => $s ? $s[0]['annee'] : null,
            'derniere'  => $s ? end($s)['annee'] : null,
        ],
    ];
}

// Mode API : renvoie du JSON pour le graphique dynamique (sans recharger la page)
if (isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');
    if ($erreur) { echo json_encode(['erreur' => $erreur]); exit; }
    echo json_encode(['a' => resume($pdo, $T, (int) ($_GET['a'] ?? 0), $ecuries),
                      'b' => resume($pdo, $T, (int) ($_GET['b'] ?? 0), $ecuries)],
                     JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    exit;
}

// Écuries sélectionnées par défaut
$defaut = [];
foreach (['Ferrari', 'McLaren'] as $nom) {
    foreach ($ecuries as $e) { if (stripos($e['nom'], $nom) !== false) { $defaut[] = $e['id']; break; } }
}
$a = (int) ($_GET['a'] ?? ($defaut[0] ?? ($ecuries[0]['id'] ?? 0)));
$b = (int) ($_GET['b'] ?? ($defaut[1] ?? ($ecuries[1]['id'] ?? 0)));

$titre = 'Comparer deux écuries';
require __DIR__ . '/haut.php';
?>

<h1>Comparer deux écuries</h1>

<?php if ($erreur): ?>
  <p><strong>Erreur :</strong> <?= e($erreur) ?></p>
<?php else: ?>
  <div class="pa-barre">
    <label>Écurie A
      <select id="ea"><?php foreach ($ecuries as $e): ?><option value="<?= (int) $e['id'] ?>"<?= (int) $e['id'] === $a ? ' selected' : '' ?>><?= e($e['nom']) ?></option><?php endforeach; ?></select>
    </label>
    <label>Écurie B
      <select id="eb"><?php foreach ($ecuries as $e): ?><option value="<?= (int) $e['id'] ?>"<?= (int) $e['id'] === $b ? ' selected' : '' ?>><?= e($e['nom']) ?></option><?php endforeach; ?></select>
    </label>
    <label>Indicateur
      <select id="metrique">
        <option value="points">Points en fin de saison</option>
        <option value="victoires">Victoires par saison</option>
        <option value="position">Classement final</option>
      </select>
    </label>
  </div>

  <p id="cmp-message" role="status"></p>
  <div id="cmp-graphique"></div>

  <table class="pa-tableau">
    <thead><tr><th>Bilan</th><th id="th-a">A</th><th id="th-b">B</th></tr></thead>
    <tbody id="cmp-corps"></tbody>
  </table>

  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script>
  const LIBELLES = [
    ['saisons', 'Saisons disputées'], ['premiere', 'Première saison'], ['derniere', 'Dernière saison'],
    ['victoires', 'Victoires'], ['titres', 'Titres constructeurs'], ['meilleure', 'Meilleur classement'], ['points', 'Points cumulés']
  ];
  const $ = id => document.getElementById(id);
  let donnees = null;

  function dessiner() {
    if (!donnees) return;
    const m = $('metrique').value;
    const inverse = m === 'position';
    const couleur = getComputedStyle(document.body).color;
    const serie = d => d.saisons.filter(s => s[m] !== null).map(s => [s.annee, s[m]]);
    Highcharts.chart('cmp-graphique', {
      chart: { type: 'line', backgroundColor: 'transparent', style: { color: couleur } },
      title: { text: $('metrique').selectedOptions[0].text, style: { color: couleur } },
      xAxis: { title: { text: 'Saison' }, allowDecimals: false, labels: { style: { color: couleur } } },
      yAxis: { title: { text: null }, reversed: inverse, min: inverse ? 1 : 0, allowDecimals: false, labels: { style: { color: couleur } } },
      legend: { itemStyle: { color: couleur } },
      tooltip: { shared: true, headerFormat: '<b>Saison {point.key}</b><br>' },
      credits: { enabled: false },
      series: [{ name: donnees.a.nom, data: serie(donnees.a) }, { name: donnees.b.nom, data: serie(donnees.b) }]
    });
    $('th-a').textContent = donnees.a.nom;
    $('th-b').textContent = donnees.b.nom;
    $('cmp-corps').innerHTML = LIBELLES.map(([k, l]) =>
      '<tr><td>' + l + '</td><td>' + (donnees.a.kpi[k] ?? '–') + '</td><td>' + (donnees.b.kpi[k] ?? '–') + '</td></tr>').join('');
  }

  async function charger() {
    const a = $('ea').value, b = $('eb').value;
    $('cmp-message').textContent = a === b ? 'Choisis deux écuries différentes pour les comparer.' : '';
    try {
      const rep = await fetch('comparaison.php?api=1&a=' + a + '&b=' + b);
      donnees = await rep.json();
      if (donnees.erreur) { $('cmp-message').textContent = donnees.erreur; return; }
      history.replaceState(null, '', '?a=' + a + '&b=' + b);
      dessiner();
    } catch (e) {
      $('cmp-message').textContent = 'Impossible de charger les données.';
    }
  }

  ['ea', 'eb'].forEach(id => $(id).addEventListener('change', charger));
  $('metrique').addEventListener('change', dessiner);
  window.addEventListener('pa-theme', dessiner);
  charger();
  </script>
<?php endif; ?>

<?php require __DIR__ . '/bas.php'; ?>
