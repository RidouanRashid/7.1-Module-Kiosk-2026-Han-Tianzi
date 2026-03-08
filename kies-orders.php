<?php
include("includes/header.php");
include("includes/connection.php");

$categories = [
	1 => "Choose your breakfast",
	2 => "Choose your lunch & dinner",
	3 => "Choose your handhelds",
	4 => "Choose your sides",
	5 => "Choose your drinks",
	6 => "Choose your dips",
];

$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 1;

if (!array_key_exists($cat, $categories)) {
	$cat = 1;
}

$pageTitle = $categories[$cat];
?>

<body>
	<div id="achtergrond-orders">

		<?php include("includes/topbar-orderscherm.php"); ?>

		<div id="body-box-kies-order">

			<?php include("includes/sidebar-orderscherm.php"); ?>

			<div class="content-kies-orders">

				<div class="titel-box">
					<p class="welkom-tekst">Welcome by Happy Herbivore</p>
					<p class="categorie-naam-tekst"><?php echo htmlspecialchars($pageTitle); ?></p>
				</div>

				<?php
				if (!($conn instanceof PDO)) {
					echo '<p>Database connection is unavailable right now.</p>';
				} else {
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
						AND p.category_id = :cat
						ORDER BY p.product_id ASC
					");

						$stmt->bindValue(':cat', $cat, PDO::PARAM_INT);
						$stmt->execute();
						$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
				?>

						<div class="productContainer">
							<?php foreach ($products as $v): ?>
								<div class="product">
									<a href="detail.php?id=<?php echo (int)$v['product_id']; ?>&cat=<?php echo (int)$cat; ?>">
										<div class="img-box">
											<?php if (!empty($v['filename'])): ?>
												<img src="<?php echo htmlspecialchars(getImagePath($cat, $v['filename'], $categoryFolders)); ?>" alt="">
											<?php endif; ?>
										</div>

										<div class="tekst-box">
											<p class="naam-product"><?php echo htmlspecialchars($v['NAME']); ?></p>
											<div class="prijs-kcal-box">
												<p class="prijs"><?php echo $fmt->formatCurrency((float)$v['price'], 'EUR'); ?></p>
												<p class="kcal"><?php echo (int)$v['kcal']; ?> kcal</p>
											</div>
										</div>
									</a>
								</div>
							<?php endforeach; ?>
						</div>

				<?php
					} catch (PDOException $e) {
						echo "Error: " . $e->getMessage();
					}
				}
				?>

			</div>
		</div>
	</div>

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
	</script>
</body>
