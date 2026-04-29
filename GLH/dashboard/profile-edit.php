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

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $farm_location = trim($_POST['farm_location']);
    $description = trim($_POST['description']);

    // Basic validation
    if (strlen($name) < 2 || strlen($email) < 5 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($farm_location) < 2 || strlen($description) < 10) {
        $message = 'Please fill all fields correctly. Description must be at least 10 characters.';
    } else {
$stmt = $pdo->prepare("UPDATE Producer SET Name = ?, ContactEmail = ?, FarmLocation = ?, Description = ? WHERE CustomerID = ?");
$stmt->execute([$name, $email, $farm_location, $description, $userId]);
        $message = 'Profile updated successfully!';

        // Refresh producer data
        $stmt = $pdo->prepare("SELECT * FROM Producer WHERE CustomerID = ?");
        $stmt->execute([$userId]);
        $producer = $stmt->fetch();
    }
}
?>

<?php include '../includes/header.php'; ?>

<main>
    <div class="container">
        <div style="margin-top: 30px;">
            <h1 style="color: #266426;">Edit Profile – <?php echo htmlspecialchars($producer['Name'] ?? ''); ?></h1>

            <?php if ($message): ?>
                <div class="success-message" style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px;"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" style="max-width: 600px;">
                <div style="margin-bottom: 20px;">
                    <label for="name" style="display: block; margin-bottom: 5px; font-weight: bold;">Farm Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($producer['Name'] ?? ''); ?>" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">Contact Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($producer['ContactEmail'] ?? ''); ?>" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="farm_location" style="display: block; margin-bottom: 5px; font-weight: bold;">Farm Location:</label>
                    <input type="text" id="farm_location" name="farm_location" value="<?php echo htmlspecialchars($producer['FarmLocation'] ?? ''); ?>" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="description" style="display: block; margin-bottom: 5px; font-weight: bold;">Farmer Story / Description:</label>
                    <textarea id="description" name="description" rows="6" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; font-family: inherit;"><?php echo htmlspecialchars($producer['Description'] ?? ''); ?></textarea>
                    <small style="color: #666;">Tell your story – what makes your farm special? (min 10 chars)</small>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 16px;">Update Profile</button>
                    <a href="/GLH/dashboard/index.php" class="btn btn-dark" style="padding: 12px 30px; text-decoration: none; border-radius: 4px;">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

