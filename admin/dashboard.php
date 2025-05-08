<?php
session_start();
require_once '../database/config-login.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil data pesanan dari database
$sql_total_pesanan = "SELECT COUNT(*) as total FROM orders";
$sql_pesanan_sukses = "SELECT COUNT(*) as sukses FROM orders WHERE status = 'completed'";
$sql_pesanan_pending = "SELECT COUNT(*) as pending FROM orders WHERE status = 'pending'";
$sql_pesanan_proses = "SELECT COUNT(*) as proses FROM orders WHERE status = 'processing'";
$sql_pesanan_batal = "SELECT COUNT(*) as batal FROM orders WHERE status = 'cancelled'";

$result_total = $conn->query($sql_total_pesanan);
$result_sukses = $conn->query($sql_pesanan_sukses);
$result_pending = $conn->query($sql_pesanan_pending);
$result_proses = $conn->query($sql_pesanan_proses);
$result_batal = $conn->query($sql_pesanan_batal);

$total_pesanan = $result_total->fetch_assoc()['total'];
$pesanan_sukses = $result_sukses->fetch_assoc()['sukses'];
$pesanan_pending = $result_pending->fetch_assoc()['pending'];
$pesanan_proses = $result_proses->fetch_assoc()['proses'];
$pesanan_batal = $result_batal->fetch_assoc()['batal'];

// Query untuk data grafik
function getOrderData($period = 'day') {
    global $conn;
    
    switch($period) {
        case 'month':
            $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as date, 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
                    FROM orders 
                    WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY date";
            break;
        case 'year':
            $sql = "SELECT YEAR(created_at) as date,
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
                    FROM orders 
                    GROUP BY YEAR(created_at)
                    ORDER BY date";
            break;
        default: // day
            $sql = "SELECT DATE(created_at) as date,
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
                    FROM orders
                    GROUP BY DATE(created_at)
                    ORDER BY date";
    }
    
    $result = $conn->query($sql);
    if (!$result) {
        // Jika query error, kembalikan array kosong
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

$period = isset($_GET['period']) ? $_GET['period'] : 'day';
$orderData = getOrderData($period);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Restoran Makan Mania</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="logo-container">
                <img src="../assets/logo.png" alt="Logo" class="logo">
                <h2>Admin Panel</h2>
            </div>
            <nav class="nav-menu">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="crud-menu.php">
                    <i class="fas fa-utensils"></i> Kelola Menu
                </a>
                <a href="read-pesanan.php">
                    <i class="fas fa-clipboard-list"></i> Lihat Pesanan
                </a>
                <a href="akun.php">
                    <i class="fas fa-user"></i> Kelola Akun
                </a>
                <a href="../login.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <button id="toggleSidebar" class="toggle-btn">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Dashboard Admin</h1>
                <div class="user-info">
                    <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </header>

            <!-- Filter dan Grafik -->
            <div class="chart-container">
                <div class="chart-header">
                    <h2>Grafik Penjualan</h2>
                    <div class="period-filter">
                        <select id="periodFilter" onchange="changePeriod(this.value)">
                            <option value="day" <?php echo $period == 'day' ? 'selected' : ''; ?>>Harian</option>
                            <option value="month" <?php echo $period == 'month' ? 'selected' : ''; ?>>Bulanan</option>
                            <option value="year" <?php echo $period == 'year' ? 'selected' : ''; ?>>Tahunan</option>
                        </select>
                    </div>
                </div>
                <div style="height: 400px; max-width: 800px; margin: 0 auto;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambahkan Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data dari PHP
        const orderData = <?php echo json_encode($orderData); ?>;
        
        // Siapkan data untuk grafik
        const labels = orderData.map(item => item.date);
        const datasets = [
            {
                label: 'Pesanan Selesai',
                data: orderData.map(item => item.completed),
                backgroundColor: '#4CAF50',
                borderColor: '#388E3C',
                borderWidth: 1,
                borderRadius: 5
            }
        ];

        // Buat grafik
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal'
                        },
                        barPercentage: 0.8,
                        categoryPercentage: 0.9,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Jumlah Pesanan'
                        },
                        grid: {
                            color: '#f0f0f0'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Statistik Pesanan Selesai',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            bottom: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.raw} pesanan selesai`;
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 10,
                        cornerRadius: 5
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Fungsi untuk mengubah periode
        function changePeriod(period) {
            window.location.href = `dashboard.php?period=${period}`;
        }

        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
            document.querySelector('.main-content').classList.toggle('expanded');
        });
    </script>
</body>
</html>
