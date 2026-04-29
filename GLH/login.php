<?php
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Customer WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['Password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['CustomerID'];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['user_email'] = $user['Email'];
            $_SESSION['user_role'] = $user['Role'];
            $_SESSION['loyalty_points'] = $user['LoyaltyPoints'];

            if ($user['Role'] === 'producer') {
                header('Location: /GLH/index.php');
            } else {
                header('Location: /GLH/index.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<main>
    <div class="container">
        <div class="form-container">
            <h2>Login</h2>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="e.g. john@example.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>

            <div class="form-links">
                <p><a href="/GLH/forgot-password.php">Forgot password?</a></p>
                <p><a href="/GLH/register.php">Create an account</a></p>
                <p><a href="/GLH/index.php">Skip login and browse as guest</a></p>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>