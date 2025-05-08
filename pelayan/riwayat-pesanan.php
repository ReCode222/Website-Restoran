<?php
session_start();
require_once('../database/config-login.php');

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelayan') {
    header("Location: ../login.php");
    exit();
}

// Get username for welcome message
$user_id = $_SESSION['user_id'];
$query_user = "SELECT username FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $query_user);
$user_data = mysqli_fetch_assoc($result_user);
$username = $user_data['username'];

// Get all orders except pending
$query_orders = "SELECT id, order_number, total_price, status, created_at FROM orders WHERE status='completed' ORDER BY created_at DESC";
$result_orders = mysqli_query($conn, $query_orders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Pelayan</title>
    <link rel="stylesheet" href="../css/pelayan-sidebar.css">
    <link rel="stylesheet" href="../css/kelola-pesanan-kasir.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="../assets/logo.png" alt="Logo Restoran">
            </div>
            <nav>
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="kelola-pesanan.php">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Kelola Pesanan</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="riwayat-pesanan.php">
                            <i class="fas fa-history"></i>
                            <span>Riwayat Pesanan</span>
                        </a>
                    </li>
                    <li>
                        <a href="../login.php" class="logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="main-content">
            <header>
                <h1>Riwayat Pesanan</h1>
                <div class="user-welcome">
                    <p>Selamat datang, <span class="username"><?php echo $username; ?></span></p>
                </div>
            </header>

            <div class="orders-container">
                <div class="order-header">
                    <h2>Daftar Riwayat Pesanan</h2>
                    <div class="refresh-btn" id="refresh-btn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </div>
                </div>

                <div class="table-container" id="orders-table">
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
                                        <span class="status <?php echo $order['status']; ?>">
                                            <?php 
                                            switch($order['status']) {
                                                case 'processing':
                                                    echo 'Diproses';
                                                    break;
                                                case 'completed':
                                                    echo 'Selesai';
                                                    break;
                                                case 'cancelled':
                                                    echo 'Dibatalkan';
                                                    break;
                                            }
                                            ?>
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
                                    <td colspan="6" class="no-data">Tidak ada riwayat pesanan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Pesanan -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Detail Pesanan</h2>
            <div id="detail-content">
                <!-- Detail pesanan akan dimuat di sini -->
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Auto refresh setiap 30 detik
            setInterval(function() {
                refreshOrdersTable();
            }, 30000);

            // Refresh button
            $('#refresh-btn').click(function() {
                refreshOrdersTable();
            });

            // Detail pesanan
            $(document).on('click', '.detail-btn', function() {
                const orderId = $(this).data('id');
                loadOrderDetails(orderId);
            });

            // Tutup modal
            $('.close').click(function() {
                $('#detailModal').hide();
            });

            // Tutup modal ketika klik di luar konten
            $(window).click(function(e) {
                if ($(e.target).is('#detailModal')) {
                    $('#detailModal').hide();
                }
            });
        });

        function refreshOrdersTable() {
            $.ajax({
                url: 'ajax/get-order-history.php',
                type: 'GET',
                success: function(response) {
                    $('#orders-table').html(response);
                }
            });
        }

        function loadOrderDetails(orderId) {
            $.ajax({
                url: 'ajax/get-order-history-detail.php',
                type: 'GET',
                data: { id: orderId },
                success: function(response) {
                    $('#detail-content').html(response);
                    $('#detailModal').show();
                }
            });
        }
    </script>
</body>
</html> 