var reports = [
    { title: 'وقوف مخالف أمام مدخل',       category: 'وقوف مخالف',            status: 'pending',  location: 'حي النزهة، الرياض',        lat: 24.7741, lng: 46.6922 },
    { title: 'إشغال رصيف المشاة',            category: 'إشغال الأرصفة',          status: 'resolved', location: 'حي العليا، الرياض',         lat: 24.6877, lng: 46.6860 },
    { title: 'سد مخرج طوارئ',               category: 'سد مخارج الطوارئ',       status: 'review',   location: 'حي الملز، الرياض',          lat: 24.6972, lng: 46.7219 },
    { title: 'احتلال موقف ذوي الاحتياجات',  category: 'مواقف ذوي الاحتياجات',   status: 'rejected', location: 'حي السليمانية، الرياض',     lat: 24.6890, lng: 46.6800 },
    { title: 'إعاقة حركة المرور',            category: 'إعاقة حركة المرور',      status: 'pending',  location: 'حي الروضة، الرياض',         lat: 24.7200, lng: 46.6900 },
    { title: 'وقوف في منطقة ممنوعة',         category: 'وقوف مخالف',            status: 'resolved', location: 'حي الورود، الرياض',         lat: 24.7050, lng: 46.7100 }
];

var colors = { pending: '#E65100', review: '#1565C0', resolved: '#2E7D32', rejected: '#C62828' };
var labels = { pending: 'قيد الانتظار', review: 'قيد المراجعة', resolved: 'تم الحل', rejected: 'مرفوض' };

var map = L.map('map').setView([24.7136, 46.6753], 12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

var markers = reports.map(function (r) {
    var icon = L.divIcon({
        className: '',
        html: '<div style="width:16px;height:16px;border-radius:50%;background:' + colors[r.status] + ';border:2.5px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',
        iconSize: [16, 16],
        iconAnchor: [8, 8]
    });

    var popup = '<div style="direction:rtl;font-family:Tajawal,sans-serif;min-width:180px;">'
        + '<h4 style="font-size:0.92rem;margin-bottom:0.3rem;">' + r.title + '</h4>'
        + '<p style="font-size:0.8rem;color:#5a6b5e;margin-bottom:0.2rem;">🏷️ ' + r.category + '</p>'
        + '<p style="font-size:0.8rem;color:#5a6b5e;margin-bottom:0.3rem;">📍 ' + r.location + '</p>'
        + '<span style="font-size:0.75rem;font-weight:700;padding:0.15rem 0.6rem;border-radius:20px;background:' + colors[r.status] + '20;color:' + colors[r.status] + ';">' + labels[r.status] + '</span>'
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