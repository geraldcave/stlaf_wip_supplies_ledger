<?php
session_start();
require_once '../../sql/config.php';
require_once '../../auth/oop/request_form.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$firstname  = ucfirst($_SESSION['username'] ?? 'Guest');
$department = ucfirst($_SESSION['department'] ?? 'Employee');

// Fetch items for left form
$items = $conn->query("SELECT id, description, unit FROM items ORDER BY description ASC");

// Fetch request list for right side
$sql = "SELECT * FROM req_form ORDER BY date_req DESC";
$result = $conn->query($sql);
$requests = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>STLAF | Dashboard</title>
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
</head>

<body>

    <nav class="navbar px-5 bg-light">
        <div class="d-flex align-items-center gap-3">
            <img src="../../assets/images/official_logo.png" width="80" height="80">
        </div>
        <div>
            <a href="../../logout.php"><button class="logout-btn">Logout</button></a>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row g-4">
            <div class="col-md-4">
                <?php include "guest.php"; ?>
            </div>
            <div class="col-md-8">
                <?php include "req_list.php"; ?>
            </div>

        </div>
    </div>

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="assets/re.js"></script>
</body>

</html>