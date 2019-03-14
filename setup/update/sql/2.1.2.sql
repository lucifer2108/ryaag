SET FOREIGN_KEY_CHECKS = 0;

UPDATE `config` SET `value`='2.1.2' WHERE `name`='thelia_version';
UPDATE `config` SET `value`='2' WHERE `name`='thelia_major_version';
UPDATE `config` SET `value`='1' WHERE `name`='thelia_minus_version';
UPDATE `config` SET `value`='2' WHERE `name`='thelia_release_version';
UPDATE `config` SET `value`='' WHERE `name`='thelia_extra_version';

SELECT @maxHookId := MAX(`id`) FROM `hook`;

INSERT INTO `hook` (`id`, `code`, `type`, `by_module`, `block`, `native`, `activate`, `position`, `created_at`, `updated_at`) VALUES
  (@maxHookId + 1, 'coupon.delete-form', 2, 0, 0, 1, 1, 1, NOW(), NOW())
;

INSERT INTO  `hook_i18n` (`id`, `locale`, `title`, `description`, `chapo`) VALUES
(@maxHookId + 1, 'ar_SA', NULL, '', ''),
(@maxHookId + 1, 'cs_CZ', NULL, '', ''),
(@maxHookId + 1, 'de_DE', 'Gutschein-Seite - Löschungsformular', '', ''),
(@maxHookId + 1, 'el_GR', NULL, '', ''),
(@maxHookId + 1, 'en_US', 'Coupon page - in deletion form', '', ''),
(@maxHookId + 1, 'es_ES', 'Página de cupón - en formulario de eliminación', '', ''),
(@maxHookId + 1, 'fa_IR', NULL, '', ''),
(@maxHookId + 1, 'fr_FR', 'Page coupon - formulaire de suppression', '', ''),
(@maxHookId + 1, 'hu_HU', NULL, '', ''),
(@maxHookId + 1, 'id_ID', NULL, '', ''),
(@maxHookId + 1, 'it_IT', NULL, '', ''),
(@maxHookId + 1, 'pl_PL', NULL, '', ''),
(@maxHookId + 1, 'pt_BR', NULL, '', ''),
(@maxHookId + 1, 'pt_PT', NULL, '', ''),
(@maxHookId + 1, 'ru_RU', 'Страница купона - в форме удаления', '', ''),
(@maxHookId + 1, 'sk_SK', NULL, '', ''),
(@maxHookId + 1, 'tr_TR', 'Kupon sayfasında - silme formu', '', ''),
(@maxHookId + 1, 'uk_UA', NULL, '', '')
;

UPDATE `config_i18n` SET `title`='Utiliser un cookie persistant pour memoriser le panier du client' WHERE `locale`='fr_FR' AND `id`=(SELECT`id` FROM `config` WHERE `name`='cart.use_persistent_cookie');

-- New ignored_module_hook table

CREATE TABLE IF NOT EXISTS `ignored_module_hook`
(
    `module_id` INTEGER NOT NULL,
    `hook_id` INTEGER NOT NULL,
    `method` VARCHAR(255),
    `classname` VARCHAR(255),
    INDEX `fk_deleted_module_hook_module_id_idx` (`module_id`),
    INDEX `fk_deleted_module_hook_hook_id_idx` (`hook_id`),
    CONSTRAINT `fk_deleted_module_hook_module_id`
        FOREIGN KEY (`module_id`)
        REFERENCES `module` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_deleted_module_hook_hook_id`
        FOREIGN KEY (`hook_id`)
        REFERENCES `hook` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET='utf8';

SET FOREIGN_KEY_CHECKS = 1;
