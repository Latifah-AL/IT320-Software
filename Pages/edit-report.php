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

// Fetch report — must belong to this citizen
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

// Get errors and old values from session if redirected back after failed edit
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old']    ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

// Use old values if available (after failed submit), otherwise use DB values
$title       = $old['title']       ?? $report['Title'];
$category    = $old['category']    ?? $report['ViolationType'];
$description = $old['description'] ?? $report['Description'];
$location    = $old['location']    ?? $report['Address'] ?? '';
$datetime    = $old['datetime']    ?? substr($report['SubmittedAt'], 0, 16);

$categories = [
    'وقوف مخالف'           => '🚗 وقوف مخالف',
    'إشغال رصيف المشاة'    => '🚶 إشغال رصيف المشاة',
    'سد مخرج طوارئ'        => '🚑 سد مخرج طوارئ',
    'مواقف ذوي الاحتياجات' => '♿ مواقف ذوي الاحتياجات',
    'إعاقة حركة المرور'    => '🚧 إعاقة حركة المرور',
    'أخرى'                  => '⚠️ أخرى',
];

function fieldError($errors, $key) {
    if (!empty($errors[$key])) {
        echo '<span style="display:block;font-size:0.82rem;color:#C62828;margin-top:0.3rem;">'
           . htmlspecialchars($errors[$key]) . '</span>';
    }
}
function borderStyle($errors, $key) {
    return !empty($errors[$key]) ? 'border-color:#C62828;' : '';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>عينك — تعديل البلاغ</title>
        <link rel="stylesheet" href="../CSS/main.css"/>
        <link rel="stylesheet" href="../CSS/edit-report.css"/>
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

                <div class="page-header">
                    <div>
                        <h1>تعديل البلاغ</h1>
                        <p class="report-id-label">رقم البلاغ: <span><?= htmlspecialchars($report['ReportID']) ?></span></p>
                    </div>
                    <a href="my-reports.php" class="btn-back">
                        <i class="fa-solid fa-arrow-right"></i> العودة للبلاغات
                    </a>
                </div>

                <div class="form-wrapper">

                    <?php if (!empty($errors)): ?>
                        <div style="background:#fde8e8;color:#c0392b;padding:12px;border-radius:8px;margin-bottom:16px;text-align:center;font-size:14px;">
                            يرجى تصحيح الأخطاء أدناه قبل الحفظ
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="../reports/edit_report.php" enctype="multipart/form-data">

                        <input type="hidden" name="report_id" value="<?= htmlspecialchars($report['ReportID']) ?>"/>
                        <input type="hidden" name="lat" id="lat" value="<?= htmlspecialchars($report['Latitude'] ?? '0') ?>"/>
                        <input type="hidden" name="lng" id="lng" value="<?= htmlspecialchars($report['Longitude'] ?? '0') ?>"/>

                        <!-- Title -->
                        <div class="form-group">
                            <label for="title">
                                <i class="fa-solid fa-heading"></i> عنوان البلاغ
                            </label>
                            <input type="text" id="title" name="title"
                                   value="<?= htmlspecialchars($title) ?>"
                                   style="<?= borderStyle($errors, 'title') ?>"/>
                            <?php fieldError($errors, 'title'); ?>
                        </div>

                        <!-- Category -->
                        <div class="form-group">
                            <label for="category">
                                <i class="fa-solid fa-tag"></i> نوع المخالفة
                            </label>
                            <select id="category" name="category"
                                    style="<?= borderStyle($errors, 'category') ?>">
                                <?php foreach ($categories as $val => $label): ?>
                                    <option value="<?= htmlspecialchars($val) ?>"
                                        <?= $category === $val ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php fieldError($errors, 'category'); ?>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">
                                <i class="fa-solid fa-align-right"></i> وصف المخالفة
                            </label>
                            <textarea id="description" name="description" rows="4"
                                      style="<?= borderStyle($errors, 'description') ?>"><?= htmlspecialchars($description) ?></textarea>
                            <?php fieldError($errors, 'description'); ?>
                        </div>

                        <!-- Current Image + Upload -->
                        <div class="form-group">
                            <label>
                                <i class="fa-solid fa-camera"></i> صورة المخالفة
                            </label>
                            <?php if ($report['ImagePath']): ?>
                                <div class="current-image" style="margin-bottom:0.75rem;">
                                    <img src="../<?= htmlspecialchars($report['ImagePath']) ?>"
                                         alt="الصورة الحالية"
                                         style="max-width:100%;border-radius:8px;"/>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="imageInput" name="image"
                                   accept="image/jpeg,image/jpg,image/png"
                                   hidden/>
                            <div class="upload-area" onclick="document.getElementById('imageInput').click()">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <p>اضغط لتغيير الصورة (اختياري)</p>
                                <span>PNG, JPG حتى 5MB</span>
                            </div>
                            <div id="newImagePreview"></div>
                            <?php fieldError($errors, 'image'); ?>
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <label for="location">
                                <i class="fa-solid fa-location-dot"></i> الموقع
                            </label>
                            <div class="location-row">
                                <input type="text" id="location" name="location"
                                       value="<?= htmlspecialchars($location) ?>"
                                       style="<?= borderStyle($errors, 'location') ?>"/>
                                <button type="button" class="btn-detect" id="detectBtn">
                                    <i class="fa-solid fa-crosshairs"></i> تحديد تلقائي
                                </button>
                            </div>
                            <?php fieldError($errors, 'location'); ?>
                        </div>

                        <!-- Date & Time -->
                        <div class="form-group">
                            <label for="datetime">
                                <i class="fa-solid fa-clock"></i> تاريخ ووقت المخالفة
                            </label>
                            <input type="datetime-local" id="datetime" name="datetime"
                                   value="<?= htmlspecialchars($datetime) ?>"
                                   style="<?= borderStyle($errors, 'datetime') ?>"/>
                            <?php fieldError($errors, 'datetime'); ?>
                        </div>

                        <!-- Submit -->
                        <div class="form-submit">
                            <a href="my-reports.php" class="btn-cancel-form">إلغاء</a>
                            <button type="submit" class="btn-submit">
                                <i class="fa-solid fa-floppy-disk"></i> حفظ التعديلات
                            </button>
                        </div>

                    </form>
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

        // Image preview
        document.getElementById('imageInput').addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var allowed = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowed.includes(file.type)) {
                alert('صيغة الملف غير مدعومة. يرجى رفع صورة بصيغة JPG أو PNG فقط');
                this.value = ''; return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('حجم الصورة كبير جداً. الحد الأقصى 5MB');
                this.value = ''; return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                var preview = document.getElementById('newImagePreview');
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-width:100%;border-radius:8px;margin-top:0.5rem;" alt="صورة جديدة"/>';
            };
            reader.readAsDataURL(file);
        });

        // Geolocation detect
        document.getElementById('detectBtn').addEventListener('click', function () {
            var btn = this;
            if (!navigator.geolocation) { alert('المتصفح لا يدعم تحديد الموقع'); return; }
            btn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> جاري التحديد...';
            btn.disabled  = true;
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    var lat = pos.coords.latitude;
                    var lng = pos.coords.longitude;
                    fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json&accept-language=ar')
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        var a = data.address;
                        var readable = '';
                        if (a.neighbourhood) readable += a.neighbourhood + '، ';
                        if (a.road)          readable += a.road + '، ';
                        if (a.city)          readable += a.city;
                        else if (a.state)    readable += a.state;
                        document.getElementById('location').value = readable || data.display_name;
                        document.getElementById('lat').value = lat;
                        document.getElementById('lng').value = lng;
                    })
                    .catch(function () {
                        document.getElementById('location').value = lat + ', ' + lng;
                    });
                    btn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> تحديد تلقائي';
                    btn.disabled  = false;
                },
                function () {
                    alert('تعذّر تحديد الموقع');
                    btn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> تحديد تلقائي';
                    btn.disabled  = false;
                },
                { timeout: 10000, maximumAge: 0, enableHighAccuracy: false }
            );
        });
        </script>

    </body>
</html>