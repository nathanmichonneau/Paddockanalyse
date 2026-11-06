# F1 Data — projet web BUT SD 2 (S3)

Application PHP / MySQL sur le championnat du monde de Formule 1.

## Pages
- `index.php` : chiffres clés et mini-carte des circuits
- `ecuries.php` : liste des écuries (recherche, tri, filtre) + ajout / modification / suppression, sans connexion
- `circuits.php` : carte Leaflet / OpenStreetMap, recherche et filtre par pays
- `resultats.php` : résultats d'une écurie
- `dashboard.php` : 7 graphiques Highcharts
- `documentation.php` : source des données, modèle, cas d'utilisation

## Mise en ligne
1. Base **neuve** : exécuter `sql/creation_base.sql`, puis ouvrir `importer_donnees.php?confirmer=oui`.
2. Base **déjà importée** : exécuter une seule fois `sql/ajout_coordonnees.sql` dans phpMyAdmin
   (ajoute les colonnes `latitude` / `longitude` à la table `circuit`).
3. Pousser sur la branche `main` : le déploiement alwaysdata est automatique (`.github/workflows/deploy.yml`).

Les fichiers du site doivent être à la **racine** du dépôt (et non dans `.github/`).
