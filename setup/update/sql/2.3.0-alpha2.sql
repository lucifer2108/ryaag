SET FOREIGN_KEY_CHECKS = 0;

UPDATE `config` SET `value`='2.3.0-alpha2' WHERE `name`='thelia_version';
UPDATE `config` SET `value`='2' WHERE `name`='thelia_major_version';
UPDATE `config` SET `value`='3' WHERE `name`='thelia_minus_version';
UPDATE `config` SET `value`='0' WHERE `name`='thelia_release_version';
UPDATE `config` SET `value`='alpha2' WHERE `name`='thelia_extra_version';

-- Add column unsubscribed in newsletter table
ALTER TABLE `newsletter` ADD `unsubscribed` TINYINT(1) NOT NULL DEFAULT '0' AFTER `locale`;

-- add admin email
ALTER TABLE  `admin` ADD  `email` VARCHAR(255) NOT NULL AFTER `remember_me_serial` ;
ALTER TABLE  `admin` ADD  `password_renew_token` VARCHAR(255) NOT NULL AFTER `email` ;

-- add admin password renew message

SELECT @max := MAX(`id`) FROM `message`;
SET @max := @max+1;

INSERT INTO `message` (`id`, `name`, `secured`, `text_layout_file_name`, `text_template_file_name`, `html_layout_file_name`, `html_template_file_name`, `created_at`, `updated_at`) VALUES
(@max, 'new_admin_password', NULL, NULL, 'admin_password.txt', NULL, 'admin_password.html', NOW(), NOW());

INSERT INTO `message_i18n` (`id`, `locale`, `title`, `subject`, `text_message`, `html_message`) VALUES
    (@max, 'ar_SA', NULL, NULL, NULL, NULL),
    (@max, 'cs_CZ', NULL, NULL, NULL, NULL),
    (@max, 'de_DE', NULL, NULL, NULL, NULL),
    (@max, 'el_GR', NULL, NULL, NULL, NULL),
    (@max, 'en_US', 'Mail sent to an administrator who requested a new password', 'New password request on {config key=\"store_name\"}', NULL, NULL),
    (@max, 'es_ES', 'Correo enviado a un administrador que ha solicitado una nueva contraseña', 'Nueva contraseña solicitada en {config key=\"store_name\"}', NULL, NULL),
    (@max, 'fa_IR', NULL, NULL, NULL, NULL),
    (@max, 'fr_FR', 'Courrier envoyé à un administrateur qui a demandé un nouveau mot de passe', 'Votre demande de mot de passe {config key=\"store_name\"}', NULL, NULL),
    (@max, 'hu_HU', NULL, NULL, NULL, NULL),
    (@max, 'id_ID', NULL, NULL, NULL, NULL),
    (@max, 'it_IT', NULL, NULL, NULL, NULL),
    (@max, 'pl_PL', NULL, NULL, NULL, NULL),
    (@max, 'pt_BR', NULL, NULL, NULL, NULL),
    (@max, 'pt_PT', NULL, NULL, NULL, NULL),
    (@max, 'ru_RU', 'Письмо отсылаемое администратору при запросе нового пароля', 'Ваш новый пароль для %store', NULL, NULL),
    (@max, 'sk_SK', 'E-mail, ktorý je odoslaný adminovi po žiadosti o nové heslo', NULL, NULL, NULL),
    (@max, 'tr_TR', NULL, NULL, NULL, NULL),
    (@max, 'uk_UA', NULL, NULL, NULL, NULL)
;

-- Insert a fake email address for administrators, to trigger the admin update dialog
-- at next admin login.

UPDATE `admin` set email = CONCAT('CHANGE_ME_', ID);

ALTER TABLE `admin` ADD UNIQUE `email_UNIQUE` (`email`);

-- additional config variables

SELECT @max_id := IFNULL(MAX(`id`),0) FROM `config`;

INSERT INTO `config` (`id`, `name`, `value`, `secured`, `hidden`, `created_at`, `updated_at`) VALUES
(@max_id + 1, 'minimum_admin_password_length', '4', 0, 0, NOW(), NOW()),
(@max_id + 2, 'enable_lost_admin_password_recovery', '1', 0, 0, NOW(), NOW()),
(@max_id + 3, 'notify_newsletter_subscription', '1', 0, 0, NOW(), NOW())
;

INSERT INTO `config_i18n` (`id`, `locale`, `title`, `description`, `chapo`, `postscriptum`) VALUES
    (@max_id + 1, 'ar_SA', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'ar_SA', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'ar_SA', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'cs_CZ', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'cs_CZ', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'cs_CZ', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'de_DE', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'de_DE', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'de_DE', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'el_GR', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'el_GR', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'el_GR', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'en_US', 'The minimum length required for an administrator password', NULL, NULL, NULL),
    (@max_id + 2, 'en_US', 'Allow an administrator to recreate a lost password (1 = yes, 0 = no)', NULL, NULL, NULL),
    (@max_id + 3, 'en_US', 'Send a confirmation email to newsletter subscribers (1 = yes, 0 = no)', NULL, NULL, NULL),
    (@max_id + 1, 'es_ES', 'La longitud mínima de la contraseña de administrador', NULL, NULL, NULL),
    (@max_id + 2, 'es_ES', 'Permite a un administrador recrear una contraseña perdida (1 = sí, 0 = no)', NULL, NULL, NULL),
    (@max_id + 3, 'es_ES', 'Enviar un correo de confirmación a los suscriptores del boletín (1 = sí, 0 = no)', NULL, NULL, NULL),
    (@max_id + 1, 'fa_IR', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'fa_IR', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'fa_IR', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'fr_FR', 'La longueur minimale requise pour un mot de passe administrateur', NULL, NULL, NULL),
    (@max_id + 2, 'fr_FR', 'Permettre à un administrateur de recréer un mot de passe perdu (1 = Oui, 0 = non)', NULL, NULL, NULL),
    (@max_id + 3, 'fr_FR', 'Envoyer un email de confirmation aux abonnés de la newsletter (1 = Oui, 0 = non)', NULL, NULL, NULL),
    (@max_id + 1, 'hu_HU', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'hu_HU', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'hu_HU', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'id_ID', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'id_ID', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'id_ID', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'it_IT', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'it_IT', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'it_IT', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'pl_PL', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'pl_PL', 'Pozwól administratorom na ponowne utworzenie hasła (1 = tak, 0 = nie)', NULL, NULL, NULL),
    (@max_id + 3, 'pl_PL', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'pt_BR', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'pt_BR', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'pt_BR', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'pt_PT', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'pt_PT', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'pt_PT', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'ru_RU', 'Минимальная длина пароля для админа', NULL, NULL, NULL),
    (@max_id + 2, 'ru_RU', 'Позволять восстановление утеряного пароля админа (1 - Да, 0 - Нет)', NULL, NULL, NULL),
    (@max_id + 3, 'ru_RU', 'Посылать email подтверждения подписчикам рассылки', NULL, NULL, NULL),
    (@max_id + 1, 'sk_SK', 'Minimálna požadovaná dĺžka hesla administrátora', NULL, NULL, NULL),
    (@max_id + 2, 'sk_SK', 'Povoliť správcom obnoviť stratené heslo (1 = áno, 0 = nie)', NULL, NULL, NULL),
    (@max_id + 3, 'sk_SK', 'Poslať potvrdzovací e-mail pre záujemcov o newsletter (1 = áno, 0 = nie)', NULL, NULL, NULL),
    (@max_id + 1, 'tr_TR', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'tr_TR', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'tr_TR', NULL, NULL, NULL, NULL),
    (@max_id + 1, 'uk_UA', NULL, NULL, NULL, NULL),
    (@max_id + 2, 'uk_UA', NULL, NULL, NULL, NULL),
    (@max_id + 3, 'uk_UA', NULL, NULL, NULL, NULL)
;

-- Additional hooks

SELECT @max_id := IFNULL(MAX(`id`),0) FROM `hook`;

INSERT INTO `hook` (`id`, `code`, `type`, `by_module`, `block`, `native`, `activate`, `position`, `created_at`, `updated_at`) VALUES
    (@max_id+1, 'sale.top', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+2, 'sale.bottom', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+3, 'sale.main-top', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+4, 'sale.main-bottom', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+5, 'sale.content-top', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+6, 'sale.content-bottom', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+7, 'sale.stylesheet', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+8, 'sale.after-javascript-include', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+9, 'sale.javascript-initialization', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+10, 'account-order.invoice-address-bottom', 1, 1, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+11, 'account-order.delivery-address-bottom', 1, 1, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+12, 'newsletter-unsubscribe.top', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+13, 'newsletter-unsubscribe.bottom', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+14, 'newsletter-unsubscribe.stylesheet', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+15, 'newsletter-unsubscribe.after-javascript-include', 1, 0, 0, 1, 1, 1, NOW(), NOW()),
    (@max_id+16, 'newsletter-unsubscribe.javascript-initialization', 1, 0, 0, 1, 1, 1, NOW(), NOW())
;

INSERT INTO  `hook_i18n` (`id`, `locale`, `title`, `description`, `chapo`) VALUES
    (@max_id+1, 'ar_SA', NULL, NULL, NULL),
    (@max_id+2, 'ar_SA', NULL, NULL, NULL),
    (@max_id+3, 'ar_SA', NULL, NULL, NULL),
    (@max_id+4, 'ar_SA', NULL, NULL, NULL),
    (@max_id+5, 'ar_SA', NULL, NULL, NULL),
    (@max_id+6, 'ar_SA', NULL, NULL, NULL),
    (@max_id+7, 'ar_SA', NULL, NULL, NULL),
    (@max_id+8, 'ar_SA', NULL, NULL, NULL),
    (@max_id+9, 'ar_SA', NULL, NULL, NULL),
    (@max_id+10, 'ar_SA', NULL, NULL, NULL),
    (@max_id+11, 'ar_SA', NULL, NULL, NULL),
    (@max_id+12, 'ar_SA', NULL, NULL, NULL),
    (@max_id+13, 'ar_SA', NULL, NULL, NULL),
    (@max_id+14, 'ar_SA', NULL, NULL, NULL),
    (@max_id+15, 'ar_SA', NULL, NULL, NULL),
    (@max_id+16, 'ar_SA', NULL, NULL, NULL),
    (@max_id+1, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+2, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+3, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+4, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+5, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+6, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+7, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+8, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+9, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+10, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+11, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+12, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+13, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+14, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+15, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+16, 'cs_CZ', NULL, NULL, NULL),
    (@max_id+1, 'de_DE', NULL, NULL, NULL),
    (@max_id+2, 'de_DE', NULL, NULL, NULL),
    (@max_id+3, 'de_DE', NULL, NULL, NULL),
    (@max_id+4, 'de_DE', NULL, NULL, NULL),
    (@max_id+5, 'de_DE', NULL, NULL, NULL),
    (@max_id+6, 'de_DE', NULL, NULL, NULL),
    (@max_id+7, 'de_DE', NULL, NULL, NULL),
    (@max_id+8, 'de_DE', NULL, NULL, NULL),
    (@max_id+9, 'de_DE', NULL, NULL, NULL),
    (@max_id+10, 'de_DE', NULL, NULL, NULL),
    (@max_id+11, 'de_DE', NULL, NULL, NULL),
    (@max_id+12, 'de_DE', NULL, NULL, NULL),
    (@max_id+13, 'de_DE', NULL, NULL, NULL),
    (@max_id+14, 'de_DE', NULL, NULL, NULL),
    (@max_id+15, 'de_DE', NULL, NULL, NULL),
    (@max_id+16, 'de_DE', NULL, NULL, NULL),
    (@max_id+1, 'el_GR', NULL, NULL, NULL),
    (@max_id+2, 'el_GR', NULL, NULL, NULL),
    (@max_id+3, 'el_GR', NULL, NULL, NULL),
    (@max_id+4, 'el_GR', NULL, NULL, NULL),
    (@max_id+5, 'el_GR', NULL, NULL, NULL),
    (@max_id+6, 'el_GR', NULL, NULL, NULL),
    (@max_id+7, 'el_GR', NULL, NULL, NULL),
    (@max_id+8, 'el_GR', NULL, NULL, NULL),
    (@max_id+9, 'el_GR', NULL, NULL, NULL),
    (@max_id+10, 'el_GR', NULL, NULL, NULL),
    (@max_id+11, 'el_GR', NULL, NULL, NULL),
    (@max_id+12, 'el_GR', NULL, NULL, NULL),
    (@max_id+13, 'el_GR', NULL, NULL, NULL),
    (@max_id+14, 'el_GR', NULL, NULL, NULL),
    (@max_id+15, 'el_GR', NULL, NULL, NULL),
    (@max_id+16, 'el_GR', NULL, NULL, NULL),
    (@max_id+1, 'en_US', 'Sale - at the top', NULL, NULL),
    (@max_id+2, 'en_US', 'Sale - at the bottom', NULL, NULL),
    (@max_id+3, 'en_US', 'Sale - at the top of the main area', NULL, NULL),
    (@max_id+4, 'en_US', 'Sale - at the bottom of the main area', NULL, NULL),
    (@max_id+5, 'en_US', 'Sale - before the main content area', NULL, NULL),
    (@max_id+6, 'en_US', 'Sale - after the main content area', NULL, NULL),
    (@max_id+7, 'en_US', 'Sale - CSS stylesheet', NULL, NULL),
    (@max_id+8, 'en_US', 'Sale - after javascript include', NULL, NULL),
    (@max_id+9, 'en_US', 'Sale - javascript initialization', NULL, NULL),
    (@max_id+10, 'en_US', 'Order details - after invoice address', NULL, NULL),
    (@max_id+11, 'en_US', 'Order details - after delivery address', NULL, NULL),
    (@max_id+12, 'en_US', 'Newsletter unsubscribe page - at the top', NULL, NULL),
    (@max_id+13, 'en_US', 'Newsletter unsubscribe page - at the bottom', NULL, NULL),
    (@max_id+14, 'en_US', 'Newsletter unsubscribe page - CSS stylesheet', NULL, NULL),
    (@max_id+15, 'en_US', 'Newsletter unsubscribe page - after javascript include', NULL, NULL),
    (@max_id+16, 'en_US', 'Newsletter unsubscribe page - after javascript initialisation', NULL, NULL),
    (@max_id+1, 'es_ES', 'Venta - encabezado', NULL, NULL),
    (@max_id+2, 'es_ES', 'Venta - al pie', NULL, NULL),
    (@max_id+3, 'es_ES', 'Venta - encabezado del área principal', NULL, NULL),
    (@max_id+4, 'es_ES', 'Venta - al pie del área principal', NULL, NULL),
    (@max_id+5, 'es_ES', 'Venta - antes del área de contenido principal', NULL, NULL),
    (@max_id+6, 'es_ES', 'Venta - después del área de contenido principal', NULL, NULL),
    (@max_id+7, 'es_ES', 'Venta - Hoja de estilos CSS', NULL, NULL),
    (@max_id+8, 'es_ES', 'Venta - después de incluir JavaScript', NULL, NULL),
    (@max_id+9, 'es_ES', 'Venta - inicialización de JavaScript', NULL, NULL),
    (@max_id+10, 'es_ES', 'Detalles de pedido - después de la dirección de facturación', NULL, NULL),
    (@max_id+11, 'es_ES', 'Detalles de pedido - después de la dirección de entrega', NULL, NULL),
    (@max_id+12, 'es_ES', 'Página de baja del boletín - en la parte superior', NULL, NULL),
    (@max_id+13, 'es_ES', 'Página de baja del boletín - al pie', NULL, NULL),
    (@max_id+14, 'es_ES', 'Página de baja del boletín - Hoja de estilos CSS', NULL, NULL),
    (@max_id+15, 'es_ES', 'Página de baja del boletín - después de incluir JavaScript', NULL, NULL),
    (@max_id+16, 'es_ES', 'Página de baja del boletín - después de la inicialización de JavaScript', NULL, NULL),
    (@max_id+1, 'fa_IR', NULL, NULL, NULL),
    (@max_id+2, 'fa_IR', NULL, NULL, NULL),
    (@max_id+3, 'fa_IR', NULL, NULL, NULL),
    (@max_id+4, 'fa_IR', NULL, NULL, NULL),
    (@max_id+5, 'fa_IR', NULL, NULL, NULL),
    (@max_id+6, 'fa_IR', NULL, NULL, NULL),
    (@max_id+7, 'fa_IR', NULL, NULL, NULL),
    (@max_id+8, 'fa_IR', NULL, NULL, NULL),
    (@max_id+9, 'fa_IR', NULL, NULL, NULL),
    (@max_id+10, 'fa_IR', NULL, NULL, NULL),
    (@max_id+11, 'fa_IR', NULL, NULL, NULL),
    (@max_id+12, 'fa_IR', NULL, NULL, NULL),
    (@max_id+13, 'fa_IR', NULL, NULL, NULL),
    (@max_id+14, 'fa_IR', NULL, NULL, NULL),
    (@max_id+15, 'fa_IR', NULL, NULL, NULL),
    (@max_id+16, 'fa_IR', NULL, NULL, NULL),
    (@max_id+1, 'fr_FR', 'Promotion - en haut', NULL, NULL),
    (@max_id+2, 'fr_FR', 'Promotion - en bas', NULL, NULL),
    (@max_id+3, 'fr_FR', 'Promotion - en haut de la zone principal', NULL, NULL),
    (@max_id+4, 'fr_FR', 'Promotion - en bas de la zone principal', NULL, NULL),
    (@max_id+5, 'fr_FR', 'Promotion - au dessous de la zone de contenu principale', NULL, NULL),
    (@max_id+6, 'fr_FR', 'Promotion - en dessous de la zone de contenu principale', NULL, NULL),
    (@max_id+7, 'fr_FR', 'Promotion - feuille de style CSS', NULL, NULL),
    (@max_id+8, 'fr_FR', 'Promotion - après l\'inclusion du JavaScript', NULL, NULL),
    (@max_id+9, 'fr_FR', 'Promotion - initialisation du JavaScript', NULL, NULL),
    (@max_id+10, 'fr_FR', 'Détail d\'une commande - après l\'adresse de facturation', NULL, NULL),
    (@max_id+11, 'fr_FR', 'Détails d\'une commande - après l\'adresse de livraison', NULL, NULL),
    (@max_id+12, 'fr_FR', 'Désabonnement newsletter - en haut', NULL, NULL),
    (@max_id+13, 'fr_FR', 'Désabonnement newsletter - en bas', NULL, NULL),
    (@max_id+14, 'fr_FR', 'Désabonnement newsletter - feuille de style CSS', NULL, NULL),
    (@max_id+15, 'fr_FR', 'Désabonnement newsletter - après l\'inclusion du JavaScript', NULL, NULL),
    (@max_id+16, 'fr_FR', 'Désabonnement newsletter - après l\'initialisation du JavaScript', NULL, NULL),
    (@max_id+1, 'hu_HU', NULL, NULL, NULL),
    (@max_id+2, 'hu_HU', NULL, NULL, NULL),
    (@max_id+3, 'hu_HU', NULL, NULL, NULL),
    (@max_id+4, 'hu_HU', NULL, NULL, NULL),
    (@max_id+5, 'hu_HU', NULL, NULL, NULL),
    (@max_id+6, 'hu_HU', NULL, NULL, NULL),
    (@max_id+7, 'hu_HU', NULL, NULL, NULL),
    (@max_id+8, 'hu_HU', NULL, NULL, NULL),
    (@max_id+9, 'hu_HU', NULL, NULL, NULL),
    (@max_id+10, 'hu_HU', NULL, NULL, NULL),
    (@max_id+11, 'hu_HU', NULL, NULL, NULL),
    (@max_id+12, 'hu_HU', NULL, NULL, NULL),
    (@max_id+13, 'hu_HU', NULL, NULL, NULL),
    (@max_id+14, 'hu_HU', NULL, NULL, NULL),
    (@max_id+15, 'hu_HU', NULL, NULL, NULL),
    (@max_id+16, 'hu_HU', NULL, NULL, NULL),
    (@max_id+1, 'id_ID', NULL, NULL, NULL),
    (@max_id+2, 'id_ID', NULL, NULL, NULL),
    (@max_id+3, 'id_ID', NULL, NULL, NULL),
    (@max_id+4, 'id_ID', NULL, NULL, NULL),
    (@max_id+5, 'id_ID', NULL, NULL, NULL),
    (@max_id+6, 'id_ID', NULL, NULL, NULL),
    (@max_id+7, 'id_ID', NULL, NULL, NULL),
    (@max_id+8, 'id_ID', NULL, NULL, NULL),
    (@max_id+9, 'id_ID', NULL, NULL, NULL),
    (@max_id+10, 'id_ID', NULL, NULL, NULL),
    (@max_id+11, 'id_ID', NULL, NULL, NULL),
    (@max_id+12, 'id_ID', NULL, NULL, NULL),
    (@max_id+13, 'id_ID', NULL, NULL, NULL),
    (@max_id+14, 'id_ID', NULL, NULL, NULL),
    (@max_id+15, 'id_ID', NULL, NULL, NULL),
    (@max_id+16, 'id_ID', NULL, NULL, NULL),
    (@max_id+1, 'it_IT', NULL, NULL, NULL),
    (@max_id+2, 'it_IT', NULL, NULL, NULL),
    (@max_id+3, 'it_IT', NULL, NULL, NULL),
    (@max_id+4, 'it_IT', NULL, NULL, NULL),
    (@max_id+5, 'it_IT', NULL, NULL, NULL),
    (@max_id+6, 'it_IT', NULL, NULL, NULL),
    (@max_id+7, 'it_IT', NULL, NULL, NULL),
    (@max_id+8, 'it_IT', NULL, NULL, NULL),
    (@max_id+9, 'it_IT', NULL, NULL, NULL),
    (@max_id+10, 'it_IT', NULL, NULL, NULL),
    (@max_id+11, 'it_IT', NULL, NULL, NULL),
    (@max_id+12, 'it_IT', NULL, NULL, NULL),
    (@max_id+13, 'it_IT', NULL, NULL, NULL),
    (@max_id+14, 'it_IT', NULL, NULL, NULL),
    (@max_id+15, 'it_IT', NULL, NULL, NULL),
    (@max_id+16, 'it_IT', NULL, NULL, NULL),
    (@max_id+1, 'pl_PL', NULL, NULL, NULL),
    (@max_id+2, 'pl_PL', NULL, NULL, NULL),
    (@max_id+3, 'pl_PL', NULL, NULL, NULL),
    (@max_id+4, 'pl_PL', NULL, NULL, NULL),
    (@max_id+5, 'pl_PL', NULL, NULL, NULL),
    (@max_id+6, 'pl_PL', NULL, NULL, NULL),
    (@max_id+7, 'pl_PL', NULL, NULL, NULL),
    (@max_id+8, 'pl_PL', NULL, NULL, NULL),
    (@max_id+9, 'pl_PL', NULL, NULL, NULL),
    (@max_id+10, 'pl_PL', NULL, NULL, NULL),
    (@max_id+11, 'pl_PL', NULL, NULL, NULL),
    (@max_id+12, 'pl_PL', NULL, NULL, NULL),
    (@max_id+13, 'pl_PL', NULL, NULL, NULL),
    (@max_id+14, 'pl_PL', NULL, NULL, NULL),
    (@max_id+15, 'pl_PL', NULL, NULL, NULL),
    (@max_id+16, 'pl_PL', NULL, NULL, NULL),
    (@max_id+1, 'pt_BR', NULL, NULL, NULL),
    (@max_id+2, 'pt_BR', NULL, NULL, NULL),
    (@max_id+3, 'pt_BR', NULL, NULL, NULL),
    (@max_id+4, 'pt_BR', NULL, NULL, NULL),
    (@max_id+5, 'pt_BR', NULL, NULL, NULL),
    (@max_id+6, 'pt_BR', NULL, NULL, NULL),
    (@max_id+7, 'pt_BR', NULL, NULL, NULL),
    (@max_id+8, 'pt_BR', NULL, NULL, NULL),
    (@max_id+9, 'pt_BR', NULL, NULL, NULL),
    (@max_id+10, 'pt_BR', NULL, NULL, NULL),
    (@max_id+11, 'pt_BR', NULL, NULL, NULL),
    (@max_id+12, 'pt_BR', NULL, NULL, NULL),
    (@max_id+13, 'pt_BR', NULL, NULL, NULL),
    (@max_id+14, 'pt_BR', NULL, NULL, NULL),
    (@max_id+15, 'pt_BR', NULL, NULL, NULL),
    (@max_id+16, 'pt_BR', NULL, NULL, NULL),
    (@max_id+1, 'pt_PT', NULL, NULL, NULL),
    (@max_id+2, 'pt_PT', NULL, NULL, NULL),
    (@max_id+3, 'pt_PT', NULL, NULL, NULL),
    (@max_id+4, 'pt_PT', NULL, NULL, NULL),
    (@max_id+5, 'pt_PT', NULL, NULL, NULL),
    (@max_id+6, 'pt_PT', NULL, NULL, NULL),
    (@max_id+7, 'pt_PT', NULL, NULL, NULL),
    (@max_id+8, 'pt_PT', NULL, NULL, NULL),
    (@max_id+9, 'pt_PT', NULL, NULL, NULL),
    (@max_id+10, 'pt_PT', NULL, NULL, NULL),
    (@max_id+11, 'pt_PT', NULL, NULL, NULL),
    (@max_id+12, 'pt_PT', NULL, NULL, NULL),
    (@max_id+13, 'pt_PT', NULL, NULL, NULL),
    (@max_id+14, 'pt_PT', NULL, NULL, NULL),
    (@max_id+15, 'pt_PT', NULL, NULL, NULL),
    (@max_id+16, 'pt_PT', NULL, NULL, NULL),
    (@max_id+1, 'ru_RU', 'Распродажа - вверху', NULL, NULL),
    (@max_id+2, 'ru_RU', 'Распродажа - внизу', NULL, NULL),
    (@max_id+3, 'ru_RU', 'Распродажа - вверху основной зоны', NULL, NULL),
    (@max_id+4, 'ru_RU', 'Распродажа - внизу основной зоны', NULL, NULL),
    (@max_id+5, 'ru_RU', 'Распродажа - перед зоной основного контента', NULL, NULL),
    (@max_id+6, 'ru_RU', 'Распродажа - после зоной основного контента', NULL, NULL),
    (@max_id+7, 'ru_RU', 'Распродажа - CSS стили', NULL, NULL),
    (@max_id+8, 'ru_RU', 'Распродажа - после включения javascript', NULL, NULL),
    (@max_id+9, 'ru_RU', 'Распродажа - после инициализации javascript ', NULL, NULL),
    (@max_id+10, 'ru_RU', 'Детали заказа - после адреса счет-фактуры', NULL, NULL),
    (@max_id+11, 'ru_RU', 'Детали заказа - после адреса доставки', NULL, NULL),
    (@max_id+12, 'ru_RU', 'Страница отписки от рассылки - вверху', NULL, NULL),
    (@max_id+13, 'ru_RU', 'Страница отписки от рассылки - внизу', NULL, NULL),
    (@max_id+14, 'ru_RU', 'Страница отписки от рассылки - CSS стили', NULL, NULL),
    (@max_id+15, 'ru_RU', 'Страница отписки от рассылки - после включения javascript', NULL, NULL),
    (@max_id+16, 'ru_RU', 'Страница отписки от рассылки - после инициализации javascript', NULL, NULL),
    (@max_id+1, 'sk_SK', 'Výpredaj - hore', NULL, NULL),
    (@max_id+2, 'sk_SK', NULL, NULL, NULL),
    (@max_id+3, 'sk_SK', 'Výpredaj - v hornej časti hlavnej oblasti', NULL, NULL),
    (@max_id+4, 'sk_SK', NULL, NULL, NULL),
    (@max_id+5, 'sk_SK', NULL, NULL, NULL),
    (@max_id+6, 'sk_SK', NULL, NULL, NULL),
    (@max_id+7, 'sk_SK', 'Predaj - CSS štýlov', NULL, NULL),
    (@max_id+8, 'sk_SK', NULL, NULL, NULL),
    (@max_id+9, 'sk_SK', NULL, NULL, NULL),
    (@max_id+10, 'sk_SK', 'Podrobnosti objednávky - po fakturačnej adrese', NULL, NULL),
    (@max_id+11, 'sk_SK', 'Podrobnosti objednávky - po adresu doručenia', NULL, NULL),
    (@max_id+12, 'sk_SK', NULL, NULL, NULL),
    (@max_id+13, 'sk_SK', NULL, NULL, NULL),
    (@max_id+14, 'sk_SK', NULL, NULL, NULL),
    (@max_id+15, 'sk_SK', NULL, NULL, NULL),
    (@max_id+16, 'sk_SK', NULL, NULL, NULL),
    (@max_id+1, 'tr_TR', NULL, NULL, NULL),
    (@max_id+2, 'tr_TR', NULL, NULL, NULL),
    (@max_id+3, 'tr_TR', NULL, NULL, NULL),
    (@max_id+4, 'tr_TR', NULL, NULL, NULL),
    (@max_id+5, 'tr_TR', NULL, NULL, NULL),
    (@max_id+6, 'tr_TR', NULL, NULL, NULL),
    (@max_id+7, 'tr_TR', NULL, NULL, NULL),
    (@max_id+8, 'tr_TR', NULL, NULL, NULL),
    (@max_id+9, 'tr_TR', NULL, NULL, NULL),
    (@max_id+10, 'tr_TR', NULL, NULL, NULL),
    (@max_id+11, 'tr_TR', NULL, NULL, NULL),
    (@max_id+12, 'tr_TR', NULL, NULL, NULL),
    (@max_id+13, 'tr_TR', NULL, NULL, NULL),
    (@max_id+14, 'tr_TR', NULL, NULL, NULL),
    (@max_id+15, 'tr_TR', NULL, NULL, NULL),
    (@max_id+16, 'tr_TR', NULL, NULL, NULL),
    (@max_id+1, 'uk_UA', NULL, NULL, NULL),
    (@max_id+2, 'uk_UA', NULL, NULL, NULL),
    (@max_id+3, 'uk_UA', NULL, NULL, NULL),
    (@max_id+4, 'uk_UA', NULL, NULL, NULL),
    (@max_id+5, 'uk_UA', NULL, NULL, NULL),
    (@max_id+6, 'uk_UA', NULL, NULL, NULL),
    (@max_id+7, 'uk_UA', NULL, NULL, NULL),
    (@max_id+8, 'uk_UA', NULL, NULL, NULL),
    (@max_id+9, 'uk_UA', NULL, NULL, NULL),
    (@max_id+10, 'uk_UA', NULL, NULL, NULL),
    (@max_id+11, 'uk_UA', NULL, NULL, NULL),
    (@max_id+12, 'uk_UA', NULL, NULL, NULL),
    (@max_id+13, 'uk_UA', NULL, NULL, NULL),
    (@max_id+14, 'uk_UA', NULL, NULL, NULL),
    (@max_id+15, 'uk_UA', NULL, NULL, NULL),
    (@max_id+16, 'uk_UA', NULL, NULL, NULL)
;

-- Update module version column
ALTER TABLE `module` MODIFY `version` varchar(25) NOT NULL DEFAULT '';

-- Add new column in coupon table
ALTER TABLE `coupon` ADD `start_date` DATETIME AFTER`is_enabled`;
ALTER TABLE `coupon` ADD INDEX `idx_start_date` (`start_date`);

-- Add new column in coupon version table
ALTER TABLE `coupon_version` ADD `start_date` DATETIME AFTER`is_enabled`;

-- Add new column in order coupon table
ALTER TABLE `order_coupon` ADD `start_date` DATETIME AFTER`description`;

-- Add new column in attribute combination table
ALTER TABLE `attribute_combination` ADD `position` INT NULL AFTER `product_sale_elements_id`;

-- Add newsletter subscription confirmation message

SELECT @max := MAX(`id`) FROM `message`;
SET @max := @max+1;

INSERT INTO `message` (`id`, `name`, `secured`, `text_layout_file_name`, `text_template_file_name`, `html_layout_file_name`, `html_template_file_name`, `created_at`, `updated_at`) VALUES
(@max, 'newsletter_subscription_confirmation', NULL, NULL, 'newsletter_subscription_confirmation.txt', NULL, 'newsletter_subscription_confirmation.html', NOW(), NOW());

INSERT INTO `message_i18n` (`id`, `locale`, `title`, `subject`, `text_message`, `html_message`) VALUES
    (@max, 'ar_SA', NULL, NULL, NULL, NULL),
    (@max, 'cs_CZ', NULL, NULL, NULL, NULL),
    (@max, 'de_DE', NULL, NULL, NULL, NULL),
    (@max, 'el_GR', NULL, NULL, NULL, NULL),
    (@max, 'en_US', 'Mail sent after a subscription to newsletter', 'Your subscription to {config key=\"store_name\"} newsletter', NULL, NULL),
    (@max, 'es_ES', 'Correo enviado después de la suscripción al boletín de noticias', 'Tu suscripción al boletín de {config key=\"store_name\"}', NULL, NULL),
    (@max, 'fa_IR', NULL, NULL, NULL, NULL),
    (@max, 'fr_FR', 'Email envoyé après l\'inscription à la newsletter', 'Votre abonnement à {config key=\"store_name\"} newsletter', NULL, NULL),
    (@max, 'hu_HU', NULL, NULL, NULL, NULL),
    (@max, 'id_ID', NULL, NULL, NULL, NULL),
    (@max, 'it_IT', NULL, NULL, NULL, NULL),
    (@max, 'pl_PL', NULL, NULL, NULL, NULL),
    (@max, 'pt_BR', NULL, NULL, NULL, NULL),
    (@max, 'pt_PT', NULL, NULL, NULL, NULL),
    (@max, 'ru_RU', 'Выслано письмо после подписки на рассылку', 'Подписка на рассылку новостей %store', NULL, NULL),
    (@max, 'sk_SK', 'E-mail zaslaný po prihlásení sa na odber noviniek', 'Odoberanie noviniek %store', NULL, NULL),
    (@max, 'tr_TR', NULL, NULL, NULL, NULL),
    (@max, 'uk_UA', NULL, NULL, NULL, NULL)
;

-- add new config variables number_default_results_per_page
SELECT @max := IFNULL(MAX(`id`),0) FROM `config`;

INSERT INTO `config` (`id`, `name`, `value`, `secured`, `hidden`, `created_at`, `updated_at`) VALUES (@max+1, 'number_default_results_per_page.product_list', '20', '0', '0', NOW(), NOW());
INSERT INTO `config` (`id`, `name`, `value`, `secured`, `hidden`, `created_at`, `updated_at`) VALUES (@max+2, 'number_default_results_per_page.order_list', '20', '0', '0', NOW(), NOW());
INSERT INTO `config` (`id`, `name`, `value`, `secured`, `hidden`, `created_at`, `updated_at`) VALUES (@max+3, 'number_default_results_per_page.customer_list', '20', '0', '0', NOW(), NOW());

INSERT INTO `config_i18n` (`id`, `locale`, `title`, `chapo`, `description`, `postscriptum`) VALUES
    (@max+1, 'ar_SA', NULL, NUll, NULL, NULL),
    (@max+2, 'ar_SA', NULL, NUll, NULL, NULL),
    (@max+3, 'ar_SA', NULL, NUll, NULL, NULL),
    (@max+1, 'cs_CZ', NULL, NUll, NULL, NULL),
    (@max+2, 'cs_CZ', NULL, NUll, NULL, NULL),
    (@max+3, 'cs_CZ', NULL, NUll, NULL, NULL),
    (@max+1, 'de_DE', NULL, NUll, NULL, NULL),
    (@max+2, 'de_DE', NULL, NUll, NULL, NULL),
    (@max+3, 'de_DE', NULL, NUll, NULL, NULL),
    (@max+1, 'el_GR', NULL, NUll, NULL, NULL),
    (@max+2, 'el_GR', NULL, NUll, NULL, NULL),
    (@max+3, 'el_GR', NULL, NUll, NULL, NULL),
    (@max+1, 'en_US', 'Default number of products on product list', NUll, NULL, NULL),
    (@max+2, 'en_US', 'Default number of orders on order list', NUll, NULL, NULL),
    (@max+3, 'en_US', 'Default number of customers on customer list', NUll, NULL, NULL),
    (@max+1, 'es_ES', 'Número predeterminado de resultados por página para la lista de productos', NUll, NULL, NULL),
    (@max+2, 'es_ES', 'Número predeterminado de resultados por página para la lista de pedidos', NUll, NULL, NULL),
    (@max+3, 'es_ES', 'Número predeterminado de resultados por página para la lista de clientes', NUll, NULL, NULL),
    (@max+1, 'fa_IR', NULL, NUll, NULL, NULL),
    (@max+2, 'fa_IR', NULL, NUll, NULL, NULL),
    (@max+3, 'fa_IR', NULL, NUll, NULL, NULL),
    (@max+1, 'fr_FR', 'Nombre par défaut de résultats par page pour la liste des produits', NUll, NULL, NULL),
    (@max+2, 'fr_FR', 'Nombre par défaut de résultats par page pour la liste des commandes', NUll, NULL, NULL),
    (@max+3, 'fr_FR', 'Nombre par défaut de résultats par page pour la liste des clients', NUll, NULL, NULL),
    (@max+1, 'hu_HU', NULL, NUll, NULL, NULL),
    (@max+2, 'hu_HU', NULL, NUll, NULL, NULL),
    (@max+3, 'hu_HU', NULL, NUll, NULL, NULL),
    (@max+1, 'id_ID', NULL, NUll, NULL, NULL),
    (@max+2, 'id_ID', NULL, NUll, NULL, NULL),
    (@max+3, 'id_ID', NULL, NUll, NULL, NULL),
    (@max+1, 'it_IT', NULL, NUll, NULL, NULL),
    (@max+2, 'it_IT', NULL, NUll, NULL, NULL),
    (@max+3, 'it_IT', NULL, NUll, NULL, NULL),
    (@max+1, 'pl_PL', NULL, NUll, NULL, NULL),
    (@max+2, 'pl_PL', NULL, NUll, NULL, NULL),
    (@max+3, 'pl_PL', NULL, NUll, NULL, NULL),
    (@max+1, 'pt_BR', NULL, NUll, NULL, NULL),
    (@max+2, 'pt_BR', NULL, NUll, NULL, NULL),
    (@max+3, 'pt_BR', NULL, NUll, NULL, NULL),
    (@max+1, 'pt_PT', NULL, NUll, NULL, NULL),
    (@max+2, 'pt_PT', NULL, NUll, NULL, NULL),
    (@max+3, 'pt_PT', NULL, NUll, NULL, NULL),
    (@max+1, 'ru_RU', 'Количество результатов по умолчанию для списка товаров', NUll, NULL, NULL),
    (@max+2, 'ru_RU', 'Количество результатов по умолчанию для списка заказов', NUll, NULL, NULL),
    (@max+3, 'ru_RU', 'Количество результатов по умолчанию для списка клиентов', NUll, NULL, NULL),
    (@max+1, 'sk_SK', NULL, NUll, NULL, NULL),
    (@max+2, 'sk_SK', NULL, NUll, NULL, NULL),
    (@max+3, 'sk_SK', NULL, NUll, NULL, NULL),
    (@max+1, 'tr_TR', NULL, NUll, NULL, NULL),
    (@max+2, 'tr_TR', NULL, NUll, NULL, NULL),
    (@max+3, 'tr_TR', NULL, NUll, NULL, NULL),
    (@max+1, 'uk_UA', NULL, NUll, NULL, NULL),
    (@max+2, 'uk_UA', NULL, NUll, NULL, NULL),
    (@max+3, 'uk_UA', NULL, NUll, NULL, NULL)
;

-- Add module HookAdminHome
SELECT @max_id := IFNULL(MAX(`id`),0) FROM `module`;
SELECT @max_classic_position := IFNULL(MAX(`position`),0) FROM `module` WHERE `type`=1;

INSERT INTO `module` (`id`, `code`, `type`, `activate`, `position`, `full_namespace`, `created_at`, `updated_at`) VALUES
(@max_id+1, 'HookAdminHome', 1, 1, @max_classic_position+1, 'HookAdminHome\\HookAdminHome', NOW(), NOW())
;

INSERT INTO  `module_i18n` (`id`, `locale`, `title`, `description`, `chapo`, `postscriptum`) VALUES
(@max_id+1, 'ar_SA', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'cs_CZ', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'de_DE', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'el_GR', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'en_US', 'Displays the default blocks on the homepage of the administration', NULL,  NULL,  NULL),
(@max_id+1, 'es_ES', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'fa_IR', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'fr_FR', 'Affiche les blocs par défaut sur la page d\'accueil de l\'administration', NULL,  NULL,  NULL),
(@max_id+1, 'hu_HU', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'id_ID', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'it_IT', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'pl_PL', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'pt_BR', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'pt_PT', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'ru_RU', 'Отображение стандартных блоков на главной админки', NULL,  NULL,  NULL),
(@max_id+1, 'sk_SK', 'Zobrazuje predvolené bloky na domovskú stránku správy', NULL,  NULL,  NULL),
(@max_id+1, 'tr_TR', NULL, NULL,  NULL,  NULL),
(@max_id+1, 'uk_UA', NULL, NULL,  NULL,  NULL)
;

-- Update customer lang FK
ALTER TABLE `customer` CHANGE `lang` `lang_id` INT(11)  NULL  DEFAULT NULL;
ALTER TABLE `customer` ADD INDEX `idx_email` (`email`);
ALTER TABLE `customer` ADD INDEX `idx_customer_lang_id` (`lang_id`);
ALTER TABLE `customer` ADD CONSTRAINT `fk_customer_lang_id` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT;

OPTIMIZE TABLE `customer`;


-- Update customer version
ALTER TABLE `customer_version` CHANGE `lang` `lang_id` INT(11)  NULL  DEFAULT NULL;


-- Update newletter index
ALTER TABLE `newsletter` ADD INDEX `idx_unsubscribed` (`unsubscribed`);

OPTIMIZE TABLE `newsletter`;

SET FOREIGN_KEY_CHECKS = 1;
