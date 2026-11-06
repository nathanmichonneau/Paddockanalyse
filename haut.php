<?php // En-tête commun. Variables : $titre, $head_extra (optionnel). Nécessite outils.php. ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titre ?? 'Accueil') ?> – Paddock Analyse</title>
<script>(function(){try{var t=localStorage.getItem('pa-theme')||(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.dataset.theme=t;}catch(e){}})();</script>
<link rel="stylesheet" href="theme.css">
<?= $head_extra ?? '' ?>
</head>
<body>
<?php include __DIR__ . '/menu.php'; ?>
<main class="pa-main">
