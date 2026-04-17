<?php
require_once 'db.php';

$orderSubmitted = false;
$orderId = null;
$customer = '';
$items = [];
$totals = ['subtotal' => 0, 'discount' => 0, 'total' => 0];
$errors = [];
$quantities = [
    'Laptop' => 0,
    'Mobile' => 0,
    'Headphones' => 0,
];
$productList = [
    'Laptop' => [
        'price' => 25000,
        'label' => 'Laptop',
        'description' => 'High-performance laptop for work, media, and productivity.'
    ],
    'Mobile' => [
        'price' => 15000,
        'label' => 'Mobile',
        'description' => 'Fast, modern mobile experience for daily use.'
    ],
    'Headphones' => [
        'price' => 2500,
        'label' => 'Headphones',
        'description' => 'Comfortable audio for calls, music, and meetings.'
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer = trim($_POST['customer'] ?? '');
    $quantities = [
        'Laptop' => max(0, intval($_POST['laptop_qty'] ?? 0)),
        'Mobile' => max(0, intval($_POST['mobile_qty'] ?? 0)),
        'Headphones' => max(0, intval($_POST['headphone_qty'] ?? 0)),
    ];

    if ($customer === '') {
        $errors[] = 'Customer name is required.';
    }

    foreach ($productList as $product => $productData) {
        $quantity = $quantities[$product];
        if ($quantity > 0) {
            $subtotal = $quantity * $productData['price'];
            $items[] = [
                'name' => $productData['label'],
                'price' => $productData['price'],
                'qty' => $quantity,
                'subtotal' => $subtotal
            ];
            $totals['subtotal'] += $subtotal;
        }
    }

    if (empty($items)) {
        $errors[] = 'Please order at least one product.';
    }

    if (empty($errors)) {
        if ($totals['subtotal'] < 5000) {
            $totals['discount'] = round($totals['subtotal'] * 0.10);
        }
        $totals['total'] = $totals['subtotal'] - $totals['discount'];

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

        $orderSubmitted = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Product</title>
    <link rel="stylesheet" href="css/order.css">
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
            <a href="order.php" class="nav-link active">Order</a>
            <a href="invoice.php" class="nav-link">Invoice</a>
        </nav>
    </header>

    <main class="page-container">
        <section class="hero-panel">
            <div>
                <p class="eyebrow">Order & Billing</p>
                <h1>Place Your Order</h1>
                <p class="hero-copy">Enter your customer details and select products to generate your invoice instantly.</p>
            </div>
        </section>

        <section class="order-layout">
            <div class="order-panel">
                <div class="panel-header">
                    <h2>Place Your Order</h2>
                    <p>Select products, enter quantities, and submit the form to see your bill summary.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="order.php" class="order-form">
                    <div class="field-group">
                        <label>Customer Name</label>
                        <input type="text" name="customer" value="<?php echo htmlspecialchars($customer); ?>" placeholder="Enter customer name">
                    </div>

                    <div class="product-grid">
                        <div class="product-group">
                            <?php foreach ($productList as $product => $productData): ?>
                                <div class="product-card">
                                    <div class="product-card-header">
                                        <div class="product-copy">
                                            <div class="product-icon-wrap">
                                                <img src="images/<?php echo strtolower($product); ?>-icon.svg" alt="<?php echo htmlspecialchars($productData['label']); ?> icon">
                                            </div>
                                            <div>
                                                <h3><?php echo htmlspecialchars($productData['label']); ?></h3>
                                                <p class="product-type"><?php echo htmlspecialchars($productData['description']); ?></p>
                                            </div>
                                        </div>
                                        <span class="price-badge">Rs. <?php echo number_format($productData['price']); ?></span>
                                    </div>
                                    <div class="field-group">
                                        <label>Quantity</label>
                                        <input type="number" name="<?php echo strtolower($product); ?>_qty" min="0" value="<?php echo intval($quantities[$product]); ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <aside class="instruction-panel">
                            <div class="instruction-card">
                                <h3>Instructions</h3>
                                <ul>
                                    <li>Enter the customer name before submitting.</li>
                                    <li>Pick product quantities to calculate totals.</li>
                                    <li>A 10% discount is applied automatically on every order.</li>
                                    <li>Your bill summary will be generated after submission.</li>
                                </ul>
                            </div>
                        </aside>
                    </div>

                    <div class="action-row">
                        <button type="submit" class="btn btn-primary">Submit Order</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </div>

            <aside class="summary-panel">
                <div class="summary-header">
                    <span>Order Summary</span>
                    <p>Your final invoice details appear here after submission.</p>
                </div>

                <?php if ($orderSubmitted): ?>
                    <div class="summary-card">
                        <?php if ($orderId): ?>
                            <div class="order-note">Order saved as <strong>#<?php echo $orderId; ?></strong></div>
                        <?php endif; ?>
                        <div class="summary-meta">
                            <div><strong>Customer</strong><span><?php echo htmlspecialchars($customer); ?></span></div>
                            <div><strong>Date</strong><span><?php echo date('d M Y, h:i A'); ?></span></div>
                        </div>
                        <div class="summary-items">
                            <?php foreach ($items as $item): ?>
                                <div class="summary-item-row">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <p><?php echo $item['qty']; ?> x Rs. <?php echo number_format($item['price']); ?></p>
                                    </div>
                                    <span>Rs. <?php echo number_format($item['subtotal']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="summary-totals">
                            <div><span>Subtotal</span><span>Rs. <?php echo number_format($totals['subtotal']); ?></span></div>
                            <div><span>Discount</span><span>Rs. <?php echo number_format($totals['discount']); ?></span></div>
                            <div class="total-row"><span>Total</span><span>Rs. <?php echo number_format($totals['total']); ?></span></div>
                        </div>
                        <div class="success-note">Thank you for your order. Your bill has been generated.</div>
                    </div>
                <?php else: ?>
                    <div class="summary-card empty-state">
                        <p>Your order details will appear here after you submit the form.</p>
                    </div>
                <?php endif; ?>
            </aside>
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
