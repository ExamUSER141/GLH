<?php
require_once 'config/db.php';

if (!isset($_SESSION['trolley']) || empty($_SESSION['trolley'])) {
    header('Location: /GLH/products.php');
    exit;
}

$trolley = $_SESSION['trolley'];
$subtotal = 0;

foreach ($trolley as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$deliveryFee = 0;
$deliveryType = isset($_SESSION['delivery_type']) ? $_SESSION['delivery_type'] : 'Collection';
if ($deliveryType === 'Delivery') {
    $deliveryFee = 1.00;
}

$discount = 0;
if (isset($_SESSION['user_id']) && isset($_SESSION['loyalty_points']) && $_SESSION['loyalty_points'] >= 100) {
    $discount = $subtotal * 0.10;
}

$total = $subtotal + $deliveryFee - $discount;

$customerId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Create order
$stmt = $pdo->prepare("INSERT INTO `Order` (CustomerID, DeliveryType, TotalPrice) VALUES (?, ?, ?)");
$stmt->execute([$customerId, $deliveryType, $total]);
$orderId = $pdo->lastInsertId();

// Insert order items and reduce stock
foreach ($trolley as $productId => $item) {
    $stmt = $pdo->prepare("INSERT INTO OrderItem (OrderID, ProductID, Quantity, Price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$orderId, $productId, $item['quantity'], $item['price']]);

    // Reduce stock
    $stmt = $pdo->prepare("UPDATE Product SET StockLevel = StockLevel - ? WHERE ProductID = ?");
    $stmt->execute([$item['quantity'], $productId]);
}

// Award loyalty points if logged in
if ($customerId) {
    $pointsEarned = floor($total * 10); // 10 points per £1
    $stmt = $pdo->prepare("UPDATE Customer SET LoyaltyPoints = LoyaltyPoints + ? WHERE CustomerID = ?");
    $stmt->execute([$pointsEarned, $customerId]);

    $stmt = $pdo->prepare("INSERT INTO LoyaltyPoints (CustomerID, Points) VALUES (?, ?)");
    $stmt->execute([$customerId, $pointsEarned]);

    // Deduct points if used
    if ($discount > 0) {
        $stmt = $pdo->prepare("UPDATE Customer SET LoyaltyPoints = LoyaltyPoints - 100 WHERE CustomerID = ?");
        $stmt->execute([$customerId]);

        $stmt = $pdo->prepare("INSERT INTO LoyaltyPoints (CustomerID, Points) VALUES (?, ?)");
        $stmt->execute([$customerId, -100]);
    }

    // Refresh session
    $stmt = $pdo->prepare("SELECT LoyaltyPoints FROM Customer WHERE CustomerID = ?");
    $stmt->execute([$customerId]);
    $_SESSION['loyalty_points'] = $stmt->fetchColumn();
}

// Clear trolley
unset($_SESSION['trolley']);
unset($_SESSION['delivery_type']);

include 'includes/header.php';
?>

<main>
    <div class="container" style="text-align: center; padding: 60px 0;">
        <h1 style="color: #266426;">✅ Order Confirmed!</h1>
        <p style="font-size: 20px; margin: 20px 0;">Your order <strong>#<?php echo $orderId; ?></strong> has been placed successfully.</p>
        <p>Total paid: <strong>£<?php echo number_format($total, 2); ?></strong></p>

        <?php if ($customerId): ?>
            <p style="margin-top: 15px;">🎉 You earned <strong><?php echo floor($total * 10); ?></strong> loyalty points!</p>
            <a href="/GLH/account.php" class="btn btn-secondary" style="margin-top: 20px;">View My Account</a>
        <?php endif; ?>

        <br>
        <a href="/GLH/products.php" class="btn btn-primary" style="margin-top: 20px;">Continue Shopping</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>