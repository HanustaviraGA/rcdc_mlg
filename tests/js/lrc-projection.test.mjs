import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/lrc-projection.js', import.meta.url), 'utf8');

// The shape LrcDashboard sends: the CS roadmap recruits one S2 in 2026 and one S3 in 2027; DKV has more FM than planned.
const program = (prodi, roadmap, existing, graduates, recruits, projectedS3, projectedFm, newRecruits) => ({ prodi, roadmap, existing, graduates, recruits, projectedS3, projectedFm, newRecruits });
const projection = {
    prodi: ['CS', 'DKV'],
    dataCenter: {
        CS: { programStudi: 'Computer Science', fm: 4, s2: 1, ongoing: 2, s3: 1, lecturers: [
            { code: 'C1', name: 'Ani <b>S3</b>', pendidikan: 'S3', statusPendidikan: null, education: 'S3', proyeksiLulusS3: null },
            { code: 'C2', name: 'Budi', pendidikan: 'On going', statusPendidikan: 'On Going S3', education: 'On Going S3', proyeksiLulusS3: 2027 },
            { code: 'C3', name: 'Citra', pendidikan: 'S2', statusPendidikan: 'On Going S3', education: 'On Going S3', proyeksiLulusS3: 2028 },
            { code: 'C4', name: 'Dani', pendidikan: 'S2', statusPendidikan: null, education: 'S2', proyeksiLulusS3: null },
        ] },
        DKV: { programStudi: 'Visual Communication Design', fm: 2, s2: 0, ongoing: 0, s3: 2, lecturers: [
            { code: 'V1', name: 'Eka', pendidikan: 'S3', statusPendidikan: null, education: 'S3', proyeksiLulusS3: null },
            { code: 'V2', name: 'Fani', pendidikan: 'S3', statusPendidikan: null, education: 'S3', proyeksiLulusS3: null },
        ] },
    },
    years: [
        { year: 2026, s3: 2, fm: 6, percent: 33.3, existing: 3, graduates: 0, recruits: 0, projectedS3: 3, projectedFm: 7, newRecruits: 1, programs: [
            program('CS', { fm: 5, s2: 4, s3: 1, nrS2: 1, nrS3: 0 }, 1, 0, 0, 1, 5, [{ level: 'S2', year: 2026, count: 1 }]),
            program('DKV', { fm: 1, s2: 0, s3: 1, nrS2: 0, nrS3: 0 }, 2, 0, 0, 2, 2, []),
        ] },
        { year: 2027, s3: 4, fm: 7, percent: 57.1, existing: 3, graduates: 1, recruits: 1, projectedS3: 5, projectedFm: 8, newRecruits: 2, programs: [
            program('CS', { fm: 6, s2: 3, s3: 3, nrS2: 0, nrS3: 1 }, 1, 1, 1, 3, 6, [{ level: 'S3', year: 2027, count: 1 }, { level: 'S2', year: 2026, count: 1 }]),
            program('DKV', { fm: 1, s2: 0, s3: 1, nrS2: 0, nrS3: 0 }, 2, 0, 0, 2, 2, []),
        ] },
    ],
};

function harness(data = projection) {
    const elements = new Map();
    const node = (id = '') => ({
        id, hidden: false, textContent: '', innerHTML: '', dataset: {}, listeners: {},
        addEventListener(name, handler) { (this.listeners[name] ??= []).push(handler); },
        fire(name, event = {}) { (this.listeners[name] || []).forEach(handler => handler({ target: this, ...event })); },
        focus() { document.activeElement = this; },
    });
    const element = id => {
        if (!elements.has(id)) elements.set(id, node(id));
        return elements.get(id);
    };
    const dialog = Object.assign(element('lrcProjectionDetail'), {
        open: false, showCount: 0,
        showModal() { if (this.open) throw new Error('InvalidStateError: dialog already open'); this.open = true; this.showCount++; },
        close() { this.open = false; this.fire('close'); },
        getBoundingClientRect: () => ({ left: 10, right: 100, top: 10, bottom: 100 }),
    });
    const years = [...data.years.map(item => item.year), 2040].map(year => Object.assign(node(), { dataset: { lrcProjection: String(year) } }));
    const bodyClasses = new Set();
    const document = {
        activeElement: null,
        body: { classList: { add: name => bodyClasses.add(name), remove: name => bodyClasses.delete(name) } },
        getElementById: element,
        querySelectorAll: selector => (selector === '[data-lrc-projection]' ? years : []),
    };
    vm.runInNewContext(source, { document, window: { lrcDashboard: { projection: data } }, Intl, Array });
    const text = id => element(id).textContent;
    const html = id => element(id).innerHTML;
    // A row's cells as text; the parts of a program heading are joined with " | ".
    const cells = row => row.match(/<t[hd][^>]*>[\s\S]*?<\/t[hd]>/g)
        .map(cell => cell.replace(/<\/span>/g, ' | ').replace(/<[^>]+>/g, '').replace(/ \| $/, '').trim());
    const header = () => cells(html('lrcProjectionTable').match(/<thead>[\s\S]*?<\/thead>/)[0]);
    // Each program group: its markup, heading and lecturer rows.
    const groups = () => [...html('lrcProjectionTable').matchAll(/<tbody class="lrc-prodi-group" data-lrc-projection-group="([^"]+)">([\s\S]*?)<\/tbody>/g)]
        .map(([, prodi, markup]) => {
            const [heading, ...rows] = markup.match(/<tr[\s\S]*?<\/tr>/g);
            return { prodi, markup, heading: cells(heading)[0], rows: rows.map(cells) };
        });
    return { document, element, dialog, years, text, html, header, groups, bodyClasses };
}

// The fixture with one more lecturer: an On Going S3 in DKV graduating in 2027, and Dani (CS) overdue since 2025.
const withMoreGraduates = () => {
    const cs = projection.dataCenter.CS.lecturers.map(lecturer => (lecturer.code === 'C4' ? { ...lecturer, proyeksiLulusS3: 2025 } : lecturer));
    const dkv = [...projection.dataCenter.DKV.lecturers, { code: 'V3', name: 'Gita', pendidikan: 'S2', statusPendidikan: 'On Going S3', education: 'On Going S3', proyeksiLulusS3: 2027 }];
    return { ...projection, dataCenter: { CS: { ...projection.dataCenter.CS, lecturers: cs }, DKV: { ...projection.dataCenter.DKV, lecturers: dkv } } };
};

test('a year lists its existing S3 and the lecturers projected to graduate S3 that year, grouped per program', () => {
    const h = harness();
    h.years.at(-1).fire('click');
    assert.equal(h.dialog.open, false, 'a year without data does nothing');
    h.document.activeElement = h.years[1];
    h.years[1].fire('click');
    assert.equal(h.dialog.open, true);
    assert.ok(h.bodyClasses.has('pd-modal-open'));
    assert.equal(h.text('lrcProjectionTitle'), 'Proyeksi FM S3 2027');
    assert.equal(h.text('lrcProjectionCaption'), 'Roadmap 2027: 4 FM S3 dari 7 FM (57,1%). FM S3 eksisting dan dosen yang diproyeksikan lulus S3 pada 2027 menurut update status pendidikan terbaru, dikelompokkan per prodi.');
    const summary = h.html('lrcProjectionSummary');
    assert.match(summary, /<dt>Eksisting S3<\/dt><dd>3 orang<\/dd>/);
    assert.match(summary, /<dt>Proyeksi lulus S3 2027<\/dt><dd>1 orang<\/dd>/);
    assert.match(summary, /<dt>Proyeksi NR S3 2027<\/dt><dd>1 orang<\/dd>/);
    assert.match(summary, /FM S3 = Eksisting \+ \(Lulus \+ NR\) = 3 \+ \(1 \+ 1\) = <strong>5 orang<\/strong>/);
    assert.match(summary, /<p class="lrc-formula-note">Eksisting = dosen S3 saat ini dan dosen yang lulus sebelum 2027\. Lulus = dosen yang diproyeksikan lulus S3 pada 2027\. NR S3 2027 adalah orang baru dari luar sesuai Roadmap LRC, sehingga belum ada namanya\.<\/p>/);
    assert.match(summary, /<p class="lrc-formula-note lrc-formula-warning">Proyeksi FM S3 berbeda dari roadmap S3 pada DKV \(2, roadmap 1\)\. Data Center \+ NR berbeda dari Jumlah FM roadmap pada DKV \(2, roadmap 1\)\.<\/p>$/);
    assert.deepEqual(h.header(), ['Nama dosen', 'Kode', 'Pendidikan', 'Status pendidikan', 'Proyeksi lulus S3', 'Keterangan']);
    // Existing first, then the graduates; not the later graduate (Citra), the S2 without a plan (Dani) or the recruits.
    const [cs, dkv, ...rest] = h.groups();
    assert.deepEqual(rest, []);
    assert.equal(cs.prodi, 'CS');
    assert.equal(cs.heading, 'CS Computer Science | 1 eksisting · 1 lulus 2027');
    assert.match(cs.markup, /^<tr class="lrc-prodi-heading"><th colspan="6" scope="rowgroup">/);
    assert.deepEqual(cs.rows, [
        ['Ani &lt;b&gt;S3&lt;/b&gt;', 'C1', 'S3', '—', '—', 'Eksisting'],
        ['Budi', 'C2', 'On going', 'On Going S3', '2027', 'Lulus 2027'],
    ]);
    assert.match(cs.markup, /<tr class="lrc-existing-row">[\s\S]*<span class="lrc-status lrc-status-existing">Eksisting<\/span>/);
    assert.match(cs.markup, /<tr class="lrc-graduate-row">[\s\S]*<span class="lrc-status lrc-status-graduate">Lulus 2027<\/span>/);
    assert.equal(dkv.heading, 'DKV Visual Communication Design | 2 eksisting · 0 lulus 2027');
    assert.deepEqual(dkv.rows.map(row => [row[0], row.at(-1)]), [['Eka', 'Eksisting'], ['Fani', 'Eksisting']]);
    assert.equal(h.text('lrcProjectionMeta'), '3 eksisting + 1 lulus 2027 = 4 dosen · 2 prodi');
});

test('earlier graduates become existing; the first year also lists overdue projections as graduates', () => {
    const h = harness(withMoreGraduates());
    h.years[0].fire('click');
    assert.deepEqual(h.groups().map(group => [group.heading, group.rows.map(row => [row[0], row.at(-1)])]), [
        ['CS Computer Science | 1 eksisting · 1 lulus 2026', [['Ani &lt;b&gt;S3&lt;/b&gt;', 'Eksisting'], ['Dani', 'Lulus 2026']]],
        ['DKV Visual Communication Design | 2 eksisting · 0 lulus 2026', [['Eka', 'Eksisting'], ['Fani', 'Eksisting']]],
    ]);
    assert.match(h.html('lrcProjectionSummary'), /Lulus = dosen yang diproyeksikan lulus S3 pada 2026, termasuk proyeksi lulus sebelum 2026 yang belum tercatat S3\./);
    h.years[1].fire('click');
    assert.deepEqual(h.groups().map(group => [group.heading, group.rows.map(row => [row[0], row.at(-1)])]), [
        ['CS Computer Science | 2 eksisting · 1 lulus 2027', [['Ani &lt;b&gt;S3&lt;/b&gt;', 'Eksisting'], ['Dani', 'Eksisting'], ['Budi', 'Lulus 2027']]],
        ['DKV Visual Communication Design | 2 eksisting · 1 lulus 2027', [['Eka', 'Eksisting'], ['Fani', 'Eksisting'], ['Gita', 'Lulus 2027']]],
    ]);
    assert.doesNotMatch(h.html('lrcProjectionSummary'), /belum tercatat S3/);
    assert.equal(h.text('lrcProjectionMeta'), '4 eksisting + 2 lulus 2027 = 6 dosen · 2 prodi');
});

test('the NR S3 of earlier years count as existing without names', () => {
    const cs2028 = program('CS', { fm: 6, s2: 2, s3: 4, nrS2: 0, nrS3: 0 }, 3, 1, 0, 4, 6, [{ level: 'S3', year: 2027, count: 1 }, { level: 'S2', year: 2026, count: 1 }]);
    const dkv2028 = program('DKV', { fm: 1, s2: 0, s3: 1, nrS2: 0, nrS3: 0 }, 2, 0, 0, 2, 2, []);
    const h = harness({ ...projection, years: [...projection.years, { year: 2028, s3: 5, fm: 7, percent: 71.4, existing: 5, graduates: 1, recruits: 0, projectedS3: 6, projectedFm: 8, newRecruits: 2, programs: [cs2028, dkv2028] }] });
    h.years[2].fire('click');
    const [cs] = h.groups();
    assert.equal(cs.heading, 'CS Computer Science | 2 eksisting · 1 lulus 2028 · 1 NR S3 eksisting (belum ada nama)');
    assert.deepEqual(cs.rows.map(row => [row[0], row.at(-1)]), [['Ani &lt;b&gt;S3&lt;/b&gt;', 'Eksisting'], ['Budi', 'Eksisting'], ['Citra', 'Lulus 2028']]);
    assert.match(h.html('lrcProjectionSummary'), /Eksisting = dosen S3 saat ini dan dosen yang lulus sebelum 2028, ditambah 1 NR S3 tahun sebelumnya yang belum ada namanya\./);
    // The NR S3 joining in 2027 is not existing in 2027 yet.
    h.years[1].fire('click');
    assert.equal(h.groups()[0].heading, 'CS Computer Science | 1 eksisting · 1 lulus 2027');
});

test('programs without FM S3 are left out, and a year without any says so', () => {
    const lecturers = projection.dataCenter.CS.lecturers.filter(lecturer => lecturer.code === 'C4');
    const h = harness({ ...projection, dataCenter: { CS: { ...projection.dataCenter.CS, lecturers }, DKV: { ...projection.dataCenter.DKV, lecturers: [] } } });
    h.years[0].fire('click');
    assert.deepEqual(h.groups(), []);
    assert.match(h.html('lrcProjectionTable'), /<tbody><tr><td colspan="6">Belum ada FM S3 eksisting maupun proyeksi lulus S3 pada 2026\.<\/td><\/tr><\/tbody>$/);
    assert.equal(h.text('lrcProjectionMeta'), '0 eksisting + 0 lulus 2026 = 0 dosen · 0 prodi');
});

test('another year while open keeps the dialog open, and closing restores the focus', () => {
    const h = harness();
    h.document.activeElement = h.years[0];
    h.years[0].fire('click');
    assert.equal(h.text('lrcProjectionTitle'), 'Proyeksi FM S3 2026');
    h.years[1].fire('click');
    assert.equal(h.dialog.showCount, 1);
    assert.equal(h.text('lrcProjectionTitle'), 'Proyeksi FM S3 2027');
    h.element('lrcProjectionClose').fire('click');
    assert.equal(h.dialog.open, false);
    assert.ok(!h.bodyClasses.has('pd-modal-open'));
    assert.equal(h.document.activeElement, h.years[0]);
});
