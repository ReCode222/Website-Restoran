<?php
session_start();
require_once '../database/config-login.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $username = $_POST['username'];
        $password = $_POST['password']; // Password tidak di-hash
        $role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);

        if ($stmt->execute()) {
            header("Location: akun.php?success=added");
        } else {
            header("Location: akun.php?error=add_failed");
        }
    }

    elseif ($action === 'edit') {
        $user_id = $_POST['user_id'];
        $role = $_POST['role'];

        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("si", $role, $user_id);

        if ($stmt->execute()) {
            header("Location: akun.php?success=updated");
        } else {
            header("Location: akun.php?error=update_failed");
        }
    }

    elseif ($action === 'change_password') {
        $user_id = $_POST['user_id'];
        $new_password = $_POST['new_password']; // Password tidak di-hash

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("si", $new_password, $user_id);

        if ($stmt->execute()) {
            header("Location: akun.php?success=password_changed");
        } else {
            header("Location: akun.php?error=password_change_failed");
        }
    }

    elseif ($action === 'delete') {
        $user_id = $_POST['user_id'];
        
        // Cek apakah user yang akan dihapus adalah admin
        $check_sql = "SELECT role FROM users WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user['role'] === 'admin') {
            echo json_encode(['status' => 'error', 'message' => 'Tidak dapat menghapus akun admin']);
            exit();
        }
        
        // Hapus user
        $delete_sql = "DELETE FROM users WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'User berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus user']);
        }
    }
} 