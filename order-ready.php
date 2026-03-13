<?php
include("includes/connection.php");

$stmt = $conn->prepare("
    SELECT order_id, pickup_number, order_status_id
    FROM orders
    WHERE order_status_id IN (1, 2)
    ORDER BY DATETIME ASC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Ready</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #111;
            color: white;
            margin: 0;
            padding: 30px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .kolom {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 20px;
            min-height: 500px;
        }

        .kolom h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 30px;
        }

        .order {
            background: #2c2c2c;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 40px;
            font-weight: bold;
        }

        .bereiden .order {
            border-left: 10px solid orange;
        }

        .klaar .order {
            border-left: 10px solid limegreen;
        }
    </style>
</head>

<body>

    <h1>Bestellingen</h1>

    <div class="wrapper">
        <div class="kolom bereiden">
            <h2>Wordt bereid</h2>
            <?php foreach ($orders as $order): ?>
                <?php if ((int)$order['order_status_id'] === 1): ?>
                    <div class="order">
                        #<?php echo (int)$order['pickup_number']; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="kolom klaar">
            <h2>Klaar om op te halen</h2>
            <?php foreach ($orders as $order): ?>
                <?php if ((int)$order['order_status_id'] === 2): ?>
                    <div class="order">
                        #<?php echo (int)$order['pickup_number']; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        setTimeout(function() {
            location.reload();
        }, 5000);
    </script>

</body>

</html>