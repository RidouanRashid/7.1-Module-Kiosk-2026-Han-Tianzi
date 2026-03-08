<?php
include("includes/header.php");
include("includes/connection.php");

$cart = $_SESSION['cart'] ?? [];
$itemCount = 0;
$total = 0;
foreach ($cart as $item) {
    $itemCount += $item['qty'];
    $total += $item['price'] * $item['qty'];
}
?>

<body>
<div id="cart-screen" class="screen cart-screen active">
    <main class="mock-screen-layout">
        <section class="mock-phone-card mock-phone-card--cart">
            <header class="mock-phone-header">
                <img src="assets/logo/logo_big_dinosaur_transparent.webp" alt="Logo links" class="mock-phone-logo mock-phone-logo--left">
                <img src="assets/logo/logo_big_happy_herbivore_transparent.webp" alt="Happy Herbivore" class="mock-phone-logo mock-phone-logo--center">
                <img src="assets/pagina-deco/winkelmandje.png" alt="Winkelmand" class="mock-phone-logo mock-phone-logo--right">
            </header>
            <div class="mock-phone-body">
                <h1 class="mock-order-title">YOUR ORDER</h1>

                <div class="cart-items-panel">
                    <div class="cart-items">
                        <?php if (empty($cart)): ?>
                            <p class="cart-empty">Your cart is empty</p>
                        <?php else: ?>
                            <?php foreach ($cart as $productId => $item): ?>
                                <div class="cart-item">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-image" />
                                    <div class="cart-item-main">
                                        <div class="cart-item-name"><?php echo htmlspecialchars(strtoupper($item['name'])); ?></div>
                                        <div class="cart-item-price"><?php echo $fmt->formatCurrency($item['price'], 'EUR'); ?></div>
                                        <div class="cart-item-meta"><?php echo (int)$item['kcal']; ?> KCAL</div>
                                    </div>
                                    <div class="cart-item-controls">
                                        <form method="post" action="cart-actions.php" style="display:inline">
                                            <input type="hidden" name="action" value="decrease">
                                            <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">
                                            <button type="submit" class="cart-qty-btn">−</button>
                                        </form>
                                        <span class="cart-item-qty"><?php echo (int)$item['qty']; ?></span>
                                        <form method="post" action="cart-actions.php" style="display:inline">
                                            <input type="hidden" name="action" value="increase">
                                            <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">
                                            <button type="submit" class="cart-qty-btn">+</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <footer class="mock-cart-footer">
                        <div class="mock-cart-totals">
                            <span>ITEMS: <strong><?php echo $itemCount; ?></strong></span>
                            <span>TOTAL: <strong><?php echo $fmt->formatCurrency($total, 'EUR'); ?></strong></span>
                        </div>
                        <div class="mock-cart-actions">
                            <form method="post" action="cart-actions.php" style="display:inline">
                                <input type="hidden" name="action" value="clear">
                                <button class="mock-btn mock-btn--cancel" type="submit">CANCEL</button>
                            </form>
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
        </section>
    </main>
</div>
</body>
