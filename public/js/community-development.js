function initializeCommunityDevelopment(source) {
    const $ = id => document.getElementById(id);
    const root = $('communityDevelopment');
    const dialog = $('communityDetail');
    const search = $('communityDetailSearch');
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
    const number = value => value === null || value === undefined ? '—' : new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value);
    const statusLabel = row => row.status || (row.activities === null ? 'Belum tersedia' : row.activities > 0 ? 'Sudah PkM' : 'Belum PkM');
    let state = null;
    let previousFocus = null;

    const periodForm = $('communityPeriod')?.form;
    periodForm?.addEventListener('submit', () => {
        periodForm.querySelectorAll('input[type="hidden"]').forEach(input => input.remove());
        const append = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = name; input.value = value; periodForm.append(input);
        };
        document.querySelectorAll('#performanceYears input:checked').forEach(input => append('years[]', input.value));
        [['indicator', 'performanceIndicator'], ['quarter', 'performanceQuarter'], ['program', 'performanceProgram']].forEach(([name, id]) => {
            if ($(id)) append(name, $(id).value);
        });
    });

    function renderDetail() {
        const query = search.value.trim().toLocaleLowerCase('id-ID');
        const matches = values => values.join(' ').toLocaleLowerCase('id-ID').includes(query);
        const categories = state.view === 'categories';
        const lecturers = state.view === 'lecturers';
        $('communityDetailSource').textContent = 'COMMUNITY DEVELOPMENT · ' + (lecturers && !source.isDemo ? source.periodLabel : 'DATA CONTOH');
        $('communityCategoryList').hidden = !categories;
        $('communityDetailTableRegion').hidden = categories;
        $('communityDetailBack').hidden = state.view !== 'partners';
        const fieldLabel = state.field === 'cluster' ? 'kluster' : 'skala usaha';
        if (categories) {
            $('communityDetailTitle').textContent = state.field === 'cluster' ? 'Kategori kluster binaan' : 'Skala usaha mitra';
            $('communityDetailCaption').textContent = 'Pilih kategori untuk melihat daftar mitra binaan. Seluruh rincian menggunakan data contoh.';
            const choices = (state.field === 'cluster' ? source.clusters : source.scales).filter(label => matches([label]));
            $('communityCategoryList').innerHTML = choices.map(label => {
                const count = source.partners.filter(partner => partner[state.field] === label).length;
                return `<button type="button" class="cd-category-option" data-community-partners="${escape(label)}"><span>${escape(label)}</span><span>${count} mitra <i class="bi bi-chevron-right" aria-hidden="true"></i></span></button>`;
            }).join('') || '<p class="pd-context">Tidak ada kategori yang sesuai dengan pencarian.</p>';
            $('communityDetailMeta').textContent = `${choices.length} kategori ditampilkan · ${source.partners.length} mitra dalam data contoh`;
            return;
        }

        const program = state.program === '_unassigned'
            ? { code: 'Belum terhubung ke prodi', label: 'Kode dosen belum ditemukan atau prodi pada data dosen belum tersedia dalam rekap Rasio', lecturers: source.unassignedLecturers || [] }
            : source.programs.find(row => row.key === state.program);
        const all = lecturers
            ? [...program.lecturers].sort((a, b) => (a.score ?? Infinity) - (b.score ?? Infinity) || a.name.localeCompare(b.name, 'id-ID'))
            : source.partners.filter(partner => partner[state.field] === state.value);
        const rows = all.filter(row => matches(lecturers ? [row.name, row.code, row.activities, row.score, statusLabel(row)] : [row.name, row.cluster, row.scale]));
        $('communityDetailTitle').textContent = lecturers ? `List dosen · ${program.code}` : `Mitra binaan · ${state.value}`;
        $('communityDetailCaption').textContent = lecturers
            ? source.isDemo ? `${program.label}. Daftar ini hanya sebagian contoh dosen, bukan seluruh dosen atau kegiatan pada rekap prodi.`
                : `${program.label} · ${source.periodLabel}. Jumlah kegiatan mengikuti kegiatan yang dilaporkan ke Binusmaya; profil dosen dicocokkan berdasarkan kode dosen. Jumlah dosen pada rincian dapat berbeda dari rekap Rasio karena pengelompokan mengikuti data dosen terkini.`
            : `Data contoh mitra berdasarkan ${fieldLabel}.`;
        const headings = lecturers ? ['Nama dosen', 'Jumlah kegiatan', 'Skor KPI', 'Status'] : ['No.', 'Nama UMKM / mitra', 'Kluster', 'Skala'];
        const body = rows.map((row, index) => lecturers
            ? `<tr><td class="cd-name-cell">${escape(row.name)}${row.code ? `<small class="cd-lecturer-code">${escape(row.code)}</small>` : ''}</td><td class="pd-numeric">${number(row.activities)}</td><td><span class="pd-score ${row.score === null ? '' : row.score >= 6 ? 'is-good' : row.score >= 5 ? 'is-warning' : 'is-low'}">Skor <strong>${number(row.score)}</strong></span></td><td>${escape(statusLabel(row))}</td></tr>`
            : `<tr><td class="pd-numeric">${index + 1}</td><td class="cd-name-cell">${escape(row.name)}</td><td>${escape(row.cluster)}</td><td>${escape(row.scale)}</td></tr>`).join('');
        $('communityDetailTable').innerHTML = `<thead><tr>${headings.map((label, index) => `<th scope="col"${lecturers && index === 1 ? ' class="pd-numeric"' : ''}>${label}</th>`).join('')}</tr></thead><tbody>${body || '<tr><td colspan="4" class="cd-no-results">Tidak ada data yang sesuai dengan pencarian.</td></tr>'}</tbody>`;
        $('communityDetailMeta').textContent = `${rows.length} dari ${all.length} ${lecturers ? source.isDemo ? 'dosen contoh' : 'dosen' : 'mitra contoh'} ditampilkan`;
    }

    function openDetail(nextState) {
        previousFocus = document.activeElement;
        state = nextState;
        search.value = '';
        renderDetail();
        dialog.showModal();
        document.body.classList.add('pd-modal-open');
        search.focus();
    }

    root.addEventListener('click', event => {
        const lecturerButton = event.target.closest('[data-community-lecturers]');
        const categoryButton = event.target.closest('[data-community-category]');
        const jump = event.target.closest('[data-community-jump]');
        if (lecturerButton) openDetail({ view: 'lecturers', program: lecturerButton.dataset.communityLecturers });
        if (categoryButton) openDetail({ view: 'categories', field: categoryButton.dataset.communityCategory });
        if (jump) {
            event.preventDefault();
            const card = $(`community-card-${jump.dataset.communityJump}`);
            card.focus({ preventScroll: true });
            card.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'start' });
            card.classList.remove('is-highlighted');
            window.requestAnimationFrame(() => card.classList.add('is-highlighted'));
            window.clearTimeout(card.highlightTimer);
            card.highlightTimer = window.setTimeout(() => card.classList.remove('is-highlighted'), 1800);
        }
    });
    $('communityCategoryList').addEventListener('click', event => {
        const button = event.target.closest('[data-community-partners]');
        if (!button) return;
        state = { ...state, view: 'partners', value: button.dataset.communityPartners };
        search.value = '';
        renderDetail();
        search.focus();
    });
    $('communityDetailBack').addEventListener('click', () => {
        state = { ...state, view: 'categories', value: null };
        search.value = '';
        renderDetail();
        $('communityCategoryList').querySelector('button')?.focus();
    });
    search.addEventListener('input', renderDetail);
    $('communityDetailClose').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
        if (!document.querySelector('dialog[open]')) document.body.classList.remove('pd-modal-open');
        previousFocus?.focus({ preventScroll: true });
    });
    dialog.addEventListener('click', event => {
        const rect = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
    });
}

if (window.communityDevelopment && document.getElementById('communityDevelopment')) initializeCommunityDevelopment(window.communityDevelopment);
