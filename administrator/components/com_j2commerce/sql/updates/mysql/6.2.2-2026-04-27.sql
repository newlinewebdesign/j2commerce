--
-- Refresh ISO 3166-1 country list (issue #857).
-- Renames legacy entries to current short names and adds post-2007 additions.
-- Existing primary keys are preserved so customer/order address FKs are unaffected.
-- Only Zaire (ZR/ZAR) shifts to its successor codes (CD/COD); numeric 180 unchanged.
--

UPDATE `#__j2commerce_countries` SET `country_name` = 'Bosnia and Herzegovina' WHERE `j2commerce_country_id` = 27 AND `country_name` = 'Bosnia and Herzegowina';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Cabo Verde' WHERE `j2commerce_country_id` = 39 AND `country_name` = 'Cape Verde';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Côte d\'Ivoire' WHERE `j2commerce_country_id` = 52 AND `country_name` = 'Cote D\'Ivoire';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Czechia' WHERE `j2commerce_country_id` = 56 AND `country_name` = 'Czech Republic';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Guinea-Bissau' WHERE `j2commerce_country_id` = 91 AND `country_name` = 'Guinea-bissau';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Heard Island and McDonald Islands' WHERE `j2commerce_country_id` = 94 AND `country_name` = 'Heard and Mc Donald Islands';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Korea, Democratic People\'s Republic of' WHERE `j2commerce_country_id` = 112 AND `country_name` = 'North Korea';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Libya' WHERE `j2commerce_country_id` = 121 AND `country_name` = 'Libyan Arab Jamahiriya';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Macao' WHERE `j2commerce_country_id` = 125 AND `country_name` = 'Macau';
UPDATE `#__j2commerce_countries` SET `country_name` = 'North Macedonia' WHERE `j2commerce_country_id` = 126 AND `country_name` = 'Macedonia, The Former Yugoslav Republic of';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Réunion' WHERE `j2commerce_country_id` = 174 AND `country_name` = 'Reunion';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Saint Helena, Ascension and Tristan da Cunha' WHERE `j2commerce_country_id` = 197 AND `country_name` = 'St. Helena';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Saint Pierre and Miquelon' WHERE `j2commerce_country_id` = 198 AND `country_name` = 'St. Pierre and Miquelon';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Eswatini' WHERE `j2commerce_country_id` = 202 AND `country_name` = 'Swaziland';
UPDATE `#__j2commerce_countries` SET `country_name` = 'Congo, Democratic Republic of the', `country_isocode_2` = 'CD', `country_isocode_3` = 'COD' WHERE `j2commerce_country_id` = 237 AND `country_isocode_2` = 'ZR';

INSERT IGNORE INTO `#__j2commerce_countries` (`j2commerce_country_id`, `country_name`, `country_isocode_2`, `country_isocode_3`, `country_isocode_num`, `enabled`, `ordering`) VALUES
    (242, 'Åland Islands', 'AX', 'ALA', 248, 1, 0),
    (243, 'Bonaire, Sint Eustatius and Saba', 'BQ', 'BES', 535, 1, 0),
    (244, 'Curaçao', 'CW', 'CUW', 531, 1, 0),
    (245, 'Guernsey', 'GG', 'GGY', 831, 1, 0),
    (246, 'Isle of Man', 'IM', 'IMN', 833, 1, 0),
    (247, 'Jersey', 'JE', 'JEY', 832, 1, 0),
    (248, 'Saint Barthélemy', 'BL', 'BLM', 652, 1, 0),
    (249, 'Saint Martin (French part)', 'MF', 'MAF', 663, 1, 0),
    (250, 'Sint Maarten (Dutch part)', 'SX', 'SXM', 534, 1, 0),
    (251, 'South Sudan', 'SS', 'SSD', 728, 1, 0);
