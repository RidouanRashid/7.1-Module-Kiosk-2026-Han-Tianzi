<?php
include("includes/header.php");
include("includes/connection.php");

$categoryId = isset($_GET['cat']) ? (int)$_GET['cat'] : 1;
$productId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$product = null;

if ($conn instanceof PDO && $productId > 0) {
    $stmt = $conn->prepare("
        SELECT p.product_id, p.category_id, p.NAME, p.description, p.price, p.kcal,
               i.filename, i.filename_transparent
        FROM products p
        LEFT JOIN images i ON p.image_id = i.image_id
        WHERE p.product_id = :id
        LIMIT 1
    ");
    $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$product) {
    header("Location: kies-orders.php?cat=$categoryId");
    exit;
}

$imagePath = getImagePath((int)$product['category_id'], $product['filename'] ?? '', $categoryFolders);
?>

<body>
<div id="detail-screen" class="screen detail-screen active">
	<main class="mock-screen-layout">
		<section class="mock-phone-card mock-phone-card--detail">
			<header class="mock-phone-header">
				<img src="assets/logo/logo_big_dinosaur_transparent.webp" alt="Logo" class="mock-phone-logo mock-phone-logo--left">
				<img src="assets/logo/logo_big_happy_herbivore_transparent.webp" alt="Happy Herbivore" class="mock-phone-logo mock-phone-logo--center">
				<img src="assets/pagina-deco/winkelmandje.png" alt="Winkelmand" class="mock-phone-logo mock-phone-logo--right">
			</header>

			<div class="mock-phone-body detail-body">
				<div class="detail-hero-box">
					<img src="<?php echo htmlspecialchars($imagePath); ?>"
					     alt="<?php echo htmlspecialchars($product['NAME']); ?>"
					     class="detail-hero-img" />
				</div>

				<div class="detail-content-card">
					<h2 class="detail-product-name"><?php echo htmlspecialchars(strtoupper($product['NAME'])); ?></h2>
					<p class="detail-product-desc"><?php echo htmlspecialchars($product['description']); ?></p>

					<div class="detail-meta-row">
						<div class="detail-price-pill">
							<span class="detail-meta-dot"></span>
							<?php echo $fmt->formatCurrency((float)$product['price'], 'EUR'); ?>
						</div>
						<div class="detail-qty-pill">
							<button type="button" class="detail-qty-btn" onclick="changeQty(-1)">−</button>
							<span class="detail-qty-value" id="detail-qty">1</span>
							<button type="button" class="detail-qty-btn" onclick="changeQty(1)">+</button>
						</div>
						<div class="detail-kcal-pill">
							<?php echo (int)$product['kcal']; ?> KCAL
							<span class="detail-meta-dot"></span>
						</div>
					</div>

					<div class="detail-actions-row">
						<a href="kies-orders.php?cat=<?php echo (int)$categoryId; ?>" class="detail-action-link">
							<button class="mock-btn mock-btn--cancel" type="button">CANCEL</button>
						</a>
						<form method="post" action="cart-actions.php" class="detail-action-link">
							<input type="hidden" name="action" value="add">
							<input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
							<input type="hidden" name="cat" value="<?php echo (int)$categoryId; ?>">
							<input type="hidden" name="qty" value="1" id="detail-qty-input">
							<button class="mock-btn mock-btn--confirm" type="submit">ADD TO ORDER</button>
						</form>
					</div>
				</div>
			</div>
		</section>
	</main>
</div>

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
