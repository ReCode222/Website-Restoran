<?php
require_once '../database/config-login.php';

function getMenu($category = null) {
    global $conn;
    
    // Base query with ordering by category_id and name
    $sql = "SELECT m.*, c.name as category_name 
            FROM menu m 
            JOIN categories c ON m.category_id = c.id";
    
    // If category is specified, add WHERE clause
    if ($category !== null) {
        $sql .= " WHERE c.id = ?";
    }
    
    // Always order by category_id and then by name
    $sql .= " ORDER BY c.id, m.name";
    
    try {
        $stmt = mysqli_prepare($conn, $sql);
        
        // Bind category parameter if specified
        if ($category !== null) {
            mysqli_stmt_bind_param($stmt, "i", $category);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $menu_items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $menu_items[] = [
                'id' => $row['id'],
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name'],
                'name' => $row['name'],
                'price' => $row['price'],
                'price_formatted' => "Rp " . number_format($row['price'], 0, ',', '.'),
                'description' => $row['description'],
                'image' => $row['image'],
                'image_url' => '../assets/menu/' . $row['image']
            ];
        }
        
        return [
            'status' => 'success',
            'data' => $menu_items
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Error loading menu: ' . $e->getMessage()
        ];
    }
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $response = getMenu($category);
    
    echo json_encode($response);
    exit;
}

// Function to get categories
function getCategories() {
    global $conn;
    
    try {
        $sql = "SELECT * FROM categories ORDER BY id";
        $result = mysqli_query($conn, $sql);
        
        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name']
            ];
        }
        
        return [
            'status' => 'success',
            'data' => $categories
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Error loading categories: ' . $e->getMessage()
        ];
    }
}

// Handle categories request
if (isset($_GET['action']) && $_GET['action'] === 'categories') {
    header('Content-Type: application/json');
    $response = getCategories();
    echo json_encode($response);
    exit;
}

// Function to search menu
function searchMenu($keyword) {
    global $conn;
    
    try {
        $sql = "SELECT m.*, c.name as category_name 
                FROM menu m 
                JOIN categories c ON m.category_id = c.id 
                WHERE m.name LIKE ? OR m.description LIKE ?";
                
        $stmt = mysqli_prepare($conn, $sql);
        $search = "%{$keyword}%";
        mysqli_stmt_bind_param($stmt, "ss", $search, $search);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $menu_items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $formatted_price = "Rp " . number_format($row['price'], 0, ',', '.');
            
            $menu_items[] = [
                'id' => $row['id'],
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name'],
                'name' => $row['name'],
                'price' => $row['price'],
                'price_formatted' => $formatted_price,
                'description' => $row['description'],
                'image' => $row['image'],
                'image_url' => '../assets/menu/' . $row['image']
            ];
        }
        
        return [
            'status' => 'success',
            'data' => $menu_items
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Error searching menu: ' . $e->getMessage()
        ];
    }
}

// Handle search request
if (isset($_GET['action']) && $_GET['action'] === 'search' && isset($_GET['keyword'])) {
    header('Content-Type: application/json');
    $response = searchMenu($_GET['keyword']);
    echo json_encode($response);
    exit;
}
?>
