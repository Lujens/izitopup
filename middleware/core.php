<?php
require_once __DIR__ . '/../config/config.php';

// ── CORS ──
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . APP_URL);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// ── RESPONSE HELPERS ──
function respond(bool $success, string $message, array $data = [], int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

function success(string $message, array $data = [], int $code = 200): void {
    respond(true, $message, $data, $code);
}

function error(string $message, int $code = 400, array $data = []): void {
    respond(false, $message, $data, $code);
}

// ── INPUT ──
function getInput(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

function sanitize(string $val): string {
    return trim(htmlspecialchars($val, ENT_QUOTES, 'UTF-8'));
}

// ── TOKEN ──
function generateToken(int $userId): string {
    $payload = $userId . '|' . time() . '|' . bin2hex(random_bytes(16));
    return hash_hmac('sha256', $payload, TOKEN_SECRET);
}

function saveToken(int $userId, string $token): void {
    $db = getDB();
    $expires = date('Y-m-d H:i:s', time() + TOKEN_EXPIRE);
    $stmt = $db->prepare("INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?,?,?)");
    $stmt->execute([$userId, $token, $expires]);
}

function validateToken(string $token): ?array {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT t.user_id, u.email, u.first_name, u.last_name, u.role, u.status
        FROM auth_tokens t
        JOIN users u ON u.id = t.user_id
        WHERE t.token = ? AND t.expires_at > NOW()
    ");
    $stmt->execute([$token]);
    return $stmt->fetch() ?: null;
}

function getBearerToken(): ?string {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (preg_match('/Bearer\s+(.+)/i', $auth, $m)) return $m[1];
    return null;
}

// ── AUTH MIDDLEWARE ──
function requireAuth(): array {
    $token = getBearerToken();
    if (!$token) error('Non otorize — konekte anvan', 401);
    $user = validateToken($token);
    if (!$user) error('Sesyon ekspire — konekte ankò', 401);
    if ($user['status'] === 'blocked') error('Kont ou bloke — kontakte sipò', 403);
    return $user;
}

function requireAdmin(): array {
    $user = requireAuth();
    if (!in_array($user['role'], ['admin', 'staff'])) error('Aksè refize', 403);
    return $user;
}

// ── REFERRAL CODE ──
function generateReferralCode(string $name): string {
    $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 4));
    return $base . strtoupper(bin2hex(random_bytes(3)));
}

// ── ORDER NUMBER ──
function generateOrderNumber(): string {
    return 'IZI-' . strtoupper(bin2hex(random_bytes(4)));
}

// ── LOG ──
function logAction(int $userId = null, string $action = '', array $details = []): void {
    try {
        $db = getDB();
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $db->prepare("INSERT INTO system_logs (user_id, action, details, ip_address) VALUES (?,?,?,?)");
        $stmt->execute([$userId, $action, json_encode($details), $ip]);
    } catch (Exception $e) { /* non-blocking */ }
}
