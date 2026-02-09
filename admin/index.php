<?php
require_once '../includes/config.php';
require_once '../includes/security_headers.php';

if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Rate limiting check
    $rateLimitResult = checkLoginRateLimit($username);
    if ($rateLimitResult['blocked']) {
        $error = $rateLimitResult['message'];
    } else {
        if ($username !== '' && $password !== '') {
            $stmt = $conn->prepare("SELECT id, username, password, email, role FROM users WHERE username = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $users = $result->fetch_assoc();

                if (($users['role'] ?? '') === 'admin') {
                    $stored_password = (string)($users['password'] ?? '');
                    $is_valid = password_verify($password, $stored_password);

                    if (!$is_valid) {
                        $info = password_get_info($stored_password);
                        if (($info['algo'] ?? 0) === 0 && hash_equals($stored_password, $password)) {
                            $is_valid = true;
                            $new_hash = password_hash($password, PASSWORD_DEFAULT);
                            $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ? LIMIT 1");
                            $upd->bind_param("si", $new_hash, $users['id']);
                            $upd->execute();
                        }
                    }

                    if ($is_valid) {
                        // Successful login
                        clearLoginAttempts($username);
                        
                        session_regenerate_id(true);
                        $_SESSION['admin_id'] = $users['id'];
                        $_SESSION['admin_username'] = $users['username'];
                        $_SESSION['admin_email'] = $users['email'];
                        $_SESSION['admin_role'] = $users['role'];
                        $_SESSION['user_id'] = $users['id'];
                        $_SESSION['username'] = $users['username'];
                        $_SESSION['email'] = $users['email'];
                        $_SESSION['user_role'] = $users['role'];
                        
                        // Log successful login
                        logAdminAction('LOGIN', null, null, "Admin login successful for user: $username");
                        
                        header('Location: dashboard.php');
                        exit();
                    }
                }
            }
            
            // Failed login
            logAdminAction('LOGIN_FAILED', null, null, "Failed login attempt for user: $username");
            $error = "Username atau password salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="<?php echo buildUrl('img/Kabupaten Dompu.png'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PPID Kabupaten Dompu</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body admin-login">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Admin Login</h1>
                <p>PPID Kabupaten Dompu</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">Login</button>
            </form>
            
            <div class="back-link">
                <a href="../index.php">← Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>



