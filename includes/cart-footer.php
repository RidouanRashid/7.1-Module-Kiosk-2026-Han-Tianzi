<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once(__DIR__ . "/connection.php");

if (!isset($fmt)) {
    if (class_exists('NumberFormatter')) {
        $fmt = new NumberFormatter('nl_NL', NumberFormatter::CURRENCY);
    } else {
        $fmt = new class {
            public function formatCurrency(float $amount, string $currency): string
            {
                $symbol = $currency === 'EUR' ? '€' : $currency . ' ';
                return $symbol . number_format($amount, 2, ',', '.');
            }
        };
    }
}

$cart = $_SESSION['cart'] ?? [];
$itemCount = 0;
$total = 0;

foreach ($cart as $item) {
    $qty = (int)($item['qty'] ?? 1);
    $itemCount += $qty;
    $total += ((float)($item['price'] ?? 0)) * $qty;
}

function getCategoryFolderForCart($categoryId, $categoryName, $categoryFolders)
{
    if (isset($categoryFolders[$categoryId]) && !empty($categoryFolders[$categoryId])) {
        return $categoryFolders[$categoryId];
    }

    return strtolower(str_replace([' ', '&'], ['', ''], trim($categoryName)));
}

function getTransparentImageForProduct($conn, $productId, $categoryFolders)
{
    if (!$productId || !($conn instanceof PDO)) {
        return '';
    }

    $stmt = $conn->prepare("
        SELECT 
            p.category_id,
            c.NAME AS category_name,
            i.filename_transparent
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN images i ON p.image_id = i.image_id
        WHERE p.product_id = :id
        LIMIT 1
    ");
    $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || empty($row['filename_transparent'])) {
        return '';
    }

    $folder = getCategoryFolderForCart(
        (int)$row['category_id'],
        $row['category_name'] ?? '',
        $categoryFolders ?? []
    );

    return "assets/menu/" . $folder . "/" . $row['filename_transparent'];
}
?>

<div class="cart-bar">
    <div class="cart-bar-main">
        <div class="cart-bar-title">
            <p>Your order</p>
        </div>

        <div class="cart-bar-items">
            <?php foreach ($cart as $index => $item): ?>
                <?php
                $qty = (int)($item['qty'] ?? 1);
                $foodImage = '';

                if (($item['type'] ?? '') === 'menu') {
                    $main = $item['main'] ?? null;

                    if (!empty($main['filename_transparent'])) {
                        $mainCategoryId = (int)($main['category_id'] ?? 0);
                        $mainCategoryName = $main['category_name'] ?? '';

                        $mainCategoryFolder = getCategoryFolderForCart(
                            $mainCategoryId,
                            $mainCategoryName,
                            $categoryFolders ?? []
                        );

                        $foodImage = "assets/menu/" . $mainCategoryFolder . "/" . $main['filename_transparent'];
                    }
                } else {
                    $productId = (int)($item['product_id'] ?? 0);
                    $foodImage = getTransparentImageForProduct($conn, $productId, $categoryFolders ?? []);
                }
                ?>

                <div class="cart-mini-card">
                    <div class="cart-mini-img-box">
                        <?php if (!empty($foodImage)): ?>
                            <img
                                src="<?php echo htmlspecialchars($foodImage); ?>"
                                class="cart-mini-img"
                                alt="<?php echo htmlspecialchars($item['name'] ?? 'Product'); ?>">
                        <?php endif; ?>
                    </div>

                    <div class="cart-mini-text">
                        <p class="cart-mini-name">
                            <?php echo htmlspecialchars($item['name'] ?? 'Product'); ?>
                        </p>

                        <div class="cart-mini-meta">
                            <span class="cart-mini-price">
                                <?php echo $fmt->formatCurrency((float)($item['price'] ?? 0), 'EUR'); ?>
                            </span>
                            <span class="cart-mini-kcal">
                                <?php echo (int)($item['kcal'] ?? 0); ?> KCAL
                            </span>
                        </div>

                        <div class="cart-mini-qty">
                            <form method="post" action="cart-actions.php" class="cart-qty-form">
                                <input type="hidden" name="action" value="decrease">
                                <input type="hidden" name="product_id" value="<?php echo (int)$index; ?>">
                                <input type="hidden" name="ajax" value="1">
                                <button type="submit">−</button>
                            </form>

                            <span><?php echo $qty; ?></span>

                            <form method="post" action="cart-actions.php" class="cart-qty-form">
                                <input type="hidden" name="action" value="increase">
                                <input type="hidden" name="product_id" value="<?php echo (int)$index; ?>">
                                <input type="hidden" name="ajax" value="1">
                                <button type="submit">+</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="cart-bar-summary">

        <div id="mini-cart-tekst-box">
            <p class="cart-total-label">TOTAL :</p>
            <p class="cart-total-price"><?php echo $fmt->formatCurrency((float)$total, 'EUR'); ?></p>
            <p class="cart-total-items">ITEMS :<br><?php echo $itemCount; ?></p>
        </div>
        <a href="cart.php" class="cart-view-btn">VIEW ORDER</a>
    </div>
</div>

<script>
    (function() {
        if (window.cartFooterAjaxBound) return;
        window.cartFooterAjaxBound = true;

        document.addEventListener('submit', function(e) {
            const form = e.target;

            if (!form.classList.contains('cart-qty-form')) {
                return;
            }

            e.preventDefault();

            const formData = new FormData(form);

            fetch('cart-actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    let data;

                    try {
                        data = JSON.parse(text);
                    } catch (error) {
                        console.error('Geen geldige JSON ontvangen van cart-actions.php');
                        console.error(text);
                        return;
                    }

                    if (!data.success) {
                        console.error('Cart update niet gelukt:', data);
                        return;
                    }

                    return fetch('includes/cart-footer.php', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                })
                .then(response => {
                    if (!response) return null;
                    return response.text();
                })
                .then(html => {
                    if (!html) return;

                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html;

                    const newCartBar = wrapper.querySelector('.cart-bar');
                    const currentCartBar = document.querySelector('.cart-bar');

                    if (newCartBar && currentCartBar) {
                        currentCartBar.replaceWith(newCartBar);
                    }

                    return fetch('cart-actions.php?action=count&ajax=1');
                })
                .then(response => {
                    if (!response) return null;
                    return response.text();
                })
                .then(text => {
                    if (!text) return;

                    let data;

                    try {
                        data = JSON.parse(text);
                    } catch (error) {
                        console.error('Geen geldige JSON ontvangen voor badge count');
                        console.error(text);
                        return;
                    }

                    const badge = document.querySelector('.cart-badge');

                    if (badge) {
                        if (data.itemCount > 0) {
                            badge.textContent = data.itemCount;
                        } else {
                            badge.remove();
                        }
                    } else if (data.itemCount > 0) {
                        const cartLink = document.querySelector('#winkelmandje-link');
                        if (cartLink) {
                            const span = document.createElement('span');
                            span.className = 'cart-badge';
                            span.textContent = data.itemCount;
                            cartLink.appendChild(span);
                        }
                    }
                })
                .catch(error => {
                    console.error('Cart update failed:', error);
                });
        });
    })();
</script>