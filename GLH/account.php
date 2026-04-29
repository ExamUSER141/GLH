<?php
require_once 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /GLH/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM Customer WHERE CustomerID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Get orders
$stmt = $pdo->prepare("SELECT o.*, c.Name as CustomerName FROM `Order` o JOIN Customer c ON o.CustomerID = c.CustomerID WHERE o.CustomerID = ? ORDER BY OrderDate DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>

<main>
    <div class="container account-section">
        <h1 style="color: #266426;">Welcome, <?php echo htmlspecialchars($user['Name']); ?>!</h1>

        <!-- Loyalty Points -->
        <div class="loyalty-display">
            <p class="points"><?php echo $user['LoyaltyPoints']; ?></p>
            <p class="points-label">Loyalty Points</p>
            <?php if ($user['LoyaltyPoints'] >= 100): ?>
                <p style="color: #266426; font-weight: bold; margin-top: 10px;">🎉 You can use your points for 10% off!</p>
            <?php else: ?>
                <p style="margin-top: 10px;">Earn <?php echo 100 - $user['LoyaltyPoints']; ?> more points to unlock 10% off!</p>
            <?php endif; ?>
        </div>

        <!-- Orders -->
        <h2 style="margin: 30px 0 15px; color: #266426;">Your Orders</h2>

        <?php if (empty($orders)): ?>
            <p>You have no orders yet. <a href="/GLH/products.php" style="color: #642664;">Start shopping</a></p>
        <?php else: ?>
            <table class="trolley-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['OrderID']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['OrderDate'])); ?></td>
                            <td><?php echo $order['DeliveryType']; ?></td>
                            <td><?php echo $order['OrderStatus']; ?></td>
                            <td>£<?php echo number_format($order['TotalPrice'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>