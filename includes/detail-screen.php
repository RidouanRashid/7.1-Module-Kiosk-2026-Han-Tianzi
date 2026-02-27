<div id="detail-screen" class="screen detail-screen">
	<header class="top-bar">
		<button class="back-btn" type="button" data-action="back-to-category">&larr;</button>
		<div class="top-title-wrapper">
			<h1 class="top-title">Jouw keuze</h1>
			<p class="top-subtitle">Pas je gerecht aan en bevestig</p>
		</div>
		<div class="step-indicator" aria-label="Order progress">
			<span class="step-indicator-step" data-step-name="category">1. Kies</span>
			<span class="step-indicator-step" data-step-name="cart">2. Controleer</span>
			<span class="step-indicator-step" data-step-name="instructions">3. Betaal</span>
			<span class="step-indicator-step" data-step-name="thankyou">4. Klaar</span>
		</div>
	</header>
	<main class="detail-layout">
		<div class="detail-image-wrapper">
			<img id="detail-image" src="" alt="" class="detail-image">
		</div>
		<div class="detail-info">
			<h2 id="detail-name" class="detail-name"></h2>
			<p id="detail-description" class="detail-description"></p>
			<p class="detail-price-label">Prijs</p>
			<p id="detail-price" class="detail-price"></p>
			<div class="detail-quantity">
				<span>Aantal</span>
				<div class="quantity-controls">
					<button type="button" class="secondary-btn" data-action="decrease-qty">−</button>
					<span id="detail-quantity-value" class="quantity-value">1</span>
					<button type="button" class="secondary-btn" data-action="increase-qty">+</button>
				</div>
			</div>
			<div class="detail-actions">
				<button id="add-to-cart-btn" class="primary-btn" type="button">Toevoegen aan bestelling</button>
				<button class="secondary-btn" type="button" data-action="back-to-category">Terug</button>
			</div>
		</div>
	</main>
</div>

