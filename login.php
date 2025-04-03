<?php
session_start();
require_once 'database/config-login.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        
        // Redirect based on role
        if ($row['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } elseif ($row['role'] == 'pelayan') {
            header("Location: pelayan/dashboard.php");
        } else {
            header("Location: kasir/dashboard.php");
        }
        exit();
    }
    
    $_SESSION['error_message'] = "Username atau password salah";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Hapus pesan error jika halaman di-reload
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Restoran Makan Mania</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <img src="assets/logo.png" alt="Logo Restoran" class="logo">
        <h1>Login</h1>
        <?php
        if (isset($error_message)) {
            echo "<p class='error'>$error_message</p>";
        }
        ?>
        <form action="login.php" method="POST" id="loginForm">
            <div class="form-group">
                <input type="text" name="username" id="username" placeholder=" " required>
                <label for="username">Username</label>
            </div>
            <div class="form-group">
                <input type="password" name="password" id="password" placeholder=" " required>
                <label for="password">Password</label>
                <i class="fas fa-eye-slash toggle-password" onclick="togglePassword()"></i>
            </div>
            <button type="submit">Masuk</button>
        </form>
    </div>
    <script>
    const form = document.getElementById('loginForm');
    const inputs = form.querySelectorAll('input');

    function checkInputs() {
        let filled = true;
        inputs.forEach(input => {
            if (!input.value.trim()) {
                filled = false;
            }
        });
        if (filled) {
            form.classList.add('filled');
        } else {
            form.classList.remove('filled');
        }
    }

    inputs.forEach(input => {
        input.addEventListener('input', checkInputs);
    });

    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.querySelector('.toggle-password');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        }
    }
    </script>
</body>
</html>
