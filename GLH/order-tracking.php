<?php
require_once 'config/db.php';
include 'includes/header.php';

$order = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)$_POST['order_id'];
    $stmt = $pdo->prepare("SELECT * FROM `Order` WHERE OrderID = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        $error = 'Order not found. Please check the order number.';
    }
}
?>

<main>
    <div class="container">
        <div class="form-container">
            <h2>Track Your Order</h2>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="order_id">Order Number</label>
                    <input type="number" id="order_id" name="order_id" placeholder="e.g. 122" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Track Order</button>
            </form>

            <?php if ($order): ?>
                <div class="success-message" style="margin-top: 20px;">
                    <p><strong>Order #<?php echo $order['OrderID']; ?></strong></p>
                    <p>Status: <strong><?php echo $order['OrderStatus']; ?></strong></p>
                    <p>Delivery: <?php echo $order['DeliveryType']; ?></p>
                    <p>Total: £<?php echo number_format($order['TotalPrice'], 2); ?></p>
                    <p>Date: <?php echo date('d/m/Y H:i', strtotime($order['OrderDate'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>