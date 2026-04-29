<?php
require_once 'config/db.php';
include 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM Producer WHERE ProducerID = ?");
$stmt->execute([$id]);
$farmer = $stmt->fetch();

if (!$farmer) {
    echo '<div class="container"><p>Farmer not found.</p></div>';
    include 'includes/footer.php';
    exit;
}

// Get farmer products with available stock
$stmt = $pdo->prepare("
    SELECT 
        p.*, 
        p.StockLevel as AvailableStock
    FROM Product p 
    WHERE p.ProducerID = ?
    ORDER BY p.Name
");
$stmt->execute([$id]);
$products = $stmt->fetchAll();
?>


<main>
    <div class="container farmer-story">
        <h1><?php echo htmlspecialchars($farmer['Name']); ?></h1>

        <div class="farmer-story-content">
            <div class="story-text">
                <p><?php echo nl2br(htmlspecialchars($farmer['Description'])); ?></p>
                <p style="margin-top: 15px;"><strong>📍 Location:</strong> <?php echo htmlspecialchars($farmer['FarmLocation']); ?></p>
                <p><strong>📧 Contact:</strong> <?php echo htmlspecialchars($farmer['ContactEmail']); ?></p>
            </div>
            <div class="story-image">
                <img src="/GLH/images/<?php echo htmlspecialchars($farmer['ImageURL']); ?>" alt="<?php echo htmlspecialchars($farmer['Name']); ?>">
            </div>
        </div>

        <h2 style="color: #266426;">Products from <?php echo htmlspecialchars($farmer['Name']); ?></h2>

        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="/GLH/images/<?php echo htmlspecialchars($product['ImageURL']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['Name']); ?></h3>
                        <p class="price">£<?php echo number_format($product['Price'], 2); ?></p>
                        <?php if (($product['AvailableStock'] ?? 0) > 0): ?>
                            <form method="POST" action="/GLH/products.php" class="add-to-trolley-form" data-product-id="<?php echo $product['ProductID']; ?>">
                                <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                                <button type="submit" name="add_to_trolley" class="btn btn-primary add-stock-btn" style="margin-top: 10px;" data-product-id="<?php echo $product['ProductID']; ?>" data-stock="<?php echo $product['AvailableStock']; ?>">Add to Trolley</button>
                            </form>
                        <?php else: ?>
                            <div class="product-actions">
                                <span class="stock-zero">Out of Stock</span>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>