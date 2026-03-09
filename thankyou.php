<?php
include("includes/header.php");
include("includes/connection.php");

$pickupNumber = $_SESSION['last_pickup_number'] ?? 0;
$orderItems   = $_SESSION['last_order_items'] ?? [];
$orderTotal   = $_SESSION['last_order_total'] ?? 0;

// Update order status to "paid" (status 2) if we have order info
if (!empty($_SESSION['last_order_id']) && $conn instanceof PDO) {
    $stmt = $conn->prepare("UPDATE orders SET order_status_id = 2 WHERE order_id = :id");
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
unset($_SESSION['last_order_id'], $_SESSION['last_pickup_number'], $_SESSION['last_order_items'], $_SESSION['last_order_total']);
?>

<body>
    <div id="achtergrond-thankyou">
        <?php include("includes/topbar-orderscherm.php"); ?>
        <div class="page-content page-content--thankyou">
                    <div class="mock-info-panel mock-info-panel--thankyou">
                        <p class="mock-info-title">Your order number</p>
                        <p id="order-number-display" class="order-number-display">#<?php echo (int)$pickupNumber; ?></p>
                        <p class="thankyou-text">Thank you for ordering, see you next time!</p>
                        <div id="print-status" class="print-status"></div>
                        <button id="print-receipt-btn" class="mock-btn mock-btn--confirm print-bon-btn" type="button">
                            <i class="fa-solid fa-print"></i> PRINT BON
                        </button>
                        <p class="countdown-text">Nieuwe bestelling in <span id="countdown-value">15</span>s</p>
                    </div>
                </div>
            </div>

    <script>
    (function() {
        // ── Receipt data from PHP ──
        const receiptData = <?php echo json_encode($receiptData, JSON_UNESCAPED_UNICODE); ?>;

        const printBtn = document.getElementById('print-receipt-btn');
        const printStatus = document.getElementById('print-status');

        // ── Printer vendor IDs (Xprinter, Epson, etc.) ──
        const PRINTER_VENDORS = [
            0x0483, // STM Microelectronics (Xprinter)
            0x04b8, // Seiko Epson
            0x0456, // Microtek
            0x067b, // Prolific Technology
            0x1a86, // QinHeng Electronics (CH340 - common in cheap printers)
            0x0525, // Netchip Technology
            0x0dd4, // Custom Engineering
            0x0fe6, // ICS Electronics (some thermal printers)
        ];

        function setStatus(msg, type) {
            printStatus.textContent = msg;
            printStatus.className = 'print-status';
            if (type) printStatus.classList.add('print-status--' + type);
        }

        // ── Build ESC/POS receipt ──
        function buildReceipt(data) {
            const ESC  = '\x1B';
            const GS   = '\x1D';
            const INIT = ESC + '\x40';
            const CENTER = ESC + '\x61\x01';
            const LEFT   = ESC + '\x61\x00';
            const BOLD_ON  = ESC + '\x45\x01';
            const BOLD_OFF = ESC + '\x45\x00';
            const CUT    = GS + '\x56\x00';
            const SEP = '----------------------------------------';

            let r = INIT + '\n';
            r += CENTER + BOLD_ON + 'HAPPY HERBIVORE\n' + BOLD_OFF + '\n';
            r += BOLD_ON + 'Order #' + data.pickupNumber + '\n' + BOLD_OFF;
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
            r += CENTER + 'Bedankt voor uw bezoek!\n\n\n\n';
            r += CUT;
            return r;
        }

        // ── Send ESC/POS data to a USB printer ──
        async function sendToPrinter(printer, data) {
            await printer.open();
            if (printer.configuration === null) {
                await printer.selectConfiguration(1);
            }
            try { await printer.claimInterface(0); } catch(e) { /* already claimed */ }

            const encoded = new TextEncoder().encode(data);
            const intf = printer.configuration.interfaces[0].alternates[0];
            const ep = intf.endpoints.find(e => e.direction === 'out');

            if (ep) {
                await printer.transferOut(ep.endpointNumber, encoded);
            } else {
                await printer.transferOut(1, encoded);
            }
            setTimeout(() => { try { printer.close(); } catch(e){} }, 1000);
        }

        // ── Try auto-print with already authorized device (no user gesture needed) ──
        async function tryAutoPrint() {
            if (!navigator.usb) return false;
            try {
                const devices = await navigator.usb.getDevices();
                const printer = devices.find(d => PRINTER_VENDORS.includes(d.vendorId));
                if (printer) {
                    setStatus('Bon wordt geprint...', 'loading');
                    await sendToPrinter(printer, buildReceipt(receiptData));
                    setStatus('✓ Bon geprint!', 'success');
                    printBtn.style.display = 'none';
                    return true;
                }
            } catch (e) {
                console.warn('Auto-print failed:', e);
            }
            return false;
        }

        // ── Manual print (button click - has user gesture) ──
        async function manualPrint() {
            printBtn.disabled = true;
            printBtn.textContent = 'BEZIG...';

            // Method 1: Try WebUSB
            if (navigator.usb) {
                try {
                    // First check already-authorized devices
                    const devices = await navigator.usb.getDevices();
                    let printer = devices.find(d => PRINTER_VENDORS.includes(d.vendorId));

                    // If none, prompt user to select (works because we have user gesture)
                    if (!printer) {
                        setStatus('Selecteer uw printer in het popup-venster...', 'loading');
                        printer = await navigator.usb.requestDevice({
                            filters: PRINTER_VENDORS.map(v => ({ vendorId: v }))
                        });
                    }

                    if (printer) {
                        setStatus('Bon wordt geprint...', 'loading');
                        await sendToPrinter(printer, buildReceipt(receiptData));
                        setStatus('✓ Bon geprint!', 'success');
                        printBtn.style.display = 'none';
                        return;
                    }
                } catch (e) {
                    console.warn('WebUSB print failed:', e);
                    if (e.name === 'NotFoundError') {
                        setStatus('Geen printer geselecteerd', 'error');
                    } else {
                        setStatus('USB fout: ' + e.message, 'error');
                    }
                }
            }

            // Method 2: Fallback to network print (PHP backend)
            try {
                setStatus('Netwerk print poging...', 'loading');
                const resp = await fetch('xprint.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'print',
                        receipt: buildReceipt(receiptData)
                    })
                });
                const result = await resp.json();
                if (result.success) {
                    setStatus('✓ Bon geprint via netwerk!', 'success');
                    printBtn.style.display = 'none';
                    return;
                } else {
                    setStatus('Netwerk print fout: ' + (result.error || 'onbekend'), 'error');
                }
            } catch (e) {
                console.warn('Network print failed:', e);
            }

            // Method 3: Browser print dialog as last resort
            setStatus('Printer niet gevonden. Probeer browser print...', 'error');
            const printWindow = window.open('', '_blank', 'width=300,height=600');
            if (printWindow) {
                const receipt = buildReceipt(receiptData).replace(/\x1B[^a-zA-Z]*[a-zA-Z]/g, '').replace(/\x1D[^a-zA-Z]*[a-zA-Z]/g, '');
                printWindow.document.write('<html><head><title>Bon</title><style>body{font-family:monospace;font-size:12px;white-space:pre-wrap;width:280px;margin:0 auto;padding:20px;}@media print{body{margin:0;padding:0;}}</style></head><body>' + receipt.replace(/\n/g, '<br>') + '</body></html>');
                printWindow.document.close();
                printWindow.print();
            }

            printBtn.disabled = false;
            printBtn.innerHTML = '<i class="fa-solid fa-print"></i> PRINT BON';
        }

        // ── Button click handler ──
        if (printBtn) {
            printBtn.addEventListener('click', manualPrint);
        }

        // ── Auto-print on load for already-authorized printers ──
        if (receiptData.items.length > 0) {
            tryAutoPrint();
        } else {
            if (printBtn) printBtn.style.display = 'none';
        }

        // ── Countdown to redirect ──
        let countdown = 15;
        const countdownElement = document.getElementById('countdown-value');

        const timer = setInterval(function() {
            countdown -= 1;
            if (countdownElement) {
                countdownElement.textContent = String(Math.max(countdown, 0));
            }
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = 'index.php';
            }
        }, 1000);
    })();
    </script>
</body>
