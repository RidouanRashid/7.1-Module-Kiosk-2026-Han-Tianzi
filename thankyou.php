<?php
include("includes/header.php");
include("includes/connection.php");

$pickupNumber = $_SESSION['last_pickup_number'] ?? 0;
$orderItems   = $_SESSION['last_order_items'] ?? [];
$orderTotal   = $_SESSION['last_order_total'] ?? 0;

// Nieuwe order op "bereiden" laten staan
if (!empty($_SESSION['last_order_id']) && $conn instanceof PDO) {
    $stmt = $conn->prepare("UPDATE orders SET order_status_id = 1 WHERE order_id = :id");
    $stmt->execute([':id' => $_SESSION['last_order_id']]);
}

// Build receipt data for JavaScript
$receiptData = [
    'pickupNumber' => (int)$pickupNumber,
    'items' => [],
    'total' => (float)$orderTotal,
];

foreach ($orderItems as $item) {
    $receiptData['items'][] = [
        'name'  => $item['name'],
        'qty'   => (int)$item['qty'],
        'price' => (float)$item['price'],
    ];
}

// Clear order session data
unset(
    $_SESSION['last_order_id'],
    $_SESSION['last_pickup_number'],
    $_SESSION['last_order_items'],
    $_SESSION['last_order_total']
);
?>

<body>
    <div id="achtergrond-thankyou">
        <?php include("includes/topbar-orderscherm.php"); ?>

        <div class="page-content page-content--thankyou">
            <div class="mock-info-panel mock-info-panel--thankyou">
                <p class="mock-info-title">Your order number</p>
                <p id="order-number-display" class="order-number-display">
                    #<?php echo (int)$pickupNumber; ?>
                </p>
                <p class="thankyou-text">Thank you for ordering, see you next time!</p>

                <div id="print-status" class="print-status"></div>

                <button id="print-receipt-btn" class="mock-btn mock-btn--confirm print-bon-btn" type="button">
                    <i class="fa-solid fa-print"></i> PRINT BON
                </button>

                <p class="countdown-text">
                    Nieuwe bestelling in <span id="countdown-value">15</span>s
                </p>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const receiptData = <?php echo json_encode($receiptData, JSON_UNESCAPED_UNICODE); ?>;

            const printBtn = document.getElementById('print-receipt-btn');
            const printStatus = document.getElementById('print-status');

            const PRINTER_VENDORS = [
                0x0483, // STM Microelectronics (Xprinter)
                0x04b8, // Seiko Epson
                0x0456, // Microtek
                0x067b, // Prolific Technology
                0x1a86, // QinHeng Electronics (CH340)
                0x0525, // Netchip Technology
                0x0dd4, // Custom Engineering
                0x0fe6, // ICS Electronics
            ];

            function setStatus(msg, type) {
                printStatus.textContent = msg;
                printStatus.className = 'print-status';
                if (type) {
                    printStatus.classList.add('print-status--' + type);
                }
            }

            function buildReceipt(data) {
                const ESC = '\x1B';
                const GS = '\x1D';
                const INIT = ESC + '\x40';
                const CENTER = ESC + '\x61\x01';
                const LEFT = ESC + '\x61\x00';
                const BOLD_ON = ESC + '\x45\x01';
                const BOLD_OFF = ESC + '\x45\x00';
                const DOUBLE_ON = GS + '\x21\x11';
                const DOUBLE_OFF = GS + '\x21\x00';
                const CUT = GS + '\x56\x00';
                const SEP = '----------------------------------------';

                const now = new Date();
                const datum = now.toLocaleDateString('nl-NL', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
                const tijd = now.toLocaleTimeString('nl-NL', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                let r = INIT + '\n';

                r += CENTER + BOLD_ON + DOUBLE_ON;
                r += 'HAPPY HERBIVORE\n';
                r += DOUBLE_OFF + BOLD_OFF + '\n';

                r += CENTER + BOLD_ON + 'Order #' + data.pickupNumber + '\n' + BOLD_OFF;
                r += datum + '  ' + tijd + '\n';
                r += LEFT + SEP + '\n';

                data.items.forEach(function(item) {
                    const name = item.qty + 'x ' + item.name;
                    const price = 'EUR ' + (item.price * item.qty).toFixed(2);
                    const pad = Math.max(1, 40 - name.length - price.length);
                    r += name + ' '.repeat(pad) + price + '\n';
                });

                r += SEP + '\n' + BOLD_ON;
                const tl = 'TOTAAL:';
                const tp = 'EUR ' + data.total.toFixed(2);
                r += tl + ' '.repeat(Math.max(1, 40 - tl.length - tp.length)) + tp + '\n';
                r += BOLD_OFF + SEP + '\n\n';

                const qrContent = 'ORDER-' + data.pickupNumber;

                r += CENTER;
                r += 'Afhaalnummer: #' + data.pickupNumber + '\n';
                r += 'Bewaar deze bon goed\n\n';
                r += 'QR: ' + qrContent + '\n\n';
                r += LEFT;

                r += 'Bedankt voor je bestelling!\n\n\n';
                r += CUT;

                return new TextEncoder().encode(r);
            }

            async function printReceipt() {
                if (!('usb' in navigator)) {
                    setStatus('WebUSB wordt niet ondersteund in deze browser.', 'error');
                    return;
                }

                try {
                    setStatus('Printer verbinden...', 'info');

                    const device = await navigator.usb.requestDevice({
                        filters: PRINTER_VENDORS.map(vendorId => ({
                            vendorId
                        }))
                    });

                    await device.open();

                    if (device.configuration === null) {
                        await device.selectConfiguration(1);
                    }

                    await device.claimInterface(0);

                    const data = buildReceipt(receiptData);
                    await device.transferOut(1, data);

                    setStatus('Bon succesvol geprint.', 'success');
                } catch (error) {
                    console.error(error);
                    setStatus('Printen mislukt of geannuleerd.', 'error');
                }
            }

            if (printBtn) {
                printBtn.addEventListener('click', printReceipt);
            }

            const countdownValue = document.getElementById('countdown-value');
            let seconds = 15;

            const timer = setInterval(function() {
                seconds--;

                if (countdownValue) {
                    countdownValue.textContent = seconds;
                }

                if (seconds <= 0) {
                    clearInterval(timer);
                    window.location.href = "index.php";
                }
            }, 1000);
        })();
    </script>
</body>