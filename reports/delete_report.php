<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['citizen_id'])) {
    header('Location: ../Pages/login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/my-reports.html');
    exit;
}

$reportId  = $_POST['report_id'] ?? '';
$citizenId = $_SESSION['citizen_id'];

// Make sure report belongs to this citizen
$stmt = $pdo->prepare('SELECT ReportID, ImagePath FROM report WHERE ReportID = ? AND CitizenID = ?');
$stmt->execute([$reportId, $citizenId]);
$report = $stmt->fetch();

if (!$report) {
    header('Location: ../Pages/my-reports.html?error=not_found');
    exit;
}

// Delete image file if exists
if ($report['ImagePath'] && file_exists('../' . $report['ImagePath'])) {
    unlink('../' . $report['ImagePath']);
}

// Delete report (cascades to location and rewards)
$stmt = $pdo->prepare('DELETE FROM report WHERE ReportID = ?');
$stmt->execute([$reportId]);

header('Location: ../Pages/my-reports.php?deleted=1');
exit;
?>