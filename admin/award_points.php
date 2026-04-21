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

$reportId  = $_POST['report_id']  ?? '';
$citizenId = $_POST['citizen_id'] ?? '';
$points    = (int)($_POST['points'] ?? 0);

if (!$reportId || !$citizenId || $points <= 0) {
    header('Location: ../Pages/admin-report-details.php?id=' . urlencode($reportId) . '&msg=' . urlencode('يرجى إدخال عدد نقاط صحيح') . '&type=error');
    exit;
}

// Insert into rewards table
$stmt = $pdo->prepare('INSERT INTO rewards (CitizenID, ReportID, Points, Date) VALUES (?, ?, ?, CURDATE())');
$stmt->execute([$citizenId, $reportId, $points]);

// Update citizen total points
$stmt = $pdo->prepare('UPDATE citizen SET TotalRewardPoints = TotalRewardPoints + ? WHERE CitizenID = ?');
$stmt->execute([$points, $citizenId]);

header('Location: ../Pages/admin-report-details.php?id=' . urlencode($reportId) . '&msg=' . urlencode('✓ تم منح ' . $points . ' نقطة للمستخدم بنجاح') . '&type=success');
exit;
?>