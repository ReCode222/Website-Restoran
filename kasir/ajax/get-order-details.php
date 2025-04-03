<?php
session_start();
require_once('../../database/config-login.php');

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    exit('Unauthorized');
}

if(!isset($_GET['id'])) {
    exit('Invalid request');
}

// Ambil username untuk struk
$user_id = $_SESSION['user_id'];
$query_user = "SELECT username FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $query_user);
$user_data = mysqli_fetch_assoc($result_user);
$username = $user_data['username'];

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

// Simpan items dalam array untuk digunakan kembali nanti
$items_array = [];
while($item = mysqli_fetch_assoc($result_items)) {
    $items_array[] = $item;
}
?>

<div class="order-info">
    <p><strong>No. Pesanan:</strong> <?php echo $order['order_number']; ?></p>
    <p><strong>Tanggal:</strong> <?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></p>
    <p><strong>Status:</strong> <span class="status pending"><?php echo ucfirst($order['status']); ?></span></p>
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
        foreach($items_array as $item): 
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
        <?php endforeach; ?>
    </tbody>
</table>

<div class="order-summary">
    <h3>Ringkasan Pesanan</h3>
    <p>
        <span>Total Item:</span>
        <span><?php echo count($items_array); ?> item</span>
    </p>
    <p class="total">
        <span>Total Bayar:</span>
        <span>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></span>
    </p>
</div>

<div class="payment-section">
    <h3>Pembayaran</h3>
    <div class="payment-input">
        <div class="input-group">
            <label for="paid-amount">Jumlah Bayar:</label>
            <div class="input-with-icon">
                <span class="input-prefix">Rp</span>
                <input type="number" id="paid-amount" class="payment-input-field" placeholder="Masukkan jumlah bayar">
            </div>
        </div>
        <button class="calculate-btn" data-total="<?php echo $order['total_price']; ?>">
            <i class="fas fa-calculator"></i> Hitung Kembalian
        </button>
    </div>
    
    <div class="change-amount">
        <p>Kembalian: <span id="change-amount"></span></p>
    </div>
    
    <div class="action-buttons">
        <button type="button" id="print-receipt-btn" class="print-btn" 
            data-order-id="<?php echo $order_id; ?>" 
            data-order-number="<?php echo $order['order_number']; ?>"
            data-total="<?php echo $order['total_price']; ?>"
            data-date="<?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?>">
            <i class="fas fa-print"></i> Cetak Struk
        </button>
        
        <form method="post" action="kelola-pesanan.php" class="cancel-form">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
            <input type="hidden" name="new_status" value="cancelled">
            <button type="submit" name="update_status" class="cancel-btn">
                <i class="fas fa-times-circle"></i> Batalkan Pesanan
            </button>
        </form>
    </div>
</div>

<!-- Template untuk cetak struk (hidden) -->
<div id="receipt-template" style="display: none;">
    <div class="receipt" id="receipt-content">
        <div class="receipt-header">
            <h2>MAKAN MANIA</h2>
            <p>Jl. Contoh No. 123, Kota Contoh</p>
            <p>Telp: 021-1234567</p>
            <hr>
            <p>STRUK PEMBAYARAN</p>
            <hr>
        </div>
        <div class="receipt-info">
            <p>No. Pesanan: <span id="print-order-number"></span></p>
            <p>Tanggal: <span id="print-date"></span></p>
            <p>Kasir: <span><?php echo $username; ?></span></p>
        </div>
        <div class="receipt-items">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="print-items">
                    <?php foreach($items_array as $item): 
                        $subtotal = $item['quantity'] * $item['price'];
                    ?>
                    <tr>
                        <td><?php echo $item['name']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="receipt-total">
            <p>Total: Rp <span id="print-total"></span></p>
            <p>Bayar: Rp <span id="print-paid"></span></p>
            <p>Kembali: Rp <span id="print-change"></span></p>
        </div>
        <div class="receipt-footer">
            <p>Terima Kasih Atas Kunjungan Anda</p>
            <p>Silahkan Datang Kembali</p>
        </div>
    </div>
</div> 