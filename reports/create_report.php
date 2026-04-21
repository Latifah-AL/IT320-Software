<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['citizen_id'])) {
    header('Location: ../Pages/login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/create-report.php');
    exit;
}

$citizenId   = $_SESSION['citizen_id'];
$title       = trim($_POST['title'] ?? '');
$category    = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$location    = trim($_POST['location'] ?? '');
$lat         = trim($_POST['lat'] ?? '0');
$lng         = trim($_POST['lng'] ?? '0');
$datetime    = trim($_POST['datetime'] ?? '');
$errors      = [];

// Validate each field separately
if (!$title)       $errors['title']       = 'يرجى إدخال عنوان البلاغ';
if (!$category)    $errors['category']    = 'يرجى اختيار نوع المخالفة';
if (!$description) $errors['description'] = 'يرجى إدخال وصف المخالفة';
if (!$location)    $errors['location']    = 'يرجى إدخال الموقع';
if (!$datetime)    $errors['datetime']    = 'يرجى إدخال تاريخ ووقت المخالفة';

// Validate image
$imagePath = null;
if (empty($_FILES['image']['name'])) {
    $errors['image'] = 'يرجى إرفاق صورة للمخالفة';
} else {
    $file    = $_FILES['image'];
    $allowed = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($file['type'], $allowed)) {
        $errors['image'] = 'صيغة الصورة غير مدعومة، يرجى رفع JPG أو PNG فقط';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $errors['image'] = 'حجم الصورة كبير جداً، الحد الأقصى 5MB';
    }
}

// If any errors — redirect back with errors and old values in session
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old']    = [
        'title'       => $title,
        'category'    => $category,
        'description' => $description,
        'location'    => $location,
        'datetime'    => $datetime,
    ];
    header('Location: ../Pages/create-report.php');
    exit;
}

// Upload image
$ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename  = uniqid('img_', true) . '.' . $ext;
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
    $_SESSION['form_errors'] = ['image' => 'فشل رفع الصورة، يرجى المحاولة مرة أخرى'];
    $_SESSION['form_old']    = ['title' => $title, 'category' => $category, 'description' => $description, 'location' => $location, 'datetime' => $datetime];
    header('Location: ../Pages/create-report.php');
    exit;
}
$imagePath = 'uploads/' . $filename;

// Insert report
$reportId    = 'RPT-' . strtoupper(substr(uniqid(), -6));
$submittedAt = date('Y-m-d H:i:s', strtotime($datetime));

$stmt = $pdo->prepare('
    INSERT INTO report (ReportID, CitizenID, Title, ViolationType, Description, ImagePath, SubmittedAt, Status)
    VALUES (?, ?, ?, ?, ?, ?, ?, "Pending")
');
$stmt->execute([$reportId, $citizenId, $title, $category, $description, $imagePath, $submittedAt]);

$stmt = $pdo->prepare('INSERT INTO location (ReportID, Latitude, Longitude, Address) VALUES (?, ?, ?, ?)');
$stmt->execute([$reportId, $lat ?: 0, $lng ?: 0, $location]);

$stmt = $pdo->prepare('INSERT INTO rewards (CitizenID, ReportID, Points, Date) VALUES (?, ?, 10, CURDATE())');
$stmt->execute([$citizenId, $reportId]);

$stmt = $pdo->prepare('UPDATE citizen SET TotalRewardPoints = TotalRewardPoints + 10 WHERE CitizenID = ?');
$stmt->execute([$citizenId]);

// Clear session form data
unset($_SESSION['form_errors'], $_SESSION['form_old']);

header('Location: ../Pages/my-reports.php?success=1&id=' . urlencode($reportId));
exit;
?>