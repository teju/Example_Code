ALTER TABLE `twallet` ADD `imei` VARCHAR( 45 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `user_id` ;

ALTER TABLE `twallet` ADD `comment` VARCHAR( 1000 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `balance_amount` ;


ALTER TABLE `tTag` ADD `is_standard_tag` TINYINT(1) NOT NULL DEFAULT '0' AFTER `tag_value`;

`ALTER TABLE tTag DROP INDEX tag_name;`

ALTER TABLE `tTag` ADD UNIQUE( `tag_name`, `tag_value`, `is_standard_tag`, `org_id`);

ALTER TABLE `tTag` ADD `is_tag_sync` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_standard_tag`;

ALTER TABLE `tTag` ADD `is_active` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_tag_sync`;

ALTER TABLE `tTag`  ADD `type` ENUM('PUBLIC','PRIVATE','INTERNAL') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'PUBLIC'  AFTER `is_tag_sync`;

ALTER TABLE `tCertificate` CHANGE `nfc_tag_id` `usn` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

`ALTER TABLE tCertificate DROP INDEX nfc_tag_id_UNIQUE;

ALTER TABLE `tCertificate` ADD UNIQUE( `usn`, `org_id`);

ALTER TABLE `tUser` ADD `otp` INT(6) NULL DEFAULT NULL AFTER `password`;

ALTER TABLE `tUser` ADD `otp_valid_dt` DATETIME NULL DEFAULT NULL AFTER `otp`;

ALTER TABLE `tUser` CHANGE `otp` `otp` BIGINT(18) NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `tNFCTag` (
  `nfc_tag_id` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `usn` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `created_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `org_id` int(11) NOT NULL,
  UNIQUE KEY `nfc_tag_id` (`nfc_tag_id`),
  UNIQUE KEY `usn` (`usn`,`org_id`),
  KEY `org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `tNFCTag`
  ADD CONSTRAINT `tnfctag_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `torg` (`org_id`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `tCertificate` ADD `status` ENUM('UNISSUED','UNSIGNED','SIGNED','LOST','DAMAGED') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'UNISSUED' AFTER `photo`;


CREATE TABLE IF NOT EXISTS `tOrder` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `email_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `mobile` bigint(18) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pmt_status` enum('PAID','PENDING','FAILED') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'PENDING',
  `pmt_type` enum('CC','DC','NB') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'CC',
  `order_status` enum('VALID','INVALID') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'INVALID',
  `comments` text COLLATE utf8_unicode_ci,
  `ordered_dt` datetime NOT NULL,
  `created_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime DEFAULT NULL,
  `org_id` int(11) NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `org_id` (`org_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

ALTER TABLE `tOrder`
  ADD CONSTRAINT `torder_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `tOrg` (`org_id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `tSetting` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `is_hidden` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `name` (`name`,`org_id`),
  KEY `org_id` (`org_id`),
  KEY `org_id_2` (`org_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

ALTER TABLE `tSetting`
  ADD CONSTRAINT `tsetting_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `tOrg` (`org_id`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `tUser` DROP `org_id`;