<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['citizen_id'])) {
    header('Location: ../Pages/login.html');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/my-reports.php');
    exit;
}

$citizenId   = $_SESSION['citizen_id'];
$reportId    = $_POST['report_id'] ?? '';
$title       = trim($_POST['title'] ?? '');
$category    = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$location    = trim($_POST['location'] ?? '');
$lat         = trim($_POST['lat'] ?? '0');
$lng         = trim($_POST['lng'] ?? '0');
$datetime    = trim($_POST['datetime'] ?? '');
$errors      = [];

// Verify ownership
$stmt = $pdo->prepare('SELECT ReportID, ImagePath FROM report WHERE ReportID = ? AND CitizenID = ?');
$stmt->execute([$reportId, $citizenId]);
$existing = $stmt->fetch();

if (!$existing) {
    header('Location: ../Pages/my-reports.php');
    exit;
}

// Validate
if (!$title)       $errors['title']       = 'يرجى إدخال عنوان البلاغ';
if (!$category)    $errors['category']    = 'يرجى اختيار نوع المخالفة';
if (!$description) $errors['description'] = 'يرجى إدخال وصف المخالفة';
if (!$location)    $errors['location']    = 'يرجى إدخال الموقع';
if (!$datetime)    $errors['datetime']    = 'يرجى إدخال تاريخ ووقت المخالفة';

// Validate image if uploaded
$imagePath = $existing['ImagePath'];
if (!empty($_FILES['image']['name'])) {
    $file    = $_FILES['image'];
    $allowed = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($file['type'], $allowed)) {
        $errors['image'] = 'صيغة الصورة غير مدعومة، يرجى رفع JPG أو PNG فقط';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $errors['image'] = 'حجم الصورة كبير جداً، الحد الأقصى 5MB';
    }
}

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old']    = [
        'title'       => $title,
        'category'    => $category,
        'description' => $description,
        'location'    => $location,
        'datetime'    => $datetime,
    ];
    header('Location: ../Pages/edit-report.php?id=' . urlencode($reportId));
    exit;
}

// Upload new image if provided
if (!empty($_FILES['image']['name']) && empty($errors['image'])) {
    $file      = $_FILES['image'];
    $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename  = uniqid('img_', true) . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        // Delete old image
        if ($existing['ImagePath'] && file_exists(__DIR__ . '/../' . $existing['ImagePath'])) {
            unlink(__DIR__ . '/../' . $existing['ImagePath']);
        }
        $imagePath = 'uploads/' . $filename;
    }
}

$submittedAt = date('Y-m-d H:i:s', strtotime($datetime));

// Update report
$stmt = $pdo->prepare('
    UPDATE report SET Title=?, ViolationType=?, Description=?, ImagePath=?, SubmittedAt=?
    WHERE ReportID=? AND CitizenID=?
');
$stmt->execute([$title, $category, $description, $imagePath, $submittedAt, $reportId, $citizenId]);

// Update location
$stmt = $pdo->prepare('UPDATE location SET Latitude=?, Longitude=?, Address=? WHERE ReportID=?');
$stmt->execute([$lat ?: 0, $lng ?: 0, $location, $reportId]);

header('Location: ../Pages/my-reports.php?updated=1');
exit;
?>