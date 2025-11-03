<?php
function showRequests($requests, $filterStatus) {
    ?>
    <div class="table-responsive elegant-table mt-3" style="max-height: 600px; overflow-y: auto;">
        <table class="table table-hover align-middle mb-0 text-center">
            <thead class="table-primary">
                <tr>
                    <th>Request ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Item</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <?php if ($filterStatus === 'Cancelled'): ?>
                        <th>Reason</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $filtered = array_filter($requests, fn($r) => $r['status'] === $filterStatus);
                if (!empty($filtered)):
                    foreach ($filtered as $r): ?>
                        <tr>
                            <td><span class="fw-bold text-primary"><?= htmlspecialchars($r['req_id']) ?></span></td>
                            <td><?= htmlspecialchars($r['name']) ?></td>
                            <td><?= ucfirst($r['department']) ?></td>
                            <td><?= htmlspecialchars($r['item']) ?></td>
                            <td><?= htmlspecialchars($r['size']) ?></td>
                            <td><?= htmlspecialchars($r['quantity']) ?></td>
                            <td><?= htmlspecialchars($r['unit']) ?></td>
                            <td>
                                <span class="badge bg-<?= $filterStatus === 'Delivered' ? 'success' : ($filterStatus === 'Cancelled' ? 'danger' : 'secondary') ?> px-3 py-2 shadow-sm">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </td>
                            <?php if ($filterStatus === 'Cancelled'): ?>
                                <td><?= htmlspecialchars($r['cancel_reason'] ?: 'N/A') ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="<?= $filterStatus === 'Cancelled' ? 9 : 8 ?>" class="text-center text-muted py-3">
                            No <?= strtolower($filterStatus) ?> requests found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>
