

#1 - Finde item_base, hvor der kun er priser for en forretning
INSERT INTO jac (item_base_id) 

SELECT item_base_id FROM (
SELECT COUNT(record_store_id) AS nCount, item_base_id FROM (

SELECT record_store_id, item_base_id FROM item_price WHERE item_base_id IN (
SELECT item_base_id FROM item_price
WHERE record_store_id = 19
)
GROUP BY record_store_id, item_base_id
) AS Res

GROUP BY item_base_id
) AS Res2
WHERE Res2.nCount = 1
-- LIMIT 1, 10000


#3 - Fjerne sange / album 
DELETE FROM 
item_base WHERE item_base_id IN (
SELECT item_base_id FROM airplay_music_v1.jac
);

#4 - Fjerne priser
DELETE FROM 
item_price WHERE item_base_id IN (
SELECT item_base_id FROM airplay_music_v1.jac
);



