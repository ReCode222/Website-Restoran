<?php
session_start();
require_once('../../database/config-login.php');

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// Get all orders except pending
$query_orders = "SELECT id, order_number, total_price, status, created_at FROM orders WHERE status IN ('processing', 'completed', 'cancelled') ORDER BY created_at DESC";
$result_orders = mysqli_query($conn, $query_orders);

if (!$result_orders) {
    header('HTTP/1.1 500 Internal Server Error');
    exit();
}

$output = '<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>No. Pesanan</th>
            <th>Total Harga</th>
            <th>Status</th>
            <th>Tanggal Pesanan</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>';

if (mysqli_num_rows($result_orders) > 0) {
    while ($order = mysqli_fetch_assoc($result_orders)) {
        $output .= '<tr>
            <td>' . $order['id'] . '</td>
            <td>' . $order['order_number'] . '</td>
            <td>Rp ' . number_format($order['total_price'], 0, ',', '.') . '</td>
            <td>
                <span class="status ' . $order['status'] . '">';
        
        switch($order['status']) {
            case 'processing':
                $output .= 'Diproses';
                break;
            case 'completed':
                $output .= 'Selesai';
                break;
            case 'cancelled':
                $output .= 'Dibatalkan';
                break;
        }
        
        $output .= '</span>
            </td>
            <td>' . date('d-m-Y H:i', strtotime($order['created_at'])) . '</td>
            <td>
                <button class="detail-btn" data-id="' . $order['id'] . '">
                    Detail Pesanan
                </button>
            </td>
        </tr>';
    }
} else {
    $output .= '<tr>
        <td colspan="6" class="no-data">Tidak ada riwayat pesanan.</td>
    </tr>';
}

$output .= '</tbody></table>';

echo $output;
?> 