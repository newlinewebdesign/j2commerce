--
-- Issue #893: Default email option
--
-- Adds an `is_default` flag to email templates so the store owner can
-- pin a single fallback template that is sent when no template matches
-- a given order's status / receiver / payment / customer-group filters.
--
-- On upgrade, the first published Confirmed (orderstatus_id='1')
-- transactional template is seeded as the default — but only if no
-- row is already flagged.
--
-- Tracking: GitHub issue #893
--

ALTER TABLE `#__j2commerce_emailtemplates`
    ADD COLUMN `is_default` tinyint NOT NULL DEFAULT 0 /** CAN FAIL **/;

UPDATE `#__j2commerce_emailtemplates`
   SET `is_default` = 1
 WHERE `j2commerce_emailtemplate_id` = (
        SELECT `pk` FROM (
            SELECT MIN(`j2commerce_emailtemplate_id`) AS `pk`
              FROM `#__j2commerce_emailtemplates`
             WHERE `enabled` = 1
               AND `email_type` = 'transactional'
               AND `orderstatus_id` = '1'
        ) AS `seed`
   )
   AND (
        SELECT `cnt` FROM (
            SELECT COUNT(*) AS `cnt`
              FROM `#__j2commerce_emailtemplates`
             WHERE `is_default` = 1
        ) AS `chk`
   ) = 0;
