<?php
require_once 'database/config-login.php';

header('Content-Type: application/json');

if (isset($_GET['order'])) {
    $orderNumber = (int)$_GET['order'];
    
    $sql = "SELECT * FROM orders WHERE order_number = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $orderNumber);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'status' => $order['status'] ?? 'pending',
        'order_number' => $orderNumber,
        'debug' => $order
    ]);
} else {
    echo json_encode([
        'error' => 'No order number provided'
    ]);
}
?> 