const indicators = {
    fm: { label: 'Publication FM', icon: 'bi-journal-text', unit: 'bobot' },
    mhs: { label: 'Publikasi Mahasiswa', icon: 'bi-mortarboard', unit: 'judul' },
    citation: { label: 'New Citation', icon: 'bi-quote', message: 'Riwayat sitasi dan data pembanding belum tersedia untuk periode ini.' },
    research: { label: 'Research Program', icon: 'bi-lightbulb', message: 'Rekap capaian dan target Research Program belum ditetapkan. Proyek penelitian dapat ditelusuri melalui Research Gallery.' },
    ip: { label: '# of New IP', icon: 'bi-patch-check', message: 'Data kekayaan intelektual beserta tanggal dan status pengakuannya belum tersedia.' },
    ntr: { label: 'Other Revenue Stream (NTR)', icon: 'bi-wallet2', message: 'Rekap pendapatan dan target NTR belum tersedia untuk periode ini.' },
    vrtt: { label: 'Alokasi Budget VRTT', icon: 'bi-cash-stack', message: 'Pagu dan realisasi Budget VRTT memerlukan pencatatan manual. Data anggaran belum tersedia untuk periode ini.' },
};
const numberFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
const shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
const hasNumber = value => value !== null && value !== undefined && value !== '' && Number.isFinite(Number(value));
const format = value => hasNumber(value) ? numberFormat.format(Number(value)) : '—';
const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
const periodLabel = snapshot => snapshot ? `${shortMonths[snapshot.month - 1]} ${snapshot.year}` : 'Belum tersedia';

export function selectSnapshot(snapshots, year, kind, quarter = 'all') {
    const cutoff = /^Q[1-4]$/.test(quarter) ? Number(quarter.slice(1)) * 3 : 12;
    return snapshots.filter(row => String(row.year) === String(year) && row.month >= 1 && row.month <= cutoff && row[kind])
        .sort((a, b) => b.month - a.month)[0] ?? null;
}

export function selectedSources(source, state, kind) {
    return state.years.map(year => {
        const snapshot = selectSnapshot(source.snapshots, year, kind, state.quarter);
        let data = snapshot?.[kind] ?? null;
        if (data && state.program) {
            const card = data.cards.find(row => row.program === state.program);
            data = card ? { ...data, ...card, cards: [card] } : null;
        }
        return { year, snapshot, data };
    });
}

export function totalAvailable(selections, field) {
    const values = selections.map(row => row.data?.[field]).filter(hasNumber);
    return values.length ? values.reduce((sum, value) => sum + Number(value), 0) : null;
}

export function csvCell(value) {
    let text = String(value ?? '');
    if (typeof value === 'string' && /^[\s]*[=+@\-\t\r]/.test(text)) text = `'${text}`;
    return `"${text.replace(/"/g, '""')}"`;
}

function initialize(source) {
    const $ = id => document.getElementById(id);
    const params = new URLSearchParams(window.location.search);
    const requestedYears = [...params.getAll('years[]'), ...(params.get('year')?.split(',') ?? [])].filter(year => source.years.includes(year));
    const state = {
        years: requestedYears.length ? [...new Set(requestedYears)].sort() : [source.defaultYear],
        indicator: Object.hasOwn(indicators, params.get('indicator')) ? params.get('indicator') : 'fm',
        quarter: /^Q[1-4]$/.test(params.get('quarter')) ? params.get('quarter') : 'all',
        program: '',
    };
    const availablePrograms = [...new Set(source.snapshots.flatMap(row => ['fm', 'mhs'].flatMap(kind => row[kind]?.cards.map(card => card.program) ?? [])))].sort();
    $('performanceProgram').innerHTML += availablePrograms.map(code => `<option value="${escape(code)}">${escape(code)} · ${escape(source.programs[code] ?? code)}</option>`).join('');
    state.program = availablePrograms.includes(params.get('program')) ? params.get('program') : '';
    let renderedCards = [], detail = null, previousFocus = null;

    const pair = (title, icon, target, actual, foot, muted = false) => `
        <article class="pd-summary ${muted ? 'is-muted' : ''}">
            <div class="pd-summary-head"><h3>${escape(title)}</h3><i class="bi ${icon} pd-summary-icon" aria-hidden="true"></i></div>
            <div class="pd-summary-values"><div><span>Target tahunan</span><strong>${format(target)}</strong></div><div class="pd-realized"><span>Realisasi</span><strong>${format(actual)}</strong></div></div>
            <p class="pd-summary-foot">${escape(foot)}</p>
        </article>`;

    function renderSummary() {
        const publication = ['fm', 'mhs'].includes(state.indicator);
        if (!publication) {
            const labels = state.indicator === 'vrtt' ? ['Alokasi Budget VRTT', 'Target Budget VRTT', 'Realisasi Budget VRTT'] : [indicators[state.indicator].label, 'Capaian FM', 'Capaian mahasiswa'];
            $('performanceSummary').innerHTML = labels.map(label => pair(label, indicators[state.indicator].icon, null, null, 'Data belum tersedia', true)).join('') + percentCard(null, 'Capaian', 'Menunggu data indikator');
            return;
        }
        const fm = selectedSources(source, state, 'fm'), students = selectedSources(source, state, 'mhs');
        const current = fm[0]?.data;
        const percent = state.years.length === 1 && current?.facultyTotal > 0 ? 100 * current.scopusFaculty / current.facultyTotal : null;
        $('performanceSummary').innerHTML = pair('Milestone', 'bi-flag', null, null, 'Target dan sumber milestone belum ditetapkan', true)
            + pair('Publikasi Scopus FM', 'bi-journal-text', totalAvailable(fm, 'target'), totalAvailable(fm, 'actual'), 'Bobot · rekap publikasi FM')
            + pair('Publikasi Scopus MHS', 'bi-mortarboard', totalAvailable(students, 'target'), totalAvailable(students, 'actual'), 'Judul · rekap TITLE / LIST')
            + percentCard(percent, 'FM memiliki Scopus', state.years.length > 1 ? 'Persentase tersedia per tahun' : current ? `${format(current.scopusFaculty)} dari ${format(current.facultyTotal)} dosen dalam data periode ini` : 'Data dosen belum tersedia');
    }

    function percentCard(percent, title, foot) {
        return `<article class="pd-summary ${percent === null ? 'is-muted' : ''}"><div class="pd-summary-head"><h3>${escape(title)}</h3><i class="bi bi-people pd-summary-icon" aria-hidden="true"></i></div><div class="pd-summary-percent">${format(percent)}${percent === null ? '' : '<small> %</small>'}</div><p class="pd-summary-foot">${escape(foot)}</p></article>`;
    }

    function renderCard({ card, snapshot, kind }, index) {
        const target = hasNumber(card.target) ? Number(card.target) : null;
        const actual = Number(card.actual), max = Math.max(target ?? 0, actual, 1);
        const height = value => value > 0 ? Math.max(3, value / max * 124) : 0;
        const gap = target === null ? null : actual - target;
        const tone = card.score === null ? '' : card.score >= 4 ? 'is-good' : card.score >= 3 ? 'is-warning' : 'is-low';
        const percent = card.facultyTotal > 0 ? 100 * card.scopusFaculty / card.facultyTotal : null;
        const breakdown = kind === 'fm' ? `<div class="pd-faculty-stat"><div><strong>${format(percent)}${percent === null ? '' : '%'}</strong> FM memiliki Scopus<small>${format(card.scopusFaculty)} dari ${format(card.facultyTotal)} dosen pada data periode ini</small></div><i class="bi bi-people" aria-hidden="true"></i></div>
            <div class="pd-publication-total"><span>Paper Scopus</span><strong>${format(card.weight)} bobot · ${format(card.titles)} judul</strong></div>
            <dl class="pd-breakdown">${card.breakdown.map(row => `<div><dt>${escape(row.label)}</dt><dd>${format(row.weight)} bobot <span> / ${format(row.titles)} judul</span></dd></div>`).join('')}</dl>`
            : `<div class="pd-faculty-stat"><div><strong>${format(card.titles)}</strong> judul publikasi Scopus<small>Rekap berdasarkan TITLE / LIST</small></div><i class="bi bi-mortarboard" aria-hidden="true"></i></div><div class="pd-publication-total"><span>Penulis mahasiswa</span><strong>${format(card.authors)} nama unik</strong></div>`;
        return `<article class="pd-department" data-program="${escape(card.program)}" data-year="${snapshot.year}" data-kind="${kind}">
            <div class="pd-department-head"><div class="pd-program-name"><h3>${escape(card.program)} <span>· ${snapshot.year}</span></h3><p title="${escape(card.label)}">${escape(card.label)}</p></div><span class="pd-score ${tone}">Skor <strong>${format(card.score)}</strong></span></div>
            <div class="pd-chart" role="img" aria-label="Target tahunan ${format(target)} ${indicators[kind].unit}; realisasi kumulatif ${format(actual)} ${indicators[kind].unit}"><div class="pd-gap ${gap === null ? '' : gap >= 0 ? 'is-good' : 'is-low'}">GAP<strong>${gap === null ? '—' : `${gap > 0 ? '+' : ''}${format(gap)}`}</strong></div><div class="pd-bar-column"><span class="pd-bar-value">${format(target)}</span><div class="pd-chart-bar" style="height:${height(target)}px"></div></div><div class="pd-bar-column"><span class="pd-bar-value">${format(actual)}</span><div class="pd-chart-bar is-actual" style="height:${height(actual)}px"></div></div></div>
            <div class="pd-chart-labels"><span>Target tahunan</span><span>Realisasi</span></div><p class="pd-card-period">${indicators[kind].unit} · s.d. ${periodLabel(snapshot)}</p>
            <div class="pd-card-insights">${breakdown}</div><div class="pd-department-footer"><span>${kind === 'fm' ? `${format(card.facultyTotal)} dosen` : `${format(card.students.length)} entri publikasi`}</span><button class="pd-button pd-detail-button" type="button" data-detail="${index}">Detail <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button></div>
        </article>`;
    }

    function render() {
        $('performanceIndicator').value = state.indicator;
        $('performanceQuarter').value = state.quarter;
        $('performanceProgram').value = state.program;
        document.querySelectorAll('#performanceYears input').forEach(input => { input.checked = state.years.includes(input.value); });
        $('performanceYearLabel').textContent = state.years.join(', ');
        $('performanceSummaryPeriod').textContent = `${state.years.join(', ')} · ${state.quarter === 'all' ? 'Periode terbaru' : `Kumulatif ${state.quarter}`}`;
        $('performanceIndicatorLabel').textContent = indicators[state.indicator].label;
        const selections = selectedSources(source, state, state.indicator);
        renderedCards = selections.flatMap(({ snapshot, data }) => data ? data.cards.map(card => ({ card, snapshot, kind: state.indicator })) : []);
        $('performanceDepartments').innerHTML = renderedCards.map(renderCard).join('');
        $('performanceProgramCount').textContent = format(new Set(renderedCards.map(row => row.card.program)).size);
        const periods = [...new Set(selections.filter(row => row.data).map(row => periodLabel(row.snapshot)))];
        $('performanceCutoff').textContent = periods.length > 1 ? `${periods.length} periode` : periods[0] ?? 'Belum tersedia';
        const updated = selections.map(row => row.data?.updatedAt).filter(Boolean).sort().at(-1);
        const date = updated ? new Date(updated.replace(' ', 'T')) : null;
        $('performanceUpdated').textContent = date && !Number.isNaN(date.getTime()) ? `Diperbarui ${date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}` : 'Tanggal pembaruan belum tersedia';
        $('performanceUpdated').parentElement.classList.toggle('is-pending', !updated);
        renderSummary();
        const missing = selections.filter(row => !row.data).map(row => row.year);
        const publication = ['fm', 'mhs'].includes(state.indicator);
        const partial = state.quarter !== 'all' && selections.some(row => row.data && row.snapshot.month < Number(state.quarter.slice(1)) * 3);
        $('performanceContext').textContent = publication
            ? `Target yang tersedia adalah target tahunan. Realisasi bersifat kumulatif, mengikuti snapshot terakhir dalam batas periode.${partial ? ' Data belum mencapai akhir kuartal; bulan sumber ditampilkan pada setiap grafik.' : ''}${state.years.length > 1 ? ' Ringkasan menjumlahkan tahun yang tersedia; persentase ditampilkan per tahun pada kartu jurusan.' : ''}${missing.length ? ` Data ${missing.join(', ')} belum tersedia pada filter ini.` : ''}`
            : indicators[state.indicator].message;
        $('performanceScope').textContent = `${renderedCards.length} grafik${state.program ? ` · ${state.program}` : ' · Semua program studi'}${periods.length ? ` · Data s.d. ${periods.join(' / ')}` : ''}`;
        $('performanceEmpty').hidden = renderedCards.length > 0;
        $('performanceEmptyTitle').textContent = publication ? 'Belum ada data pada periode ini' : `${indicators[state.indicator].label} belum tersedia`;
        $('performanceEmptyText').textContent = publication ? 'Belum ada snapshot yang sesuai dengan tahun, batas kuartal, dan program studi terpilih. Coba pilih periode atau program studi lain.' : indicators[state.indicator].message;
        renderNotes(selections, publication);
    }

    function renderNotes(selections, publication) {
        if (!publication) {
            $('performanceNotes').innerHTML = `<p>${escape(indicators[state.indicator].message)}</p><p>Nilai yang belum tersedia ditampilkan sebagai tanda pisah.</p>`;
            return;
        }
        const warnings = [...new Set(['fm', 'mhs'].flatMap(kind => selectedSources(source, state, kind).flatMap(row => row.data?.warnings ?? [])))];
        const sources = selections.filter(row => row.data).map(row => `${periodLabel(row.snapshot)}: ${row.data.actualSource}${row.data.filename ? ` (${row.data.filename})` : ''}`);
        $('performanceNotes').innerHTML = `<p>${escape(sources.join(' · ') || 'Sumber belum tersedia pada periode terpilih.')}</p>
            <p>Realisasi FM mengikuti rekap workbook atau bobot asli publikasi bila rekap belum tersedia. Rincian KPI dosen memakai nilai tertinggi Rectorate/RTTO per kategori. Skor jurusan mengikuti rentang Scoring PI; skor dosen mengikuti KPI RTTO.</p>
            <p>Persentase FM memakai dosen yang tercatat dalam data KPI dan publikasi periode terpilih. Tahun tanpa data dan skor yang belum tersedia tetap kosong. Target kampus dapat berbeda dari penjumlahan target jurusan.</p>
            <p>Snapshot kumulatif antarbulan tidak dijumlahkan. Kategori publikasi mengikuti kebijakan impor, termasuk status Accepted/Reviewed; kategori Scopus tidak selalu berarti sudah terbit.</p>
            ${warnings.length ? `<ul>${warnings.map(warning => `<li>${escape(warning)}</li>`).join('')}</ul>` : ''}`;
    }

    function reset() {
        state.years = [source.defaultYear]; state.indicator = 'fm'; state.quarter = 'all'; state.program = '';
        $('performanceYears').open = false;
        render();
    }

    const facultyColumns = [
        ['code', 'Kode dosen'], ['name', 'Nama dosen'], ['program', 'Jurusan'], ['rank', 'JJA'], ['faculty', 'Faculty type'],
        ['nonScopus', 'Non Scopus', true], ['scopus', 'Scopus', true], ['firstAuthor', 'First author', true], ['score', 'Skor KPI', true],
    ];
    const paperColumns = [['title', 'Judul'], ['author', 'Author'], ['name', 'FM Author'], ['request', 'Request code'], ['firstAuthor', 'First author'], ['weight', 'Bobot asli', true], ['category', 'Submitted'], ['status', 'Status'], ['type', 'Jenis'], ['index', 'Tipe publikasi'], ['source', 'Source title'], ['quartile', 'Quartile jurnal']];
    const studentColumns = [['name', 'Penulis mahasiswa'], ['title', 'Judul publikasi']];

    function setDetailTable(mode, code = null) {
        detail.mode = mode; detail.code = code; detail.sort = mode === 'papers' ? 'title' : 'name'; detail.ascending = true;
        $('performanceDetailSearch').value = '';
        $('performanceDetailBack').hidden = mode !== 'papers';
        $('performanceDetailCaption').textContent = mode === 'faculty' ? 'Klik nama dosen untuk melihat publikasinya. Bobot Non Scopus/Scopus mengikuti MAX(Rectorate, RTTO); skor kosong berarti belum tersedia.'
            : mode === 'papers' ? `Publikasi ${detail.card.faculty.find(row => row.code === code)?.name ?? code} pada jurusan dan snapshot terpilih. Bobot per publikasi memakai bobot asli.`
                : 'Daftar penulis mahasiswa dan judul publikasi mengikuti LIST pada periode terpilih.';
        renderTable();
    }

    function tableRows() {
        const columns = detail.mode === 'faculty' ? facultyColumns : detail.mode === 'papers' ? paperColumns : studentColumns;
        const rows = detail.mode === 'faculty' ? detail.card.faculty : detail.mode === 'papers' ? detail.card.publications.filter(row => row.code === detail.code) : detail.card.students;
        const query = $('performanceDetailSearch').value.toLocaleLowerCase('id-ID').trim();
        const visible = rows.filter(row => !query || columns.some(([key]) => String(row[key] ?? '').toLocaleLowerCase('id-ID').includes(query)));
        const numeric = columns.find(([key]) => key === detail.sort)?.[2];
        visible.sort((a, b) => {
            const left = a[detail.sort], right = b[detail.sort];
            if (left === null || left === undefined) return right === null || right === undefined ? 0 : 1;
            if (right === null || right === undefined) return -1;
            const comparison = numeric ? Number(left) - Number(right) : String(left).localeCompare(String(right), 'id', { numeric: true });
            return detail.ascending ? comparison : -comparison;
        });
        return { columns, rows: visible, total: rows.length };
    }

    function renderTable() {
        const { columns, rows, total } = tableRows();
        $('performanceDetailTable').innerHTML = `<thead><tr>${columns.map(([key, label, numeric]) => `<th scope="col" ${numeric ? 'class="pd-numeric"' : ''} aria-sort="${detail.sort === key ? detail.ascending ? 'ascending' : 'descending' : 'none'}"><button type="button" data-sort="${key}">${label}<i class="bi ${detail.sort === key ? detail.ascending ? 'bi-arrow-up' : 'bi-arrow-down' : 'bi-arrow-down-up'}" aria-hidden="true"></i></button></th>`).join('')}</tr></thead><tbody>${rows.length ? rows.map(row => `<tr ${detail.mode === 'faculty' && row.score === 0 ? 'class="is-zero"' : ''}>${columns.map(([key, , numeric]) => `<td class="${numeric ? 'pd-numeric' : key === 'name' ? 'pd-name-cell' : key === 'title' ? 'pd-title-cell' : key === 'author' ? 'pd-author-cell' : key === 'request' ? 'pd-request-cell' : ''}">${detail.mode === 'faculty' && key === 'name' ? `<button class="pd-text-button" type="button" data-faculty="${escape(row.code)}">${escape(row.name)}</button>` : numeric ? format(row[key]) : escape(row[key] ?? '—')}</td>`).join('')}</tr>`).join('') : `<tr><td colspan="${columns.length}">Tidak ada data yang sesuai.</td></tr>`}</tbody>`;
        $('performanceDetailMeta').textContent = `Menampilkan ${format(rows.length)} dari ${format(total)} ${detail.mode === 'faculty' ? 'dosen' : 'entri publikasi'} · s.d. ${periodLabel(detail.snapshot)}`;
    }

    function openDetail(index) {
        detail = { ...renderedCards[index] };
        previousFocus = document.activeElement;
        $('performanceDetailTitle').textContent = `${detail.card.program} · ${detail.card.label}`;
        $('performanceDetailSubtitle').textContent = `${indicators[detail.kind].label} · s.d. ${periodLabel(detail.snapshot)}`;
        $('performanceDetailStats').innerHTML = [['Target tahunan', detail.card.target], ['Realisasi kumulatif', detail.card.actual], ['Skor jurusan', detail.card.score]].map(([label, value]) => `<div class="pd-detail-stat"><span>${label}</span><strong>${format(value)}</strong></div>`).join('');
        setDetailTable(detail.kind === 'fm' ? 'faculty' : 'students');
        $('performanceDetail').showModal();
        document.body.classList.add('pd-modal-open');
        $('performanceDetailSearch').focus();
    }

    $('performanceFilters').addEventListener('submit', event => event.preventDefault());
    $('performanceFilters').addEventListener('reset', event => { event.preventDefault(); reset(); });
    $('performanceIndicator').addEventListener('change', event => { state.indicator = event.target.value; render(); });
    $('performanceQuarter').addEventListener('change', event => { state.quarter = event.target.value; render(); });
    $('performanceProgram').addEventListener('change', event => { state.program = event.target.value; render(); });
    $('performanceYears').addEventListener('change', event => {
        const years = [...document.querySelectorAll('#performanceYears input:checked')].map(input => input.value);
        if (!years.length) { event.target.checked = true; return; }
        state.years = years.sort(); render();
    });
    $('performanceAllYears').addEventListener('click', () => { if (source.years.length) state.years = [...source.years]; render(); });
    document.addEventListener('click', event => { if (!$('performanceYears').contains(event.target)) $('performanceYears').open = false; });
    $('performanceEmptyReset').addEventListener('click', reset);
    $('performancePrint').addEventListener('click', () => window.print());
    $('performanceDepartments').addEventListener('click', event => { const button = event.target.closest('[data-detail]'); if (button) openDetail(Number(button.dataset.detail)); });
    $('performanceDetailClose').addEventListener('click', () => $('performanceDetail').close());
    $('performanceDetail').addEventListener('close', () => { document.body.classList.remove('pd-modal-open'); previousFocus?.focus(); });
    $('performanceDetail').addEventListener('click', event => {
        const rect = $('performanceDetail').getBoundingClientRect();
        if (event.target === $('performanceDetail') && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) $('performanceDetail').close();
    });
    $('performanceDetailSearch').addEventListener('input', renderTable);
    $('performanceDetailBack').addEventListener('click', () => setDetailTable('faculty'));
    $('performanceDetailTable').addEventListener('click', event => {
        const lecturer = event.target.closest('[data-faculty]'), sort = event.target.closest('[data-sort]');
        if (lecturer) setDetailTable('papers', lecturer.dataset.faculty);
        if (sort) { detail.ascending = detail.sort === sort.dataset.sort ? !detail.ascending : true; detail.sort = sort.dataset.sort; renderTable(); }
    });
    $('performanceExport').addEventListener('click', () => {
        const { columns, rows } = tableRows();
        const csv = '\uFEFF' + [columns.map(([, label]) => label), ...rows.map(row => columns.map(([key]) => row[key]))].map(row => row.map(csvCell).join(';')).join('\r\n');
        const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
        const link = document.createElement('a');
        link.href = url; link.download = `publikasi-${detail.kind}-${detail.card.program}-${detail.snapshot.year}-${detail.snapshot.month}-${detail.mode}.csv`;
        document.body.appendChild(link); link.click(); link.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000);
    });
    render();
}

if (typeof document !== 'undefined' && window.publicationPerformance) initialize(window.publicationPerformance);
