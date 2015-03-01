SET FOREIGN_KEY_CHECKS = 0;

UPDATE `config` SET `value`='2.2.0-alpha1' WHERE `name`='thelia_version';
UPDATE `config` SET `value`='2' WHERE `name`='thelia_major_version';
UPDATE `config` SET `value`='2' WHERE `name`='thelia_minus_version';
UPDATE `config` SET `value`='0' WHERE `name`='thelia_release_version';
UPDATE `config` SET `value`='alpha1' WHERE `name`='thelia_extra_version';

-- admin hooks

SELECT @max_id := IFNULL(MAX(`id`),0) FROM `hook`;

INSERT INTO `hook` (`id`, `code`, `type`, `by_module`, `block`, `native`, `activate`, `position`, `created_at`, `updated_at`) VALUES
  (@max_id + 1, 'order.tab', 2, 0, 1, 1, 1, 1, NOW(), NOW())
;

INSERT INTO  `hook_i18n` (`id`, `locale`, `title`, `description`, `chapo`) VALUES
  (@max_id + 1, 'fr_FR', 'Commande - Onglet', '', ''),
  (@max_id + 1, 'en_US', 'Order - Tab', '', '')
;

SELECT @max_id := MAX(`id`) FROM `order_status`;

INSERT INTO `order_status` VALUES
  (@max_id + 1, "refunded", NOW(), NOW())
;

INSERT INTO  `order_status_i18n` VALUES
  (@max_id + 1, "en_US", "Refunded", "", "", ""),
  (@max_id + 1, "fr_FR", "Remboursée", "", "", "")
;

-- new column in admin_log

ALTER TABLE `admin_log` ADD `resource_id` INTEGER AFTER `resource` ;

-- new config

SELECT @max_id := IFNULL(MAX(`id`),0) FROM `config`;

INSERT INTO `config` (`id`, `name`, `value`, `secured`, `hidden`, `created_at`, `updated_at`) VALUES
(@max_id + 1, 'customer_change_email', '0', 0, 0, NOW(), NOW()),
(@max_id + 2, 'customer_confirm_email', '0', 0, 0, NOW(), NOW()),
(@max_id + 3, 'customer_birthday_enable', '0', 0, 0, NOW(), NOW()),
(@max_id + 4, 'customer_birthday_required', '1', 0, 0, NOW(), NOW())
;

INSERT INTO `config_i18n` (`id`, `locale`, `title`, `description`, `chapo`, `postscriptum`) VALUES
(@max_id + 1, 'en_US', 'Allow customers to change their email. 1 for yes, 0 for no', NULL, NULL, NULL),
(@max_id + 1, 'fr_FR', 'Permettre aux clients de changer leur email. 1 pour oui, 0 pour non', NULL, NULL, NULL),
(@max_id + 2, 'en_US', 'Ask the customers to confirm their email, 1 for yes, 0 for no', NULL, NULL, NULL),
(@max_id + 2, 'fr_FR', 'Demander aux clients de confirmer leur email. 1 pour oui, 0 pour non', NULL, NULL, NULL),
(@max_id + 3, 'en_US', 'Ask customers their birthday, 1 for yes, 0 for no', NULL, NULL, NULL),
(@max_id + 3, 'fr_FR', 'Demander aux clients leur date de naissance. 1 pour oui, 0 pour non', NULL, NULL, NULL),
(@max_id + 4, 'en_US', 'Force the customer to enter a birthday, 1 for yes, 0 for no', NULL, NULL, NULL),
(@max_id + 4, 'fr_FR', 'Forcer le client à entrer une de naissance. 1 pour oui, 0 pour non', NULL, NULL, NULL)
;

-- country area table

create table `country_area`
(
    `country_id` INTEGER NOT NULL,
    `area_id` INTEGER NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    INDEX `country_area_area_id_idx` (`area_id`),
    INDEX `fk_country_area_country_id_idx` (`country_id`),
    CONSTRAINT `fk_country_area_area_id`
        FOREIGN KEY (`area_id`)
        REFERENCES `area` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_country_area_country_id`
        FOREIGN KEY (`country_id`)
        REFERENCES `country` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET='utf8';

-- Initialize the table with existing data
INSERT INTO `country_area` (`country_id`, `area_id`, `created_at`, `updated_at`) select `id`, `area_id`, NOW(), NOW() FROM `country` WHERE `area_id` IS NOT NULL;

-- Remove area_id column from country table
ALTER TABLE `country` DROP `area_id`;

ALTER TABLE  `customer` ADD  `birthday` DATE NULL AFTER  `algo` ;
ALTER TABLE  `customer_version` ADD  `birthday` DATE NULL AFTER  `algo` ;

SET FOREIGN_KEY_CHECKS = 1;