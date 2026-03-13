<?php
include("includes/connection.php");

$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$ready = isset($_POST['ready']) ? (int)$_POST['ready'] : 0;

if (!$orderId || !$productId) {
    header("Location: order-beheer.php");
    exit;
}

// item updaten
$stmt = $conn->prepare("
    UPDATE order_product
    SET is_ready = :ready
    WHERE order_id = :order_id
      AND product_id = :product_id
");
$stmt->execute([
    ':ready' => $ready ? 1 : 0,
    ':order_id' => $orderId,
    ':product_id' => $productId
]);

// controleren of alles klaar is
$stmtCheck = $conn->prepare("
    SELECT COUNT(*)
    FROM order_product
    WHERE order_id = :order_id
      AND is_ready = 0
");
$stmtCheck->execute([':order_id' => $orderId]);
$notReadyCount = (int)$stmtCheck->fetchColumn();

// order status aanpassen
$newStatus = ($notReadyCount === 0) ? 2 : 1;

$stmtOrder = $conn->prepare("
    UPDATE orders
    SET order_status_id = :status
    WHERE order_id = :order_id
");
$stmtOrder->execute([
    ':status' => $newStatus,
    ':order_id' => $orderId
]);

header("Location: order-beheer.php?order_id=" . $orderId);
exit;
