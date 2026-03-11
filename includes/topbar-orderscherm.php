<div id="achtergrond-topbar">
    <a href="kies-order-begin.php">
        <img src="assets\logo\logo_big_complete_transparent.webp" id="Happy-Herbivore-logo" alt="Happy-Herbivore-logo">
    </a>
    <img src="assets\logo\logo_big_happy_herbivore_transparent.png" id="Happy-Herbivore-Tekst" alt="Happy-Herbivore-Tekst">

    <a href="cart.php" id="winkelmandje-link">
        <img src="assets\pagina-deco\winkelmandje.png" id="winkelmandje" alt="winkelmandje">

        <?php
        $cartCount = 0;

        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $ci) {
                $cartCount += (int)($ci['qty'] ?? $ci['quantity'] ?? 1);
            }
        }

        if ($cartCount > 0):
        ?>
            <span class="cart-badge"><?php echo $cartCount; ?></span>
        <?php endif; ?>
    </a>
</div>