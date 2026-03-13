<?php
include("includes/connection.php");

$order_id = $_POST['order_id'];

$stmt = $conn->prepare("
UPDATE orders
SET order_status_id = 2
WHERE order_id = :id
");

$stmt->execute([':id' => $order_id]);

header("Location: order-ready.php");
exit;
