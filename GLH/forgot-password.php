<?php
require_once 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Check if account exists
        $stmt = $pdo->prepare("SELECT CustomerID FROM Customer WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Hash new password
            $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password
            $stmt = $pdo->prepare("UPDATE Customer SET Password = ? WHERE Email = ?");
            $stmt->execute([$hashedPassword, $email]);

            $success = 'Your password has been reset successfully. You can now log in.';
        } else {
            $error = 'No account was found with that email address.';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<main>
    <div class="container">
        <div class="form-container">
            <h2>Reset Password</h2>
            <p>Enter your email address and choose a new password.</p>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="e.g. john@example.com" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Minimum 8 characters" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Reset Password</button>
            </form>

            <div class="form-links">
                <p><a href="/GLH/login.php">Back to login</a></p>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>