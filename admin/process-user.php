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
} 