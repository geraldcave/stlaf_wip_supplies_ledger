<?php
session_start();
require_once '../../sql/config.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$month = $_GET['month'] ?? null;
$year = $_GET['year'] ?? null;
$dept = $_GET['department'] ?? null; // New filter

$db = new Database();
$conn = $db->getConnection();

// 1. Build Query with Department Filter
$sql = "SELECT name, department, item, unit, quantity, date_req, status 
        FROM req_form
        WHERE 1=1";

if ($month) $sql .= " AND MONTH(date_req) = '" . $conn->real_escape_string($month) . "'";
if ($year) $sql .= " AND YEAR(date_req) = '" . $conn->real_escape_string($year) . "'";
if ($dept) $sql .= " AND department = '" . $conn->real_escape_string($dept) . "'";

$sql .= " ORDER BY date_req DESC";
$result = $conn->query($sql);

// 2. HTML Template
// Define display variables
$mName = (!empty($_GET['month'])) ? date("F", mktime(0, 0, 0, $_GET['month'], 1)) : "All Months";
$yValue = (!empty($_GET['year'])) ? $_GET['year'] : "All Years";
$deptDisplay = (!empty($_GET['department'])) ? $_GET['department'] : "All Departments";

$html = '
<style>
    body { font-family: sans-serif; font-size: 11px; }
    h2 { text-align: center; margin-bottom: 10px; }
    .filter-summary { text-align: center; margin-bottom: 20px; color: #555; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>

<h2>Request Detailed Report</h2>
<div class="filter-summary">
    <strong>Department:</strong> ' . $deptDisplay . ' | 
    <strong>Period:</strong> ' . $mName . ' ' . $yValue . '
</div>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Department</th>
            <th>Item</th>
            <th>Size</th>
            <th>Qty</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>';

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $totalQty += $row['quantity'];
        $html .= '
        <tr>
            <td>' . htmlspecialchars($row['name']) . '</td>
            <td>' . htmlspecialchars($row['department']) . '</td>
            <td>' . htmlspecialchars($row['item']) . '</td>
            <td>' . htmlspecialchars($row['unit'] ?: '-') . '</td>
            <td>' . htmlspecialchars($row['quantity']) . '</td>
            <td>' . date("Y-m-d", strtotime($row['date_req'])) . '</td>
            <td>' . htmlspecialchars($row['status']) . '</td>
        </tr>';
    }

    // 3. Add the Total Row
    $html .= '
    <tr class="total-row">
        <td colspan="4" style="text-align:right;">GRAND TOTAL:</td>
        <td>' . $totalQty . '</td>
        <td colspan="2"></td>
    </tr>';
} else {
    $html .= '<tr><td colspan="7" style="text-align:center;">No records found.</td></tr>';
}

$html .= '</tbody></table>';

// 1. Capture the filter values from the URL
$deptName = (!empty($_GET['department'])) ? $_GET['department'] : "All_Departments";
$mValue = (!empty($_GET['month'])) ? $_GET['month'] : null;
$yValue = (!empty($_GET['year'])) ? $_GET['year'] : "All_Years";

// 2. Convert month number to full name (e.g., 1 -> January)
$mName = $mValue ? date("F", mktime(0, 0, 0, $mValue, 1)) : "All_Months";

// 3. Create the dynamic filename
$filename = "Detailed_Report_of_{$deptName}_{$mName}_{$yValue}.pdf";

// 4. Clean up the filename (Replace spaces with underscores for better file handling)
$filename = str_replace(' ', '_', $filename);

// 5. Render and Stream PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Clear any output buffers to prevent PDF corruption
if (ob_get_length()) ob_end_clean();

$dompdf->stream($filename, ["Attachment" => true]);
exit;
