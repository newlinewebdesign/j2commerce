-- Register and enable the J2Commerce Web Services API plugin
INSERT INTO `#__extensions` (`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `manifest_cache`, `params`, `ordering`, `state`)
SELECT 'plg_webservices_j2commerce', 'plugin', 'j2commerce', 'webservices', 0, 1, 1, '{}', '{}', 0, 0
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `#__extensions` WHERE `type` = 'plugin' AND `element` = 'j2commerce' AND `folder` = 'webservices'
);
