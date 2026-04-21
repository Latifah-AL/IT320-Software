<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.html');
    exit;
}

$adminName    = $_SESSION['admin_name'] ?? 'المشرف';
$filterStatus = $_GET['filter'] ?? 'all';
$searchId     = trim($_GET['search'] ?? '');

$sql    = 'SELECT r.ReportID, r.Title, r.ViolationType, r.Status, r.SubmittedAt,
                  c.FirstName, c.LastName, l.Address
           FROM report r
           LEFT JOIN citizen c ON r.CitizenID = c.CitizenID
           LEFT JOIN location l ON r.ReportID = l.ReportID
           WHERE 1=1';
$params = [];

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
if (isset($_GET['updated'])) {
    $notify    = '✓ تم تحديث البلاغ بنجاح';
    $notifyCss = 'background:#e8f5e9;color:#2e7d32;';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>عينك — إدارة البلاغات</title>
    <link rel="stylesheet" href="../CSS/main.css"/>
    <link rel="stylesheet" href="../CSS/manage-reports.css"/>
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
                <li><a href="admin-dashboard.php">لوحة التحكم</a></li>
                <li><a href="manage-reports.php" class="active">إدارة البلاغات</a></li>
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

            <h1 class="page-title">إدارة البلاغات</h1>

            <?php if ($notify): ?>
                <div style="<?= $notifyCss ?>padding:12px;border-radius:8px;margin-bottom:16px;text-align:center;font-size:14px;">
                    <?= $notify ?>
                </div>
            <?php endif; ?>

            <!-- Search & Filter -->
            <div class="toolbar" style="margin-bottom:1.5rem;">
                <form method="GET" action="manage-reports.php" style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                    <input type="text" name="search" placeholder="ابحث برقم البلاغ..."
                           value="<?= htmlspecialchars($searchId) ?>"
                           style="padding:0.5rem 0.75rem;border:1px solid #ddd;border-radius:6px;font-family:inherit;font-size:0.9rem;"/>
                    <?php if ($filterStatus !== 'all'): ?>
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filterStatus) ?>"/>
                    <?php endif; ?>
                    <button type="submit" class="btn-green" style="padding:0.5rem 1rem;">بحث</button>
                </form>
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.75rem;">
                    <a href="manage-reports.php" class="filter-btn <?= $filterStatus === 'all' ? 'active' : '' ?>">الكل</a>
                    <a href="manage-reports.php?filter=Pending" class="filter-btn <?= $filterStatus === 'Pending' ? 'active' : '' ?>">قيد الانتظار</a>
                    <a href="manage-reports.php?filter=Under+Review" class="filter-btn <?= $filterStatus === 'Under Review' ? 'active' : '' ?>">قيد المراجعة</a>
                    <a href="manage-reports.php?filter=Resolved" class="filter-btn <?= $filterStatus === 'Resolved' ? 'active' : '' ?>">تم الحل</a>
                    <a href="manage-reports.php?filter=Rejected" class="filter-btn <?= $filterStatus === 'Rejected' ? 'active' : '' ?>">مرفوض</a>
                </div>
            </div>

            <p style="color:#666;margin-bottom:1rem;">عرض <?= count($reports) ?> بلاغات</p>

            <?php if (empty($reports)): ?>
                <p style="text-align:center;color:#888;padding:2rem;">لا توجد بلاغات</p>
            <?php else: ?>
                <?php foreach ($reports as $r): ?>
                    <?php
                        $stLabel = $statusLabels[$r['Status']] ?? $r['Status'];
                        $stClass = $statusClasses[$r['Status']] ?? 'pending';
                        $dateStr = substr($r['SubmittedAt'], 0, 10);
                    ?>
                    <div class="report-card">
                        <div class="report-info">
                            <h3>بلاغ رقم: <?= htmlspecialchars($r['ReportID']) ?></h3>
                            <p><?= htmlspecialchars($r['Title']) ?></p>
                            <p style="font-size:0.82rem;color:#888;">
                                المستخدم: <?= htmlspecialchars($r['FirstName'] . ' ' . $r['LastName']) ?> — <?= $dateStr ?>
                            </p>
                            <span class="status <?= $stClass ?>"><?= $stLabel ?></span>
                        </div>
                        <div class="actions">
                            <a href="admin-report-details.php?id=<?= urlencode($r['ReportID']) ?>" class="btn-view">
                                <i class="fa-solid fa-eye"></i> عرض
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

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

    <script>
    document.getElementById('burger').addEventListener('click', function () {
        document.getElementById('mobileNav').classList.toggle('open');
    });
    </script>

</body>
</html>