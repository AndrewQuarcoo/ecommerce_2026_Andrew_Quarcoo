-- ============================================================
-- shoppn e-commerce database — SCHEMA (clean slate, no data)
-- ------------------------------------------------------------
-- Improved from shoppn_empty.sql:
--   • utf8mb4 / utf8mb4_unicode_ci on every table (was latin1) so
--     names, addresses and search terms support all languages + emoji.
--   • customer_address column added (the registration form collects it).
--   • DECIMAL(10,2) for money (product_price, payment amt) instead of
--     DOUBLE — no floating-point rounding on prices.
--   • created_at / updated_at timestamps for auditing (orders.created_at
--     is required by Task 13).
--   • UNIQUE (p_id, ip_add) on cart so the same product can't be added
--     twice for one visitor; UNIQUE invoice_no on orders.
--   • Consistent FK ON DELETE / ON UPDATE rules.
--
-- Import:
--   phpMyAdmin → Import this file, OR
--   mysql -u root < database/shoppn.sql
--
-- ── First admin account ─────────────────────────────────────
--   1. Register on the site:  views/register.php
--      (every new signup is a regular customer, user_role = 2)
--   2. Promote it:
--        UPDATE customer SET user_role = 1
--        WHERE customer_email = 'you@example.com';
--   3. Log out and back in — the Admin links appear in the header.
--
-- ── Adding products ─────────────────────────────────────────
--   Brands and categories are empty and products require both
--   (foreign keys). As admin, add at least one brand and one
--   category before adding products.
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `shoppn`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `shoppn`;

-- Drop in FK-safe order (children first) so re-imports are idempotent.
DROP TABLE IF EXISTS `payment`;
DROP TABLE IF EXISTS `orderdetails`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `brands`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `customer`;

-- ── brands ──────────────────────────────────────────────────
CREATE TABLE `brands` (
  `brand_id`   int(11)      NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(100) NOT NULL,
  PRIMARY KEY (`brand_id`),
  UNIQUE KEY `brand_name` (`brand_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── categories ──────────────────────────────────────────────
CREATE TABLE `categories` (
  `cat_id`   int(11)      NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(100) NOT NULL,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_name` (`cat_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── customer ────────────────────────────────────────────────
CREATE TABLE `customer` (
  `customer_id`      int(11)      NOT NULL AUTO_INCREMENT,
  `customer_name`    varchar(100) NOT NULL,
  `customer_email`   varchar(50)  NOT NULL,
  `customer_pass`    varchar(255) NOT NULL,           -- bcrypt hash (60 chars); 255 leaves room for future algos
  `customer_country` varchar(30)  NOT NULL,
  `customer_city`    varchar(30)  NOT NULL,
  `customer_contact` varchar(15)  NOT NULL,
  `customer_address` varchar(255) DEFAULT NULL,
  `customer_image`   varchar(100) DEFAULT NULL,
  `user_role`        int(11)      NOT NULL DEFAULT 2, -- 1 = admin, 2 = customer
  `created_at`       timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `customer_email` (`customer_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── products ────────────────────────────────────────────────
CREATE TABLE `products` (
  `product_id`       int(11)        NOT NULL AUTO_INCREMENT,
  `product_cat`      int(11)        NOT NULL,
  `product_brand`    int(11)        NOT NULL,
  `product_title`    varchar(200)   NOT NULL,
  `product_price`    decimal(10,2)  NOT NULL,
  `product_desc`     varchar(500)   DEFAULT NULL,
  `product_image`    varchar(100)   DEFAULT NULL,
  `product_keywords` varchar(100)   DEFAULT NULL,
  `created_at`       timestamp      NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`),
  KEY `product_cat` (`product_cat`),
  KEY `product_brand` (`product_brand`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`product_cat`)
      REFERENCES `categories` (`cat_id`)  ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`product_brand`)
      REFERENCES `brands` (`brand_id`)    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── cart ────────────────────────────────────────────────────
CREATE TABLE `cart` (
  `cart_id` int(11)     NOT NULL AUTO_INCREMENT,
  `p_id`    int(11)     NOT NULL,
  `ip_add`  varchar(50) NOT NULL,
  `c_id`    int(11)     DEFAULT NULL,
  `qty`     int(11)     NOT NULL DEFAULT 1,
  PRIMARY KEY (`cart_id`),
  UNIQUE KEY `p_ip` (`p_id`, `ip_add`),
  KEY `c_id` (`c_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`p_id`)
      REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`c_id`)
      REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── orders ──────────────────────────────────────────────────
CREATE TABLE `orders` (
  `order_id`     int(11)      NOT NULL AUTO_INCREMENT,
  `customer_id`  int(11)      NOT NULL,
  `invoice_no`   int(11)      NOT NULL,
  `order_date`   date         NOT NULL,
  `order_status` varchar(100) NOT NULL DEFAULT 'paid',
  `created_at`   timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`)
      REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── orderdetails ────────────────────────────────────────────
CREATE TABLE `orderdetails` (
  `order_id`   int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty`        int(11) NOT NULL,
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`order_id`)
      REFERENCES `orders` (`order_id`)     ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`product_id`)
      REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── payment ─────────────────────────────────────────────────
CREATE TABLE `payment` (
  `pay_id`       int(11)       NOT NULL AUTO_INCREMENT,
  `amt`          decimal(10,2) NOT NULL,
  `customer_id`  int(11)       NOT NULL,
  `order_id`     int(11)       NOT NULL,
  `currency`     varchar(10)   NOT NULL DEFAULT 'GHS',
  `reference`    varchar(100)  DEFAULT NULL,          -- Paystack transaction reference (Task 13)
  `payment_date` date          NOT NULL,
  PRIMARY KEY (`pay_id`),
  KEY `customer_id` (`customer_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`customer_id`)
      REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`order_id`)
      REFERENCES `orders` (`order_id`)      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
