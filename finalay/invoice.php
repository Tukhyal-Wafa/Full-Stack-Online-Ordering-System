<?php
require_once 'db.php';

$customer = '';
$items = [];
$totals = ['subtotal' => 0, 'discount' => 0, 'total' => 0];
$invoiceReady = false;
$latestOrder = null;
$quantities = [
    'Laptop' => 0,
    'Mobile' => 0,
    'Headphones' => 0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer = trim($_POST['customer'] ?? '');
    $quantities = [
        'Laptop' => max(0, intval($_POST['laptop_qty'] ?? 0)),
        'Mobile' => max(0, intval($_POST['mobile_qty'] ?? 0)),
        'Headphones' => max(0, intval($_POST['headphone_qty'] ?? 0)),
    ];

    foreach ($quantities as $product => $qty) {
        if ($qty > 0) {
            $price = $product === 'Laptop' ? 25000 : ($product === 'Mobile' ? 15000 : 2500);
            $subtotal = $qty * $price;
            $items[] = ['name' => $product, 'qty' => $qty, 'price' => $price, 'subtotal' => $subtotal];
            $totals['subtotal'] += $subtotal;
        }
    }

    if ($totals['subtotal'] > 0) {
        $totals['discount'] = round($totals['subtotal'] * 0.10);
        $totals['total'] = $totals['subtotal'] - $totals['discount'];
        $invoiceReady = true;

        $stmt = $mysqli->prepare("INSERT INTO orders (customer, subtotal, discount, total) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('siii', $customer, $totals['subtotal'], $totals['discount'], $totals['total']);
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();

        $itemStmt = $mysqli->prepare("INSERT INTO order_items (order_id, product, qty, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $itemStmt->bind_param('isiii', $orderId, $item['name'], $item['qty'], $item['price'], $item['subtotal']);
            $itemStmt->execute();
        }
        $itemStmt->close();

        $latestOrder = [
            'id' => $orderId,
            'customer' => $customer,
            'created_at' => date('Y-m-d H:i:s'),
            'discount' => $totals['discount'],
            'total' => $totals['total']
        ];
    }
} else {
    $result = $mysqli->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $latestOrder = $result->fetch_assoc();
        $invoiceReady = true;
        $itemsResult = $mysqli->query("SELECT product, qty, price, subtotal FROM order_items WHERE order_id = " . intval($latestOrder['id']));
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
            $totals['subtotal'] += $item['subtotal'];
        }
        $totals['discount'] = $latestOrder['discount'];
        $totals['total'] = $latestOrder['total'];
        $customer = $latestOrder['customer'];
    }
}

$invoiceDate = isset($latestOrder['created_at'])
    ? date('d M Y, h:i A', strtotime($latestOrder['created_at']))
    : date('d M Y, h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <link rel="stylesheet" href="css/invoice.css">
</head>
<body>
    <header class="site-header">
        <div class="site-brand">Online Order System</div>
        <button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span aria-hidden="true">☰</span>
        </button>
        <nav class="site-nav" id="site-nav">
            <a href="index.php" class="nav-link">Home</a>
            <a href="features.php" class="nav-link">Features</a>
            <a href="order.php" class="nav-link">Order</a>
            <a href="invoice.php" class="nav-link active">Invoice</a>
        </nav>
    </header>

    <main class="invoice-page">
        <section class="invoice-card">
            <div class="invoice-intro">
                <span class="eyebrow">Generate Invoice</span>
                <div class="invoice-intro-header">
                    <span class="invoice-badge">📄</span>
                    <h1>Invoice Output</h1>
                </div>
                <p>Use the form below to create the invoice and review the bill details in a polished layout.</p>
            </div>

            <form method="post" action="invoice.php" class="invoice-form">
                <div class="field-group">
                    <label>Customer Name</label>
                    <input type="text" name="customer" value="<?php echo htmlspecialchars($customer); ?>" placeholder="Enter customer name">
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label>Quantity Laptop</label>
                        <input type="number" name="laptop_qty" min="0" value="<?php echo intval($quantities['Laptop']); ?>">
                    </div>
                    <div class="field-group">
                        <label>Quantity Mobile</label>
                        <input type="number" name="mobile_qty" min="0" value="<?php echo intval($quantities['Mobile']); ?>">
                    </div>
                    <div class="field-group">
                        <label>Quantity Headphones</label>
                        <input type="number" name="headphone_qty" min="0" value="<?php echo intval($quantities['Headphones']); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Generate Invoice</button>
            </form>

            <?php if ($invoiceReady): ?>
                <div class="invoice-result">
                    <div class="invoice-header">
                        <div>
                            <span>Customer</span>
                            <strong><?php echo htmlspecialchars($customer); ?></strong>
                        </div>
                        <div>
                            <span>Date</span>
                            <strong><?php echo htmlspecialchars($invoiceDate); ?></strong>
                        </div>
                    </div>
                    <table class="invoice-table">
                        <thead>
                            <tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo $item['qty']; ?></td>
                                    <td>Rs. <?php echo number_format($item['price']); ?></td>
                                    <td>Rs. <?php echo number_format($item['subtotal']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="invoice-totals">
                        <?php if (!empty($latestOrder['id'])): ?>
                            <div><span>Order ID</span><strong>#<?php echo intval($latestOrder['id']); ?></strong></div>
                        <?php endif; ?>
                        <div><span>Subtotal</span><strong>Rs. <?php echo number_format($totals['subtotal']); ?></strong></div>
                        <div><span>Discount</span><strong>Rs. <?php echo number_format($totals['discount']); ?></strong></div>
                        <div class="total-row"><span>Total Due</span><strong>Rs. <?php echo number_format($totals['total']); ?></strong></div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>

     <footer class="site-footer">
    <div class="footer-container">

        <div class="footer-branding">
            <div class="footer-logo">Online Order System</div>
            <p class="footer-copy">
                A complete web development practice site that blends PHP, HTML, and CSS for professional order and billing workflows.
            </p>
        </div>

        <div class="footer-links">
            <div>
                <h4>Quick Links</h4>
                <a href="index.php">Home</a>
                <a href="features.php">Features</a>
                <a href="order.php">Order</a>
                <a href="invoice.php">Invoice</a>
            </div>

            <div>
                <h4>Support</h4>
                <a href="#">Docs</a>
                <a href="#">Contact</a>
                <a href="#">Privacy</a>
            </div>
        </div>

        <div class="footer-note">
            Designed for modern product ordering with clean UI and responsive layout.
        </div>

    </div>

    <div class="footer-bottom">
        © 2026 Online Order System. Crafted with care for web development practice.
    </div>
</footer>
<script src="js/site.js" defer></script>
</body>
</html>
