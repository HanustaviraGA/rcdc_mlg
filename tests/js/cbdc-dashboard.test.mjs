import assert from 'node:assert/strict';
import test from 'node:test';
import { comdevRows, initializeCbdc, statusLabel } from '../../public/js/cbdc-dashboard.js';
import * as shared from '../../public/js/publication-performance.js';

const source = {
    label: '2026 Q3',
    publication: {
        period: '2026-9',
        faculty: [
            { code: 'D2', name: 'Budi <b>', rank: 'AA', faculty: 'FM', nonScopus: 0, scopus: 0, firstAuthor: 0, score: 0 },
            { code: 'D1', name: 'Ani', rank: 'L', faculty: 'FM', nonScopus: 1, scopus: 2.5, firstAuthor: 1, score: 4 },
        ],
        publications: [
            { code: 'D1', title: 'Paper B', type: 'Seminar', index: 'Scopus', category: 'Scopus FM', firstAuthor: 'Y', weight: 1.5, quartile: null },
            { code: 'D1', title: 'Paper A', type: 'Jurnal', index: 'Nscopus', category: 'Non Scopus FM', firstAuthor: 'N', weight: 1, quartile: 'Jurnal SINTA 2' },
            { code: 'D2', title: 'Other lecturer', type: 'Jurnal', index: 'Scopus', category: 'Scopus FM', firstAuthor: 'Y', weight: 1 },
        ],
    },
    comdev: {
        semester: '2026/2027 Ganjil',
        lecturers: [
            { code: 'D1', name: 'Ani', activities: 3, score: 5, status: 'Sudah PkM' },
            { code: 'D3', name: 'Citra', activities: 0, score: null, status: null },
            { code: 'D2', name: 'Budi', activities: 2, score: 6, status: '' },
        ],
    },
};

// A small DOM stand-in: elements by id, the two open buttons and the dialogs.
function harness() {
    const elements = new Map();
    const node = (id = '') => ({
        id, hidden: false, textContent: '', innerHTML: '', value: '', dataset: {}, listeners: {},
        addEventListener(name, handler) { (this.listeners[name] ??= []).push(handler); },
        fire(name, event = {}) { (this.listeners[name] || []).forEach(handler => handler({ target: this, ...event })); },
        focus() { document.activeElement = this; },
    });
    const element = id => {
        if (!elements.has(id)) elements.set(id, node(id));
        return elements.get(id);
    };
    for (const id of ['cbdcPublicationDetail', 'cbdcComdevDetail']) {
        Object.assign(element(id), {
            open: false,
            showModal() { if (this.open) throw new Error('InvalidStateError'); this.open = true; },
            close() { this.open = false; this.fire('close'); },
            getBoundingClientRect: () => ({ left: 0, right: 100, top: 0, bottom: 100 }),
        });
    }
    const buttons = ['publikasi', 'comdev'].map(key => Object.assign(node(), { dataset: { cbdcOpen: key } }));
    const bodyClasses = new Set();
    const document = {
        activeElement: null,
        body: { classList: { add: name => bodyClasses.add(name), remove: name => bodyClasses.delete(name) } },
        getElementById: element,
        querySelector: selector => (selector === 'dialog[open]' ? [...elements.values()].find(item => item.open) ?? null : null),
        querySelectorAll: selector => (selector === '[data-cbdc-open]' ? buttons : []),
    };
    initializeCbdc(source, shared, document);
    const target = (selector, dataset) => ({ closest: wanted => (wanted === selector ? { dataset } : null) });

    return { element, buttons, bodyClasses, document, target };
}

const cells = html => [...html.matchAll(/<tbody>(.*)<\/tbody>/gs)][0][1].split('</tr>').filter(Boolean)
    .map(row => [...row.matchAll(/<td[^>]*>(.*?)<\/td>/g)].map(cell => cell[1].replace(/<[^>]+>/g, '')));

test('Community Development lists the lowest scores first and labels a missing status', () => {
    assert.deepEqual(comdevRows(source.comdev.lecturers).map(row => row.code), ['D1', 'D2', 'D3']);
    assert.deepEqual(comdevRows(source.comdev.lecturers, ' belum ').map(row => row.code), ['D3']);
    assert.deepEqual(source.comdev.lecturers.map(statusLabel), ['Sudah PkM', 'Belum PkM', 'Sudah PkM']);
    assert.equal(statusLabel({ activities: null }), 'Belum tersedia');
});

test('Publikasi opens the CBDC lecturers as on the Dashboard Riset, then the publications of one lecturer', () => {
    const h = harness();
    h.buttons[0].focus();
    h.buttons[0].fire('click');
    const dialog = h.element('cbdcPublicationDetail');
    assert.equal(dialog.open, true);
    assert.equal(h.bodyClasses.has('pd-modal-open'), true);
    assert.equal(h.document.activeElement, h.element('cbdcPublicationSearch'));
    const table = h.element('cbdcPublicationTable').innerHTML;
    assert.match(table, /<th scope="col" {2}aria-sort="ascending"><button type="button" data-sort="name">Nama dosen/);
    assert.match(table, /data-faculty="D2">Budi &lt;b&gt;<\/button>/);
    assert.deepEqual(cells(table), [['D1', 'Ani', 'L', 'FM', '1', '2,5', '1', '4'], ['D2', 'Budi &lt;b&gt;', 'AA', 'FM', '0', '0', '0', '0']]);
    assert.match(table, /<tr class="is-zero">/, 'a zero KPI score is flagged as on the Dashboard Riset');
    assert.equal(h.element('cbdcPublicationMeta').textContent, 'Menampilkan 2 dari 2 dosen · 2026 Q3');
    assert.equal(h.element('cbdcFacultySummary').hidden, true);

    // Sorting by Skor KPI, then descending.
    h.element('cbdcPublicationTable').fire('click', { target: h.target('[data-sort]', { sort: 'score' }) });
    h.element('cbdcPublicationTable').fire('click', { target: h.target('[data-sort]', { sort: 'score' }) });
    assert.deepEqual(cells(h.element('cbdcPublicationTable').innerHTML).map(row => row[0]), ['D1', 'D2']);

    h.element('cbdcPublicationTable').fire('click', { target: h.target('[data-faculty]', { faculty: 'D1' }) });
    assert.equal(h.element('cbdcPublicationBack').hidden, false);
    assert.equal(h.element('cbdcPublicationStats').hidden, true);
    assert.equal(h.element('cbdcFacultySummary').hidden, false);
    assert.equal(h.element('cbdcPublicationFilters').hidden, false);
    assert.equal(h.element('cbdcFacultyName').textContent, 'Ani · D1');
    // First author conference, first author journal, non first conference, non first journal, SINTA, KPI.
    assert.equal(h.element('cbdcFacultyCounts').innerHTML, '<tr><td>1</td><td>0</td><td>0</td><td>1</td><td>1</td><td>4</td></tr>');
    assert.match(h.element('cbdcPublicationFilterGrid').innerHTML, /data-paper-filter="type"/);
    assert.deepEqual(cells(h.element('cbdcPublicationTable').innerHTML).map(row => row[0]), ['Paper A', 'Paper B']);
    assert.equal(h.element('cbdcPublicationMeta').textContent, 'Menampilkan 2 dari 2 entri publikasi · 2026 Q3');

    // A publication filter, as on the Dashboard Riset.
    const summary = { textContent: '' };
    const group = {
        dataset: { paperFilter: 'type' }, classList: { toggle() {} },
        querySelectorAll: selector => (selector.endsWith(':checked') ? [{ value: 'Seminar' }] : [{}, {}]),
        querySelector: () => summary,
    };
    h.element('cbdcPublicationFilterGrid').fire('change', { target: { matches: selector => selector === '[data-paper-option]', closest: () => group } });
    assert.equal(summary.textContent, 'Seminar');
    assert.deepEqual(cells(h.element('cbdcPublicationTable').innerHTML).map(row => row[0]), ['Paper B']);
    h.element('cbdcPublicationReset').fire('click');
    assert.equal(cells(h.element('cbdcPublicationTable').innerHTML).length, 2);

    h.element('cbdcPublicationSearch').value = 'paper a';
    h.element('cbdcPublicationSearch').fire('input');
    assert.deepEqual(cells(h.element('cbdcPublicationTable').innerHTML).map(row => row[0]), ['Paper A']);

    h.element('cbdcPublicationBack').fire('click');
    assert.equal(h.element('cbdcPublicationSearch').value, '');
    assert.equal(h.element('cbdcFacultySummary').hidden, true);
    assert.equal(cells(h.element('cbdcPublicationTable').innerHTML).length, 2);

    h.element('cbdcPublicationClose').fire('click');
    assert.equal(dialog.open, false);
    assert.equal(h.bodyClasses.has('pd-modal-open'), false);
    assert.equal(h.document.activeElement, h.buttons[0], 'focus returns to the Detail dosen button');

    // Reopening starts again from the lecturer list.
    h.element('cbdcPublicationTable').fire('click', { target: h.target('[data-faculty]', { faculty: 'D1' }) });
    h.buttons[0].fire('click');
    assert.equal(h.element('cbdcPublicationBack').hidden, true);
});

test('Community Development opens the lecturer list of the Dashboard ComDev with score badges and search', () => {
    const h = harness();
    h.buttons[1].fire('click');
    assert.equal(h.element('cbdcComdevDetail').open, true);
    const table = h.element('cbdcComdevTable').innerHTML;
    assert.match(table, /<th scope="col">Nama dosen<\/th><th scope="col" class="pd-numeric">Jumlah kegiatan<\/th><th scope="col">Skor KPI<\/th><th scope="col">Status<\/th>/);
    assert.match(table, /<td class="cd-name-cell">Ani<small class="cd-lecturer-code">D1<\/small><\/td><td class="pd-numeric">3<\/td><td><span class="pd-score is-warning">Skor <strong>5<\/strong><\/span><\/td><td>Sudah PkM<\/td>/);
    assert.match(table, /<span class="pd-score is-good">Skor <strong>6<\/strong>/);
    assert.match(table, /<span class="pd-score ">Skor <strong>—<\/strong><\/span><\/td><td>Belum PkM<\/td>/);
    assert.equal(h.element('cbdcComdevMeta').textContent, '3 dari 3 dosen ditampilkan');

    h.element('cbdcComdevSearch').value = 'zzz';
    h.element('cbdcComdevSearch').fire('input');
    assert.match(h.element('cbdcComdevTable').innerHTML, /Tidak ada data yang sesuai dengan pencarian/);
    assert.equal(h.element('cbdcComdevMeta').textContent, '0 dari 3 dosen ditampilkan');

    h.element('cbdcComdevDetail').close();
    h.buttons[1].fire('click');
    assert.equal(h.element('cbdcComdevSearch').value, '', 'reopening clears the search');
    assert.equal(h.element('cbdcComdevMeta').textContent, '3 dari 3 dosen ditampilkan');
});
