<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['citizen_id'])) {
    header('Location: login.html');
    exit;
}

$citizenId = $_SESSION['citizen_id'];

// Fetch citizen info
$stmt = $pdo->prepare('
    SELECT FirstName, LastName, Username, Email, PhoneNo, BirthDate, Nationality, IDNo, TotalRewardPoints
    FROM citizen WHERE CitizenID = ?
');
$stmt->execute([$citizenId]);
$citizen = $stmt->fetch();

if (!$citizen) {
    header('Location: login.html');
    exit;
}

// Fetch report stats
$stmt = $pdo->prepare('SELECT Status, COUNT(*) as cnt FROM report WHERE CitizenID = ? GROUP BY Status');
$stmt->execute([$citizenId]);
$statsRows = $stmt->fetchAll();
$stats = ['Pending' => 0, 'Under Review' => 0, 'Resolved' => 0, 'Rejected' => 0, 'total' => 0];
foreach ($statsRows as $row) {
    $stats[$row['Status']] = (int)$row['cnt'];
    $stats['total'] += (int)$row['cnt'];
}

// Fetch last report
$stmt = $pdo->prepare('
    SELECT r.ReportID, r.Title, r.Status, r.SubmittedAt, l.Address
    FROM report r
    LEFT JOIN location l ON r.ReportID = l.ReportID
    WHERE r.CitizenID = ?
    ORDER BY r.SubmittedAt DESC LIMIT 1
');
$stmt->execute([$citizenId]);
$lastReport = $stmt->fetch();

$statusLabels  = ['Pending' => 'قيد الانتظار', 'Under Review' => 'قيد المراجعة', 'Resolved' => 'تم الحل', 'Rejected' => 'مرفوض'];
$statusClasses = ['Pending' => 'pending', 'Under Review' => 'review', 'Resolved' => 'resolved', 'Rejected' => 'rejected'];
$pts        = (int)$citizen['TotalRewardPoints'];
$ptsPercent = min(($pts / 500) * 100, 100);
$natLabel   = $citizen['Nationality'] === 'sa' ? 'سعودي' : 'غير سعودي';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>عينك — صفحة المستخدم</title>
        <link rel="stylesheet" href="../CSS/main.css"/>
        <link rel="stylesheet" href="../CSS/profile.css"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&family=Cairo:wght@700;800&display=swap" rel="stylesheet"/>
    </head>
    <body>

        <nav class="navbar">
            <div class="nav-inner">
                <a href="index.php" class="logo">
                    <img src="../Images/logo.png" alt="عينك"/>
                </a>
                <ul class="nav-links">
                    <li><a href="index.php">الرئيسية</a></li>
                    <li><a href="create-report.php">إضافة بلاغ</a></li>
                    <li><a href="my-reports.php">بلاغاتي</a></li>
                    <li><a href="map.php">الخريطة</a></li>
                </ul>
                <div class="nav-user">
                    <a href="profile.php" class="nav-username">
                        <i class="fa-solid fa-circle-user"></i>
                        <span><?= htmlspecialchars($citizen['FirstName']) ?></span>
                    </a>
                    <a href="../auth/logout.php" class="btn-logout">
                        <i class="fa-solid fa-right-from-bracket"></i> خروج
                    </a>
                </div>
                <button class="burger" id="burger">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
            <div class="mobile-nav" id="mobileNav">
                <a href="index.php">الرئيسية</a>
                <a href="create-report.php">إضافة بلاغ</a>
                <a href="my-reports.php">بلاغاتي</a>
                <a href="map.php">الخريطة</a>
                <a href="../auth/logout.php" class="btn-logout-mobile">خروج</a>
            </div>
        </nav>

        <main class="page-main">
            <div class="container">

                <div class="page-header">
                    <h1>مرحباً <?= htmlspecialchars($citizen['FirstName']) ?>!</h1>
                </div>

                <div class="profile-layout">

                    <div class="profile-main">

                        <div class="profile-card">
                            <div class="card-header">
                                <i class="fa-solid fa-user"></i>
                                <h2>المعلومات الشخصية</h2>
                            </div>
                            <div class="info-list">
                                <div class="info-row">
                                    <span class="info-label">الاسم الأول</span>
                                    <span class="info-value"><?= htmlspecialchars($citizen['FirstName']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">اسم العائلة</span>
                                    <span class="info-value"><?= htmlspecialchars($citizen['LastName']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">اسم المستخدم</span>
                                    <div class="info-value-group">
                                        <span class="info-value"><?= htmlspecialchars($citizen['Username']) ?></span>
                                        <span class="info-note">يستخدم لتسجيل الدخول — لا يمكن تغييره</span>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">البريد الإلكتروني</span>
                                    <span class="info-value"><?= htmlspecialchars($citizen['Email']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">رقم الجوال</span>
                                    <span class="info-value ltr"><?= htmlspecialchars($citizen['PhoneNo']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">رقم الهوية الوطنية</span>
                                    <span class="info-value ltr"><?= htmlspecialchars($citizen['IDNo']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">الجنسية</span>
                                    <span class="info-value"><?= $natLabel ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">تاريخ الميلاد</span>
                                    <span class="info-value ltr"><?= htmlspecialchars($citizen['BirthDate']) ?></span>
                                </div>
                            </div>
                        </div>

                        <?php if ($lastReport): ?>
                        <div class="profile-card last-report-card">
                            <div class="card-header">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                <h2>آخر بلاغ</h2>
                            </div>
                            <div class="last-report-body">
                                <div class="last-report-top">
                                    <span class="last-report-id"><?= htmlspecialchars($lastReport['ReportID']) ?></span>
                                    <span class="card-status <?= $statusClasses[$lastReport['Status']] ?? 'pending' ?>">
                                        <?= $statusLabels[$lastReport['Status']] ?? $lastReport['Status'] ?>
                                    </span>
                                </div>
                                <h3><?= htmlspecialchars($lastReport['Title']) ?></h3>
                                <div class="last-report-meta">
                                    <span>
                                        <i class="fa-solid fa-location-dot"></i>
                                        <?= htmlspecialchars($lastReport['Address'] ?? '—') ?>
                                    </span>
                                    <span>
                                        <i class="fa-solid fa-calendar"></i>
                                        <?= substr($lastReport['SubmittedAt'], 0, 10) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="last-report-footer">
                                <a href="report-detail.php?id=<?= urlencode($lastReport['ReportID']) ?>" class="btn-view-report">
                                    <i class="fa-solid fa-eye"></i> عرض البلاغ
                                </a>
                                <a href="my-reports.php" class="btn-all-reports">
                                    عرض جميع بلاغاتي <i class="fa-solid fa-arrow-left"></i>
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="profile-card last-report-card">
                            <div class="card-header">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                <h2>آخر بلاغ</h2>
                            </div>
                            <p style="padding:1rem;color:#888;text-align:center;">لم تقم برفع أي بلاغ بعد</p>
                            <div class="last-report-footer">
                                <a href="create-report.php" class="btn-view-report">
                                    <i class="fa-solid fa-plus"></i> أضف بلاغاً الآن
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <div class="profile-side">

                        <div class="points-card">
                            <div class="points-top">
                                <i class="fa-solid fa-trophy"></i>
                                <div>
                                    <p class="points-label">نقاطي</p>
                                    <p class="points-value"><?= $pts ?> <span>نقطة</span></p>
                                </div>
                            </div>
                            <div class="points-bar">
                                <div class="points-fill" style="width:<?= $ptsPercent ?>%;"></div>
                            </div>
                            <p class="points-hint"><?= $pts ?> / 500 للوصول للمستوى التالي</p>
                        </div>

                        <div class="profile-card">
                            <div class="card-header">
                                <i class="fa-solid fa-chart-simple"></i>
                                <h2>إحصائياتي</h2>
                            </div>
                            <div class="stats-list">
                                <div class="stat-row">
                                    <span class="stat-label">إجمالي البلاغات</span>
                                    <span class="stat-num"><?= $stats['total'] ?></span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">قيد الانتظار</span>
                                    <span class="stat-num pending"><?= $stats['Pending'] ?></span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">قيد المراجعة</span>
                                    <span class="stat-num review"><?= $stats['Under Review'] ?></span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">تم الحل</span>
                                    <span class="stat-num resolved"><?= $stats['Resolved'] ?></span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">مرفوض</span>
                                    <span class="stat-num rejected"><?= $stats['Rejected'] ?></span>
                                </div>
                            </div>
                        </div>

                    </div>
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
                    <a href="index.php">الرئيسية</a>
                    <a href="create-report.php">إضافة بلاغ</a>
                    <a href="my-reports.php">بلاغاتي</a>
                    <a href="map.php">الخريطة</a>
                </div>
                <div class="footer-links">
                    <h5>تواصل معنا</h5>
                    <span><i class="fa-solid fa-envelope"></i> support@aynek.sa</span>
                    <span><i class="fa-solid fa-location-dot"></i> المملكة العربية السعودية</span>
                </div>
            </div>
            <div class="footer-bottom">
                <p>جميع الحقوق محفوظة &copy; 2025 — عينك</p>
            </div>
        </footer>

        <script>
        document.getElementById('burger').addEventListener('click', function () {
            document.getElementById('mobileNav').classList.toggle('open');
        });
        </script>

    </body>
</html>