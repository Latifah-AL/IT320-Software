document.addEventListener('DOMContentLoaded', function () {

    // Mobile menu
    document.getElementById('burger').addEventListener('click', function () {
        document.getElementById('mobileNav').classList.toggle('open');
    });

    // Load all reports
    var allReports    = getAllReports();
    var grid          = document.getElementById('reportsGrid');
    var countEl       = document.getElementById('resultsCount');
    var currentFilter = 'all';
    var currentSearch = '';

    grid.innerHTML = '';

    // Render reports
    function renderReports() {
        var filtered = allReports.filter(function (r) {
            var matchFilter = currentFilter === 'all' || r.status === currentFilter;
            var matchSearch = currentSearch === '' || r.id.toLowerCase().includes(currentSearch.toLowerCase());
            return matchFilter && matchSearch;
        });

        grid.innerHTML = '';

        if (filtered.length === 0) {
            grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;text-align:center;padding:3rem;"><i class="fa-solid fa-inbox" style="font-size:2.5rem;color:var(--border);display:block;margin-bottom:0.75rem;"></i><p style="color:var(--muted);">لا توجد بلاغات</p></div>';
            countEl.textContent = 'عرض 0 بلاغات';
            return;
        }

        countEl.textContent = 'عرض ' + filtered.length + ' بلاغات';

        filtered.forEach(function (r) {
            var imgHtml = r.image
                ? '<img src="' + r.image + '" alt="' + r.title + '"/>'
                : '<div class="img-placeholder"><i class="fa-solid fa-image"></i><span>لم يتم إرفاق صورة</span></div>';

            var card = document.createElement('div');
            card.className      = 'report-card';
            card.dataset.status = r.status;
            card.dataset.id     = r.id;

            card.innerHTML =
                '<div class="card-img">'
              +     imgHtml
              +     '<span class="card-status ' + r.status + '">' + r.statusLabel + '</span>'
              +     '<span class="card-category"><i class="fa-solid ' + r.categoryIcon + '"></i></span>'
              + '</div>'
              + '<div class="card-body">'
              +     '<div class="card-id">' + r.id + '</div>'
              +     '<h3>' + r.title + '</h3>'
              +     '<div class="card-meta">'
              +         '<span><i class="fa-solid fa-location-dot"></i> ' + r.location + '</span>'
              +         '<span><i class="fa-solid fa-calendar"></i> ' + r.date + '</span>'
              +     '</div>'
              + '</div>'
              + '<div class="card-actions">'
              +     '<a href="report-detail.html?id=' + r.id + '" class="btn-view"><i class="fa-solid fa-eye"></i> عرض</a>'
              +     '<a href="edit-report.html?id=' + r.id + '" class="btn-edit"><i class="fa-solid fa-pen"></i> تعديل</a>'
              +     '<button class="btn-delete" data-id="' + r.id + '"><i class="fa-solid fa-trash"></i> حذف</button>'
              + '</div>';

            grid.appendChild(card);
        });

        // Delete buttons — just hide the card
        grid.querySelectorAll('.btn-delete').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('confirmDeleteBtn').dataset.id = btn.dataset.id;
                document.getElementById('deleteModal').style.display  = 'flex';
            });
        });
    }

    renderReports();

    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            currentFilter = btn.dataset.filter;
            renderReports();
        });
    });

    // Search
    document.querySelector('.search-box button').addEventListener('click', function () {
        currentSearch = document.querySelector('.search-box input').value.trim();
        renderReports();
    });

    document.querySelector('.search-box input').addEventListener('keyup', function (e) {
        if (e.key === 'Enter') {
            currentSearch = this.value.trim();
            renderReports();
        }
    });

    // Delete modal — just hide the card, no actual delete
    document.getElementById('cancelDeleteBtn').addEventListener('click', function () {
        document.getElementById('deleteModal').style.display = 'none';
    });

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        var id = this.dataset.id;

        // Hide from allReports array
        allReports = allReports.filter(function (r) { return r.id !== id; });

        document.getElementById('deleteModal').style.display = 'none';
        renderReports();
    });

});