<?php
session_start();
include("includes/header.php");
include("includes/connection.php");

$menu = $_GET['menu'] ?? '';

$mainCategories = [
    'breakfast' => 1,
    'lunchdinner' => 2,
    'handhelds' => 3
];

$sideCategoryId = 4;
$sauceCategoryId = 5;
$drinkCategoryId = 6;

if (!isset($mainCategories[$menu])) {
    echo "Ongeldig menu";
    exit;
}

$mainCategoryId = $mainCategories[$menu];

function getProductsByCategory($conn, $categoryId)
{
    $stmt = $conn->prepare("
        SELECT 
            p.product_id,
            p.NAME AS product_name,
            p.price,
            p.kcal,
            p.`V-VG` AS food_type,
            p.category_id,
            i.filename_transparent,
            c.NAME AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN images i ON p.image_id = i.image_id
        WHERE p.category_id = :cat
        ORDER BY p.product_id ASC
    ");
    $stmt->bindParam(':cat', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductById($conn, $id)
{
    if ($id == 0) {
        return null;
    }

    $stmt = $conn->prepare("
        SELECT 
            p.product_id,
            p.NAME AS product_name,
            p.price,
            p.kcal,
            p.category_id,
            p.`V-VG` AS food_type,
            i.filename_transparent,
            c.NAME AS category_name
        FROM products p
        LEFT JOIN images i ON p.image_id = i.image_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id = :id
        LIMIT 1
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCategoryFolder($categoryId, $categoryName, $categoryFolders = [])
{
    if (isset($categoryFolders[$categoryId]) && !empty($categoryFolders[$categoryId])) {
        return $categoryFolders[$categoryId];
    }

    $fallbackFolders = [
        1 => 'breakfast',
        2 => 'lunchdinner',
        3 => 'handhelds',
        4 => 'sides',
        5 => 'dips',
        6 => 'drinks'
    ];

    if (isset($fallbackFolders[$categoryId])) {
        return $fallbackFolders[$categoryId];
    }

    return strtolower(str_replace([' ', '&'], ['', ''], trim($categoryName)));
}

function getMenuDisplayName($menu)
{
    $names = [
        'breakfast' => 'Breakfast',
        'lunchdinner' => 'Lunch & Dinner',
        'handhelds' => 'Handhelds'
    ];

    return $names[$menu] ?? ucfirst($menu);
}

$mainProducts = getProductsByCategory($conn, $mainCategoryId);
$sideProducts = getProductsByCategory($conn, $sideCategoryId);
$sauceProducts = getProductsByCategory($conn, $sauceCategoryId);
$drinkProducts = getProductsByCategory($conn, $drinkCategoryId);

$mainCategoryName = $mainProducts[0]['category_name'] ?? '';
$sideCategoryName = $sideProducts[0]['category_name'] ?? '';
$sauceCategoryName = $sauceProducts[0]['category_name'] ?? '';
$drinkCategoryName = $drinkProducts[0]['category_name'] ?? '';

$mainFolder = getCategoryFolder($mainCategoryId, $mainCategoryName, $categoryFolders ?? []);
$sideFolder = getCategoryFolder($sideCategoryId, $sideCategoryName, $categoryFolders ?? []);
$sauceFolder = getCategoryFolder($sauceCategoryId, $sauceCategoryName, $categoryFolders ?? []);
$drinkFolder = getCategoryFolder($drinkCategoryId, $drinkCategoryName, $categoryFolders ?? []);

$menuDisplayName = getMenuDisplayName($menu);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mainId = (int)($_POST['main_id'] ?? 0);
    $sideId = (int)($_POST['side_id'] ?? 0);
    $sauceId = (int)($_POST['sauce_id'] ?? 0);
    $drinkId = (int)($_POST['drink_id'] ?? 0);

    $mainProduct = getProductById($conn, $mainId);
    $sideProduct = getProductById($conn, $sideId);
    $sauceProduct = getProductById($conn, $sauceId);
    $drinkProduct = getProductById($conn, $drinkId);

    if (!$mainProduct) {
        echo "Je moet een hoofdgerecht kiezen.";
        exit;
    }

    $totalPrice = (float)$mainProduct['price'];
    $totalKcal = (int)$mainProduct['kcal'];

    if ($sideProduct) {
        $totalPrice += (float)$sideProduct['price'];
        $totalKcal += (int)$sideProduct['kcal'];
    }

    if ($sauceProduct) {
        $totalPrice += (float)$sauceProduct['price'];
        $totalKcal += (int)$sauceProduct['kcal'];
    }

    if ($drinkProduct) {
        $totalPrice += (float)$drinkProduct['price'];
        $totalKcal += (int)$drinkProduct['kcal'];
    }

    $cartItem = [
        'type' => 'menu',
        'menu_type' => $menu,
        'name' => $menuDisplayName . ' Menu',
        'main' => $mainProduct,
        'side' => $sideProduct,
        'sauce' => $sauceProduct,
        'drink' => $drinkProduct,
        'price' => $totalPrice,
        'kcal' => $totalKcal,
        'qty' => 1
    ];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $_SESSION['cart'][] = $cartItem;

    header("Location: cart.php");
    exit;
}

function renderOptionGroup($products, $name, $title, $folder, $fmt)
{
?>
    <div class="menu-step-box">
        <p class="menu-step-title"><?php echo htmlspecialchars($title); ?></p>
        <div class="productContainer">

            <label class="menu-option-label">
                <input type="radio" name="<?php echo htmlspecialchars($name); ?>" value="0" checked class="menu-option-input">
                <div class="product product-no-choice">
                    <div class="img-box">
                        <div class="geen-keuze-tekst">No choice</div>
                    </div>
                    <div class="tekst-box">
                        <p class="naam-product">No choice</p>
                        <div class="prijs-kcal-box">
                            <p class="prijs">€ 0,00</p>
                            <p class="kcal">0 KCAL</p>
                        </div>
                    </div>
                </div>
            </label>

            <?php foreach ($products as $product): ?>
                <?php
                $foodIcon = '';

                if ($product['food_type'] === 'Vegan') {
                    $foodIcon = 'assets/pagina-deco/icoontjes/vegan.png';
                } elseif ($product['food_type'] === 'Vegetarian') {
                    $foodIcon = 'assets/pagina-deco/icoontjes/vegetarian.png';
                }
                ?>
                <label class="menu-option-label">
                    <input type="radio" name="<?php echo htmlspecialchars($name); ?>" value="<?php echo (int)$product['product_id']; ?>" class="menu-option-input">
                    <div class="product">
                        <div class="img-box">
                            <?php if (!empty($foodIcon)): ?>
                                <img src="<?php echo htmlspecialchars($foodIcon); ?>" class="product-food-icon" alt="<?php echo htmlspecialchars($product['food_type']); ?>">
                            <?php endif; ?>

                            <?php if (!empty($product['filename_transparent'])): ?>
                                <img src="assets/menu/<?php echo htmlspecialchars($folder); ?>/<?php echo htmlspecialchars($product['filename_transparent']); ?>" class="product-img" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            <?php endif; ?>
                        </div>

                        <div class="tekst-box">
                            <p class="naam-product"><?php echo htmlspecialchars($product['product_name']); ?></p>
                            <div class="prijs-kcal-box">
                                <p class="prijs"><?php echo $fmt->formatCurrency((float)$product['price'], 'EUR'); ?></p>
                                <p class="kcal"><?php echo (int)$product['kcal']; ?> KCAL</p>
                            </div>
                        </div>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
<?php
}
?>

<body>
    <div id="achtergrond-orders">

        <?php include("includes/topbar-orderscherm.php"); ?>

        <div id="body-box-kies-order">

            <?php include("includes/sidebar-orderscherm.php"); ?>

            <div class="content-kies-orders">

                <div class="titel-box">
                    <p class="welkom-tekst">Welcome by Happy Herbivore</p>
                    <p class="categorie-naam-tekst">Build your <?php echo htmlspecialchars($menuDisplayName); ?> menu</p>
                </div>

                <form method="post" class="menu-builder-form">
                    <div class="menu-step-box">
                        <p class="menu-step-title">Choose your main</p>
                        <div class="productContainer">
                            <?php foreach ($mainProducts as $product): ?>
                                <?php
                                $foodIcon = '';

                                if ($product['food_type'] === 'Vegan') {
                                    $foodIcon = 'assets/pagina-deco/icoontjes/vegan.png';
                                } elseif ($product['food_type'] === 'Vegetarian') {
                                    $foodIcon = 'assets/pagina-deco/icoontjes/vegetarian.png';
                                }
                                ?>
                                <label class="menu-option-label">
                                    <input type="radio" name="main_id" value="<?php echo (int)$product['product_id']; ?>" required class="menu-option-input">
                                    <div class="product">
                                        <div class="img-box">
                                            <?php if (!empty($foodIcon)): ?>
                                                <img src="<?php echo htmlspecialchars($foodIcon); ?>" class="product-food-icon" alt="<?php echo htmlspecialchars($product['food_type']); ?>">
                                            <?php endif; ?>

                                            <?php if (!empty($product['filename_transparent'])): ?>
                                                <img src="assets/menu/<?php echo htmlspecialchars($mainFolder); ?>/<?php echo htmlspecialchars($product['filename_transparent']); ?>" class="product-img" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                            <?php endif; ?>
                                        </div>

                                        <div class="tekst-box">
                                            <p class="naam-product"><?php echo htmlspecialchars($product['product_name']); ?></p>
                                            <div class="prijs-kcal-box">
                                                <p class="prijs"><?php echo $fmt->formatCurrency((float)$product['price'], 'EUR'); ?></p>
                                                <p class="kcal"><?php echo (int)$product['kcal']; ?> KCAL</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php
                    renderOptionGroup($sideProducts, 'side_id', 'Choose your side', $sideFolder, $fmt);
                    renderOptionGroup($sauceProducts, 'sauce_id', 'Choose your sauce', $sauceFolder, $fmt);
                    renderOptionGroup($drinkProducts, 'drink_id', 'Choose your drink', $drinkFolder, $fmt);
                    ?>

                    <div class="menu-submit-box">
                        <a href="menu-start.php" class="menu-terug-knop">Back</a>
                        <button type="submit" class="menu-verder-knop">Add menu to cart</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <?php include("includes/sessie-timeout.php"); ?>

</body>