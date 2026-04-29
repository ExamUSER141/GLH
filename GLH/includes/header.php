<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>GLH - Greenfield Local Hub</title>
    <link rel="stylesheet" href="/GLH/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- Mobile hamburger button -->
<button class="hamburger" aria-label="Toggle menu" aria-expanded="false">
    <span></span>
    <span></span>
    <span></span>
</button>

<header>
    <div class="container">
        <div class="logo">
            <a href="/GLH/index.php">
                <img src="/GLH/images/glhlogo.jpg" alt="GLH Logo">
            </a>
        </div>
        <nav>
  <ul>
    <li><a href="/GLH/index.php">Home</a></li>
    <li><a href="/GLH/products.php">Products</a></li>
    <li><a href="/GLH/farmers.php">Our Farmers</a></li>
    <li><a href="/GLH/support.php">Support</a></li>

    <?php if (isset($_SESSION['user_id'])): ?>

      <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'producer'): ?>
        <li><a href="/GLH/dashboard/index.php">Dashboard</a></li>
      <?php else: ?>
        <li><a href="/GLH/account.php">Account</a></li>
      <?php endif; ?>

      <li><a href="/GLH/logout.php">Logout</a></li>

    <?php else: ?>
      <li><a href="/GLH/login.php">Login</a></li>
    <?php endif; ?>
  </ul>
</nav>
        <div class="nav-right">
            <a href="/GLH/trolley.php" class="trolley-icon">
                <img src="/GLH/images/Trolley.jpg" alt="Shopping trolley">
                <?php if (isset($_SESSION['trolley']) && count($_SESSION['trolley']) > 0): ?>
                    (<?php echo count($_SESSION['trolley']); ?>)
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>
