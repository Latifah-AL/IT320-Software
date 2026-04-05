var reportsData = {
    'RPT-001': {
        id:          'RPT-001',
        title:       'وقوف مخالف أمام مدخل',
        category:    'وقوف مخالف',
        categoryIcon:'fa-car-burst',
        categoryValue:'parking',
        status:      'pending',
        statusLabel: 'قيد الانتظار',
        location:    'حي النزهة، الرياض',
        date:        '12 يناير 2025',
        datetime:    '2025-01-12T10:30',
        description: 'سيارة واقفة بشكل مخالف أمام مدخل المبنى وتعيق حركة الدخول والخروج للسكان والزوار، وقد استمر هذا الوضع لأكثر من ساعة.',
        image:       '../Images/re1.jpeg'
    },
    'RPT-002': {
        id:          'RPT-002',
        title:       'إشغال رصيف المشاة',
        category:    'إشغال الأرصفة',
        categoryIcon:'fa-person-walking',
        categoryValue:'sidewalk',
        status:      'resolved',
        statusLabel: 'تم الحل',
        location:    'حي العليا، الرياض',
        date:        '8 يناير 2025',
        datetime:    '2025-01-08T14:00',
        description: 'سيارة تسد رصيف المشاة بالكامل مما يضطر المشاة للنزول إلى الشارع وتعريض أنفسهم للخطر.',
        image:       '../Images/re2.jpeg'
    },
    'RPT-003': {
        id:          'RPT-003',
        title:       'سد مخرج طوارئ',
        category:    'سد مخارج الطوارئ',
        categoryIcon:'fa-truck-medical',
        categoryValue:'emergency',
        status:      'review',
        statusLabel: 'قيد المراجعة',
        location:    'حي الملز، الرياض',
        date:        '5 يناير 2025',
        datetime:    '2025-01-05T09:15',
        description: 'مركبة تسد مخرج الطوارئ الرئيسي للمبنى مما يشكل خطراً داهماً في حالات الطوارئ والحرائق.',
        image:       '../Images/re3.jpeg'
    },
    'RPT-004': {
        id:          'RPT-004',
        title:       'احتلال موقف ذوي الاحتياجات',
        category:    'مواقف ذوي الاحتياجات',
        categoryIcon:'fa-wheelchair',
        categoryValue:'disabled',
        status:      'rejected',
        statusLabel: 'مرفوض',
        location:    'حي السليمانية، الرياض',
        date:        '1 يناير 2025',
        datetime:    '2025-01-01T11:45',
        description: 'سيارة بدون تصريح لذوي الاحتياجات تحتل الموقف المخصص لهم أمام المركز التجاري.',
        image:       '../Images/re4.jpeg'
    },
    'RPT-005': {
        id:          'RPT-005',
        title:       'إعاقة حركة المرور',
        category:    'إعاقة حركة المرور',
        categoryIcon:'fa-road-barrier',
        categoryValue:'traffic',
        status:      'pending',
        statusLabel: 'قيد الانتظار',
        location:    'حي الروضة، الرياض',
        date:        '28 ديسمبر 2024',
        datetime:    '2024-12-28T16:30',
        description: 'مركبة متوقفة في منتصف الشارع تعيق حركة السير وتسبب ازدحاماً مرورياً.',
        image:       null
    },
    'RPT-006': {
        id:          'RPT-006',
        title:       'وقوف في منطقة ممنوعة',
        category:    'وقوف مخالف',
        categoryIcon:'fa-car-burst',
        categoryValue:'parking',
        status:      'resolved',
        statusLabel: 'تم الحل',
        location:    'حي الورود، الرياض',
        date:        '20 ديسمبر 2024',
        datetime:    '2024-12-20T08:00',
        description: 'سيارة واقفة في منطقة ممنوعة الوقوف بالقرب من تقاطع مروري مما يعيق الرؤية.',
        image:       null
    }
};

// Category map
var categoryMap = {
    'parking':   { label: 'وقوف مخالف',          icon: 'fa-car-burst' },
    'sidewalk':  { label: 'إشغال الأرصفة',        icon: 'fa-person-walking' },
    'emergency': { label: 'سد مخارج الطوارئ',     icon: 'fa-truck-medical' },
    'disabled':  { label: 'مواقف ذوي الاحتياجات', icon: 'fa-wheelchair' },
    'traffic':   { label: 'إعاقة حركة المرور',    icon: 'fa-road-barrier' },
    'other':     { label: 'أخرى',                 icon: 'fa-triangle-exclamation' }
};

// Status map
var statusMap = {
    'pending':  'قيد الانتظار',
    'review':   'قيد المراجعة',
    'resolved': 'تم الحل',
    'rejected': 'مرفوض'
};

// Get all reports (dummy + localStorage)
function getAllReports() {
    var stored = JSON.parse(localStorage.getItem('aynek_reports') || '[]');
    var dummy  = Object.values(reportsData);
    return dummy.concat(stored);
}

// Get single report by ID
function getReport(id) {
    if (reportsData[id]) return reportsData[id];
    var stored = JSON.parse(localStorage.getItem('aynek_reports') || '[]');
    return stored.find(function (r) { return r.id === id; }) || null;
}

// Save new report to localStorage
function saveReport(report) {
    var stored = JSON.parse(localStorage.getItem('aynek_reports') || '[]');
    stored.push(report);
    localStorage.setItem('aynek_reports', JSON.stringify(stored));
}

// Update existing report in localStorage
function updateReport(id, updated) {
    var stored = JSON.parse(localStorage.getItem('aynek_reports') || '[]');
    stored = stored.map(function (r) { return r.id === id ? updated : r; });
    localStorage.setItem('aynek_reports', JSON.stringify(stored));
}

// Delete report from localStorage
function deleteReport(id) {
    var stored = JSON.parse(localStorage.getItem('aynek_reports') || '[]');
    stored = stored.filter(function (r) { return r.id !== id; });
    localStorage.setItem('aynek_reports', JSON.stringify(stored));
}

// Generate new report ID
function generateId() {
    var stored = JSON.parse(localStorage.getItem('aynek_reports') || '[]');
    var num    = 7 + stored.length;
    return 'RPT-00' + num;
}

// Format date
function formatDate(datetime) {
    if (!datetime) return '—';
    var d = new Date(datetime);
    return d.toLocaleDateString('ar-SA', { year: 'numeric', month: 'long', day: 'numeric' });
}