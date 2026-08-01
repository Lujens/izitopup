<?php
require_once __DIR__ . '/../../middleware/core.php';

$action = $_GET['action'] ?? '';

match($action) {
    'stats'          => referralStats(),
    'profile'        => getProfile(),
    'update-profile' => updateProfile(),
    'wallet'         => getWallet(),
    'redeem-points'  => redeemPoints(),
    'validate-coupon'=> validateCoupon(),
    default          => error('Route pa jwenn', 404)
};

function referralStats(): void {
    $auth = requireAuth();
    $db   = getDB();

    $user = $db->prepare("SELECT referral_code, points, wallet_balance FROM users WHERE id = ?")->execute([$auth['user_id']]) ? null : null;
    $stmt = $db->prepare("SELECT referral_code, points, wallet_balance FROM users WHERE id = ?");
    $stmt->execute([$auth['user_id']]);
    $user = $stmt->fetch();

    $refStmt = $db->prepare("
        SELECT r.*, u.first_name, u.last_name, u.created_at AS joined_at
        FROM referrals r JOIN users u ON u.id = r.referred_id
        WHERE r.referrer_id = ?
        ORDER BY r.created_at DESC
    ");
    $refStmt->execute([$auth['user_id']]);
    $referrals = $refStmt->fetchAll();

    $ptStmt = $db->prepare("SELECT * FROM points_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $ptStmt->execute([$auth['user_id']]);
    $pointsHistory = $ptStmt->fetchAll();

    success('OK', [
        'referral_code'   => $user['referral_code'],
        'referral_link'   => APP_URL . '/?ref=' . $user['referral_code'],
        'total_referrals' => count($referrals),
        'completed'       => count(array_filter($referrals, fn($r) => $r['status'] === 'completed')),
        'points'          => (int)$user['points'],
        'wallet_balance'  => (float)$user['wallet_balance'],
        'referrals'       => $referrals,
        'points_history'  => $pointsHistory,
    ]);
}

function getProfile(): void {
    $auth = requireAuth();
    $db   = getDB();
    $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, referral_code, wallet_balance, points, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$auth['user_id']]);
    success('OK', ['user' => $stmt->fetch()]);
}

function updateProfile(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Metòd pa pèmèt', 405);
    $auth = requireAuth();
    $data = getInput();

    $firstName = sanitize($data['first_name'] ?? '');
    $lastName  = sanitize($data['last_name'] ?? '');
    $phone     = sanitize($data['phone'] ?? '');

    if (!$firstName || !$lastName) error('Non ak siyati obligatwa');

    $db = getDB();
    $db->prepare("UPDATE users SET first_name=?, last_name=?, phone=? WHERE id=?")
       ->execute([$firstName, $lastName, $phone, $auth['user_id']]);

    success('Pwofil aktyalize');
}

function getWallet(): void {
    $auth = requireAuth();
    $db   = getDB();
    $stmt = $db->prepare("SELECT wallet_balance, points FROM users WHERE id = ?");
    $stmt->execute([$auth['user_id']]);
    $user = $stmt->fetch();

    $txStmt = $db->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $txStmt->execute([$auth['user_id']]);

    success('OK', [
        'balance'      => (float)$user['wallet_balance'],
        'points'       => (int)$user['points'],
        'transactions' => $txStmt->fetchAll(),
    ]);
}

function redeemPoints(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Metòd pa pèmèt', 405);
    $auth   = requireAuth();
    $data   = getInput();
    $points = (int)($data['points'] ?? 0);

    if ($points < 100) error('Ou bezwen omwen 100 pwen pou konvèti');

    $db   = getDB();
    $stmt = $db->prepare("SELECT points FROM users WHERE id = ?");
    $stmt->execute([$auth['user_id']]);
    $user = $stmt->fetch();

    if ($user['points'] < $points) error('Pa gen ase pwen');

    $htgValue = round($points * POINTS_TO_HTG_RATE, 2);
    $usdValue = round($htgValue / 130, 2);

    $db->prepare("UPDATE users SET points = points - ?, wallet_balance = wallet_balance + ? WHERE id=?")
       ->execute([$points, $usdValue, $auth['user_id']]);
    $db->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description) VALUES (?,?,?,?)")
       ->execute([$auth['user_id'], 'credit', $usdValue, "Konvèsyon {$points} pwen"]);
    $db->prepare("INSERT INTO points_transactions (user_id, type, points, description) VALUES (?,?,?,?)")
       ->execute([$auth['user_id'], 'redeem', $points, "Konvèti an kredi wallet"]);

    success("Konvèsyon reyisi! {$points} pwen = \${$usdValue} kredi", [
        'points_used'    => $points,
        'wallet_credited'=> $usdValue,
    ]);
}

function validateCoupon(): void {
    $auth = requireAuth();
    $code = strtoupper(trim($_GET['code'] ?? ''));
    $productId = (int)($_GET['product_id'] ?? 0);

    if (!$code) error('Kòd obligatwa');

    $db   = getDB();
    $stmt = $db->prepare("
        SELECT * FROM coupons
        WHERE code = ? AND is_active = 1
        AND (expires_at IS NULL OR expires_at > NOW())
        AND (max_uses IS NULL OR used_count < max_uses)
        AND (product_id IS NULL OR product_id = ?)
    ");
    $stmt->execute([$code, $productId ?: null]);
    $coupon = $stmt->fetch();

    if (!$coupon) error('Kòd promo pa valid oswa ekspire');

    success('Kòd promo valid!', [
        'code'    => $coupon['code'],
        'type'    => $coupon['type'],
        'value'   => $coupon['value'],
        'min_order'=> $coupon['min_order'],
    ]);
}
