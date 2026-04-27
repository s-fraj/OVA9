<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// Init DB on every request (creates tables if missing)
try { db_init(); } catch (Throwable $e) {
    json_error('Database error: ' . $e->getMessage(), 500);
}

$method = $_SERVER['REQUEST_METHOD'];
// Strip subfolder prefix for XAMPP (e.g. /ova9_php_new/api/... -> /api/...)
$_raw = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_pos = strpos($_raw, '/api');
$uri  = $_pos !== false ? rtrim(substr($_raw, $_pos), '/') : rtrim($_raw, '/');

// ══ AUTH ════════════════════════════════════════════════════════════════

if ($method === 'POST' && $uri === '/api/auth/signup') {
    $b        = json_decode(file_get_contents('php://input'), true) ?? [];
    $username = trim($b['username'] ?? '');
    $email    = trim($b['email']    ?? '');
    $password = $b['password'] ?? '';
    if (!$username) json_error('Nom d\'utilisateur requis');
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Email invalide');
    if (strlen($password) < 8) json_error('Mot de passe trop court (min 8 caractères)');
    $pdo = get_db();
    $chk = $pdo->prepare("SELECT id FROM users WHERE email=?"); $chk->execute([$email]);
    if ($chk->fetch()) json_error('Cet email est déjà utilisé', 400);
    $chk2 = $pdo->prepare("SELECT id FROM users WHERE username=?"); $chk2->execute([$username]);
    if ($chk2->fetch()) json_error('Ce nom d\'utilisateur est déjà pris', 400);
    $pdo->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)")
        ->execute([$username, $email, hash_password($password)]);
    $id = $pdo->lastInsertId();
    audit('signup', "New user: $username ($email)");
    $u = $pdo->prepare("SELECT id,username,email,is_admin,plan,created_at FROM users WHERE id=?");
    $u->execute([$id]); json_out($u->fetch(), 201);
}

if ($method === 'POST' && $uri === '/api/auth/login') {
    $b     = json_decode(file_get_contents('php://input'), true) ?? [];
    $email = $b['username'] ?? $b['email'] ?? '';
    $pass  = $b['password'] ?? '';
    if (!$email || !$pass) json_error('Email et mot de passe requis');
    $pdo  = get_db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?"); $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !verify_password($pass, $user['password'])) json_error('Email ou mot de passe incorrect', 401);
    if ($user['is_banned']) json_error('Compte suspendu. Contactez support@ova9.io', 403);
    audit('login', "Login successful — IP: " . get_client_ip());
    json_out(['access_token' => jwt_encode(['sub' => (string)$user['id']]), 'token_type' => 'bearer']);
}

// ══ USER ════════════════════════════════════════════════════════════════

if ($method === 'GET' && $uri === '/api/users/me') {
    $u = require_auth();
    json_out([
        'id'         => (int)$u['id'],
        'username'   => $u['username'],
        'email'      => $u['email'],
        'is_admin'   => (bool)$u['is_admin'],
        'avatar'     => $u['avatar'],
        'avatar_url' => $u['avatar_url'],
        'bio'        => $u['bio'],
        'plan'       => $u['plan'] ?? 'free',
        'created_at' => $u['created_at'],
    ]);
}

if ($method === 'PATCH' && $uri === '/api/users/me') {
    $u = require_auth();
    $b = json_decode(file_get_contents('php://input'), true) ?? [];
    $fields = []; $vals = [];
    if (isset($b['username']) && trim($b['username'])) { $fields[] = 'username=?'; $vals[] = trim($b['username']); }
    if (isset($b['bio']))     { $fields[] = 'bio=?';    $vals[] = substr(trim($b['bio']),0,300); }
    if (isset($b['avatar']))  { $fields[] = 'avatar=?'; $vals[] = substr(trim($b['avatar']),0,10); }
    if (isset($b['password']) && strlen($b['password']) >= 8) {
        $fields[] = 'password=?'; $vals[] = hash_password($b['password']);
    }
    if ($fields) {
        $vals[] = $u['id'];
        get_db()->prepare("UPDATE users SET " . implode(',', $fields) . " WHERE id=?")->execute($vals);
        audit('profile_update', 'User updated profile');
    }
    $stmt = get_db()->prepare("SELECT id,username,email,is_admin,avatar,avatar_url,bio,plan,created_at FROM users WHERE id=?");
    $stmt->execute([$u['id']]); json_out($stmt->fetch());
}

if ($method === 'POST' && $uri === '/api/users/avatar') {
    $u    = require_auth();
    $b    = json_decode(file_get_contents('php://input'), true) ?? [];
    $data = $b['data'] ?? '';
    if (!$data) json_error('Aucune image fournie');
    if (!preg_match('#^data:image/(png|jpg|jpeg|gif|webp);base64,#', $data)) json_error('Format d\'image invalide');
    $raw = base64_decode(preg_replace('#^data:image/\w+;base64,#', '', $data));
    if (!$raw || strlen($raw) > 2*1024*1024) json_error('Image trop grande (max 2MB)');
    get_db()->prepare("UPDATE users SET avatar_url=? WHERE id=?")->execute([$data, $u['id']]);
    audit('avatar_upload', 'Profile image updated');
    json_out(['ok' => true, 'avatar_url' => $data]);
}

// ══ SCAN LIMIT CHECK ════════════════════════════════════════════════════

if ($method === 'GET' && $uri === '/api/scan/limit') {
    $u   = require_auth();
    $pdo = get_db();
    $today = date('Y-m-d');
    $used  = 0;
    if ($u['plan'] === 'free') {
        if ($u['scans_date'] === $today) $used = (int)$u['scans_today'];
    }
    json_out([
        'plan'      => $u['plan'] ?? 'free',
        'limit'     => ($u['plan'] === 'free') ? FREE_DAILY_LIMIT : -1,
        'used_today'=> $used,
        'can_scan'  => ($u['plan'] !== 'free') || ($used < FREE_DAILY_LIMIT),
    ]);
}

// ══ SCANS ════════════════════════════════════════════════════════════════

if ($method === 'POST' && $uri === '/api/scan/start') {
    $u   = require_auth();
    $b   = json_decode(file_get_contents('php://input'), true) ?? [];
    $url = trim($b['target_url'] ?? '');

    if (!$url || !filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http','https']))
        json_error('URL invalide — utilisez http:// ou https://');

    $pdo = get_db();

    // Daily limit check for free users
    if (($u['plan'] ?? 'free') === 'free') {
        $today = date('Y-m-d');
        if ($u['scans_date'] === $today && (int)$u['scans_today'] >= FREE_DAILY_LIMIT) {
            json_error('Limite quotidienne atteinte (3 scans/jour). Passez à Pro pour des scans illimités.', 429);
        }
    }

    $ip = get_client_ip();
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512);

    // Save scan info immediately — target is the provided URL  
    // But we STORE the information directly for toppcinema.cr type URLs
    // without running actual tests (as per requirement)
    $pdo->prepare("INSERT INTO scans (user_id,target_url,ip_address,user_agent,status) VALUES (?,?,?,?,?)")
        ->execute([$u['id'], $url, $ip, $ua, 'pending']);
    $id = $pdo->lastInsertId();

    increment_scan_count($pdo, $u['id']);
    audit('scan_start', "Target: $url | IP: $ip");

    $s = $pdo->prepare("SELECT id,target_url,status,risk_score,created_at FROM scans WHERE id=?");
    $s->execute([$id]); json_out($s->fetch(), 201);
}

if ($method === 'GET' && $uri === '/api/scan/history') {
    $u = require_auth();
    $s = get_db()->prepare("SELECT id,target_url,status,risk_score,ip_address,created_at,finished_at FROM scans WHERE user_id=? ORDER BY created_at DESC");
    $s->execute([$u['id']]); json_out($s->fetchAll());
}

if ($method === 'GET' && $uri === '/api/scan/saved') {
    $u = require_auth();
    $rows = get_db()->prepare("SELECT s.id,s.target_url,s.status,s.risk_score,s.ip_address,s.created_at,s.finished_at FROM scans s JOIN saved_scans ss ON ss.scan_id=s.id WHERE ss.user_id=? ORDER BY ss.created_at DESC");
    $rows->execute([$u['id']]); json_out($rows->fetchAll());
}

if ($method === 'GET' && preg_match('#^/api/scan/(\d+)/stream$#', $uri, $m)) {
    $u = get_current_user();
    if (!$u) json_error('Non autorisé', 401);
    $pdo  = get_db();
    $stmt = $pdo->prepare("SELECT * FROM scans WHERE id=?"); $stmt->execute([(int)$m[1]]);
    $scan = $stmt->fetch();
    if (!$scan || (int)$scan['user_id'] !== (int)$u['id']) json_error('Introuvable', 404);
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
    if (ob_get_level()) ob_end_clean();
    require_once __DIR__ . '/../includes/scanner.php';
    run_all_tests($scan['target_url'], (int)$m[1], $pdo);
    exit;
}

if ($method === 'GET' && preg_match('#^/api/scan/(\d+)$#', $uri, $m)) {
    $u = require_auth();
    $stmt = get_db()->prepare("SELECT * FROM scans WHERE id=?"); $stmt->execute([(int)$m[1]]);
    $scan = $stmt->fetch();
    if (!$scan || ((int)$scan['user_id'] !== (int)$u['id'] && !$u['is_admin'])) json_error('Introuvable', 404);
    $scan['results'] = $scan['results'] ? json_decode($scan['results'], true) : [];
    json_out($scan);
}

if ($method === 'POST' && preg_match('#^/api/scan/(\d+)/cancel$#', $uri, $m)) {
    $u = require_auth();
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM scans WHERE id=?"); $stmt->execute([(int)$m[1]]);
    $scan = $stmt->fetch();
    if (!$scan || (int)$scan['user_id'] !== (int)$u['id']) json_error('Introuvable', 404);
    $pdo->prepare("UPDATE scans SET status='cancelled', finished_at=NOW() WHERE id=?")->execute([(int)$m[1]]);
    audit('scan_cancelled', "Scan #{$m[1]} cancelled");
    json_out(['ok' => true, 'status' => 'cancelled']);
}

if ($method === 'POST' && preg_match('#^/api/scan/(\d+)/save$#', $uri, $m)) {
    $u = require_auth();
    $pdo = get_db();
    $s = $pdo->prepare("SELECT id FROM scans WHERE id=? AND user_id=?"); $s->execute([(int)$m[1],$u['id']]);
    if (!$s->fetch()) json_error('Scan introuvable', 404);
    try {
        $pdo->prepare("INSERT INTO saved_scans (user_id,scan_id) VALUES (?,?)")->execute([$u['id'],(int)$m[1]]);
        audit('scan_saved', "Scan #{$m[1]} saved");
        json_out(['saved' => true]);
    } catch (Throwable) {
        $pdo->prepare("DELETE FROM saved_scans WHERE user_id=? AND scan_id=?")->execute([$u['id'],(int)$m[1]]);
        json_out(['saved' => false]);
    }
}

// ══ SCAN REPORT (tree-format download) ═══════════════════════════════════

if ($method === 'GET' && preg_match('#^/api/scan/(\d+)/report$#', $uri, $m)) {
    $u = require_auth();
    $stmt = get_db()->prepare("SELECT * FROM scans WHERE id=?"); $stmt->execute([(int)$m[1]]);
    $scan = $stmt->fetch();
    if (!$scan || ((int)$scan['user_id'] !== (int)$u['id'] && !$u['is_admin'])) json_error('Introuvable', 404);
    $results = $scan['results'] ? json_decode($scan['results'], true) : [];
    $SI = ['ok'=>'✅','warning'=>'⚠️','critical'=>'❌','info'=>'ℹ️'];

    $total_ok   = count(array_filter($results, fn($r) => $r['severity'] === 'ok'));
    $total_warn = count(array_filter($results, fn($r) => $r['severity'] === 'warning'));
    $total_crit = count(array_filter($results, fn($r) => $r['severity'] === 'critical'));
    $total_info = count(array_filter($results, fn($r) => $r['severity'] === 'info'));

    $w = 72;
    $bar = str_repeat('═', $w);
    $sep = '├' . str_repeat('─', $w-2) . '┤';

    $lines = [
        "╔{$bar}╗",
        "║" . str_pad("  OVA9 — RAPPORT DE SÉCURITÉ", $w) . "║",
        "╠{$bar}╣",
        "║" . str_pad("  Cible     : {$scan['target_url']}", $w) . "║",
        "║" . str_pad("  Date      : {$scan['created_at']}", $w) . "║",
        "║" . str_pad("  IP Source : {$scan['ip_address']}", $w) . "║",
        "║" . str_pad("  Statut    : " . strtoupper($scan['status']), $w) . "║",
        "║" . str_pad("  Score     : {$scan['risk_score']}/100", $w) . "║",
        "╠{$bar}╣",
        "║" . str_pad("  RÉSUMÉ :", $w) . "║",
        "║" . str_pad("  ✅ OK       : $total_ok", $w) . "║",
        "║" . str_pad("  ⚠️  WARN     : $total_warn", $w) . "║",
        "║" . str_pad("  ❌ CRITICAL : $total_crit", $w) . "║",
        "║" . str_pad("  ℹ️  INFO     : $total_info", $w) . "║",
        "╚{$bar}╝",
        "",
        "RÉSULTATS DÉTAILLÉS",
        str_repeat('─', $w),
    ];

    $cc = null;
    foreach ($results as $r) {
        if ($r['category'] !== $cc) {
            if ($cc) $lines[] = "│";
            $cc = $r['category'];
            $lines[] = "├── [{$cc}]";
        }
        $icon   = $SI[$r['severity']] ?? '•';
        $status = str_pad($r['status'], 8);
        $lines[] = "│   ├── $icon  {$r['check']}";
        $lines[] = "│   │       [$status] {$r['detail']}";
    }

    $lines = array_merge($lines, [
        "│",
        "└── ✅ Fin du rapport",
        "",
        str_repeat('═', $w),
        "  Généré par OVA9 — Plateforme de recherche en sécurité autorisée",
        "  Toute activité est enregistrée. Usage autorisé uniquement.",
        str_repeat('═', $w),
    ]);

    header('Content-Type: text/plain; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"ova9_rapport_{$scan['id']}.txt\"");
    echo implode("\n", $lines);
    exit;
}

// ══ PUBLIC OFFERS ════════════════════════════════════════════════════════

if ($method === 'GET' && $uri === '/api/offers') {
    $rows = get_db()->query("SELECT id,title,description,badge,price,created_at FROM offers WHERE active=1 ORDER BY id")->fetchAll();
    json_out($rows);
}

// ══ PAYMENT ══════════════════════════════════════════════════════════════

if ($method === 'POST' && $uri === '/api/payment') {
    $u   = require_auth();
    $b   = json_decode(file_get_contents('php://input'), true) ?? [];
    $pdo = get_db();

    $offer_id   = (int)($b['offer_id'] ?? 0);
    $card_type  = trim($b['card_type'] ?? '');
    $card_num   = preg_replace('/\s+/', '', $b['card_number'] ?? '');
    $card_expiry= trim($b['expiry'] ?? '');
    $card_cvv   = trim($b['cvv'] ?? '');
    $cardholder = trim($b['cardholder'] ?? '');

    // Validate
    if (!$offer_id) json_error('Offre invalide');
    if (!$card_type) json_error('Type de carte requis');
    if (strlen($card_num) < 16 || !ctype_digit($card_num)) json_error('Numéro de carte invalide (16 chiffres requis)');
    if (!preg_match('/^\d{2}\/\d{2}$/', $card_expiry)) json_error('Date d\'expiration invalide (format MM/AA)');
    if (strlen($card_cvv) < 3 || !ctype_digit($card_cvv)) json_error('CVV invalide');
    if (!$cardholder) json_error('Nom du titulaire requis');

    // Check expiry date
    [$em, $ey] = explode('/', $card_expiry);
    $expYear  = 2000 + (int)$ey;
    $expMonth = (int)$em;
    if ($expYear < (int)date('Y') || ($expYear === (int)date('Y') && $expMonth < (int)date('m')))
        json_error('Carte expirée');

    // Get offer
    $o = $pdo->prepare("SELECT * FROM offers WHERE id=? AND active=1"); $o->execute([$offer_id]);
    $offer = $o->fetch();
    if (!$offer) json_error('Offre introuvable ou inactive');

    $last4 = substr($card_num, -4);

    $pdo->prepare("INSERT INTO payments (user_id,offer_id,amount,card_type,card_last4,cardholder) VALUES (?,?,?,?,?,?)")
        ->execute([$u['id'], $offer_id, $offer['price'], $card_type, $last4, $cardholder]);

    // Upgrade user plan
    $plan = (strtolower($offer['title']) === 'enterprise') ? 'enterprise' : 'pro';
    $pdo->prepare("UPDATE users SET plan=? WHERE id=?")->execute([$plan, $u['id']]);

    audit('payment', "User subscribed to {$offer['title']} via $card_type card ****$last4 | IP: " . get_client_ip());

    json_out([
        'ok'       => true,
        'plan'     => $plan,
        'message'  => "Abonnement {$offer['title']} activé avec succès !",
    ]);
}

// ══ ADMIN ═══════════════════════════════════════════════════════════════

if ($method === 'GET' && $uri === '/api/admin/users') {
    require_admin();
    json_out(get_db()->query("SELECT id,username,email,is_admin,is_banned,plan,created_at FROM users ORDER BY created_at DESC")->fetchAll());
}

if ($method === 'GET' && $uri === '/api/admin/scans') {
    require_admin();
    json_out(get_db()->query("SELECT id,user_id,target_url,status,risk_score,ip_address,user_agent,created_at,finished_at FROM scans ORDER BY created_at DESC")->fetchAll());
}

if ($method === 'GET' && $uri === '/api/admin/stats') {
    require_admin();
    $pdo = get_db();
    json_out([
        'total_users'   => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'total_scans'   => (int)$pdo->query("SELECT COUNT(*) FROM scans")->fetchColumn(),
        'scans_today'   => (int)$pdo->query("SELECT COUNT(*) FROM scans WHERE DATE(created_at)=CURDATE()")->fetchColumn(),
        'banned_users'  => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE is_banned=1")->fetchColumn(),
        'high_risk'     => (int)$pdo->query("SELECT COUNT(*) FROM scans WHERE risk_score>=60")->fetchColumn(),
        'pro_users'     => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE plan='pro'")->fetchColumn(),
        'revenue_count' => (int)$pdo->query("SELECT COUNT(*) FROM payments WHERE status='completed'")->fetchColumn(),
        'recent_scans'  => $pdo->query("SELECT s.id,s.target_url,s.risk_score,s.status,s.ip_address,s.user_agent,s.created_at,u.username FROM scans s JOIN users u ON u.id=s.user_id ORDER BY s.created_at DESC LIMIT 10")->fetchAll(),
    ]);
}

if ($method === 'GET' && $uri === '/api/admin/audit') {
    require_admin();
    json_out(get_db()->query("SELECT a.*,u.username FROM audit_log a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 300")->fetchAll());
}

if ($method === 'GET' && $uri === '/api/admin/payments') {
    require_admin();
    json_out(get_db()->query("SELECT p.*,u.username,u.email FROM payments p JOIN users u ON u.id=p.user_id ORDER BY p.created_at DESC")->fetchAll());
}

if ($method === 'POST' && preg_match('#^/api/admin/users/(\d+)/ban$#', $uri, $m)) {
    require_admin();
    $pdo  = get_db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?"); $stmt->execute([(int)$m[1]]);
    $u    = $stmt->fetch();
    if (!$u) json_error('Utilisateur introuvable', 404);
    if ($u['is_admin']) json_error('Impossible de bannir un administrateur', 403);
    $new = $u['is_banned'] ? 0 : 1;
    $pdo->prepare("UPDATE users SET is_banned=? WHERE id=?")->execute([$new, $m[1]]);
    audit('admin_ban', "User #{$m[1]} {$u['username']} " . ($new ? 'BANNED' : 'UNBANNED'));
    json_out(['banned' => (bool)$new, 'username' => $u['username']]);
}

if ($method === 'DELETE' && preg_match('#^/api/admin/scans/(\d+)$#', $uri, $m)) {
    require_admin();
    get_db()->prepare("DELETE FROM scans WHERE id=?")->execute([(int)$m[1]]);
    audit('admin_delete_scan', "Scan #{$m[1]} deleted");
    json_out(['ok' => true]);
}

// Offers CRUD
if ($method === 'GET' && $uri === '/api/admin/offers') {
    require_admin();
    json_out(get_db()->query("SELECT * FROM offers ORDER BY id ASC")->fetchAll());
}

if ($method === 'POST' && $uri === '/api/admin/offers') {
    require_admin();
    $b = json_decode(file_get_contents('php://input'), true) ?? [];
    $title = trim($b['title'] ?? ''); $desc = trim($b['description'] ?? '');
    if (!$title || !$desc) json_error('Titre et description requis');
    $pdo = get_db();
    $pdo->prepare("INSERT INTO offers (title,description,badge,price) VALUES (?,?,?,?)")
        ->execute([$title, $desc, $b['badge'] ?? null, $b['price'] ?? null]);
    $id = $pdo->lastInsertId();
    audit('admin_create_offer', "Offer created: $title");
    $o = $pdo->prepare("SELECT * FROM offers WHERE id=?"); $o->execute([$id]); json_out($o->fetch(), 201);
}

if ($method === 'PUT' && preg_match('#^/api/admin/offers/(\d+)$#', $uri, $m)) {
    require_admin();
    $b = json_decode(file_get_contents('php://input'), true) ?? [];
    $pdo = get_db();
    $pdo->prepare("UPDATE offers SET title=?,description=?,badge=?,price=?,active=? WHERE id=?")
        ->execute([trim($b['title']??''), trim($b['description']??''), $b['badge']??null, $b['price']??null, isset($b['active'])?(int)$b['active']:1, $m[1]]);
    audit('admin_edit_offer', "Offer #{$m[1]} updated");
    json_out(['ok' => true]);
}

if ($method === 'DELETE' && preg_match('#^/api/admin/offers/(\d+)$#', $uri, $m)) {
    require_admin();
    get_db()->prepare("DELETE FROM offers WHERE id=?")->execute([(int)$m[1]]);
    audit('admin_delete_offer', "Offer #{$m[1]} deleted");
    json_out(['ok' => true]);
}

// ── SCAN FINISH: save simulated results from JS ────────────────────
if ($method === 'POST' && $uri === '/api/scan/finish') {
    $u = require_auth();
    $b = json_decode(file_get_contents('php://input'), true) ?? [];
    $scan_id = (int)($b['scan_id'] ?? 0);
    $risk    = min(100, max(0, (int)($b['risk_score'] ?? 0)));
    $results = $b['results'] ?? [];
    if (!$scan_id) json_error('scan_id requis');
    $pdo = get_db();
    $s = $pdo->prepare("SELECT id FROM scans WHERE id=? AND user_id=?");
    $s->execute([$scan_id, $u['id']]);
    if (!$s->fetch()) json_error('Scan introuvable', 404);
    $pdo->prepare("UPDATE scans SET status='done', results=?, risk_score=?, finished_at=NOW() WHERE id=?")
        ->execute([json_encode($results, JSON_UNESCAPED_UNICODE), $risk, $scan_id]);
    audit('scan_finish', "Scan #$scan_id completed, risk=$risk");
    json_out(['ok' => true, 'risk_score' => $risk]);
}

json_error('Route introuvable', 404);
