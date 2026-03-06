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
            c.NAME AS category_name,
            c.description AS category_description,
            i.filename AS photo,
            i.description AS image_description
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
    $category_description = $product['category_description'];
    $image_description = $product['image_description'];

    $categoryFolder = strtolower(trim($category));
    $categoryFolder = str_replace(' ', '', $categoryFolder);
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
                <img src="assets/menu/<?php echo htmlspecialchars($categoryFolder); ?>/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($title); ?>">
            <?php endif; ?>
        </div>

        <div id="detail-info-box">
            <p id="detail-titel"><?php echo htmlspecialchars($title); ?></p>
            <p id="detail-beschrijving"><?php echo htmlspecialchars($info); ?></p>

            <div id="detail-onderkant">
                <p id="detail-prijs" data-prijs="<?php echo htmlspecialchars($price); ?>">
                    <?php echo $fmt->formatCurrency($price, 'EUR'); ?>
                </p>

                <div id="detail-aantal-box">
                    <button type="button" class="aantal-knop min">−</button>
                    <span id="aantal">1</span>
                    <button type="button" class="aantal-knop plus">+</button>
                </div>

                <p id="detail-kcal"><?php echo htmlspecialchars($kcal); ?> KCAL</p>
            </div>
        </div>
    </div>

    <script>
        const minBtn = document.querySelector('.min');
        const plusBtn = document.querySelector('.plus');
        const aantalSpan = document.getElementById('aantal');
        const prijsElement = document.getElementById('detail-prijs');

        let aantal = 1;
        const basisPrijs = parseFloat(prijsElement.dataset.prijs);

        function updatePrijs() {
            const totaalPrijs = basisPrijs * aantal;
            prijsElement.textContent = '€ ' + totaalPrijs.toFixed(2);
        }

        plusBtn.addEventListener('click', () => {
            aantal++;
            aantalSpan.textContent = aantal;
            updatePrijs();
        });

        minBtn.addEventListener('click', () => {
            if (aantal > 1) {
                aantal--;
                aantalSpan.textContent = aantal;
                updatePrijs();
            }
        });
    </script>
</body>