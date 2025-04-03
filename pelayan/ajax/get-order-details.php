<?php
session_start();
require_once('../../database/config-login.php');

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelayan') {
    exit('Unauthorized');
}

if(!isset($_GET['id'])) {
    exit('Invalid request');
}

$order_id = $_GET['id'];

// Get order details
$query_order = "SELECT id, order_number, total_price, status, created_at FROM orders WHERE id = $order_id";
$result_order = mysqli_query($conn, $query_order);

if(mysqli_num_rows($result_order) == 0) {
    exit('Pesanan tidak ditemukan');
}

$order = mysqli_fetch_assoc($result_order);

// Get order items
$query_items = "SELECT oi.id, oi.quantity, oi.price, m.name, m.description, c.name as category 
                FROM order_items oi
                JOIN menu m ON oi.menu_id = m.id
                JOIN categories c ON m.category_id = c.id
                WHERE oi.order_id = $order_id";
$result_items = mysqli_query($conn, $query_items);
?>

<div class="order-info">
    <p><strong>No. Pesanan:</strong> <?php echo $order['order_number']; ?></p>
    <p><strong>Tanggal:</strong> <?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></p>
    <p><strong>Status:</strong> <span class="status processing"><?php echo ucfirst($order['status']); ?></span></p>
</div>

<h3>Item Pesanan</h3>
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
        <?php 
        $total = 0;
        while($item = mysqli_fetch_assoc($result_items)): 
            $subtotal = $item['quantity'] * $item['price'];
            $total += $subtotal;
        ?>
        <tr>
            <td><?php echo $item['name']; ?></td>
            <td><?php echo $item['category']; ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
            <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div class="order-summary">
    <h3>Ringkasan Pesanan</h3>
    <p>
        <span>Total Item:</span>
        <span><?php echo mysqli_num_rows($result_items); ?> item</span>
    </p>
    <p class="total">
        <span>Total Bayar:</span>
        <span>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></span>
    </p>
</div>

<form method="post" action="kelola-pesanan.php">
    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
    <button type="submit" name="pesanan_selesai" class="finish-order-btn">
        <i class="fas fa-check-circle"></i> Pesanan Selesai
    </button>
</form> 