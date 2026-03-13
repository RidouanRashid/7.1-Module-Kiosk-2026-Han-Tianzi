<?php
include("includes/connection.php");

$selectedOrderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

$stmtOrders = $conn->prepare("
    SELECT 
        o.order_id,
        o.pickup_number,
        o.order_status_id,
        o.price_total,
        o.DATETIME,
        COUNT(op.product_id) AS total_items,
        COALESCE(SUM(op.is_ready), 0) AS ready_items
    FROM orders o
    LEFT JOIN order_product op ON op.order_id = o.order_id
    WHERE o.order_status_id IN (1, 2)
    GROUP BY o.order_id, o.pickup_number, o.order_status_id, o.price_total, o.DATETIME
    ORDER BY o.DATETIME ASC
");
$stmtOrders->execute();
$orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

$orderDetails = null;
$orderItems = [];

if ($selectedOrderId) {
    $stmtDetail = $conn->prepare("
        SELECT order_id, pickup_number, order_status_id, price_total, DATETIME
        FROM orders
        WHERE order_id = :order_id
        LIMIT 1
    ");
    $stmtDetail->execute([':order_id' => $selectedOrderId]);
    $orderDetails = $stmtDetail->fetch(PDO::FETCH_ASSOC);

    if ($orderDetails) {
        $stmtItems = $conn->prepare("
            SELECT 
                op.order_id,
                op.product_id,
                op.price,
                op.is_ready,
                p.NAME AS product_name,
                p.kcal,
                c.NAME AS category_name
            FROM order_product op
            LEFT JOIN products p ON p.product_id = op.product_id
            LEFT JOIN categories c ON c.category_id = p.category_id
            WHERE op.order_id = :order_id
            ORDER BY op.product_id ASC
        ");
        $stmtItems->execute([':order_id' => $selectedOrderId]);
        $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order beheer</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            color: #222;
        }

        .header {
            background: #ff7a1a;
            color: white;
            padding: 20px 30px;
            font-size: 32px;
            font-weight: bold;
        }

        .layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            height: calc(100vh - 76px);
            overflow: hidden;
        }

        .sidebar {
            background: #1f1f1f;
            padding: 20px;
            color: white;
            overflow-y: auto;
            height: 100%;
        }

        .sidebar h2 {
            margin-top: 0;
            font-size: 24px;
        }

        .order-link {
            display: block;
            text-decoration: none;
            color: inherit;
            background: #2b2b2b;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
            border: 2px solid transparent;
            transition: 0.2s ease;
        }

        .order-link:hover {
            background: #353535;
        }

        .order-link.active {
            border-color: #ff7a1a;
            background: #333;
        }

        .order-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            gap: 10px;
        }

        .pickup {
            font-size: 28px;
            font-weight: bold;
        }

        .progress {
            font-size: 14px;
            color: #ccc;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: bold;
            white-space: nowrap;
        }

        .badge-bereiden {
            background: #fff3cd;
            color: #856404;
        }

        .badge-klaar {
            background: #d4edda;
            color: #155724;
        }

        .content {
            padding: 30px;
            overflow-y: auto;
            height: 100%;
        }

        .empty-box,
        .detail-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .detail-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .detail-title {
            font-size: 36px;
            font-weight: bold;
        }

        .detail-meta {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .items {
            display: grid;
            gap: 14px;
        }

        .item {
            border: 2px solid #eee;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            background: #fff;
        }

        .item.ready {
            background: #f3fff5;
            border-color: #8ad39a;
        }

        .item-info {
            flex: 1;
            min-width: 0;
        }

        .item-info h3 {
            margin: 0 0 6px;
            font-size: 22px;
        }

        .item-info p {
            margin: 0;
            color: #666;
        }

        .item-actions {
            flex-shrink: 0;
        }

        .item-actions form {
            margin: 0;
        }

        .btn {
            border: none;
            padding: 12px 16px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
        }

        .btn-ready {
            background: #28a745;
            color: white;
        }

        .btn-unready {
            background: #dc3545;
            color: white;
        }

        .btn-pickup {
            background: #ff7a1a;
            color: white;
            padding: 14px 22px;
            font-size: 16px;
        }

        .pickup-box {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }

        .status-text {
            font-weight: bold;
            color: #1d7a32;
        }

        @media (max-width: 1100px) {
            .layout {
                grid-template-columns: 1fr;
                height: auto;
                overflow: visible;
            }

            .sidebar,
            .content {
                height: auto;
                overflow: visible;
            }
        }
    </style>
</head>

<body>
    <div class="header">Order beheer</div>

    <div class="layout">
        <aside class="sidebar">
            <h2>Openstaande orders</h2>

            <?php if (empty($orders)): ?>
                <p>Er zijn geen openstaande orders.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <?php
                    $totalItems = (int)($order['total_items'] ?? 0);
                    $readyItems = (int)($order['ready_items'] ?? 0);
                    $activeClass = ($selectedOrderId == $order['order_id']) ? 'active' : '';
                    ?>
                    <a class="order-link <?php echo $activeClass; ?>" href="order-beheer.php?order_id=<?php echo (int)$order['order_id']; ?>">
                        <div class="order-top">
                            <span class="pickup">#<?php echo (int)$order['pickup_number']; ?></span>

                            <?php if ((int)$order['order_status_id'] === 2): ?>
                                <span class="badge badge-klaar">Klaar</span>
                            <?php else: ?>
                                <span class="badge badge-bereiden">Bereiden</span>
                            <?php endif; ?>
                        </div>

                        <div class="progress">
                            <?php echo $readyItems; ?> / <?php echo $totalItems; ?> items klaar
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </aside>

        <main class="content" id="content-area">
            <?php if (!$orderDetails): ?>
                <div class="empty-box">
                    <h2>Kies een order</h2>
                    <p>Klik links op een ordernummer om de bestelling te bekijken.</p>
                </div>
            <?php else: ?>
                <div class="detail-card">
                    <div class="detail-head">
                        <div class="detail-title">Order #<?php echo (int)$orderDetails['pickup_number']; ?></div>

                        <?php if ((int)$orderDetails['order_status_id'] === 2): ?>
                            <span class="badge badge-klaar">Klaar om op te halen</span>
                        <?php else: ?>
                            <span class="badge badge-bereiden">Wordt bereid</span>
                        <?php endif; ?>
                    </div>

                    <div class="detail-meta">
                        Totaal: € <?php echo number_format((float)$orderDetails['price_total'], 2, ',', '.'); ?><br>
                        Tijd: <?php echo htmlspecialchars($orderDetails['DATETIME']); ?>
                    </div>

                    <div class="items">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="item <?php echo ((int)$item['is_ready'] === 1) ? 'ready' : ''; ?>">
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['product_name'] ?? 'Onbekend product'); ?></h3>
                                    <p>
                                        <?php echo htmlspecialchars($item['category_name'] ?? ''); ?>
                                        <?php if ((float)$item['price'] > 0): ?>
                                            · € <?php echo number_format((float)$item['price'], 2, ',', '.'); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <div class="item-actions">
                                    <?php if ((int)$orderDetails['order_status_id'] === 1): ?>
                                        <?php if ((int)$item['is_ready'] === 1): ?>
                                            <form method="post" action="update-order-item.php" class="scroll-form">
                                                <input type="hidden" name="order_id" value="<?php echo (int)$item['order_id']; ?>">
                                                <input type="hidden" name="product_id" value="<?php echo (int)$item['product_id']; ?>">
                                                <input type="hidden" name="ready" value="0">
                                                <button type="submit" class="btn btn-unready">Toch niet klaar</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="update-order-item.php" class="scroll-form">
                                                <input type="hidden" name="order_id" value="<?php echo (int)$item['order_id']; ?>">
                                                <input type="hidden" name="product_id" value="<?php echo (int)$item['product_id']; ?>">
                                                <input type="hidden" name="ready" value="1">
                                                <button type="submit" class="btn btn-ready">Afvinken</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="status-text">Klaar</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ((int)$orderDetails['order_status_id'] === 2): ?>
                        <div class="pickup-box">
                            <form method="post" action="pickup-order.php" class="scroll-form">
                                <input type="hidden" name="order_id" value="<?php echo (int)$orderDetails['order_id']; ?>">
                                <button type="submit" class="btn btn-pickup">Klant heeft opgehaald</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        const contentArea = document.getElementById('content-area');

        document.querySelectorAll('.scroll-form').forEach(function(form) {
            form.addEventListener('submit', function() {
                if (contentArea) {
                    sessionStorage.setItem('orderBeheerScrollTop', contentArea.scrollTop);
                }
            });
        });

        window.addEventListener('load', function() {
            const savedScrollTop = sessionStorage.getItem('orderBeheerScrollTop');

            if (savedScrollTop !== null && contentArea) {
                contentArea.scrollTop = parseInt(savedScrollTop, 10);
                sessionStorage.removeItem('orderBeheerScrollTop');
            }
        });
    </script>
</body>

</html>