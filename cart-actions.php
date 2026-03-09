<?php
ob_start();

session_start();
include("includes/connection.php");

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$isAjax = isset($_POST['ajax']) || isset($_GET['ajax']);

function cartItemCount($cart)
{
    $count = 0;
    foreach ($cart as $item) {
        $count += (int)($item['qty'] ?? 1);
    }
    return $count;
}

function ajaxResponse($success = true, $extra = [])
{
    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'itemCount' => cartItemCount($_SESSION['cart'] ?? [])
    ], $extra));
    exit;
}

switch ($action) {

    case 'count':
        ajaxResponse(true);
        break;

    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));

        if ($productId > 0 && $conn instanceof PDO) {
            $stmt = $conn->prepare("
                SELECT 
                    p.product_id, 
                    p.category_id, 
                    p.NAME, 
                    p.price, 
                    p.kcal,
                    i.filename,
                    i.filename_transparent,
                    c.NAME AS category_name
                FROM products p
                LEFT JOIN images i ON p.image_id = i.image_id
                LEFT JOIN categories c ON p.category_id = c.category_id
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
                    array_unshift($_SESSION['cart'], [
                        'type' => 'product',
                        'product_id' => (int)$product['product_id'],
                        'name' => $product['NAME'],
                        'price' => (float)$product['price'],
                        'kcal' => (int)$product['kcal'],
                        'image' => $imagePath,
                        'filename_transparent' => $product['filename_transparent'] ?? '',
                        'category_name' => $product['category_name'] ?? '',
                        'category_id' => (int)$product['category_id'],
                        'qty' => $qty,
                    ]);
                }
            }
        }

        if ($isAjax) {
            ajaxResponse(true);
        }

        header("Location: cart.php");
        exit;

    case 'remove':
        $cartIndex = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? -1);

        if (isset($_SESSION['cart'][$cartIndex])) {
            unset($_SESSION['cart'][$cartIndex]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }

        if ($isAjax) {
            ajaxResponse(true);
        }

        header("Location: cart.php");
        exit;

    case 'increase':
        $cartIndex = (int)($_POST['product_id'] ?? 0);

        if (isset($_SESSION['cart'][$cartIndex])) {
            $_SESSION['cart'][$cartIndex]['qty']++;
        }

        if ($isAjax) {
            ajaxResponse(true);
        }

        header("Location: cart.php");
        exit;

    case 'decrease':
        $cartIndex = (int)($_POST['product_id'] ?? 0);

        if (isset($_SESSION['cart'][$cartIndex])) {
            $_SESSION['cart'][$cartIndex]['qty']--;

            if ($_SESSION['cart'][$cartIndex]['qty'] <= 0) {
                unset($_SESSION['cart'][$cartIndex]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            }
        }

        if ($isAjax) {
            ajaxResponse(true);
        }

        header("Location: cart.php");
        exit;

    case 'clear':
        $_SESSION['cart'] = [];

        if ($isAjax) {
            ajaxResponse(true);
        }

        header("Location: kies-order-begin.php");
        exit;

    case 'checkout':
        if (empty($_SESSION['cart']) || !($conn instanceof PDO)) {
            if ($isAjax) {
                ajaxResponse(false);
            }
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

        $_SESSION['last_order_id'] = $orderId;
        $_SESSION['last_pickup_number'] = $pickupNumber;
        $_SESSION['last_order_total'] = $total;
        $_SESSION['last_order_items'] = $_SESSION['cart'];

        $_SESSION['cart'] = [];

        if ($isAjax) {
            ajaxResponse(true);
        }

        header("Location: betaal.php");
        exit;

    default:
        if ($isAjax) {
            ajaxResponse(false);
        }
        header("Location: kies-orders.php");
        exit;
}
