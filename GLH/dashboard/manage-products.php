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

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId = (int)$_POST['product_id'];
    $newStock = (int)$_POST['new_stock'];

    $stmt = $pdo->prepare("UPDATE Product SET StockLevel = ? WHERE ProductID = ? AND ProducerID = ?");
    $stmt->execute([$newStock, $productId, $producerId]);

    $success = "Stock updated successfully!";
}

// Get products
$stmt = $pdo->prepare("SELECT * FROM Product WHERE ProducerID = ?");
$stmt->execute([$producerId]);
$products = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<main>
    <div class="container">
        <h1 style="margin-top: 30px; color: #266426;">Manage Products – <?php echo htmlspecialchars($producer['Name']); ?></h1>

        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <table class="trolley-table" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Current Stock</th>
                    <th>Update Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['Name']); ?></td>
                        <td>£<?php echo number_format($product['Price'], 2); ?></td>
                        <td>
                            <?php echo $product['StockLevel']; ?>
                            <?php if ($product['StockLevel'] < 5): ?>
                                <span class="low-stock"> – LOW STOCK!</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="" style="display: flex; gap: 10px; align-items: center;">
                                <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                                <input type="number" name="new_stock" value="<?php echo $product['StockLevel']; ?>" min="0" style="width: 80px; padding: 8px;">
                                <button type="submit" name="update_stock" class="btn btn-primary" style="padding: 8px 15px;">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="/GLH/dashboard/index.php" class="btn btn-dark" style="margin-top: 20px;">Back to Dashboard</a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>