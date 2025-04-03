<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restoran Makan Mania</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (isset($_GET['order'])) : ?>
    <meta http-equiv="refresh" content="5">
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <img src="assets/logo.png" alt="Logo Restoran" class="logo">
        <h1>Restoran Makan Mania</h1>
        
        <?php
        // Debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Tambahkan koneksi database
        require_once 'database/config-login.php';

        if (isset($_GET['order'])) {
            $orderNumber = htmlspecialchars($_GET['order']);
            
            // Debug query
            $sql = "SELECT * FROM orders WHERE order_number = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $orderNumber);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $order = mysqli_fetch_assoc($result);
            
            echo "<!-- Debug Order: ";
            print_r($order);
            echo " -->";
        }
        
        // Tampilkan parameter GET untuk debugging
        echo "<!-- Debug: ";
        print_r($_GET);
        echo " -->";
        
        // Tambahkan fungsi untuk mengambil status pesanan
        function getOrderStatus($orderNumber) {
            global $conn;
            
            // Debug koneksi
            if (!$conn) {
                error_log("Database connection failed");
                return 'pending';
            }
            
            $sql = "SELECT status FROM orders WHERE order_number = ?";
            $stmt = mysqli_prepare($conn, $sql);
            
            if (!$stmt) {
                error_log("Prepare statement failed: " . mysqli_error($conn));
                return 'pending';
            }
            
            mysqli_stmt_bind_param($stmt, "i", $orderNumber);
            
            if (!mysqli_stmt_execute($stmt)) {
                error_log("Execute statement failed: " . mysqli_stmt_error($stmt));
                return 'pending';
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            
            // Debug hasil query
            error_log("Order query result: " . print_r($row, true));
            
            return $row['status'] ?? 'pending';
        }
        
        // Tampilkan status pesanan jika ada parameter order
        if (isset($_GET['order'])) {
            $orderNumber = htmlspecialchars($_GET['order']);
            $status = getOrderStatus($orderNumber);
            
            // Debugging
            error_log("Status from DB: " . $status);
            
            $statusText = '';
            $statusClass = '';
            $showOrderAgain = false;
            
            switch($status) {
                case 'completed':
                    $statusText = 'Pesanan Selesai';
                    $statusClass = 'completed';
                    $showOrderAgain = true;
                    $noteText = 'Pesanan Anda sudah selesai!';
                    break;
                case 'processing':
                    $statusText = 'Sedang Diproses';
                    $statusClass = 'processing';
                    $noteText = 'Pesanan Anda sedang diproses. Mohon tunggu sebentar!';
                    break;
                case 'cancelled':
                    $statusText = 'Dibatalkan';
                    $statusClass = 'cancelled';
                    $showOrderAgain = true;
                    $noteText = 'Pesanan Anda dibatalkan. Silahkan pesan kembali!';
                    break;
                default:
                    $statusText = 'Menunggu';
                    $statusClass = 'pending';
                    $noteText = 'Silahkan tunggu pesanan Anda';
            }
            
            echo '
            <div class="order-status">
                <div class="status-content">
                    <h3>Status Pesanan</h3>
                    <p>Nomor Antrian: <strong>' . $orderNumber . '</strong></p>
                    <p>Status: <span class="status-badge ' . $statusClass . '">' . $statusText . '</span></p>
                    <p class="order-note">' . $noteText . '</p>';
            
            if ($showOrderAgain) {
                echo '<button onclick="window.location.href=\'user/menu.php\'" class="btn-order-again">
                        Pesan Lagi
                      </button>';
            }
            
            echo '</div></div>';
        } else {
            echo '
            <div class="buttons">
                <a href="user/menu.php" class="btn-primary">Pesan Sekarang</a>
            </div>';
        }
        ?>
    </div>
    <?php if (isset($_GET['order'])) : ?>
    <script>
    function checkOrderStatus() {
        fetch('check_status.php?order=<?php echo $orderNumber; ?>')
            .then(response => response.json())
            .then(data => {
                if (data.status !== '<?php echo $status; ?>') {
                    location.reload();
                }
            });
    }

    setInterval(checkOrderStatus, 5000);
    </script>
    <?php endif; ?>
</body>
</html>