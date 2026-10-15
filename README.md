\set ON_ERROR_STOP on
\timing off

-- -----------------------------------------------------------------------------
-- 0. Nettoyage (ordre inverse des dépendances)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS lap_times            CASCADE;
DROP TABLE IF EXISTS pit_stops            CASCADE;
DROP TABLE IF EXISTS qualifying           CASCADE;
DROP TABLE IF EXISTS sprint_results       CASCADE;
DROP TABLE IF EXISTS results              CASCADE;
DROP TABLE IF EXISTS constructor_results  CASCADE;
DROP TABLE IF EXISTS constructor_standings CASCADE;
DROP TABLE IF EXISTS driver_standings     CASCADE;
DROP TABLE IF EXISTS races                CASCADE;
DROP TABLE IF EXISTS status               CASCADE;
DROP TABLE IF EXISTS drivers              CASCADE;
DROP TABLE IF EXISTS constructors         CASCADE;
DROP TABLE IF EXISTS circuits             CASCADE;
DROP TABLE IF EXISTS seasons              CASCADE;

-- -----------------------------------------------------------------------------
-- 1. Tables de référence
-- -----------------------------------------------------------------------------
CREATE TABLE seasons (
    year  INTEGER      PRIMARY KEY,
    url   VARCHAR(255) NOT NULL
);

CREATE TABLE circuits (
    circuitId            INTEGER      PRIMARY KEY,
    circuit_name         VARCHAR(100) NOT NULL,
    circuit_location     VARCHAR(100),
    circuit_country      VARCHAR(100),
    circuit_nationality  VARCHAR(100)
);

CREATE TABLE constructors (
    constructorId            INTEGER      PRIMARY KEY,
    constructor_name         VARCHAR(100) NOT NULL,
    constructor_nationality  VARCHAR(100),
    constructor_home         VARCHAR(100)
);

CREATE TABLE drivers (
    driverId            INTEGER      PRIMARY KEY,
    driver_code         VARCHAR(3),            -- NULL pour les pilotes sans code (\N dans le CSV)
    driver_forename     VARCHAR(100) NOT NULL,
    driver_surname      VARCHAR(100) NOT NULL,
    driver_dob          DATE,
    driver_nationality  VARCHAR(100),
    driver_home         VARCHAR(100)           -- NULL si inconnu (2 pilotes)
);

CREATE TABLE status (
    statusId  INTEGER      PRIMARY KEY,
    status    VARCHAR(100) NOT NULL
);

-- -----------------------------------------------------------------------------
-- 2. Courses
-- -----------------------------------------------------------------------------
CREATE TABLE races (
    raceId     INTEGER PRIMARY KEY,
    year       INTEGER NOT NULL REFERENCES seasons(year),
    round      INTEGER NOT NULL,
    circuitId  INTEGER NOT NULL REFERENCES circuits(circuitId),
    UNIQUE (year, round)
);

-- -----------------------------------------------------------------------------
-- 3. Tables de faits
--    Les durées (lap_time, q1, fastestLapTime...) sont stockées en INTERVAL :
--    PostgreSQL lit directement le format '0 days 00:01:38.109000'.
-- -----------------------------------------------------------------------------
CREATE TABLE results (
    resultId         INTEGER       PRIMARY KEY,
    raceId           INTEGER       NOT NULL REFERENCES races(raceId),
    driverId         INTEGER       NOT NULL REFERENCES drivers(driverId),
    constructorId    INTEGER       NOT NULL REFERENCES constructors(constructorId),
    grid_position    INTEGER,
    position         INTEGER,
    points           NUMERIC(5,2),
    laps             INTEGER,
    fastestLap       INTEGER,
    fastestLapRank   INTEGER,
    fastestLapTime   INTERVAL,
    fastestLapSpeed  NUMERIC(7,3),
    statusId         INTEGER       NOT NULL REFERENCES status(statusId)
);

CREATE TABLE sprint_results (
    resultId        INTEGER      PRIMARY KEY,
    raceId          INTEGER      NOT NULL REFERENCES races(raceId),
    driverId        INTEGER      NOT NULL REFERENCES drivers(driverId),
    constructorId   INTEGER      NOT NULL REFERENCES constructors(constructorId),
    grid_position   INTEGER,
    position        INTEGER,
    points          NUMERIC(5,2),
    laps            INTEGER,
    fastestLap      INTEGER,
    fastestLapTime  INTERVAL,
    statusId        INTEGER      NOT NULL REFERENCES status(statusId),
    fastestLapRank  INTEGER
);

CREATE TABLE qualifying (
    qualifyId      INTEGER  PRIMARY KEY,
    raceId         INTEGER  NOT NULL REFERENCES races(raceId),
    driverId       INTEGER  NOT NULL REFERENCES drivers(driverId),
    constructorId  INTEGER  NOT NULL REFERENCES constructors(constructorId),
    number         INTEGER,
    position       INTEGER,
    q1             INTERVAL,
    q2             INTERVAL,
    q3             INTERVAL
);

CREATE TABLE lap_times (
    raceId    INTEGER  NOT NULL REFERENCES races(raceId),
    driverId  INTEGER  NOT NULL REFERENCES drivers(driverId),
    lap       INTEGER  NOT NULL,
    position  INTEGER,
    lap_time  INTERVAL NOT NULL,
    PRIMARY KEY (raceId, driverId, lap)
);

CREATE TABLE pit_stops (
    raceId        INTEGER NOT NULL REFERENCES races(raceId),
    driverId      INTEGER NOT NULL REFERENCES drivers(driverId),
    stop          INTEGER NOT NULL,
    lap           INTEGER,
    milliseconds  INTEGER,
    PRIMARY KEY (raceId, driverId, stop)
);

CREATE TABLE driver_standings (
    driverStandingsId  INTEGER       PRIMARY KEY,
    raceId             INTEGER       NOT NULL REFERENCES races(raceId),
    driverId           INTEGER       NOT NULL REFERENCES drivers(driverId),
    points             NUMERIC(6,2),
    position           INTEGER,
    wins               INTEGER
);

CREATE TABLE constructor_standings (
    constructorStandingsId  INTEGER       PRIMARY KEY,
    raceId                  INTEGER       NOT NULL REFERENCES races(raceId),
    constructorId           INTEGER       NOT NULL REFERENCES constructors(constructorId),
    points                  NUMERIC(6,2),
    position                INTEGER,
    wins                    INTEGER
);

CREATE TABLE constructor_results (
    constructorResultsId  INTEGER       PRIMARY KEY,
    raceId                INTEGER       NOT NULL REFERENCES races(raceId),
    constructorId         INTEGER       NOT NULL REFERENCES constructors(constructorId),
    points                NUMERIC(6,2)
);

-- -----------------------------------------------------------------------------
-- 4. Import des CSV (dans l'ordre des dépendances)
-- -----------------------------------------------------------------------------
\echo '>> Import des tables de référence'
\copy seasons       (year, url)                                                                  FROM 'cleaned_seasons.csv'       WITH (FORMAT csv, HEADER true)
\copy circuits      (circuitId, circuit_name, circuit_location, circuit_country, circuit_nationality) FROM 'cleaned_circuits.csv'  WITH (FORMAT csv, HEADER true)
\copy constructors  (constructorId, constructor_name, constructor_nationality, constructor_home) FROM 'cleaned_constructors.csv'  WITH (FORMAT csv, HEADER true)
\copy status        (statusId, status)                                                           FROM 'cleaned_status.csv'        WITH (FORMAT csv, HEADER true)

-- drivers : '\N' (sans code pilote) devient NULL ; le texte vide de driver_home est traité plus bas
\copy drivers (driverId, driver_code, driver_forename, driver_surname, driver_dob, driver_nationality, driver_home) FROM 'cleaned_drivers.csv' WITH (FORMAT csv, HEADER true, NULL '\N')

\echo '>> Import des courses'
\copy races (raceId, year, round, circuitId) FROM 'cleaned_races.csv' WITH (FORMAT csv, HEADER true)

\echo '>> Import des tables de faits'
\copy results (resultId, raceId, driverId, constructorId, grid_position, position, points, laps, fastestLap, fastestLapRank, fastestLapTime, fastestLapSpeed, statusId) FROM 'cleaned_results.csv' WITH (FORMAT csv, HEADER true)
\copy sprint_results (resultId, raceId, driverId, constructorId, grid_position, position, points, laps, fastestLap, fastestLapTime, statusId, fastestLapRank) FROM 'cleaned_sprint_results.csv' WITH (FORMAT csv, HEADER true)
\copy qualifying (qualifyId, raceId, driverId, constructorId, number, position, q1, q2, q3) FROM 'cleaned_qualifying.csv' WITH (FORMAT csv, HEADER true, NULL '')
\copy lap_times (raceId, driverId, lap, position, lap_time) FROM 'cleaned_lap_times.csv' WITH (FORMAT csv, HEADER true)
\copy pit_stops (raceId, driverId, stop, lap, milliseconds) FROM 'cleaned_pit_stops.csv' WITH (FORMAT csv, HEADER true)
\copy driver_standings (driverStandingsId, raceId, driverId, points, position, wins) FROM 'cleaned_driver_standings.csv' WITH (FORMAT csv, HEADER true)
\copy constructor_standings (constructorStandingsId, raceId, constructorId, points, position, wins) FROM 'cleaned_constructor_standings.csv' WITH (FORMAT csv, HEADER true)
\copy constructor_results (constructorResultsId, raceId, constructorId, points) FROM 'cleaned_constructor_results.csv' WITH (FORMAT csv, HEADER true)

-- -----------------------------------------------------------------------------
-- 5. Valeurs manquantes : on remplace les marqueurs du CSV par de vrais NULL
--    - driver_home vide                      -> NULL
--    - durée '0 days 00:00:00' (= pas de temps) -> NULL
--      (un tour à 0 seconde fausserait MIN/AVG sur les temps)
-- -----------------------------------------------------------------------------
UPDATE drivers        SET driver_home    = NULL WHERE driver_home = '';
UPDATE results        SET fastestLapTime = NULL WHERE fastestLapTime = INTERVAL '0';
UPDATE sprint_results SET fastestLapTime = NULL WHERE fastestLapTime = INTERVAL '0';
UPDATE qualifying     SET q1 = NULL WHERE q1 = INTERVAL '0';
UPDATE qualifying     SET q2 = NULL WHERE q2 = INTERVAL '0';
UPDATE qualifying     SET q3 = NULL WHERE q3 = INTERVAL '0';

-- -----------------------------------------------------------------------------
-- 6. Index sur les clés étrangères (accélèrent les jointures)
--    lap_times et pit_stops sont déjà couverts par leur clé primaire sur raceId.
-- -----------------------------------------------------------------------------
CREATE INDEX idx_races_circuit        ON races(circuitId);
CREATE INDEX idx_results_race         ON results(raceId);
CREATE INDEX idx_results_driver       ON results(driverId);
CREATE INDEX idx_results_constructor  ON results(constructorId);
CREATE INDEX idx_results_status       ON results(statusId);
CREATE INDEX idx_sprint_race          ON sprint_results(raceId);
CREATE INDEX idx_qualifying_race      ON qualifying(raceId);
CREATE INDEX idx_qualifying_driver    ON qualifying(driverId);
CREATE INDEX idx_lap_times_driver     ON lap_times(driverId);
CREATE INDEX idx_pit_stops_driver     ON pit_stops(driverId);
CREATE INDEX idx_dstand_race          ON driver_standings(raceId);
CREATE INDEX idx_dstand_driver        ON driver_standings(driverId);
CREATE INDEX idx_cstand_race          ON constructor_standings(raceId);
CREATE INDEX idx_cstand_constructor   ON constructor_standings(constructorId);
CREATE INDEX idx_cres_race            ON constructor_results(raceId);
CREATE INDEX idx_cres_constructor     ON constructor_results(constructorId);

ANALYZE;

-- -----------------------------------------------------------------------------
-- 7. Contrôle : nombre de lignes importées (attendu entre parenthèses)
-- -----------------------------------------------------------------------------
\echo '>> Contrôle des volumes'
SELECT 'seasons'               AS table_name, COUNT(*) AS lignes,     74 AS attendu FROM seasons
UNION ALL SELECT 'circuits',              COUNT(*),     77 FROM circuits
UNION ALL SELECT 'constructors',          COUNT(*),    211 FROM constructors
UNION ALL SELECT 'drivers',               COUNT(*),    857 FROM drivers
UNION ALL SELECT 'status',                COUNT(*),    139 FROM status
UNION ALL SELECT 'races',                 COUNT(*),   1101 FROM races
UNION ALL SELECT 'results',               COUNT(*),  26080 FROM results
UNION ALL SELECT 'sprint_results',        COUNT(*),    180 FROM sprint_results
UNION ALL SELECT 'qualifying',            COUNT(*),   9815 FROM qualifying
UNION ALL SELECT 'lap_times',             COUNT(*), 551742 FROM lap_times
UNION ALL SELECT 'pit_stops',             COUNT(*),  10089 FROM pit_stops
UNION ALL SELECT 'driver_standings',      COUNT(*),  34124 FROM driver_standings
UNION ALL SELECT 'constructor_standings', COUNT(*),  13051 FROM constructor_standings
UNION ALL SELECT 'constructor_results',   COUNT(*),  12290 FROM constructor_results;