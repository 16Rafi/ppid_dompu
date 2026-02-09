<?php
// Set JSON response header first
header('Content-Type: application/json');

require_once '../../includes/config.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data || !isset($data['menus'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data format']);
    exit();
}

// Move function outside loop to prevent redeclaration
function isDescendant($conn, $menu_id, $parent_id, $depth = 0) {
    // Prevent infinite recursion
    if ($depth > 10) {
        return false;
    }
    
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
    
    return isDescendant($conn, $menu_id, $parent['parent_id'], $depth + 1);
}

try {
    $conn->begin_transaction();
    
    foreach ($data['menus'] as $index => $menu_data) {
        $menu_id = (int)$menu_data['id'];
        $parent_id = isset($menu_data['parent']) ? (int)$menu_data['parent'] : 0;
        $order_index = isset($menu_data['order']) ? (int)$menu_data['order'] : ($index + 1);
        
        // Validate menu exists
        $stmt = $conn->prepare("SELECT id FROM menus WHERE id = ?");
        $stmt->bind_param("i", $menu_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Menu with ID $menu_id not found");
        }
        
        // Prevent circular reference
        if ($parent_id > 0) {
            if (isDescendant($conn, $menu_id, $parent_id)) {
                throw new Exception("Circular reference detected for menu $menu_id");
            }
        }
        
        // Update menu
        $stmt = $conn->prepare("UPDATE menus SET parent_id = ?, order_index = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("iii", $parent_id, $order_index, $menu_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update menu $menu_id");
        }
    }
    
    $conn->commit();
    
    // Log admin action
    logAdminAction('REORDER_MENU', 'menu', null, "Reordered menus");
    
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Menu order updated successfully']);
    
} catch (Exception $e) {
    $conn->rollback();
    
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
