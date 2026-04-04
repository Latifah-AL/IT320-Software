document.addEventListener('DOMContentLoaded', function () {

    const detectBtn     = document.querySelector('.btn-detect');
    const locationInput = document.getElementById('location');

    detectBtn.addEventListener('click', function () {

        if (!navigator.geolocation) {
            alert('المتصفح لا يدعم تحديد الموقع');
            return;
        }

        detectBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> جاري التحديد...';
        detectBtn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function (position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

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
                .catch(function () {
                    locationInput.value = lat + ', ' + lng;
                });

                detectBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> تحديد تلقائي';
                detectBtn.disabled = false;
            },
            function (error) {
                if (error.code === 1)      alert('تم رفض إذن الموقع');
                else if (error.code === 2) alert('تعذّر تحديد الموقع');
                else if (error.code === 3) alert('انتهت مهلة التحديد');
                detectBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> تحديد تلقائي';
                detectBtn.disabled = false;
            },
            { timeout: 10000, maximumAge: 0, enableHighAccuracy: false }
        );

    });

    // Image preview
    const imageInput = document.getElementById('imageInput');
    const uploadArea  = document.querySelector('.upload-area');

    imageInput.addEventListener('change', function () {
        var file = imageInput.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            uploadArea.innerHTML = '<img src="' + e.target.result + '" style="width:100%; height:180px; object-fit:cover; border-radius:8px;"/>';
        };
        reader.readAsDataURL(file);
    });

});