<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.html');
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'المشرف';

// Real stats from DB
$stmt  = $pdo->query('SELECT Status, COUNT(*) as cnt FROM report GROUP BY Status');
$rows  = $stmt->fetchAll();
$stats = ['Pending' => 0, 'Under Review' => 0, 'Resolved' => 0, 'Rejected' => 0, 'total' => 0];
foreach ($rows as $row) {
    $stats[$row['Status']] = (int)$row['cnt'];
    $stats['total']       += (int)$row['cnt'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>عينك — لوحة تحكم المشرف</title>
    <link rel="stylesheet" href="../CSS/main.css"/>
    <link rel="stylesheet" href="../CSS/admin-dashboard.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&family=Cairo:wght@700;800&display=swap" rel="stylesheet"/>
</head>
<body>

    <nav class="navbar">
        <div class="nav-inner">
            <a href="admin-dashboard.php" class="logo">
                <img src="../Images/logo.png" alt="عينك"/>
            </a>
            <ul class="nav-links">
                <li><a href="admin-dashboard.php" class="active">لوحة التحكم</a></li>
                <li><a href="manage-reports.php">إدارة البلاغات</a></li>
            </ul>
            <div class="nav-auth">
                <span class="nav-admin-label">
                    <i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($adminName) ?>
                </span>
                <a href="../auth/logout.php" class="btn-green">
                    <i class="fa-solid fa-right-from-bracket"></i> خروج
                </a>
            </div>
            <button class="burger" id="burger">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
        <div class="mobile-nav" id="mobileNav">
            <a href="admin-dashboard.php">لوحة التحكم</a>
            <a href="manage-reports.php">إدارة البلاغات</a>
            <a href="../auth/logout.php" class="btn-logout-mobile">خروج</a>
        </div>
    </nav>

    <main class="page">
        <div class="container">

            <h1 class="page-title">لوحة تحكم المشرف</h1>

            <div class="stats">
                <div class="card">
                    <i class="fa-solid fa-file"></i>
                    <h3>إجمالي البلاغات</h3>
                    <p><?= $stats['total'] ?></p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-clock"></i>
                    <h3>قيد الانتظار</h3>
                    <p><?= $stats['Pending'] ?></p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-spinner"></i>
                    <h3>قيد المراجعة</h3>
                    <p><?= $stats['Under Review'] ?></p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-check"></i>
                    <h3>تمت المعالجة</h3>
                    <p><?= $stats['Resolved'] ?></p>
                </div>
            </div>

            <div class="page-action">
                <a href="manage-reports.php" class="btn-green">
                    <i class="fa-solid fa-list-check"></i> إدارة البلاغات
                </a>
            </div>

        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <img src="../Images/logo.png" alt="عينك"/>
                <p>منصة مجتمعية للإبلاغ عن مخالفات المدن في المملكة العربية السعودية</p>
            </div>
            <div class="footer-links">
                <h5>روابط</h5>
                <a href="admin-dashboard.php">لوحة التحكم</a>
                <a href="manage-reports.php">إدارة البلاغات</a>
            </div>
            <div class="footer-links">
                <h5>تواصل</h5>
                <span><i class="fa-solid fa-envelope"></i> support@aynek.sa</span>
                <span><i class="fa-solid fa-location-dot"></i> المملكة العربية السعودية</span>
            </div>
        </div>
        <div class="footer-bottom">
            <p>جميع الحقوق محفوظة &copy; 2025 — عينك</p>
        </div>
    </footer>

    <script src="../JavaScript/admin-dashboard.js"></script>
    <script>
    document.getElementById('burger').addEventListener('click', function () {
        document.getElementById('mobileNav').classList.toggle('open');
    });
    </script>

</body>
</html>