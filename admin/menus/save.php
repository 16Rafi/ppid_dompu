<?php
require_once '../../includes/config.php';
require_once '../../includes/security_headers.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$errors = [];

// Get form data
$menu_id = isset($_POST['menu_id']) ? (int)$_POST['menu_id'] : 0;
$name = trim($_POST['name'] ?? '');
$parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
$link_type = $_POST['link_type'] ?? '';
$page_id = isset($_POST['page_id']) ? (int)$_POST['page_id'] : 0;
$url = trim($_POST['url'] ?? '');
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validation
if (empty($name)) {
    $errors[] = "Nama menu wajib diisi";
}

if (empty($link_type) || !in_array($link_type, ['page', 'manual'])) {
    $errors[] = "Tipe link tidak valid";
}

// Generate URL based on link type
if ($link_type === 'page') {
    if ($page_id === 0) {
        $errors[] = "Silakan pilih halaman";
    } else {
        // Get page slug
        $stmt = $conn->prepare("SELECT slug FROM pages WHERE id = ?");
        $stmt->bind_param("i", $page_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $errors[] = "Halaman tidak ditemukan";
        } else {
            $page = $result->fetch_assoc();
            $url = 'pages/template.php?slug=' . $page['slug'];
        }
    }
} else {
    if (empty($url)) {
        $errors[] = "URL manual wajib diisi";
    } else {
        // Validate URL format
        if (!preg_match('/^\/[a-zA-Z0-9\/_.-]+(\?.*)?$/', $url)) {
            $errors[] = "URL harus dimulai dengan / dan hanya mengandung huruf, angka, /, ., _, -, dan query string";
        }
    }
}

// Prevent circular reference (menu becoming parent of itself)
if ($menu_id > 0 && $parent_id > 0) {
    // Check if parent_id is not a descendant of current menu
    function isDescendant($conn, $menu_id, $parent_id) {
        $stmt = $conn->prepare("SELECT parent_id FROM menus WHERE id = ?");
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $parent = $result->fetch_assoc();
        
        if ($parent['parent_id'] == $menu_id) {
            return true;
        }
        
        if ($parent['parent_id'] == 0) {
            return false;
        }
        
        return isDescendant($conn, $menu_id, $parent['parent_id']);
    }
    
    if (isDescendant($conn, $menu_id, $parent_id)) {
        $errors[] = "Tidak dapat menetapkan menu ini sebagai parent dari dirinya sendiri atau child-nya";
    }
}

// If no errors, save to database
if (empty($errors)) {
    if ($menu_id > 0) {
        // Update existing menu
        $stmt = $conn->prepare("UPDATE menus SET name = ?, url = ?, parent_id = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssiii", $name, $url, $parent_id, $is_active, $menu_id);
        
        if ($stmt->execute()) {
            // Log admin action
            logAdminAction('UPDATE_MENU', 'menu', $menu_id, "Updated menu: $name (ID: $menu_id)");
            
            header('Location: index.php?updated=1');
            exit();
        } else {
            $errors[] = "Terjadi kesalahan saat memperbarui menu";
        }
    } else {
        // Create new menu
        // Get the highest order_index for the parent
        $stmt = $conn->prepare("SELECT MAX(order_index) as max_order FROM menus WHERE parent_id = ?");
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $max_order = $result->fetch_assoc()['max_order'];
        $order_index = $max_order + 1;
        
        $stmt = $conn->prepare("INSERT INTO menus (name, url, parent_id, order_index, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssiii", $name, $url, $parent_id, $order_index, $is_active);
        
        if ($stmt->execute()) {
            $new_menu_id = $conn->insert_id;
            
            // Log admin action
            logAdminAction('CREATE_MENU', 'menu', $new_menu_id, "Created menu: $name (ID: $new_menu_id)");
            
            header('Location: index.php?created=1');
            exit();
        } else {
            $errors[] = "Terjadi kesalahan saat menyimpan menu";
        }
    }
}

// If there are errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $errorParam = urlencode($errors[0]);
    header('Location: index.php?error=' . $errorParam);
    exit();
}
?>
