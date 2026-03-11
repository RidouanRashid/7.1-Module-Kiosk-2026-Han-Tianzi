<?php
include("includes/header.php");
include("includes/connection.php");

function getMenuImages($conn, $categoryId, $categoryFolders)
{
    $stmt = $conn->prepare("
        SELECT i.filename_transparent
        FROM products p
        LEFT JOIN images i ON p.image_id = i.image_id
        WHERE p.category_id = :cat
          AND i.filename_transparent IS NOT NULL
          AND i.filename_transparent != ''
        ORDER BY p.product_id ASC
    ");
    $stmt->bindValue(':cat', $categoryId, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $folder = $categoryFolders[$categoryId] ?? '';

    $images = [];

    foreach ($rows as $row) {
        if (!empty($row['filename_transparent']) && !empty($folder)) {
            $images[] = "assets/menu/" . $folder . "/" . $row['filename_transparent'];
        }
    }

    return $images;
}

$menuImageSets = [
    'breakfast' => getMenuImages($conn, 1, $categoryFolders),
    'lunchdinner' => getMenuImages($conn, 2, $categoryFolders),
    'handhelds' => getMenuImages($conn, 3, $categoryFolders)
];
?>

<body>
    <div id="achtergrond-orders">

        <?php include("includes/topbar-orderscherm.php"); ?>

        <div id="body-box-kies-order">

            <?php include("includes/sidebar-orderscherm.php"); ?>

            <div class="content-kies-orders">

                <div class="titel-box">
                    <p class="welkom-tekst">Welcome by Happy Herbivore</p>
                    <p class="categorie-naam-tekst">Choose your menu</p>
                </div>

                <div class="productContainer menu-choice-container">

                    <a class="product-link" href="menu-opbouw.php?menu=breakfast">
                        <div class="product menu-choice-card">
                            <div class="img-box">
                                <img
                                    src="<?php echo !empty($menuImageSets['breakfast']) ? htmlspecialchars($menuImageSets['breakfast'][0]) : 'assets/sidebar-icoontjes/breakfast-icoon.png'; ?>"
                                    class="product-img rotating-menu-img"
                                    data-menu="breakfast"
                                    alt="Breakfast">
                            </div>

                            <div class="tekst-box">
                                <p class="naam-product">Breakfast Menu</p>
                                <div class="prijs-kcal-box">
                                    <p class="prijs">Choose</p>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a class="product-link" href="menu-opbouw.php?menu=lunchdinner">
                        <div class="product menu-choice-card">
                            <div class="img-box">
                                <img
                                    src="<?php echo !empty($menuImageSets['lunchdinner']) ? htmlspecialchars($menuImageSets['lunchdinner'][0]) : 'assets/sidebar-icoontjes/lunch-icoon.png'; ?>"
                                    class="product-img rotating-menu-img"
                                    data-menu="lunchdinner"
                                    alt="Lunch & Dinner">
                            </div>

                            <div class="tekst-box">
                                <p class="naam-product">Lunch & Dinner Menu</p>
                                <div class="prijs-kcal-box">
                                    <p class="prijs">Choose</p>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a class="product-link" href="menu-opbouw.php?menu=handhelds">
                        <div class="product menu-choice-card">
                            <div class="img-box">
                                <img
                                    src="<?php echo !empty($menuImageSets['handhelds']) ? htmlspecialchars($menuImageSets['handhelds'][0]) : 'assets/sidebar-icoontjes/handhelds-icoon.png'; ?>"
                                    class="product-img rotating-menu-img"
                                    data-menu="handhelds"
                                    alt="Handhelds">
                            </div>

                            <div class="tekst-box">
                                <p class="naam-product">Handhelds Menu</p>
                                <div class="prijs-kcal-box">
                                    <p class="prijs">Choose</p>
                                </div>
                            </div>
                        </div>
                    </a>

                </div>
            </div>

            <?php include("includes/cart-footer.php"); ?>

        </div>
    </div>

    <script>
        const menuImages = <?php echo json_encode($menuImageSets, JSON_UNESCAPED_SLASHES); ?>;
        const indexes = {};

        document.querySelectorAll(".rotating-menu-img").forEach(img => {

            const menu = img.dataset.menu;
            indexes[menu] = 0;

            if (menuImages[menu] && menuImages[menu].length > 1) {

                setInterval(() => {

                    indexes[menu]++;

                    if (indexes[menu] >= menuImages[menu].length) {
                        indexes[menu] = 0;
                    }

                    img.src = menuImages[menu][indexes[menu]];

                }, 3000);

            }

        });
    </script>

    <?php include("includes/sessie-timeout.php"); ?>

</body>