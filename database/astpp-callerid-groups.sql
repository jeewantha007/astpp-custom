-- ##############################################################################
-- ASTPP - Open Source VoIP Billing Solution
-- Caller ID Group Customization - Database Migration
-- ##############################################################################

-- 1. Create table for Caller ID Groups
CREATE TABLE IF NOT EXISTS `callerid_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `reseller_id` int NOT NULL DEFAULT '0' COMMENT '0 for Admin, >0 for Reseller',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: active, 1: inactive',
  `creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reseller_id` (`reseller_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 2. Create table for Caller ID Group Numbers
CREATE TABLE IF NOT EXISTS `callerid_group_numbers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL DEFAULT '0',
  `callerid_number` varchar(30) NOT NULL DEFAULT '',
  `callerid_name` varchar(50) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: active, 1: inactive',
  `creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_group_number` (`group_id`, `callerid_number`),
  KEY `callerid_number` (`callerid_number`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3. Idempotently add callerid_group_id to accounts table
DROP PROCEDURE IF EXISTS `add_callerid_group_id_to_accounts`;

DELIMITER ;;
CREATE PROCEDURE `add_callerid_group_id_to_accounts`()
BEGIN
  DECLARE col_count INT DEFAULT 0;

  SELECT COUNT(*) INTO col_count
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'accounts'
    AND column_name = 'callerid_group_id';

  IF col_count = 0 THEN
    ALTER TABLE `accounts`
      ADD COLUMN `callerid_group_id` int NOT NULL DEFAULT '0' AFTER `pricelist_id`,
      ADD INDEX `callerid_group_id` (`callerid_group_id`);
  END IF;
END;;
DELIMITER ;

CALL `add_callerid_group_id_to_accounts`();
DROP PROCEDURE IF EXISTS `add_callerid_group_id_to_accounts`;

-- 4. Idempotently add callerid_group_id to ani_map table
DROP PROCEDURE IF EXISTS `add_callerid_group_id_to_animap`;

DELIMITER ;;
CREATE PROCEDURE `add_callerid_group_id_to_animap`()
BEGIN
  DECLARE col_count INT DEFAULT 0;

  SELECT COUNT(*) INTO col_count
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'ani_map'
    AND column_name = 'callerid_group_id';

  IF col_count = 0 THEN
    ALTER TABLE `ani_map`
      ADD COLUMN `callerid_group_id` int NOT NULL DEFAULT '0' AFTER `accountid`,
      ADD INDEX `idx_animap_callerid_group_id` (`callerid_group_id`);
  END IF;
END;;
DELIMITER ;

CALL `add_callerid_group_id_to_animap`();
DROP PROCEDURE IF EXISTS `add_callerid_group_id_to_animap`;

