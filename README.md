# Paddock Analyse — projet web BUT SD 2 (S3)

Application PHP / MySQL / JavaScript sur le championnat du monde de Formule 1
(jeu de données OpenData « Formula 1 World Championship », Kaggle / Ergast).

## Les pages (toutes avec le même menu)
| Page | Rôle |
|---|---|
| `index.php` | Chiffres clés, top 5 des écuries, liens vers les autres pages |
| `ecuries.php` | Liste des écuries (recherche, filtre par nationalité, tri) + liens Résultats / Comparer / Modifier / Supprimer |
| `ecurie_ajout.php` → `ecurie_insert.php` | Ajout d'une écurie (INSERT) |
| `ecurie_modif.php` → `ecurie_update.php` | Modification d'une écurie (UPDATE) |
| `ecurie_suppr.php` | Suppression d'une écurie (DELETE) |
| `circuits.php` | Carte Leaflet des circuits + tableau (filtre par pays) |
| `resultats.php` | Résultats d'une écurie, saison par saison |
| `comparaison.php` | Comparaison de deux écuries (tableau + graphique) |
| `dashboard.php` | Tableau de bord : 8 graphiques Highcharts |
| `documentation.php` | Source des données, modèle de la base, diagramme UML |

Fichiers communs : `connexion.php` (base), `fonctions.php` (fonctions), `entete.php` / `pied.php` (mise en page), `style.css`.

## Mise en ligne sur alwaysdata
1. **GitHub** : supprimer les anciens fichiers du dépôt, puis envoyer le contenu de ce dossier
   (garder `.github/workflows/deploy.yml` et le fichier `.htaccess`). Les fichiers doivent être à la racine du dépôt.
2. **Base de données** : dans phpMyAdmin (base `saebdd_php`), onglet *Importer*, choisir `sql/f1_base_complete.sql`.
   Les anciennes tables sont remplacées.
   - Autre méthode : onglet *SQL* → exécuter `sql/creation_base.sql`, puis ouvrir `importer_donnees.php?confirmer=oui`.
3. **Déploiement** : à chaque envoi sur la branche `main`, GitHub Actions met à jour le site.

## Sécurité
- `connexion.php` contient le mot de passe MySQL : mettre le dépôt GitHub en **privé** (le déploiement continue de fonctionner)
  ou changer le mot de passe MySQL depuis l'admin alwaysdata après la remise du projet.
- `.htaccess` bloque l'accès au dossier `.git` du site.

## Test en local (EasyPHP)
Dans `connexion.php` : serveur `localhost`, utilisateur `root`, mot de passe vide, et le nom de votre base locale.
