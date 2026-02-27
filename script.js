// ---------- DATA ----------

const categories = [
	{
		id: 'breakfast',
		name: 'Breakfast',
		icon: 'assets/menu/Breakfast/breakfast1.png',
	},
	{
		id: 'lunch_dinner',
		name: 'Lunch & Dinner',
		icon: 'assets/menu/Lunch&Dinner/lunch1.png',
	},
	{
		id: 'drinks',
		name: 'Drinks',
		icon: 'assets/menu/Drinks/drink2.png',
	},
	{
		id: 'handhelds',
		name: 'Handhelds',
		icon: 'assets/menu/Handhelds/handheld1.png',
	},
];

const products = [
	{
		id: 'teriyaki_bowl',
		categoryId: 'lunch_dinner',
		name: 'Warm Teriyaki Tempeh Bowl',
		description: 'Fresh veggies, rice and tempeh with teriyaki sauce.',
		price: 9.95,
		image: 'assets/menu/Lunch&Dinner/lunch1.png',
		isPopular: true,
	},
	{
		id: 'veggie_bowl',
		categoryId: 'lunch_dinner',
		name: 'Crispy Veggie Bowl',
		description: 'Crispy vegetables served with noodles or rice.',
		price: 8.95,
		image: 'assets/menu/Lunch&Dinner/lunch2.png',
	},
	{
		id: 'smoothie_berry',
		categoryId: 'drinks',
		name: 'Berry Smoothie',
		description: 'Fresh strawberries and berries blended with yoghurt.',
		price: 4.50,
		image: 'assets/menu/Drinks/drink2.png',
		isPopular: true,
	},
	{
		id: 'smoothie_mango',
		categoryId: 'drinks',
		name: 'Mango Smoothie',
		description: 'Tropical mango smoothie with a creamy finish.',
		price: 4.50,
		image: 'assets/menu/Drinks/drink1.png',
	},
	{
		id: 'breakfast_bowl',
		categoryId: 'breakfast',
		name: 'Breakfast Granola Bowl',
		description: 'Granola, yoghurt and fresh fruits.',
		price: 6.25,
		image: 'assets/menu/Breakfast/breakfast1.png',
		isPopular: true,
	},
	{
		id: 'wrap_handheld',
		categoryId: 'handhelds',
		name: 'Crispy Veggie Wrap',
		description: 'Handheld wrap filled with crunchy veggies.',
		price: 7.50,
		image: 'assets/menu/Handhelds/handheld3.png',
	},
];

// ---------- STATE ----------

let currentScreen = 'start';
let orderType = null; // 'takeout' | 'eat-in'
let selectedCategoryId = null;
let selectedProductId = null;
let detailQuantity = 1;
let cart = [];
let currentOrderNumber = 99;

// Slideshow state
let currentSlide = 0;
let slides = [];
let slideIntervalId = null;

// ---------- HELPERS ----------

function euro(amount) {
	return `€${amount.toFixed(2).replace('.', ',')}`;
}

function findProduct(productId) {
	return products.find((p) => p.id === productId) || null;
}

// ---------- SCREEN HANDLING ----------

function showScreen(name) {
	const screens = document.querySelectorAll('.screen');
	screens.forEach((screen) => {
		screen.classList.toggle('active', screen.id === `${name}-screen`);
	});
	currentScreen = name;
	updateStepIndicators(name);
}

function goToCategory(categoryId) {
	selectedCategoryId = categoryId;
	highlightSelectedCategory();
	renderProductsForCategory();
	showScreen('category');
}

// ---------- RENDERING ----------

function renderCategories() {
	const grid = document.getElementById('category-grid');
	if (!grid) return;

	grid.innerHTML = '';
	categories.forEach((cat) => {
		const tile = document.createElement('button');
		tile.type = 'button';
		tile.className = 'category-tile';
		tile.dataset.categoryId = cat.id;

		tile.innerHTML = `
			<img src="${cat.icon}" alt="${cat.name}" class="category-icon" />
			<span class="category-name">${cat.name}</span>
		`;

		tile.addEventListener('click', () => {
			goToCategory(cat.id);
		});

		grid.appendChild(tile);
	});
}

function highlightSelectedCategory() {
	const tiles = document.querySelectorAll('.category-tile');
	tiles.forEach((tile) => {
		tile.classList.toggle(
			'selected',
			tile.dataset.categoryId === selectedCategoryId
		);
	});
}

function renderProductsForCategory() {
	const list = document.getElementById('product-list');
	if (!list) return;

	list.innerHTML = '';
	if (!selectedCategoryId) return;

	const filtered = products.filter(
		(product) => product.categoryId === selectedCategoryId
	);

	filtered.forEach((product) => {
		const popularBadgeHtml = product.isPopular
			? '<span class="badge-popular">Popular</span>'
			: '';

		const card = document.createElement('article');
		card.className = 'product-card';
		card.innerHTML = `
			<div class="product-card-image-wrapper">
				<img src="${product.image}" alt="${product.name}" class="product-card-image" />
			</div>
			<div class="product-card-body">
				<div class="product-card-header">
					<h3 class="product-card-name">${product.name}</h3>
					${popularBadgeHtml}
				</div>
				<p class="product-card-price">${euro(product.price)}</p>
				<div class="product-card-actions">
					<button type="button" class="primary-btn" data-product-id="${product.id}">
						Choose
					</button>
				</div>
			</div>
		`;

		const chooseBtn = card.querySelector('button');
		chooseBtn.addEventListener('click', () => {
			openProductDetail(product.id);
		});

		list.appendChild(card);
	});
}

function renderCartPreview() {
	const container = document.getElementById('cart-preview-items');
	const totalEl = document.getElementById('cart-preview-total-amount');
	if (!container || !totalEl) return;

	container.innerHTML = '';

	if (cart.length === 0) {
		container.innerHTML = '<p>Nog geen items.</p>';
		totalEl.textContent = euro(0);
		return;
	}

	let total = 0;

	cart.forEach((item) => {
		const product = findProduct(item.productId);
		if (!product) return;

		const subtotal = product.price * item.quantity;
		total += subtotal;

		const row = document.createElement('div');
		row.className = 'cart-preview-item';
		row.innerHTML = `
			<span>${item.quantity}× ${product.name}</span>
			<span>${euro(subtotal)}</span>
		`;
		container.appendChild(row);
	});

	totalEl.textContent = euro(total);
}

function renderCartFull() {
	const container = document.getElementById('cart-items');
	const subtotalEl = document.getElementById('cart-subtotal');
	const totalEl = document.getElementById('cart-total');
	const itemCountEl = document.getElementById('cart-item-count');
	if (!container || !subtotalEl || !totalEl) return;

	container.innerHTML = '';

	if (cart.length === 0) {
		container.innerHTML = '<p class="cart-empty">Je winkelmand is leeg.</p>';
		subtotalEl.textContent = euro(0);
		totalEl.textContent = euro(0);
		if (itemCountEl) {
			itemCountEl.textContent = '0';
		}
		return;
	}

	let subtotal = 0;
	let totalItems = 0;

	cart.forEach((item) => {
		const product = findProduct(item.productId);
		if (!product) return;

		const itemSubtotal = product.price * item.quantity;
		subtotal += itemSubtotal;
		totalItems += item.quantity;
		const kcalText = product.id === 'zucchini_fries' ? '850 KCAL' : '500 KCAL';

		const row = document.createElement('div');
		row.className = 'cart-item';
		row.innerHTML = `
			<img src="${product.image}" alt="${product.name}" class="cart-item-image" />
			<div class="cart-item-main">
				<div class="cart-item-name">${product.name}</div>
				<div class="cart-item-meta">${euro(product.price)} &nbsp; ${kcalText}</div>
			</div>
			<div class="cart-item-controls">
				<button type="button" class="cart-qty-btn" data-action="cart-decrease" data-product-id="${item.productId}">−</button>
				<span class="cart-item-qty">${item.quantity}</span>
				<button type="button" class="cart-qty-btn" data-action="cart-increase" data-product-id="${item.productId}">+</button>
			</div>
		`;

		container.appendChild(row);
	});

	subtotalEl.textContent = euro(subtotal);
	totalEl.textContent = euro(subtotal);
	if (itemCountEl) {
		itemCountEl.textContent = String(totalItems);
	}

	// attach listeners after DOM is built
	container.querySelectorAll('[data-action="cart-decrease"]').forEach((btn) =>
		btn.addEventListener('click', () => {
			const id = btn.dataset.productId;
			changeCartQuantity(id, -1);
		})
	);
	container.querySelectorAll('[data-action="cart-increase"]').forEach((btn) =>
		btn.addEventListener('click', () => {
			const id = btn.dataset.productId;
			changeCartQuantity(id, 1);
		})
	);
}

function openProductDetail(productId) {
	const product = findProduct(productId);
	if (!product) return;

	selectedProductId = productId;
	detailQuantity = 1;

	const imageEl = document.getElementById('detail-image');
	const nameEl = document.getElementById('detail-name');
	const descEl = document.getElementById('detail-description');
	const priceEl = document.getElementById('detail-price');
	const qtyEl = document.getElementById('detail-quantity-value');

	if (imageEl) imageEl.src = product.image;
	if (imageEl) imageEl.alt = product.name;
	if (nameEl) nameEl.textContent = product.name;
	if (descEl) descEl.textContent = product.description;
	if (priceEl) priceEl.textContent = euro(product.price);
	if (qtyEl) qtyEl.textContent = String(detailQuantity);

	showScreen('detail');
}

// ---------- CART ----------

function addToCart(productId, quantity) {
	if (!productId || quantity <= 0) return;

	const existing = cart.find((item) => item.productId === productId);
	if (existing) {
		existing.quantity += quantity;
	} else {
		cart.push({ productId, quantity });
	}

	renderCartPreview();
	renderCartFull();
}

function changeCartQuantity(productId, delta) {
	const item = cart.find((i) => i.productId === productId);
	if (!item) return;

	item.quantity += delta;
	if (item.quantity <= 0) {
		cart = cart.filter((i) => i.productId !== productId);
	}

	renderCartPreview();
	renderCartFull();
}

function resetForNewOrder() {
	cart = [];
	selectedCategoryId = null;
	selectedProductId = null;
	orderType = null;
	currentOrderNumber += 1;

	renderCartPreview();
	renderCartFull();
	renderCategories();
	highlightSelectedCategory();

	showScreen('start');
}

// ---------- PAYMENT FLOW ----------

function proceedToPayment() {
	if (cart.length === 0) {
		return;
	}
	showScreen('instructions');
}

// Update progress indicator based on current screen
function updateStepIndicators(activeScreenName) {
	const screenStepMap = {
		category: 'category',
		detail: 'category',
		cart: 'cart',
		instructions: 'instructions',
		thankyou: 'thankyou',
	};

	const currentStepName = screenStepMap[activeScreenName] || null;

	document
		.querySelectorAll('.step-indicator-step')
		.forEach((step) => {
			step.classList.toggle(
				'active',
				!!currentStepName &&
					step.dataset.stepName === currentStepName
			);
		});
}

function paymentCompleted() {
	const display = document.getElementById('order-number-display');
	if (display) {
		display.textContent = `#${currentOrderNumber}`;
	}
	showScreen('thankyou');

	// after 15 seconds go back to start for new order
	setTimeout(() => {
		resetForNewOrder();
	}, 15000);
}

// ---------- SLIDESHOW ----------

function setupSlideshow() {
	slides = Array.from(document.querySelectorAll('.scheme'));
	if (slides.length === 0) return;

	currentSlide = 0;
	slides.forEach((slide, index) => {
		slide.classList.toggle('active', index === 0);
	});

	if (slideIntervalId) {
		clearInterval(slideIntervalId);
	}
	slideIntervalId = setInterval(nextSlide, 5000);
}

function showSlide(n) {
	if (!slides.length) return;
	slides.forEach((slide, index) => {
		slide.classList.toggle('active', index === n);
	});
}

function nextSlide() {
	if (!slides.length) return;
	currentSlide = (currentSlide + 1) % slides.length;
	showSlide(currentSlide);
}

// ---------- INIT & EVENT BINDING ----------

function setupEventListeners() {
	// Order buttons on slideshow
	document
		.querySelectorAll('[data-action="start-order"]')
		.forEach((btn) =>
			btn.addEventListener('click', () => {
				// after clicking "Order here" first choose order type
				showScreen('order-type');
			})
		);

	// Order type selection buttons
	document
		.querySelectorAll('.order-type-btn')
		.forEach((btn) =>
			btn.addEventListener('click', () => {
				const chosenType = btn.dataset.orderType;
				if (chosenType === 'takeout' || chosenType === 'eat-in') {
					orderType = chosenType;
				}

				// default to first category when starting the actual order
				if (!selectedCategoryId && categories.length) {
					selectedCategoryId = categories[0].id;
				}
				highlightSelectedCategory();
				renderProductsForCategory();
				renderCartPreview();
				showScreen('category');
			})
		);

	// Back buttons to category
	document
		.querySelectorAll('[data-action="back-to-category"]')
		.forEach((btn) =>
			btn.addEventListener('click', () => {
				showScreen('category');
			})
		);

	// Detail quantity buttons
	const qtyInc = document.querySelector(
		'[data-action="increase-qty"]'
	);
	const qtyDec = document.querySelector(
		'[data-action="decrease-qty"]'
	);
	const qtyValue = document.getElementById('detail-quantity-value');

	if (qtyInc && qtyDec && qtyValue) {
		qtyInc.addEventListener('click', () => {
			detailQuantity += 1;
			qtyValue.textContent = String(detailQuantity);
		});
		qtyDec.addEventListener('click', () => {
			if (detailQuantity > 1) {
				detailQuantity -= 1;
				qtyValue.textContent = String(detailQuantity);
			}
		});
	}

	// Add to cart button
	const addToCartBtn = document.getElementById('add-to-cart-btn');
	if (addToCartBtn) {
		addToCartBtn.addEventListener('click', () => {
			if (selectedProductId) {
				addToCart(selectedProductId, detailQuantity);
				showScreen('cart');
			}
		});
	}

	// View cart from preview
	const goToCartBtn = document.getElementById('go-to-cart-btn');
	if (goToCartBtn) {
		goToCartBtn.addEventListener('click', () => {
			renderCartFull();
			showScreen('cart');
		});
	}

	// Cart screen buttons
	const addMoreItemsBtn = document.getElementById(
		'add-more-items-btn'
	);
	if (addMoreItemsBtn) {
		addMoreItemsBtn.addEventListener('click', () => {
			showScreen('category');
		});
	}

	const proceedToPaymentBtn = document.getElementById(
		'proceed-to-payment-btn'
	);
	if (proceedToPaymentBtn) {
		proceedToPaymentBtn.addEventListener('click', () => {
			proceedToPayment();
		});
	}

	// Payment / thank you
	const paymentCompletedBtn = document.getElementById(
		'payment-completed-btn'
	);
	if (paymentCompletedBtn) {
		paymentCompletedBtn.addEventListener('click', () => {
			paymentCompleted();
		});
	}

	const newOrderBtn = document.getElementById('new-order-btn');
	if (newOrderBtn) {
		newOrderBtn.addEventListener('click', () => {
			resetForNewOrder();
		});
	}
}

function initApp() {
	renderCategories();
	if (categories.length) {
		selectedCategoryId = categories[0].id;
		highlightSelectedCategory();
		renderProductsForCategory();
	}
	renderCartPreview();
	renderCartFull();
	setupSlideshow();
	setupEventListeners();
	showScreen('start');
}

// Because script is loaded at end of body, DOM is ready
initApp();
