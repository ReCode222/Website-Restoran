<?php
session_start();
require_once('../../database/config-login.php');

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    exit('Unauthorized');
}

// Cek parameter yang diperlukan
if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    exit('Invalid request');
}

$order_id = $_POST['order_id'];
$new_status = $_POST['status'];

// Validasi status
$allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    exit('Invalid status');
}

// Update status pesanan
$update_query = "UPDATE orders SET status = '$new_status' WHERE id = $order_id";
if (mysqli_query($conn, $update_query)) {
    echo 'success';
} else {
    echo mysqli_error($conn);
}
?> 