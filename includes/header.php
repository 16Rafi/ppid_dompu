<?php
if (!isset($conn)) {
    require_once __DIR__ . '/config.php';
}
?>
<!-- Header -->
<header class="header" id="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <div class="logo-img">PPID</div>
                <h2>PPID</h2>
            </div>
            <nav class="nav-menu">
                <ul class="nav-list">
                    <?php
                    global $conn;
                    $menuByParent = [];
                    $result = $conn->query("SELECT * FROM menus WHERE is_active = 1 ORDER BY parent_id ASC, order_index ASC");
                    while ($row = $result->fetch_assoc()) {
                        $menuByParent[(int)$row['parent_id']][] = $row;
                    }

                    function renderNavMenu($menuByParent, $parentId = 0) {
                        if (empty($menuByParent[$parentId])) {
                            return;
                        }
                        foreach ($menuByParent[$parentId] as $menu) {
                            $menuId = (int)$menu['id'];
                            $hasChildren = !empty($menuByParent[$menuId]);
                            echo '<li class="nav-item">';
                            if ($hasChildren) {
                                echo '<a href="' . buildUrl($menu['url']) . '" class="nav-link dropdown-toggle">' . htmlspecialchars($menu['name']) . '</a>';
                                echo '<ul class="dropdown-menu">';
                                renderNavMenu($menuByParent, $menuId);
                                echo '</ul>';
                            } else {
                                echo '<a href="' . buildUrl($menu['url']) . '" class="nav-link">' . htmlspecialchars($menu['name']) . '</a>';
                            }
                            echo '</li>';
                        }
                    }

                    renderNavMenu($menuByParent, 0);
                    ?>
                </ul>
            </nav>

            <?php if (isset($_SESSION['admin_id'])): ?>
                <a href="<?php echo buildUrl('admin/dashboard.php'); ?>" class="admin-dashboard-btn">
                    Dashboard Admin
                </a>
            <?php endif; ?>

            <div class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</header>
