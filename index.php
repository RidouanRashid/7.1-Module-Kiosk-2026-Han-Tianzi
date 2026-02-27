<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wok to Go - Kiosk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="app" class="kiosk-frame">
        <?php include 'includes/start-screen.php'; ?>
        <?php include 'includes/order-type-screen.php'; ?>
        <?php include 'includes/category-screen.php'; ?>
        <?php include 'includes/detail-screen.php'; ?>
        <?php include 'includes/cart-screen.php'; ?>
        <?php include 'includes/instructions-screen.php'; ?>
        <?php include 'includes/thankyou-screen.php'; ?>
    </div>
    
    <script src="script.js"></script>
</body>
</html>