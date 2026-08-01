<?php
require_once __DIR__ . '/../../middleware/core.php';

$action = $_GET['action'] ?? '';

match($action) {
    'initiate'          => initiatePayment(),
    'verify'            => verifyPayment(),
    'moncash-webhook'   => moncashWebhook(),
    'natcash-webhook'   => natcashWebhook(),
    'stripe-webhook'    => stripeWebhook(),
    default             => error('Route pa jwenn', 404)
};

// ── INITIATE PAYMENT ──
function initiatePayment(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Metòd pa pèmèt', 405);
    $auth = requireAuth();
    $data = getInput();

    $orderId = (int)($data['order_id'] ?? 0);
    if (!$orderId) error('Order ID obligatwa');

    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $auth['user_id']]);
    $order = $stmt->fetch();
    if (!$order) error('Kòmand pa jwenn', 404);
    if ($order['payment_status'] === 'paid') error('Kòmand sa a deja peye');

    $result = match($order['payment_method']) {
        'moncash' => initiateMoncash($order),
        'natcash' => initiateNatcash($order),
        'card'    => initiateStripe($order, $data['payment_method_id'] ?? ''),
        default   => null
    };

    if (!$result) error('Erè lè ap inisye peman', 500);
    success('Peman inisye', $result);
}

// ── MONCASH ──
function initiateMoncash(array $order): array {
    // MonCash OAuth + payment init
    $credentials = base64_encode(MONCASH_CLIENT_ID . ':' . MONCASH_SECRET);

    // Step 1: Get access token
    $ch = curl_init(MONCASH_BASE_URL . '/Api/v1/CreatePayment');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'amount'   => $order['price_htg'],
            'orderId'  => $order['order_number'],
        ]),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode !== 200 || !isset($result['payment_token'])) {
        logAction(null, 'moncash_init_failed', ['order_id' => $order['id'], 'response' => $result]);
        error('MonCash pa disponib kounye a — eseye pita', 503);
    }

    $token = $result['payment_token']['token'];
    $redirectUrl = MONCASH_BASE_URL . '/Payment/Redirect?token=' . $token;

    // Save provider ref
    $db = getDB();
    $db->prepare("UPDATE payments SET provider_ref = ?, raw_response = ? WHERE order_id = ?")
       ->execute([$token, json_encode($result), $order['id']]);

    return [
        'provider'     => 'moncash',
        'redirect_url' => $redirectUrl,
        'token'        => $token,
    ];
}

function initiateMoncashWebhookVerify(string $token, string $orderNumber): array {
    $credentials = base64_encode(MONCASH_CLIENT_ID . ':' . MONCASH_SECRET);
    $ch = curl_init(MONCASH_BASE_URL . '/Api/v1/RetrieveTransactionPayment');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $credentials, 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode(['transactionId' => $token]),
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $response ?? [];
}

// ── NATCASH ──
function initiateNatcash(array $order): array {
    // NatCash API — update endpoint when you receive real API docs
    $ch = curl_init(NATCASH_BASE_URL . '/payment/initiate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'X-API-Key: ' . NATCASH_API_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'merchant_id' => NATCASH_MERCHANT_ID,
            'amount'      => $order['price_htg'],
            'reference'   => $order['order_number'],
            'callback'    => API_URL . '/payments/?action=natcash-webhook',
        ]),
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    // Adjust based on real NatCash API response shape
    $db = getDB();
    $db->prepare("UPDATE payments SET provider_ref = ?, raw_response = ? WHERE order_id = ?")
       ->execute([$result['reference'] ?? null, json_encode($result), $order['id']]);

    return ['provider' => 'natcash', 'data' => $result];
}

// ── STRIPE ──
function initiateStripe(array $order, string $paymentMethodId): array {
    if (!$paymentMethodId) error('Payment method ID obligatwa pou kàt');

    // Using Stripe API directly (no SDK needed)
    $ch = curl_init('https://api.stripe.com/v1/payment_intents');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ':',
        CURLOPT_POSTFIELDS     => http_build_query([
            'amount'               => (int)round($order['price_usd'] * 100), // cents
            'currency'             => 'usd',
            'payment_method'       => $paymentMethodId,
            'confirm'              => 'true',
            'return_url'           => APP_URL . '/pages/checkout-return.html',
            'metadata[order_id]'   => $order['id'],
            'metadata[order_num]'  => $order['order_number'],
        ]),
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode !== 200) {
        logAction(null, 'stripe_init_failed', ['order_id' => $order['id'], 'error' => $result['error'] ?? null]);
        error($result['error']['message'] ?? 'Erè Stripe', 400);
    }

    $db = getDB();
    $db->prepare("UPDATE payments SET provider_ref = ?, raw_response = ? WHERE order_id = ?")
       ->execute([$result['id'], json_encode($result), $order['id']]);

    return [
        'provider'         => 'stripe',
        'client_secret'    => $result['client_secret'],
        'status'           => $result['status'],
        'payment_intent_id'=> $result['id'],
    ];
}

// ── VERIFY PAYMENT (polling fallback) ──
function verifyPayment(): void {
    $auth    = requireAuth();
    $orderId = (int)($_GET['order_id'] ?? 0);
    if (!$orderId) error('Order ID obligatwa');

    $db   = getDB();
    $stmt = $db->prepare("SELECT o.*, p.provider_ref, p.status AS pay_status FROM orders o JOIN payments p ON p.order_id = o.id WHERE o.id = ? AND o.user_id = ?");
    $stmt->execute([$orderId, $auth['user_id']]);
    $order = $stmt->fetch();
    if (!$order) error('Kòmand pa jwenn', 404);

    success('OK', [
        'payment_status'  => $order['payment_status'],
        'delivery_status' => $order['delivery_status'],
    ]);
}

// ── MONCASH WEBHOOK ──
function moncashWebhook(): void {
    $data = getInput();
    $transactionId = $data['transactionId'] ?? ($_GET['transactionId'] ?? '');
    $orderNumber   = $data['orderId'] ?? ($_GET['orderId'] ?? '');

    if (!$transactionId || !$orderNumber) {
        http_response_code(400); exit;
    }

    // Verify with MonCash
    $verification = initiateMoncashWebhookVerify($transactionId, $orderNumber);

    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
    if (!$order) { http_response_code(404); exit; }

    $payStatus = strtolower($verification['payment']['message'] ?? '');
    if ($payStatus === 'successful' || $payStatus === 'success') {
        confirmPayment($order['id'], $transactionId, json_encode($verification));
    } elseif ($payStatus === 'failed') {
        $db->prepare("UPDATE orders SET payment_status='failed' WHERE id=?")->execute([$order['id']]);
        $db->prepare("UPDATE payments SET status='failed' WHERE order_id=?")->execute([$order['id']]);
    }

    http_response_code(200);
    echo json_encode(['received' => true]);
}

// ── NATCASH WEBHOOK ──
function natcashWebhook(): void {
    $data   = getInput();
    $ref    = $data['reference'] ?? '';
    $status = strtolower($data['status'] ?? '');

    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$ref]);
    $order = $stmt->fetch();
    if (!$order) { http_response_code(404); exit; }

    if ($status === 'success' || $status === 'completed') {
        confirmPayment($order['id'], $data['transaction_id'] ?? $ref, json_encode($data));
    }

    http_response_code(200);
    echo json_encode(['received' => true]);
}

// ── STRIPE WEBHOOK ──
function stripeWebhook(): void {
    $payload   = file_get_contents('php://input');
    $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    // Verify Stripe signature
    try {
        $parts = explode(',', $sigHeader);
        $ts = null; $sig = null;
        foreach ($parts as $part) {
            [$k, $v] = explode('=', $part, 2);
            if ($k === 't') $ts = $v;
            if ($k === 'v1') $sig = $v;
        }
        $expected = hash_hmac('sha256', $ts . '.' . $payload, STRIPE_WEBHOOK_SECRET);
        if (!hash_equals($expected, $sig)) { http_response_code(400); exit('Signature invalid'); }
    } catch (Exception $e) { http_response_code(400); exit; }

    $event = json_decode($payload, true);

    if ($event['type'] === 'payment_intent.succeeded') {
        $pi      = $event['data']['object'];
        $orderId = (int)($pi['metadata']['order_id'] ?? 0);
        if ($orderId) {
            $db   = getDB();
            $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            if ($order) confirmPayment($order['id'], $pi['id'], json_encode($pi));
        }
    }

    http_response_code(200);
    echo json_encode(['received' => true]);
}

// ── SHARED: CONFIRM PAYMENT & TRIGGER DELIVERY ──
function confirmPayment(int $orderId, string $providerRef, string $rawResponse): void {
    $db = getDB();

    // Idempotency check
    $stmt = $db->prepare("SELECT payment_status FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if ($order['payment_status'] === 'paid') return;

    $db->prepare("UPDATE orders SET payment_status='paid', updated_at=NOW() WHERE id=?")->execute([$orderId]);
    $db->prepare("UPDATE payments SET status='success', provider_ref=?, raw_response=?, updated_at=NOW() WHERE order_id=?")
       ->execute([$providerRef, $rawResponse, $orderId]);

    // Auto-deliver code
    autoDeliverCode($orderId);
}

function autoDeliverCode(int $orderId): void {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT o.*, p.name AS product_name, pkg.label AS package_label, u.email, u.first_name
        FROM orders o
        JOIN packages pkg ON pkg.id = o.package_id
        JOIN products p ON p.id = pkg.product_id
        JOIN users u ON u.id = o.user_id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order || $order['delivery_status'] === 'delivered') return;

    $db->beginTransaction();
    try {
        $codeStmt = $db->prepare("SELECT id, code FROM code_inventory WHERE package_id = ? AND status = 'available' LIMIT 1 FOR UPDATE");
        $codeStmt->execute([$order['package_id']]);
        $inv = $codeStmt->fetch();

        if (!$inv) {
            $db->rollBack();
            // Alert admin — out of stock
            logAction(null, 'delivery_failed_no_stock', ['order_id' => $orderId]);
            return;
        }

        $db->prepare("UPDATE code_inventory SET status='sold', order_id=?, sold_at=NOW() WHERE id=?")->execute([$orderId, $inv['id']]);
        $db->prepare("INSERT INTO order_codes (order_id, inventory_id, code) VALUES (?,?,?)")->execute([$orderId, $inv['id'], $inv['code']]);
        $db->prepare("UPDATE orders SET delivery_status='delivered' WHERE id=?")->execute([$orderId]);

        // Points
        if ($order['points_earned'] > 0) {
            $db->prepare("UPDATE users SET points = points + ? WHERE id=?")->execute([$order['points_earned'], $order['user_id']]);
            $db->prepare("INSERT INTO points_transactions (user_id, type, points, description, order_id) VALUES (?,?,?,?,?)")
               ->execute([$order['user_id'], 'earn', $order['points_earned'], "Pwen #{$order['order_number']}", $orderId]);
        }

        // Referral points
        $refStmt = $db->prepare("SELECT * FROM referrals WHERE referred_id = ? AND status = 'pending'");
        $refStmt->execute([$order['user_id']]);
        $referral = $refStmt->fetch();
        if ($referral) {
            $db->prepare("UPDATE users SET points = points + ? WHERE id=?")->execute([POINTS_PER_REFERRAL, $referral['referrer_id']]);
            $db->prepare("UPDATE referrals SET status='completed', order_id=?, points_awarded=? WHERE id=?")
               ->execute([$orderId, POINTS_PER_REFERRAL, $referral['id']]);
        }

        $db->commit();

        require_once __DIR__ . '/../../utils/email.php';
        emailCodeDelivery($order['email'], $order['first_name'], $order, $inv['code']);
        logAction($order['user_id'], 'code_auto_delivered', ['order_id' => $orderId]);

    } catch (Exception $e) {
        $db->rollBack();
        logAction(null, 'auto_delivery_error', ['order_id' => $orderId, 'error' => $e->getMessage()]);
    }
}
