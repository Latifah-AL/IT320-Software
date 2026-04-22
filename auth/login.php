<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/login.html');
    exit;
}

$identifier = trim($_POST['identifier'] ?? '');
$password   = $_POST['password'] ?? '';
$role       = $_POST['role'] ?? 'user';
$error      = '';

if (!$identifier || !$password) {
    $error = 'يرجى ملء جميع الحقول';
} else {
    if ($role === 'admin') {
        $stmt = $pdo->prepare('SELECT * FROM admin WHERE Username = ? OR Email = ?');
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['Password'])) {
            $error = 'بيانات المشرف غير صحيحة';
        } else {
            $_SESSION['admin_id']   = $user['AdminID'];
            $_SESSION['admin_name'] = $user['FirstName'];
            $_SESSION['role']       = 'admin';
            header('Location: ../Pages/admin-dashboard.php');
            exit;
        }
    } else {
        $stmt = $pdo->prepare('SELECT * FROM citizen WHERE Username = ? OR Email = ?');
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['Password'])) {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        } else {
            $_SESSION['citizen_id']   = $user['CitizenID'];
            $_SESSION['citizen_name'] = $user['FirstName'];
            $_SESSION['username']     = $user['Username'];
            $_SESSION['role']         = 'citizen';
            header('Location: ../Pages/profile.php');
            exit;
        }
    }
}

header('Location: ../Pages/login.html?error=' . urlencode($error));
exit;
?>
