START TRANSACTION;

UPDATE jugadores
SET posicion = 'PEND'
WHERE TRIM(posicion) IN ('No asignad', 'No asignada', 'NO ASIGNAD', 'NO ASIGNADA');

COMMIT;

SELECT posicion, COUNT(*) AS total
FROM jugadores
GROUP BY posicion
ORDER BY total DESC;
