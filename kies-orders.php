<?php
include("includes/header.php");
include("includes/connection.php");
?>

<body>
    <div id="achtergrond-orders">

        <?php include("includes/topbar-orderscherm.php"); ?>

        <div id="body-box">

            <?php include("includes/sidebar-orderscherm.php"); ?>

            <div class="content-kies-orders">

                <div class="titel-box">
                    <p class="welkom-tekst">Welcome by Happy Herbivore</p>
                    <p class="categorie-naam-tekst">Choose your breakfast</p>
                </div>

                <?php
                try {

                    $stmt = $conn->prepare("
                        SELECT 
                            p.product_id,
                            p.NAME,
                            p.price,
                            p.kcal,
                            i.filename
                        FROM products p
                        LEFT JOIN images i ON p.image_id = i.image_id
                        WHERE p.available = 1
                        AND p.category_id = 1
                        ORDER BY p.product_id ASC
                    ");

                    $stmt->execute();
                ?>

                    <div class="productContainer">
                        <?php
                        foreach ($stmt->fetchAll() as $v) {
                            echo "<div class='product'>
                                <a href='detail.php?id={$v['product_id']}'>
                                <img src='assets/menu/Breakfast/{$v['filename']}' alt='{$v['NAME']}'>
                                <p class='naam-product'>{$v['NAME']}</p>
                                <div class='prijs-kcal-box'>
                                    <p class='prijs'>" . $fmt->formatCurrency($v['price'], 'EUR') . "</p>
                                    <p class='kcal'>{$v['kcal']} kcal</p>
                                </div>
                                </a>
                            </div>";
                        }
                        ?>
                    </div>

                <?php
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                ?>

            </div>
        </div>
    </div>
</body>