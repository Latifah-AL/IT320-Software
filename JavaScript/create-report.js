document.addEventListener('DOMContentLoaded', function () {

    // Location detect
    var detectBtn     = document.querySelector('.btn-detect');
    var locationInput = document.getElementById('location');

    if (detectBtn && locationInput) {
        detectBtn.addEventListener('click', function () {
            if (!navigator.geolocation) { alert('المتصفح لا يدعم تحديد الموقع'); return; }
            detectBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> جاري التحديد...';
            detectBtn.disabled  = true;
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
                        locationInput.value = readable || data.display_name;
                    })
                    .catch(function () { locationInput.value = lat + ', ' + lng; });
                    detectBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> تحديد تلقائي';
                    detectBtn.disabled  = false;
                },
                function (error) {
                    if (error.code === 1)      alert('تم رفض إذن الموقع');
                    else if (error.code === 2) alert('تعذّر تحديد الموقع');
                    else if (error.code === 3) alert('انتهت مهلة التحديد');
                    detectBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> تحديد تلقائي';
                    detectBtn.disabled  = false;
                },
                { timeout: 10000, maximumAge: 0, enableHighAccuracy: false }
            );
        });
    }

    // Image preview
    var imageInput   = document.getElementById('imageInput');
    var uploadArea   = document.querySelector('.upload-area');
    var imageDataUrl = null;

    if (imageInput && uploadArea) {
        imageInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                imageDataUrl = e.target.result;
                uploadArea.innerHTML = '<img src="' + imageDataUrl + '" style="width:100%;height:180px;object-fit:cover;border-radius:8px;"/>';
            };
            reader.readAsDataURL(file);
        });
    }

    // Form submit
    var form = document.getElementById('createReportForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var category = document.getElementById('category').value;
            var cat      = categoryMap[category] || categoryMap['other'];

            var report = {
                id:           generateId(),
                title:        document.getElementById('title').value,
                category:     cat.label,
                categoryIcon: cat.icon,
                categoryValue:category,
                status:       'pending',
                statusLabel:  'قيد الانتظار',
                location:     document.getElementById('location').value,
                datetime:     document.getElementById('datetime').value,
                date:         formatDate(document.getElementById('datetime').value),
                description:  document.getElementById('description').value,
                image:        imageDataUrl
            };

            saveReport(report);
            window.location.href = 'my-reports.html';
        });
    }

});