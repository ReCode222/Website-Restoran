<?php
session_start();
require_once('../database/config-login.php');

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header("Location: ../login.php");
    exit();
}

// Get username for welcome message
$user_id = $_SESSION['user_id'];
$query_user = "SELECT username FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $query_user);
$user_data = mysqli_fetch_assoc($result_user);
$username = $user_data['username'];

// Handle status update
if(isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $update_query = "UPDATE orders SET status = '$new_status' WHERE id = $order_id";
    
    if(mysqli_query($conn, $update_query)) {
        $message = "Status pesanan berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui status pesanan: " . mysqli_error($conn);
    }
}

// Get pending orders
$query_orders = "SELECT id, order_number, total_price, status, created_at FROM orders WHERE status = 'pending' ORDER BY created_at DESC";
$result_orders = mysqli_query($conn, $query_orders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Kasir</title>
    <link rel="stylesheet" href="../css/kasir-sidebar.css">
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
                    <li class="active">
                        <a href="kelola-pesanan.php">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Kelola Pesanan</span>
                        </a>
                    </li>
                    <li>
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
                <h1>Kelola Pesanan</h1>
                <div class="user-welcome">
                    <p>Selamat datang, <span class="username"><?php echo $username; ?></span></p>
                </div>
            </header>

            <div class="orders-container">
                <?php if(isset($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="order-header">
                    <h2>Daftar Pesanan Menunggu Pembayaran</h2>
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
            
            // Hitung kembalian
            $(document).on('click', '.calculate-btn', function() {
                const totalAmount = parseFloat($(this).data('total'));
                const paidAmount = parseFloat($('#paid-amount').val());
                
                if (isNaN(paidAmount)) {
                    alert('Silakan masukkan jumlah pembayaran');
                    return;
                }
                
                if (paidAmount < totalAmount) {
                    alert('Pembayaran kurang! Total yang harus dibayar: Rp ' + totalAmount.toLocaleString('id-ID'));
                    $('#change-amount').text('');
                } else {
                    const change = paidAmount - totalAmount;
                    $('#change-amount').html('Rp ' + change.toLocaleString('id-ID'));
                }
            });

            // Modifikasi handler tombol cetak struk
            $(document).on('click', '#print-receipt-btn', function() {
                const orderId = $(this).data('order-id');
                const orderNumber = $(this).data('order-number');
                const total = $(this).data('total');
                const date = $(this).data('date');
                const paidAmount = parseFloat($('#paid-amount').val());
                
                // Validasi pembayaran
                if (isNaN(paidAmount)) {
                    alert('Silakan masukkan jumlah pembayaran terlebih dahulu');
                    return;
                }
                
                if (paidAmount < total) {
                    alert('Pembayaran kurang! Total yang harus dibayar: Rp ' + total.toLocaleString('id-ID'));
                    return;
                }
                
                // Tutup modal detail pesanan SEBELUM cetak dan pemrosesan lainnya
                $('#detailModal').hide();
                
                // Cetak struk menggunakan extension
                printThermalReceipt(orderId, orderNumber, total, date, paidAmount);
                
                // Update status pesanan menjadi processing
                updateOrderStatus(orderId, 'processing');
                refreshOrdersTable();
            });
            
            function printThermalReceipt(orderId, orderNumber, total, date, paidAmount) {
                // Dapatkan detail item pesanan dari tabel yang sudah ada
                const items = [];
                $('#print-items tr').each(function() {
                    const cells = $(this).find('td');
                    if (cells.length > 0) {
                        items.push({
                            name: $(cells[0]).text(),
                            qty: parseInt($(cells[1]).text()),
                            price: parseNumeric($(cells[2]).text()),
                            subtotal: parseNumeric($(cells[3]).text())
                        });
                    }
                });
                
                const change = paidAmount - total;
                
                // Kirim data ke server untuk mencetak struk
                $.ajax({
                    url: 'ajax/print-receipt.php',
                    type: 'POST',
                    data: {
                        order_id: orderId,
                        order_number: orderNumber,
                        date: date,
                        items: JSON.stringify(items),
                        total: total,
                        paid: paidAmount,
                        change: change,
                        kasir: '<?php echo $username; ?>'
                    },
                    success: function(response) {
                        if (response === 'success') {
                            alert('Struk berhasil dicetak');
                            refreshOrdersTable();
                        } else {
                            // Fallback ke metode cetak browser jika extension tidak tersedia
                            fallbackBrowserPrint(orderNumber, date, items, total, paidAmount, change);
                            refreshOrdersTable();
                        }
                    },
                    error: function() {
                        // Fallback ke metode cetak browser
                        fallbackBrowserPrint(orderNumber, date, items, total, paidAmount, change);
                        refreshOrdersTable();
                    }
                });
            }
            
            function parseNumeric(str) {
                // Fungsi untuk mengubah string format uang ke numerik
                return parseFloat(str.replace(/[^\d,-]/g, '').replace(',', '.'));
            }
            
            function fallbackBrowserPrint(orderNumber, date, items, total, paidAmount, change) {
                // Fungsi fallback jika extension tidak tersedia
                
                // Isi data pada template struk
                $('#print-order-number').text(orderNumber);
                $('#print-date').text(date);
                $('#print-total').text(total.toLocaleString('id-ID'));
                $('#print-paid').text(paidAmount.toLocaleString('id-ID'));
                $('#print-change').text(change.toLocaleString('id-ID'));
                
                // Buat window baru untuk cetak
                const printWindow = window.open('', '_blank', 'width=400,height=600');
                
                // CSS untuk struk
                const receiptStyle = `
                    <style>
                        @page {
                            size: 80mm auto;  /* Ukuran kertas struk thermal */
                            margin: 0mm;
                        }
                        body { 
                            font-family: 'Courier New', monospace;
                            font-size: 12px;
                            margin: 0;
                            padding: 0;
                        }
                        .receipt {
                            width: 76mm; /* Lebar struk thermal standard */
                            margin: 0 auto;
                            padding: 2mm;
                        }
                        .receipt-header {
                            text-align: center;
                            margin-bottom: 3mm;
                        }
                        .receipt-header h2 {
                            font-size: 14px;
                            margin: 0;
                        }
                        .receipt-header p {
                            margin: 1mm 0;
                        }
                        hr {
                            border: none;
                            border-top: 1px dashed #000;
                            margin: 2mm 0;
                        }
                        .receipt-info {
                            margin-bottom: 3mm;
                        }
                        .receipt-info p {
                            margin: 1mm 0;
                            font-size: 11px;
                        }
                        .receipt-items table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 3mm;
                        }
                        .receipt-items th {
                            font-size: 11px;
                            text-align: left;
                            border-bottom: 1px dashed #000;
                            padding-bottom: 1mm;
                        }
                        .receipt-items td {
                            font-size: 11px;
                            padding: 1mm 0;
                        }
                        .receipt-total {
                            margin-top: 3mm;
                            text-align: right;
                            border-top: 1px dashed #000;
                            padding-top: 2mm;
                        }
                        .receipt-total p {
                            margin: 1mm 0;
                            font-weight: bold;
                        }
                        .receipt-footer {
                            margin-top: 5mm;
                            text-align: center;
                            font-size: 11px;
                        }
                        .receipt-footer p {
                            margin: 1mm 0;
                        }
                    </style>
                `;
                
                // Isi window cetak
                printWindow.document.write('<html><head>');
                printWindow.document.write('<title>Struk Pembayaran</title>');
                printWindow.document.write(receiptStyle);
                printWindow.document.write('</head><body>');
                printWindow.document.write($('#receipt-content').html());
                printWindow.document.write('</body></html>');
                
                printWindow.document.close();
                
                // Tunggu hingga konten dimuat
                printWindow.onload = function() {
                    // Cetak dokumen
                    printWindow.print();
                    // Tutup window setelah cetak
                    printWindow.onafterprint = function() {
                        printWindow.close();
                    };
                };
            }
            
            function updateOrderStatus(orderId, newStatus) {
                $.ajax({
                    url: 'ajax/update-order-status.php',
                    type: 'POST',
                    data: {
                        order_id: orderId,
                        status: newStatus
                    },
                    success: function(response) {
                        if (response === 'success') {
                            // Refresh tabel pesanan
                            refreshOrdersTable();
                            // Tampilkan notifikasi
                            alert('Status pesanan berhasil diperbarui menjadi \'Processing\'');
                        } 
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat memperbarui status pesanan');
                    }
                });
            }
        });

        function refreshOrdersTable() {
            $.ajax({
                url: 'ajax/get-orders.php',
                type: 'GET',
                success: function(response) {
                    $('#orders-table').html(response);
                }
            });
        }

        function loadOrderDetails(orderId) {
            $.ajax({
                url: 'ajax/get-order-details.php',
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
