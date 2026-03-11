<?php
/**
 * Cart actions handler.
 * Supports: add, remove, clear, checkout
 * Cart is stored in $_SESSION['cart'] as:
 *   [ product_id => ['qty' => int, 'name' => string, 'price' => float, 'kcal' => int, 'image' => string, 'category_id' => int] ]
 */
include("includes/connection.php");

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ─── Add product to cart ──────────────────────────────
    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty       = max(1, (int)($_POST['qty'] ?? 1));
        $cat       = (int)($_POST['cat'] ?? 1);

        if ($productId > 0 && $conn instanceof PDO) {
            $stmt = $conn->prepare("
                SELECT p.product_id, p.category_id, p.NAME, p.price, p.kcal,
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
                $key = $product['product_id'];
                $imagePath = getImagePath((int)$product['category_id'], $product['filename'] ?? '', $categoryFolders);

                if (isset($_SESSION['cart'][$key])) {
                    $_SESSION['cart'][$key]['qty'] += $qty;
                } else {
                    $_SESSION['cart'][$key] = [
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

        $cat = (int)($_POST['cat'] ?? 1);
        header("Location: kies-orders.php?cat=" . $cat);
        exit;

    // ─── Remove one product entirely ──────────────────────
    case 'remove':
        $productId = (int)($_POST['product_id'] ?? 0);
        unset($_SESSION['cart'][$productId]);
        header("Location: cart.php");
        exit;

    // ─── Update quantity (+1 / -1) ────────────────────────
    case 'increase':
        $productId = (int)($_POST['product_id'] ?? 0);
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['qty']++;
        }
        header("Location: cart.php");
        exit;

    case 'decrease':
        $productId = (int)($_POST['product_id'] ?? 0);
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['qty']--;
            if ($_SESSION['cart'][$productId]['qty'] <= 0) {
                unset($_SESSION['cart'][$productId]);
            }
        }
        header("Location: cart.php");
        exit;

    // ─── Clear entire cart ────────────────────────────────
    case 'clear':
        $_SESSION['cart'] = [];
        header("Location: kies-order-begin.php");
        exit;

    // ─── Checkout: create order in DB ─────────────────────
    case 'checkout':
        if (empty($_SESSION['cart']) || !($conn instanceof PDO)) {
            header("Location: cart.php");
            exit;
        }

        // Calculate total
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['qty'];
        }

        // Determine next pickup number (1–99 cycling)
        $lastPickup = $conn->query("SELECT MAX(pickup_number) FROM orders")->fetchColumn();
        $pickupNumber = ($lastPickup !== null && $lastPickup < 99) ? $lastPickup + 1 : 1;

        // Insert order (status 1 = new/pending)
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

        // Insert order lines
        $stmtLine = $conn->prepare("
            INSERT INTO order_product (order_id, product_id, price)
            VALUES (:order_id, :product_id, :price)
        ");
        foreach ($_SESSION['cart'] as $productId => $item) {
            // Insert one row per unique product; store unit price × qty
            $stmtLine->execute([
                ':order_id'   => $orderId,
                ':product_id' => $productId,
                ':price'      => $item['price'] * $item['qty'],
            ]);
        }

        // Save order info in session (including cart items for receipt), then clear cart
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
