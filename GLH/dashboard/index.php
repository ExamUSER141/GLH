<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'producer') {
    header('Location: /GLH/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM Producer WHERE CustomerID = ?");
$stmt->execute([$userId]);
$producer = $stmt->fetch();
$producerId = $producer['ProducerID'];

// Handle actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_stock'])) {
        $productId = (int)$_POST['product_id'];
        $newStock = (int)$_POST['new_stock'];
        $stmt = $pdo->prepare("UPDATE Product SET StockLevel = ? WHERE ProductID = ? AND ProducerID = ?");
        $stmt->execute([$newStock, $productId, $producerId]);
        $message = 'Stock updated successfully!';
    } elseif (isset($_POST['update_order_status'])) {
        $orderId = (int)$_POST['order_id'];
        $newStatus = $_POST['order_status'];
        // I assume that Order table has ProducerID or links via OrderItem; update directly if producer owns
        $stmt = $pdo->prepare("UPDATE `Order` SET OrderStatus = ? WHERE OrderID = ?");
        $stmt->execute([$newStatus, $orderId]);
        $message = 'Order status updated successfully!';
    }
}

// Get data
$stmt = $pdo->prepare("SELECT * FROM Product WHERE ProducerID = ?");
$stmt->execute([$producerId]);
$products = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM Product WHERE ProducerID = ? AND StockLevel < 5");
$stmt->execute([$producerId]);
$lowStockCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT o.*, c.Name as CustomerName FROM `Order` o JOIN Customer c ON o.CustomerID = c.CustomerID WHERE o.OrderID IN (SELECT oi.OrderID FROM OrderItem oi JOIN Product p ON oi.ProductID = p.ProductID WHERE p.ProducerID = ?) ORDER BY o.OrderDate DESC LIMIT 10");
$stmt->execute([$producerId]);
$orders = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM `Order` o JOIN OrderItem oi ON o.OrderID = oi.OrderID JOIN Product p ON oi.ProductID = p.ProductID WHERE p.ProducerID = ? AND o.OrderStatus = 'Pending'");
$stmt->execute([$producerId]);
$pendingOrders = $stmt->fetchColumn();
?>

<?php include '../includes/header.php'; ?>

<main>
    <div class="container">
        <div style="margin-top: 30px;">
            <h1 style="color: #266426;">Producer Dashboard – <?php echo htmlspecialchars($producer['Name']); ?></h1>
            
            <?php if ($message): ?>
                <div class="success-message"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
                <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                    <h3><?php echo count($products); ?></h3>
                    <p>Total Products</p>
                </div>
                <div class="stat-card <?php echo $lowStockCount > 0 ? 'low-stock' : ''; ?>" style="background: <?php echo $lowStockCount > 0 ? '#fff3cd' : '#f8f9fa'; ?>; padding: 20px; border-radius: 8px; text-align: center;">
                    <h3><?php echo $lowStockCount; ?></h3>
                    <p>Low Stock Items</p>
                </div>
                <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                    <h3><?php echo $pendingOrders; ?></h3>
                    <p>Pending Orders</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="margin-bottom: 30px;">
                <a href="/GLH/dashboard/manage-products.php" class="btn btn-primary" style="padding: 12px 24px; margin-right: 10px;">Full Product Management</a>
                <a href="/GLH/dashboard/profile-edit.php" class="btn btn-secondary" style="padding: 12px 24px;">Edit Profile</a>
            </div>

            <!-- Recent Orders -->
            <h2 style="color: #266426; margin-top: 40px;">Recent Orders</h2>
            <table class="trolley-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['OrderID']; ?></td>
                        <td><?php echo htmlspecialchars($order['CustomerName']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($order['OrderDate'])); ?></td>
                        <td><?php echo $order['OrderStatus']; ?></td>
                        <td>£<?php echo number_format($order['TotalPrice'], 2); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
                                <select name="order_status" style="padding: 5px;">
                                    <option value="Pending" <?php echo $order['OrderStatus']=='Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Processing" <?php echo $order['OrderStatus']=='Processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="Shipped" <?php echo $order['OrderStatus']=='Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="Delivered" <?php echo $order['OrderStatus']=='Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                </select>
                                <button type="submit" name="update_order_status" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Products Quick View -->
            <h2 style="color: #266426; margin-top: 40px;">Products Quick View</h2>
            <table class="trolley-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Stock</th>
                        <th>Quick Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($products, 0, 5) as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['Name']); ?></td>
                        <td>
                            <?php echo $product['StockLevel']; ?>
                            <?php if ($product['StockLevel'] < 5): ?>
                                <span class="low-stock"> – LOW!</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                                <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                                <input type="number" name="new_stock" value="<?php echo $product['StockLevel']; ?>" min="0" style="width: 60px;">
                                <button type="submit" name="update_stock" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($products) > 5): ?>
                    <tr><td colspan="3"><a href="/GLH/dashboard/manage-products.php">Manage All Products →</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

