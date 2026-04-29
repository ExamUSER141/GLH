<?php
require_once 'config/db.php';
include 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM Producer");
$farmers = $stmt->fetchAll();
?>

<main>
    <div class="container">
        <h1 style="margin-top: 30px; color: #266426;">Our Farmers</h1>

        <div class="search-sort-bar">
            <input type="text" id="product-search" placeholder="🔍 Search for a farm...">
        </div>

        <div class="farmer-grid">
            <?php foreach ($farmers as $farmer): ?>
                <div class="farmer-card product-card">
                    <img src="/GLH/images/<?php echo htmlspecialchars($farmer['ImageURL']); ?>" alt="<?php echo htmlspecialchars($farmer['Name']); ?>">
                    <div class="farmer-info">
                        <h3><?php echo htmlspecialchars($farmer['Name']); ?></h3>
                        <p><?php echo substr(htmlspecialchars($farmer['Description']), 0, 120); ?>...</p>
                        <a href="/GLH/farmer-story.php?id=<?php echo $farmer['ProducerID']; ?>" class="btn btn-dark">Read More</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>