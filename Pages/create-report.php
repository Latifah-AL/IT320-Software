<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['citizen_id'])) {
    header('Location: login.html');
    exit;
}

$citizenId   = $_SESSION['citizen_id'];
$citizenName = $_SESSION['citizen_name'] ?? '';
?>




<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>عينك — إضافة بلاغ</title>
        <link rel="stylesheet" href="../CSS/main.css"/>
        <link rel="stylesheet" href="../CSS/create-report.css"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&family=Cairo:wght@700;800&display=swap" rel="stylesheet"/>
    </head>
    <body>

        <!-- NAVBAR -->
<nav class="navbar">
            <div class="nav-inner">
                <a href="index.php" class="logo">
                    <img src="../Images/logo.png" alt="عينك"/>
                </a>
                <ul class="nav-links">
                    <li><a href="index.php">الرئيسية</a></li>
                    <li><a href="create-report.php"  class="active">إضافة بلاغ</a></li>
                    <li><a href="my-reports.php">بلاغاتي</a></li>
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

        <!-- PAGE CONTENT -->
        <main class="page-main">
            <div class="container">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1>إضافة بلاغ جديد</h1>
                        <p>أدخل تفاصيل المخالفة التي تريد الإبلاغ عنها</p>
                    </div>
                    <a href="my-reports.php" class="btn-back">
                        <i class="fa-solid fa-arrow-right"></i> العودة للبلاغات
                    </a>
                </div>

                <!-- Form -->
                <div class="form-wrapper">
                    <div id="error-banner" style="display:none; background:#fde8e8; color:#c0392b; padding:12px; border-radius:8px; margin-bottom:16px; text-align:center; font-size:14px;"></div>
                    <form action="../reports/create_report.php" method="POST" enctype="multipart/form-data" id="createReportForm">
                        <!-- Title -->
                        <div class="form-group">
                            <label for="title">
                                <i class="fa-solid fa-heading"></i> عنوان البلاغ
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                placeholder="مثال: وقوف مخالف أمام مدخل"
                                
                            />
                        </div>

                        <!-- Category -->
                        <div class="form-group">
                            <label for="category">
                                <i class="fa-solid fa-tag"></i> نوع المخالفة
                            </label>
                            <select id="category" name="category" >
                                <option value="" disabled selected>اختر نوع المخالفة</option>
                                <option value="وقوف مخالف">🚗 وقوف مخالف</option>
                                <option value="إشغال رصيف المشاة">🚶 إشغال رصيف المشاة</option>
                                <option value="سد مخرج طوارئ">🚑 سد مخرج طوارئ</option>
                                <option value="مواقف ذوي الاحتياجات">♿ مواقف ذوي الاحتياجات</option>
                                <option value="إعاقة حركة المرور">🚧 إعاقة حركة المرور</option>
                                <option value="أخرى">⚠️ أخرى</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">
                                <i class="fa-solid fa-align-right"></i> وصف المخالفة
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="اكتب وصفاً مختصراً للمخالفة..."
                                
                            ></textarea>
                        </div>

                        <!-- Image Upload -->
                        <div class="form-group">
                            <label>
                                <i class="fa-solid fa-camera"></i> صورة المخالفة
                            </label>
                            <input type="file" id="imageInput" name="image" accept="image/*" hidden/>
                        <div class="upload-area" onclick="document.getElementById('imageInput').click()">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <p>اضغط لرفع صورة</p>
                            <span>PNG, JPG حتى 5MB</span>
                        </div>
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <label>
                                <i class="fa-solid fa-location-dot"></i> الموقع
                            </label>
                            <div class="location-row">
                                <input type="text" id="location" name="location" placeholder="مثال: النزهة، شارع القادسية، الرياض">
                                <input type="hidden" id="lat" name="lat" >
                                <input type="hidden" id="lng" name="lng">
                                <button type="button" class="btn-detect" >
                                    <i class="fa-solid fa-crosshairs"></i> تحديد تلقائي
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="datetime">
                                <i class="fa-solid fa-clock"></i> تاريخ ووقت المخالفة
                            </label>
                            <input type="datetime-local" id="datetime" name="datetime">
                        </div>

                        <!-- Submit -->
                        <div class="form-submit">
                            <a href="my-reports.php" class="btn-cancel-form">إلغاء</a>
                            <button type="submit" class="btn-submit">
                                <i class="fa-solid fa-paper-plane"></i> إرسال البلاغ
                            </button>
                        </div>

                    </form>
                </div>

            </div>
            <!-- Success Modal -->
            <div class="modal-overlay" id="successModal" style="display:none;">
                <div class="modal">
                    <div class="modal-icon" style="color:#2E7D32;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3>تم بنجاح!</h3>
                    <p id="successMessage"></p>
                    <div class="modal-actions">
                        <button class="btn-confirm-success" onclick="window.location.href='my-reports.php'">
                            عرض البلاغات
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- FOOTER -->
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
        <script src="../JavaScript/create-report.js"></script>
    </body>
</html>