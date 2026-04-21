<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/signup.html');
    exit;
}

$fname       = trim($_POST['fname'] ?? '');
$lname       = trim($_POST['lname'] ?? '');
$username    = trim($_POST['username'] ?? '');
$email       = trim($_POST['email'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$nationalid  = trim($_POST['nationalid'] ?? '');
$nationality = trim($_POST['nationality'] ?? '');
$dob         = trim($_POST['dob'] ?? '');
$password    = $_POST['password'] ?? '';
$confirm     = $_POST['confirm'] ?? '';
$error       = '';

if (!$fname || !$lname || !$username || !$email || !$phone || !$nationalid || !$nationality || !$dob || !$password) {
    $error = 'جميع الحقول مطلوبة';
} elseif ($password !== $confirm) {
    $error = 'كلمتا المرور غير متطابقتين';
} elseif (strlen($password) < 8) {
    $error = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
} else {
    $stmt = $pdo->prepare('SELECT CitizenID FROM citizen WHERE Username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) $error = 'اسم المستخدم مستخدم بالفعل';

    if (!$error) {
        $stmt = $pdo->prepare('SELECT CitizenID FROM citizen WHERE Email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $error = 'البريد الإلكتروني مستخدم بالفعل';
    }

    if (!$error) {
        $stmt = $pdo->prepare('SELECT CitizenID FROM citizen WHERE IDNo = ?');
        $stmt->execute([$nationalid]);
        if ($stmt->fetch()) $error = 'رقم الهوية مستخدم بالفعل';
    }
}

if ($error) {
    header('Location: ../Pages/signup.html?error=' . urlencode($error));
    exit;
}

$hashed = password_hash($password, PASSWORD_BCRYPT);
$stmt   = $pdo->prepare('
    INSERT INTO citizen (FirstName, LastName, Username, Email, PhoneNo, Password, BirthDate, Nationality, IDNo)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
');
$stmt->execute([$fname, $lname, $username, $email, $phone, $hashed, $dob, $nationality, $nationalid]);

header('Location: ../Pages/login.html?success=1');
exit;
?>
