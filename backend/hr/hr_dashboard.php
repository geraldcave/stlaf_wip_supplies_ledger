<?php
session_start();
include '../../sql/config.php';

$db = new Database();
$conn = $db->getConnection();

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'hr') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo "User ID is not set in session.";
    exit();
}

$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "No user found.";
    exit();
}

if (strtolower($user['department']) !== 'hr') {
    echo "Access denied. You are not in hr.";
    exit();
}

$firstname = $user['firstname'];
$lastname = $user['lastname'];
$department = $user['department'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | HR Dashboard</title>
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
</head>

<body>

    <nav class="navbar px-5 bg-light">
        <div class="d-flex align-items-center gap-5">
            <a class="navbar-brand m-0 p-0" href="index.php">
                <img src="../../assets/images/official_logo.png" alt="Logo" width="100" height="80">
            </a>
            <h3 class="supply mb-0">WIP Supplies Ledger</h3>
        </div>
        <div>
            Welcome, <?php echo htmlspecialchars($firstname); ?> <?php echo htmlspecialchars($lastname); ?> (<?php echo htmlspecialchars($department); ?>)
            <a href="../../logout.php"><button class="logout-btn">Logout</button></a>
        </div>
    </nav>

    <div class="dashboard-container">
        testing if all data can fetch
        <pre><?php print_r($user); ?></pre>
    </div>

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap/all.min.js"></script>
</body>

</html>