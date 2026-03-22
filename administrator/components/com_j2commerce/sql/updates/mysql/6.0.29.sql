-- J2Commerce 6.0.29
-- Remove legacy router settings from component params (now hardcoded)
UPDATE `#__extensions`
SET `params` = JSON_REMOVE(JSON_REMOVE(`params`, '$.sef_router'), '$.sef_router_noids')
WHERE `element` = 'com_j2commerce'
AND `type` = 'component';
