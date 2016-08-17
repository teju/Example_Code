-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema certified42
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema certified42
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `certified42` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `certified42` ;

-- -----------------------------------------------------
-- Table `certified42`.`tOrg`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `certified42`.`tOrg` (
  `org_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NULL,
  `logo` VARCHAR(200) NULL,
  `created_dt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`org_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `certified42`.`tUser`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `certified42`.`tUser` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `photo` VARCHAR(100) NULL,
  `email_id` VARCHAR(100) NULL,
  `password` VARCHAR(50) NULL,
  `user_type` ENUM('PUBLIC','LICENSED','ISSUER','ADMIN') NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `created_dt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `org_id` INT(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  INDEX `org_id` (`org_id` ASC),
  CONSTRAINT `org_id3`
    FOREIGN KEY (`org_id`)
    REFERENCES `certified42`.`tOrg` (`org_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `certified42`.`tUserIMEI`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `certified42`.`tUserIMEI` (
  `user_id` INT(11) NOT NULL,
  `imei` VARCHAR(45) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `created_dt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `user_id_idx` (`user_id` ASC),
  UNIQUE INDEX `imei_UNIQUE` (`imei` ASC),
  CONSTRAINT `user_id5`
    FOREIGN KEY (`user_id`)
    REFERENCES `certified42`.`tUser` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `certified42`.`tWallet`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `certified42`.`tWallet` (
  `wallet_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `created_dt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` VARCHAR(500) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `transaction_type` ENUM('TOPUP','PURCHASE') NULL,
  `amount` DECIMAL(10,2) NULL DEFAULT 0.00,
  `balance_amount` DECIMAL(10,2) NULL DEFAULT 0.00,
  `org_id` INT(11) NOT NULL,
  PRIMARY KEY (`wallet_id`),
  INDEX `user_id_idx` (`user_id` ASC),
  INDEX `org_id` (`org_id` ASC),
  CONSTRAINT `user_id_idx`
    FOREIGN KEY (`user_id`)
    REFERENCES `certified42`.`tUser` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `org_id1`
    FOREIGN KEY (`org_id`)
    REFERENCES `certified42`.`tOrg` (`org_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `certified42`.`tCertificate`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `certified42`.`tCertificate` (
  `certificate_id` INT(11) NOT NULL AUTO_INCREMENT,
  `nfc_tag_id` VARCHAR(45) NOT NULL,
  `name` VARCHAR(200) NULL,
  `photo` VARCHAR(100) NULL,
  `created_dt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `org_id` INT(11) NOT NULL,
  PRIMARY KEY (`certificate_id`),
  UNIQUE INDEX `nfc_tag_id_UNIQUE` (`nfc_tag_id` ASC),
  UNIQUE INDEX `org_id` (`org_id` ASC),
  CONSTRAINT `org_id2`
    FOREIGN KEY (`org_id`)
    REFERENCES `certified42`.`tOrg` (`org_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `certified42`.`tSearch`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `certified42`.`tSearch` (
  `search_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `certificate_id` INT(11) NOT NULL,
  `search_result` VARCHAR(200) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `created_dt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `org_id` INT(11) NOT NULL,
  PRIMARY KEY (`search_id`),
  INDEX `user_id` (`user_id` ASC),
  INDEX `certificate_id` (`certificate_id` ASC),
  INDEX `org_id` (`org_id` ASC),
  CONSTRAINT `user_id7`
    FOREIGN KEY (`user_id`)
    REFERENCES `certified42`.`tUser` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `certificate_id2`
    FOREIGN KEY (`certificate_id`)
    REFERENCES `certified42`.`tCertificate` (`certificate_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `org_id4`
    FOREIGN KEY (`org_id`)
    REFERENCES `certified42`.`tOrg` (`org_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `certified42`.`tTag`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `certified42`.`tTag` (
  `tag_id` INT(11) NOT NULL AUTO_INCREMENT,
  `tag_name` VARCHAR(200) NULL,
  `tag_value` VARCHAR(200) NULL,
  `org_id` INT(11) NULL,
  PRIMARY KEY (`tag_id`),
  INDEX `org_id` (`org_id` ASC),
  CONSTRAINT `org_id5`
    FOREIGN KEY (`org_id`)
    REFERENCES `certified42`.`tOrg` (`org_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `certified42`.`tCertTag`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `certified42`.`tCertTag` (
  `certtag_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  INDEX `certtag_id` (`certtag_id` ASC),
  INDEX `tag_id` (`tag_id` ASC),
  CONSTRAINT `certtag_id`
    FOREIGN KEY (`certtag_id`)
    REFERENCES `certified42`.`tCertificate` (`certificate_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `tag_id`
    FOREIGN KEY (`tag_id`)
    REFERENCES `certified42`.`tTag` (`tag_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
