<?php
require_once __DIR__ . '/../../middleware/core.php';
require_once __DIR__ . '/../../utils/email.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

match($action) {
    'create'   => createOrder(),
    'list'     => listOrders(),
    'detail'   => orderDetail(),
    'deliver'  => deliverCode(),
    default    => error('Route pa jwenn', 404)
};

// ── CREATE ORDER ──
function createOrder(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Metòd pa pèmèt', 405);
    $auth = requireAuth();
    $data = getInput();

    $packageId     = (int)($data['package_id'] ?? 0);
    $gameUid       = sanitize($data['game_uid'] ?? '');
    $paymentMethod = sanitize($data['payment_method'] ?? '');
    $couponCode    = strtoupper(trim($data['coupon_code'] ?? ''));
    $useWallet     = (bool)($data['use_wallet'] ?? false);

    if (!$packageId) error('Package obligatwa');
    if (!$gameUid)   error('ID jwèt obligatwa');
    if (!in_array($paymentMethod, ['moncash','natcash','card'])) error('Metòd peman pa valid');

    $db = getDB();

    // Get package + product
    $stmt = $db->prepare("
        SELECT pkg.*, p.name AS product_name, p.slug, p.id AS product_id
        FROM packages pkg JOIN products p ON p.id = pkg.product_id
        WHERE pkg.id = ? AND pkg.is_active = 1 AND p.is_active = 1
    ");
    $stmt->execute([$packageId]);
    $pkg = $stmt->fetch();
    if (!$pkg) error('Package pa jwenn');

    // Check stock
    $stockStmt = $db->prepare("SELECT COUNT(*) AS cnt FROM code_inventory WHERE package_id = ? AND status = 'available'");
    $stockStmt->execute([$packageId]);
    if ($stockStmt->fetch()['cnt'] < 1) error('Stock fini pou pwodwi sa a — eseye pita');

    // Coupon
    $discount = 0;
    $couponId = null;
    if ($couponCode) {
        $cStmt = $db->prepare("
            SELECT * FROM coupons 
            WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())
            AND (max_uses IS NULL OR used_count < max_uses)
            AND (product_id IS NULL OR product_id = ?)
        ");
        $cStmt->execute([$couponCode, $pkg['product_id']]);
        $coupon = $cStmt->fetch();
        if ($coupon) {
            $couponId = $coupon['id'];
            $priceUsd = $pkg['price_usd'];
            if ($coupon['type'] === 'percent') $discount = round($priceUsd * $coupon['value'] / 100, 2);
            else $discount = min($coupon['value'], $priceUsd);
        }
    }

    // Wallet deduction
    $walletUsed = 0;
    if ($useWallet) {
        $uStmt = $db->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $uStmt->execute([$auth['user_id']]);
        $walletBal = (float)$uStmt->fetch()['wallet_balance'];
        $remaining = $pkg['price_usd'] - $discount;
        $walletUsed = min($walletBal, $remaining);
    }

    $finalPriceUsd = round($pkg['price_usd'] - $discount - $walletUsed, 2);
    $finalPriceHtg = round($pkg['price_htg'] - ($discount * 130) - ($walletUsed * 130), 2);

    // Points earned
    $pointsEarned = (int)floor($finalPriceUsd * POINTS_PER_DOLLAR);

    // Create order
    $orderNumber = generateOrderNumber();
    $stmt = $db->prepare("
        INSERT INTO orders 
        (order_number, user_id, package_id, game_uid, price_usd, price_htg,
         payment_method, points_earned, wallet_used, coupon_code, discount_amount)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([
        $orderNumber, $auth['user_id'], $packageId, $gameUid,
        $finalPriceUsd, $finalPriceHtg, $paymentMethod,
        $pointsEarned, $walletUsed, $couponCode ?: null, $discount
    ]);
    $orderId = (int)$db->lastInsertId();

    // Deduct wallet
    if ($walletUsed > 0) {
        $db->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?")
           ->execute([$walletUsed, $auth['user_id']]);
        $db->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description, order_id) VALUES (?,?,?,?,?)")
           ->execute([$auth['user_id'], 'debit', $walletUsed, "Peman kòmand #{$orderNumber}", $orderId]);
    }

    // Update coupon usage
    if ($couponId) {
        $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$couponId]);
    }

    // Create payment record
    $db->prepare("INSERT INTO payments (order_id, provider, amount_htg) VALUES (?,?,?)")
       ->execute([$orderId, $paymentMethod === 'card' ? 'stripe' : $paymentMethod, $finalPriceHtg]);

    logAction($auth['user_id'], 'order_created', ['order_id' => $orderId, 'order_number' => $orderNumber]);

    success('Kòmand kreye ak siksè!', [
        'order_id'     => $orderId,
        'order_number' => $orderNumber,
        'price_usd'    => $finalPriceUsd,
        'price_htg'    => $finalPriceHtg,
        'payment_method'=> $paymentMethod,
    ], 201);
}

// ── DELIVER CODE (called after payment confirmed) ──
function deliverCode(): void {
    $auth    = requireAuth();
    $orderId = (int)($_GET['order_id'] ?? 0);
    if (!$orderId) error('Order ID obligatwa');

    $db   = getDB();
    $stmt = $db->prepare("
        SELECT o.*, p.name AS product_name, pkg.label AS package_label
        FROM orders o
        JOIN packages pkg ON pkg.id = o.package_id
        JOIN products p ON p.id = pkg.product_id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $auth['user_id']]);
    $order = $stmt->fetch();

    if (!$order) error('Kòmand pa jwenn', 404);
    if ($order['payment_status'] !== 'paid') error('Peman poko konfime');
    if ($order['delivery_status'] === 'delivered') {
        // Already delivered — return existing code
        $cStmt = $db->prepare("SELECT code FROM order_codes WHERE order_id = ?");
        $cStmt->execute([$orderId]);
        $row = $cStmt->fetch();
        success('Kòd deja livre', ['code' => $row['code'] ?? null]);
        return;
    }

    // Atomically grab one available code
    $db->beginTransaction();
    try {
        $codeStmt = $db->prepare("
            SELECT id, code FROM code_inventory
            WHERE package_id = ? AND status = 'available'
            LIMIT 1 FOR UPDATE
        ");
        $codeStmt->execute([$order['package_id']]);
        $inv = $codeStmt->fetch();

        if (!$inv) {
            $db->rollBack();
            error('Stock fini — kontakte sipò', 503);
        }

        // Mark code as sold
        $db->prepare("UPDATE code_inventory SET status='sold', order_id=?, sold_at=NOW() WHERE id=?")
           ->execute([$orderId, $inv['id']]);

        // Save to order_codes
        $db->prepare("INSERT INTO order_codes (order_id, inventory_id, code) VALUES (?,?,?)")
           ->execute([$orderId, $inv['id'], $inv['code']]);

        // Update order delivery status
        $db->prepare("UPDATE orders SET delivery_status='delivered' WHERE id=?")->execute([$orderId]);

        // Award points to user
        if ($order['points_earned'] > 0) {
            $db->prepare("UPDATE users SET points = points + ? WHERE id=?")->execute([$order['points_earned'], $auth['user_id']]);
            $db->prepare("INSERT INTO points_transactions (user_id, type, points, description, order_id) VALUES (?,?,?,?,?)")
               ->execute([$auth['user_id'], 'earn', $order['points_earned'], "Pwen pou kòmand #{$order['order_number']}", $orderId]);
        }

        // Award referral points to referrer
        $refStmt = $db->prepare("SELECT * FROM referrals WHERE referred_id = ? AND status = 'pending'");
        $refStmt->execute([$auth['user_id']]);
        $referral = $refStmt->fetch();
        if ($referral) {
            $db->prepare("UPDATE users SET points = points + ? WHERE id=?")->execute([POINTS_PER_REFERRAL, $referral['referrer_id']]);
            $db->prepare("INSERT INTO points_transactions (user_id, type, points, description) VALUES (?,?,?,?)")
               ->execute([$referral['referrer_id'], 'earn', POINTS_PER_REFERRAL, "Referans — kòmand #{$order['order_number']}"]);
            $db->prepare("UPDATE referrals SET status='completed', order_id=?, points_awarded=? WHERE id=?")
               ->execute([$orderId, POINTS_PER_REFERRAL, $referral['id']]);
        }

        $db->commit();

        // Send delivery email
        $uStmt = $db->prepare("SELECT email, first_name FROM users WHERE id=?");
        $uStmt->execute([$auth['user_id']]);
        $user = $uStmt->fetch();
        emailCodeDelivery($user['email'], $user['first_name'], $order, $inv['code']);

        logAction($auth['user_id'], 'code_delivered', ['order_id' => $orderId]);

        success('Kòd livre ak siksè!', [
            'code'          => $inv['code'],
            'order_number'  => $order['order_number'],
            'product_name'  => $order['product_name'],
            'package_label' => $order['package_label'],
            'points_earned' => $order['points_earned'],
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        error('Erè sistèm — eseye ankò', 500);
    }
}

// ── LIST ORDERS ──
function listOrders(): void {
    $auth = requireAuth();
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT o.*, p.name AS product_name, pkg.label AS package_label, pkg.amount, pkg.unit,
               oc.code AS delivered_code
        FROM orders o
        JOIN packages pkg ON pkg.id = o.package_id
        JOIN products p ON p.id = pkg.product_id
        LEFT JOIN order_codes oc ON oc.order_id = o.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$auth['user_id']]);
    success('OK', ['orders' => $stmt->fetchAll()]);
}

// ── ORDER DETAIL ──
function orderDetail(): void {
    $auth    = requireAuth();
    $orderId = (int)($_GET['id'] ?? 0);
    $db      = getDB();
    $stmt    = $db->prepare("
        SELECT o.*, p.name AS product_name, pkg.label AS package_label,
               pkg.amount, pkg.unit, oc.code AS delivered_code
        FROM orders o
        JOIN packages pkg ON pkg.id = o.package_id
        JOIN products p ON p.id = pkg.product_id
        LEFT JOIN order_codes oc ON oc.order_id = o.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $auth['user_id']]);
    $order = $stmt->fetch();
    if (!$order) error('Kòmand pa jwenn', 404);
    success('OK', ['order' => $order]);
}
