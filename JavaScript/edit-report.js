document.addEventListener('DOMContentLoaded', function () {

    var params = new URLSearchParams(window.location.search);
    var id     = params.get('id');
    var report = getReport(id);

    if (!report) { window.location.href = 'my-reports.html'; return; }

    // Fill fields
    document.getElementById('reportIdLabel').textContent = report.id;
    document.getElementById('title').value               = report.title;
    document.getElementById('category').value            = report.categoryValue || 'parking';
    document.getElementById('description').value         = report.description;
    document.getElementById('location').value            = report.location;
    document.getElementById('datetime').value            = report.datetime;

    // Image
    var currentImage = document.getElementById('currentImage');
    if (report.image) {
        currentImage.innerHTML = '<img src="' + report.image + '" alt="' + report.title + '"/>';
    } else {
        currentImage.innerHTML = '<div class="img-placeholder-edit"><i class="fa-solid fa-image"></i><span>لم يتم إرفاق صورة</span></div>';
    }

    var imageDataUrl = report.image;

    // Image change
    document.getElementById('imageInput').addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            imageDataUrl = e.target.result;
            currentImage.innerHTML = '<img src="' + imageDataUrl + '" alt="صورة جديدة"/>';
        };
        reader.readAsDataURL(file);
    });

    // Location detect
    var detectBtn     = document.querySelector('.btn-detect');
    var locationInput = document.getElementById('location');

    detectBtn.addEventListener('click', function () {
        if (!navigator.geolocation) { alert('المتصفح لا يدعم تحديد الموقع'); return; }
        detectBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> جاري التحديد...';
        detectBtn.disabled  = true;
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                locationInput.value = pos.coords.latitude + ', ' + pos.coords.longitude;
                detectBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> تحديد تلقائي';
                detectBtn.disabled  = false;
            },
            function () {
                alert('تعذّر تحديد الموقع');
                detectBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> تحديد تلقائي';
                detectBtn.disabled  = false;
            }
        );
    });

    // Save
    document.getElementById('editForm').addEventListener('submit', function (e) {
        e.preventDefault();

        var category = document.getElementById('category').value;
        var cat      = categoryMap[category] || categoryMap['other'];

        var updated = {
            id:           report.id,
            title:        document.getElementById('title').value,
            category:     cat.label,
            categoryIcon: cat.icon,
            categoryValue:category,
            status:       report.status,
            statusLabel:  report.statusLabel,
            location:     document.getElementById('location').value,
            datetime:     document.getElementById('datetime').value,
            date:         formatDate(document.getElementById('datetime').value),
            description:  document.getElementById('description').value,
            image:        imageDataUrl
        };

        // Only update localStorage reports, not dummy ones
        if (!reportsData[report.id]) {
            updateReport(report.id, updated);
        }

        window.location.href = 'my-reports.html';
    });

});