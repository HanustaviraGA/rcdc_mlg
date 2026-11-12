import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/lrc-dashboard.js', import.meta.url), 'utf8');

const data = {
    rankProgression: [
        { current: 'TP', next: 'AA', nextLabel: 'AA (Asisten Ahli)' },
        { current: 'AA', next: 'L', nextLabel: 'L (Lektor)' },
        { current: 'L', next: 'LK', nextLabel: 'LK (Lektor Kepala)' },
        { current: 'LK', next: 'GB', nextLabel: 'GB (Guru Besar)' },
        { current: 'GB', next: null, nextLabel: null },
    ],
    charts: {
        jja: { labels: ['TP (Tenaga Pengajar)', 'AA (Asisten Ahli)', 'L (Lektor)', 'LK (Lektor Kepala)', 'GB (Guru Besar)'], data: [5, 1, 1, 1, 1] },
        pendidikan: { labels: ['S2', 'S3', 'On Going S3'], data: [4, 2, 3] },
        usia: { labels: ['20-40 tahun', '41-54 tahun'], data: [2, 0] },
        agama: { labels: ['Islam'], data: [1] },
        fakultas: { labels: ['FM'], data: [1] },
    },
    chartDetails: {
        jja: [[
            { code: 'P1', name: 'First TP', gugus: 'Computer Science', program: 'Informatics', roadmapKenaikanJja: 'AA150', roadmapJjaRank: 'AA', eligibleKenaikanJja: '2028-04-01', eligibleJjaYear: 2028 },
            { code: 'P2', name: 'Second TP', gugus: 'Computer Science', program: 'Informatics', roadmapKenaikanJja: 'AA', roadmapJjaRank: 'AA', eligibleKenaikanJja: '2027-11-01', eligibleJjaYear: 2027 },
            { code: 'P3', name: 'Third TP', gugus: 'Design', program: 'Interior Design', roadmapKenaikanJja: null, roadmapJjaRank: null, eligibleKenaikanJja: null, eligibleJjaYear: null },
            { code: 'P4', name: 'Fourth TP', gugus: 'Design', program: 'Interior Design', roadmapKenaikanJja: 'Lektor', roadmapJjaRank: 'L', eligibleKenaikanJja: '2031-01-01', eligibleJjaYear: 2031 },
            { code: 'P5', name: 'Fifth TP', gugus: 'Computer Science', program: 'Computer Science', roadmapKenaikanJja: 'Asisten Ahli', roadmapJjaRank: 'AA', eligibleKenaikanJja: null, eligibleJjaYear: null },
        ], [
            { code: 'P6', name: 'AA lecturer', roadmapKenaikanJja: 'Lektor', roadmapJjaRank: 'L', eligibleKenaikanJja: '2030-01-01', eligibleJjaYear: 2030 },
        ], [
            { code: 'P7', name: 'L lecturer', roadmapKenaikanJja: 'Lektor Kepala', roadmapJjaRank: 'LK', eligibleKenaikanJja: '2029-01-01', eligibleJjaYear: 2029 },
        ], [
            { code: 'P8', name: 'LK lecturer', roadmapKenaikanJja: 'GB', roadmapJjaRank: 'GB', eligibleKenaikanJja: '2032-01-01', eligibleJjaYear: 2032 },
        ], [
            { code: 'P9', name: 'GB lecturer', roadmapKenaikanJja: null, roadmapJjaRank: null, eligibleKenaikanJja: null, eligibleJjaYear: null },
        ]],
        pendidikan: [[
            { code: 'D1', name: 'Ani', gugus: 'Computer Science', program: 'Informatics', roadmapS3: 2030, proyeksiLulusS3: 2034 },
            { code: 'D2', name: 'Budi', gugus: 'Computer Science', program: 'Computer Science', roadmapS3: 2028, proyeksiLulusS3: 2032 },
            { code: 'D3', name: 'Citra', gugus: 'Design', program: 'Interior Design', roadmapS3: 2028, proyeksiLulusS3: 2033 },
            { code: 'D4', name: 'Dani', roadmapS3: null, proyeksiLulusS3: null },
        ], [
            { code: 'D5', name: 'Eka', gugus: 'Language', program: 'English Literature' },
            { code: 'D9', name: 'Ika', gugus: 'Character Building', program: 'Character Building' },
        ], [
            { code: 'D6', name: 'Fani', gugus: 'Computer Science', program: 'Informatics', proyeksiLulusS3: 2028 },
            { code: 'D7', name: 'Gani', gugus: 'Design', program: 'Interior Design', proyeksiLulusS3: 2027 },
            { code: 'D8', name: 'Hani', gugus: 'Computer Science', program: 'Computer Science', proyeksiLulusS3: 2028 },
        ]],
        usia: [[
            { code: 'D2', name: 'Ani <b>Dosen</b>', gugus: 'Computer Science', program: null },
            { code: 'D3', name: 'Budi', gugus: null, program: 'Program A' },
        ], []],
        agama: [[{ code: 'D1', name: 'Citra', gugus: null, program: null }]],
    },
    facultyDetails: { FM: { label: 'FM', count: 1, types: [{ label: 'Functional Faculty(A)', count: 1, lecturers: [{ code: 'D1', name: 'Citra' }] }] } },
};

// Small DOM and Chart.js stand-ins: run the shipped script and drive its click handlers.
function harness() {
    const elements = new Map();
    const node = (id = '') => ({
        id, hidden: false, textContent: '', innerHTML: '', value: '', dataset: {}, style: {}, listeners: {},
        addEventListener(name, handler) { (this.listeners[name] ??= []).push(handler); },
        fire(name, event = {}) { (this.listeners[name] || []).forEach(handler => handler({ target: this, ...event })); },
        focus() { document.activeElement = this; },
    });
    const element = id => {
        if (!elements.has(id)) elements.set(id, node(id));
        return elements.get(id);
    };
    const dialog = element('lrcDetail');
    Object.assign(dialog, {
        open: false, showCount: 0,
        showModal() { if (this.open) throw new Error('InvalidStateError: dialog already open'); this.open = true; this.showCount++; },
        close() { this.open = false; this.fire('close'); },
        getBoundingClientRect: () => ({ left: 0, right: 0, top: 0, bottom: 0 }),
    });
    const chip = Object.assign(node(), { dataset: { lrcChart: 'agama', lrcIndex: '0' } });
    const cards = { usia: { dataset: { lrcTitle: 'Rentang Usia' } }, agama: { dataset: { lrcTitle: 'Agama' } } };
    const document = {
        activeElement: null,
        body: { classList: { add() {}, remove() {} } },
        getElementById: element,
        querySelectorAll: selector => (selector === '[data-lrc-chart]' ? [chip] : []),
        querySelector: selector => cards[selector.match(/data-lrc-chart-card="([^"]+)"/)?.[1]] ?? null,
    };
    const charts = {};
    class Chart { constructor(canvas, config) { charts[canvas.id] = config; } }
    vm.runInNewContext(source, { document, window: { lrcDashboard: data }, Chart, Intl });
    const click = (canvas, index) => charts[canvas].options.onClick({}, index === undefined ? [] : [{ index }]);

    return { element, dialog, chip, charts, click };
}

test('clicking a bar lists the lecturers in that category, escaped, with a single level', () => {
    const h = harness();
    h.click('lrc-chart-usia');
    assert.equal(h.dialog.open, false, 'clicking outside the bars does nothing');
    h.click('lrc-chart-usia', 0);
    assert.equal(h.dialog.open, true);
    assert.equal(h.element('lrcDetailEyebrow').textContent, 'RENTANG USIA');
    assert.equal(h.element('lrcDetailTitle').textContent, '20-40 tahun');
    assert.equal(h.element('lrcDetailCaption').textContent, 'Rentang Usia · 2 dosen pada filter terpilih.');
    assert.equal(h.element('lrcLecturerRegion').hidden, false);
    assert.equal(h.element('lrcFacultyList').hidden, true);
    assert.equal(h.element('lrcDetailBack').hidden, true);
    const table = h.element('lrcLecturerTable').innerHTML;
    assert.match(table, /Ani &lt;b&gt;Dosen&lt;\/b&gt;/);
    assert.match(table, /Budi<\/td><td>D3<\/td><td>—<\/td><td>Program A/);
    assert.equal(h.element('lrcDetailMeta').textContent, '2 dosen ditampilkan');
});

test('search narrows the category list and an empty category shows an empty state', () => {
    const h = harness();
    h.click('lrc-chart-usia', 0);
    h.element('lrcDetailSearch').value = 'program a';
    h.element('lrcDetailSearch').fire('input');
    assert.equal(h.element('lrcDetailMeta').textContent, '1 dosen ditampilkan');
    assert.doesNotMatch(h.element('lrcLecturerTable').innerHTML, /Ani/);
    h.dialog.close();
    h.click('lrc-chart-usia', 1);
    assert.equal(h.element('lrcDetailSearch').value, '', 'a new category starts without a search');
    assert.match(h.element('lrcLecturerTable').innerHTML, /Tidak ada dosen yang sesuai/);
});

test('category buttons open the same list and every chart shows a pointer over its bars', () => {
    const h = harness();
    h.chip.fire('click');
    assert.equal(h.element('lrcDetailTitle').textContent, 'Islam');
    assert.match(h.element('lrcLecturerTable').innerHTML, /Citra/);
    for (const canvas of ['lrc-chart-usia', 'lrc-chart-agama', 'lrc-chart-fakultas']) {
        const target = { style: {} };
        h.charts[canvas].options.onHover({ native: { target } }, [{ index: 0 }]);
        assert.equal(target.style.cursor, 'pointer', canvas);
        h.charts[canvas].options.onHover({ native: { target } }, []);
        assert.equal(target.style.cursor, 'default', canvas);
    }
});

test('switching from a chart list to Komposisi Fakultas reuses the open dialog and restores its labels', () => {
    const h = harness();
    h.click('lrc-chart-usia', 0);
    h.click('lrc-chart-fakultas', 0);
    assert.equal(h.dialog.showCount, 1, 'an open dialog is not shown again');
    assert.equal(h.element('lrcDetailEyebrow').textContent, 'KOMPOSISI FAKULTAS');
    assert.equal(h.element('lrcDetailTitle').textContent, 'Detail FM');
    assert.equal(h.element('lrcDetailBack').hidden, false);
    assert.equal(h.element('lrcFacultyList').hidden, false);
    assert.equal(h.element('lrcLecturerRegion').hidden, true);
});

test('S2 displays separate year cards, sorted numerically, with counts and percentages including missing years', () => {
    const h = harness();
    h.click('lrc-chart-pendidikan', 0);
    assert.equal(h.element('lrcEducationStats').hidden, false);
    assert.equal(h.element('lrcEducationFilters').hidden, false);
    assert.equal(h.element('lrcRoadmapFilterField').hidden, false);
    const stats = h.element('lrcEducationStats').innerHTML;
    const roadmap = stats.split('<section')[1];
    const projection = stats.split('<section')[2];
    assert.match(roadmap, /Roadmap S3/);
    assert.match(roadmap, /2028<\/h4><strong>2 <small>orang<\/small><\/strong><span>50%/);
    assert.match(roadmap, /2030<\/h4><strong>1 <small>orang<\/small><\/strong><span>25%/);
    assert.ok(roadmap.indexOf('2028</h4>') < roadmap.indexOf('2030</h4>'));
    assert.match(roadmap, /Belum diisi<\/h4><strong>1 <small>orang<\/small><\/strong><span>25%/);
    assert.match(projection, /Proyeksi Lulus S3/);
    assert.ok(projection.indexOf('2032</h4>') < projection.indexOf('2033</h4>'));
    assert.ok(projection.indexOf('2033</h4>') < projection.indexOf('2034</h4>'));
    assert.match(h.element('lrcLecturerTable').innerHTML, /<th>Roadmap S3<\/th><th>Proyeksi Lulus S3<\/th>/);
    assert.match(h.element('lrcRoadmapFilter').innerHTML, /value="empty">Belum diisi/);
});

test('year filters combine, update cards and their denominator, and reset with search', () => {
    const h = harness();
    h.click('lrc-chart-pendidikan', 0);
    h.element('lrcRoadmapFilter').value = '2028';
    h.element('lrcRoadmapFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '2 dosen ditampilkan');
    assert.doesNotMatch(h.element('lrcLecturerTable').innerHTML, /Ani/);
    assert.match(h.element('lrcEducationStats').innerHTML, /2 <small>orang<\/small><\/strong><span>100%/);
    h.element('lrcProjectionFilter').value = '2032';
    h.element('lrcProjectionFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '1 dosen ditampilkan');
    assert.match(h.element('lrcLecturerTable').innerHTML, /Budi/);
    h.element('lrcDetailSearch').value = 'Citra';
    h.element('lrcDetailSearch').fire('input');
    assert.equal(h.element('lrcDetailMeta').textContent, '0 dosen ditampilkan');
    assert.match(h.element('lrcLecturerTable').innerHTML, /colspan="6"/);
    assert.doesNotMatch(h.element('lrcEducationStats').innerHTML, /NaN|Infinity/);
    h.element('lrcEducationReset').fire('click');
    assert.equal(h.element('lrcDetailMeta').textContent, '4 dosen ditampilkan');
    h.element('lrcRoadmapFilter').value = 'empty';
    h.element('lrcRoadmapFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '1 dosen ditampilkan');
    assert.match(h.element('lrcLecturerTable').innerHTML, /Dani/);
});

test('On Going S3 offers projection only and S3 retains category filters without year controls', () => {
    const h = harness();
    h.click('lrc-chart-pendidikan', 0);
    h.element('lrcRoadmapFilter').value = '2028';
    h.click('lrc-chart-pendidikan', 2);
    assert.equal(h.element('lrcRoadmapFilter').value, '');
    assert.equal(h.element('lrcRoadmapFilterField').hidden, true);
    assert.doesNotMatch(h.element('lrcLecturerTable').innerHTML, /Roadmap S3/);
    assert.doesNotMatch(h.element('lrcEducationStats').innerHTML, /Roadmap S3/);
    assert.match(h.element('lrcEducationStats').innerHTML, /2028<\/h4><strong>2 <small>orang<\/small><\/strong><span>66,7%/);
    h.element('lrcProjectionFilter').value = '2028';
    h.element('lrcProjectionFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '2 dosen ditampilkan');
    assert.doesNotMatch(h.element('lrcLecturerTable').innerHTML, /Gani/);
    h.click('lrc-chart-pendidikan', 1);
    assert.equal(h.element('lrcEducationStats').hidden, true);
    assert.equal(h.element('lrcEducationFilters').hidden, false);
    assert.equal(h.element('lrcRoadmapFilterField').hidden, true);
    assert.equal(h.element('lrcProjectionFilterField').hidden, true);
    assert.equal(h.element('lrcProjectionFilter').value, '');
    assert.match(h.element('lrcLecturerTable').innerHTML, /Eka/);
    h.click('lrc-chart-usia', 0);
    assert.equal(h.element('lrcEducationStats').hidden, true);
    assert.equal(h.element('lrcEducationFilters').hidden, true);
});

test('gugus and program filters combine with S2 year filters and update the statistics denominator', () => {
    const h = harness();
    h.click('lrc-chart-pendidikan', 0);
    assert.match(h.element('lrcGugusFilter').innerHTML, /value="Computer Science">Computer Science/);
    assert.doesNotMatch(h.element('lrcGugusFilter').innerHTML, /Language/);
    h.element('lrcGugusFilter').value = 'Computer Science';
    h.element('lrcGugusFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '2 dosen ditampilkan');
    assert.match(h.element('lrcEducationStats').innerHTML, /2028<\/h4><strong>1 <small>orang<\/small><\/strong><span>50%/);
    h.element('lrcRoadmapFilter').value = '2028';
    h.element('lrcRoadmapFilter').fire('change');
    h.element('lrcProjectionFilter').value = '2032';
    h.element('lrcProjectionFilter').fire('change');
    h.element('lrcProgramFilter').value = 'Computer Science';
    h.element('lrcProgramFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '1 dosen ditampilkan');
    assert.match(h.element('lrcLecturerTable').innerHTML, /Budi/);
    assert.match(h.element('lrcEducationStats').innerHTML, /Persentase dari 1 dosen/);
    assert.match(h.element('lrcEducationStats').innerHTML, /<span>100%/);
    h.element('lrcProgramFilter').value = 'Interior Design';
    h.element('lrcProgramFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '0 dosen ditampilkan');
    assert.match(h.element('lrcLecturerTable').innerHTML, /Tidak ada dosen yang sesuai/);
    h.element('lrcEducationReset').fire('click');
    for (const id of ['lrcGugusFilter', 'lrcProgramFilter', 'lrcRoadmapFilter', 'lrcProjectionFilter']) {
        assert.equal(h.element(id).value, '');
    }
    assert.equal(h.element('lrcDetailMeta').textContent, '4 dosen ditampilkan');
});

test('S3 filters by gugus, program and search, and switching categories starts with fresh options and values', () => {
    const h = harness();
    h.click('lrc-chart-pendidikan', 1);
    assert.match(h.element('lrcGugusFilter').innerHTML, /value="Language">Language/);
    assert.doesNotMatch(h.element('lrcGugusFilter').innerHTML, /Computer Science/);
    assert.match(h.element('lrcProgramFilter').innerHTML, /English Literature/);
    h.element('lrcGugusFilter').value = 'Language';
    h.element('lrcGugusFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '1 dosen ditampilkan');
    assert.match(h.element('lrcLecturerTable').innerHTML, /Eka/);
    assert.doesNotMatch(h.element('lrcLecturerTable').innerHTML, /Ika|Roadmap S3|Proyeksi Lulus S3/);
    h.element('lrcProgramFilter').value = 'Character Building';
    h.element('lrcProgramFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '0 dosen ditampilkan');
    h.element('lrcGugusFilter').value = '';
    h.element('lrcGugusFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '1 dosen ditampilkan');
    assert.match(h.element('lrcLecturerTable').innerHTML, /Ika/);
    h.element('lrcDetailSearch').value = 'Eka';
    h.element('lrcDetailSearch').fire('input');
    assert.equal(h.element('lrcDetailMeta').textContent, '0 dosen ditampilkan');
    h.click('lrc-chart-pendidikan', 2);
    assert.equal(h.element('lrcGugusFilter').value, '');
    assert.equal(h.element('lrcProgramFilter').value, '');
    assert.equal(h.element('lrcDetailSearch').value, '');
    assert.equal(h.element('lrcDetailMeta').textContent, '3 dosen ditampilkan');
    assert.doesNotMatch(h.element('lrcProgramFilter').innerHTML, /Character Building/);
});

test('On Going S3 combines gugus and program with its projection filter and search', () => {
    const h = harness();
    h.click('lrc-chart-pendidikan', 2);
    h.element('lrcGugusFilter').value = 'Computer Science';
    h.element('lrcGugusFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '2 dosen ditampilkan');
    assert.match(h.element('lrcEducationStats').innerHTML, /2028<\/h4><strong>2 <small>orang<\/small><\/strong><span>100%/);
    h.element('lrcProgramFilter').value = 'Informatics';
    h.element('lrcProgramFilter').fire('change');
    h.element('lrcProjectionFilter').value = '2028';
    h.element('lrcProjectionFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '1 dosen ditampilkan');
    assert.match(h.element('lrcLecturerTable').innerHTML, /Fani/);
    assert.doesNotMatch(h.element('lrcLecturerTable').innerHTML, /Gani|Hani/);
    h.element('lrcDetailSearch').value = 'Hani';
    h.element('lrcDetailSearch').fire('input');
    assert.equal(h.element('lrcDetailMeta').textContent, '0 dosen ditampilkan');
    h.element('lrcEducationReset').fire('click');
    assert.equal(h.element('lrcDetailMeta').textContent, '3 dosen ditampilkan');
    assert.equal(h.element('lrcDetailSearch').value, '');
});

test('JJA details show the roadmap, full date, and sorted eligibility years for the next rank', () => {
    const h = harness();
    h.click('lrc-chart-jja', 0);
    assert.equal(h.element('lrcDetailTitle').textContent, 'TP (Tenaga Pengajar)');
    const table = h.element('lrcLecturerTable').innerHTML;
    assert.match(table, /<th>Roadmap kenaikan JJA<\/th><th>Eligible kenaikan JJA<\/th>/);
    assert.match(table, /AA150<\/td><td>01-04-2028/);
    assert.match(table, /Lektor<\/td><td>01-01-2031/, 'the source roadmap stays visible even if it skips a rank');
    const stats = h.element('lrcEducationStats').innerHTML;
    assert.match(stats, /Eligible kenaikan ke AA \(Asisten Ahli\)/);
    assert.match(stats, /2027<\/h4><strong>1 <small>orang<\/small><\/strong><span>20%/);
    assert.ok(stats.indexOf('2027</h4>') < stats.indexOf('2028</h4>'));
    assert.doesNotMatch(stats, /2031<\/h4>/, 'another target is excluded from eligibility years for AA');
    for (const label of ['Tanggal belum diisi', 'Roadmap belum diisi', 'Roadmap berbeda']) {
        assert.ok(stats.includes(`${label}</h4><strong>1 <small>orang</small></strong><span>20%`));
    }
    assert.equal(h.element('lrcEducationFilters').hidden, false);
    assert.equal(h.element('lrcJjaRoadmapFilterField').hidden, false);
    assert.equal(h.element('lrcJjaEligibleFilterField').hidden, false);
    assert.equal(h.element('lrcRoadmapFilterField').hidden, true);
    assert.equal(h.element('lrcProjectionFilterField').hidden, true);
});

test('each JJA level targets its next rank and GB has no higher promotion target', () => {
    const h = harness();
    for (const [index, label] of ['AA (Asisten Ahli)', 'L (Lektor)', 'LK (Lektor Kepala)', 'GB (Guru Besar)'].entries()) {
        h.click('lrc-chart-jja', index);
        assert.ok(h.element('lrcEducationStats').innerHTML.includes(`Eligible kenaikan ke ${label}</h3>`));
    }
    h.click('lrc-chart-jja', 4);
    assert.match(h.element('lrcEducationStats').innerHTML, /Jenjang JJA tertinggi/);
    assert.doesNotMatch(h.element('lrcEducationStats').innerHTML, /Eligible kenaikan ke|lrc-year-card/);
    assert.match(h.element('lrcLecturerTable').innerHTML, /GB lecturer/);
});

test('JJA combines gugus, program, roadmap, eligibility year and search, and resets on category changes', () => {
    const h = harness();
    h.click('lrc-chart-jja', 0);
    h.element('lrcGugusFilter').value = 'Computer Science';
    h.element('lrcGugusFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '3 dosen ditampilkan');
    assert.match(h.element('lrcEducationStats').innerHTML, /<span>33,3%/);
    h.element('lrcProgramFilter').value = 'Informatics';
    h.element('lrcProgramFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '2 dosen ditampilkan');
    h.element('lrcJjaEligibleFilter').value = '2028';
    h.element('lrcJjaEligibleFilter').fire('change');
    h.element('lrcJjaRoadmapFilter').value = 'AA150';
    h.element('lrcJjaRoadmapFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '1 dosen ditampilkan');
    assert.match(h.element('lrcLecturerTable').innerHTML, /First TP/);
    assert.match(h.element('lrcEducationStats').innerHTML, /<span>100%/);
    h.element('lrcDetailSearch').value = 'Second';
    h.element('lrcDetailSearch').fire('input');
    assert.equal(h.element('lrcDetailMeta').textContent, '0 dosen ditampilkan');
    assert.doesNotMatch(h.element('lrcEducationStats').innerHTML, /NaN|Infinity/);
    h.element('lrcEducationReset').fire('click');
    assert.equal(h.element('lrcDetailMeta').textContent, '5 dosen ditampilkan');
    h.element('lrcJjaEligibleFilter').value = '2027';
    h.element('lrcJjaEligibleFilter').fire('change');
    h.click('lrc-chart-pendidikan', 0);
    assert.equal(h.element('lrcJjaEligibleFilter').value, '');
    assert.equal(h.element('lrcJjaRoadmapFilterField').hidden, true);
    assert.equal(h.element('lrcJjaEligibleFilterField').hidden, true);
    assert.equal(h.element('lrcDetailMeta').textContent, '4 dosen ditampilkan');
});

test('missing eligibility dates and different roadmaps can be filtered without assigning another promotion target', () => {
    const h = harness();
    h.click('lrc-chart-jja', 0);
    h.element('lrcJjaEligibleFilter').value = 'empty';
    h.element('lrcJjaEligibleFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '2 dosen ditampilkan');
    h.element('lrcEducationReset').fire('click');
    h.element('lrcJjaRoadmapFilter').value = 'Lektor';
    h.element('lrcJjaRoadmapFilter').fire('change');
    assert.equal(h.element('lrcDetailMeta').textContent, '1 dosen ditampilkan');
    assert.match(h.element('lrcEducationStats').innerHTML, /Roadmap berbeda<\/h4><strong>1 <small>orang<\/small><\/strong><span>100%/);
    assert.doesNotMatch(h.element('lrcEducationStats').innerHTML, /2031<\/h4>/);
});
