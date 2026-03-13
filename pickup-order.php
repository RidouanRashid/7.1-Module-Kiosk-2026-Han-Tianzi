<?php
include("includes/connection.php");

$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);

if (!$orderId) {
    header("Location: order-beheer.php");
    exit;
}

$stmt = $conn->prepare("
    UPDATE orders
    SET order_status_id = 3
    WHERE order_id = :order_id
");
$stmt->execute([
    ':order_id' => $orderId
]);

header("Location: order-beheer.php");
exit;
