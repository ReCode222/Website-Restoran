<?php
session_start();
require_once '../database/config-login.php';
require_once '../vendor/tecnickcom/tcpdf/tcpdf.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil parameter tanggal
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Ambil data pesanan
$sql = "SELECT id, total_price, status, created_at 
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ?
        ORDER BY created_at DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);

    // Buat instance TCPDF
    class MYPDF extends TCPDF {
        public function Header() {
            // Logo
            $image_file = '../assets/logo.png';
            $this->Image($image_file, 15, 10, 20, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            
            // Judul
            $this->SetFont('helvetica', 'B', 16);
            $this->SetY(10);
            $this->Cell(0, 15, 'Laporan Pesanan Restoran Makanan Mania', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            
            // Alamat
            $this->SetFont('helvetica', '', 10);
            $this->SetY(20);
            $this->MultiCell(0, 5, 
                "Jl. Duren Raya - Desa Sukaragam Kec. Serang Baru\n".
                "Kabupaten Bekasi - Jawa Barat",
                0, 'C', 0, 1, '', '', true);

            // Garis bawah alamat
            $this->Line(10, 35, 195, 35);
        }
    }

    // Inisialisasi PDF
    $pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set informasi dokumen
    $pdf->SetCreator('Restoran Makanan Mania');
    $pdf->SetAuthor('Admin Restoran');
    $pdf->SetTitle('Laporan Pesanan');

    // Set margin
    $pdf->SetMargins(15, 45, 15);
    $pdf->SetHeaderMargin(3);
    $pdf->SetFooterMargin(10);

    // Add halaman
    $pdf->AddPage();

    // Tambah informasi periode dan user
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Ln(1);
    $pdf->Cell(0, 7, 'Periode: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)), 0, 1, 'L');
    $pdf->Cell(0, 7, 'Data diexport oleh: ' . $_SESSION['username'], 0, 1, 'L');
    $pdf->Ln(5);

    // Header tabel
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(30, 10, 'ID Pesanan', 1, 0, 'C', 1);
    $pdf->Cell(50, 10, 'Total Harga', 1, 0, 'C', 1);
    $pdf->Cell(50, 10, 'Status', 1, 0, 'C', 1);
    $pdf->Cell(50, 10, 'Tanggal', 1, 1, 'C', 1);

    // Isi tabel
    $pdf->SetFont('helvetica', '', 10);
    foreach($orders as $order) {
        // Konversi status ke Bahasa Indonesia
        switch($order['status']) {
            case 'pending':
                $status = 'Menunggu';
                break;
            case 'processing':
                $status = 'Diproses';
                break;
            case 'completed':
                $status = 'Selesai';
                break;
            case 'cancelled':
                $status = 'Dibatalkan';
                break;
            default:
                $status = ucfirst($order['status']);
        }

        $pdf->Cell(30, 10, $order['id'], 1, 0, 'C');
        $pdf->Cell(50, 10, 'Rp ' . number_format($order['total_price'], 0, ',', '.'), 1, 0, 'C');
        $pdf->Cell(50, 10, $status, 1, 0, 'C');
        $pdf->Cell(50, 10, date('d/m/Y H:i', strtotime($order['created_at'])), 1, 1, 'C');
    }

    // Output PDF
    $pdf->Output('Laporan_Pesanan_' . date('Y-m-d') . '.pdf', 'D');

} catch (Exception $e) {
    error_log("Error in export-pesanan: " . $e->getMessage());
    header("Location: read-pesanan.php?error=export_failed");
    exit();
}
