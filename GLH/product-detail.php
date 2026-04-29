<?php
require_once 'config/db.php';
include 'includes/header.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId === 0) {
    header('Location: /GLH/products.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        p.*, 
        pr.Name AS FarmName,
        p.StockLevel as AvailableStock
    FROM Product p 
    JOIN Producer pr ON p.ProducerID = pr.ProducerID 
    WHERE p.ProductID = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /GLH/products.php');
    exit;
}

$availableStock = $product['AvailableStock'] ?? 0;

$addMessage = '';
$addError = '';
// Handle add to trolley locally
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_trolley'])) {
    $sessionId = session_id();
    
    if ($availableStock > 0) {
        if (!isset($_SESSION['trolley'])) {
            $_SESSION['trolley'] = [];
        }
        
        if (!isset($_SESSION['trolley'][$productId])) {
            $_SESSION['trolley'][$productId] = [
                'name' => $product['Name'],
                'price' => $product['Price'],
                'image' => $product['ImageURL'],
                'quantity' => 1
            ];
            $addMessage = htmlspecialchars($product['Name']) . ' added to trolley!';
        } else {
            $currentQty = $_SESSION['trolley'][$productId]['quantity'] ?? 0;
            if ($currentQty < $availableStock) {
                $_SESSION['trolley'][$productId]['quantity'] = $currentQty + 1;
                $addMessage = 'Added another ' . htmlspecialchars($product['Name']) . ' to trolley!';
            } else {
                $addError = "Cannot add more: only {$availableStock} available.";
            }
        }


    } else {
        $addError = "Cannot add: 0 available in stock.";
    }

}
?>

<main>
    <div class="container">
        <a href="/GLH/products.php" class="btn btn-secondary" style="margin-bottom: 2rem;">← Back to Products</a>
        
        <?php if ($addMessage): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem; padding: 1rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 0.5rem; color: #155724;">
                ✅ <?php echo $addMessage; ?> <a href="/GLH/trolley.php" style="color: #266426; font-weight: bold;">View Trolley</a>
            </div>
        <?php endif; ?>

        <?php if ($addError): ?>
            <div class="alert alert-error" style="margin-bottom: 1rem; padding: 1rem; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 0.5rem; color: #721c24;">
                ❌ <?php echo $addError; ?>
            </div>
        <?php endif; ?>
        
        <div class="product-detail">
            <div class="product-detail-image">
                <img src="/GLH/images/<?php echo htmlspecialchars($product['ImageURL']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>">
            </div>
            <div class="product-detail-info">
                <h1><?php echo htmlspecialchars($product['Name']); ?></h1>
                
                <div class="farm-badge">
                    <span class="farm-name"><?php echo htmlspecialchars($product['FarmName']); ?></span>
                </div>
                
                <div class="price-large">£<?php echo number_format($product['Price'], 2); ?></div>
                
                <?php if ($availableStock > 0): ?>
                    <?php if ($availableStock < 5): ?>
                        <p class="stock-warning-large">⚠ Only <?php echo $availableStock; ?> available</p>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="add-form-<?php echo $product['ProductID']; ?>" style="margin: 2rem 0;" data-product-id="<?php echo $product['ProductID']; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                        <button type="submit" name="add_to_trolley" class="btn btn-primary btn-large add-stock-btn" data-product-id="<?php echo $product['ProductID']; ?>" data-stock="<?php echo $availableStock; ?>">
                            + Add to Trolley (<?php echo $availableStock; ?> available)
                        </button>
                    </form>
                <?php else: ?>
                    <p class="out-of-stock-large">Out of Stock</p>
                <?php endif; ?>
                
                <div class="product-description">
                    <h3>About this Product</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['Description'])); ?></p>
                </div>
                
                <div class="farm-info">
                    <h3>About <?php echo htmlspecialchars($product['FarmName']); ?> Farm</h3>
                    <p>Premium local farm supplying fresh, sustainable produce to GLH customers.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.product-detail {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.product-detail-image img {
    width: 100%;
    height: 60vh;
    object-fit: cover;
    border-radius: 1rem;
}

.farm-badge {
    margin: 1rem 0;
}

.price-large {
    font-size: 2.5rem;
    font-weight: bold;
    color: #266426;
    margin: 1rem 0;
}

.stock-warning-large {
    color: #c62828;
    font-weight: bold;
    font-size: 1.2rem;
    margin: 1rem 0;
}

.out-of-stock-large {
    color: #999;
    font-style: italic;
    font-size: 1.2rem;
    margin: 1rem 0;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.2rem;
}

.product-description,
.farm-info {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #eee;
}

.product-description h3,
.farm-info h3 {
    color: #266426;
    margin-bottom: 1rem;
}

@media (min-width: 769px) {
    .product-detail {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>

