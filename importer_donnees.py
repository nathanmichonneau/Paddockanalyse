# importer_donnees.py : importe les fichiers CSV dans la base MySQL
# Avant : installer le module avec   pip install pymysql
# Avant : avoir créé les tables avec sql/creation_base.sql (phpMyAdmin)

import csv
import pymysql

#  Informations de connexion 
SERVEUR = "mysql-saebdd.alwaysdata.net"
BASE = "saebdd_php"
USER = "saebdd"
MDP = "SAEAlixNathan$"

# Connexion à la base 
connexion = pymysql.connect(host=SERVEUR, user=USER, password=MDP,
                            database=BASE, charset="utf8mb4")
curseur = connexion.cursor()


# Fonction qui transforme un temps en secondes 
# Exemple : "0 days 00:01:26.572000" devient 86.572
def temps_en_secondes(texte):
    heure = texte.split(" ")[2]          # "00:01:26.572000"
    h, m, s = heure.split(":")           # "00", "01", "26.572000"
    return int(h) * 3600 + int(m) * 60 + float(s)


# Fonction qui importe un fichier CSV dans une table 
def importer(table, fichier, colonnes_temps=[]):
    # On ouvre le CSV
    f = open("data/" + fichier)
    lecteur = csv.reader(f)

    # La première ligne contient les noms des colonnes
    noms = next(lecteur)

    # On lit toutes les autres lignes
    lignes = []
    for ligne in lecteur:
        nouvelle_ligne = []
        for i in range(len(ligne)):
            valeur = ligne[i]
            if valeur == "" or valeur == "\\N":
                valeur = None                        # case vide -> NULL
            elif noms[i] in colonnes_temps:
                valeur = temps_en_secondes(valeur)   # temps -> secondes
            nouvelle_ligne.append(valeur)
        lignes.append(nouvelle_ligne)
    f.close()

    # On prépare la requête : INSERT INTO table (col1, col2...) VALUES (%s, %s...)
    colonnes = ", ".join(noms)
    valeurs = ", ".join(["%s"] * len(noms))
    requete = "INSERT INTO " + table + " (" + colonnes + ") VALUES (" + valeurs + ")"

    # On insère toutes les lignes d'un coup
    curseur.executemany(requete, lignes)
    connexion.commit()
    print(table, ":", len(lignes), "lignes importées")


# On vide les tables (dans l'ordre inverse) Pour pas avoir de donnée en doublon
for table in ["classement_ecurie", "qualification", "resultat", "course",
              "pilote", "statut", "ecurie", "circuit", "saison"]:
    curseur.execute("DELETE FROM " + table)
connexion.commit()
print("Tables vidées")

# ---------- 6. On importe (d'abord les tables sans clé étrangère) ----------
importer("saison", "cleaned_seasons.csv")
importer("circuit", "cleaned_circuits.csv")
importer("ecurie", "cleaned_constructors.csv")
importer("statut", "cleaned_status.csv")
importer("pilote", "cleaned_drivers.csv")
importer("course", "cleaned_races.csv")
importer("resultat", "cleaned_results.csv", ["fastestLapTime"])
importer("qualification", "cleaned_qualifying.csv", ["q1", "q2", "q3"])
importer("classement_ecurie", "cleaned_constructor_standings.csv")

# ---------- 7. Fin ----------
connexion.close()
print("Import terminé !")
