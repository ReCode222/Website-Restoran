<?php
session_start();
require_once '../database/config-login.php';
require_once '../vendor/tecnickcom/tcpdf/tcpdf.php';

// Cek login admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil tanggal dari parameter GET
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Query untuk mengambil data pesanan
$sql = "SELECT id, total_price, created_at 
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ? 
        ORDER BY created_at DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);

    // Kelas MYPDF untuk kustomisasi header dan footer
    class MYPDF extends TCPDF
    {
        public function Header()
        {
            $this->SetY(10);
            $this->Image('../assets/logo.png', 15, 8, 18, '', 'PNG');
            $this->SetFont('helvetica', 'B', 16);
            $this->Cell(0, 8, 'Restoran Makan Mania', 0, 1, 'C');
            $this->SetFont('helvetica', '', 10);
            $this->Cell(0, 5, 'Jl. Duren Raya - Desa Sukaragam Kec. Serang Baru', 0, 1, 'C');
            $this->Cell(0, 5, 'Kabupaten Bekasi - Jawa Barat', 0, 1, 'C');
            $this->Line(10, 30, 200, 30);
        }

        public function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, 'Halaman ' . $this->getAliasNumPage() . ' dari ' . $this->getAliasNbPages(), 0, false, 'C');
            // Tambahkan garis horizontal sebelum nomor halaman
            $this->Line(10, $this->GetY() - 2, 200, $this->GetY() - 2);
        }
    }

    // Inisialisasi PDF
    $pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Restoran Makan Mania');
    $pdf->SetAuthor('Admin Restoran');
    $pdf->SetTitle('Laporan Rekapitulasi Pesanan');
    $pdf->SetMargins(15, 35, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);
    $pdf->AddPage();

    // Judul laporan
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'LAPORAN REKAPITULASI PESANAN', 0, 1, 'C');
    $pdf->Ln(2);

    // Informasi laporan
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 7, 'Periode: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)), 0, 1, 'L');
    $pdf->Cell(0, 7, 'Data diexport oleh: ' . $_SESSION['username'], 0, 1, 'L');
    $pdf->Ln(5);

    // Header tabel
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(30, 10, 'ID Pesanan', 1, 0, 'C', 1);
    $pdf->Cell(70, 10, 'Tanggal', 1, 0, 'C', 1);
    $pdf->Cell(70, 10, 'Total Harga', 1, 1, 'C', 1);

    // Isi tabel
    $pdf->SetFont('helvetica', '', 10);
    foreach ($orders as $order) {
        $pdf->Cell(30, 8, $order['id'], 1, 0, 'C');
        $pdf->Cell(70, 8, date('d/m/Y H:i', strtotime($order['created_at'])), 1, 0, 'C');
        $pdf->Cell(70, 8, 'Rp ' . number_format($order['total_price'], 0, ',', '.'), 1, 1, 'C');
    }

    // Tanda tangan
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 11);

    $tandaTanganX = 135; 
    $pdf->SetX($tandaTanganX);
    $pdf->Cell(0, 7, 'Bekasi, ' . date('d F Y'), 0, 1, 'L');

    $pdf->Ln(20);
    $pdf->SetX($tandaTanganX);
    $pdf->Cell(0, 7, '( ' . $_SESSION['username'] . ' )', 0, 1, 'L');


    // Output PDF
    $pdf->Output('Laporan_Pesanan_' . date('Y-m-d') . '.pdf', 'D');

} catch (Exception $e) {
    error_log("Error in export-pesanan: " . $e->getMessage());
    header("Location: read-pesanan.php?error=export_failed");
    exit();
}
?>