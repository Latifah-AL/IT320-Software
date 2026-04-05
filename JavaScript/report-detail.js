document.addEventListener('DOMContentLoaded', function () {

    var params = new URLSearchParams(window.location.search);
    var id = params.get('id');
    var report = getReport(id);

    if (!report) {
        window.location.href = 'my-reports.html';
        return;
    }

    document.title = 'عينك — ' + report.title;

    document.getElementById('reportIdLabel').textContent = report.id;

    var imgArea = document.getElementById('detailImg');
    if (report.image) {
        imgArea.innerHTML = '<img src="' + report.image + '" alt="' + report.title + '" style="width:100%;height:100%;object-fit:cover;"/>';
    } else {
        imgArea.innerHTML = '<div class="img-placeholder-detail"><i class="fa-solid fa-image"></i><span>لم يتم إرفاق صورة</span></div>';
    }

    var statusEl = document.getElementById('detailStatus');
    statusEl.textContent = report.statusLabel;
    statusEl.className = 'status-badge ' + report.status;

    var steps = ['تم الرفع', 'قيد الانتظار', 'قيد المراجعة', 'تم الحل'];
    var statuses = ['done', 'pending', 'review', 'resolved'];
    var current = statuses.indexOf(report.status);
    if (current === -1) current = 1;

    var timelineEl = document.getElementById('statusTimeline');
    timelineEl.innerHTML = '';

    steps.forEach(function (step, i) {
        var isDone = i < current;
        var isActive = i === current;
        var stepClass = isDone ? 'done' : (isActive ? 'active' : '');

        timelineEl.innerHTML +=
            '<div class="timeline-step ' + stepClass + '">'
          +     '<div class="step-dot"></div>'
          +     '<div class="step-info">'
          +         '<span class="step-title">' + step + '</span>'
          +         '<span class="step-date">' + (isDone || isActive ? report.date : '—') + '</span>'
          +     '</div>'
          + '</div>';

        if (i < steps.length - 1) {
            timelineEl.innerHTML += '<div class="timeline-line ' + (isDone ? 'done' : '') + '"></div>';
        }
    });

    document.getElementById('categoryIcon').className    = 'fa-solid ' + report.categoryIcon;
    document.getElementById('detailTitle').textContent   = report.title;
    document.getElementById('categoryLabel').textContent = report.category;

    document.getElementById('infoLocation').textContent = report.location;
    document.getElementById('infoDate').textContent     = report.date;
    document.getElementById('infoDatetime').textContent = report.datetime.replace('T', ' ');
    document.getElementById('infoId').textContent       = report.id;

    document.getElementById('detailDesc').textContent = report.description;

    document.getElementById('burger').addEventListener('click', function () {
        document.getElementById('mobileNav').classList.toggle('open');
    });

});