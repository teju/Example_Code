ALTER TABLE `tWallet` ADD `imei` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `user_id`;

ALTER TABLE `tWallet` CHANGE `created_dt` `created_dt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `tCertificate` CHANGE `photo` `photo` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT 'no_image.jpg';

ALTER TABLE `tcertificate` CHANGE `created_dt` `created_dt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `tTag` ADD `is_standard_tag` TINYINT(1) NOT NULL DEFAULT '0' AFTER `tag_value`;
`ALTER TABLE tTag DROP INDEX tag_name;`
ALTER TABLE `tTag` ADD UNIQUE( `tag_name`, `tag_value`, `is_standard_tag`, `org_id`);