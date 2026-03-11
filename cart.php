<?php
session_start();
include("includes/header.php");
include("includes/connection.php");

$cart = $_SESSION['cart'] ?? [];
$itemCount = 0;
$total = 0;

foreach ($cart as $item) {
    $qty = $item['qty'] ?? 1;
    $itemCount += $qty;
    $total += $item['price'] * $qty;
}

function getCategoryFolderName($categoryName)
{
    return strtolower(str_replace([' ', '&'], ['', ''], trim($categoryName)));
}
?>

<body>
    <div id="achtergrond-cart">
        <?php include("includes/topbar-orderscherm.php"); ?>

        <div class="page-content page-content--cart">
            <div class="cart-items-panel">
                <h1 class="mock-order-title">YOUR ORDER</h1>

                <div class="cart-items">
                    <?php if (empty($cart)): ?>
                        <p class="cart-empty">Your cart is empty</p>
                    <?php else: ?>
                        <?php foreach ($cart as $index => $item): ?>
                            <?php
                            $qty = $item['qty'] ?? 1;
                            $isMenu = ($item['type'] ?? '') === 'menu';

                            if ($isMenu) {
                                $main = $item['main'] ?? null;
                                $side = $item['side'] ?? null;
                                $sauce = $item['sauce'] ?? null;
                                $drink = $item['drink'] ?? null;

                                $mainImage = '';
                                if (!empty($main['filename_transparent']) && !empty($main['category_name'])) {
                                    $folder = getCategoryFolderName($main['category_name']);
                                    $mainImage = "assets/menu/{$folder}/" . $main['filename_transparent'];
                                }
                            } else {
                                $mainImage = $item['image'] ?? '';
                            }
                            ?>

                            <div class="cart-item">
                                <?php if (!empty($mainImage)): ?>
                                    <img src="<?php echo htmlspecialchars($mainImage); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-image" />
                                <?php endif; ?>

                                <div class="cart-item-main">
                                    <div class="cart-item-name">
                                        <?php echo htmlspecialchars(strtoupper($item['name'])); ?>
                                    </div>

                                    <div class="cart-item-price">
                                        <?php echo $fmt->formatCurrency((float)$item['price'], 'EUR'); ?>
                                    </div>

                                    <div class="cart-item-meta">
                                        <?php echo (int)$item['kcal']; ?> KCAL
                                    </div>

                                    <?php if ($isMenu): ?>
                                        <div class="cart-item-menu-details">
                                            <p><strong>Main:</strong> <?php echo htmlspecialchars($main['product_name'] ?? '-'); ?></p>
                                            <p><strong>Side:</strong> <?php echo htmlspecialchars($side['product_name'] ?? 'No choice'); ?></p>
                                            <p><strong>Sauce:</strong> <?php echo htmlspecialchars($sauce['product_name'] ?? 'No choice'); ?></p>
                                            <p><strong>Drink:</strong> <?php echo htmlspecialchars($drink['product_name'] ?? 'No choice'); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="cart-item-controls">
                                    <form method="post" action="cart-actions.php" style="display:inline">
                                        <input type="hidden" name="action" value="decrease">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$index; ?>">
                                        <button type="submit" class="cart-qty-btn cart-qty-btn-decrease">−</button>
                                    </form>

                                    <span class="cart-item-qty"><?php echo (int)$qty; ?></span>

                                    <form method="post" action="cart-actions.php" style="display:inline">
                                        <input type="hidden" name="action" value="increase">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$index; ?>">
                                        <button type="submit" class="cart-qty-btn cart-qty-btn-increase">+</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <footer class="mock-cart-footer">
                    <div class="mock-cart-totals">
                        <span>ITEMS: <strong><?php echo $itemCount; ?></strong></span>
                        <span>TOTAL: <strong><?php echo $fmt->formatCurrency((float)$total, 'EUR'); ?></strong></span>
                    </div>

                    <div class="mock-cart-actions">
                        <a href="kies-order-begin.php" class="mock-btn mock-btn--cancel">CANCEL</a>

                        <?php if (!empty($cart)): ?>
                            <form method="post" action="cart-actions.php" style="display:inline">
                                <input type="hidden" name="action" value="checkout">
                                <button class="mock-btn mock-btn--confirm" type="submit">COMPLETE ORDER</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <?php include("includes/sessie-timeout.php"); ?>

</body>