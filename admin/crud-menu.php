<?php
session_start();
require_once '../database/config-login.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil data menu dari database
$sql = "SELECT * FROM menu ORDER BY category_id, name";
$result = mysqli_query($conn, $sql);

// Fungsi untuk menambah menu
function addMenu($conn) {
    if(isset($_POST['name']) && isset($_POST['category_id']) && isset($_POST['price']) && isset($_POST['description'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $category_id = intval($_POST['category_id']);
        $price = floatval($_POST['price']); // Harga normal tanpa konversi
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        // Handle file upload
        $image = '';
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
            $filename = $_FILES["image"]["name"];
            $filetype = $_FILES["image"]["type"];
            $filesize = $_FILES["image"]["size"];
        
            // Verify file extension
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if(!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");
        
            // Verify file size - 5MB maximum
            $maxsize = 5 * 1024 * 1024;
            if($filesize > $maxsize) die("Error: File size is larger than the allowed limit.");
        
            // Verify MYME type of the file
            if(in_array($filetype, $allowed)) {
                // Check whether file exists before uploading it
                if(file_exists("../assets/menu/" . $filename)) {
                    echo $filename . " is already exists.";
                } else {
                    if(move_uploaded_file($_FILES["image"]["tmp_name"], "../assets/menu/" . $filename)) {
                        $image = $filename;
                    } else {
                        echo "Error: There was a problem uploading your file. Please try again."; 
                        return;
                    }
                }
            } else {
                echo "Error: There was a problem uploading your file. Please try again."; 
                return;
            }
        }
        
        $sql = "INSERT INTO menu (name, category_id, price, description, image) VALUES ('$name', $category_id, $price, '$description', '$image')";
        if(mysqli_query($conn, $sql)) {
            echo "Menu berhasil ditambahkan.";
            header("Location: crud-menu.php");
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
}

// Fungsi untuk mengedit menu
function editMenu($conn, $id) {
    if(isset($_POST['name']) && isset($_POST['category_id']) && isset($_POST['price']) && isset($_POST['description'])) {
        $id = intval($id);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $category_id = intval($_POST['category_id']);
        $price = floatval($_POST['price']); // Harga normal tanpa konversi
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        $sql = "UPDATE menu SET name='$name', category_id=$category_id, price=$price, description='$description' WHERE id=$id";
        
        // Handle file upload if a new image is provided
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
            $filename = $_FILES["image"]["name"];
            $filetype = $_FILES["image"]["type"];
            $filesize = $_FILES["image"]["size"];
        
            // Verify file extension
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if(!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");
        
            // Verify file size - 5MB maximum
            $maxsize = 5 * 1024 * 1024;
            if($filesize > $maxsize) die("Error: File size is larger than the allowed limit.");
        
            // Verify MYME type of the file
            if(in_array($filetype, $allowed)) {
                // Check whether file exists before uploading it
                if(file_exists("../assets/menu/" . $filename)) {
                    echo $filename . " is already exists.";
                } else {
                    if(move_uploaded_file($_FILES["image"]["tmp_name"], "../assets/menu/" . $filename)) {
                        $sql = "UPDATE menu SET name='$name', category_id=$category_id, price=$price, description='$description', image='$filename' WHERE id=$id";
                    } else {
                        echo "Error: There was a problem uploading your file. Please try again."; 
                        return;
                    }
                }
            } else {
                echo "Error: There was a problem uploading your file. Please try again."; 
                return;
            }
        }
        
        if(mysqli_query($conn, $sql)) {
            echo "Menu berhasil diperbarui.";
            header("Location: crud-menu.php");
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
}

// Fungsi untuk menghapus menu
function deleteMenu($conn, $id) {
    $id = intval($id);
    
    // Cek apakah menu masih terkait dengan order_items
    $check_sql = "SELECT COUNT(*) as count FROM order_items WHERE menu_id = $id";
    $check_result = mysqli_query($conn, $check_sql);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['count'] > 0) {
        echo "Error: Menu tidak dapat dihapus karena masih terkait dengan pesanan.";
        return;
    }
    
    // Ambil nama file gambar sebelum menghapus menu
    $sql_select = "SELECT image FROM menu WHERE id=$id";
    $result = mysqli_query($conn, $sql_select);
    if ($row = mysqli_fetch_assoc($result)) {
        $image_file = $row['image'];
    }
    
    $sql = "DELETE FROM menu WHERE id=$id";
    if(mysqli_query($conn, $sql)) {
        // Hapus file gambar jika ada
        if (!empty($image_file)) {
            $image_path = "../assets/menu/" . $image_file;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        echo "Menu berhasil dihapus.";
        header("Location: crud-menu.php");
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

// Proses form jika ada request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        addMenu($conn);
    } elseif (isset($_POST['edit'])) {
        editMenu($conn, $_POST['edit_id']);
    } elseif (isset($_POST['delete'])) {
        deleteMenu($conn, $_POST['delete_id']);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Restoran Makan Mania</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/crud-menu.css">
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
                <a href="crud-menu.php" class="active">
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
                <h1>Kelola Menu</h1>
                <div class="user-info">
                    <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </header>

            <!-- Menu Management -->
            <div class="menu-container">
                <div class="menu-actions">
                    <button class="add-menu-btn" onclick="showAddForm()">
                        <i class="fas fa-plus"></i> Tambah Menu Baru
                    </button>
                </div>

                <div class="menu-list">
                    <table>
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama Menu</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td>
                                    <img src="../assets/menu/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="menu-image">
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <?php
                                    switch ($row['category_id']) {
                                        case 1:
                                            echo 'Makanan';
                                            break;
                                        case 2:
                                            echo 'Minuman';
                                            break;
                                        case 3:
                                            echo 'Snack';
                                            break;
                                        default:
                                            echo 'Tidak diketahui';
                                    }
                                    ?>
                                </td>
                                <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td class="action-buttons">
                                    <button class="edit-btn" data-id="<?php echo $row['id']; ?>" onclick="showEditForm(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button onclick="showDeleteConfirmation(<?php echo $row['id']; ?>)" class="delete-btn">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Tambah Menu -->
    <div id="addMenuModal" class="modal">
        <div class="modal-content">
            <h2>Tambah Menu Baru</h2>
            <form id="addMenuForm" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nama Menu:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="category_id">Kategori:</label>
                    <select id="category_id" name="category_id" required>
                        <option value="1">Makanan</option>
                        <option value="2">Minuman</option>
                        <option value="3">Snack</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Harga (Rp):</label>
                    <input type="number" id="price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Gambar:</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>
                <button type="submit" name="add">Tambah Menu</button>
            </form>
        </div>
    </div>

    <!-- Modal untuk Edit Menu -->
    <div id="editMenuModal" class="modal">
        <div class="modal-content">
            <h2>Edit Menu</h2>
            <form id="editMenuForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_id" name="edit_id">
                <div class="form-group">
                    <label for="edit_name">Nama Menu:</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_category_id">Kategori:</label>
                    <select id="edit_category_id" name="category_id" required>
                        <option value="1">Makanan</option>
                        <option value="2">Minuman</option>
                        <option value="3">Snack</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_price">Harga (Rp):</label>
                    <input type="number" id="edit_price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Deskripsi:</label>
                    <textarea id="edit_description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_image">Gambar Baru (opsional):</label>
                    <input type="file" id="edit_image" name="image" accept="image/*">
                </div>
                <button type="submit" name="edit">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Modal untuk Konfirmasi Hapus -->
    <div id="deleteConfirmModal" class="modal">
        <div class="modal-content">
            <h2>Konfirmasi Hapus</h2>
            <p>Apakah Anda yakin ingin menghapus menu ini?</p>
            <form id="deleteMenuForm" method="POST">
                <input type="hidden" id="delete_id" name="delete_id">
                <button type="submit" name="delete">Ya, Hapus</button>
                <button type="button" onclick="closeDeleteConfirmation()">Batal</button>
            </form>
        </div>
    </div>

    <script>
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // Fungsi untuk menampilkan form tambah menu
        function showAddForm() {
            document.getElementById('addMenuModal').style.display = 'block';
        }

        // Fungsi untuk menampilkan form edit menu
        function showEditForm(id) {
            // Debug
            console.log('Edit button clicked for ID:', id);
            
            // Tambahkan base URL
            const baseUrl = window.location.href.split('/admin')[0];
            
            // Mengambil data menu dari server
            fetch(`${baseUrl}/admin/get_menu_data.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data); // Debug
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_category_id').value = data.category_id;
                    document.getElementById('edit_price').value = data.price;
                    document.getElementById('edit_description').value = data.description;
                    document.getElementById('editMenuModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data menu');
                });
        }

        // Fungsi untuk menampilkan konfirmasi hapus menu
        function showDeleteConfirmation(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteConfirmModal').style.display = 'block';
        }

        // Fungsi untuk menutup modal konfirmasi hapus
        function closeDeleteConfirmation() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
        }

        // Tutup modal ketika mengklik di luar modal
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Tambahkan event listener untuk tombol edit
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                showEditForm(id);
            });
        });
    </script>
</body>
</html>
