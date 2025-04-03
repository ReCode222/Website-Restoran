<?php
session_start();
require_once('../../database/config-login.php');

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    exit('Unauthorized');
}

// Cek parameter yang diperlukan
if (!isset($_POST['order_id']) || !isset($_POST['order_number']) || !isset($_POST['items'])) {
    exit('Invalid request');
}

// Ambil data dari POST
$order_id = $_POST['order_id'];
$order_number = $_POST['order_number'];
$date = $_POST['date'];
$items = json_decode($_POST['items'], true);
$total = $_POST['total'];
$paid = $_POST['paid'];
$change = $_POST['change'];
$kasir = $_POST['kasir'];

// Inisialisasi koneksi ke printer
try {
    // Deteksi OS untuk menentukan cara print
    $os = php_uname('s');
    $printSuccess = false;
    
    if (strpos($os, 'Windows') !== false) {
        // Windows - Menggunakan PrinterUtil atau PrintFile
        $printSuccess = printReceiptWindows($order_number, $date, $items, $total, $paid, $change, $kasir);
    } elseif (strpos($os, 'Linux') !== false) {
        // Linux - Menggunakan lpr atau CUPS
        $printSuccess = printReceiptLinux($order_number, $date, $items, $total, $paid, $change, $kasir);
    } else {
        // Sistem lain - gunakan metode umum
        $printSuccess = printReceiptGeneric($order_number, $date, $items, $total, $paid, $change, $kasir);
    }
    
    if ($printSuccess) {
        // Update status pesanan ke processing
        $update_query = "UPDATE orders SET status = 'processing' WHERE id = $order_id";
        mysqli_query($conn, $update_query);
        echo 'success';
    } else {
        echo 'Printer tidak tersedia, gunakan metode cetak browser';
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

// Fungsi untuk mencetak struk di Windows
function printReceiptWindows($order_number, $date, $items, $total, $paid, $change, $kasir) {
    try {
        // Nama printer - sesuaikan dengan nama printer thermal Anda
        $printer_name = "POS-58"; // Ganti dengan nama printer POS Anda
        
        // Buat konten struk
        $content = generateReceiptContent($order_number, $date, $items, $total, $paid, $change, $kasir);
        
        // Simpan ke file temporary
        $tempFile = sys_get_temp_dir() . '\\receipt_' . time() . '.txt';
        file_put_contents($tempFile, $content);
        
        // Cetak file menggunakan perintah print di Windows
        exec('print /d:"' . $printer_name . '" "' . $tempFile . '"', $output, $return_var);
        
        // Hapus file temporary
        unlink($tempFile);
        
        return ($return_var === 0);
    } catch (Exception $e) {
        return false;
    }
}

// Fungsi untuk mencetak struk di Linux
function printReceiptLinux($order_number, $date, $items, $total, $paid, $change, $kasir) {
    try {
        // Buat konten struk
        $content = generateReceiptContent($order_number, $date, $items, $total, $paid, $change, $kasir);
        
        // Simpan ke file temporary
        $tempFile = '/tmp/receipt_' . time() . '.txt';
        file_put_contents($tempFile, $content);
        
        // Cetak file menggunakan lpr
        exec('lpr ' . $tempFile, $output, $return_var);
        
        // Hapus file temporary
        unlink($tempFile);
        
        return ($return_var === 0);
    } catch (Exception $e) {
        return false;
    }
}

// Fungsi untuk mencetak struk pada sistem lain
function printReceiptGeneric($order_number, $date, $items, $total, $paid, $change, $kasir) {
    // Pada sistem yang tidak dikenal, kita gunakan metode browser print
    return false;
}

// Fungsi untuk menghasilkan konten struk
function generateReceiptContent($order_number, $date, $items, $total, $paid, $change, $kasir) {
    $content = "";
    
    // Header
    $content .= str_pad("MAKAN MANIA", 40, " ", STR_PAD_BOTH) . "\n";
    $content .= str_pad("Jl. Contoh No. 123, Kota Contoh", 40, " ", STR_PAD_BOTH) . "\n";
    $content .= str_pad("Telp: 021-1234567", 40, " ", STR_PAD_BOTH) . "\n";
    $content .= str_repeat("-", 40) . "\n";
    $content .= str_pad("STRUK PEMBAYARAN", 40, " ", STR_PAD_BOTH) . "\n";
    $content .= str_repeat("-", 40) . "\n\n";
    
    // Info Pesanan
    $content .= "No. Pesanan: " . $order_number . "\n";
    $content .= "Tanggal: " . $date . "\n";
    $content .= "Kasir: " . $kasir . "\n\n";
    
    // Header Item
    $content .= str_pad("Item", 20, " ", STR_PAD_RIGHT);
    $content .= str_pad("Qty", 5, " ", STR_PAD_LEFT);
    $content .= str_pad("Harga", 8, " ", STR_PAD_LEFT);
    $content .= str_pad("Total", 7, " ", STR_PAD_LEFT) . "\n";
    $content .= str_repeat("-", 40) . "\n";
    
    // Items
    foreach ($items as $item) {
        // Truncate item name if too long
        $name = substr($item['name'], 0, 19);
        $content .= str_pad($name, 20, " ", STR_PAD_RIGHT);
        $content .= str_pad($item['qty'], 5, " ", STR_PAD_LEFT);
        $content .= str_pad(number_format($item['price'], 0, ',', '.'), 8, " ", STR_PAD_LEFT);
        $content .= str_pad(number_format($item['subtotal'], 0, ',', '.'), 7, " ", STR_PAD_LEFT) . "\n";
    }
    
    $content .= str_repeat("-", 40) . "\n";
    
    // Total, Bayar, Kembali
    $content .= str_pad("Total:", 25, " ", STR_PAD_LEFT);
    $content .= str_pad("Rp " . number_format($total, 0, ',', '.'), 15, " ", STR_PAD_LEFT) . "\n";
    
    $content .= str_pad("Bayar:", 25, " ", STR_PAD_LEFT);
    $content .= str_pad("Rp " . number_format($paid, 0, ',', '.'), 15, " ", STR_PAD_LEFT) . "\n";
    
    $content .= str_pad("Kembali:", 25, " ", STR_PAD_LEFT);
    $content .= str_pad("Rp " . number_format($change, 0, ',', '.'), 15, " ", STR_PAD_LEFT) . "\n\n";
    
    // Footer
    $content .= str_repeat("-", 40) . "\n";
    $content .= str_pad("Terima Kasih Atas Kunjungan Anda", 40, " ", STR_PAD_BOTH) . "\n";
    $content .= str_pad("Silahkan Datang Kembali", 40, " ", STR_PAD_BOTH) . "\n";
    
    // Add cut command for thermal printers (ESC/POS)
    $content .= "\x1D\x56\x01"; // GS V 1 - Full cut
    
    return $content;
}
?> 