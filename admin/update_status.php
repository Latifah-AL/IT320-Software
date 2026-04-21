<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../Pages/login.html');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/manage-reports.php');
    exit;
}

$reportId = $_POST['report_id'] ?? '';
$action   = $_POST['action']    ?? '';
$adminId  = $_SESSION['admin_id'];

$allowed = ['Pending', 'Under Review', 'Resolved', 'Rejected'];
if (!in_array($action, $allowed) || !$reportId) {
    header('Location: ../Pages/manage-reports.php');
    exit;
}

$stmt = $pdo->prepare('UPDATE report SET Status=?, AdminID=? WHERE ReportID=?');
$stmt->execute([$action, $adminId, $reportId]);

header('Location: ../Pages/admin-report-details.php?id=' . urlencode($reportId) . '&msg=' . urlencode('✓ تم تحديث الحالة بنجاح') . '&type=success');
exit;
?>