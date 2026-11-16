// Proyeksi FM S3: a year box opens the programs of that year; a program opens its lecturers and New FM Recruits.
function initializeLrcProjection(projection) {
    const $ = id => document.getElementById(id);
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
    const number = value => new Intl.NumberFormat('id-ID').format(value ?? 0);
    const percent = value => (value == null ? '–' : new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(value) + '%');
    const sum = (rows, value) => rows.reduce((total, row) => total + value(row), 0);
    const recruitCount = entry => sum(entry.newRecruits, recruit => recruit.count);
    const dialog = $('lrcProjectionDetail');
    const table = $('lrcProjectionTable');
    const summary = $('lrcProjectionSummary');
    const back = $('lrcProjectionBack');
    let state = { year: null, prodi: null };
    let previousFocus = null;

    const yearData = () => projection.years.find(item => item.year === state.year);
    // Year table columns after Prodi: Roadmap (FM, S2, S3), Data Center (FM, S2, On Going S3, S3),
    // FM S3 (Eksisting, Lulus, NR, Total) and New FM Recruit; a group starts at each of these indexes.
    const groupStarts = [0, 3, 7, 11];
    const totalColumn = 10;
    const columnClass = index => `pd-numeric${groupStarts.includes(index) ? ' lrc-group-start' : ''}${index === totalColumn ? ' lrc-total-column' : ''}`;
    // Optional titles per column index; a titled FM S3 total differs from the roadmap.
    const cells = (values, titles = {}) => values.map((value, index) => {
        const title = titles[index];
        const classes = columnClass(index) + (index === totalColumn && title ? ' lrc-total-differs' : '');
        return `<td class="${classes}"${title ? ` title="${escape(title)}"` : ''}>${number(value)}</td>`;
    }).join('');
    const recruitsColumn = 11;
    const recruitsTitle = entry => entry.newRecruits.map(recruit => `${recruit.level} ${number(recruit.count)} orang baru (rekrut ${recruit.year})`).join(', ');
    // Without NR to spare, the projected S3 of a program can differ from its roadmap S3.
    const difference = entry => (entry.projectedS3 === entry.roadmap.s3 ? ''
        : `Proyeksi FM S3 ${number(entry.projectedS3)} ${entry.projectedS3 > entry.roadmap.s3 ? 'melebihi' : 'kurang dari'} roadmap S3 ${number(entry.roadmap.s3)}.`);
    const headers = labels => labels.map((label, index) => `<th scope="col" class="${columnClass(index)}">${label}</th>`).join('');

    // FM S3 = existing + (graduates + recruits), as LRC writes it.
    function formula(label, entry) {
        return `<dl class="lrc-formula-terms">`
            + `<div><dt>Eksisting S3</dt><dd>${number(entry.existing)} orang</dd></div>`
            + `<div><dt>Proyeksi lulus S3 ${state.year}</dt><dd>${number(entry.graduates)} orang</dd></div>`
            + `<div><dt>Proyeksi NR S3 ${state.year}</dt><dd>${number(entry.recruits)} orang</dd></div></dl>`
            + `<p class="lrc-formula-total">${escape(label)} = Eksisting + (Lulus + NR) = ${number(entry.existing)} + (${number(entry.graduates)} + ${number(entry.recruits)}) = <strong>${number(entry.projectedS3)} orang</strong></p>`;
    }

    function renderYear() {
        const year = yearData();
        const dataCenter = year.programs.map(entry => projection.dataCenter[entry.prodi]);
        $('lrcProjectionEyebrow').textContent = 'PROYEKSI FM S3';
        $('lrcProjectionTitle').textContent = `Proyeksi FM S3 ${year.year}`;
        $('lrcProjectionCaption').textContent = `Roadmap ${year.year}: ${number(year.s3)} FM S3 dari ${number(year.fm)} FM (${percent(year.percent)}). `
            + 'Data Center memakai roster dosen saat ini dan update status pendidikan terbaru. Klik prodi untuk melihat daftar FM.';
        const differing = year.programs.filter(entry => entry.projectedS3 !== entry.roadmap.s3);
        summary.innerHTML = formula('FM S3', year)
            + `<p class="lrc-formula-note">Proyeksi lulus: dosen S2 dan On Going S3 dengan proyeksi lulus S3 ${year.year}. `
            + 'NR (New FM Recruit): orang baru dari luar yang masuk menjadi FM, sebanyak kelebihan Jumlah FM roadmap dibanding FM di Data Center. '
            + 'Dosen yang naik jenjang ke S3 dihitung di Lulus, bukan NR. Kolom NR S3 hanya NR bergelar S3; NR bergelar S2 terlihat di kolom New FM Recruit.'
            + (differing.length ? ` Proyeksi FM S3 berbeda dari roadmap S3 pada ${differing.map(entry => `${escape(entry.prodi)} (proyeksi ${number(entry.projectedS3)}, roadmap ${number(entry.roadmap.s3)})`).join(', ')}.` : '')
            + '</p>';
        table.innerHTML = '<thead><tr><th rowspan="2" scope="col" class="lrc-prodi-cell">Prodi</th>'
            + `<th colspan="3" scope="colgroup" class="lrc-group-start">Roadmap ${year.year}</th><th colspan="4" scope="colgroup" class="lrc-group-start">Data Center saat ini</th>`
            + `<th colspan="4" scope="colgroup" class="lrc-group-start">FM S3 ${year.year}</th><th rowspan="2" scope="col" class="pd-numeric lrc-group-start">New FM Recruit<br>s/d ${year.year}</th></tr>`
            + `<tr>${headers(['FM', 'S2', 'S3', 'FM', 'S2', 'On Going S3', 'S3', 'Eksisting', 'Lulus', 'NR S3', 'Total'])}</tr></thead><tbody>`
            + year.programs.map(entry => {
                const center = projection.dataCenter[entry.prodi];
                return `<tr data-lrc-projection-row="${escape(entry.prodi)}"><th scope="row" class="lrc-prodi-cell"><button type="button" class="pd-text-button lrc-prodi-button" data-lrc-projection-prodi="${escape(entry.prodi)}">${escape(entry.prodi)} <i class="bi bi-chevron-right" aria-hidden="true"></i></button><small>${escape(center.programStudi || '')}</small></th>`
                    + cells([entry.roadmap.fm, entry.roadmap.s2, entry.roadmap.s3, center.fm, center.s2, center.ongoing, center.s3, entry.existing, entry.graduates, entry.recruits, entry.projectedS3, recruitCount(entry)],
                        { [totalColumn]: difference(entry), [recruitsColumn]: recruitsTitle(entry) })
                    + '</tr>';
            }).join('')
            + '</tbody><tfoot><tr><th scope="row" class="lrc-prodi-cell">Total</th>'
            + cells([year.fm, sum(year.programs, entry => entry.roadmap.s2), year.s3, sum(dataCenter, center => center.fm), sum(dataCenter, center => center.s2),
                sum(dataCenter, center => center.ongoing), sum(dataCenter, center => center.s3), year.existing, year.graduates, year.recruits, year.projectedS3, year.newRecruits])
            + '</tr></tfoot>';
        back.hidden = true;
        $('lrcProjectionMeta').textContent = `${year.programs.length} prodi · ${number(year.newRecruits)} New FM Recruit s/d ${year.year}`;
    }

    function projectedEducation(lecturer) {
        if (lecturer.education === 'S3') return 'S3';
        if (lecturer.proyeksiLulusS3 != null && lecturer.proyeksiLulusS3 <= state.year) return `S3 (lulus ${lecturer.proyeksiLulusS3})`;
        return lecturer.education || '—';
    }

    function renderProgram() {
        const year = yearData();
        const entry = year.programs.find(item => item.prodi === state.prodi);
        const center = projection.dataCenter[state.prodi];
        const recruits = recruitCount(entry);
        const surplus = center.fm - entry.roadmap.fm;
        $('lrcProjectionEyebrow').textContent = `PROYEKSI FM S3 ${year.year}`;
        $('lrcProjectionTitle').textContent = `${state.prodi} · ${year.year}`;
        $('lrcProjectionCaption').textContent = `${center.programStudi || state.prodi} · Roadmap ${year.year}: ${number(entry.roadmap.fm)} FM (S2 ${number(entry.roadmap.s2)} · S3 ${number(entry.roadmap.s3)}) · `
            + `Data Center saat ini: ${number(center.fm)} FM (S2 ${number(center.s2)} · On Going S3 ${number(center.ongoing)} · S3 ${number(center.s3)}).`
            + (surplus > 0 ? ` Data Center melebihi roadmap ${number(surplus)} FM.` : '');
        summary.innerHTML = formula(`FM S3 ${state.prodi}`, entry)
            + (difference(entry) ? `<p class="lrc-formula-note lrc-formula-warning">${difference(entry)} Periksa proyeksi lulus S3 dan status pendidikan FM di bawah, atau roadmap ${escape(state.prodi)} ${year.year}.</p>` : '');
        table.innerHTML = `<thead><tr><th scope="col">Nama FM</th><th scope="col">Kode</th><th scope="col">Pendidikan</th><th scope="col">Status pendidikan</th><th scope="col">Proyeksi lulus S3</th><th scope="col">Pendidikan ${year.year}</th></tr></thead><tbody>`
            + center.lecturers.map(lecturer => `<tr><td class="lrc-name-cell">${escape(lecturer.name)}</td><td>${escape(lecturer.code || '—')}</td>`
                + `<td>${escape(lecturer.pendidikan || '—')}</td><td>${escape(lecturer.statusPendidikan || '—')}</td><td>${escape(lecturer.proyeksiLulusS3 ?? '—')}</td>`
                + `<td>${escape(projectedEducation(lecturer))}</td></tr>`).join('')
            + entry.newRecruits.flatMap(recruit => Array.from({ length: recruit.count }, () => '<tr class="lrc-recruit-row" data-lrc-recruit>'
                + `<td class="lrc-name-cell">New FM Recruit<small>Orang baru, rekrut ${recruit.year} · ${escape(recruit.level)}</small></td><td>—</td>`
                + `<td>${escape(recruit.level)}</td><td>—</td><td>—</td><td>${escape(recruit.level)}</td></tr>`)).join('')
            + (center.fm + recruits ? '' : '<tr><td colspan="6">Belum ada FM pada prodi ini.</td></tr>')
            + '</tbody>';
        back.hidden = false;
        $('lrcProjectionMeta').textContent = `${number(center.fm)} FM Data Center + ${number(recruits)} New FM Recruit = ${number(center.fm + recruits)} FM`;
    }

    function render() {
        if (state.prodi) renderProgram();
        else renderYear();
    }

    function open(year) {
        if (!projection.years.some(item => item.year === year)) return;
        if (!dialog.open) previousFocus = document.activeElement;
        state = { year, prodi: null };
        render();
        if (!dialog.open) dialog.showModal();
        document.body.classList.add('pd-modal-open');
    }

    document.querySelectorAll('[data-lrc-projection]').forEach(button => button.addEventListener('click', () => open(Number(button.dataset.lrcProjection))));
    table.addEventListener('click', event => {
        const program = event.target.closest('[data-lrc-projection-prodi]');
        if (!program) return;
        state = { ...state, prodi: program.dataset.lrcProjectionProdi };
        render();
        back.focus();
    });
    back.addEventListener('click', () => {
        const prodi = state.prodi;
        state = { ...state, prodi: null };
        render();
        table.querySelector(`[data-lrc-projection-prodi="${prodi}"]`)?.focus();
    });
    $('lrcProjectionClose').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => { document.body.classList.remove('pd-modal-open'); previousFocus?.focus({ preventScroll: true }); });
    dialog.addEventListener('click', event => {
        const rect = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
    });
}

if (window.lrcDashboard?.projection && document.getElementById('lrcProjectionDetail')) initializeLrcProjection(window.lrcDashboard.projection);
