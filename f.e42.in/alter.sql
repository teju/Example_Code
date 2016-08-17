ALTER TABLE `twallet` ADD `location_id` INT( 11 ) NOT NULL AFTER `student_id` ;
ALTER TABLE `twallet` ADD `group_id` INT( 11 ) NOT NULL AFTER `location_id` ;
ALTER TABLE `twallet` CHANGE `loc_id` `location_id` INT( 11 ) NOT NULL ;
ALTER TABLE `tiisclog` ADD `log_dt` DATETIME NOT NULL AFTER `image` ;
ALTER TABLE `timeilocation` ADD `imeiloc_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
ALTER TABLE `tstudentlog` ADD `log_dt` DATETIME NULL AFTER `student_id` ;
ALTER TABLE `tstudentlog` ADD `status` VARCHAR( 45 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `time_of_day` ;
