<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "u240073_kiosk";
$password = "zHYpEbTTCkdb33FDFsjA";
$dbname = "u240073_kiosk";

$conn = null;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
}

// Category-id → image folder mapping
$categoryFolders = [
    1 => 'Breakfast',
    2 => 'Lunch&Dinner',
    3 => 'Handhelds',
    4 => 'Sides&Small-Plates',
    5 => 'Signature-Dips',
    6 => 'Drinks',
];

/**
 * Build the image path for a product.
 */
function getImagePath(int $categoryId, string $filename, array $categoryFolders): string {
    $folder = $categoryFolders[$categoryId] ?? 'Breakfast';
    return 'assets/menu/' . $folder . '/' . $filename;
}

// Initialise cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

