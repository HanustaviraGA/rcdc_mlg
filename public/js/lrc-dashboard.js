function initializeLrcDashboard(source) {
    const $ = id => document.getElementById(id);
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
    const number = value => new Intl.NumberFormat('id-ID').format(value ?? 0);
    const charts = {};
    const colors = ['#176b87', '#e48b3a', '#5b7c99', '#57a773', '#b25c7e', '#8b6fb3', '#d2a72c', '#637083'];
    const chartOptions = (type, labels) => ({
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 450 },
        plugins: {
            legend: { display: type === 'doughnut', position: 'bottom', labels: { boxWidth: 12, padding: 14, font: { size: 11 } } },
            tooltip: { callbacks: { label: context => ` ${context.label}: ${number(context.raw)}` } }
        },
        scales: type === 'doughnut' ? {} : { x: { beginAtZero: true, ticks: { precision: 0 } }, y: { beginAtZero: true, ticks: { precision: 0 } } }
    });

    function draw(key, canvasId, type = 'bar', extra = {}) {
        const canvas = $(canvasId);
        if (!canvas || !source.charts[key]) return;
        const chart = source.charts[key];
        charts[key] = new Chart(canvas, {
            type,
            data: { labels: chart.labels, datasets: [{ data: chart.data, backgroundColor: colors, borderColor: colors, borderWidth: 1, borderRadius: type === 'bar' ? 5 : 0 }] },
            options: { ...chartOptions(type, chart.labels), ...extra }
        });
    }

    // A bar or slice opens the lecturers behind it; the pointer shows it can be clicked.
    const pointer = (event, elements) => { if (event.native?.target) event.native.target.style.cursor = elements.length ? 'pointer' : 'default'; };
    const clickable = key => ({ onHover: pointer, onClick: (event, elements) => { if (elements.length) openCategory(key, elements[0].index); } });

    draw('pendidikan', 'lrc-chart-pendidikan', 'bar', { indexAxis: 'x', plugins: { legend: { display: false } }, ...clickable('pendidikan') });
    draw('jja', 'lrc-chart-jja', 'bar', { plugins: { legend: { display: false } }, ...clickable('jja') });
    draw('usia', 'lrc-chart-usia', 'bar', { plugins: { legend: { display: false } }, ...clickable('usia') });
    draw('serdos', 'lrc-chart-serdos', 'doughnut', clickable('serdos'));
    draw('nuptk', 'lrc-chart-nuptk', 'doughnut', clickable('nuptk'));
    draw('tipeDosen', 'lrc-chart-tipe-dosen', 'doughnut', clickable('tipeDosen'));
    draw('agama', 'lrc-chart-agama', 'bar', { plugins: { legend: { display: false } }, ...clickable('agama') });
    draw('fakultas', 'lrc-chart-fakultas', 'bar', { plugins: { legend: { display: false } }, onHover: pointer, onClick: (event, elements) => { if (elements.length) openFaculty(source.charts.fakultas.labels[elements[0].index]); } });

    const dialog = $('lrcDetail');
    const search = $('lrcDetailSearch');
    const list = $('lrcFacultyList');
    const tableRegion = $('lrcLecturerRegion');
    const table = $('lrcLecturerTable');
    const back = $('lrcDetailBack');
    let state = { view: 'faculties', faculty: null, type: null };
    let previousFocus = null;

    function allTypes() {
        return state.faculty ? (source.facultyDetails[state.faculty]?.types || []) : [];
    }

    function chartTitle(key) {
        return document.querySelector(`[data-lrc-chart-card="${key}"]`)?.dataset.lrcTitle || key;
    }

    function lecturerTable(lecturers) {
        return `<thead><tr><th>Nama dosen</th><th>Kode</th><th>Gugus binaan</th><th>Program</th></tr></thead><tbody>${lecturers.map(row => `<tr><td class="lrc-name-cell">${escape(row.name)}</td><td>${escape(row.code || '—')}</td><td>${escape(row.gugus || '—')}</td><td>${escape(row.program || '—')}</td></tr>`).join('') || `<tr><td colspan="4">Tidak ada dosen yang sesuai.</td></tr>`}</tbody>`;
    }

    function renderDetail() {
        const query = search.value.trim().toLocaleLowerCase('id-ID');
        const matches = text => String(text ?? '').toLocaleLowerCase('id-ID').includes(query);
        const isLecturers = state.view === 'lecturers' || state.view === 'chart';
        const lecturerMatches = row => matches(`${row.name} ${row.code} ${row.gugus} ${row.program}`);
        // A chart category has a single level, so it has nothing to go back to.
        $('lrcDetailBack').hidden = state.view === 'faculties' || state.view === 'chart';
        $('lrcDetailEyebrow').textContent = state.view === 'chart' ? chartTitle(state.chart).toLocaleUpperCase('id-ID') : 'KOMPOSISI FAKULTAS';
        list.hidden = isLecturers;
        tableRegion.hidden = !isLecturers;
        if (state.view === 'chart') {
            const chart = source.charts[state.chart];
            const lecturers = (source.chartDetails[state.chart]?.[state.index] || []).filter(lecturerMatches);
            $('lrcDetailTitle').textContent = chart.labels[state.index];
            $('lrcDetailCaption').textContent = `${chartTitle(state.chart)} · ${number(chart.data[state.index])} dosen pada filter terpilih.`;
            table.innerHTML = lecturerTable(lecturers);
            $('lrcDetailMeta').textContent = `${lecturers.length} dosen ditampilkan`;
        } else if (state.view === 'faculties') {
            $('lrcDetailTitle').textContent = 'Komposisi Fakultas';
            $('lrcDetailCaption').textContent = 'Pilih fakultas untuk melihat tipe faculty detail.';
            const faculties = Object.values(source.facultyDetails).filter(row => matches(row.label));
            list.innerHTML = faculties.map(row => `<button type="button" class="lrc-detail-option" data-lrc-open-faculty="${escape(row.label)}"><span>${escape(row.label)}</span><span>${number(row.count)} dosen <i class="bi bi-chevron-right" aria-hidden="true"></i></span></button>`).join('') || '<p class="pd-context">Tidak ada fakultas yang sesuai.</p>';
            $('lrcDetailMeta').textContent = `${faculties.length} fakultas ditampilkan`;
        } else if (state.view === 'types') {
            $('lrcDetailTitle').textContent = `Detail ${state.faculty}`;
            $('lrcDetailCaption').textContent = 'Pilih tipe faculty untuk melihat daftar nama dosen.';
            const types = allTypes().filter(row => matches(row.label));
            list.innerHTML = types.map(row => `<button type="button" class="lrc-detail-option" data-lrc-open-type="${escape(row.label)}"><span>${escape(row.label)}</span><span>${number(row.count)} dosen <i class="bi bi-chevron-right" aria-hidden="true"></i></span></button>`).join('') || '<p class="pd-context">Tidak ada tipe faculty yang sesuai.</p>';
            $('lrcDetailMeta').textContent = `${types.length} tipe faculty ditampilkan · ${number(source.facultyDetails[state.faculty]?.count || 0)} dosen`;
        } else {
            const type = allTypes().find(row => row.label === state.type);
            const lecturers = (type?.lecturers || []).filter(lecturerMatches);
            $('lrcDetailTitle').textContent = state.type;
            $('lrcDetailCaption').textContent = `${state.faculty} · Daftar dosen dengan tipe faculty ini.`;
            table.innerHTML = lecturerTable(lecturers);
            $('lrcDetailMeta').textContent = `${lecturers.length} dosen ditampilkan`;
        }
    }

    function openDialog(next) {
        // Navigating inside an open dialog keeps the focus to restore on close.
        if (!dialog.open) previousFocus = document.activeElement;
        state = next;
        search.value = '';
        renderDetail();
        if (!dialog.open) dialog.showModal();
        document.body.classList.add('pd-modal-open');
        search.focus();
    }

    function openFaculty(faculty) {
        openDialog({ view: 'types', faculty, type: null });
    }

    function openCategory(chart, index) {
        if (!source.charts[chart] || !source.chartDetails?.[chart]?.[index]) return;
        openDialog({ view: 'chart', chart, index });
    }

    function openType(type) {
        state = { ...state, view: 'lecturers', type };
        search.value = '';
        renderDetail();
        search.focus();
    }

    document.querySelectorAll('[data-lrc-faculty]').forEach(button => button.addEventListener('click', () => openFaculty(button.dataset.lrcFaculty)));
    document.querySelectorAll('[data-lrc-chart]').forEach(button => button.addEventListener('click', () => openCategory(button.dataset.lrcChart, Number(button.dataset.lrcIndex))));
    $('lrc-chart-fakultas')?.addEventListener('click', event => { if (event.target === event.currentTarget) return; });
    list.addEventListener('click', event => {
        const faculty = event.target.closest('[data-lrc-open-faculty]');
        const type = event.target.closest('[data-lrc-open-type]');
        if (faculty) openFaculty(faculty.dataset.lrcOpenFaculty);
        if (type) openType(type.dataset.lrcOpenType);
    });
    back.addEventListener('click', () => {
        state = state.view === 'lecturers' ? { view: 'types', faculty: state.faculty, type: null } : { view: 'faculties', faculty: null, type: null };
        search.value = '';
        renderDetail();
    });
    search.addEventListener('input', renderDetail);
    $('lrcDetailClose').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => { document.body.classList.remove('pd-modal-open'); previousFocus?.focus({ preventScroll: true }); });
    dialog.addEventListener('click', event => { const rect = dialog.getBoundingClientRect(); if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close(); });
}

if (window.lrcDashboard && document.getElementById('lrcDetail')) initializeLrcDashboard(window.lrcDashboard);
