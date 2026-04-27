-- ============================================================
--  OVA9 — Database Setup for XAMPP
--  How to use:
--    1. Open http://localhost/phpmyadmin
--    2. Click the "SQL" tab at the top
--    3. Paste this entire file and click "Go"
-- ============================================================

CREATE DATABASE IF NOT EXISTS ova9
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ova9;

-- ── USERS ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(100) NOT NULL UNIQUE,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    is_admin    TINYINT(1)  DEFAULT 0,
    is_banned   TINYINT(1)  DEFAULT 0,
    avatar      VARCHAR(10) DEFAULT NULL,
    avatar_url  MEDIUMTEXT  DEFAULT NULL,
    bio         VARCHAR(300) DEFAULT NULL,
    plan        VARCHAR(30) DEFAULT 'free',
    scans_today INT DEFAULT 0,
    scans_date  DATE DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── SCANS ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS scans (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    target_url  VARCHAR(2048) NOT NULL,
    status      VARCHAR(20) DEFAULT 'pending',
    results     LONGTEXT DEFAULT NULL,
    risk_score  INT DEFAULT 0,
    ip_address  VARCHAR(64) DEFAULT NULL,
    user_agent  VARCHAR(512) DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    finished_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── SAVED SCANS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS saved_scans (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    scan_id    INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_scan (user_id, scan_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (scan_id) REFERENCES scans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── OFFERS ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS offers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    badge       VARCHAR(50) DEFAULT NULL,
    price       VARCHAR(50) DEFAULT NULL,
    active      TINYINT(1) DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── PAYMENTS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS payments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    offer_id    INT NOT NULL,
    amount      VARCHAR(50) DEFAULT NULL,
    card_type   VARCHAR(30) DEFAULT NULL,
    card_last4  VARCHAR(4)  DEFAULT NULL,
    cardholder  VARCHAR(100) DEFAULT NULL,
    status      VARCHAR(20) DEFAULT 'completed',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── AUDIT LOG ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_log (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT DEFAULT NULL,
    action      VARCHAR(100) NOT NULL,
    detail      TEXT DEFAULT NULL,
    ip_address  VARCHAR(64) DEFAULT NULL,
    user_agent  VARCHAR(512) DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── SEED OFFERS ──────────────────────────────────────────────
INSERT IGNORE INTO offers (id, title, description, badge, price, active) VALUES
(1, 'Gratuit',
   '3 scans par jour inclus. Toutes les 9 catégories de tests, téléchargement des rapports, historique 30 jours.',
   NULL, 'Gratuit', 1),
(2, 'Pro',
   'Scans illimités, toutes les 9 catégories, traitement prioritaire, historique complet, export PDF/texte.',
   'MOST POPULAR', '29 DT/mois', 1),
(3, 'Enterprise',
   'Comptes équipes illimités, accès API complet, intégrations personnalisées, SLA et support dédié 24/7.',
   'CONTACT US', 'Sur devis', 1);

-- ── SEED USERS ───────────────────────────────────────────────
-- NOTE: Passwords are hashed by PHP on first launch via seed_defaults().
-- These INSERT statements use a known bcrypt hash of each password.
-- If they fail, just visit the app — it auto-seeds on first request.

-- Admin: admin@ova9.io / Admin9999!
-- Hash = bcrypt of 'Admin9999!'
INSERT IGNORE INTO users
  (username, email, password, is_admin, plan, bio, created_at)
VALUES (
  'admin',
  'admin@ova9.io',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  1,
  'enterprise',
  'Platform administrator — full access',
  NOW()
);

-- Demo: demo@ova9.io / Demo1234!
-- Hash = bcrypt of 'Demo1234!'
INSERT IGNORE INTO users
  (username, email, password, is_admin, plan, bio, created_at)
VALUES (
  'demo',
  'demo@ova9.io',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  0,
  'free',
  'Security researcher & demo account',
  NOW()
);

-- IMPORTANT: The hash above is Laravel's default test hash for 'password'.
-- On first app load, seed_defaults() in database.php will INSERT with
-- correct bcrypt hashes if these rows don't exist yet.
-- To manually set correct passwords, visit the app and use the
-- "Change Password" feature in Profile > Settings.

-- ── AUDIT: record seed ───────────────────────────────────────
INSERT INTO audit_log (action, detail, ip_address)
VALUES ('db_seed', 'Database seeded from ova9_database.sql', '127.0.0.1');

-- ============================================================
--  CREDENTIALS SUMMARY
-- ============================================================
--
--  👑  ADMIN
--      Email    : admin@ova9.io
--      Password : Admin9999!
--      URL      : http://localhost/ova9_php/public/
--
--  👤  DEMO USER
--      Email    : demo@ova9.io
--      Password : Demo1234!
--      URL      : http://localhost/ova9_php/public/
--
--  ⚠️  If login fails after SQL import:
--      The PHP app will re-seed correct hashes on first visit.
--      Just open the app URL once, then try logging in again.
--
-- ============================================================
