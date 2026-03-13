<?php
include("includes/connection.php");
include("includes/header.php");

$cat = filter_input(INPUT_GET, 'cat', FILTER_VALIDATE_INT);

if (!$cat) {
    if (!empty($_SESSION['last_cat'])) {
        header("Location: kies-orders.php?cat=" . (int)$_SESSION['last_cat']);
        exit;
    }
    header("Location: kies-order-begin.php");
    exit;
}

$_SESSION['last_cat'] = $cat;

try {
    $stmt = $conn->prepare("
        SELECT 
            p.product_id,
            p.NAME AS product_name,
            p.price,
            p.kcal,
            p.`V-VG` AS food_type,
            i.filename_transparent,
            c.NAME AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN images i ON p.image_id = i.image_id
        WHERE p.category_id = :cat
        ORDER BY p.product_id ASC
    ");

    $stmt->bindParam(':cat', $cat, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$products) {
        echo "Geen producten gevonden in deze categorie";
        exit;
    }

    $category = $products[0]['category_name'];

    $categoryFolder = $categoryFolders[$cat] ?? str_replace(' ', '', trim($category));
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
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
                    <p class="categorie-naam-tekst">
                        Choose your <?php echo htmlspecialchars($category); ?>
                    </p>

                    <div class="filter-box">
                        <button class="filter-btn active" data-filter="all">
                            <img src="assets/pagina-deco/icoontjes/vegan.png" alt="Vegan">
                            <span>MIXED</span>
                            <img src="assets/pagina-deco/icoontjes/vegetarian.png" alt="Vegetarian">
                        </button>
                        <button class="filter-btn" data-filter="Vegan">
                            <img src="assets/pagina-deco/icoontjes/vegan.png" alt="Vegan">
                            <span>VEGAN</span>
                        </button>
                        <button class="filter-btn" data-filter="Vegetarian">
                            <img src="assets/pagina-deco/icoontjes/vegetarian.png" alt="Vegetarian">
                            <span>VEGETARIAN</span>
                        </button>
                    </div>
                </div>

                <div class="productContainer">
                    <?php foreach ($products as $v): ?>
                        <?php
                        $foodIcon = '';

                        if ($v['food_type'] === 'Vegan') {
                            $foodIcon = 'assets/pagina-deco/icoontjes/vegan.png';
                        } elseif ($v['food_type'] === 'Vegetarian') {
                            $foodIcon = 'assets/pagina-deco/icoontjes/vegetarian.png';
                        }
                        ?>

                        <a class="product-link" href="detail.php?id=<?php echo (int)$v['product_id']; ?>&cat=<?php echo (int)$cat; ?>" data-food-type="<?php echo htmlspecialchars($v['food_type'] ?? ''); ?>">
                            <div class="product">
                                <div class="img-box">
                                    <?php if (!empty($foodIcon)): ?>
                                        <img src="<?php echo htmlspecialchars($foodIcon); ?>" class="product-food-icon" alt="<?php echo htmlspecialchars($v['food_type']); ?>">
                                    <?php endif; ?>

                                    <?php if (!empty($v['filename_transparent'])): ?>
                                        <img src="assets/menu/<?php echo htmlspecialchars($categoryFolder); ?>/<?php echo htmlspecialchars($v['filename_transparent']); ?>" class="product-img" alt="<?php echo htmlspecialchars($v['product_name']); ?>">
                                    <?php endif; ?>

                                </div>

                                <div class="tekst-box">
                                    <p class="naam-product"><?php echo htmlspecialchars($v['product_name']); ?></p>

                                    <div class="prijs-kcal-box">
                                        <p class="prijs"><?php echo $fmt->formatCurrency((float)$v['price'], 'EUR'); ?></p>
                                        <p class="kcal"><?php echo (int)$v['kcal']; ?> KCAL</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php include("includes/cart-footer.php"); ?>

            </div>
        </div>
    </div>

    <?php include("includes/sessie-timeout.php"); ?>

</body>

<script>
    (function() {
        const url = new URL(window.location.href);
        const cat = url.searchParams.get("cat");

        if (cat) {
            localStorage.setItem("lastCategory", cat);
        } else {
            const last = localStorage.getItem("lastCategory");
            if (last) {
                url.searchParams.set("cat", last);
                window.location.replace(url.toString());
            }
        }
    })();

    // Food type filter
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.getAttribute('data-filter');

            document.querySelectorAll('.product-link').forEach(link => {
                const type = link.getAttribute('data-food-type');

                if (filter === 'all' || type === filter) {
                    link.style.display = '';
                } else {
                    link.style.display = 'none';
                }
            });
        });
    });
</script>