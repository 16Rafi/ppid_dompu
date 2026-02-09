<?php
function requireAdmin(mysqli $conn): array
{
    $currentFp = hash('sha256', $_SERVER['HTTP_USER_AGENT']);

    if (!hash_equals($_SESSION['fingerprint'] ?? '', $currentFp)) {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    if (empty($_SESSION['admin_id'])) {
        header('Location: index.php');
        exit;
    }

    $userId = (int)$_SESSION['admin_id'];

    $stmt = $conn->prepare(
        "SELECT id, username, role, status_aktif 
         FROM users 
         WHERE id = ? 
         LIMIT 1"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (
        !$user ||
        $user['role'] !== 'admin' ||
        (isset($user['status_aktif']) && (int)$user['status_aktif'] !== 1)
    ) {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php');
        exit;
    }

    $timeout = 30 * 60; // 30 menit

    if (isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity']) > $timeout
    ) {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    $_SESSION['last_activity'] = time();
    $_SESSION['admin_role'] = $user['role'];

    return $user;
}
