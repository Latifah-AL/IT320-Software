<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.html');
    exit;
}

$adminId   = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'] ?? 'المشرف';
$reportId  = $_GET['id'] ?? '';

if (!$reportId) {
    header('Location: manage-reports.php');
    exit;
}

$stmt = $pdo->prepare('
    SELECT r.ReportID, r.Title, r.ViolationType, r.Description,
           r.ImagePath, r.SubmittedAt, r.Status,
           r.CitizenID,
           c.FirstName, c.LastName, c.Username, c.TotalRewardPoints,
           l.Address
    FROM report r
    LEFT JOIN citizen c ON r.CitizenID = c.CitizenID
    LEFT JOIN location l ON r.ReportID = l.ReportID
    WHERE r.ReportID = ?
');
$stmt->execute([$reportId]);
$report = $stmt->fetch();

if (!$report) {
    header('Location: manage-reports.php');
    exit;
}

$statusLabels  = ['Pending' => 'قيد الانتظار', 'Under Review' => 'قيد المراجعة', 'Resolved' => 'تم الحل', 'Rejected' => 'مرفوض'];
$statusClasses = ['Pending' => 'pending', 'Under Review' => 'review', 'Resolved' => 'resolved', 'Rejected' => 'rejected'];
$dateStr = substr($report['SubmittedAt'], 0, 10);

// Success/error message
$notify    = htmlspecialchars($_GET['msg'] ?? '');
$notifyCss = $_GET['type'] ?? 'success' === 'error'
    ? 'background:#e8f5e9;color:#2e7d32;'
    : 'background:#fde8e8;color:#c0392b;';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>عينك — تفاصيل البلاغ</title>
    <link rel="stylesheet" href="../CSS/main.css"/>
    <link rel="stylesheet" href="../CSS/admin-report-details.css"/>
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

            <div class="page-header">
                <div>
                    <h1 class="page-title">تفاصيل البلاغ</h1>
                    <p class="report-id-label">رقم البلاغ: <span><?= htmlspecialchars($report['ReportID']) ?></span></p>
                </div>
                <a href="manage-reports.php" class="btn-back">
                    <i class="fa-solid fa-arrow-right"></i> العودة لإدارة البلاغات
                </a>
            </div>

            <?php if ($notify): ?>
                <div style="<?= $notifyCss ?>padding:12px;border-radius:8px;margin-bottom:16px;text-align:center;font-size:14px;">
                    <?= $notify ?>
                </div>
            <?php endif; ?>

            <div class="details-card">

                <!-- Info Grid -->
                <div class="detail-info-grid">
                    <div class="detail-info-item">
                        <span class="detail-label"><i class="fa-solid fa-hashtag"></i> رقم البلاغ</span>
                        <span class="detail-value mono"><?= htmlspecialchars($report['ReportID']) ?></span>
                    </div>
                    <div class="detail-info-item">
                        <span class="detail-label"><i class="fa-solid fa-tag"></i> نوع المخالفة</span>
                        <span class="detail-value"><?= htmlspecialchars($report['ViolationType']) ?></span>
                    </div>
                    <div class="detail-info-item">
                        <span class="detail-label"><i class="fa-solid fa-location-dot"></i> الموقع</span>
                        <span class="detail-value"><?= htmlspecialchars($report['Address'] ?? '—') ?></span>
                    </div>
                    <div class="detail-info-item">
                        <span class="detail-label"><i class="fa-solid fa-calendar"></i> التاريخ</span>
                        <span class="detail-value"><?= $dateStr ?></span>
                    </div>
                    <div class="detail-info-item">
                        <span class="detail-label"><i class="fa-solid fa-circle-info"></i> الحالة الحالية</span>
                        <span class="detail-value">
                            <span class="status <?= $statusClasses[$report['Status']] ?? 'pending' ?>">
                                <?= $statusLabels[$report['Status']] ?? $report['Status'] ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-info-item">
                        <span class="detail-label"><i class="fa-solid fa-user"></i> المُبلِّغ</span>
                        <span class="detail-value">
                            <?= htmlspecialchars($report['FirstName'] . ' ' . $report['LastName']) ?>
                            <span style="font-size:0.8rem;color:#888;display:block;">
                                نقاطه الحالية: <?= (int)$report['TotalRewardPoints'] ?> نقطة
                            </span>
                        </span>
                    </div>
                </div>

                <!-- Description -->
                <div class="detail-desc">
                    <span class="detail-label"><i class="fa-solid fa-align-right"></i> وصف المخالفة</span>
                    <p><?= htmlspecialchars($report['Description']) ?></p>
                </div>

                <!-- Image -->
                <div class="detail-img-wrapper">
                    <span class="detail-label"><i class="fa-solid fa-camera"></i> صورة المخالفة</span>
                    <?php if ($report['ImagePath']): ?>
                        <img src="../<?= htmlspecialchars($report['ImagePath']) ?>"
                             style="max-width:100%;border-radius:8px;margin-top:0.5rem;"
                             alt="صورة البلاغ"/>
                    <?php else: ?>
                        <div class="img-placeholder-admin">
                            <i class="fa-solid fa-image"></i>
                            <span>لا توجد صورة مرفقة</span>
                        </div>
                    <?php endif; ?>
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee;"/>

                <!-- ===== ACTION 1: Change Status ===== -->
                <div style="margin-bottom:1.5rem;">
                    <h3 style="font-size:1rem;margin-bottom:0.75rem;color:#333;">
                        <i class="fa-solid fa-sliders"></i> تغيير حالة البلاغ
                    </h3>
                    <form method="POST" action="../admin/update_status.php"
                          style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
                        <input type="hidden" name="report_id" value="<?= htmlspecialchars($report['ReportID']) ?>"/>
                        <select name="action" style="padding:0.55rem 0.75rem;border:1px solid #ddd;border-radius:6px;font-family:inherit;font-size:0.9rem;">
                            <option value="Pending"      <?= $report['Status'] === 'Pending'      ? 'selected' : '' ?>>قيد الانتظار</option>
                            <option value="Under Review" <?= $report['Status'] === 'Under Review' ? 'selected' : '' ?>>قيد المراجعة</option>
                            <option value="Resolved"     <?= $report['Status'] === 'Resolved'     ? 'selected' : '' ?>>تم الحل</option>
                            <option value="Rejected"     <?= $report['Status'] === 'Rejected'     ? 'selected' : '' ?>>مرفوض</option>
                        </select>
                        <button type="submit" class="btn-approve">
                            <i class="fa-solid fa-check"></i> حفظ الحالة
                        </button>
                    </form>
                </div>

                <!-- ===== ACTION 2: Award Points ===== -->
                <div>
                    <h3 style="font-size:1rem;margin-bottom:0.75rem;color:#333;">
                        <i class="fa-solid fa-trophy"></i> منح نقاط للمستخدم
                    </h3>
                    <form method="POST" action="../admin/award_points.php"
                          style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
                        <input type="hidden" name="report_id" value="<?= htmlspecialchars($report['ReportID']) ?>"/>
                        <input type="hidden" name="citizen_id" value="<?= htmlspecialchars($report['CitizenID']) ?>"/>
                        <input type="number" name="points" min="1" max="500" placeholder="عدد النقاط"
                               style="padding:0.55rem 0.75rem;border:1px solid #ddd;border-radius:6px;font-family:inherit;font-size:0.9rem;width:140px;"
                               required/>
                        <button type="submit" style="background:#1565C0;color:#fff;border:none;padding:0.55rem 1.2rem;border-radius:6px;cursor:pointer;font-family:inherit;font-size:0.9rem;">
                            <i class="fa-solid fa-plus"></i> منح النقاط
                        </button>
                    </form>
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