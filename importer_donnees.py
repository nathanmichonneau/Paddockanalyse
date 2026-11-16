# importer_donnees.py : importe les fichiers CSV dans la base MySQL
# Avant : installer les modules avec   pip install pandas pymysql
# Avant : avoir créé les tables avec sql/creation_base.sql (phpMyAdmin)

import pandas as pd
import pymysql

# ---------- 1. Informations de connexion (comme dans connexion.php) ----------
SERVEUR = "mysql-saebdd.alwaysdata.net"
BASE = "saebdd_php"
USER = "saebdd"
MDP = "SAEAlixNathan$"

# Dossier où se trouvent les CSV
DOSSIER = "data/"

# ---------- 2. Connexion à la base ----------
connexion = pymysql.connect(host=SERVEUR, user=USER, password=MDP,
                            database=BASE, charset="utf8mb4")
curseur = connexion.cursor()


# ---------- 3. Fonction qui importe un fichier CSV dans une table ----------
def importer(table, fichier, colonnes_temps=[]):
    # On lit le CSV
    df = pd.read_csv(DOSSIER + fichier)

    # Les temps sont écrits "0 days 00:01:26.572000" -> on les passe en secondes
    for col in colonnes_temps:
        df[col] = pd.to_timedelta(df[col]).dt.total_seconds()

    # Les "\N" (valeur manquante de l'export d'origine) deviennent des cases vides
    df = df.replace("\\N", float("nan"))

    # Les cases vides (NaN) deviennent NULL (None en Python)
    df = df.astype(object).where(df.notna(), None)

    # On prépare la requête : INSERT INTO table (col1, col2...) VALUES (%s, %s...)
    colonnes = ", ".join(df.columns)
    valeurs = ", ".join(["%s"] * len(df.columns))
    requete = "INSERT INTO " + table + " (" + colonnes + ") VALUES (" + valeurs + ")"

    # On insère toutes les lignes d'un coup
    curseur.executemany(requete, df.values.tolist())
    connexion.commit()
    print(table, ":", len(df), "lignes importées")


# ---------- 4. On vide les tables (dans l'ordre inverse à cause des clés étrangères) ----------
for table in ["classement_ecurie", "qualification", "resultat", "course",
              "pilote", "statut", "ecurie", "circuit", "saison"]:
    curseur.execute("DELETE FROM " + table)
connexion.commit()
print("Tables vidées")

# ---------- 5. On importe (d'abord les tables sans clé étrangère) ----------
importer("saison", "cleaned_seasons.csv")
importer("circuit", "cleaned_circuits.csv")
importer("ecurie", "cleaned_constructors.csv")
importer("statut", "cleaned_status.csv")
importer("pilote", "cleaned_drivers.csv")
importer("course", "cleaned_races.csv")
importer("resultat", "cleaned_results.csv", ["fastestLapTime"])
importer("qualification", "cleaned_qualifying.csv", ["q1", "q2", "q3"])
importer("classement_ecurie", "cleaned_constructor_standings.csv")

# ---------- 6. Fin ----------
connexion.close()
print("Import terminé !")
