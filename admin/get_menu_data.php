<?php
require_once '../database/config-login.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM menu WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $menu = mysqli_fetch_assoc($result);
    
    header('Content-Type: application/json');
    echo json_encode($menu);
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'ID tidak ditemukan']);
}
?> 