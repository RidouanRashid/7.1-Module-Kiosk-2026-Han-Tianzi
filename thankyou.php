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

        // ── Column width for 80mm thermal printer (42 chars at Font A) ──
        const COL = 42;

        // ── Pad a line: left-text .... right-text ──
        function padLine(left, right, width) {
            const gap = Math.max(1, width - left.length - right.length);
            return left + ' '.repeat(gap) + right;
        }

        // ── Load image and convert to monochrome ESC/POS raster bytes ──
        function loadLogoRaster(src, maxWidth) {
            return new Promise(function(resolve) {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = function() {
                    // Scale to maxWidth keeping aspect ratio
                    const scale = Math.min(1, maxWidth / img.width);
                    const w = Math.floor(img.width * scale);
                    const h = Math.floor(img.height * scale);

                    const canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    // White background for transparency
                    ctx.fillStyle = '#fff';
                    ctx.fillRect(0, 0, w, h);
                    ctx.drawImage(img, 0, 0, w, h);

                    const imageData = ctx.getImageData(0, 0, w, h).data;

                    // ESC/POS raster: width must be multiple of 8
                    const byteWidth = Math.ceil(w / 8);
                    const raster = new Uint8Array(byteWidth * h);

                    for (let y = 0; y < h; y++) {
                        for (let x = 0; x < w; x++) {
                            const idx = (y * w + x) * 4;
                            const gray = imageData[idx] * 0.299 + imageData[idx+1] * 0.587 + imageData[idx+2] * 0.114;
                            // Threshold: dark pixels become 1 (black ink)
                            if (gray < 128) {
                                const bytePos = y * byteWidth + Math.floor(x / 8);
                                raster[bytePos] |= (0x80 >> (x % 8));
                            }
                        }
                    }

                    resolve({ bytes: raster, byteWidth: byteWidth, height: h });
                };
                img.onerror = function() {
                    resolve(null); // fallback: no logo
                };
                img.src = src;
            });
        }

        // ── Build GS v 0 raster bit-image command ──
        function rasterImageCmd(raster) {
            // GS v 0  m  xL xH  yL yH  d1...dk
            const xL = raster.byteWidth & 0xFF;
            const xH = (raster.byteWidth >> 8) & 0xFF;
            const yL = raster.height & 0xFF;
            const yH = (raster.height >> 8) & 0xFF;
            const header = new Uint8Array([0x1D, 0x76, 0x30, 0x00, xL, xH, yL, yH]);
            const combined = new Uint8Array(header.length + raster.bytes.length);
            combined.set(header, 0);
            combined.set(raster.bytes, header.length);
            return combined;
        }

        // ── Encode string to Uint8Array ──
        const encoder = new TextEncoder();
        function strBytes(s) { return encoder.encode(s); }

        // ── Concatenate Uint8Arrays ──
        function concatBytes(arrays) {
            let total = 0;
            arrays.forEach(function(a) { total += a.length; });
            const result = new Uint8Array(total);
            let offset = 0;
            arrays.forEach(function(a) {
                result.set(a, offset);
                offset += a.length;
            });
            return result;
        }

        // ── Preload the dino logo ──
        let logoRasterPromise = loadLogoRaster('assets/logo/logo_big_dinosaur_transparent.webp', 200);

        // ── Build ESC/POS receipt (async for image loading) ──
        async function buildReceipt(data) {
            const ESC  = '\x1B';
            const GS   = '\x1D';
            const INIT = ESC + '\x40';
            const CENTER = ESC + '\x61\x01';
            const LEFT   = ESC + '\x61\x00';
            const BOLD_ON  = ESC + '\x45\x01';
            const BOLD_OFF = ESC + '\x45\x00';
            const DOUBLE_ON  = GS + '\x21\x11';
            const DOUBLE_OFF = GS + '\x21\x00';
            const CUT    = GS + '\x56\x00';
            const FEED3  = ESC + '\x64\x03';
            const SEP = '------------------------------------------';

            const now   = new Date();
            const datum = now.toLocaleDateString('nl-NL', { day:'2-digit', month:'2-digit', year:'numeric' });
            const tijd  = now.toLocaleTimeString('nl-NL', { hour:'2-digit', minute:'2-digit' });

            const parts = [];

            parts.push(strBytes(INIT));

            // ── Dino logo (raster image) ──
            parts.push(strBytes(CENTER));
            const logoRaster = await logoRasterPromise;
            if (logoRaster) {
                parts.push(rasterImageCmd(logoRaster));
                parts.push(strBytes('\n'));
            }

            // ── Brand name ──
            parts.push(strBytes(CENTER + BOLD_ON + DOUBLE_ON));
            parts.push(strBytes('HAPPY HERBIVORE\n'));
            parts.push(strBytes(DOUBLE_OFF + BOLD_OFF + '\n'));

            // ── Separator ──
            parts.push(strBytes(LEFT + SEP + '\n'));

            // ── Order number (large) ──
            parts.push(strBytes(CENTER + BOLD_ON + DOUBLE_ON));
            parts.push(strBytes('ORDER #' + data.pickupNumber + '\n'));
            parts.push(strBytes(DOUBLE_OFF + BOLD_OFF));

            // ── Date / time ──
            parts.push(strBytes(CENTER));
            parts.push(strBytes(datum + '  |  ' + tijd + '\n'));
            parts.push(strBytes(LEFT + SEP + '\n\n'));

            // ── Column headers ──
            let r = '';
            r += BOLD_ON;
            r += padLine('ITEM', 'PRIJS', COL) + '\n';
            r += BOLD_OFF;
            r += LEFT + SEP + '\n';

            // ── Items ──
            data.items.forEach(function(item) {
                const lineTotal = item.price * item.qty;
                const qtyLabel  = item.qty + 'x';
                const nameStr   = ' ' + item.name;
                const priceStr  = 'EUR ' + lineTotal.toFixed(2);

                r += padLine(qtyLabel + nameStr, priceStr, COL) + '\n';

                if (item.qty > 1) {
                    const unitNote = '   @ EUR ' + item.price.toFixed(2) + ' per stuk';
                    r += unitNote + '\n';
                }
            });

            // ── Subtotal / Total ──
            r += LEFT + SEP + '\n';
            r += BOLD_ON;
            r += padLine('TOTAAL', 'EUR ' + data.total.toFixed(2), COL) + '\n';
            r += BOLD_OFF;
            r += LEFT + SEP + '\n';

            // ── Payment confirmation ──
            r += '\n' + CENTER;
            r += 'BETAALD - PIN\n\n';

            parts.push(strBytes(r));

            // ── QR code with discount link ──
            const discountCode = 'HH10-' + data.pickupNumber;
            const qrContent = 'https://u240073.gluwebsite.nl/kiosk?code=' + discountCode;
            parts.push(strBytes(CENTER));
            parts.push(strBytes(buildQR(GS, qrContent)));

            // ── QR explanation ──
            let qrText = '';
            qrText += CENTER + '\n';
            qrText += BOLD_ON + 'Scan voor 10% korting\n' + BOLD_OFF;
            qrText += 'op je volgende bestelling!\n';
            qrText += 'Code: ' + discountCode + '\n\n';

            // ── Footer ──
            qrText += SEP + '\n\n';
            qrText += BOLD_ON + 'Bedankt voor uw bezoek!\n' + BOLD_OFF;
            qrText += 'Laat deze bon zien bij het afhalen.\n\n';
            qrText += '~ 100% Plant-Based Goodness ~\n';
            qrText += 'u240073.gluwebsite.nl/kiosk\n';
            qrText += '\n\n\n\n';
            qrText += FEED3;
            qrText += CUT;
            parts.push(strBytes(qrText));

            return concatBytes(parts);
        }

        // ── Build QR code ESC/POS commands ──
        function buildQR(GS, content) {
            const data = new TextEncoder().encode(content);
            const len = data.length;
            let q = '';

            // QR model: Model 2
            q += GS + '\x28\x6B\x04\x00\x31\x41\x32\x00';
            // QR size: module size 6
            q += GS + '\x28\x6B\x03\x00\x31\x43\x06';
            // QR error correction: Level M (48 = '0')
            q += GS + '\x28\x6B\x03\x00\x31\x45\x31';
            // Store QR data
            const storeLen = len + 3;
            const pL = storeLen & 0xFF;
            const pH = (storeLen >> 8) & 0xFF;
            q += GS + '\x28\x6B' + String.fromCharCode(pL) + String.fromCharCode(pH) + '\x31\x50\x30' + content;
            // Print QR
            q += GS + '\x28\x6B\x03\x00\x31\x51\x30';

            return q;
        }

        // ── Send ESC/POS binary data to a USB printer ──
        async function sendToPrinter(printer, data) {
            await printer.open();
            if (printer.configuration === null) {
                await printer.selectConfiguration(1);
            }

            // data is already a Uint8Array from buildReceipt
            const bytes = (data instanceof Uint8Array) ? data : encoder.encode(data);
            const intf = printer.configuration.interfaces[0].alternates[0];
            const ep = intf.endpoints.find(e => e.direction === 'out');

            // Send in chunks of 4096 bytes to avoid USB buffer overflow
            const CHUNK = 4096;
            for (let i = 0; i < bytes.length; i += CHUNK) {
                const chunk = bytes.slice(i, Math.min(i + CHUNK, bytes.length));
                if (ep) {
                    await printer.transferOut(ep.endpointNumber, chunk);
                } else {
                    await printer.transferOut(1, chunk);
                }
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
                    const receipt = await buildReceipt(receiptData);
                    await sendToPrinter(printer, receipt);
                    setStatus('✓ Bon geprint!', 'success');
                    printBtn.style.display = 'none';
                    return true;
                }
            } catch (e) {
                console.warn('Auto-print failed:', e);
            }

            async function printReceipt() {
                if (!('usb' in navigator)) {
                    setStatus('WebUSB wordt niet ondersteund in deze browser.', 'error');
                    return;
                }

                try {
                    setStatus('Printer verbinden...', 'info');

                    if (printer) {
                        setStatus('Bon wordt geprint...', 'loading');
                        const receipt = await buildReceipt(receiptData);
                        await sendToPrinter(printer, receipt);
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

            // Method 2: Fallback to network print (PHP backend)
            try {
                setStatus('Netwerk print poging...', 'loading');
                const resp = await fetch('xprint.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: await (async function() {
                        const receipt = await buildReceipt(receiptData);
                        // Convert Uint8Array to base64 for JSON transport
                        let binary = '';
                        for (let i = 0; i < receipt.length; i++) binary += String.fromCharCode(receipt[i]);
                        return JSON.stringify({ action: 'print', receipt: btoa(binary) });
                    })()
                });
                const result = await resp.json();
                if (result.success) {
                    setStatus('✓ Bon geprint via netwerk!', 'success');
                    printBtn.style.display = 'none';
                    return;
                } else {
                    setStatus('Netwerk print fout: ' + (result.error || 'onbekend'), 'error');
                }
            }

            // Method 3: Browser print dialog as last resort
            setStatus('Printer niet gevonden. Probeer browser print...', 'error');
            const printWindow = window.open('', '_blank', 'width=320,height=700');
            if (printWindow) {
                const d = receiptData;
                const now = new Date();
                const datum = now.toLocaleDateString('nl-NL', { day:'2-digit', month:'2-digit', year:'numeric' });
                const tijd  = now.toLocaleTimeString('nl-NL', { hour:'2-digit', minute:'2-digit' });

                let itemsHtml = '';
                d.items.forEach(function(item) {
                    const lineTotal = (item.price * item.qty).toFixed(2);
                    itemsHtml += '<tr>'
                        + '<td style="text-align:left">' + item.qty + 'x ' + item.name + '</td>'
                        + '<td style="text-align:right;white-space:nowrap">EUR ' + lineTotal + '</td>'
                        + '</tr>';
                    if (item.qty > 1) {
                        itemsHtml += '<tr><td colspan="2" style="text-align:left;font-size:10px;color:#666;padding-left:18px">@ EUR ' + item.price.toFixed(2) + ' per stuk</td></tr>';
                    }
                });

                const html = '<!DOCTYPE html><html><head><title>Bon</title>'
                    + '<style>'
                    + 'body{font-family:"Courier New",monospace;font-size:13px;width:280px;margin:0 auto;padding:16px;color:#222;}'
                    + '.logo{text-align:center;margin-bottom:8px;}'
                    + '.logo img{width:120px;height:auto;}'
                    + '.brand{text-align:center;font-weight:bold;font-size:16px;letter-spacing:2px;margin:4px 0 2px;}'
                    + '.sub{text-align:center;font-size:11px;margin-bottom:8px;}'
                    + '.sep{border:none;border-top:1px dashed #333;margin:8px 0;}'
                    + '.order{text-align:center;font-size:22px;font-weight:bold;margin:6px 0 2px;}'
                    + '.datetime{text-align:center;font-size:11px;margin-bottom:4px;}'
                    + 'table{width:100%;border-collapse:collapse;}'
                    + 'th{text-align:left;font-size:11px;border-bottom:1px solid #333;padding:2px 0;}'
                    + 'th:last-child{text-align:right;}'
                    + 'td{padding:3px 0;font-size:12px;vertical-align:top;}'
                    + '.total td{font-weight:bold;border-top:1px solid #333;padding-top:6px;}'
                    + '.footer{text-align:center;font-size:11px;margin-top:12px;}'
                    + '.footer b{display:block;margin-bottom:2px;}'
                    + '@media print{body{margin:0;padding:8px;}}'
                    + '</style></head><body>'
                    + '<div class="logo"><img src="assets/logo/logo_big_dinosaur_transparent.webp" alt="Happy Herbivore"></div>'
                    + '<div class="brand">HAPPY HERBIVORE</div>'
                    + '<div class="sub">100% Plant-Based Goodness</div>'
                    + '<hr class="sep">'
                    + '<div class="order">ORDER #' + d.pickupNumber + '</div>'
                    + '<div class="datetime">' + datum + '  |  ' + tijd + '</div>'
                    + '<hr class="sep">'
                    + '<table><thead><tr><th>ITEM</th><th>PRIJS</th></tr></thead>'
                    + '<tbody>' + itemsHtml
                    + '<tr class="total"><td>TOTAAL</td><td style="text-align:right">EUR ' + d.total.toFixed(2) + '</td></tr>'
                    + '</tbody></table>'
                    + '<hr class="sep">'
                    + '<div class="footer"><b>Bedankt voor uw bezoek!</b>Laat deze bon zien bij het afhalen.<br><br>~ www.happyherbivore.nl ~</div>'
                    + '</body></html>';

                printWindow.document.write(html);
                printWindow.document.close();
                printWindow.print();
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