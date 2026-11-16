import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/lrc-projection.js', import.meta.url), 'utf8');

// The shape LrcDashboard sends: CS needs one S2 recruit from 2026 and one S3 recruit from 2027; DKV has more FM than planned.
const program = (prodi, roadmap, existing, graduates, recruits, projectedS3, newRecruits) => ({ prodi, roadmap, existing, graduates, recruits, projectedS3, newRecruits });
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
        { year: 2026, s3: 2, fm: 6, percent: 33.3, existing: 3, graduates: 0, recruits: 0, projectedS3: 3, newRecruits: 1, programs: [
            program('CS', { fm: 5, s2: 4, s3: 1 }, 1, 0, 0, 1, [{ level: 'S2', year: 2026, count: 1 }]),
            program('DKV', { fm: 1, s2: 0, s3: 1 }, 2, 0, 0, 2, []),
        ] },
        { year: 2027, s3: 4, fm: 7, percent: 57.1, existing: 3, graduates: 1, recruits: 1, projectedS3: 5, newRecruits: 2, programs: [
            program('CS', { fm: 6, s2: 3, s3: 3 }, 1, 1, 1, 3, [{ level: 'S3', year: 2027, count: 1 }, { level: 'S2', year: 2026, count: 1 }]),
            program('DKV', { fm: 1, s2: 0, s3: 1 }, 2, 0, 0, 2, []),
        ] },
    ],
};

function harness() {
    const elements = new Map();
    const node = (id = '') => ({
        id, hidden: false, textContent: '', innerHTML: '', dataset: {}, listeners: {}, selectors: {},
        addEventListener(name, handler) { (this.listeners[name] ??= []).push(handler); },
        fire(name, event = {}) { (this.listeners[name] || []).forEach(handler => handler({ target: this, ...event })); },
        focus() { document.activeElement = this; },
        querySelector(selector) { return this.selectors[selector] ?? null; },
        closest() { return null; },
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
    const years = [2026, 2027, 2040].map(year => Object.assign(node(), { dataset: { lrcProjection: String(year) } }));
    const bodyClasses = new Set();
    const document = {
        activeElement: null,
        body: { classList: { add: name => bodyClasses.add(name), remove: name => bodyClasses.delete(name) } },
        getElementById: element,
        querySelectorAll: selector => (selector === '[data-lrc-projection]' ? years : []),
    };
    vm.runInNewContext(source, { document, window: { lrcDashboard: { projection } }, Intl, Array });
    // A program button inside the year table.
    const programButton = prodi => {
        const button = node();
        button.dataset.lrcProjectionProdi = prodi;
        button.closest = selector => (selector === '[data-lrc-projection-prodi]' ? button : null);
        return button;
    };
    const text = id => element(id).textContent;
    const html = id => element(id).innerHTML;
    const rows = () => html('lrcProjectionTable').match(/<tr[\s\S]*?<\/tr>/g).map(row => row.replace(/<small>[\s\S]*?<\/small>/g, '').replace(/<br>/g, ' ')
        .match(/<t[hd][^>]*>[\s\S]*?<\/t[hd]>/g).map(cell => cell.replace(/<[^>]+>/g, '').trim()));
    return { document, element, dialog, years, programButton, text, html, rows, bodyClasses };
}

test('a year lists every program with its roadmap, Data Center and FM S3 formula', () => {
    const h = harness();
    h.years[2].fire('click');
    assert.equal(h.dialog.open, false, 'a year without data does nothing');
    h.document.activeElement = h.years[1];
    h.years[1].fire('click');
    assert.equal(h.dialog.open, true);
    assert.ok(h.bodyClasses.has('pd-modal-open'));
    assert.equal(h.text('lrcProjectionTitle'), 'Proyeksi FM S3 2027');
    assert.match(h.text('lrcProjectionCaption'), /^Roadmap 2027: 4 FM S3 dari 7 FM \(57,1%\)\. Data Center memakai roster dosen saat ini dan update status pendidikan terbaru\./);
    const summary = h.html('lrcProjectionSummary');
    assert.match(summary, /<dt>Eksisting S3<\/dt><dd>3 orang<\/dd>/);
    assert.match(summary, /<dt>Proyeksi lulus S3 2027<\/dt><dd>1 orang<\/dd>/);
    assert.match(summary, /<dt>Proyeksi NR S3 2027<\/dt><dd>1 orang<\/dd>/);
    assert.match(summary, /FM S3 = Eksisting \+ \(Lulus \+ NR\) = 3 \+ \(1 \+ 1\) = <strong>5 orang<\/strong>/);
    assert.match(summary, /NR \(New FM Recruit\): orang baru dari luar yang masuk menjadi FM, sebanyak kelebihan Jumlah FM roadmap dibanding FM di Data Center\. Dosen yang naik jenjang ke S3 dihitung di Lulus, bukan NR\. Kolom NR S3 hanya NR bergelar S3; NR bergelar S2 terlihat di kolom New FM Recruit\. Proyeksi FM S3 berbeda dari roadmap S3 pada DKV \(proyeksi 2, roadmap 1\)\.<\/p>$/);
    assert.match(h.html('lrcProjectionTable'), /<td class="pd-numeric lrc-total-column lrc-total-differs" title="Proyeksi FM S3 2 melebihi roadmap S3 1\.">2<\/td>/);
    const [groups, columns, cs, dkv, total] = h.rows();
    assert.deepEqual(groups, ['Prodi', 'Roadmap 2027', 'Data Center saat ini', 'FM S3 2027', 'New FM Recruit s/d 2027']);
    assert.deepEqual(columns, ['FM', 'S2', 'S3', 'FM', 'S2', 'On Going S3', 'S3', 'Eksisting', 'Lulus', 'NR S3', 'Total']);
    assert.deepEqual(cs, ['CS', '6', '3', '3', '4', '1', '2', '1', '1', '1', '1', '3', '2']);
    assert.deepEqual(dkv, ['DKV', '1', '0', '1', '2', '0', '0', '2', '2', '0', '0', '2', '0']);
    assert.deepEqual(total, ['Total', '7', '3', '4', '6', '1', '2', '3', '3', '1', '1', '5', '2']);
    assert.match(h.html('lrcProjectionTable'), /data-lrc-projection-prodi="CS">CS <i class="bi bi-chevron-right" aria-hidden="true"><\/i><\/button><small>Computer Science<\/small>/);
    // Each group of columns starts with a divider, in the header and in every row; the FM S3 total stands out.
    const groupStarts = markup => [...markup.matchAll(/<t[hd][^>]*class="([^"]*)"/g)].map(match => match[1])
        .flatMap((classes, index) => (classes.includes('lrc-group-start') ? [index] : []));
    const [head, csRow, , footer] = h.html('lrcProjectionTable').match(/<tr[\s\S]*?<\/tr>/g).slice(1);
    assert.deepEqual(groupStarts(head), [0, 3, 7], 'New FM Recruit spans both header rows');
    assert.deepEqual(groupStarts(csRow), [1, 4, 8, 12], 'after the Prodi cell');
    assert.deepEqual(groupStarts(footer), [1, 4, 8, 12]);
    assert.match(csRow, /<td class="pd-numeric lrc-total-column">3<\/td>/);
    // The New FM Recruit count says who they are: new people by level and year.
    assert.match(csRow, /<td class="pd-numeric lrc-group-start" title="S3 1 orang baru \(rekrut 2027\), S2 1 orang baru \(rekrut 2026\)">2<\/td><\/tr>$/);
    assert.equal(groupStarts(h.html('lrcProjectionTable').match(/<tr>[\s\S]*?<\/tr>/)[0]).length, 4, 'Roadmap, Data Center, FM S3 and New FM Recruit');
    assert.equal(h.element('lrcProjectionBack').hidden, true);
    assert.equal(h.text('lrcProjectionMeta'), '2 prodi · 2 New FM Recruit s/d 2027');
});

test('a program lists its lecturers, their education now and in the year, then its New FM Recruits', () => {
    const h = harness();
    h.years[1].fire('click');
    h.element('lrcProjectionTable').fire('click', { target: h.programButton('CS') });
    assert.equal(h.text('lrcProjectionEyebrow'), 'PROYEKSI FM S3 2027');
    assert.equal(h.text('lrcProjectionTitle'), 'CS · 2027');
    assert.equal(h.text('lrcProjectionCaption'), 'Computer Science · Roadmap 2027: 6 FM (S2 3 · S3 3) · Data Center saat ini: 4 FM (S2 1 · On Going S3 2 · S3 1).');
    assert.match(h.html('lrcProjectionSummary'), /FM S3 CS = Eksisting \+ \(Lulus \+ NR\) = 1 \+ \(1 \+ 1\) = <strong>3 orang<\/strong><\/p>$/, 'no warning when it matches the roadmap');
    const [header, ...rows] = h.rows();
    assert.deepEqual(header, ['Nama FM', 'Kode', 'Pendidikan', 'Status pendidikan', 'Proyeksi lulus S3', 'Pendidikan 2027']);
    assert.deepEqual(rows, [
        ['Ani &lt;b&gt;S3&lt;/b&gt;', 'C1', 'S3', '—', '—', 'S3'],
        ['Budi', 'C2', 'On going', 'On Going S3', '2027', 'S3 (lulus 2027)'],
        ['Citra', 'C3', 'S2', 'On Going S3', '2028', 'On Going S3'],
        ['Dani', 'C4', 'S2', '—', '—', 'S2'],
        ['New FM Recruit', '—', 'S3', '—', '—', 'S3'],
        ['New FM Recruit', '—', 'S2', '—', '—', 'S2'],
    ]);
    assert.match(h.html('lrcProjectionTable'), /New FM Recruit<small>Orang baru, rekrut 2027 · S3<\/small>/);
    assert.match(h.html('lrcProjectionTable'), /New FM Recruit<small>Orang baru, rekrut 2026 · S2<\/small>/);
    assert.equal(h.text('lrcProjectionMeta'), '4 FM Data Center + 2 New FM Recruit = 6 FM');
    assert.equal(h.element('lrcProjectionBack').hidden, false);
    assert.equal(h.document.activeElement, h.element('lrcProjectionBack'));

    // A program with more FM than its roadmap has no recruits.
    h.element('lrcProjectionBack').fire('click');
    h.element('lrcProjectionTable').fire('click', { target: h.programButton('DKV') });
    assert.match(h.text('lrcProjectionCaption'), /Data Center melebihi roadmap 1 FM\.$/);
    assert.match(h.html('lrcProjectionSummary'), /<p class="lrc-formula-note lrc-formula-warning">Proyeksi FM S3 2 melebihi roadmap S3 1\. Periksa proyeksi lulus S3 dan status pendidikan FM di bawah, atau roadmap DKV 2027\.<\/p>$/);
    assert.equal(h.rows().length, 3);
    assert.equal(h.text('lrcProjectionMeta'), '2 FM Data Center + 0 New FM Recruit = 2 FM');
});

test('back returns to the programs of the year, and closing restores the focus', () => {
    const h = harness();
    h.document.activeElement = h.years[0];
    h.years[0].fire('click');
    h.element('lrcProjectionTable').fire('click', { target: h.programButton('CS') });
    // 2026: C2 still On Going S3, the recruit is S2.
    assert.deepEqual(h.rows()[2].slice(-1), ['On Going S3']);
    assert.equal(h.rows().length, 6);
    const csButton = h.programButton('CS');
    h.element('lrcProjectionTable').selectors['[data-lrc-projection-prodi="CS"]'] = csButton;
    h.element('lrcProjectionBack').fire('click');
    assert.equal(h.text('lrcProjectionTitle'), 'Proyeksi FM S3 2026');
    assert.equal(h.element('lrcProjectionBack').hidden, true);
    assert.equal(h.document.activeElement, csButton);
    // Another year while open keeps the dialog open.
    h.years[1].fire('click');
    assert.equal(h.dialog.showCount, 1);
    h.element('lrcProjectionClose').fire('click');
    assert.equal(h.dialog.open, false);
    assert.ok(!h.bodyClasses.has('pd-modal-open'));
    assert.equal(h.document.activeElement, h.years[0]);
});
