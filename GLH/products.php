    <?php
require_once 'config/db.php';
    include 'includes/header.php';

    $addMessage = '';
    $addError = '';

    // Handle delivery type selection with validation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivery_type'])) {
        $delivery_type = $_POST['delivery_type'];
        $error = '';
        
        $_SESSION['delivery_type'] = $delivery_type;
        if (isset($_POST['postcode'])) {
            $_SESSION['delivery_postcode'] = trim($_POST['postcode']);
        }
    }

    // Handle add to trolley (only after delivery selection)
    if (isset($_SESSION['delivery_type']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_trolley'])) {
        $productId = (int)$_POST['product_id'];
        $stmt = $pdo->prepare("SELECT Name, Price, ImageURL, StockLevel as AvailableStock FROM Product WHERE ProductID = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if ($product && $product['AvailableStock'] > 0) {
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
                $currentQty = $_SESSION['trolley'][$productId]['quantity'];
                if ($currentQty < $product['AvailableStock']) {
                    $_SESSION['trolley'][$productId]['quantity'] = $currentQty + 1;
                    $addMessage = 'Added another ' . htmlspecialchars($product['Name']) . ' to trolley!';
                } else {
                    $addError = "Cannot add more: only {$product['AvailableStock']} available.";
                }
            }
        } else {
            $addError = 'Product out of stock.';
        }
    }

$query = "
    SELECT 
        p.ProductID, 
        p.Name, 
        p.Price, 
        p.StockLevel as AvailableStock,
        p.ImageURL, 
        p.CreatedAt,
        pr.Name as FarmName
    FROM Product p 
    JOIN Producer pr ON p.ProducerID = pr.ProducerID 

    ORDER BY 
";
// Get sort option
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
if ($sort === 'name') {
    $orderBy = 'pr.Name ASC';
} elseif ($sort === 'price_low') {
    $orderBy = 'p.Price ASC';       
} elseif ($sort === 'price_high') {
    $orderBy = 'p.Price DESC';
} elseif ($sort === 'newest') {
    $orderBy = 'p.CreatedAt DESC';
} else {
    $orderBy = 'pr.Name ASC';
}

$query .= $orderBy;
$stmt = $pdo->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll();

    ?>

    <main>
        <div class="container">

            <?php if (!isset($_SESSION['delivery_type'])): ?>
                <!-- Delivery/Collection Selection -->
                <div class="form-container" style="margin-top: 40px;">
                    <h2>How would you like to receive your products?</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="radio-label">
                                <input type="radio" name="delivery_type" value="Delivery" required> Delivery (+ £1.00)
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="radio-label">
                                <input type="radio" name="delivery_type" value="Collection"> Collection (Free)
                            </label>
                        </div>
                        <!-- Postcode Hidden for Collection -->
                        <div id="postcode-group" style="display: none;">
                            <div class="form-group">
                                <label for="postcode">Postcode (for delivery)</label>
                                <input type="text" id="postcode" name="postcode" placeholder="e.g. SW1A 1AA">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Continue to Shop</button>
                    </form>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const deliveryRadio = document.querySelector('input[name="delivery_type"][value="Delivery"]');
                        const collectionRadio = document.querySelector('input[name="delivery_type"][value="Collection"]');
                        const postcodeGroup = document.getElementById('postcode-group');
                        const form = postcodeGroup.closest('form');
                        
                        function togglePostcode() {
                            if (deliveryRadio.checked) {
                                postcodeGroup.style.display = 'block';
                                document.getElementById('postcode').required = true;
                            } else {
                                postcodeGroup.style.display = 'none';
                                document.getElementById('postcode').required = false;
                                document.getElementById('postcode').value = '';
                            }
                        }
                        
                        deliveryRadio.addEventListener('change', togglePostcode);
                        collectionRadio.addEventListener('change', togglePostcode);
                        
                        togglePostcode(); // Initial
                    });
                    </script>
                </div>
            <?php else: ?>

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

                <h1 style="margin-top: 30px; color: #266426;">Our Products</h1>
                <p>Delivery method: <strong><?php echo $_SESSION['delivery_type'] ?? 'Collection'; ?></strong>
                    <?php if (isset($_SESSION['delivery_postcode'])): ?>
                        (Postcode: <?php echo htmlspecialchars($_SESSION['delivery_postcode']); ?>)
                    <?php endif; ?>
                    <a href="/GLH/products.php?reset=1" style="color: #642664; margin-left: 10px;">(Change)</a>
                </p>

                <?php
                if (isset($_GET['reset'])) {
                    unset($_SESSION['delivery_type']);
                    header('Location: /GLH/products.php');
                    exit;
                }
                ?>

                <!-- Search and Sort -->
                <div class="search-sort-bar">
                    <input type="text" id="product-search" placeholder="🔍 Search for a product...">
                    <form method="GET" action="">
                        <select name="sort" onchange="this.form.submit()">
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Sort by Name</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Most Recent</option>
                        </select>
                    </form>
                </div>

                <!-- Product Grid -->
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <?php 
                            // Show low stock message only once at the top if applicable
                            $showLowStockMessage = false;
                            if ($product['AvailableStock'] > 0 && $product['AvailableStock'] < 5) {
                                $showLowStockMessage = true;
                            }
                        ?>
                        
                        <div class="product-card">
                            <a href="/GLH/product-detail.php?id=<?php echo $product['ProductID']; ?>" class="product-link">
                                <img src="/GLH/images/<?php echo htmlspecialchars($product['ImageURL']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>">
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($product['Name']); ?></h3>
                                    <p class="price">£<?php echo number_format($product['Price'], 2); ?></p>


                                    
                                    <?php 
                                    $sessionQty = 0;
                                    if (isset($_SESSION['trolley'][$product['ProductID']])) {
                                        $sessionQty = $_SESSION['trolley'][$product['ProductID']]['quantity'];
                                    }
                                    $effectiveStock = $product['AvailableStock'] - $sessionQty;
                                    if ($effectiveStock > 0 && $effectiveStock <= 5): ?>

                                        <p class="stock-warning">⚠️ Only <?php echo $effectiveStock; ?> left (<?php echo $sessionQty; ?> in trolley)</p>

                                    <?php endif; ?>

                                    <span class="farm-name"><?php echo htmlspecialchars($product['FarmName']); ?></span>
                                </div>
                            </a>
                            <?php if ($product['AvailableStock'] > 0): ?>
                                <div class="product-actions">
                                    <form method="POST" action="" class="add-to-trolley-form" data-product-id="<?php echo $product['ProductID']; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                                        <button type="submit" name="add_to_trolley" class="btn-add add-stock-btn" data-product-id="<?php echo $product['ProductID']; ?>" data-stock="<?php echo $product['AvailableStock']; ?>">+</button>

                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="product-actions">
                                    <span class="stock-zero">0 available</span>
                                </div>
                            <?php endif; ?>
                            


                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </div>
    </main>

    <?php include 'includes/footer.php'; ?>