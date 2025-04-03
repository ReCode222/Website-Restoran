<?php
session_start();
require_once '../database/config-login.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Query untuk data grafik
$sql_chart = "SELECT 
    DATE(created_at) as order_date,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
FROM orders 
WHERE DATE(created_at) BETWEEN ? AND ?
GROUP BY DATE(created_at)
ORDER BY DATE(created_at)";

// Query untuk data tabel
$sql_table = "SELECT id, total_price, status, created_at 
              FROM orders 
              WHERE DATE(created_at) BETWEEN ? AND ?
              ORDER BY created_at DESC";

try {
    // Query untuk ringkasan
    $sql_summary = "SELECT 
        COALESCE(SUM(total_price), 0) as total_revenue,
        COUNT(*) as total_orders,
        COALESCE(AVG(total_price), 0) as avg_order
    FROM orders
    WHERE DATE(created_at) BETWEEN ? AND ?";
    
    $stmt_summary = $conn->prepare($sql_summary);
    $stmt_summary->bind_param("ss", $start_date, $end_date);
    $stmt_summary->execute();
    $summary = $stmt_summary->get_result()->fetch_assoc();

    // Ambil data untuk grafik
    $stmt_chart = $conn->prepare($sql_chart);
    $stmt_chart->bind_param("ss", $start_date, $end_date);
    $stmt_chart->execute();
    $result_chart = $stmt_chart->get_result();

    // Ambil data untuk tabel
    $stmt_table = $conn->prepare($sql_table);
    $stmt_table->bind_param("ss", $start_date, $end_date);
    $stmt_table->execute();
    $result_table = $stmt_table->get_result();

    $labels = [];
    $pending = [];
    $processing = [];
    $completed = [];
    $cancelled = [];
    $orders = [];

    // Process chart data
    while ($row = $result_chart->fetch_assoc()) {
        $labels[] = date('d/m', strtotime($row['order_date']));
        $pending[] = (int)($row['pending'] ?? 0);
        $processing[] = (int)($row['processing'] ?? 0);
        $completed[] = (int)($row['completed'] ?? 0);
        $cancelled[] = (int)($row['cancelled'] ?? 0);
    }

    // Process table data
    while ($row = $result_table->fetch_assoc()) {
        $orders[] = [
            'id' => $row['id'],
            'total_price' => $row['total_price'],
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode([
        'labels' => $labels,
        'pending' => $pending,
        'processing' => $processing,
        'completed' => $completed,
        'cancelled' => $cancelled,
        'orders' => $orders,
        'summary' => [
            'total_revenue' => (float)$summary['total_revenue'],
            'total_orders' => (int)$summary['total_orders'],
            'avg_order' => (float)$summary['avg_order']
        ]
    ]);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}