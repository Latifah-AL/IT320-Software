<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['citizen_id'])) {
    header('Location: login.html');
    exit;
}

$citizenId    = $_SESSION['citizen_id'];
$citizenName  = $_SESSION['citizen_name'] ?? '';
$filterStatus = $_GET['filter'] ?? 'all';
$searchId     = trim($_GET['search'] ?? '');

$sql    = 'SELECT r.ReportID, r.Title, r.ViolationType, r.Status, r.SubmittedAt, r.ImagePath, l.Address
           FROM report r
           LEFT JOIN location l ON r.ReportID = l.ReportID
           WHERE r.CitizenID = ?';
$params = [$citizenId];

if ($filterStatus !== 'all') {
    $sql     .= ' AND r.Status = ?';
    $params[] = $filterStatus;
}
if ($searchId !== '') {
    $sql     .= ' AND r.ReportID LIKE ?';
    $params[] = '%' . $searchId . '%';
}
$sql .= ' ORDER BY r.SubmittedAt DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

$statusLabels  = ['Pending' => 'قيد الانتظار', 'Under Review' => 'قيد المراجعة', 'Resolved' => 'تم الحل', 'Rejected' => 'مرفوض'];
$statusClasses = ['Pending' => 'pending', 'Under Review' => 'review', 'Resolved' => 'resolved', 'Rejected' => 'rejected'];

$notify    = '';
$notifyCss = '';
if (isset($_GET['success'])) {
    $notify    = '✓ تم إرسال البلاغ بنجاح برقم ' . htmlspecialchars($_GET['id'] ?? '');
    $notifyCss = 'background:#e8f5e9;color:#2e7d32;';
} elseif (isset($_GET['deleted'])) {
    $notify    = 'تم حذف البلاغ بنجاح';
    $notifyCss = 'background:#fde8e8;color:#c0392b;';
} elseif (isset($_GET['updated'])) {
    $notify    = '✓ تم تحديث البلاغ بنجاح';
    $notifyCss = 'background:#e8f5e9;color:#2e7d32;';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>عينك — بلاغاتي</title>
        <link rel="stylesheet" href="../CSS/main.css"/>
        <link rel="stylesheet" href="../CSS/my-reports.css"/>
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
                    <li><a href="my-reports.php" class="active">بلاغاتي</a></li>
                    <li><a href="map.php">الخريطة</a></li>
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
                <a href="index.php">الرئيسية</a>
                <a href="create-report.php">إضافة بلاغ</a>
                <a href="my-reports.php">بلاغاتي</a>
                <a href="map.php">الخريطة</a>
                <a href="../auth/logout.php" class="btn-logout-mobile">خروج</a>
            </div>
        </nav>

        <main class="page-main">
            <div class="container">

                <?php if ($notify): ?>
                    <div style="<?= $notifyCss ?>padding:12px;border-radius:8px;margin-bottom:16px;text-align:center;font-size:14px;">
                        <?= $notify ?>
                    </div>
                <?php endif; ?>

                <div class="page-header">
                    <div>
                        <h1>بلاغاتي</h1>
                        <p>جميع البلاغات التي قمت برفعها</p>
                    </div>
                    <div class="header-btns">
                        <a href="map.php" class="btn-outline-dark">
                            <i class="fa-solid fa-map-location-dot"></i> عرض كافة البلاغات على الخريطة
                        </a>
                        <a href="create-report.php" class="btn-green">
                            <i class="fa-solid fa-plus"></i> بلاغ جديد
                        </a>
                    </div>
                </div>

                <!-- Search & Filter -->
                <div class="toolbar">
                    <form method="GET" action="my-reports.php" class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" placeholder="ابحث برقم البلاغ..."
                               value="<?= htmlspecialchars($searchId) ?>"/>
                        <?php if ($filterStatus !== 'all'): ?>
                            <input type="hidden" name="filter" value="<?= htmlspecialchars($filterStatus) ?>"/>
                        <?php endif; ?>
                        <button type="submit">بحث</button>
                    </form>
                    <div class="filter-btns">
                        <a href="my-reports.php" class="filter-btn <?= $filterStatus === 'all' ? 'active' : '' ?>">الكل</a>
                        <a href="my-reports.php?filter=Pending" class="filter-btn <?= $filterStatus === 'Pending' ? 'active' : '' ?>">قيد الانتظار</a>
                        <a href="my-reports.php?filter=Under+Review" class="filter-btn <?= $filterStatus === 'Under Review' ? 'active' : '' ?>">قيد المراجعة</a>
                        <a href="my-reports.php?filter=Resolved" class="filter-btn <?= $filterStatus === 'Resolved' ? 'active' : '' ?>">تم الحل</a>
                        <a href="my-reports.php?filter=Rejected" class="filter-btn <?= $filterStatus === 'Rejected' ? 'active' : '' ?>">مرفوض</a>
                    </div>
                </div>

                <p class="results-count">عرض <?= count($reports) ?> بلاغات</p>

                <div class="reports-grid">
                    <?php if (empty($reports)): ?>
                        <div style="grid-column:1/-1;text-align:center;padding:3rem;">
                            <i class="fa-solid fa-inbox" style="font-size:2.5rem;color:#ccc;display:block;margin-bottom:0.75rem;"></i>
                            <p style="color:#888;">لا توجد بلاغات</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reports as $r): ?>
                            <?php
                                $stLabel = $statusLabels[$r['Status']] ?? $r['Status'];
                                $stClass = $statusClasses[$r['Status']] ?? 'pending';
                                $dateStr = substr($r['SubmittedAt'], 0, 10);
                            ?>
                            <div class="report-card">
                                <div class="card-img">
                                    <?php if ($r['ImagePath']): ?>
                                        <img src="../<?= htmlspecialchars($r['ImagePath']) ?>"
                                             alt="<?= htmlspecialchars($r['Title']) ?>"/>
                                    <?php else: ?>
                                        <div class="img-placeholder">
                                            <i class="fa-solid fa-image"></i>
                                            <span>لم يتم إرفاق صورة</span>
                                        </div>
                                    <?php endif; ?>
                                    <span class="card-status <?= $stClass ?>"><?= $stLabel ?></span>
                                </div>
                                <div class="card-body">
                                    <div class="card-id"><?= htmlspecialchars($r['ReportID']) ?></div>
                                    <h3><?= htmlspecialchars($r['Title']) ?></h3>
                                    <div class="card-meta">
                                        <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($r['Address'] ?? '—') ?></span>
                                        <span><i class="fa-solid fa-calendar"></i> <?= $dateStr ?></span>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <a href="report-detail.php?id=<?= urlencode($r['ReportID']) ?>" class="btn-view">
                                        <i class="fa-solid fa-eye"></i> عرض
                                    </a>
                                    <a href="edit-report.php?id=<?= urlencode($r['ReportID']) ?>" class="btn-edit">
                                        <i class="fa-solid fa-pen"></i> تعديل
                                    </a>
                                    <form method="POST" action="../reports/delete_report.php"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا البلاغ؟')">
                                        <input type="hidden" name="report_id"
                                               value="<?= htmlspecialchars($r['ReportID']) ?>"/>
                                        <button type="submit" class="btn-delete">
                                            <i class="fa-solid fa-trash"></i> حذف
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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