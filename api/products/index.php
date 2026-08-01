<?php
require_once __DIR__ . '/../../middleware/core.php';

$action = $_GET['action'] ?? 'list';

match($action) {
    'list'    => listProducts(),
    'detail'  => productDetail(),
    default   => error('Route pa jwenn', 404)
};

function listProducts(): void {
    $db   = getDB();
    $stmt = $db->query("
        SELECT p.*, 
               COUNT(c.id) AS stock_count,
               MIN(pkg.price_htg) AS min_price_htg,
               MIN(pkg.price_usd) AS min_price_usd
        FROM products p
        LEFT JOIN packages pkg ON pkg.product_id = p.id AND pkg.is_active = 1
        LEFT JOIN code_inventory c ON c.package_id = pkg.id AND c.status = 'available'
        WHERE p.is_active = 1
        GROUP BY p.id
        ORDER BY p.sort_order ASC
    ");
    $products = $stmt->fetchAll();

    foreach ($products as &$p) {
        $pkgStmt = $db->prepare("
            SELECT pkg.*, 
                   COUNT(c.id) AS stock_count
            FROM packages pkg
            LEFT JOIN code_inventory c ON c.package_id = pkg.id AND c.status = 'available'
            WHERE pkg.product_id = ? AND pkg.is_active = 1
            GROUP BY pkg.id
            ORDER BY pkg.sort_order ASC
        ");
        $pkgStmt->execute([$p['id']]);
        $p['packages'] = $pkgStmt->fetchAll();
    }

    success('OK', ['products' => $products]);
}

function productDetail(): void {
    $slug = sanitize($_GET['slug'] ?? '');
    if (!$slug) error('Slug obligatwa');

    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM products WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    $product = $stmt->fetch();
    if (!$product) error('Pwodwi pa jwenn', 404);

    $pkgStmt = $db->prepare("
        SELECT pkg.*, COUNT(c.id) AS stock_count
        FROM packages pkg
        LEFT JOIN code_inventory c ON c.package_id = pkg.id AND c.status = 'available'
        WHERE pkg.product_id = ? AND pkg.is_active = 1
        GROUP BY pkg.id ORDER BY pkg.sort_order
    ");
    $pkgStmt->execute([$product['id']]);
    $product['packages'] = $pkgStmt->fetchAll();

    success('OK', ['product' => $product]);
}
