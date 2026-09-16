(() => {
    'use strict';
    const data = window.publicationDashboard;
    const el = id => document.getElementById(id);
    const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    const number = (value, digits = 0) => value == null ? '—' : Number(value).toLocaleString('id-ID', {maximumFractionDigits: digits});
    const empty = message => `<div class="empty">${escape(message)}</div>`;
    const mean = values => values.length ? values.reduce((sum, value) => sum + value, 0) / values.length : null;
    const sum = (rows, field) => rows.reduce((total, row) => total + Number(row[field] || 0), 0);
    const annual = (row, year = state.year) => row.annual[year] || {score: null, rows: 0, weight: 0, cluster: null, mentor: null};
    const state = {year: data.default_year || '', program: '', faculty: '', education: '', rank: '', cluster: '', mentor: '', lecturer: '', search: ''};
    const dimensions = ['program', 'faculty', 'education', 'rank', 'cluster', 'mentor'];
    const fieldValue = (row, field) => ['cluster', 'mentor'].includes(field) ? annual(row)[field] : row[field];
    const filter = (row, exceptLecturer = false) => dimensions.every(field => !state[field] || (fieldValue(row, field) || 'Belum tercatat') === state[field]) && (exceptLecturer || !state.lecturer || row.code === state.lecturer);
    const rows = () => data.faculty.filter(row => filter(row));
    const select = (id, values, label) => {
        const current = state[id];
        el(id).innerHTML = `<option value="">${escape(label)}</option>` + values.map(([value, name]) => `<option value="${escape(value)}">${escape(name)}</option>`).join('');
        el(id).value = values.some(([value]) => value === current) ? current : '';
        state[id] = el(id).value;
    };
    const pill = (text, style = 'neu') => `<span class="pill p-${style}">${escape(text)}</span>`;
    const table = (headers, body) => body.length ? `<div class="table-wrap"><table class="tbl"><thead><tr>${headers.map(h => `<th>${escape(h)}</th>`).join('')}</tr></thead><tbody>${body.join('')}</tbody></table></div>` : empty('Belum ada data pada cakupan ini.');
    const bars = (id, items, max = null) => {
        const scale = max || Math.max(...items.map(item => item.value || 0), 1);
        el(id).innerHTML = items.length ? items.map(item => `<div class="bar-row"><span>${escape(item.label)}</span><div class="track"><i style="width:${Math.max(0, Math.min(100, Number(item.value || 0) / scale * 100))}%;background:${item.color || '#2E77C4'}"></i></div><b>${number(item.value, 2)}</b></div>`).join('') : empty('Belum ada data pada cakupan ini.');
    };
    function refreshLecturers() {
        select('lecturer', data.faculty.filter(row => filter(row, true)).map(row => [row.code, row.name]), 'Semua dosen');
    }
    function init() {
        el('year').innerHTML = data.years.slice().reverse().map(year => `<option value="${escape(year)}">${escape(year)}</option>`).join('') || '<option value="">Belum ada snapshot</option>';
        el('year').value = state.year;
        dimensions.forEach(field => select(field, [...new Set(data.faculty.map(row => fieldValue(row, field) || 'Belum tercatat'))].sort().map(value => [value, value]), 'Semua'));
        refreshLecturers();
        [...dimensions, 'lecturer', 'year'].forEach(id => el(id).addEventListener('change', event => {
            state[id] = event.target.value;
            if (id === 'year') {
                ['cluster', 'mentor'].forEach(field => select(field, [...new Set(data.faculty.map(row => annual(row)[field] || 'Belum tercatat'))].sort().map(value => [value, value]), 'Semua'));
            }
            if (id !== 'lecturer') refreshLecturers();
            render();
        }));
        el('reset').addEventListener('click', () => {
            [...dimensions, 'lecturer'].forEach(id => { state[id] = ''; el(id).value = ''; });
            state.search = ''; el('search').value = ''; refreshLecturers(); render();
        });
        el('search').addEventListener('input', event => { state.search = event.target.value.toLowerCase(); renderPapers(rows()); });
        el('ranking').addEventListener('click', event => {
            const link = event.target.closest('[data-lecturer]');
            if (!link) return;
            state.lecturer = link.dataset.lecturer; el('lecturer').value = state.lecturer; render();
        });
        el('print').addEventListener('click', () => window.print());
        el('stamps').innerHTML = [[data.faculty.length, 'Faculty member'], [new Set(data.faculty.map(row => row.program)).size, 'Program studi'], [data.years.join(' · ') || 'Belum tersedia', 'Tahun publikasi']].map(([value, label]) => `<div class="stamp"><b>${escape(value)}</b><span>${escape(label)}</span></div>`).join('');
        el('issues').innerHTML = data.issues.map(issue => `<p><b>${escape(issue.feature)}</b><br>${escape(issue.detail)}</p>`).join('');
        render();
    }
    function selectedPapers(list) {
        const codes = new Set(list.map(row => row.code));
        return data.publications.filter(paper => String(paper.year) === state.year && codes.has(paper.kode_dosen));
    }
    function render() {
        const list = rows(), scores = list.map(row => annual(row).score).filter(score => score != null), papers = selectedPapers(list);
        const snapshot = data.snapshots[state.year];
        el('scope').textContent = `${list.length} dosen dalam cakupan · ${scores.length} memiliki skor`;
        el('sourceNote').innerHTML = snapshot ? `<b>Snapshot ${snapshot.month}/${snapshot.year} · Quarter ${snapshot.period}</b> — ${number(snapshot.rows)} baris dosen–publikasi. ${snapshot.legacy_campus ? `${number(snapshot.legacy_campus)} baris memakai konfirmasi kampus dari master dosen. ` : ''}${snapshot.duplicates ? `${snapshot.duplicates} duplikat dihitung sekali. ` : ''}${snapshot.unverified_campus ? `${snapshot.unverified_campus} baris dengan kampus belum terverifikasi tidak dihitung. ` : ''}Laporan ini mencakup seluruh status yang lolos filter, termasuk Accepted dan Reviewed. <b>Tanpa data tidak dianggap nol.</b>` : 'Data publikasi belum tersedia. Dashboard akan diperbarui setelah laporan ditambahkan oleh pengelola.';
        const average = mean(scores), qualifying = scores.filter(score => score >= 4).length;
        const cards = [
            ['Faculty member', list.length, `${list.length - scores.length} dosen tanpa skor`],
            ['Publikasi unik', new Set(papers.map(p => p.request_code)).size, `${papers.length} baris dosen–publikasi`],
            ['Scopus FM', papers.filter(p => p.submitted === 'Scopus FM').length, 'Kontribusi dosen, semua status'],
            ['Non Scopus FM', papers.filter(p => p.submitted === 'Non Scopus FM').length, 'Kontribusi dosen, semua status'],
            ['Rata-rata KPI', average, 'Skala 0–6 · hanya skor tersedia'],
            ['Memenuhi skor ≥ 4', scores.length ? qualifying / scores.length * 100 : null, `${qualifying} dari ${scores.length} dosen berskor`, '%']
        ];
        el('kpis').innerHTML = cards.map(([label, value, foot, unit]) => `<div class="kpi"><div class="lab">${escape(label)}</div><div class="val">${number(value, 2)}${unit && value != null ? `<small> ${unit}</small>` : ''}</div><div class="foot">${escape(foot)}</div></div>`).join('');
        renderTrend(list);
        bars('productivity', [
            {label: 'Excellent · 6', value: scores.filter(s => s === 6).length, color: '#12764C'},
            {label: 'Good · 5', value: scores.filter(s => s === 5).length},
            {label: 'Moderate · 3–4', value: scores.filter(s => s >= 3 && s < 5).length, color: '#B5790C'},
            {label: 'Perlu ditinjau · 0–2', value: scores.filter(s => s < 3).length, color: '#A93226'},
            {label: 'Tanpa skor', value: list.length - scores.length, color: '#93A3B5'}
        ]);
        const programs = [...new Set(list.map(row => row.program))].map(program => {
            const members = list.filter(row => row.program === program);
            return {label: program, value: mean(members.map(row => annual(row).score).filter(score => score != null)), papers: members.reduce((total, row) => total + annual(row).rows, 0)};
        });
        bars('programScores', programs.slice().sort((a, b) => (b.value ?? -1) - (a.value ?? -1)), 6);
        bars('programPapers', programs.map(row => ({label: row.label, value: row.papers})).sort((a, b) => b.value - a.value));
        renderRisk(list);
        const sorted = list.slice().sort((a, b) => (annual(b).score ?? -1) - (annual(a).score ?? -1) || annual(b).weight - annual(a).weight || a.name.localeCompare(b.name));
        el('ranking').innerHTML = table(['Peringkat', 'Dosen', 'Prodi', 'Matriks', 'Skor', 'Baris', 'Bobot KPI', 'Bobot asli'], sorted.map((row, index) => `<tr><td>${annual(row).score == null ? '—' : index + 1}</td><td><button class="btn" data-lecturer="${escape(row.code)}">${escape(row.name)}</button><div class="muted">${escape(row.code)}</div></td><td>${escape(row.program)}</td><td>${escape(row.rule?.label || 'Belum lengkap')}</td><td>${number(annual(row).score)}</td><td>${annual(row).rows}</td><td>${number(annual(row).weight, 2)}</td><td>${number(annual(row).original_weight, 2)}</td></tr>`));
        const needs = sorted.filter(row => annual(row).score != null && annual(row).score < 3).reverse();
        el('interventions').innerHTML = needs.length ? table(['Dosen', 'Prodi', 'Skor'], needs.map(row => `<tr><td>${escape(row.name)}</td><td>${escape(row.program)}</td><td>${pill(annual(row).score, 'warn')}</td></tr>`)) : empty('Tidak ada dosen dengan skor tersedia di bawah 3 pada cakupan ini.');
        renderPapers(list); renderResearch(list); renderQuality(list); renderPriorities(list);
    }
    function renderTrend(list) {
        const points = data.years.map(year => ({year, score: mean(list.map(row => annual(row, year).score).filter(value => value != null)), count: list.filter(row => annual(row, year).score != null).length}));
        if (!points.some(point => point.score != null)) { el('trend').innerHTML = empty('Belum tersedia skor untuk grafik tren.'); return; }
        const x = i => points.length === 1 ? 280 : 50 + i * 480 / (points.length - 1), y = score => 200 - score / 6 * 170;
        let svg = '<svg viewBox="0 0 580 245" role="img" aria-label="Tren rata-rata KPI per tahun">';
        [0, 2, 4, 6].forEach(score => { svg += `<line class="gridline" x1="45" x2="540" y1="${y(score)}" y2="${y(score)}"/><text class="tick" x="20" y="${y(score) + 4}">${score}</text>`; });
        points.forEach((point, i) => {
            const previous = points[i - 1];
            if (i && previous.score != null && point.score != null) svg += `<line x1="${x(i - 1)}" y1="${y(previous.score)}" x2="${x(i)}" y2="${y(point.score)}" stroke="#16406F" stroke-width="3"/>`;
            if (point.score != null) svg += `<circle cx="${x(i)}" cy="${y(point.score)}" r="5" fill="#16406F"><title>${point.year}: ${number(point.score, 2)} (${point.count} dosen)</title></circle><text x="${x(i)}" y="${y(point.score) - 12}" text-anchor="middle" class="tick b">${number(point.score, 2)}</text>`;
            svg += `<text x="${x(i)}" y="224" text-anchor="middle" class="tick">${escape(point.year)} · n=${point.count}</text>`;
        });
        el('trend').innerHTML = svg + '</svg>';
    }
    function renderRisk(list) {
        const plotted = list.filter(row => annual(row).score != null && row.rule?.threshold != null);
        if (!plotted.length) { el('risk').innerHTML = empty('Belum ada pasangan skor dan ambang matriks yang dapat diplot.'); return; }
        let svg = '<svg viewBox="0 0 560 300" role="img" aria-label="Matriks skor KPI dan ambang bobot"><rect x="45" y="15" width="460" height="230" fill="#F0F6FC"/>';
        const x = score => 45 + score / 6 * 460, y = threshold => 245 - threshold / 6 * 230;
        [0, 2, 4, 6].forEach(tick => { svg += `<line x1="45" x2="505" y1="${y(tick)}" y2="${y(tick)}" class="gridline"/><text x="25" y="${y(tick) + 4}" class="tick">${tick}</text><text x="${x(tick)}" y="267" class="tick">${tick}</text>`; });
        svg += `<line x1="${x(4)}" x2="${x(4)}" y1="15" y2="245" stroke="#12764C" stroke-dasharray="5 5"/>`;
        plotted.forEach(row => { const score = annual(row).score; svg += `<circle cx="${x(score)}" cy="${y(row.rule.threshold)}" r="6" fill="${score >= 4 ? '#12764C' : '#B5790C'}" opacity=".65"><title>${escape(row.name)} · KPI ${score} · ambang ${row.rule.threshold}</title></circle>`; });
        el('risk').innerHTML = svg + '<text x="250" y="295" class="tick">Skor KPI →</text><text x="50" y="12" class="tick">Ambang bobot ↑</text></svg>' + `<p class="hint">${plotted.length} dosen diplot; titik dengan nilai sama saling menumpuk.</p>`;
    }
    function renderPapers(list) {
        const selected = selectedPapers(list), papers = selected.filter(p => !state.search || [p.title, p.request_code, p.source_title, p.status, p.kode_dosen].join(' ').toLowerCase().includes(state.search));
        el('paperCount').textContent = `${papers.length} dari ${selected.length} baris · ${new Set(papers.map(p => p.request_code)).size} publikasi unik`;
        el('papers').innerHTML = table(['RequestCode / Dosen', 'Judul / Sumber', 'Kategori', 'Status', 'Jenis / Quartile', 'Bobot KPI / Asli', 'Pelaporan'], papers.map(p => `<tr><td>${escape(p.request_code)}<div class="muted">${escape(p.kode_dosen)}</div></td><td class="title">${escape(p.title)}<div class="muted">${escape(p.source_title || 'Sumber belum tercatat')}</div></td><td>${pill(p.submitted, p.submitted === 'Scopus FM' ? 'blue' : 'neu')}</td><td>${escape(p.status)}</td><td>${escape(p.jenis)}<br>${escape(p.quartile_jurnal || '—')}</td><td>${number(p.bobot, 2)} / ${number(p.bobot_asli, 3)}</td><td>${escape(p.tanggal_pelaporan || '—')}</td></tr>`));
    }
    function renderResearch(list) {
        const codes = new Set(list.map(row => row.code)), names = new Map(list.map(row => [row.code, row.name]));
        const recorded = list.filter(row => annual(row).mentor || annual(row).cluster);
        el('mentors').innerHTML = recorded.length ? table(['Dosen', 'Cluster', 'Mentor'], recorded.map(row => `<tr><td>${escape(row.name)}</td><td>${escape(annual(row).cluster || '—')}</td><td>${escape(annual(row).mentor || '—')}</td></tr>`)) : empty(`Belum ada penetapan mentor atau cluster riset tahun ${state.year || 'terpilih'} pada cakupan ini.`);
        const grants = data.grants.filter(row => codes.has(row.code));
        const chairs = grants.filter(row => row.role?.toLowerCase() === 'ketua').length;
        const members = grants.filter(row => row.role?.toLowerCase().startsWith('anggota')).length;
        el('grantSummary').textContent = grants.length ? `${grants.length} peran tercatat · ${chairs} Ketua · ${members} Anggota · ${new Set(grants.map(row => row.year + '|' + row.proposal)).size} proyek` : '';
        el('grants').innerHTML = grants.length ? table(['Dosen', 'Tahun', 'Peran', 'KPI Research', 'Judul'], grants.map(row => `<tr><td>${escape(names.get(row.code))}</td><td>${escape(row.year)}</td><td>${escape(row.role)}</td><td>${escape(row.research_kpi || '—')}</td><td>${escape(row.title)}</td></tr>`)) : empty('Data peran hibah belum tersedia pada cakupan ini. Tidak dapat menyimpulkan dosen belum pernah menjadi ketua/anggota.');
        const research = data.research.filter(row => codes.has(row.code) || row.researchers.some(person => codes.has(person.ID)));
        const unique = [...new Map(research.map(row => [row.id || [row.year, row.title.trim().toLowerCase()].join('|'), row])).values()].sort((a, b) => Number(b.year) - Number(a.year));
        el('research').innerHTML = table(['Tahun', 'Judul', 'Sumber pendanaan', 'Peneliti tercatat'], unique.map(row => `<tr><td>${escape(row.year)}</td><td class="title">${escape(row.title)}</td><td>${escape(row.fund || '—')}</td><td>${escape(row.researchers.map(person => person.name || person.ID).join(', ') || names.get(row.code) || row.code)}</td></tr>`));
    }
    function renderQuality(list) {
        const matrix = [...new Set(list.map(row => row.rule?.label || 'Belum terpetakan'))].map(label => ({label, members: list.filter(row => (row.rule?.label || 'Belum terpetakan') === label)}));
        el('matrix').innerHTML = table(['Profil matriks', 'Dosen', 'Ambang skor 4'], matrix.map(row => `<tr><td>${escape(row.label)}</td><td>${row.members.length}</td><td>${row.members[0].rule ? row.members[0].rule.threshold == null ? 'Kualitatif' : number(row.members[0].rule.threshold, 2) : '—'}</td></tr>`));
        const papers = selectedPapers(list);
        const issues = [
            [`Tanpa baris publikasi ${state.year}`, list.filter(row => !annual(row).rows).length, 'dosen'],
            ['Profil matriks belum lengkap', list.filter(row => !row.rule).length, 'dosen'],
            ['Cluster riset belum ditetapkan', list.filter(row => !annual(row).cluster).length, 'dosen'],
            ['Label mentor belum ditetapkan', list.filter(row => !annual(row).mentor).length, 'dosen'],
            ['Penerbit belum tercatat', papers.filter(p => !p.publisher).length, 'baris'],
            ['Tanggal pelaporan belum tercatat', papers.filter(p => !p.tanggal_pelaporan).length, 'baris']
        ];
        el('completeness').innerHTML = issues.map(([label, count, unit]) => `<p class="progress-note">${pill(`${count} ${unit}`, count ? 'warn' : 'ok')} ${escape(label)}</p>`).join('');
    }
    function renderPriorities(list) {
        const names = new Map(list.map(row => [row.code, row.name]));
        const priorities = data.priorities.filter(row => names.has(row.code));
        el('priorities').innerHTML = priorities.length ? table(['Dosen', 'Topik 2027', 'Sumber'], priorities.map(row => `<tr><td>${escape(names.get(row.code))}</td><td>${escape(row.topic)}</td><td>${escape(row.source || '—')}</td></tr>`)) : empty('Topik prioritas 2027 belum tercatat. Isi template HTML tidak dijadikan data rencana.');
        const counts = new Map();
        priorities.forEach(row => row.sdgs.forEach(sdg => { if (!counts.has(sdg)) counts.set(sdg, new Set()); counts.get(sdg).add(row.code); }));
        if (!counts.size) el('sdgs').innerHTML = empty('SDG untuk rencana 2027 belum tercatat.');
        else bars('sdgs', [...counts].map(([label, lecturers]) => ({label, value: lecturers.size})).sort((a, b) => b.value - a.value));
    }
    init();
})();
