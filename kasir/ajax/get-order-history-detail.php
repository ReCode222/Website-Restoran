<?php
session_start();
require_once('../../database/config-login.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">ID pesanan tidak ditemukan.</div>';
    exit();
}

$order_id = intval($_GET['id']);

// Get order info
$query_order = "SELECT o.id, o.order_number, o.total_price, o.status, o.created_at FROM orders o WHERE o.id = $order_id";
$result_order = mysqli_query($conn, $query_order);
if (!$result_order || mysqli_num_rows($result_order) == 0) {
    echo '<div class="alert alert-danger">Pesanan tidak ditemukan.</div>';
    exit();
}
$order = mysqli_fetch_assoc($result_order);

// Get order items
$query_items = "SELECT oi.quantity, oi.price, m.name as menu_name, c.name as category_name FROM order_items oi JOIN menu m ON oi.menu_id = m.id JOIN categories c ON m.category_id = c.id WHERE oi.order_id = $order_id";
$result_items = mysqli_query($conn, $query_items);

// Count total items
$total_items = 0;
$order_items = [];
while ($item = mysqli_fetch_assoc($result_items)) {
    $item['subtotal'] = $item['quantity'] * $item['price'];
    $total_items += $item['quantity'];
    $order_items[] = $item;
}

// Status label
$status_label = '';
switch ($order['status']) {
    case 'processing':
        $status_label = '<span class="status processing">Diproses</span>';
        break;
    case 'completed':
        $status_label = '<span class="status completed">Selesai</span>';
        break;
    case 'cancelled':
        $status_label = '<span class="status cancelled">Dibatalkan</span>';
        break;
}

// Output HTML
?>
<div class="order-info">
    <p><strong>No. Pesanan:</strong> <?php echo $order['order_number']; ?></p>
    <p><strong>Tanggal:</strong> <?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></p>
    <p><strong>Status:</strong> <?php echo $status_label; ?></p>
</div>

<h3 style="margin-bottom:1rem;">Item Pesanan</h3>
<table class="order-details-table">
    <thead>
        <tr>
            <th>Menu</th>
            <th>Kategori</th>
            <th>Jumlah</th>
            <th>Harga</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($order_items as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['menu_name']); ?></td>
            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
            <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="order-summary">
    <h3>Ringkasan Pesanan</h3>
    <p>Total Item: <span><?php echo $total_items; ?> item</span></p>
    <p class="total">Total Bayar: <span>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></span></p>
</div> 