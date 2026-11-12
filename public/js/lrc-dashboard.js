function initializeLrcDashboard(source) {
    const $ = id => document.getElementById(id);
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
    const number = value => new Intl.NumberFormat('id-ID').format(value ?? 0);
    const charts = {};
    const colors = ['#176b87', '#e48b3a', '#5b7c99', '#57a773', '#b25c7e', '#8b6fb3', '#d2a72c', '#637083'];
    const percentageLabels = {
        id: 'lrcPercentageLabels',
        afterDatasetsDraw(chart) {
            if (!chart.isDatasetVisible(0)) return;
            const values = chart.data.datasets[0].data;
            const visible = index => chart.getDataVisibility(index) && Number(values[index]) > 0;
            const total = values.reduce((sum, value, index) => sum + (visible(index) ? Number(value) : 0), 0);
            if (!total) return;
            const { ctx, chartArea } = chart;
            const doughnut = chart.config.type === 'doughnut';
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            chart.getDatasetMeta(0).data.forEach((element, index) => {
                if (!visible(index)) return;
                const label = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(Number(values[index]) / total * 100) + '%';
                ctx.font = '600 12px Roboto, sans-serif';
                if (!doughnut && element.width < ctx.measureText(label).width + 10) {
                    ctx.font = '600 10px Roboto, sans-serif';
                }
                const width = ctx.measureText(label).width + 10;
                const height = 21;
                let { x, y } = doughnut ? element.tooltipPosition() : { x: element.x, y: (element.y + element.base) / 2 };
                // Short bars keep the label just above the bar, within the chart.
                if (!doughnut && Math.abs(element.base - element.y) < height + 6) y = element.y - height / 2 - 4;
                x = Math.max(chartArea.left + width / 2, Math.min(chartArea.right - width / 2, x));
                y = Math.max(chartArea.top + height / 2, Math.min(chartArea.bottom - height / 2, y));
                if (!Number.isFinite(x) || !Number.isFinite(y)) return;
                ctx.beginPath();
                ctx.roundRect(x - width / 2, y - height / 2, width, height, 4);
                ctx.fillStyle = 'rgba(255, 255, 255, 0.94)';
                ctx.fill();
                ctx.fillStyle = '#17324d';
                ctx.fillText(label, x, y);
            });
            ctx.restore();
        }
    };
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
            plugins: [percentageLabels],
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
    const educationStats = $('lrcEducationStats');
    const educationFilters = $('lrcEducationFilters');
    const gugusFilter = $('lrcGugusFilter');
    const programFilter = $('lrcProgramFilter');
    const roadmapFilter = $('lrcRoadmapFilter');
    const projectionFilter = $('lrcProjectionFilter');
    const jjaRoadmapFilter = $('lrcJjaRoadmapFilter');
    const jjaEligibleFilter = $('lrcJjaEligibleFilter');
    let state = { view: 'faculties', faculty: null, type: null };
    let previousFocus = null;

    function allTypes() {
        return state.faculty ? (source.facultyDetails[state.faculty]?.types || []) : [];
    }

    function chartTitle(key) {
        return document.querySelector(`[data-lrc-chart-card="${key}"]`)?.dataset.lrcTitle || key;
    }

    function isEducationDetail() {
        return state.view === 'chart' && state.chart === 'pendidikan';
    }

    function isRankDetail() {
        return state.view === 'chart' && state.chart === 'jja';
    }

    function hasDetailFilters() {
        return isEducationDetail() || isRankDetail();
    }

    function educationColumns() {
        if (!isEducationDetail()) return [];
        const label = source.charts.pendidikan.labels[state.index];
        return [
            ...(label === 'S2' ? [{ key: 'roadmapS3', label: 'Roadmap S3', description: 'Tahun rencana mulai S3' }] : []),
            ...(['S2', 'On Going S3'].includes(label) ? [{ key: 'proyeksiLulusS3', label: 'Proyeksi Lulus S3', description: 'Tahun perkiraan selesai S3' }] : []),
        ];
    }

    function columnValue(row, column) {
        const value = row[column.key];
        return column.date && value ? String(value).replace(/^(\d{4})-(\d{2})-(\d{2})$/, '$3-$2-$1') : value;
    }

    function lecturerTable(lecturers, columns = []) {
        return `<thead><tr><th>Nama dosen</th><th>Kode</th><th>Gugus binaan</th><th>Program</th>${columns.map(column => `<th>${column.label}</th>`).join('')}</tr></thead><tbody>${lecturers.map(row => `<tr><td class="lrc-name-cell">${escape(row.name)}</td><td>${escape(row.code || '—')}</td><td>${escape(row.gugus || '—')}</td><td>${escape(row.program || '—')}</td>${columns.map(column => `<td>${escape(columnValue(row, column) ?? '—')}</td>`).join('')}</tr>`).join('') || `<tr><td colspan="${4 + columns.length}">Tidak ada dosen yang sesuai.</td></tr>`}</tbody>`;
    }

    function yearOptions(lecturers, key) {
        const years = [...new Set(lecturers.map(row => row[key]).filter(year => year != null))].sort((a, b) => a - b);
        return '<option value="">Semua tahun</option>' + years.map(year => `<option value="${escape(year)}">${escape(year)}</option>`).join('')
            + (lecturers.some(row => row[key] == null) ? '<option value="empty">Belum diisi</option>' : '');
    }

    function categoryOptions(lecturers, key, label) {
        const values = [...new Set(lecturers.map(row => row[key]).filter(value => value != null && value !== ''))]
            .sort((a, b) => a.localeCompare(b, 'id-ID'));
        return `<option value="">Semua ${label}</option>` + values.map(value => `<option value="${escape(value)}">${escape(value)}</option>`).join('');
    }

    function matchesYear(row, key, value) {
        return value === '' || (value === 'empty' ? row[key] == null : String(row[key]) === value);
    }

    function yearSummary(label, description, total, entries) {
        const percent = count => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(total ? count / total * 100 : 0);
        return `<section class="lrc-year-summary" aria-label="Statistik ${escape(label)}"><h3>${escape(label)}</h3><p>${escape(description)} · Persentase dari ${number(total)} dosen pada hasil filter dan pencarian.</p><div class="lrc-year-cards">${entries.map(([year, count]) => `<article class="lrc-year-card${typeof year === 'number' ? '' : ' lrc-year-empty'}"><h4>${escape(year)}</h4><strong>${number(count)} <small>orang</small></strong><span>${percent(count)}%</span></article>`).join('') || '<p class="pd-context">Tidak ada dosen yang sesuai.</p>'}</div></section>`;
    }

    function renderEducationStats(lecturers, columns) {
        educationStats.innerHTML = columns.map(column => {
            const counts = new Map();
            lecturers.forEach(row => {
                const year = row[column.key] ?? null;
                counts.set(year, (counts.get(year) || 0) + 1);
            });
            const years = [...counts.keys()].filter(year => year !== null).sort((a, b) => a - b);
            if (counts.has(null)) years.push(null);
            return yearSummary(column.label, column.description, lecturers.length, years.map(year => [year ?? 'Belum diisi', counts.get(year)]));
        }).join('');
    }

    function renderRankStats(lecturers) {
        const progression = source.rankProgression[state.index];
        if (!progression.next) {
            educationStats.innerHTML = '<section class="lrc-year-summary"><h3>Jenjang JJA tertinggi</h3><p>Guru Besar (GB) adalah jenjang tertinggi pada urutan TP → AA → L → LK → GB.</p></section>';
            return;
        }
        const counts = new Map();
        lecturers.forEach(row => {
            const bucket = !row.roadmapKenaikanJja ? 'Roadmap belum diisi'
                : row.roadmapJjaRank !== progression.next ? 'Roadmap berbeda'
                : row.eligibleJjaYear ?? 'Tanggal belum diisi';
            counts.set(bucket, (counts.get(bucket) || 0) + 1);
        });
        const years = [...counts.keys()].filter(year => typeof year === 'number').sort((a, b) => a - b);
        const other = ['Tanggal belum diisi', 'Roadmap belum diisi', 'Roadmap berbeda'].filter(key => counts.has(key));
        educationStats.innerHTML = yearSummary(`Eligible kenaikan ke ${progression.nextLabel}`,
            'Tahun eligible untuk roadmap ke jenjang berikutnya. Roadmap berbeda ditampilkan terpisah',
            lecturers.length, [...years, ...other].map(key => [key, counts.get(key)]));
    }

    function renderDetail() {
        const query = search.value.trim().toLocaleLowerCase('id-ID');
        const matches = text => String(text ?? '').toLocaleLowerCase('id-ID').includes(query);
        const isLecturers = state.view === 'lecturers' || state.view === 'chart';
        const educationFields = educationColumns();
        const columns = isRankDetail() ? [
            { key: 'roadmapKenaikanJja', label: 'Roadmap kenaikan JJA' },
            { key: 'eligibleKenaikanJja', label: 'Eligible kenaikan JJA', date: true },
        ] : educationFields;
        const lecturerMatches = row => matches(`${row.name} ${row.code} ${row.gugus} ${row.program} ${columns.map(column => columnValue(row, column) ?? '').join(' ')}`);
        educationStats.hidden = !educationFields.length && !isRankDetail();
        educationFilters.hidden = !hasDetailFilters();
        $('lrcRoadmapFilterField').hidden = !educationFields.some(column => column.key === 'roadmapS3');
        $('lrcProjectionFilterField').hidden = !educationFields.some(column => column.key === 'proyeksiLulusS3');
        $('lrcJjaRoadmapFilterField').hidden = !isRankDetail();
        $('lrcJjaEligibleFilterField').hidden = !isRankDetail();
        // A chart category has a single level, so it has nothing to go back to.
        $('lrcDetailBack').hidden = state.view === 'faculties' || state.view === 'chart';
        $('lrcDetailEyebrow').textContent = state.view === 'chart' ? chartTitle(state.chart).toLocaleUpperCase('id-ID') : 'KOMPOSISI FAKULTAS';
        list.hidden = isLecturers;
        tableRegion.hidden = !isLecturers;
        if (state.view === 'chart') {
            const chart = source.charts[state.chart];
            const lecturers = (source.chartDetails[state.chart]?.[state.index] || []).filter(row => lecturerMatches(row)
                && (!hasDetailFilters() || ((!gugusFilter.value || row.gugus === gugusFilter.value)
                    && (!programFilter.value || row.program === programFilter.value)))
                && educationFields.every(column => matchesYear(row, column.key, column.key === 'roadmapS3' ? roadmapFilter.value : projectionFilter.value))
                && (!isRankDetail() || ((!jjaRoadmapFilter.value || row.roadmapKenaikanJja === jjaRoadmapFilter.value)
                    && matchesYear(row, 'eligibleJjaYear', jjaEligibleFilter.value))));
            $('lrcDetailTitle').textContent = chart.labels[state.index];
            $('lrcDetailCaption').textContent = `${chartTitle(state.chart)} · ${number(chart.data[state.index])} dosen pada filter terpilih.`;
            if (isRankDetail()) renderRankStats(lecturers);
            else if (educationFields.length) renderEducationStats(lecturers, educationFields);
            table.innerHTML = lecturerTable(lecturers, columns);
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
        gugusFilter.value = programFilter.value = roadmapFilter.value = projectionFilter.value = jjaRoadmapFilter.value = jjaEligibleFilter.value = '';
        if (hasDetailFilters()) {
            const lecturers = source.chartDetails[state.chart]?.[state.index] || [];
            gugusFilter.innerHTML = categoryOptions(lecturers, 'gugus', 'gugus binaan');
            programFilter.innerHTML = categoryOptions(lecturers, 'program', 'program');
            roadmapFilter.innerHTML = yearOptions(lecturers, 'roadmapS3');
            projectionFilter.innerHTML = yearOptions(lecturers, 'proyeksiLulusS3');
            jjaRoadmapFilter.innerHTML = categoryOptions(lecturers, 'roadmapKenaikanJja', 'roadmap');
            jjaEligibleFilter.innerHTML = yearOptions(lecturers, 'eligibleJjaYear');
        }
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
    gugusFilter.addEventListener('change', renderDetail);
    programFilter.addEventListener('change', renderDetail);
    roadmapFilter.addEventListener('change', renderDetail);
    projectionFilter.addEventListener('change', renderDetail);
    jjaRoadmapFilter.addEventListener('change', renderDetail);
    jjaEligibleFilter.addEventListener('change', renderDetail);
    $('lrcEducationReset').addEventListener('click', () => {
        gugusFilter.value = programFilter.value = roadmapFilter.value = projectionFilter.value = jjaRoadmapFilter.value = jjaEligibleFilter.value = search.value = '';
        renderDetail();
    });
    $('lrcDetailClose').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => { document.body.classList.remove('pd-modal-open'); previousFocus?.focus({ preventScroll: true }); });
    dialog.addEventListener('click', event => { const rect = dialog.getBoundingClientRect(); if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close(); });
}

if (window.lrcDashboard && document.getElementById('lrcDetail')) initializeLrcDashboard(window.lrcDashboard);
