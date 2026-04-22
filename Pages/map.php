<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['citizen_id'])) {
    header('Location: login.html');
    exit;
}

$citizenName = $_SESSION['citizen_name'] ?? '';

// Fetch all reports that have real coordinates
$stmt = $pdo->query('
    SELECT r.ReportID, r.Title, r.ViolationType, r.Status,
           l.Address, l.Latitude, l.Longitude
    FROM report r
    LEFT JOIN location l ON r.ReportID = l.ReportID
    WHERE l.Latitude != 0 AND l.Longitude != 0
    ORDER BY r.SubmittedAt DESC
');
$reports = $stmt->fetchAll();

$statusLabels  = [
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
$statusColors = [
    'Pending'      => '#E65100',
    'Under Review' => '#1565C0',
    'Resolved'     => '#2E7D32',
    'Rejected'     => '#C62828'
];

// Build JS-safe array for Leaflet
$mapData = [];
foreach ($reports as $r) {
    $mapData[] = [
        'id'       => $r['ReportID'],
        'title'    => $r['Title'],
        'type'     => $r['ViolationType'],
        'status'   => $r['Status'],
        'label'    => $statusLabels[$r['Status']] ?? $r['Status'],
        'color'    => $statusColors[$r['Status']] ?? '#888',
        'location' => $r['Address'] ?? '—',
        'lat'      => (float)$r['Latitude'],
        'lng'      => (float)$r['Longitude'],
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>عينك — الخريطة</title>
        <link rel="stylesheet" href="../CSS/main.css"/>
        <link rel="stylesheet" href="../CSS/map.css"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
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
                    <li><a href="map.php" class="active">الخريطة</a></li>
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

        <div class="map-layout">

            <!-- Sidebar -->
            <aside class="map-sidebar">
                <div class="sidebar-header">
                    <h2><i class="fa-solid fa-map-location-dot"></i> البلاغات على الخريطة</h2>
                    <span class="report-count"><?= count($reports) ?> بلاغات</span>
                </div>

                <div class="sidebar-list">
                    <?php if (empty($reports)): ?>
                        <div style="text-align:center;padding:2rem;color:#888;">
                            <i class="fa-solid fa-map-pin" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                            لا توجد بلاغات على الخريطة بعد
                        </div>
                    <?php else: ?>
                        <?php foreach ($reports as $i => $r): ?>
                            <?php
                                $cls   = $statusClasses[$r['Status']] ?? 'pending';
                                $label = $statusLabels[$r['Status']] ?? $r['Status'];
                            ?>
                            <div class="sidebar-item" onclick="focusPin(<?= $i ?>)">
                                <div class="sidebar-icon <?= $cls ?>">
                                    <i class="fa-solid fa-car-burst"></i>
                                </div>
                                <div class="sidebar-info">
                                    <h4><?= htmlspecialchars($r['Title']) ?></h4>
                                    <span>
                                        <i class="fa-solid fa-location-dot"></i>
                                        <?= htmlspecialchars($r['Address'] ?? '—') ?>
                                    </span>
                                </div>
                                <span class="status-dot <?= $cls ?>"></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Legend -->
                <div class="map-legend">
                    <h4>الحالات</h4>
                    <div class="legend-item">
                        <span class="status-dot pending"></span> قيد الانتظار
                    </div>
                    <div class="legend-item">
                        <span class="status-dot review"></span> قيد المراجعة
                    </div>
                    <div class="legend-item">
                        <span class="status-dot resolved"></span> تم الحل
                    </div>
                    <div class="legend-item">
                        <span class="status-dot rejected"></span> مرفوض
                    </div>
                </div>

            </aside>

            <!-- Map -->
            <div id="map"></div>

        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
        <script>
        // Reports data from PHP — no AJAX needed
        var reports = <?= json_encode($mapData) ?>;

        var map = L.map('map').setView([24.7136, 46.6753], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var markers = reports.map(function (r) {
            var icon = L.divIcon({
                className: '',
                html: '<div style="width:16px;height:16px;border-radius:50%;background:' + r.color + ';border:2.5px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',
                iconSize:   [16, 16],
                iconAnchor: [8, 8]
            });

            var popup = '<div style="direction:rtl;font-family:Tajawal,sans-serif;min-width:180px;">'
                + '<h4 style="font-size:0.92rem;margin-bottom:0.3rem;">' + r.title + '</h4>'
                + '<p style="font-size:0.8rem;color:#5a6b5e;margin-bottom:0.2rem;">🏷️ ' + r.type + '</p>'
                + '<p style="font-size:0.8rem;color:#5a6b5e;margin-bottom:0.3rem;">📍 ' + r.location + '</p>'
                + '<span style="font-size:0.75rem;font-weight:700;padding:0.15rem 0.6rem;border-radius:20px;background:' + r.color + '20;color:' + r.color + ';">' + r.label + '</span>'
                + '</div>';

            var marker = L.marker([r.lat, r.lng], { icon: icon }).addTo(map).bindPopup(popup);
            marker.on('mouseover', function () { this.openPopup(); });
            marker.on('mouseout',  function () { this.closePopup(); });
            return marker;
        });

        function focusPin(index) {
            map.setView(markers[index].getLatLng(), 15);
            markers[index].openPopup();
        }

        document.getElementById('burger').addEventListener('click', function () {
            document.getElementById('mobileNav').classList.toggle('open');
        });
        </script>

    </body>
</html>