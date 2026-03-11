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
    <div id="achtergrond-betaal">
        <?php include("includes/topbar-orderscherm.php"); ?>
        <div class="page-content page-content--betaal">
                    <div class="mock-info-panel">
                        <p class="mock-info-title">Please follow instructions</p>
                        <p class="mock-info-text">Follow the instructions on the payment terminal to complete your transaction.</p>
                        <img src="assets/pagina-deco/icoontjes/pin.png" alt="Payment terminal" class="instructions-image">
                    </div>
        </div>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = "thankyou.php";
        }, 2000);
    </script>
</body>
