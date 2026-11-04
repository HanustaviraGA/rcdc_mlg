// Lecturer details of the Dashboard CBDC. Publikasi follows the Dashboard Riset (lecturers, then the
// publications of one lecturer) and Community Development the lecturer list of the Dashboard ComDev.
// `shared` is public/js/publication-performance.js, which holds the publication table and its filters.
const numberFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
const hasNumber = value => value !== null && value !== undefined && value !== '' && Number.isFinite(Number(value));
const format = value => hasNumber(value) ? numberFormat.format(Number(value)) : '—';
const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);

export const facultyColumns = [
    ['code', 'Kode dosen'], ['name', 'Nama dosen'], ['rank', 'JJA'], ['faculty', 'Faculty type'],
    ['nonScopus', 'Non Scopus', true], ['scopus', 'Scopus', true], ['firstAuthor', 'First author', true], ['score', 'Skor KPI', true],
];

export const statusLabel = row => row.status || (row.activities === null || row.activities === undefined ? 'Belum tersedia' : row.activities > 0 ? 'Sudah PkM' : 'Belum PkM');

/** As on the Dashboard ComDev: the lowest scores first, then by name, narrowed by the search. */
export function comdevRows(lecturers, query = '') {
    const search = query.trim().toLocaleLowerCase('id-ID');
    return [...lecturers]
        .sort((a, b) => (a.score ?? Infinity) - (b.score ?? Infinity) || a.name.localeCompare(b.name, 'id-ID'))
        .filter(row => [row.name, row.code, row.activities, row.score, statusLabel(row)].join(' ').toLocaleLowerCase('id-ID').includes(search));
}

export function initializeCbdc(source, shared, doc = document) {
    const $ = id => doc.getElementById(id);
    let previousFocus = null;

    function open(dialog, focus) {
        previousFocus = doc.activeElement;
        dialog.showModal();
        doc.body.classList.add('pd-modal-open');
        focus.focus();
    }
    for (const [id, close] of [['cbdcPublicationDetail', 'cbdcPublicationClose'], ['cbdcComdevDetail', 'cbdcComdevClose']]) {
        const dialog = $(id);
        $(close).addEventListener('click', () => dialog.close());
        dialog.addEventListener('close', () => {
            if (!doc.querySelector('dialog[open]')) doc.body.classList.remove('pd-modal-open');
            previousFocus?.focus({ preventScroll: true });
        });
        dialog.addEventListener('click', event => {
            const rect = dialog.getBoundingClientRect();
            if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
            doc.querySelectorAll('.pd-paper-select[open]').forEach(group => { if (!group.contains(event.target)) group.open = false; });
        });
    }

    // Publikasi: the CBDC lecturers, and the publications of the lecturer whose name is clicked.
    const publication = source.publication;
    const detail = { mode: 'faculty', code: null, sort: 'name', ascending: true, filters: {} };
    const lecturer = code => publication.faculty.find(row => row.code === code);
    const papers = () => publication.publications.filter(row => row.code === detail.code);

    function setMode(mode, code = null) {
        Object.assign(detail, { mode, code, sort: mode === 'papers' ? 'title' : 'name', ascending: true, filters: {} });
        $('cbdcPublicationSearch').value = '';
        $('cbdcPublicationBack').hidden = mode !== 'papers';
        $('cbdcPublicationStats').hidden = mode === 'papers';
        $('cbdcFacultySummary').hidden = mode !== 'papers';
        $('cbdcPublicationFilters').hidden = mode !== 'papers';
        const name = lecturer(code)?.name ?? code;
        if (mode === 'papers') {
            const counts = shared.publicationCounts(papers());
            $('cbdcFacultyName').textContent = `${name} · ${code}`;
            $('cbdcFacultyCounts').innerHTML = `<tr>${[counts.firstConference, counts.firstJournal, counts.nonFirstConference, counts.nonFirstJournal, counts.sinta, lecturer(code)?.score]
                .map(value => `<td>${format(value)}</td>`).join('')}</tr>`;
            $('cbdcPublicationFilterGrid').innerHTML = shared.publicationFilterMarkup(papers());
        }
        $('cbdcPublicationCaption').textContent = mode === 'faculty'
            ? 'Klik nama dosen untuk melihat publikasinya. Bobot Non Scopus/Scopus mengikuti MAX(Rectorate, RTTO); skor kosong berarti belum tersedia.'
            : `Publikasi ${name} pada upload terpilih. Bobot per publikasi memakai bobot asli.`;
        renderPublication();
    }

    function publicationTable() {
        const columns = detail.mode === 'faculty' ? facultyColumns : shared.publicationColumns;
        const rows = detail.mode === 'faculty' ? publication.faculty : papers();
        const query = $('cbdcPublicationSearch').value.toLocaleLowerCase('id-ID').trim();
        const filtered = detail.mode === 'papers' ? shared.filterPublications(rows, detail.filters) : rows;
        const visible = filtered.filter(row => !query || columns.some(([key]) => String(row[key] ?? '').toLocaleLowerCase('id-ID').includes(query)));
        const numeric = columns.find(([key]) => key === detail.sort)?.[2];
        return { columns, rows: shared.sortRows(visible, detail.sort, detail.ascending, numeric), total: rows.length };
    }

    function renderPublication() {
        const { columns, rows, total } = publicationTable();
        const faculty = detail.mode === 'faculty';
        const cellClass = (key, numeric) => numeric ? 'pd-numeric' : { name: 'pd-name-cell', title: 'pd-title-cell', author: 'pd-author-cell', request: 'pd-request-cell' }[key] ?? '';
        const head = columns.map(([key, label, numeric]) => {
            const sort = detail.sort === key ? detail.ascending ? 'ascending' : 'descending' : 'none';
            const icon = detail.sort === key ? detail.ascending ? 'bi-arrow-up' : 'bi-arrow-down' : 'bi-arrow-down-up';
            return `<th scope="col" ${numeric ? 'class="pd-numeric"' : ''} aria-sort="${sort}"><button type="button" data-sort="${key}">${label}<i class="bi ${icon}" aria-hidden="true"></i></button></th>`;
        }).join('');
        const body = rows.map(row => `<tr ${faculty && row.score === 0 ? 'class="is-zero"' : ''}>${columns.map(([key, , numeric]) => `<td class="${cellClass(key, numeric)}">${faculty && key === 'name'
            ? `<button class="pd-text-button" type="button" data-faculty="${escape(row.code)}">${escape(row.name)}</button>`
            : numeric ? format(row[key]) : escape(row[key] ?? '—')}</td>`).join('')}</tr>`).join('');
        $('cbdcPublicationTable').innerHTML = `<thead><tr>${head}</tr></thead><tbody>${body || `<tr><td colspan="${columns.length}">Tidak ada data yang sesuai.</td></tr>`}</tbody>`;
        $('cbdcPublicationMeta').textContent = `Menampilkan ${format(rows.length)} dari ${format(total)} ${faculty ? 'dosen' : 'entri publikasi'} · ${source.label}`;
    }

    $('cbdcPublicationTable').addEventListener('click', event => {
        const name = event.target.closest('[data-faculty]'), sort = event.target.closest('[data-sort]');
        if (name) {
            setMode('papers', name.dataset.faculty);
            $('cbdcPublicationBack').focus();
        }
        if (sort) {
            detail.ascending = detail.sort === sort.dataset.sort ? !detail.ascending : true;
            detail.sort = sort.dataset.sort;
            renderPublication();
        }
    });
    $('cbdcPublicationBack').addEventListener('click', () => { setMode('faculty'); $('cbdcPublicationSearch').focus(); });
    $('cbdcPublicationSearch').addEventListener('input', renderPublication);
    shared.bindPublicationFilters($('cbdcPublicationFilterGrid'), group => { shared.applyPublicationFilter(group, detail.filters); renderPublication(); });
    $('cbdcPublicationReset').addEventListener('click', () => {
        detail.filters = {};
        $('cbdcPublicationSearch').value = '';
        $('cbdcPublicationFilterGrid').innerHTML = shared.publicationFilterMarkup(papers());
        renderPublication();
    });
    $('cbdcPublicationExport').addEventListener('click', () => {
        const { columns, rows } = publicationTable();
        const csv = '﻿' + [columns.map(([, label]) => label), ...rows.map(row => columns.map(([key]) => row[key]))].map(row => row.map(shared.csvCell).join(';')).join('\r\n');
        const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
        const link = doc.createElement('a');
        link.href = url;
        link.download = `publikasi-cbdc-${publication.period}-${detail.mode === 'faculty' ? 'dosen' : detail.code}.csv`;
        doc.body.appendChild(link); link.click(); link.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000);
    });

    // Community Development: the CBDC lecturers and the activities they reported.
    const comdev = source.comdev;
    function renderComdev() {
        const rows = comdevRows(comdev.lecturers, $('cbdcComdevSearch').value);
        const badge = score => score === null || score === undefined ? '' : score >= 6 ? 'is-good' : score >= 5 ? 'is-warning' : 'is-low';
        const body = rows.map(row => `<tr><td class="cd-name-cell">${escape(row.name)}${row.code ? `<small class="cd-lecturer-code">${escape(row.code)}</small>` : ''}</td><td class="pd-numeric">${format(row.activities)}</td><td><span class="pd-score ${badge(row.score)}">Skor <strong>${format(row.score)}</strong></span></td><td>${escape(statusLabel(row))}</td></tr>`).join('');
        $('cbdcComdevTable').innerHTML = '<thead><tr><th scope="col">Nama dosen</th><th scope="col" class="pd-numeric">Jumlah kegiatan</th><th scope="col">Skor KPI</th><th scope="col">Status</th></tr></thead>'
            + `<tbody>${body || '<tr><td colspan="4" class="cd-no-results">Tidak ada data yang sesuai dengan pencarian.</td></tr>'}</tbody>`;
        $('cbdcComdevMeta').textContent = `${rows.length} dari ${comdev.lecturers.length} dosen ditampilkan`;
    }
    $('cbdcComdevSearch').addEventListener('input', renderComdev);

    doc.querySelectorAll('[data-cbdc-open]').forEach(button => button.addEventListener('click', () => {
        if (button.dataset.cbdcOpen === 'publikasi') {
            setMode('faculty');
            open($('cbdcPublicationDetail'), $('cbdcPublicationSearch'));
        } else {
            $('cbdcComdevSearch').value = '';
            renderComdev();
            open($('cbdcComdevDetail'), $('cbdcComdevSearch'));
        }
    }));
}
