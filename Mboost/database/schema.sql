-- ============================================================
-- M'Boost Juice Bar - Database Schema
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+03:00';
SET foreign_key_checks = 0;
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ------------------------------------------------------------
-- Drop tables in reverse dependency order
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `paiements`;
DROP TABLE IF EXISTS `lignes_commandes`;
DROP TABLE IF EXISTS `commande_statuts_historique`;
DROP TABLE IF EXISTS `commandes`;
DROP TABLE IF EXISTS `jus_ingredients`;
DROP TABLE IF EXISTS `jus_personnalises`;
DROP TABLE IF EXISTS `annonces_sante`;
DROP TABLE IF EXISTS `hero_publicites`;
DROP TABLE IF EXISTS `ingredients`;
DROP TABLE IF EXISTS `parametres`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `users`;

-- ------------------------------------------------------------
-- Create database (run separately if needed)
-- CREATE DATABASE IF NOT EXISTS `mboost_db`
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;
-- USE `mboost_db`;
-- ------------------------------------------------------------

-- ============================================================
-- Table: users
-- ============================================================
CREATE TABLE `users` (
    `id`                 INT UNSIGNED       NOT NULL AUTO_INCREMENT,
    `nom`                VARCHAR(100)       NOT NULL,
    `prenom`             VARCHAR(100)       NOT NULL,
    `email`              VARCHAR(180)       NOT NULL,
    `telephone`          VARCHAR(20)        NOT NULL,
    `mot_de_passe`       VARCHAR(255)       NOT NULL COMMENT 'bcrypt hash',
    `adresse_livraison`  TEXT               DEFAULT NULL,
    `statut`             ENUM('actif','bloque') NOT NULL DEFAULT 'actif',
    `created_at`         TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Registered customer accounts';

-- ============================================================
-- Table: admins
-- ============================================================
CREATE TABLE `admins` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nom`          VARCHAR(100)  NOT NULL,
    `prenom`       VARCHAR(100)  NOT NULL,
    `email`        VARCHAR(180)  NOT NULL,
    `mot_de_passe` VARCHAR(255)  NOT NULL COMMENT 'bcrypt hash',
    `role`         ENUM('super_admin','admin') NOT NULL DEFAULT 'admin',
    `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admins_email` (`email`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Back-office administrator accounts';

-- ============================================================
-- Table: ingredients
-- ============================================================
CREATE TABLE `ingredients` (
    `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `nom`         VARCHAR(150)   NOT NULL,
    `categorie`   ENUM('legumes','fruits','graines') NOT NULL,
    `description` TEXT           DEFAULT NULL,
    `prix`        DECIMAL(10,2)  NOT NULL DEFAULT 0.00 COMMENT 'Base price in Ariary',
    `image`       VARCHAR(255)   DEFAULT NULL COMMENT 'Filename stored under uploads/',
    `stock`       INT            NOT NULL DEFAULT 100,
    `emoji`       VARCHAR(10)    DEFAULT NULL,
    `actif`       BOOLEAN        NOT NULL DEFAULT TRUE,
    `created_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ingredients_categorie` (`categorie`),
    KEY `idx_ingredients_actif`     (`actif`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Juice ingredients catalogue';

-- ============================================================
-- Table: jus_personnalises
-- ============================================================
CREATE TABLE `jus_personnalises` (
    `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED   NOT NULL,
    `nom_jus`     VARCHAR(200)   NOT NULL,
    `taille`      ENUM('petit','moyen','grand') NOT NULL DEFAULT 'moyen',
    `prix_total`  DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `created_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_jus_user` (`user_id`),
    CONSTRAINT `fk_jus_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Custom juices created by users';

-- ============================================================
-- Table: jus_ingredients  (pivot)
-- ============================================================
CREATE TABLE `jus_ingredients` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `jus_id`        INT UNSIGNED NOT NULL,
    `ingredient_id` INT UNSIGNED NOT NULL,
    `quantite`      INT          NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_jus_ingredient` (`jus_id`, `ingredient_id`),
    KEY `idx_ji_ingredient` (`ingredient_id`),
    CONSTRAINT `fk_ji_jus`
        FOREIGN KEY (`jus_id`)        REFERENCES `jus_personnalises` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ji_ingredient`
        FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Many-to-many: custom juice <-> ingredient';

-- ============================================================
-- Table: commandes
-- ============================================================
CREATE TABLE `commandes` (
    `id`                  INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`             INT UNSIGNED   NOT NULL,
    `adresse_livraison`   TEXT           NOT NULL,
    `sous_total`          DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `frais_livraison`     DECIMAL(10,2)  NOT NULL DEFAULT 1000.00,
    `total`               DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `statut`              ENUM(
                            'en_attente',
                            'paye',
                            'en_preparation',
                            'en_livraison',
                            'livre',
                            'annule'
                          ) NOT NULL DEFAULT 'en_attente',
    `mode_paiement`       ENUM('orange_money','mvola') NOT NULL,
    `numero_transaction`  VARCHAR(100)   DEFAULT NULL,
    `notes`               TEXT           DEFAULT NULL,
    `created_at`          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_commandes_user`   (`user_id`),
    KEY `idx_commandes_statut` (`statut`),
    CONSTRAINT `fk_commandes_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Customer orders';

-- ============================================================
-- Table: commande_statuts_historique
-- ============================================================
CREATE TABLE `commande_statuts_historique` (
    `id`                 INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `commande_id`        INT UNSIGNED   NOT NULL,
    `ancien_statut`      VARCHAR(50)    DEFAULT NULL,
    `nouveau_statut`     VARCHAR(50)    NOT NULL,
    `note`               VARCHAR(255)   DEFAULT NULL,
    `changed_by_admin`   INT UNSIGNED   DEFAULT NULL COMMENT 'FK to admins.id (soft ref)',
    `created_at`         TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_csh_commande` (`commande_id`),
    KEY `idx_csh_nouveau_statut` (`nouveau_statut`),
    KEY `idx_csh_created_at` (`created_at`),
    CONSTRAINT `fk_csh_commande`
        FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Order status transition history';

-- ============================================================
-- Table: lignes_commandes
-- ============================================================
CREATE TABLE `lignes_commandes` (
    `id`                 INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `commande_id`        INT UNSIGNED   NOT NULL,
    `jus_id`             INT UNSIGNED   DEFAULT NULL COMMENT 'NULL if juice was later deleted',
    `nom_jus`            VARCHAR(200)   NOT NULL,
    `taille`             ENUM('petit','moyen','grand') NOT NULL DEFAULT 'moyen',
    `quantite`           INT            NOT NULL DEFAULT 1,
    `prix_unitaire`      DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `prix_total`         DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `ingredients_detail` JSON           DEFAULT NULL COMMENT 'Snapshot of ingredients at order time',
    PRIMARY KEY (`id`),
    KEY `idx_lc_commande` (`commande_id`),
    KEY `idx_lc_jus`      (`jus_id`),
    CONSTRAINT `fk_lc_commande`
        FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lc_jus`
        FOREIGN KEY (`jus_id`) REFERENCES `jus_personnalises` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Line items within an order';

-- ============================================================
-- Table: paiements
-- ============================================================
CREATE TABLE `paiements` (
    `id`                  INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `commande_id`         INT UNSIGNED   NOT NULL,
    `montant`             DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `mode_paiement`       ENUM('orange_money','mvola') NOT NULL,
    `numero_transaction`  VARCHAR(100)   NOT NULL,
    `statut`              ENUM('en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente',
    `justificatif`        VARCHAR(255)   DEFAULT NULL COMMENT 'Upload filename of payment proof',
    `notes_admin`         TEXT           DEFAULT NULL,
    `validated_at`        TIMESTAMP      NULL DEFAULT NULL,
    `created_at`          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_paiements_commande` (`commande_id`),
    KEY `idx_paiements_statut`   (`statut`),
    CONSTRAINT `fk_paiements_commande`
        FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Payment records for orders';

-- ============================================================
-- Table: annonces_sante
-- ============================================================
CREATE TABLE `annonces_sante` (
    `id`                   INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `titre`                VARCHAR(255)   NOT NULL,
    `categories`           JSON           DEFAULT NULL  COMMENT 'Array of category tags e.g. ["detox","immunite"]',
    `organes_cibles`       VARCHAR(255)   DEFAULT NULL  COMMENT 'Comma-separated targeted organs',
    `description`          TEXT           DEFAULT NULL,
    `image_url`            VARCHAR(255)   DEFAULT NULL,
    `composition_jus`      JSON           DEFAULT NULL  COMMENT 'Array of {ingredient_id, nom, quantite}',
    `taille_suggeree`      ENUM('petit','moyen','grand') DEFAULT 'moyen',
    `prix_estime`          DECIMAL(10,2)  DEFAULT NULL,
    `conseils_consommation` TEXT          DEFAULT NULL,
    `sources`              TEXT           DEFAULT NULL,
    `statut`               ENUM('brouillon','publie','archive') NOT NULL DEFAULT 'brouillon',
    `date_publication`     TIMESTAMP      NULL DEFAULT NULL,
    `epingle`              BOOLEAN        NOT NULL DEFAULT FALSE,
    `nb_vues`              INT            NOT NULL DEFAULT 0,
    `nb_clics_creation`    INT            NOT NULL DEFAULT 0,
    `created_by`           INT UNSIGNED   DEFAULT NULL  COMMENT 'FK to admins.id (soft ref)',
    `created_at`           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_annonces_statut`  (`statut`),
    KEY `idx_annonces_epingle` (`epingle`),
    KEY `idx_annonces_created_by` (`created_by`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Health-tip announcements with suggested juice compositions';

-- ============================================================
-- Table: hero_publicites
-- ============================================================
CREATE TABLE `hero_publicites` (
    `id`                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `titre`             VARCHAR(255)   NOT NULL,
    `description`       TEXT           DEFAULT NULL,
    `media_type`        ENUM('image','video') NOT NULL DEFAULT 'image',
    `media_url`         VARCHAR(255)   DEFAULT NULL,
    `lien_url`          VARCHAR(255)   DEFAULT NULL,
    `statut`            ENUM('brouillon','publie') NOT NULL DEFAULT 'brouillon',
    `ordre_affichage`   INT            NOT NULL DEFAULT 0,
    `created_by`        INT UNSIGNED   DEFAULT NULL COMMENT 'FK to admins.id (soft ref)',
    `created_at`        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_hero_publicites_statut` (`statut`),
    KEY `idx_hero_publicites_ordre` (`ordre_affichage`),
    KEY `idx_hero_publicites_created_by` (`created_by`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Hero section advertisements (image/video)';

-- ============================================================
-- Table: parametres  (key-value store for app settings)
-- ============================================================
CREATE TABLE `parametres` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `cle`        VARCHAR(100)  NOT NULL,
    `valeur`     TEXT          DEFAULT NULL,
    `updated_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_parametres_cle` (`cle`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Application configuration key-value pairs';

SET foreign_key_checks = 1;

-- ============================================================
-- Seed: default application parameters
-- ============================================================
INSERT INTO `parametres` (`cle`, `valeur`) VALUES
    ('frais_livraison',         '1000'),
    ('tel_orange_money',        '+261 34 00 000 00'),
    ('tel_mvola',               '+261 38 00 000 00'),
    ('message_bienvenue',       'Bienvenue chez M\'Boost, votre bar à jus personnalisé !'),
    ('delai_livraison_minutes', '45'),
    ('maintenance_mode',        '0');

-- ============================================================
-- Seed: default super-admin
-- Password: Admin@1234  (bcrypt, cost 12)
-- CHANGE THIS IMMEDIATELY AFTER FIRST LOGIN
-- ============================================================
INSERT INTO `admins` (`nom`, `prenom`, `email`, `mot_de_passe`, `role`) VALUES
    ('Admin', 'M\'Boost', 'admin@mboost.mg',
     '$2y$12$eImiTXuWVxfM37uY4JANjQ==ignored_placeholder_replace_me',
     'super_admin');

-- ============================================================
-- Seed: sample ingredients
-- ============================================================
INSERT INTO `ingredients` (`nom`, `categorie`, `description`, `prix`, `stock`, `emoji`, `actif`) VALUES
    ('Carotte',      'legumes', 'Riche en bêta-carotène et vitamine A.',    300.00, 200, '🥕', TRUE),
    ('Concombre',    'legumes', 'Hydratant et faible en calories.',          250.00, 150, '🥒', TRUE),
    ('Épinard',      'legumes', 'Riche en fer et vitamines B.',              350.00, 100, '🥬', TRUE),
    ('Betterave',    'legumes', 'Améliore la circulation sanguine.',         400.00, 120, '🫚', TRUE),
    ('Gingembre',    'legumes', 'Anti-inflammatoire naturel.',               500.00,  80, '🫚', TRUE),
    ('Mangue',       'fruits',  'Riche en vitamine C et antioxydants.',      600.00, 180, '🥭', TRUE),
    ('Ananas',       'fruits',  'Contient de la bromélaïne, digestif.',      500.00, 160, '🍍', TRUE),
    ('Banane',       'fruits',  'Source d\'énergie rapide, riche en potassium.', 300.00, 200, '🍌', TRUE),
    ('Orange',       'fruits',  'Vitamine C et flavonoïdes.',                400.00, 170, '🍊', TRUE),
    ('Citron',       'fruits',  'Alcalinisant, riche en vitamine C.',        200.00, 200, '🍋', TRUE),
    ('Pastèque',     'fruits',  'Hydratant, riche en lycopène.',             350.00, 140, '🍉', TRUE),
    ('Graines de chia', 'graines', 'Riches en oméga-3 et fibres.',          800.00,  60, '🌱', TRUE),
    ('Graines de lin',  'graines', 'Source d\'oméga-3 et lignanes.',        700.00,  60, '🌾', TRUE),
    ('Graines de tournesol', 'graines', 'Riches en vitamine E.',            600.00,  70, '🌻', TRUE);
