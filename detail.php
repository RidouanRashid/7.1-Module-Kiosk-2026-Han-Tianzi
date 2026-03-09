<?php
include("includes/connection.php");

if (!isset($_GET['id'])) {
    echo "Geen product ID opgegeven";
    exit;
}

$id = (int) $_GET['id'];

try {
    $stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.NAME AS titel,
        p.description AS info,
        p.price,
        p.kcal,
        p.available,
        p.`V-VG` AS food_type,
        c.NAME AS category_name,
        i.filename AS photo
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN images i ON p.image_id = i.image_id
    WHERE p.product_id = :id
");

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Product niet gevonden";
        exit;
    }

    $title = $product['titel'];
    $image = $product['photo'];
    $info = $product['info'];
    $price = $product['price'];
    $kcal = $product['kcal'];
    $available = $product['available'];
    $category = $product['category_name'];
    $cat = filter_input(INPUT_GET, 'cat', FILTER_VALIDATE_INT);

    $categoryFolder = $categoryFolders[$cat] ?? strtolower(str_replace(' ', '', trim($category)));

    $foodType = $product['food_type'];
    $foodIcon = '';

    if ($foodType === 'Vegan') {
        $foodIcon = 'assets/pagina-deco/icoontjes/vegan.png';
    } elseif ($foodType === 'Vegetarian') {
        $foodIcon = 'assets/pagina-deco/icoontjes/vegetarian.png';
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

include("includes/header.php");
?>

<body>
    <?php include("includes/topbar-orderscherm.php"); ?>

    <div id="achtergrond-detail">

        <div id="detail-image-box">
            <?php if (!empty($image)) : ?>
                <img src="assets/menu/<?php echo htmlspecialchars($categoryFolder); ?>/<?php echo htmlspecialchars($image); ?>" id='detail-eten-img' alt="<?php echo htmlspecialchars($title); ?>">
            <?php endif; ?>

            <?php if (!empty($foodIcon)) : ?>
                <img id="food-label-icon" src="<?php echo htmlspecialchars($foodIcon); ?>" alt="<?php echo htmlspecialchars($foodType); ?>">
            <?php endif; ?>
        </div>

        <div id="detail-info-box">
            <p id="detail-titel"><?php echo htmlspecialchars($title); ?></p>
            <p id="detail-beschrijving"><?php echo htmlspecialchars($info); ?></p>

<<<<<<< HEAD
=======
<script>
function changeQty(delta) {
    const el = document.getElementById('detail-qty');
    const input = document.getElementById('detail-qty-input');
    let qty = Math.max(1, parseInt(el.textContent) + delta);
    el.textContent = qty;
    input.value = qty;
}
</script>
</body>
include("includes/connection.php");

if (!isset($_GET['id'])) {
    echo "Geen product ID opgegeven";
    exit;
}

$id = (int) $_GET['id'];

try {
    $stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.NAME AS titel,
        p.description AS info,
        p.price,
        p.kcal,
        p.available,
        p.`V-VG` AS food_type,
        c.NAME AS category_name,
        i.filename AS photo
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN images i ON p.image_id = i.image_id
    WHERE p.product_id = :id
");

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Product niet gevonden";
        exit;
    }

    $title = $product['titel'];
    $image = $product['photo'];
    $info = $product['info'];
    $price = $product['price'];
    $kcal = $product['kcal'];
    $available = $product['available'];
    $category = $product['category_name'];
    $cat = filter_input(INPUT_GET, 'cat', FILTER_VALIDATE_INT);

    $categoryFolder = strtolower(trim($category));
    $categoryFolder = str_replace(' ', '', $categoryFolder);

    $foodType = $product['food_type'];
    $foodIcon = '';

    if ($foodType === 'Vegan') {
        $foodIcon = 'assets/pagina-deco/icoontjes/vegan.png';
    } elseif ($foodType === 'Vegetarian') {
        $foodIcon = 'assets/pagina-deco/icoontjes/vegetarian.png';
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

include("includes/header.php");
?>

<body>
    <?php include("includes/topbar-orderscherm.php"); ?>

    <div id="achtergrond-detail">

        <div id="detail-image-box">
            <?php if (!empty($image)) : ?>
                <img src="assets/menu/<?php echo htmlspecialchars($categoryFolder); ?>/<?php echo htmlspecialchars($image); ?>" id='detail-eten-img' alt="<?php echo htmlspecialchars($title); ?>">
            <?php endif; ?>

            <?php if (!empty($foodIcon)) : ?>
                <img id="food-label-icon" src="<?php echo htmlspecialchars($foodIcon); ?>" alt="<?php echo htmlspecialchars($foodType); ?>">
            <?php endif; ?>
        </div>

        <div id="detail-info-box">
            <p id="detail-titel"><?php echo htmlspecialchars($title); ?></p>
            <p id="detail-beschrijving"><?php echo htmlspecialchars($info); ?></p>

>>>>>>> origin/main
            <div id="detail-onderkant">
                <p id="detail-prijs" data-prijs="<?php echo htmlspecialchars($price); ?>">
                    <?php echo $fmt->formatCurrency($price, 'EUR'); ?>
                </p>

                <div id="detail-aantal-box">
                    <button type="button" class="aantal-knop min">−</button>
                    <span id="aantal">1</span>
                    <button type="button" class="aantal-knop plus">+</button>
                </div>

                <p id="detail-kcal" data-kcal="<?php echo htmlspecialchars($kcal); ?>">
                    <?php echo htmlspecialchars($kcal); ?> KCAL
                </p>
            </div>

            <div id="detail-knoppen">
                <a href="kies-orders.php?cat=<?php echo (int)$cat; ?>" id="cancel-knop">CANCEL</a>

<<<<<<< HEAD
                <form method="POST" action="cart-actions.php" id="add-to-order-form">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                    <input type="hidden" name="qty" id="aantal-input" value="1">
                    <input type="hidden" name="cat" value="<?php echo (int)$cat; ?>">
=======
                <form method="POST" action="winkelmand.php" id="add-to-order-form">
                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                    <input type="hidden" name="aantal" id="aantal-input" value="1">
>>>>>>> origin/main
                    <button type="submit" id="add-to-order-knop">ADD TO ORDER</button>
                </form>
            </div>
        </div>

        <script>
            const minBtn = document.querySelector('.min');
            const plusBtn = document.querySelector('.plus');
            const aantalSpan = document.getElementById('aantal');
            const aantalInput = document.getElementById('aantal-input');
            const prijsElement = document.getElementById('detail-prijs');

            let aantal = 1;
            const basisPrijs = parseFloat(prijsElement.dataset.prijs);

            function updateGegevens() {
                const totaalPrijs = basisPrijs * aantal;

                aantalSpan.textContent = aantal;
                aantalInput.value = aantal;
                prijsElement.textContent = '€ ' + totaalPrijs.toFixed(2);
            }

            plusBtn.addEventListener('click', () => {
                aantal++;
                updateGegevens();
            });

            minBtn.addEventListener('click', () => {
                if (aantal > 1) {
                    aantal--;
                    updateGegevens();
                }
            });
        </script>
<<<<<<< HEAD
</body>
=======
</body>
>>>>>>> origin/main
