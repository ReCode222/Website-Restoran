<?php
require_once 'database/config-login.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    
    // Query berdasarkan id
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'status' => $order['status'] ?? 'pending',
        'order_id' => $orderId,
        'debug' => $order
    ]);
} else {
    echo json_encode([
        'error' => 'No order ID provided'
    ]);
}
?>
