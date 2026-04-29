<?php
require_once 'config/db.php';
include 'includes/header.php';

    if (!isset($_SESSION['trolley']) || empty($_SESSION['trolley'])) {
        header('Location: /GLH/products.php');
        exit;
    }

    $is_delivery = isset($_SESSION['delivery_type']) && $_SESSION['delivery_type'] === 'Delivery';
?>
    
    <main>
    <div class="container">
        <div class="form-container payment-form">
            <h2>Payment Information</h2>

            <form method="POST" action="/GLH/payment.php">
                <?php if ($is_delivery): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.querySelector('.payment-form form');
                    form.addEventListener('submit', function(e) {
                        const line1 = document.getElementById('address_line1').value.trim();
                        const city = document.getElementById('city').value.trim();
                        const postcode = document.getElementById('postcode').value.trim();
                        if (!line1 || !city || !postcode) {
                            e.preventDefault();
                            alert('All delivery address fields are required.');
                            return false;
                        }
                    });
                });
                </script>
                <?php endif; ?>
                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required maxlength="19">
                </div>
                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="expiry">Expiry Date</label>
                        <input type="text" id="expiry" name="expiry" placeholder="MM/YY" required maxlength="5">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="e.g. 123" required maxlength="3">
                    </div>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" placeholder="John Doe" required>
                </div>

                <?php if ($is_delivery): ?>
                    <div id="delivery-address-section">
                        <h3 style="margin: 20px 0 10px 0; color: #266426;">Delivery Address *</h3>
                        <div class="form-group">
                            <label for="address_line1">Address Line 1</label>
                            <input type="text" id="address_line1" name="address_line1" placeholder="123 Farm Road" required>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" placeholder="London" required>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="postcode">Postcode <?php echo $is_delivery ? '' : '(Collection - no postcode needed)'; ?></label>
                    <input type="text" id="postcode" name="postcode" placeholder="SW1A 1AA" <?php echo $is_delivery ? 'required' : ''; ?> value="<?php echo htmlspecialchars($_SESSION['delivery_postcode'] ?? ''); ?>">
                </div>

                <button type="submit" class="btn btn-dark" style="width: 100%;">Complete & Pay</button>

                <p class="secure-notice">🔒 <span>Secure payment – 256-bit SSL encryption</span></p>
            </form>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>