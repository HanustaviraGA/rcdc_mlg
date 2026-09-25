<script>
(() => {
    const urls = {generate: @json(route('exportreport.generate')), save: @json(route('exportreport.save')), download: @json(route('exportreport.download'))};
    const csrf = @json(csrf_token());
    const el = id => document.getElementById(id);
    let draft = @json($savedDraft ?? null);
    const message = text => { el('reportMessage').textContent = text; };
    function busy(value) { ['generateReport', 'saveReport', 'downloadReport'].forEach(id => el(id).disabled = value); }
    async function post(action, body) {
        const response = await fetch(urls[action], {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify(body)});
        if (!response.ok) { const error = await response.json().catch(() => ({})); throw new Error(error.errors ? Object.values(error.errors).flat().join('\n') : error.message || 'Proses laporan gagal.'); }
        return response;
    }
    function payload() { return {id: draft.id, title: el('reportTitle').value, period_label: el('reportPeriod').value, tables: draft.report.tables.map(table => ({id: table.id, rows: table.rows}))}; }
    function number(value) { const clean = String(value).replace(/\s/g, ''); return clean === '' ? null : Number(clean.includes(',') ? clean.replace(/\./g, '').replace(',', '.') : clean); }
    function charts() {
        el('reportCharts').replaceChildren();
        draft.report.tables.slice(0, 2).forEach(table => {
            const card = document.createElement('div'); card.className = 'col-lg-6';
            const panel = document.createElement('div'); panel.className = 'card card-body h-100';
            const heading = document.createElement('h3'); heading.textContent = table.label; panel.append(heading);
            const legend = document.createElement('p'); legend.className = 'text-muted'; legend.textContent = 'Biru: realisasi · Oranye: sisa target'; panel.append(legend);
            table.rows.forEach(row => {
                const target = number(row[1]), actual = number(row[2]);
                if (!(target > 0) || actual === null || !Number.isFinite(actual) || actual < 0) return;
                const label = document.createElement('p'); label.className = 'mb-1 mt-3'; label.textContent = row[0] + ': ' + (100 * actual / target).toLocaleString('id-ID', {maximumFractionDigits: 1}) + '% (' + row[2] + ' / ' + row[1] + ')';
                const track = document.createElement('div'); track.style.cssText = 'height:20px;background:#ed7d31;border-radius:3px;overflow:hidden';
                const fill = document.createElement('div'); fill.style.cssText = 'height:100%;background:#4472c4'; fill.style.width = Math.min(100, 100 * actual / target) + '%'; track.append(fill); panel.append(label, track);
            });
            card.append(panel); el('reportCharts').append(card);
        });
    }
    function tables() {
        el('reportTables').replaceChildren();
        draft.report.tables.forEach(table => {
            const card = document.createElement('section'); card.className = 'card mb-5';
            const body = document.createElement('div'); body.className = 'card-body';
            const heading = document.createElement('h3'); heading.textContent = table.label; body.append(heading);
            const wrap = document.createElement('div'); wrap.className = 'table-responsive';
            const grid = document.createElement('table'); grid.className = 'table table-bordered align-middle';
            const head = grid.createTHead().insertRow();
            [...table.headers, 'Aksi'].forEach(label => { const th = document.createElement('th'); th.textContent = label; head.append(th); });
            const tbody = grid.createTBody();
            table.rows.forEach((row, r) => {
                const tr = tbody.insertRow();
                row.forEach((value, c) => {
                    const input = document.createElement('textarea'); input.className = 'form-control form-control-sm'; input.rows = 2; input.value = value;
                    input.style.minWidth = (table.headers[c] === 'Title' || table.headers[c] === 'Nama Dosen' ? 240 : 90) + 'px';
                    input.setAttribute('aria-label', table.label + ', baris ' + (r + 1) + ', ' + table.headers[c]);
                    input.addEventListener('input', () => { table.rows[r][c] = input.value; if (table.id.endsWith('_summary')) charts(); });
                    tr.insertCell().append(input);
                });
                const remove = document.createElement('button'); remove.className = 'btn btn-sm btn-light-danger'; remove.type = 'button'; remove.textContent = 'Hapus';
                remove.onclick = () => { table.rows.splice(r, 1); tables(); charts(); }; tr.insertCell().append(remove);
            });
            wrap.append(grid); body.append(wrap);
            const add = document.createElement('button'); add.type = 'button'; add.className = 'btn btn-sm btn-light-primary'; add.textContent = 'Tambah baris';
            add.onclick = () => { table.rows.push(table.headers.map(() => '')); tables(); }; body.append(add); card.append(body); el('reportTables').append(card);
        });
    }
    function showDraft() {
        el('reportEditor').hidden = false; el('reportTitle').value = draft.report.title; el('reportPeriod').value = draft.report.period_label;
        el('reportSources').textContent = Object.entries(draft.report.sources).map(([kind, file]) => kind + ': ' + (file || 'Belum diupload')).join(' · ');
        message(['Draft siap diperiksa.', ...draft.report.warnings].join('\n')); tables(); charts();
    }
    el('generateReport').onclick = async () => {
        if (draft && !window.confirm('Generate ulang akan mengganti perubahan pada draft yang sedang dibuka. Lanjutkan?')) return;
        busy(true); message('Mengambil data laporan...');
        try {
            draft = await (await post('generate', {year: el('reportYear').value, month: el('reportMonth').value})).json();
            showDraft();
        } catch (error) { message(error.message); } finally { busy(false); }
    };
    el('saveReport').onclick = async () => { busy(true); try { message((await (await post('save', payload())).json()).message); } catch(error) { message(error.message); } finally { busy(false); } };
    el('downloadReport').onclick = async () => {
        busy(true);
        try {
            const response = await post('download', payload()); const blob = await response.blob(); const url = URL.createObjectURL(blob);
            const link = document.createElement('a'); link.href = url; link.download = 'Laporan Publikasi Scopus FM & MHS (' + draft.report.year + '-' + String(draft.report.month).padStart(2, '0') + ').docx';
            document.body.append(link); link.click(); link.remove(); setTimeout(() => URL.revokeObjectURL(url), 10000); message('Laporan Word berhasil dibuat dari data yang sudah diedit.');
        } catch (error) { message(error.message); } finally { busy(false); }
    };
    if (draft) { showDraft(); }
})();
</script>
