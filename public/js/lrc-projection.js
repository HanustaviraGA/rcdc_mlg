// Proyeksi FM S3: a year box opens the FM S3 of that year by name, grouped per program (prodi): the existing S3
// (S3 now or graduated in an earlier roadmap year) and the lecturers projected to graduate S3 that year.
function initializeLrcProjection(projection) {
    const $ = id => document.getElementById(id);
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
    const number = value => new Intl.NumberFormat('id-ID').format(value ?? 0);
    const percent = value => (value == null ? '–' : new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(value) + '%');
    const dialog = $('lrcProjectionDetail');
    const table = $('lrcProjectionTable');
    const summary = $('lrcProjectionSummary');
    const columns = 6;
    let year = null;
    let previousFocus = null;

    const list = (entries, projected, planned) => entries.map(entry => `${escape(entry.prodi)} (${number(projected(entry))}, roadmap ${number(planned(entry))})`).join(', ');

    // FM S3 = existing + (graduates + recruits), as LRC writes it.
    function formula(entry) {
        return `<dl class="lrc-formula-terms">`
            + `<div><dt>Eksisting S3</dt><dd>${number(entry.existing)} orang</dd></div>`
            + `<div><dt>Proyeksi lulus S3 ${year.year}</dt><dd>${number(entry.graduates)} orang</dd></div>`
            + `<div><dt>Proyeksi NR S3 ${year.year}</dt><dd>${number(entry.recruits)} orang</dd></div></dl>`
            + `<p class="lrc-formula-total">FM S3 = Eksisting + (Lulus + NR) = ${number(entry.existing)} + (${number(entry.graduates)} + ${number(entry.recruits)}) = <strong>${number(entry.projectedS3)} orang</strong></p>`;
    }

    // As FmS3Projection counts them: a lecturer graduates in the year when the projection falls after the program's
    // previous roadmap year (an overdue projection counts in the first year); earlier graduates are existing.
    function status(entry, lecturer) {
        if (lecturer.education === 'S3') return 'existing';
        const projected = lecturer.proyeksiLulusS3;
        if (projected == null || projected > year.year) return null;
        const previous = projection.years.filter(item => item.year < year.year && item.programs.some(program => program.prodi === entry.prodi)).pop();
        return previous && projected <= previous.year ? 'existing' : 'graduate';
    }

    // The programs with FM S3 this year, in roadmap order: their existing S3, then their graduates, each by name,
    // and the NR S3 of earlier years, which count as existing but have no names.
    function programs() {
        return year.programs.map(entry => {
            const center = projection.dataCenter[entry.prodi];
            const lecturers = center.lecturers.map(lecturer => ({ ...lecturer, status: status(entry, lecturer) }));
            return {
                prodi: entry.prodi,
                programStudi: center.programStudi,
                existing: lecturers.filter(lecturer => lecturer.status === 'existing'),
                graduates: lecturers.filter(lecturer => lecturer.status === 'graduate'),
                recruits: entry.newRecruits.filter(recruit => recruit.level === 'S3' && recruit.year < year.year).reduce((total, recruit) => total + recruit.count, 0),
            };
        }).filter(program => program.existing.length || program.graduates.length || program.recruits);
    }

    function row(lecturer) {
        const label = lecturer.status === 'existing' ? 'Eksisting' : `Lulus ${year.year}`;
        return `<tr class="lrc-${lecturer.status}-row"><td class="lrc-name-cell">${escape(lecturer.name)}</td><td>${escape(lecturer.code || '—')}</td>`
            + `<td>${escape(lecturer.pendidikan || '—')}</td><td>${escape(lecturer.statusPendidikan || '—')}</td><td>${escape(lecturer.proyeksiLulusS3 ?? '—')}</td>`
            + `<td><span class="lrc-status lrc-status-${lecturer.status}">${label}</span></td></tr>`;
    }

    function group({ prodi, programStudi, existing, graduates, recruits }) {
        const counts = [`${number(existing.length)} eksisting`, `${number(graduates.length)} lulus ${year.year}`,
            ...(recruits ? [`${number(recruits)} NR S3 eksisting (belum ada nama)`] : [])];
        return `<tbody class="lrc-prodi-group" data-lrc-projection-group="${escape(prodi)}"><tr class="lrc-prodi-heading">`
            + `<th colspan="${columns}" scope="rowgroup"><span class="lrc-prodi-title"><strong>${escape(prodi)}</strong> ${escape(programStudi || '')}</span>`
            + `<span class="lrc-prodi-count">${counts.join(' · ')}</span></th></tr>`
            + [...existing, ...graduates].map(row).join('')
            + '</tbody>';
    }

    function render() {
        const groups = programs();
        const total = key => groups.reduce((sum, program) => sum + (key === 'recruits' ? program.recruits : program[key].length), 0);
        const overdue = groups.some(program => program.graduates.some(lecturer => lecturer.proyeksiLulusS3 < year.year));
        // Where the projection (Data Center + roadmap NR) differs from the roadmap's S3 and FM.
        const s3Differing = year.programs.filter(entry => entry.projectedS3 !== entry.roadmap.s3);
        const fmDiffering = year.programs.filter(entry => entry.projectedFm !== entry.roadmap.fm);
        $('lrcProjectionTitle').textContent = `Proyeksi FM S3 ${year.year}`;
        $('lrcProjectionCaption').textContent = `Roadmap ${year.year}: ${number(year.s3)} FM S3 dari ${number(year.fm)} FM (${percent(year.percent)}). `
            + `FM S3 eksisting dan dosen yang diproyeksikan lulus S3 pada ${year.year} menurut update status pendidikan terbaru, dikelompokkan per prodi.`;
        summary.innerHTML = formula(year)
            + `<p class="lrc-formula-note">Eksisting = dosen S3 saat ini dan dosen yang lulus sebelum ${year.year}`
            + (total('recruits') ? `, ditambah ${number(total('recruits'))} NR S3 tahun sebelumnya yang belum ada namanya` : '')
            + `. Lulus = dosen yang diproyeksikan lulus S3 pada ${year.year}${overdue ? `, termasuk proyeksi lulus sebelum ${year.year} yang belum tercatat S3` : ''}. `
            + `NR S3 ${year.year} adalah orang baru dari luar sesuai Roadmap LRC, sehingga belum ada namanya.</p>`
            + (s3Differing.length || fmDiffering.length ? '<p class="lrc-formula-note lrc-formula-warning">'
                + (s3Differing.length ? `Proyeksi FM S3 berbeda dari roadmap S3 pada ${list(s3Differing, entry => entry.projectedS3, entry => entry.roadmap.s3)}. ` : '')
                + (fmDiffering.length ? `Data Center + NR berbeda dari Jumlah FM roadmap pada ${list(fmDiffering, entry => entry.projectedFm, entry => entry.roadmap.fm)}.` : '')
                + '</p>' : '');
        table.innerHTML = '<thead><tr><th scope="col">Nama dosen</th><th scope="col">Kode</th><th scope="col">Pendidikan</th><th scope="col">Status pendidikan</th>'
            + '<th scope="col">Proyeksi lulus S3</th><th scope="col">Keterangan</th></tr></thead>'
            + (groups.map(group).join('') || `<tbody><tr><td colspan="${columns}">Belum ada FM S3 eksisting maupun proyeksi lulus S3 pada ${year.year}.</td></tr></tbody>`);
        $('lrcProjectionMeta').textContent = `${number(total('existing'))} eksisting + ${number(total('graduates'))} lulus ${year.year} = ${number(total('existing') + total('graduates'))} dosen · ${number(groups.length)} prodi`;
    }

    function open(value) {
        const next = projection.years.find(item => item.year === value);
        if (!next) return;
        if (!dialog.open) previousFocus = document.activeElement;
        year = next;
        render();
        if (!dialog.open) dialog.showModal();
        document.body.classList.add('pd-modal-open');
    }

    document.querySelectorAll('[data-lrc-projection]').forEach(button => button.addEventListener('click', () => open(Number(button.dataset.lrcProjection))));
    $('lrcProjectionClose').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => { document.body.classList.remove('pd-modal-open'); previousFocus?.focus({ preventScroll: true }); });
    dialog.addEventListener('click', event => {
        const rect = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
    });
}

if (window.lrcDashboard?.projection && document.getElementById('lrcProjectionDetail')) initializeLrcProjection(window.lrcDashboard.projection);
