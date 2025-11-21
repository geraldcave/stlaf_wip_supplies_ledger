<?php
require_once '../../sql/config.php';
require_once '../../auth/oop/request_form.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Forbidden');
}

$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT * FROM req_form ORDER BY date_req DESC";
$result = $conn->query($sql);
$requests = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

foreach ($requests as $row) {
    $status = ucfirst($row['status']);
    $badgeClass = match (strtolower($status)) {
        'delivered' => 'status-delivered',
        'cancelled' => 'status-cancelled',
        default => 'status-pending'
    };
    ?>
    <tr data-department="<?= htmlspecialchars($row['department']) ?>"> 
        <td><?= htmlspecialchars($row['req_id']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['item']) ?></td>
        <td><?= htmlspecialchars($row['quantity']) ?></td>
        <td><?= htmlspecialchars($row['unit']) ?></td>
        <td><?= htmlspecialchars($row['department']) ?></td>
        <td>
            <span class="status-badge <?= $badgeClass ?>"><?= $status ?></span>
            <?php if (strtolower($status) === 'cancelled'): ?>
                <br>
                <small class="text-muted">
                    Reason: <?= htmlspecialchars($row['cancel_reason'] ?? 'No reason provided') ?>
                </small>
            <?php endif; ?>
        </td>
        <td><?= date("M d, Y h:i A", strtotime($row['date_req'])) ?></td>
    </tr>
    <?php
}
