<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

function jwt_encode(array $payload): string {
    $header  = base64url_encode(json_encode(['typ'=>'JWT','alg'=>'HS256']));
    $payload['exp'] = time() + JWT_EXPIRE;
    $body    = base64url_encode(json_encode($payload));
    $sig     = base64url_encode(hash_hmac('sha256', "$header.$body", JWT_SECRET, true));
    return "$header.$body.$sig";
}

function jwt_decode(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$h,$b,$s] = $parts;
    $expected = base64url_encode(hash_hmac('sha256', "$h.$b", JWT_SECRET, true));
    if (!hash_equals($expected, $s)) return null;
    $payload = json_decode(base64url_decode($b), true);
    if (!$payload || (isset($payload['exp']) && $payload['exp'] < time())) return null;
    return $payload;
}

function base64url_encode(string $d): string { return rtrim(strtr(base64_encode($d),'+/','-_'),'='); }
function base64url_decode(string $d): string { return base64_decode(strtr($d,'-_','+/')); }
function hash_password(string $p): string    { return password_hash($p, PASSWORD_BCRYPT); }
function verify_password(string $p, string $h): bool { return password_verify($p, $h); }

function _resolve_token(): ?string {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (str_starts_with($auth, 'Bearer ')) return substr($auth, 7);
    if (!empty($_GET['token'])) return $_GET['token'];
    if (!empty($_SESSION['token'])) return $_SESSION['token'];
    return null;
}

function get_current_user(): ?array {
    $token = _resolve_token();
    if (!$token) return null;
    $payload = jwt_decode($token);
    if (!$payload || !isset($payload['sub'])) return null;
    $stmt = get_db()->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$payload['sub']]);
    return $stmt->fetch() ?: null;
}

function get_current_user_silent(): ?array { return get_current_user(); }

function require_auth(): array {
    $u = get_current_user();
    if (!$u) json_error('Unauthorized — please log in', 401);
    if ($u['is_banned']) json_error('Account suspended — contact support@ova9.io', 403);
    return $u;
}

function require_admin(): array {
    $u = require_auth();
    if (!$u['is_admin']) json_error('Admin access required', 403);
    return $u;
}

function json_out(mixed $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $detail, int $code = 400): never {
    json_out(['detail' => $detail], $code);
}
