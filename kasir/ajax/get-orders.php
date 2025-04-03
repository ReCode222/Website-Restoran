<?php
session_start();
require_once('../../database/config-login.php');

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    exit('Unauthorized');
}

// Get pending orders
$query_orders = "SELECT id, order_number, total_price, status, created_at FROM orders WHERE status = 'pending' ORDER BY created_at DESC";
$result_orders = mysqli_query($conn, $query_orders);
?>

<table>
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
    <tbody>
        <?php if(mysqli_num_rows($result_orders) > 0): ?>
            <?php while($order = mysqli_fetch_assoc($result_orders)): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['order_number']; ?></td>
                <td>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                <td>
                    <span class="status pending">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </td>
                <td><?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></td>
                <td>
                    <button class="detail-btn" data-id="<?php echo $order['id']; ?>">
                        Detail Pesanan
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="no-data">Tidak ada pesanan yang menunggu pembayaran saat ini.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table> 