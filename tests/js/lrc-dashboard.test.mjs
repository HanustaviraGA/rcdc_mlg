import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/lrc-dashboard.js', import.meta.url), 'utf8');

const data = {
    charts: {
        usia: { labels: ['20-40 tahun', '41-54 tahun'], data: [2, 0] },
        agama: { labels: ['Islam'], data: [1] },
        fakultas: { labels: ['FM'], data: [1] },
    },
    chartDetails: {
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
