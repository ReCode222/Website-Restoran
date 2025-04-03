<?php
session_start();
require_once '../database/config-login.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fungsi untuk mendapatkan data pesanan berdasarkan rentang waktu
function getOrderData($start_date = null, $end_date = null) {
    global $conn;
    
    // Jika tanggal tidak diset, gunakan 7 hari terakhir sebagai default
    if (!$start_date) {
        $start_date = date('Y-m-d', strtotime('-7 days'));
    }
    if (!$end_date) {
        $end_date = date('Y-m-d');
    }
    
    $sql = "SELECT 
                DATE(created_at) as order_date,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                COALESCE(SUM(total_price), 0) as total_revenue
            FROM orders 
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at)";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error in getOrderData: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk mendapatkan ringkasan data berdasarkan rentang waktu
function getOrderSummary($start_date = null, $end_date = null) {
    global $conn;
    
    if (!$start_date) {
        $start_date = date('Y-m-d', strtotime('-7 days'));
    }
    if (!$end_date) {
        $end_date = date('Y-m-d');
    }
    
    $sql = "SELECT 
                COALESCE(SUM(total_price), 0) as total_revenue,
                COUNT(*) as total_orders,
                COALESCE(AVG(total_price), 0) as avg_order
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ?";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error in getOrderSummary: " . $e->getMessage());
        return [
            'total_revenue' => 0,
            'total_orders' => 0,
            'avg_order' => 0
        ];
    }
}

// Fungsi untuk mendapatkan pesanan berdasarkan rentang tanggal
function getAllOrders($start_date = null, $end_date = null) {
    global $conn;
    
    if (!$start_date) {
        $start_date = date('Y-m-d', strtotime('-7 days'));
    }
    if (!$end_date) {
        $end_date = date('Y-m-d');
    }
    
    $sql = "SELECT id, total_price, status, created_at 
            FROM orders 
            WHERE DATE(created_at) BETWEEN ? AND ?
            ORDER BY created_at DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error in getAllOrders: " . $e->getMessage());
        return [];
    }
}

// Inisialisasi data dengan rentang tanggal
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$orderData = getOrderData($start_date, $end_date);
$summary = getOrderSummary($start_date, $end_date);
$allOrders = getAllOrders($start_date, $end_date);

// Format data untuk grafik
$chartLabels = [];
$pendingData = [];
$processingData = [];
$completedData = [];
$cancelledData = [];

foreach ($orderData as $data) {
    $chartLabels[] = date('d/m', strtotime($data['order_date']));
    $pendingData[] = (int)($data['pending'] ?? 0);
    $processingData[] = (int)($data['processing'] ?? 0);
    $completedData[] = (int)($data['completed'] ?? 0);
    $cancelledData[] = (int)($data['cancelled'] ?? 0);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Pesanan - Restoran Makanan Mania</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/read-pesanan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <a href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="crud-menu.php">
                    <i class="fas fa-utensils"></i> Kelola Menu
                </a>
                <a href="read-pesanan.php" class="active">
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
                <h1>Lihat Pesanan</h1>
                <div class="user-info">
                    <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </header>

            <!-- Chart Section -->
            <section class="chart-section">
                <div class="chart-filters">
                    <div class="filter-group">
                        <label for="startDate">Dari Tanggal:</label>
                        <input type="date" id="startDate" value="<?php echo $start_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="endDate">Sampai Tanggal:</label>
                        <input type="date" id="endDate" value="<?php echo $end_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <button onclick="updateChart()" class="filter-btn">
                        <i class="fas fa-filter"></i> Terapkan Filter
                    </button>
                    <button onclick="exportPDF()" class="export-btn">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>

                <div class="sales-summary">
                    <div class="summary-card">
                        <h3>Total Pendapatan</h3>
                        <div class="value" id="totalRevenue">Rp <?php echo number_format($summary['total_revenue'], 0, ',', '.'); ?></div>
                    </div>
                    <div class="summary-card">
                        <h3>Total Pesanan</h3>
                        <div class="value" id="totalOrders"><?php echo $summary['total_orders']; ?></div>
                    </div>
                    <div class="summary-card">
                        <h3>Rata-rata Pesanan</h3>
                        <div class="value" id="avgOrder">Rp <?php echo number_format($summary['avg_order'], 0, ',', '.'); ?></div>
                    </div>
                </div>

                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>

                <div class="table-container">
                    <table class="orders-table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allOrders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php 
                                        switch($order['status']) {
                                            case 'pending':
                                                echo 'Menunggu';
                                                break;
                                            case 'processing':
                                                echo 'Diproses';
                                                break;
                                            case 'completed':
                                                echo 'Selesai';
                                                break;
                                            case 'cancelled':
                                                echo 'Dibatalkan';
                                                break;
                                            default:
                                                echo ucfirst($order['status']);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Menunggu',
                    data: <?php echo json_encode($pendingData); ?>,
                    backgroundColor: '#FFA726' // Orange
                },
                {
                    label: 'Diproses',
                    data: <?php echo json_encode($processingData); ?>,
                    backgroundColor: '#2196F3' // Blue
                },
                {
                    label: 'Selesai',
                    data: <?php echo json_encode($completedData); ?>,
                    backgroundColor: '#4CAF50' // Green
                },
                {
                    label: 'Dibatalkan',
                    data: <?php echo json_encode($cancelledData); ?>,
                    backgroundColor: '#F44336' // Red
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: false
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Statistik Status Pesanan',
                        font: {
                            size: 16
                        }
                    }
                }
            }
        });

        // Fungsi untuk update semua data
        function updateChart() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            // Validasi tanggal
            if (startDate > endDate) {
                alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir');
                return;
            }

            // Update grafik, tabel, dan ringkasan
            fetch(`get_order_data.php?start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    // Update chart
                    salesChart.data.labels = data.labels;
                    salesChart.data.datasets[0].data = data.pending;
                    salesChart.data.datasets[1].data = data.processing;
                    salesChart.data.datasets[2].data = data.completed;
                    salesChart.data.datasets[3].data = data.cancelled;
                    salesChart.update();

                    // Update summary cards
                    document.getElementById('totalRevenue').textContent = 
                        'Rp ' + new Intl.NumberFormat('id-ID').format(data.summary.total_revenue);
                    document.getElementById('totalOrders').textContent = 
                        data.summary.total_orders;
                    document.getElementById('avgOrder').textContent = 
                        'Rp ' + new Intl.NumberFormat('id-ID').format(data.summary.avg_order);

                    // Update table
                    const tableBody = document.querySelector('#ordersTable tbody');
                    tableBody.innerHTML = data.orders.map(order => `
                        <tr>
                            <td>${order.id}</td>
                            <td>Rp ${new Intl.NumberFormat('id-ID').format(order.total_price)}</td>
                            <td>
                                <span class="status-badge status-${order.status.toLowerCase()}">
                                    ${getStatusInIndonesian(order.status)}
                                </span>
                            </td>
                            <td>${formatDate(order.created_at)}</td>
                        </tr>
                    `).join('');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data');
                });
        }

        // Fungsi helper untuk konversi status ke Bahasa Indonesia
        function getStatusInIndonesian(status) {
            const statusMap = {
                'pending': 'Menunggu',
                'processing': 'Diproses',
                'completed': 'Selesai',
                'cancelled': 'Dibatalkan'
            };
            return statusMap[status] || status;
        }

        // Fungsi helper untuk format tanggal
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).replace(',', '');
        }

        // Event listener untuk tanggal
        document.getElementById('startDate').addEventListener('change', function() {
            const endDate = document.getElementById('endDate');
            endDate.min = this.value; // Set minimum end date
        });

        document.getElementById('endDate').addEventListener('change', function() {
            const startDate = document.getElementById('startDate');
            startDate.max = this.value; // Set maximum start date
        });

        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // Fungsi untuk export PDF
        function exportPDF() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            // Redirect ke halaman export dengan parameter tanggal
            window.location.href = `export-pesanan.php?start_date=${startDate}&end_date=${endDate}`;
        }
    </script>
</body>
</html>
