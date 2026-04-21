<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['citizen_id'])) {
    header('Location: login.html');
    exit;
}

$citizenId   = $_SESSION['citizen_id'];
$citizenName = $_SESSION['citizen_name'] ?? '';
$reportId    = $_GET['id'] ?? '';

if (!$reportId) {
    header('Location: my-reports.php');
    exit;
}

// Fetch report details with location
$stmt = $pdo->prepare('
    SELECT r.ReportID, r.Title, r.ViolationType, r.Description,
           r.ImagePath, r.SubmittedAt, r.Status,
           l.Address, l.Latitude, l.Longitude
    FROM report r
    LEFT JOIN location l ON r.ReportID = l.ReportID
    WHERE r.ReportID = ? AND r.CitizenID = ?
');
$stmt->execute([$reportId, $citizenId]);
$report = $stmt->fetch();

if (!$report) {
    header('Location: my-reports.php');
    exit;
}

$statusLabels = [
    'Pending'      => 'قيد الانتظار',
    'Under Review' => 'قيد المراجعة',
    'Resolved'     => 'تم الحل',
    'Rejected'     => 'مرفوض'
];
$statusClasses = [
    'Pending'      => 'pending',
    'Under Review' => 'review',
    'Resolved'     => 'resolved',
    'Rejected'     => 'rejected'
];

// Timeline steps
$timelineSteps = [
    ['label' => 'تم الرفع',      'key' => 'done'],
    ['label' => 'قيد الانتظار', 'key' => 'Pending'],
    ['label' => 'قيد المراجعة', 'key' => 'Under Review'],
    ['label' => 'تم الحل',      'key' => 'Resolved'],
];
$statusOrder = ['done', 'Pending', 'Under Review', 'Resolved'];
$currentIdx  = array_search($report['Status'], $statusOrder);
if ($currentIdx === false) $currentIdx = 1;

$dateStr = substr($report['SubmittedAt'], 0, 10);
$timeStr = $report['SubmittedAt'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>عينك — <?= htmlspecialchars($report['Title']) ?></title>
        <link rel="stylesheet" href="../CSS/main.css"/>
        <link rel="stylesheet" href="../CSS/report-detail.css"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&family=Cairo:wght@700;800&display=swap" rel="stylesheet"/>
    </head>
    <body>

        <nav class="navbar">
            <div class="nav-inner">
                <a href="index.html" class="logo">
                    <img src="../Images/logo.png" alt="عينك"/>
                </a>
                <ul class="nav-links">
                    <li><a href="index.html">الرئيسية</a></li>
                    <li><a href="create-report.php">إضافة بلاغ</a></li>
                    <li><a href="my-reports.php" class="active">بلاغاتي</a></li>
                    <li><a href="map.html">الخريطة</a></li>
                </ul>
                <div class="nav-user">
                    <a href="profile.php" class="nav-username">
                        <i class="fa-solid fa-circle-user"></i>
                        <span><?= htmlspecialchars($citizenName) ?></span>
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
                <a href="index.html">الرئيسية</a>
                <a href="create-report.php">إضافة بلاغ</a>
                <a href="my-reports.php">بلاغاتي</a>
                <a href="map.html">الخريطة</a>
                <a href="../auth/logout.php" class="btn-logout-mobile">خروج</a>
            </div>
        </nav>

        <main class="page-main">
            <div class="container">

                <div class="page-header">
                    <div>
                        <h1>تفاصيل البلاغ</h1>
                        <p class="report-id-label">رقم البلاغ: <span><?= htmlspecialchars($report['ReportID']) ?></span></p>
                    </div>
                    <div class="header-actions">
                        <a href="my-reports.php" class="btn-back">
                            <i class="fa-solid fa-arrow-right"></i> العودة للبلاغات
                        </a>
                    </div>
                </div>

                <div class="detail-layout">

                    <!-- Right Column -->
                    <div class="detail-right">

                        <!-- Image -->
                        <div class="detail-img">
                            <?php if ($report['ImagePath']): ?>
                                <img src="../<?= htmlspecialchars($report['ImagePath']) ?>"
                                     alt="<?= htmlspecialchars($report['Title']) ?>"
                                     style="width:100%;height:100%;object-fit:cover;"/>
                            <?php else: ?>
                                <div class="img-placeholder-detail">
                                    <i class="fa-solid fa-image"></i>
                                    <span>لم يتم إرفاق صورة</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Status Card -->
                        <div class="status-card">
                            <h3>حالة البلاغ</h3>
                            <div class="status-badge <?= $statusClasses[$report['Status']] ?? 'pending' ?>">
                                <?= $statusLabels[$report['Status']] ?? $report['Status'] ?>
                            </div>

                            <!-- Timeline -->
                            <div class="status-timeline">
                                <?php foreach ($timelineSteps as $i => $step): ?>
                                    <?php
                                        $isDone   = $i < $currentIdx;
                                        $isActive = $i === $currentIdx;
                                        $cls      = $isDone ? 'done' : ($isActive ? 'active' : '');
                                    ?>
                                    <div class="timeline-step <?= $cls ?>">
                                        <div class="step-dot"></div>
                                        <div class="step-info">
                                            <span class="step-title"><?= $step['label'] ?></span>
                                            <span class="step-date"><?= ($isDone || $isActive) ? $dateStr : '—' ?></span>
                                        </div>
                                    </div>
                                    <?php if ($i < count($timelineSteps) - 1): ?>
                                        <div class="timeline-line <?= $isDone ? 'done' : '' ?>"></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>

                    <!-- Left Column -->
                    <div class="detail-left">
                        <div class="detail-card">

                            <div class="detail-title-row">
                                <div class="category-icon">
                                    <i class="fa-solid fa-car-burst"></i>
                                </div>
                                <div>
                                    <h2><?= htmlspecialchars($report['Title']) ?></h2>
                                    <span class="category-label"><?= htmlspecialchars($report['ViolationType']) ?></span>
                                </div>
                            </div>

                            <hr class="detail-divider"/>

                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">
                                        <i class="fa-solid fa-location-dot"></i> الموقع
                                    </span>
                                    <span class="info-value"><?= htmlspecialchars($report['Address'] ?? '—') ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">
                                        <i class="fa-solid fa-calendar"></i> تاريخ الرفع
                                    </span>
                                    <span class="info-value"><?= $dateStr ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">
                                        <i class="fa-solid fa-clock"></i> وقت المخالفة
                                    </span>
                                    <span class="info-value"><?= $timeStr ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">
                                        <i class="fa-solid fa-hashtag"></i> رقم البلاغ
                                    </span>
                                    <span class="info-value mono"><?= htmlspecialchars($report['ReportID']) ?></span>
                                </div>
                            </div>

                            <hr class="detail-divider"/>

                            <div class="detail-desc">
                                <span class="info-label">
                                    <i class="fa-solid fa-align-right"></i> وصف المخالفة
                                </span>
                                <p><?= htmlspecialchars($report['Description']) ?></p>
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
                    <a href="index.html">الرئيسية</a>
                    <a href="create-report.php">إضافة بلاغ</a>
                    <a href="my-reports.php">بلاغاتي</a>
                    <a href="map.html">الخريطة</a>
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