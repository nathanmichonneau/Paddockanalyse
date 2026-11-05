-- ============================================================
-- creation_base.sql : création de la base MySQL du projet F1
-- À exécuter dans phpMyAdmin alwaysdata (onglet SQL) AVANT importer_donnees.php
-- ============================================================
-- La base est créée depuis l'admin alwaysdata (Bases de données > MySQL),
-- puis on sélectionne cette base dans phpMyAdmin avant d'exécuter ce script.
DROP TABLE IF EXISTS classement_ecurie, qualification, resultat, saison, ecurie, circuit;

-- Les circuits (un circuit par ligne)
CREATE TABLE circuit (
    circuitId           INT PRIMARY KEY,
    circuit_name        VARCHAR(100) NOT NULL,
    circuit_location    VARCHAR(100),
    circuit_country     VARCHAR(50),
    circuit_nationality VARCHAR(50)
);

-- Les écuries (constructeurs) : table gérée dans l'application (ajout / modif / suppression)
CREATE TABLE ecurie (
    constructorId           INT AUTO_INCREMENT PRIMARY KEY,
    constructor_name        VARCHAR(100) NOT NULL,
    constructor_nationality VARCHAR(50),
    constructor_home        VARCHAR(50)
);

-- Les saisons de championnat
CREATE TABLE saison (
    year INT PRIMARY KEY,
    url  VARCHAR(255)
);

-- Les résultats : une ligne = un pilote dans une course
-- (les temps sont stockés en secondes, NULL si donnée manquante)
CREATE TABLE resultat (
    resultId        INT PRIMARY KEY,
    raceId          INT NOT NULL,
    driverId        INT NOT NULL,
    constructorId   INT NOT NULL,
    grid_position   INT,
    position        INT,
    points          DECIMAL(4,1),
    laps            INT,
    fastestLap      INT,
    fastestLapRank  INT,
    fastestLapTime  DECIMAL(8,3),
    fastestLapSpeed DECIMAL(7,3),
    statusId        INT,
    FOREIGN KEY (constructorId) REFERENCES ecurie(constructorId)
);

-- Les qualifications (temps Q1, Q2, Q3 en secondes)
CREATE TABLE qualification (
    qualifyId     INT PRIMARY KEY,
    raceId        INT NOT NULL,
    driverId      INT NOT NULL,
    constructorId INT NOT NULL,
    number        INT,
    position      INT,
    q1            DECIMAL(8,3),
    q2            DECIMAL(8,3),
    q3            DECIMAL(8,3),
    FOREIGN KEY (constructorId) REFERENCES ecurie(constructorId)
);

-- Le classement du championnat des constructeurs après chaque course
CREATE TABLE classement_ecurie (
    constructorStandingsId INT PRIMARY KEY,
    raceId                 INT NOT NULL,
    constructorId          INT NOT NULL,
    points                 DECIMAL(5,1),
    position               INT,
    wins                   INT,
    FOREIGN KEY (constructorId) REFERENCES ecurie(constructorId)
);

-- Index pour accélérer les requêtes du tableau de bord
CREATE INDEX idx_resultat_position ON resultat(position);
CREATE INDEX idx_resultat_ecurie   ON resultat(constructorId);
