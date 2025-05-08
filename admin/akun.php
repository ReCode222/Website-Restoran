<?php
session_start();
require_once '../database/config-login.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun - Restoran Makan Mania</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/akun.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .users-table th,
        .users-table td {
            text-align: center;
        }
        .actions {
            justify-content: center;
        }
    </style>
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
                <a href="read-pesanan.php">
                    <i class="fas fa-clipboard-list"></i> Lihat Pesanan
                </a>
                <a href="akun.php" class="active">
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
                <h1>Kelola Akun</h1>
                <div class="user-info">
                    <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </header>

            <!-- Tombol Tambah User -->
            <div class="action-buttons">
                <button class="add-btn" onclick="showAddModal()">
                    <i class="fas fa-plus"></i> Tambah User Baru
                </button>
            </div>

            <!-- Tabel User -->
            <div class="table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT id, username, role FROM users";
                        $result = $conn->query($sql);
                        
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . ucfirst(htmlspecialchars($row['role'])) . "</td>";
                            echo "<td class='actions'>";
                            if ($row['role'] !== 'admin') {
                                echo "<button class='edit-btn' onclick='showEditModal({$row['id']}, \"".htmlspecialchars($row['username'])."\", \"".htmlspecialchars($row['role'])."\")'>";
                                echo "<i class='fas fa-edit'></i> Edit Role";
                                echo "</button>";
                                echo "<button class='password-btn' onclick='showPasswordModal({$row['id']}, \"".htmlspecialchars($row['username'])."\")'>";
                                echo "<i class='fas fa-key'></i> Ubah Password";
                                echo "</button>";
                                echo "<button class='delete-btn' onclick='showDeleteModal({$row['id']}, \"".htmlspecialchars($row['username'])."\")'>";
                                echo "<i class='fas fa-trash'></i> Hapus";
                                echo "</button>";
                            } else {
                                echo "<button class='edit-btn disabled' disabled>";
                                echo "<i class='fas fa-lock'></i> Tidak dapat diedit";
                                echo "</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal Tambah User -->
            <div id="addModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeAddModal()">&times;</span>
                    <h2>Tambah User Baru</h2>
                    <form id="addUserForm" method="POST" action="process-user.php">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Role:</label>
                            <select id="role" name="role" required>
                                <option value="kasir">Kasir</option>
                                <option value="pelayan">Pelayan</option>
                            </select>
                        </div>
                        <button type="submit" class="submit-btn">Tambah User</button>
                    </form>
                </div>
            </div>

            <!-- Modal Edit User -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeEditModal()">&times;</span>
                    <h2>Edit User</h2>
                    <form id="editUserForm" method="POST" action="process-user.php">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="form-group">
                            <label for="edit_username">Username:</label>
                            <input type="text" id="edit_username" name="username" readonly>
                        </div>
                        <div class="form-group">
                            <label for="edit_role">Role:</label>
                            <select id="edit_role" name="role" required>
                                <option value="kasir">Kasir</option>
                                <option value="pelayan">Pelayan</option>
                            </select>
                        </div>
                        <button type="submit" class="submit-btn">Simpan Perubahan</button>
                    </form>
                </div>
            </div>

            <!-- Tambahkan Modal Ubah Password -->
            <div id="passwordModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closePasswordModal()">&times;</span>
                    <h2>Ubah Password</h2>
                    <form id="passwordForm" method="POST" action="process-user.php">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="user_id" id="password_user_id">
                        <div class="form-group">
                            <label for="password_username">Username:</label>
                            <input type="text" id="password_username" readonly>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Password Baru:</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <button type="submit" class="submit-btn">Ubah Password</button>
                    </form>
                </div>
            </div>

            <!-- Tambahkan Modal Hapus User -->
            <div id="deleteModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeDeleteModal()">&times;</span>
                    <h2>Hapus User</h2>
                    <p>Apakah Anda yakin ingin menghapus user <span id="delete_username"></span>?</p>
                    <p class="warning">Perhatian: Aksi ini tidak dapat dibatalkan!</p>
                    <form id="deleteUserForm" method="POST" action="process-user.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" id="delete_user_id">
                        <div class="form-buttons">
                            <button type="button" class="cancel-btn" onclick="closeDeleteModal()">Batal</button>
                            <button type="submit" class="delete-submit-btn">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // Modal Functions
        function showAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function showEditModal(id, username, role) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_role').value = role;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Tambahkan fungsi untuk modal password
        function showPasswordModal(id, username) {
            document.getElementById('password_user_id').value = id;
            document.getElementById('password_username').value = username;
            document.getElementById('passwordModal').style.display = 'block';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        // Tambahkan fungsi untuk modal hapus
        function showDeleteModal(id, username) {
            document.getElementById('delete_user_id').value = id;
            document.getElementById('delete_username').textContent = username;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
    </script>
</body>
</html>