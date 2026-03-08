<?php
include("includes/header.php");
include("includes/connection.php");

// If no order was placed, redirect back
if (empty($_SESSION['last_order_id'])) {
    header("Location: kies-orders.php");
    exit;
}
?>

<body>
    <div id="instructions-screen" class="screen instructions-screen active">
        <main class="mock-screen-layout">
            <section class="mock-phone-card mock-phone-card--instructions">
                <header class="mock-phone-header">
                    <img src="assets/logo/logo_big_dinosaur_transparent.webp" alt="Logo links" class="mock-phone-logo mock-phone-logo--left">
                    <img src="assets/logo/logo_big_happy_herbivore_transparent.webp" alt="Happy Herbivore" class="mock-phone-logo mock-phone-logo--center">
                    <img src="assets/pagina-deco/winkelmandje.png" alt="Winkelmand" class="mock-phone-logo mock-phone-logo--right">
                </header>
                <div class="mock-phone-body mock-phone-body--centered">
                    <div class="mock-info-panel">
                        <p class="mock-info-title">Please follow instructions</p>
                        <p class="mock-info-text">Follow the instructions on the payment terminal to complete your transaction.</p>
                        <img src="assets/pagina-deco/icoontjes/pin.png" alt="Payment terminal" class="instructions-image">
                    </div>
                    <a href="thankyou.php" class="pay-action-link" style="text-decoration:none;">
                        <button class="mock-btn mock-btn--confirm" type="button">I PAID</button>
                    </a>
                </div>
            </section>
        </main>
    </div>
</body>
