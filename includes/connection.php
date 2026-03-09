<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "happy-herbivore";

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
    4 => 'Sides&SmallPlates',
    5 => 'SignatureDips',
    6 => 'Drinks',
];

/**
 * Build the image path for a product.
 */
function getImagePath(int $categoryId, string $filename, array $categoryFolders): string
{
    $folder = $categoryFolders[$categoryId] ?? 'Breakfast';
    return 'assets/menu/' . $folder . '/' . $filename;
}

// Initialise cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
