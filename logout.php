<?php
// ============================================
// Logout — saves cart to DB before destroying session
// ============================================
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';

$role   = $_SESSION['role']    ?? 'client';
$userId = $_SESSION['user_id'] ?? 0;

// Save cart to DB if this is a client with items in their session cart
if ($role === 'client' && $userId > 0 && !empty($_SESSION['cart'])) {
    // Ensure cart_items table exists (idempotent)
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS cart_items (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        client_id  INT NOT NULL,
        product_id INT NOT NULL,
        quantity   INT NOT NULL DEFAULT 1,
        added_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_client_product (client_id, product_id),
        FOREIGN KEY (client_id)  REFERENCES users(id)    ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    foreach ($_SESSION['cart'] as $productId => $item) {
        $qty = intval($item['quantity']);
        if ($qty < 1) continue;
        $pid = intval($productId);
        $stmt = mysqli_prepare($conn,
            "INSERT INTO cart_items (client_id, product_id, quantity)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), added_at = CURRENT_TIMESTAMP"
        );
        mysqli_stmt_bind_param($stmt, "iii", $userId, $pid, $qty);
        mysqli_stmt_execute($stmt);
    }
}

session_unset();
session_destroy();

if ($role === 'manager') {
    header("Location: /storehub/index.php");
} else {
    header("Location: /storehub/client/login.php");
}
exit();
?>
