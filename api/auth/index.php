<?php
require_once __DIR__ . '/../../middleware/core.php';
require_once __DIR__ . '/../../utils/email.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

match($action) {
    'register'       => register(),
    'login'          => login(),
    'logout'         => logout(),
    'me'             => me(),
    'forgot-password'=> forgotPassword(),
    'reset-password' => resetPassword(),
    default          => error('Route pa jwenn', 404)
};

// ── REGISTER ──
function register(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Metòd pa pèmèt', 405);

    $data = getInput();
    $firstName = sanitize($data['first_name'] ?? '');
    $lastName  = sanitize($data['last_name'] ?? '');
    $email     = strtolower(trim($data['email'] ?? ''));
    $phone     = sanitize($data['phone'] ?? '');
    $password  = $data['password'] ?? '';
    $refCode   = strtoupper(trim($data['referral_code'] ?? ''));

    // Validate
    if (!$firstName || !$lastName) error('Non ak siyati obligatwa');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) error('Email pa valid');
    if (strlen($password) < 8) error('Modpas dwe gen omwen 8 karaktè');

    $db = getDB();

    // Check duplicate email
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) error('Yon kont deja egziste ak email sa a');

    // Check referral code
    $referredBy = null;
    if ($refCode) {
        $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->execute([$refCode]);
        $referrer = $stmt->fetch();
        if ($referrer) $referredBy = $referrer['id'];
    }

    // Create user
    $passwordHash  = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $myReferralCode = generateReferralCode($firstName);

    $stmt = $db->prepare("
        INSERT INTO users (first_name, last_name, email, phone, password_hash, referral_code, referred_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$firstName, $lastName, $email, $phone, $passwordHash, $myReferralCode, $referredBy]);
    $userId = (int)$db->lastInsertId();

    // Record referral relationship
    if ($referredBy) {
        $stmt = $db->prepare("INSERT INTO referrals (referrer_id, referred_id) VALUES (?, ?)");
        $stmt->execute([$referredBy, $userId]);
    }

    // Generate token
    $token = generateToken($userId);
    saveToken($userId, $token);

    // Send welcome email (non-blocking)
    emailWelcome($email, $firstName);

    logAction($userId, 'register', ['email' => $email, 'referred_by' => $referredBy]);

    success('Kont kreye ak siksè!', [
        'token'   => $token,
        'user'    => [
            'id'            => $userId,
            'first_name'    => $firstName,
            'last_name'     => $lastName,
            'email'         => $email,
            'referral_code' => $myReferralCode,
            'wallet_balance'=> 0,
            'points'        => 0,
            'role'          => 'user',
        ]
    ], 201);
}

// ── LOGIN ──
function login(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Metòd pa pèmèt', 405);

    $data     = getInput();
    $email    = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';

    if (!$email || !$password) error('Email ak modpas obligatwa');

    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        error('Email oswa modpas pa kòrèk');
    }
    if ($user['status'] === 'blocked') error('Kont ou bloke — kontakte sipò', 403);

    $token = generateToken($user['id']);
    saveToken($user['id'], $token);

    logAction($user['id'], 'login');

    success('Koneksyon reyisi!', [
        'token' => $token,
        'user'  => [
            'id'             => $user['id'],
            'first_name'     => $user['first_name'],
            'last_name'      => $user['last_name'],
            'email'          => $user['email'],
            'phone'          => $user['phone'],
            'referral_code'  => $user['referral_code'],
            'wallet_balance' => $user['wallet_balance'],
            'points'         => $user['points'],
            'role'           => $user['role'],
        ]
    ]);
}

// ── LOGOUT ──
function logout(): void {
    $token = getBearerToken();
    if ($token) {
        $db = getDB();
        $db->prepare("DELETE FROM auth_tokens WHERE token = ?")->execute([$token]);
    }
    success('Dekoneksyon reyisi');
}

// ── ME (get current user) ──
function me(): void {
    $auth = requireAuth();
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT id, first_name, last_name, email, phone, referral_code,
               wallet_balance, points, role, status, created_at
        FROM users WHERE id = ?
    ");
    $stmt->execute([$auth['user_id']]);
    $user = $stmt->fetch();
    if (!$user) error('Itilizatè pa jwenn', 404);
    success('OK', ['user' => $user]);
}

// ── FORGOT PASSWORD ──
function forgotPassword(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Metòd pa pèmèt', 405);

    $data  = getInput();
    $email = strtolower(trim($data['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) error('Email pa valid');

    $db   = getDB();
    $stmt = $db->prepare("SELECT id, first_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Always return success to prevent email enumeration
    if ($user) {
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 1800); // 30 min
        $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?,?,?)")
           ->execute([$email, $token, $expires]);
        $link = APP_URL . "/pages/reset-password.html?token=" . $token;
        emailPasswordReset($email, $user['first_name'], $link);
    }

    success('Si email sa a egziste, ou ap resevwa yon lyen reset.');
}

// ── RESET PASSWORD ──
function resetPassword(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Metòd pa pèmèt', 405);

    $data     = getInput();
    $token    = trim($data['token'] ?? '');
    $password = $data['password'] ?? '';

    if (!$token) error('Token obligatwa');
    if (strlen($password) < 8) error('Modpas dwe gen omwen 8 karaktè');

    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if (!$reset) error('Token pa valid oswa ekspire');

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?")->execute([$hash, $reset['email']]);
    $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")->execute([$token]);

    success('Modpas chanje ak siksè! Ou ka konekte kounye a.');
}
