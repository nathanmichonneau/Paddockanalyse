-- ============================================================
-- creation_base.sql : création des 9 tables de la base MySQL
-- À exécuter dans phpMyAdmin (onglet SQL), dans la base déjà créée
-- chez alwaysdata. Ensuite : importer_donnees.php?confirmer=oui
-- ============================================================

-- On désactive la vérification des clés étrangères pour pouvoir supprimer les tables dans n'importe quel ordre
SET FOREIGN_KEY_CHECKS = 0;

-- On supprime les anciennes tables si elles existent (permet de relancer le script)
DROP TABLE IF EXISTS classement_ecurie, qualification, resultat, course, pilote, statut, ecurie, circuit, saison;

-- On réactive la vérification des clés étrangères
SET FOREIGN_KEY_CHECKS = 1;

-- Les saisons du championnat (une ligne par année)
CREATE TABLE saison (
    year INT PRIMARY KEY,
    url  VARCHAR(255)
);

-- Les circuits, avec leur position GPS pour la carte
CREATE TABLE circuit (
    circuitId           INT PRIMARY KEY,
    circuit_name        VARCHAR(100) NOT NULL,
    circuit_location    VARCHAR(100),
    circuit_country     VARCHAR(50),
    circuit_nationality VARCHAR(50),
    latitude            DECIMAL(9,6),
    longitude           DECIMAL(9,6)
);

-- Les écuries : cette table est modifiable depuis le site (ajout, modification, suppression)
CREATE TABLE ecurie (
    constructorId           INT AUTO_INCREMENT PRIMARY KEY,
    constructor_name        VARCHAR(100) NOT NULL,
    constructor_nationality VARCHAR(50),
    constructor_home        VARCHAR(50)
);

-- Les courses (Grands Prix) : une année, un numéro de manche et un circuit
CREATE TABLE course (
    raceId    INT PRIMARY KEY,
    year      INT NOT NULL,
    round     INT NOT NULL,
    circuitId INT NOT NULL,
    FOREIGN KEY (year) REFERENCES saison(year),
    FOREIGN KEY (circuitId) REFERENCES circuit(circuitId)
);

-- Les pilotes
CREATE TABLE pilote (
    driverId           INT PRIMARY KEY,
    driver_code        VARCHAR(5),
    driver_forename    VARCHAR(50),
    driver_surname     VARCHAR(50),
    driver_dob         DATE,
    driver_nationality VARCHAR(50),
    driver_home        VARCHAR(50)
);

-- Les statuts d'arrivée (Finished, Engine, Accident...)
CREATE TABLE statut (
    statusId INT PRIMARY KEY,
    status   VARCHAR(50)
);

-- Les résultats : une ligne = un pilote, dans une course, pour une écurie
-- (les temps sont stockés en secondes, NULL si la donnée est manquante)
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
    statusId        INT NOT NULL,
    FOREIGN KEY (raceId) REFERENCES course(raceId),
    FOREIGN KEY (driverId) REFERENCES pilote(driverId),
    FOREIGN KEY (constructorId) REFERENCES ecurie(constructorId),
    FOREIGN KEY (statusId) REFERENCES statut(statusId)
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
    FOREIGN KEY (raceId) REFERENCES course(raceId),
    FOREIGN KEY (driverId) REFERENCES pilote(driverId),
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
    FOREIGN KEY (raceId) REFERENCES course(raceId),
    FOREIGN KEY (constructorId) REFERENCES ecurie(constructorId)
);

-- Index pour accélérer les requêtes des pages
CREATE INDEX idx_resultat_ecurie   ON resultat(constructorId);
CREATE INDEX idx_resultat_course   ON resultat(raceId);
CREATE INDEX idx_resultat_position ON resultat(position);
CREATE INDEX idx_qualif_course     ON qualification(raceId, driverId);
