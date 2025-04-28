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

// Get pending orders
$query_pending = "SELECT COUNT(*) as pending_count FROM orders WHERE status = 'pending'";
$result_pending = mysqli_query($conn, $query_pending);
$pending_count = mysqli_fetch_assoc($result_pending)['pending_count'];

// Get total orders for today
$query_today = "SELECT COUNT(*) as today_count FROM orders WHERE DATE(created_at) = CURDATE()";
$result_today = mysqli_query($conn, $query_today);
$today_count = mysqli_fetch_assoc($result_today)['today_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir</title>
    <link rel="stylesheet" href="../css/kasir-sidebar.css">
    <link rel="stylesheet" href="../css/dashboard-kasir.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="../assets/logo.png" alt="Logo Restoran">
            </div>
            <nav>
                <ul>
                    <li class="active">
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
                <h1>Dashboard Kasir</h1>
                <div class="user-welcome">
                    <p>Selamat datang, <span class="username"><?php echo $username; ?></span></p>
                </div>
            </header>

            <div class="dashboard-cards">
                <div class="card pending-orders">
                    <i class="fas fa-hourglass-half"></i>
                    <div class="card-info">
                        <h3>Pesanan Menunggu Pembayaran</h3>
                        <p class="count"><?php echo $pending_count; ?></p>
                    </div>
                </div>

                <div class="card total-orders">
                    <i class="fas fa-calendar-day"></i>
                    <div class="card-info">
                        <h3>Total Pesanan Hari Ini</h3>
                        <p class="count"><?php echo $today_count; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
