<?php
require_once __DIR__ . '/config.php';

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function db_init(): void {
    $pdo = get_db();

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Safe migrations for existing DBs
    $migrations = [
        "ALTER TABLE users ADD COLUMN avatar_url MEDIUMTEXT DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN bio VARCHAR(300) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN plan VARCHAR(30) DEFAULT 'free'",
        "ALTER TABLE users ADD COLUMN scans_today INT DEFAULT 0",
        "ALTER TABLE users ADD COLUMN scans_date DATE DEFAULT NULL",
    ];
    foreach ($migrations as $m) { try { $pdo->exec($m); } catch (Throwable) {} }

    $pdo->exec("CREATE TABLE IF NOT EXISTS scans (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS saved_scans (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        user_id    INT NOT NULL,
        scan_id    INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_user_scan (user_id, scan_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (scan_id) REFERENCES scans(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS offers (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        title       VARCHAR(200) NOT NULL,
        description TEXT NOT NULL,
        badge       VARCHAR(50) DEFAULT NULL,
        price       VARCHAR(50) DEFAULT NULL,
        active      TINYINT(1) DEFAULT 1,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT DEFAULT NULL,
        action      VARCHAR(100) NOT NULL,
        detail      TEXT DEFAULT NULL,
        ip_address  VARCHAR(64) DEFAULT NULL,
        user_agent  VARCHAR(512) DEFAULT NULL,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    try { $pdo->exec("ALTER TABLE audit_log ADD COLUMN user_agent VARCHAR(512) DEFAULT NULL"); } catch (Throwable) {}

    seed_defaults($pdo);
}

/**
 * Seed default users — always ensures admin and demo exist
 * with correct bcrypt passwords (re-seeds on every boot if needed).
 */
function seed_defaults(PDO $pdo): void {
    // ── Admin: admin@ova9.io / Admin9999! ──────────────────
    $chk = $pdo->prepare("SELECT id, password FROM users WHERE email='admin@ova9.io'");
    $chk->execute();
    $existing = $chk->fetch();
    if (!$existing) {
        $pdo->prepare("INSERT INTO users (username,email,password,is_admin,plan,bio) VALUES (?,?,?,?,?,?)")
            ->execute(['admin','admin@ova9.io',password_hash('Admin9999!',PASSWORD_BCRYPT),1,'enterprise','Platform administrator']);
    } elseif (!password_verify('Admin9999!', $existing['password'])) {
        // Fix wrong hash from SQL import
        $pdo->prepare("UPDATE users SET password=? WHERE email='admin@ova9.io'")
            ->execute([password_hash('Admin9999!',PASSWORD_BCRYPT)]);
    }

    // ── Demo: demo@ova9.io / Demo1234! ─────────────────────
    $chk2 = $pdo->prepare("SELECT id, password FROM users WHERE email='demo@ova9.io'");
    $chk2->execute();
    $existing2 = $chk2->fetch();
    if (!$existing2) {
        $pdo->prepare("INSERT INTO users (username,email,password,is_admin,plan,bio) VALUES (?,?,?,?,?,?)")
            ->execute(['demo','demo@ova9.io',password_hash('Demo1234!',PASSWORD_BCRYPT),0,'free','Security researcher & demo account']);
    } elseif (!password_verify('Demo1234!', $existing2['password'])) {
        // Fix wrong hash from SQL import
        $pdo->prepare("UPDATE users SET password=? WHERE email='demo@ova9.io'")
            ->execute([password_hash('Demo1234!',PASSWORD_BCRYPT)]);
    }

    // ── Default offers ──────────────────────────────────────
    $cnt = (int)$pdo->query("SELECT COUNT(*) FROM offers")->fetchColumn();
    if ($cnt === 0) {
        $pdo->exec("INSERT INTO offers (title,description,badge,price,active) VALUES
            ('Gratuit',
             '3 scans par jour inclus. Toutes les 9 catégories de tests, téléchargement des rapports.',
             NULL,'Gratuit',1),
            ('Pro',
             'Scans illimités, toutes les 9 catégories, traitement prioritaire, export PDF/texte, historique complet.',
             'MOST POPULAR','29 DT/mois',1),
            ('Enterprise',
             'Comptes équipes, accès API complet, intégrations personnalisées, support SLA dédié 24/7.',
             'CONTACT US','Sur devis',1)");
    }
}

// ── Helpers ─────────────────────────────────────────────────────
function audit(string $action, ?string $detail = null): void {
    try {
        $user = get_current_user_silent();
        $uid  = $user['id'] ?? null;
        $ip   = get_client_ip();
        $ua   = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512);
        get_db()->prepare("INSERT INTO audit_log (user_id,action,detail,ip_address,user_agent) VALUES (?,?,?,?,?)")
            ->execute([$uid, $action, $detail, $ip, $ua]);
    } catch (Throwable) {}
}

function get_client_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) return explode(',', $_SERVER[$k])[0];
    }
    return '127.0.0.1';
}

function check_daily_limit(PDO $pdo, array $user): bool {
    if (($user['plan'] ?? 'free') !== 'free') return true;
    $today = date('Y-m-d');
    if ($user['scans_date'] !== $today) {
        $pdo->prepare("UPDATE users SET scans_today=0, scans_date=? WHERE id=?")->execute([$today, $user['id']]);
        return true;
    }
    return (int)$user['scans_today'] < FREE_DAILY_LIMIT;
}

function increment_scan_count(PDO $pdo, int $userId): void {
    $pdo->prepare("UPDATE users SET scans_today=scans_today+1, scans_date=CURDATE() WHERE id=?")
        ->execute([$userId]);
}
