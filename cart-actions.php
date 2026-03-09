<?php

/**
 * Cart actions handler.
 * Supports: add, remove, clear, checkout, increase, decrease
 */

session_start();
include("includes/connection.php");

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ─── Add normal product to cart ──────────────────────────────
    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty       = max(1, (int)($_POST['qty'] ?? 1));

        if ($productId > 0 && $conn instanceof PDO) {
            $stmt = $conn->prepare("
                SELECT 
                    p.product_id, 
                    p.category_id, 
                    p.NAME, 
                    p.price, 
                    p.kcal,
                    i.filename
                FROM products p
                LEFT JOIN images i ON p.image_id = i.image_id
                WHERE p.product_id = :id
                LIMIT 1
            ");
            $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $imagePath = getImagePath(
                    (int)$product['category_id'],
                    $product['filename'] ?? '',
                    $categoryFolders
                );

                $foundIndex = null;

                foreach ($_SESSION['cart'] ?? [] as $index => $item) {
                    if (($item['type'] ?? 'product') === 'product' && ($item['product_id'] ?? 0) == $productId) {
                        $foundIndex = $index;
                        break;
                    }
                }

                if ($foundIndex !== null) {
                    $_SESSION['cart'][$foundIndex]['qty'] += $qty;
                } else {
                    $_SESSION['cart'][] = [
                        'type'        => 'product',
                        'product_id'  => (int)$product['product_id'],
                        'name'        => $product['NAME'],
                        'price'       => (float)$product['price'],
                        'kcal'        => (int)$product['kcal'],
                        'image'       => $imagePath,
                        'category_id' => (int)$product['category_id'],
                        'qty'         => $qty,
                    ];
                }
            }
        }

        header("Location: cart.php");
        exit;

        // ─── Remove one cart item entirely ───────────────────────────
    case 'remove':
        $cartIndex = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? -1);

        if (isset($_SESSION['cart'][$cartIndex])) {
            unset($_SESSION['cart'][$cartIndex]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }

        header("Location: cart.php");
        exit;

        // ─── Increase quantity of one cart item ─────────────────────
    case 'increase':
        $cartIndex = (int)($_POST['product_id'] ?? 0);

        if (isset($_SESSION['cart'][$cartIndex])) {
            $_SESSION['cart'][$cartIndex]['qty']++;
        }

        header("Location: cart.php");
        exit;

        // ─── Decrease quantity of one cart item ─────────────────────
    case 'decrease':
        $cartIndex = (int)($_POST['product_id'] ?? 0);

        if (isset($_SESSION['cart'][$cartIndex])) {
            $_SESSION['cart'][$cartIndex]['qty']--;

            if ($_SESSION['cart'][$cartIndex]['qty'] <= 0) {
                unset($_SESSION['cart'][$cartIndex]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            }
        }

        header("Location: cart.php");
        exit;

        // ─── Clear entire cart ──────────────────────────────────────
    case 'clear':
        $_SESSION['cart'] = [];
        header("Location: kies-order-begin.php");
        exit;

        // ─── Checkout: create order in DB ───────────────────────────
    case 'checkout':
        if (empty($_SESSION['cart']) || !($conn instanceof PDO)) {
            header("Location: cart.php");
            exit;
        }

        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $qty = (int)($item['qty'] ?? 1);
            $total += ((float)$item['price']) * $qty;
        }

        $lastPickup = $conn->query("SELECT MAX(pickup_number) FROM orders")->fetchColumn();
        $pickupNumber = ($lastPickup !== null && $lastPickup < 99) ? $lastPickup + 1 : 1;

        $stmt = $conn->prepare("
            INSERT INTO orders (order_status_id, pickup_number, price_total, DATETIME)
            VALUES (:status, :pickup, :total, NOW())
        ");
        $stmt->execute([
            ':status' => 1,
            ':pickup' => $pickupNumber,
            ':total'  => $total,
        ]);

        $orderId = (int)$conn->lastInsertId();

        $stmtLine = $conn->prepare("
            INSERT INTO order_product (order_id, product_id, price)
            VALUES (:order_id, :product_id, :price)
        ");

        foreach ($_SESSION['cart'] as $item) {
            $qty = (int)($item['qty'] ?? 1);
            $linePrice = ((float)$item['price']) * $qty;

            if (($item['type'] ?? '') === 'menu') {
                $menuParts = [
                    $item['main'] ?? null,
                    $item['side'] ?? null,
                    $item['sauce'] ?? null,
                    $item['drink'] ?? null
                ];

                foreach ($menuParts as $part) {
                    if (!empty($part['product_id'])) {
                        $stmtLine->execute([
                            ':order_id'   => $orderId,
                            ':product_id' => (int)$part['product_id'],
                            ':price'      => 0
                        ]);
                    }
                }
            } else {
                if (!empty($item['product_id'])) {
                    $stmtLine->execute([
                        ':order_id'   => $orderId,
                        ':product_id' => (int)$item['product_id'],
                        ':price'      => $linePrice,
                    ]);
                }
            }
        }

        $_SESSION['last_order_id']      = $orderId;
        $_SESSION['last_pickup_number'] = $pickupNumber;
        $_SESSION['last_order_total']   = $total;
        $_SESSION['last_order_items']   = $_SESSION['cart'];

        $_SESSION['cart'] = [];

        header("Location: betaal.php");
        exit;

    default:
        header("Location: kies-orders.php");
        exit;
}
