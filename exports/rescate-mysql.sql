SET NAMES utf8mb4;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================
-- RESCATE DE DATOS PARA DB DE PRODUCCIÓN juegpdee_base
-- Generado automáticamente por scripts/generate-rescue-sql.js
-- Compatible con schema MySQL 5.6 exportado el 09/04/2026
-- ============================================================

-- Eliminar datos de ejemplo iniciales
DELETE FROM jugadores WHERE nombre IN ('Juan Pérez', 'Luis Gómez', 'Pablo Campos');
DELETE FROM partidos WHERE id IN (1, 2);

-- Corrección de rutas de escudo (producción usa /logos/, app espera /assets-equipos/logos/)

UPDATE equipos SET escudo = '/assets-equipos/logos/roberts.png' WHERE nombre = 'Cecil A. Roberts';
UPDATE equipos SET escudo = '/assets-equipos/logos/uniontl.png' WHERE nombre = 'Unión Deportiva';
UPDATE equipos SET escudo = '/assets-equipos/logos/bonifacio.png' WHERE nombre = 'Unión de Bonifacio';
UPDATE equipos SET escudo = '/assets-equipos/logos/garre.png' WHERE nombre = 'Deportivo Garré';
UPDATE equipos SET escudo = '/assets-equipos/logos/maza.png' WHERE nombre = 'Deportivo Maza';
UPDATE equipos SET escudo = '/assets-equipos/logos/deportivoarg.png' WHERE nombre = 'Deportivo Argentino';
UPDATE equipos SET escudo = '/assets-equipos/logos/quenuma.png' WHERE nombre = 'Atlético Quenumá';
UPDATE equipos SET escudo = '/assets-equipos/logos/argentinos.png' WHERE nombre = 'Atlético Argentino';
UPDATE equipos SET escudo = '/assets-equipos/logos/salazar.png' WHERE nombre = 'Salazar FC';
UPDATE equipos SET escudo = '/assets-equipos/logos/newbery.png' WHERE nombre = 'Jorge Newbery';
UPDATE equipos SET escudo = '/assets-equipos/logos/ceibo.png' WHERE nombre = 'El Ceibo';
UPDATE equipos SET escudo = '/assets-equipos/logos/lagloria.png' WHERE nombre = 'La Gloria';
UPDATE equipos SET escudo = '/assets-equipos/logos/17.png' WHERE nombre = 'Deportivo 17';
UPDATE equipos SET escudo = '/assets-equipos/logos/juventud.png' WHERE nombre = 'Juventud Unida';
UPDATE equipos SET escudo = '/assets-equipos/logos/ferro.png' WHERE nombre = 'Ferro';

-- Equipos faltantes detectados en scripts legados

INSERT INTO equipos (nombre, estadio, escudo, categoria) VALUES ('Villa del Parque', 'Estadio Local', '/assets-equipos/logos/villa.png', 'Primera')
ON DUPLICATE KEY UPDATE
    estadio = VALUES(estadio),
    escudo = VALUES(escudo);

INSERT INTO equipos (nombre, estadio, escudo, categoria) VALUES ('Villa del Parque', 'Estadio Local', '/assets-equipos/logos/villa.png', 'Tercera')
ON DUPLICATE KEY UPDATE
    estadio = VALUES(estadio),
    escudo = VALUES(escudo);

-- Jugadores recuperados desde scripts/import-jugadores-direct.js

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Arboleda Angel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Sarasola Matias', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Labín Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gertner Genaro', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Fernandez Benjamín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Moggia Lucio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Duckardt Sebastian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Risso Jeremias', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Kuwalsuk Angel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Rodi Matías', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ditzel Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Rivero Cayetano', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Alvarez Francisco', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gonzalez Fernando', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Paturlane Santos', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Herrera Facundo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Marduel Agustín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Cotignola Francisco', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Montes Maximiliano', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Pescatori Felipe', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Alvarez Nicolas', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ibañez Mateo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Garcia Tobias', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ibañez David', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Cepeda Lucas', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Boquín Nicolás', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Maya Federico', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Vidaurre Iñaky', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Wilchez Bruno', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Bermejo Rodrigo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Bornes Kevin', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Cerna Cristian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Quiroga Ignacio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Perroud Federico', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'García Jordan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Avalos Enzo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Vela Lucas', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Lopez Angel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Caceres Elias', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Lara Joaquín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Martinez Miguel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Candia Fabricio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Rodríguez Tomás', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Sanchez Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Calvo Tobias', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gonzalez Herberto', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Sica Axel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Prienza Federico', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Mascioli Benicio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Silva Nicolas', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Barni Franco', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Mendez Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Correa Jaime', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gallardo Santiago', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Colagioia Matías', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ducasse Manuel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Benito Carlos', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Contreras Mariano', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Espinola Santiago', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Mansilla Mariano', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Lucas Ulises', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Di Palma Enzo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Anania Franco', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Caris Agustín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Prieto Gonzalo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Olaverria Valentin', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Reale Exequiel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Álvarez Braian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Fernandez German', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Suarez Milton', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Silva Alejandro', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Herrera Esteban', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Proetto Ignacio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'González Dardo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Calabaza Franco', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Silva Jonatan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Saucedo Sandro', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Taborda Ignacio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Rodríguez Braian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Mac Axel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Mang Emiliano', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Castillo Ignacio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ghinzani Federico', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Álvarez Braian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Soto Richard', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Souza Lautaro', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Flores Nahuel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gómez Adrián', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Monteventi Rocco', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Domínguez Geremías', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Culasso Gerónimo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Flores Daniel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Calabaza Francisco', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Correa Federico', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Pérez Federico', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Rosales Federico', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Calabaza Franco', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Briozzo Agustín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Olivares Damián', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Correa Agustín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Luquez Joel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Horbal Matías', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Bertolotti Julian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ponce Marcos', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Sosa Julian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Aquino Alejandro', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Sandoval Ulises', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Diego Matias', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Aul Julian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Argañin Leo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Romero Martin', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gonzalez Facundo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Villarreta Carlos', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gonzalez Julian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Lucero Cristian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Marañon Fermín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Sanchez Tomaa', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Calvos Matías', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Argañin Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Puente Franco', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Sanchez Lautaro', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Galván Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gramajo Boris', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Barrera Agustin', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ometto Claudio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Cerjenko Gastón', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Soto Marcelo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Di Paula Martín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Barros Maximiliano', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gonzalez Sebastian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Puente Fermín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Marroquín Vicente', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Romero Maximo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Bellegia Matías', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Barrientos Jeremias', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ometto Mateo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Medina Gabriel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Santander Alexis', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Córdoba Daniel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Martino Luciano', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Luna Braian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Petti Elías', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Huerta Paulo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Méndez Ciro', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Aguirre Gaspar', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Galeano Joaquín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Digiacomo Gabriel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Calvo Agustin', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Serra Yenien', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Coña Jorge', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Palomeque Sebastian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gonzalez Christian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Troya Lucas', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Domínguez Sebastián', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Peñas Ignacio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Caicedo Kevin', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Cuevas Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ramos Luciano', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Medina Cesar', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Candia Enzo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ibañez Nicolás', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Cardozo Hector', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Barrios Tobias', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Illesca Sebastián', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Siri Ignacio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Herrero Donato', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Corral Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Palacios Alejo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Aranda Sergio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Paterlini Emanuel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ávila Jeremías', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Farias Alan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Martín Gonzalo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Semino Braian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Changazzo Braian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Zárate Joaquín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ramírez Diego', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Tomasello Uriel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Zárate Cruz', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Barrio Agustin', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Carnero Julian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Toledo Pedro', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Toribio Facundo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Villagra Carlos', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Burrieza Leonel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Del Arco Matías', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Pelosi Enzo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ibañez Matias', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Navarro Joaquín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Aro Valentín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Arenas Elias', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gonzalez Maximo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Lopez Joaquín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Hoyos Matías', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Sardon José', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Martín Michael', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Fraile Thiago', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Borregon Joaquín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Guerrero Joaquín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Toscano Julian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Enrriquez Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Arancivia Simon', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gúzman Roman', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Moralejo Manuel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Fraile Alexis', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gómez Agustín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Duarte Agustín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Feloy Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Borniego Felipe', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ramírez Francisco', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Leiva Mariano', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Martinez Uriel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Achaval Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Achaval Diego', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Fernandez Michel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Leiva Yaco', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Cepeda Felipe', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Torres Julian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Achaval Gonzalo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Satarain Santiago', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gallego Alejandro', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Coña Antonio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Roman Joel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Toribio Kevin', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Coña Julian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Ibañez Ezequiel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Martins Ignacio', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Barrio Joaquín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Gomez Diego', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Sandalich Nahuel', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Benavidez Joan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Guiyelo Benjamin', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Bravo Tomas', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Miranda Cristian', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Coña Agustín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Esteban Juan', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Del Río Ricardo', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Root Martín', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
SELECT 'Rodriguez Tiago', 'No asignada', e.id, 0, 0, 0
FROM equipos e
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);

-- Partidos recuperados desde scripts legados

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-09', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 3, 1, 0, 0, 'Apertura', 'Primera', 1, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo 17' AND el.categoria = 'Primera'
  AND ev.nombre = 'Deportivo Argentino' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-09', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 3, 0, 0, 'Apertura', 'Primera', 1, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Juventud Unida' AND el.categoria = 'Primera'
  AND ev.nombre = 'Deportivo Garré' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-09', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 2, 1, 0, 0, 'Apertura', 'Primera', 1, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Cecil A. Roberts' AND el.categoria = 'Primera'
  AND ev.nombre = 'Unión de Bonifacio' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-09', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 1, 0, 0, 'Apertura', 'Primera', 1, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Atlético Argentino' AND el.categoria = 'Primera'
  AND ev.nombre = 'Deportivo Maza' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-09', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 1, 0, 0, 'Apertura', 'Primera', 1, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'El Ceibo' AND el.categoria = 'Primera'
  AND ev.nombre = 'Unión Deportiva' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-09', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 1, 0, 0, 'Apertura', 'Primera', 1, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Atlético Quenumá' AND el.categoria = 'Primera'
  AND ev.nombre = 'La Gloria' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-09', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 0, 0, 0, 'Apertura', 'Primera', 1, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Salazar FC' AND el.categoria = 'Primera'
  AND ev.nombre = 'Villa del Parque' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 2, 1, 0, 0, 'Apertura', 'Primera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Unión Deportiva' AND el.categoria = 'Primera'
  AND ev.nombre = 'Atlético Argentino' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 2, 0, 0, 'Apertura', 'Primera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Argentino' AND el.categoria = 'Primera'
  AND ev.nombre = 'Juventud Unida' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 2, 0, 0, 'Apertura', 'Primera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Maza' AND el.categoria = 'Primera'
  AND ev.nombre = 'Atlético Quenumá' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 1, 0, 0, 'Apertura', 'Primera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Villa del Parque' AND el.categoria = 'Primera'
  AND ev.nombre = 'El Ceibo' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 1, 0, 0, 'Apertura', 'Primera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Unión de Bonifacio' AND el.categoria = 'Primera'
  AND ev.nombre = 'Deportivo 17' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 2, 3, 0, 0, 'Apertura', 'Primera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Garré' AND el.categoria = 'Primera'
  AND ev.nombre = 'Salazar FC' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 1, 0, 0, 'Apertura', 'Primera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'La Gloria' AND el.categoria = 'Primera'
  AND ev.nombre = 'Jorge Newbery' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 5, 0, 0, 'Apertura', 'Primera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Atlético Quenumá' AND el.categoria = 'Primera'
  AND ev.nombre = 'Unión Deportiva' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 1, 0, 0, 'Apertura', 'Primera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'El Ceibo' AND el.categoria = 'Primera'
  AND ev.nombre = 'Deportivo Garré' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 3, 0, 0, 'Apertura', 'Primera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo 17' AND el.categoria = 'Primera'
  AND ev.nombre = 'Cecil A. Roberts' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 5, 2, 0, 0, 'Apertura', 'Primera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Jorge Newbery' AND el.categoria = 'Primera'
  AND ev.nombre = 'Deportivo Maza' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 2, 0, 0, 'Apertura', 'Primera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Atlético Argentino' AND el.categoria = 'Primera'
  AND ev.nombre = 'Villa del Parque' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 2, 2, 0, 0, 'Apertura', 'Primera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Salazar FC' AND el.categoria = 'Primera'
  AND ev.nombre = 'Deportivo Argentino' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 2, 0, 0, 0, 'Apertura', 'Primera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Juventud Unida' AND el.categoria = 'Primera'
  AND ev.nombre = 'Unión de Bonifacio' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Cecil A. Roberts' AND el.categoria = 'Primera'
  AND ev.nombre = 'Juventud Unida' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Unión Deportiva' AND el.categoria = 'Primera'
  AND ev.nombre = 'Jorge Newbery' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Villa del Parque' AND el.categoria = 'Primera'
  AND ev.nombre = 'Atlético Quenumá' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Garré' AND el.categoria = 'Primera'
  AND ev.nombre = 'Atlético Argentino' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Argentino' AND el.categoria = 'Primera'
  AND ev.nombre = 'El Ceibo' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Maza' AND el.categoria = 'Primera'
  AND ev.nombre = 'La Gloria' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Unión de Bonifacio' AND el.categoria = 'Primera'
  AND ev.nombre = 'Salazar FC' AND ev.categoria = 'Primera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 0, 0, 0, 'Apertura', 'Tercera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'La Gloria' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Jorge Newbery' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 0, 0, 0, 'Apertura', 'Tercera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Maza' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Atlético Quenumá' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 2, 0, 0, 0, 'Apertura', 'Tercera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Unión Deportiva' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Atlético Argentino' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 3, 0, 0, 'Apertura', 'Tercera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Unión de Bonifacio' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Deportivo 17' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 2, 0, 0, 'Apertura', 'Tercera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Argentino' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Juventud Unida' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 4, 0, 0, 'Apertura', 'Tercera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Villa del Parque' AND el.categoria = 'Tercera'
  AND ev.nombre = 'El Ceibo' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-16', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 4, 3, 0, 0, 'Apertura', 'Tercera', 2, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Garré' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Salazar FC' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 1, 0, 0, 'Apertura', 'Tercera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Jorge Newbery' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Deportivo Maza' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 0, 0, 0, 'Apertura', 'Tercera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo 17' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Cecil A. Roberts' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 2, 0, 0, 0, 'Apertura', 'Tercera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Atlético Argentino' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Villa del Parque' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 0, 0, 0, 'Apertura', 'Tercera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Atlético Quenumá' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Unión Deportiva' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 2, 1, 0, 0, 'Apertura', 'Tercera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Juventud Unida' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Unión de Bonifacio' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 3, 0, 0, 'Apertura', 'Tercera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Salazar FC' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Deportivo Argentino' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-23', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 2, 3, 0, 0, 'Apertura', 'Tercera', 3, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'El Ceibo' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Deportivo Garré' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 3, 0, 0, 0, 'Apertura', 'Tercera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Cecil A. Roberts' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Juventud Unida' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 4, 0, 0, 0, 'Apertura', 'Tercera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Unión Deportiva' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Jorge Newbery' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'postergado', 0, 0, 0, 0, 'Apertura', 'Tercera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Villa del Parque' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Atlético Quenumá' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 1, 0, 0, 'Apertura', 'Tercera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Garré' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Atlético Argentino' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 1, 1, 0, 0, 'Apertura', 'Tercera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Argentino' AND el.categoria = 'Tercera'
  AND ev.nombre = 'El Ceibo' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 3, 4, 0, 0, 'Apertura', 'Tercera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Deportivo Maza' AND el.categoria = 'Tercera'
  AND ev.nombre = 'La Gloria' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
SELECT '2026-03-30', '15:00:00', COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, 'jugado', 0, 1, 0, 0, 'Apertura', 'Tercera', 4, NULL
FROM equipos el JOIN equipos ev ON 1 = 1
WHERE el.nombre = 'Unión de Bonifacio' AND el.categoria = 'Tercera'
  AND ev.nombre = 'Salazar FC' AND ev.categoria = 'Tercera'
ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);

-- Goles por jugador: UPDATE directo en jugadores.goles
-- Busca por nombre del goleador O sus aliases conocidos, dentro del equipo correcto

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 4
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
  AND (j.nombre IN ('Agustin Barrio', 'Barrio Agustin'));

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 3
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
  AND (j.nombre = 'Cesar Medina');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 3
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
  AND (j.nombre = 'Tomas Rodriguez');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 3
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
  AND (j.nombre = 'Lucas Vides');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 2
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
  AND (j.nombre = 'Joan Benavidez');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 2
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
  AND (j.nombre IN ('Alana Farias', 'Farias Alan'));

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 2
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
  AND (j.nombre = 'Uriel Tomasello');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 2
WHERE e.nombre = 'Unión Deportiva' AND e.categoria = 'Primera'
  AND (j.nombre = 'Cruz Zarate');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 2
WHERE e.nombre = 'Cecil A. Roberts' AND e.categoria = 'Primera'
  AND (j.nombre = 'Lucas Rivas');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 2
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
  AND (j.nombre = 'Maico Saucedo');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 2
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
  AND (j.nombre = 'Tobias Calvo');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
  AND (j.nombre IN ('Benjamin Guiyello', 'Guiyelo Benjamin'));

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Deportivo 17' AND e.categoria = 'Primera'
  AND (j.nombre = 'Nahuel Sandalich');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Deportivo Argentino' AND e.categoria = 'Primera'
  AND (j.nombre IN ('Dardo Gonzales', 'González Dardo', 'Gonzalez Dardo'));

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Cecil A. Roberts' AND e.categoria = 'Primera'
  AND (j.nombre = 'Robert Campaz');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Cecil A. Roberts' AND e.categoria = 'Primera'
  AND (j.nombre = 'Tomas Altamiranda');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
  AND (j.nombre = 'Diego Achaval');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
  AND (j.nombre = 'Matias Rodi');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Atlético Argentino' AND e.categoria = 'Primera'
  AND (j.nombre = 'Matias Hoyos');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
  AND (j.nombre = 'Daniel Flores');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
  AND (j.nombre = 'Rocco Monteventi');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
  AND (j.nombre = 'Lucas Cepeda');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
  AND (j.nombre = 'Tobias Garcia');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
  AND (j.nombre = 'Julian Sosa');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
  AND (j.nombre = 'Claudio Ometto');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Unión de Bonifacio' AND e.categoria = 'Primera'
  AND (j.nombre IN ('Juan Shaffer', 'Achaval Juan'));

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Deportivo Garré' AND e.categoria = 'Primera'
  AND (j.nombre = 'Benicio Mascioli');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
  AND (j.nombre = 'Marcelo Mansilla');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
  AND (j.nombre = 'Hector Cardozo');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Atlético Quenumá' AND e.categoria = 'Primera'
  AND (j.nombre = 'Ignacio Quiroga');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'La Gloria' AND e.categoria = 'Primera'
  AND (j.nombre = 'Leo Aguirre');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'El Ceibo' AND e.categoria = 'Primera'
  AND (j.nombre = 'Maximo Romero');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Jorge Newbery' AND e.categoria = 'Primera'
  AND (j.nombre = 'Nicolas Ibanez');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
  AND (j.nombre = 'Sebastian Duckardt');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Deportivo Maza' AND e.categoria = 'Primera'
  AND (j.nombre = 'Lucio Moggia');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
  AND (j.nombre = 'Matias Horbal');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Villa del Parque' AND e.categoria = 'Primera'
  AND (j.nombre IN ('Julian Bertoloti', 'Bertolotti Julian'));

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Cecil A. Roberts' AND e.categoria = 'Primera'
  AND (j.nombre = 'Julian Cocimano');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Salazar FC' AND e.categoria = 'Primera'
  AND (j.nombre = 'Carlos Benito');

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
  AND (j.nombre IN ('Lautaro Sousa', 'Souza Lautaro'));

UPDATE jugadores j JOIN equipos e ON j.equipo = e.id
SET j.goles = 1
WHERE e.nombre = 'Juventud Unida' AND e.categoria = 'Primera'
  AND (j.nombre IN ('Geronimo Culaso', 'Culasso Gerónimo', 'Culasso Geronimo'));

COMMIT;
