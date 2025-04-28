<?php
require_once '../database/config-login.php';

function generateOrderNumber() {
    global $conn;
    
    // Get last order number for today
    $today = date('Y-m-d');
    $sql = "SELECT MAX(order_number) as last_number FROM orders WHERE DATE(created_at) = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $today);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    // Jika belum ada order hari ini, mulai dari 1
    return ($row['last_number'] ?? 0) + 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['cart']) || empty($data['cart'])) {
            throw new Exception('Cart is empty');
        }
        
        $cart = $data['cart'];
        $totalPrice = $data['totalPrice'];
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        // Create order
        $orderNumber = generateOrderNumber();
        $sql = "INSERT INTO orders (order_number, total_price, status) VALUES (?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "id", $orderNumber, $totalPrice);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to create order: ' . mysqli_error($conn));
        }
        
        $orderId = mysqli_insert_id($conn);
        
        // Insert order items
        $sql = "INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        
        foreach ($cart as $item) {
            mysqli_stmt_bind_param($stmt, "iiid", 
                $orderId, 
                $item['id'], 
                $item['quantity'], 
                $item['price']
            );
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to create order item: ' . mysqli_error($conn));
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Send success response with order ID instead of order number
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Order created successfully',
            'orderId' => $orderId
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
?>
