<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside id="sidebar" class="sidebar-toggle">
    <div class="sidebar-logo mt-3">
        <img src="../../assets/images/official_logo.png" width="80px" height="80px" alt="Logo">
    </div>
    <div class="menu-title">Navigation</div>

    <li class="sidebar-item">
        <a href="admin_dashboard.php" class="sidebar-link <?= ($currentPage == 'admin_dashboard.php') ? 'active' : '' ?>">
            <i class="bi bi-cast"></i>
            <span style="font-size: 18px;">Dashboard</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a href="req_tab.php" class="sidebar-link <?= ($currentPage == 'req_tab.php') ? 'active' : '' ?>">
            <i class="bi bi-box"></i>
            <span style="font-size: 18px;">Employee Requests</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a href="ins_form.php" class="sidebar-link <?= ($currentPage == 'ins_form.php') ? 'active' : '' ?>">
            <i class="bi bi-basket"></i>
            <span style="font-size: 18px;">Ins Forms</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a href="stock_in.php" class="sidebar-link <?= ($currentPage == 'stock_in.php') ? 'active' : '' ?>">
            <i class="bi bi-basket"></i>
            <span style="font-size: 18px;">Stock In</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a href="stock_out.php" class="sidebar-link <?= ($currentPage == 'stock_out.php') ? 'active' : '' ?>">
            <i class="bi bi-basket"></i>
            <span style="font-size: 18px;">Deducted Items</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a href="inventory_dashboard.php" class="sidebar-link <?= ($currentPage == 'inventory_dashboard.php') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span style="font-size: 18px;">Supply Tracking</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a href="config_item.php" class="sidebar-link <?= ($currentPage == 'config_item.php') ? 'active' : '' ?>">
            <i class="bi bi-gear"></i>
            <span>Configuration</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a href="supply.php" class="sidebar-link <?= ($currentPage == 'supply.php') ? 'active' : '' ?>">
            <i class="bi bi-gear"></i>
            <span>Update Supplier & Date</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a href="summary.php" class="sidebar-link <?= ($currentPage == 'summary.php') ? 'active' : '' ?>">
            <i class="bi bi-clipboard-data"></i>
            <span>Summary</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a href="archived_items.php" class="sidebar-link <?= ($currentPage == 'archived_items.php') ? 'active' : '' ?>">
            <i class="bi bi-archive"></i>
            <span>Archived Items</span>
        </a>
    </li>
    <li class="sidebar-item">
        <a href="../../logout.php" class="sidebar-link">
            <i class="bi bi-box-arrow-right"></i>
            <span style="font-size: 18px;">Logout</span>
        </a>
    </li>
</aside>
