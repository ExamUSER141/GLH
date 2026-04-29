
<?php
require_once 'config/db.php';
include 'includes/header.php';

$stockError = null;

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $productId = (int)($_POST['product_id'] ?? 0);
    $action    = $_POST['action'] ?? '';

    if ($productId > 0 && isset($_SESSION['trolley'][$productId])) {

        // Fetch stock limit from DB
    $stmt = $pdo->prepare("SELECT StockLevel FROM Product WHERE ProductID = ?");
    $stmt->execute([$productId]);
    $stockLevel = (int)$stmt->fetchColumn(); // if not found -> 0
        $currentQty = (int)$_SESSION['trolley'][$productId]['quantity'];
        $newQty = $currentQty;
        if ($action === 'increase') {
            if ($currentQty < $stockLevel) {
                $newQty = $currentQty + 1;
            } else {
                $stockError = "Stock limit reached (only {$stockLevel} available).";
            }
        } elseif ($action === 'decrease') {
            $newQty = $currentQty - 1;
        } elseif ($action === 'remove') {
            $newQty = 0;
        }

        // added extra validation never allow above stock
        if ($newQty > $stockLevel) {
            $newQty = $stockLevel;
            $stockError = "Stock limit reached (only {$stockLevel} available).";
        }

        if ($newQty <= 0) {
            unset($_SESSION['trolley'][$productId]);
        } else {
            $_SESSION['trolley'][$productId]['quantity'] = $newQty;
        }
    }
}

$trolley = isset($_SESSION['trolley']) ? $_SESSION['trolley'] : [];
$subtotal = 0;

foreach ($trolley as $item) {
$subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
}

$deliveryFee = 0;
if (isset($_SESSION['delivery_type']) && $_SESSION['delivery_type'] === 'Delivery') {
    $deliveryFee = 1.00;
}

// Loyalty discount uses exactly 100 points
$discount = 0;
$loyaltyUsed = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['loyalty_points']) && $_SESSION['loyalty_points'] >= 100) {
    $stmt = $pdo->prepare("UPDATE Customer SET LoyaltyPoints = LoyaltyPoints - 100 WHERE CustomerID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    $stmt = $pdo->prepare("SELECT LoyaltyPoints FROM Customer WHERE CustomerID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['loyalty_points'] = $stmt->fetchColumn();
    
    $discount = $subtotal * 0.10;
    $loyaltyUsed = true;
}

$total = $subtotal + $deliveryFee - $discount;
?>

<main>
    <div class="container">
        <h1 style="margin-top: 30px; color: #266426;">Shopping Trolley</h1>

        <?php if (!empty($stockError)): ?>
            <div style="margin: 15px 0; padding: 10px; border: 1px solid #b00020; color: #b00020; background:#fff5f5;">
                <?php echo htmlspecialchars($stockError); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($trolley)): ?>
            <p style="margin: 30px 0;">
                Your trolley is empty.
                <a href="/GLH/products.php" style="color: #642664;">Browse products</a>
            </p>
        <?php else: ?>

            <table class="trolley-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($trolley as $productId => $item): ?>
                        <?php
                            // Used to disable the button at the stock limit
                            $stmt = $pdo->prepare("SELECT StockLevel FROM Product WHERE ProductID = ?");
                            $stmt->execute([$productId]);
                            $stockLevel = (int)$stmt->fetchColumn();

                            $canIncrease = ((int)$item['quantity'] < $stockLevel);
                        ?>  

                        <tr>
                            <td data-label="Item"><?php echo htmlspecialchars($item['name'] ?? 'Unknown item'); ?></td>

                            <td data-label="Quantity">
                                <div class="quantity-controls">
                                    <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">
                                        <input type="hidden" name="action" value="decrease">
                                        <button type="submit" name="update_quantity">-</button>
                                    </form>

                                    <span>x<?php echo (int)$item['quantity']; ?></span>

                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">
                                        <input type="hidden" name="action" value="increase">
                                        <button type="submit" name="update_quantity" <?php echo $canIncrease ? '' : 'disabled'; ?>>+</button>
                                    </form>
                                </div>
                            </td>

                            <td data-label="Price">£<?php echo number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 2); ?></td>

                            <td data-label="Actions">
                                <form method="POST" action="">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" name="update_quantity" class="btn btn-secondary" style="padding: 5px 15px; font-size: 0.875rem;">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="trolley-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>£<?php echo number_format($subtotal, 2); ?></span>
                </div>

                <?php if ($loyaltyUsed): ?>
                    <div class="loyalty-section">
                        <div class="summary-row">
                            <span>🎉 Loyalty Points Used: 100 (<?php echo (int)($_SESSION['loyalty_points'] + 100); ?> remaining)</span>
                        </div>
                        <div class="summary-row">
                            <span>Discount (10%)</span>
                            <span>-£<?php echo number_format($discount, 2); ?></span>
                        </div>
                    </div>
                <?php elseif (isset($_SESSION['user_id'])): ?>
                    <div class="loyalty-section">
                        <p>You have <?php echo (int)($_SESSION['loyalty_points'] ?? 0); ?> points. Earn 100 to unlock 10% off!</p>
                    </div>
                <?php else: ?>
                    <div class="loyalty-section">
                        <p>🔒 <a href="/GLH/login.php" style="color: #642664;">Log in</a> to use loyalty points</p>
                    </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span><?php echo $_SESSION['delivery_type'] ?? 'Collection'; ?></span>
                    <span>£<?php echo number_format($deliveryFee, 2); ?></span>
                </div>

                <div class="summary-row total">
                    <span>TOTAL</span>
                    <span>£<?php echo number_format($total, 2); ?></span>
                </div>

                <a href="/GLH/checkout.php" class="btn btn-dark" style="width: 100%; margin-top: 15px; text-align: center;">
                    Checkout
                </a>
            </div>

        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
