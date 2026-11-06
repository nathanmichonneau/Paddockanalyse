-- ============================================================
-- ajout_coordonnees.sql : ajoute la position GPS des circuits (carte Leaflet)
-- À exécuter UNE FOIS dans phpMyAdmin (onglet SQL) sur la base existante,
-- sans avoir besoin de ré-importer les données.
-- Si les colonnes existent déjà, ignorer l'erreur de la 1re instruction.
-- ============================================================
ALTER TABLE circuit ADD COLUMN latitude DECIMAL(9,6) NULL, ADD COLUMN longitude DECIMAL(9,6) NULL;

UPDATE circuit SET latitude = -37.8497, longitude = 144.9680 WHERE circuitId = 1; -- Albert Park Grand Prix Circuit
UPDATE circuit SET latitude = 2.7608, longitude = 101.7381 WHERE circuitId = 2; -- Sepang International Circuit
UPDATE circuit SET latitude = 26.0325, longitude = 50.5106 WHERE circuitId = 3; -- Bahrain International Circuit
UPDATE circuit SET latitude = 41.5700, longitude = 2.2611 WHERE circuitId = 4; -- Circuit de Barcelona-Catalunya
UPDATE circuit SET latitude = 40.9517, longitude = 29.4058 WHERE circuitId = 5; -- Istanbul Park
UPDATE circuit SET latitude = 43.7347, longitude = 7.4206 WHERE circuitId = 6; -- Circuit de Monaco
UPDATE circuit SET latitude = 45.5000, longitude = -73.5228 WHERE circuitId = 7; -- Circuit Gilles Villeneuve
UPDATE circuit SET latitude = 46.8642, longitude = 3.1633 WHERE circuitId = 8; -- Circuit de Nevers Magny-Cours
UPDATE circuit SET latitude = 52.0786, longitude = -1.0169 WHERE circuitId = 9; -- Silverstone Circuit
UPDATE circuit SET latitude = 49.3278, longitude = 8.5656 WHERE circuitId = 10; -- Hockenheimring
UPDATE circuit SET latitude = 47.5789, longitude = 19.2486 WHERE circuitId = 11; -- Hungaroring
UPDATE circuit SET latitude = 39.4589, longitude = -0.3317 WHERE circuitId = 12; -- Valencia Street Circuit
UPDATE circuit SET latitude = 50.4372, longitude = 5.9714 WHERE circuitId = 13; -- Circuit de Spa-Francorchamps
UPDATE circuit SET latitude = 45.6156, longitude = 9.2811 WHERE circuitId = 14; -- Autodromo Nazionale di Monza
UPDATE circuit SET latitude = 1.2914, longitude = 103.8639 WHERE circuitId = 15; -- Marina Bay Street Circuit
UPDATE circuit SET latitude = 35.3717, longitude = 138.9272 WHERE circuitId = 16; -- Fuji Speedway
UPDATE circuit SET latitude = 31.3389, longitude = 121.2197 WHERE circuitId = 17; -- Shanghai International Circuit
UPDATE circuit SET latitude = -23.7036, longitude = -46.6997 WHERE circuitId = 18; -- Autódromo José Carlos Pace
UPDATE circuit SET latitude = 39.7950, longitude = -86.2347 WHERE circuitId = 19; -- Indianapolis Motor Speedway
UPDATE circuit SET latitude = 50.3356, longitude = 6.9475 WHERE circuitId = 20; -- Nürburgring
UPDATE circuit SET latitude = 44.3439, longitude = 11.7167 WHERE circuitId = 21; -- Autodromo Enzo e Dino Ferrari
UPDATE circuit SET latitude = 34.8431, longitude = 136.5411 WHERE circuitId = 22; -- Suzuka Circuit
UPDATE circuit SET latitude = 36.1147, longitude = -115.1728 WHERE circuitId = 80; -- Las Vegas Strip Street Circuit
UPDATE circuit SET latitude = 24.4672, longitude = 54.6031 WHERE circuitId = 24; -- Yas Marina Circuit
UPDATE circuit SET latitude = -34.6943, longitude = -58.4593 WHERE circuitId = 25; -- Autódromo Juan y Oscar Gálvez
UPDATE circuit SET latitude = 36.7083, longitude = -6.0342 WHERE circuitId = 26; -- Circuito de Jerez
UPDATE circuit SET latitude = 38.7506, longitude = -9.3947 WHERE circuitId = 27; -- Autódromo do Estoril
UPDATE circuit SET latitude = 34.9150, longitude = 134.2211 WHERE circuitId = 28; -- Okayama International Circuit
UPDATE circuit SET latitude = -34.9272, longitude = 138.6170 WHERE circuitId = 29; -- Adelaide Street Circuit
UPDATE circuit SET latitude = -25.9894, longitude = 28.0767 WHERE circuitId = 30; -- Kyalami
UPDATE circuit SET latitude = 52.8306, longitude = -1.3750 WHERE circuitId = 31; -- Donington Park
UPDATE circuit SET latitude = 19.4042, longitude = -99.0907 WHERE circuitId = 32; -- Autódromo Hermanos Rodríguez
UPDATE circuit SET latitude = 33.4479, longitude = -112.0719 WHERE circuitId = 33; -- Phoenix street circuit
UPDATE circuit SET latitude = 43.2506, longitude = 5.7917 WHERE circuitId = 34; -- Circuit Paul Ricard
UPDATE circuit SET latitude = 34.7333, longitude = 126.4167 WHERE circuitId = 35; -- Korean International Circuit
UPDATE circuit SET latitude = -22.9756, longitude = -43.3950 WHERE circuitId = 36; -- Autódromo Internacional Nelson Piquet
UPDATE circuit SET latitude = 42.3298, longitude = -83.0401 WHERE circuitId = 37; -- Detroit Street Circuit
UPDATE circuit SET latitude = 51.3569, longitude = 0.2631 WHERE circuitId = 38; -- Brands Hatch
UPDATE circuit SET latitude = 52.3888, longitude = 4.5409 WHERE circuitId = 39; -- Circuit Park Zandvoort
UPDATE circuit SET latitude = 50.9894, longitude = 5.2556 WHERE circuitId = 40; -- Zolder
UPDATE circuit SET latitude = 47.3625, longitude = 4.8992 WHERE circuitId = 41; -- Dijon-Prenois
UPDATE circuit SET latitude = 32.7788, longitude = -96.7600 WHERE circuitId = 42; -- Fair Park
UPDATE circuit SET latitude = 33.7650, longitude = -118.1892 WHERE circuitId = 43; -- Long Beach
UPDATE circuit SET latitude = 36.1162, longitude = -115.1740 WHERE circuitId = 44; -- Las Vegas Street Circuit
UPDATE circuit SET latitude = 40.6170, longitude = -3.5850 WHERE circuitId = 45; -- Jarama
UPDATE circuit SET latitude = 42.3369, longitude = -76.9272 WHERE circuitId = 46; -- Watkins Glen
UPDATE circuit SET latitude = 57.2653, longitude = 13.6042 WHERE circuitId = 47; -- Scandinavian Raceway
UPDATE circuit SET latitude = 44.0481, longitude = -78.6756 WHERE circuitId = 48; -- Mosport International Raceway
UPDATE circuit SET latitude = 41.3647, longitude = 2.1556 WHERE circuitId = 49; -- Montjuïc
UPDATE circuit SET latitude = 50.5972, longitude = 4.3256 WHERE circuitId = 50; -- Nivelles-Baulers
UPDATE circuit SET latitude = 45.7597, longitude = 3.0392 WHERE circuitId = 51; -- Charade Circuit
UPDATE circuit SET latitude = 46.1877, longitude = -74.6099 WHERE circuitId = 52; -- Circuit Mont-Tremblant
UPDATE circuit SET latitude = 49.3306, longitude = 0.9533 WHERE circuitId = 53; -- Rouen-Les-Essarts
UPDATE circuit SET latitude = 47.9500, longitude = 0.2078 WHERE circuitId = 54; -- Le Mans
UPDATE circuit SET latitude = 49.2542, longitude = 3.9306 WHERE circuitId = 55; -- Reims-Gueux
UPDATE circuit SET latitude = -33.0486, longitude = 27.8736 WHERE circuitId = 56; -- Prince George Circuit
UPDATE circuit SET latitude = 47.2039, longitude = 14.7478 WHERE circuitId = 57; -- Zeltweg
UPDATE circuit SET latitude = 53.4769, longitude = -2.9406 WHERE circuitId = 58; -- Aintree
UPDATE circuit SET latitude = 41.1675, longitude = -8.6556 WHERE circuitId = 59; -- Circuito da Boavista
UPDATE circuit SET latitude = 33.9372, longitude = -117.2731 WHERE circuitId = 60; -- Riverside International Raceway
UPDATE circuit SET latitude = 52.4803, longitude = 13.2503 WHERE circuitId = 61; -- AVUS
UPDATE circuit SET latitude = 38.7253, longitude = -9.1953 WHERE circuitId = 62; -- Monsanto Park Circuit
UPDATE circuit SET latitude = 27.4503, longitude = -81.3544 WHERE circuitId = 63; -- Sebring International Raceway
UPDATE circuit SET latitude = 33.5703, longitude = -7.6753 WHERE circuitId = 64; -- Ain Diab
UPDATE circuit SET latitude = 42.4747, longitude = 14.1472 WHERE circuitId = 65; -- Pescara Circuit
UPDATE circuit SET latitude = 46.9647, longitude = 7.4258 WHERE circuitId = 66; -- Circuit Bremgarten
UPDATE circuit SET latitude = 41.3900, longitude = 2.1150 WHERE circuitId = 67; -- Circuit de Pedralbes
UPDATE circuit SET latitude = 28.3487, longitude = 77.5331 WHERE circuitId = 68; -- Buddh International Circuit
UPDATE circuit SET latitude = 30.1328, longitude = -97.6411 WHERE circuitId = 69; -- Circuit of the Americas
UPDATE circuit SET latitude = 47.2197, longitude = 14.7647 WHERE circuitId = 70; -- Red Bull Ring
UPDATE circuit SET latitude = 43.4057, longitude = 39.9578 WHERE circuitId = 71; -- Sochi Autodrom
UPDATE circuit SET latitude = 40.3725, longitude = 49.8533 WHERE circuitId = 73; -- Baku City Circuit
UPDATE circuit SET latitude = 37.2270, longitude = -8.6267 WHERE circuitId = 75; -- Autódromo Internacional do Algarve
UPDATE circuit SET latitude = 43.9975, longitude = 11.3719 WHERE circuitId = 76; -- Autodromo Internazionale del Mugello
UPDATE circuit SET latitude = 21.6319, longitude = 39.1044 WHERE circuitId = 77; -- Jeddah Corniche Circuit
UPDATE circuit SET latitude = 25.4900, longitude = 51.4542 WHERE circuitId = 78; -- Losail International Circuit
UPDATE circuit SET latitude = 25.9581, longitude = -80.2389 WHERE circuitId = 79; -- Miami International Autodrome
