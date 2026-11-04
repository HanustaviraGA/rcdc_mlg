import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../Modules/CharacterBuilding/resources/views/javascript.blade.php', import.meta.url), 'utf8')
    .replace(/<\/?script[^>]*>/g, '');

// A small DOM: elements are registered by the selectors the script asks for.
class Element {
    constructor(tag = 'DIV', attrs = {}) {
        this.tagName = tag;
        this.dataset = {};
        this.attributes = {};
        this.listeners = {};
        this.children = [];
        this.hidden = false;
        this.disabled = false;
        this.className = '';
        this.textContent = '';
        this.innerHTML = '';
        this._value = '';
        Object.assign(this, attrs);
    }
    get options() { return this.children; }
    set value(value) { this._value = String(value); }
    get value() { return this._value; }
    append(child) { this.children.push(child); }
    setAttribute(key, value) { this.attributes[key] = value; }
    removeAttribute(key) { delete this.attributes[key]; }
    addEventListener(name, handler) { (this.listeners[name] ??= []).push(handler); }
    async fire(name, event = {}) {
        for (const handler of this.listeners[name] || []) await handler({ preventDefault() {}, target: this, ...event });
    }
    closest(selector) { return this.matches?.(selector) ? this : null; }
    focus() {}
}

function select(values, value) {
    const element = new Element('SELECT');
    values.forEach(option => element.append(new Element('OPTION', { value: option })));
    element.value = value;
    return element;
}

function harness({ confirm = true } = {}) {
    const registry = new Map();
    const root = new Element('DIV', { dataset: { createUrl: '/create', updateUrl: '/update', deleteUrl: '/delete', readUrl: '/read', csrf: 'token' } });
    root.querySelector = selector => registry.get(selector) ?? null;
    root.querySelectorAll = selector => [...registry.entries()].filter(([key]) => key.startsWith(selector.slice(0, -1))).map(([, element]) => element);
    const forms = {};
    const add = (category, fields) => {
        const form = new Element('FORM', { dataset: { cbTargetForm: category }, elements: { ...fields, target: new Element('INPUT'), score: new Element('INPUT') } });
        const submit = new Element('BUTTON');
        const cancel = new Element('BUTTON', { hidden: true });
        form.querySelector = selector => ({ '[data-cb-submit]': submit, '[data-cb-cancel]': cancel })[selector];
        registry.set(`[data-cb-target-form="${category}"]`, form);
        registry.set(`[data-cb-target-status="${category}"]`, new Element());
        registry.set(`[data-cb-target-list="${category}"]`, new Element());
        forms[category] = { form, submit, cancel };
    };
    add('publikasi', { year: select(['2026', '2025'], '2026'), quarter: select(['1', '2', '3', '4'], '4') });
    add('pengajaran', { academic_year: select(['2026/2027', '2025/2026'], '2026/2027'), semester: select(['Ganjil', 'Genap'], 'Ganjil') });
    for (const tab of ['publikasi', 'comdev']) {
        registry.set(`[data-cb-filter="${tab}"]`, new Element('FORM', { dataset: { cbFilter: tab }, elements: { year: select(['2026'], '2026'), quarter: select(['1', '2', '3', '4'], '3') } }));
        registry.set(`[data-cb-panel="${tab}"]`, new Element());
        registry.set(`[data-cb-panel-error="${tab}"]`, new Element('DIV', { hidden: true }));
    }
    const requests = [];
    const confirmations = [];
    class FormData {
        constructor() { this.values = new Map(); }
        append(key, value) { this.values.set(key, String(value)); }
    }
    const document = {
        getElementById: id => (id === 'characterBuilding' ? root : null),
        createElement: tag => new Element(tag.toUpperCase()),
    };
    vm.runInNewContext(source, {
        document, FormData,
        window: { confirm(message) { confirmations.push(message); return confirm; } },
        fetch: (url, options) => new Promise(resolve => requests.push({
            url, fields: Object.fromEntries(options.body.values),
            respond(data, ok = true) { resolve({ ok, json: async () => data }); },
        })),
    });
    const flush = async () => { for (let i = 0; i < 10; i++) await Promise.resolve(); };
    const button = dataset => {
        const element = new Element('BUTTON', { dataset });
        element.matches = selector => selector === (dataset.id && 'cbEdit' in dataset ? '[data-cb-edit]' : '[data-cb-delete]');
        return element;
    };
    return { root, registry, forms, requests, confirmations, flush, button, el: selector => registry.get(selector) };
}

test('a new target is created for its period, replaces the list and refreshes its progress tab', async () => {
    const h = harness();
    const { form, submit } = h.forms.publikasi;
    form.elements.quarter.value = '3';
    form.elements.target.value = '5';
    form.elements.score.value = '6';
    const saving = form.fire('submit');
    assert.equal(submit.disabled, true);
    const [request] = h.requests;
    assert.equal(request.url, '/create');
    assert.deepEqual(request.fields, { _token: 'token', target: '5', score: '6', year: '2026', quarter: '3', category: 'publikasi' });
    request.respond({ message: 'Target Publikasi 2026 Q3 disimpan.', category: 'publikasi', list_html: '<table>2026 Q3</table>' });
    await saving;
    assert.equal(submit.disabled, false);
    assert.equal(h.el('[data-cb-target-list="publikasi"]').innerHTML, '<table>2026 Q3</table>');
    assert.equal(h.el('[data-cb-target-status="publikasi"]').textContent, 'Target Publikasi 2026 Q3 disimpan.');
    assert.match(h.el('[data-cb-target-status="publikasi"]').className, /text-success/);
    assert.equal(form.elements.target.value, '');
    assert.equal(form.elements.score.value, '');
    // The Publikasi tab shows the new target.
    assert.deepEqual(h.requests[1].fields, { _token: 'token', tab: 'publikasi', year: '2026', quarter: '3' });
    h.requests[1].respond({ html: '<p>panel</p>' });
    await h.flush();
    assert.equal(h.el('[data-cb-panel="publikasi"]').innerHTML, '<p>panel</p>');
});

test('editing fills the form, adds an older academic year and updates by id', async () => {
    const h = harness();
    const { form, submit, cancel } = h.forms.pengajaran;
    await h.root.fire('click', { target: h.button({ cbEdit: '', category: 'pengajaran', id: '7', year: '2023', term: '2', target: '12', score: '80', label: '2023/2024 Genap' }) });
    assert.equal(form.elements.academic_year.value, '2023/2024');
    assert.ok(form.elements.academic_year.options.some(option => option.value === '2023/2024'));
    assert.equal(form.elements.semester.value, 'Genap');
    assert.equal(form.elements.target.value, '12');
    assert.equal(form.elements.score.value, '80');
    assert.equal(submit.textContent, 'Simpan perubahan');
    assert.equal(cancel.hidden, false);
    const saving = form.fire('submit');
    assert.equal(h.requests[0].url, '/update');
    assert.deepEqual(h.requests[0].fields, { _token: 'token', target: '12', score: '80', academic_year: '2023/2024', semester: 'Genap', id: '7', _method: 'PUT' });
    h.requests[0].respond({ message: 'Target Pengajaran 2023/2024 Genap diperbarui.', category: 'pengajaran', list_html: 'list' });
    await saving;
    assert.equal(form.dataset.editId, undefined);
    assert.equal(submit.textContent, 'Simpan target');
    assert.equal(cancel.hidden, true);
    assert.equal(h.requests.length, 1, 'Pengajaran has no progress panel to refresh');
});

test('a rejected target shows the validation message and keeps the form usable', async () => {
    const h = harness();
    const { form, submit } = h.forms.publikasi;
    form.elements.target.value = '5';
    form.elements.score.value = '7';
    const saving = form.fire('submit');
    h.requests[0].respond({ errors: { score: ['Skor Publikasi maksimal 6.'] } }, false);
    await saving;
    assert.equal(h.el('[data-cb-target-status="publikasi"]').textContent, 'Skor Publikasi maksimal 6.');
    assert.match(h.el('[data-cb-target-status="publikasi"]').className, /text-danger/);
    assert.equal(submit.disabled, false);
    assert.equal(form.elements.score.value, '7', 'the values stay for correction');
});

test('deleting asks first, then removes the target and stops editing it', async () => {
    const declined = harness({ confirm: false });
    await declined.root.fire('click', { target: declined.button({ category: 'publikasi', id: '3', label: '2026 Q3' }) });
    assert.equal(declined.requests.length, 0);
    assert.match(declined.confirmations[0], /Hapus target 2026 Q3/);

    const h = harness();
    await h.root.fire('click', { target: h.button({ cbEdit: '', category: 'publikasi', id: '3', year: '2026', term: '3', target: '4', score: '6', label: '2026 Q3' }) });
    const deleting = h.root.fire('click', { target: h.button({ category: 'publikasi', id: '3', label: '2026 Q3' }) });
    assert.deepEqual(h.requests[0].fields, { _token: 'token', id: '3', _method: 'DELETE' });
    h.requests[0].respond({ message: 'Target Publikasi 2026 Q3 dihapus.', category: 'publikasi', list_html: 'empty' });
    await deleting;
    assert.equal(h.el('[data-cb-target-list="publikasi"]').innerHTML, 'empty');
    assert.equal(h.forms.publikasi.form.dataset.editId, undefined);
});

test('a quarter filter loads its panel and ignores an older response', async () => {
    const h = harness();
    const filter = h.el('[data-cb-filter="comdev"]');
    filter.elements.quarter.value = '1';
    void filter.fire('change');
    await h.flush();
    filter.elements.quarter.value = '2';
    void filter.fire('change');
    await h.flush();
    assert.deepEqual(h.requests.map(request => request.fields.quarter), ['1', '2']);
    h.requests[1].respond({ html: 'Q2' });
    await h.flush();
    h.requests[0].respond({ html: 'Q1' });
    await h.flush();
    assert.equal(h.el('[data-cb-panel="comdev"]').innerHTML, 'Q2');
    filter.elements.quarter.value = '3';
    void filter.fire('change');
    await h.flush();
    h.requests[2].respond({ message: 'Server error' }, false);
    await h.flush();
    assert.equal(h.el('[data-cb-panel-error="comdev"]').hidden, false);
    assert.equal(h.el('[data-cb-panel-error="comdev"]').textContent, 'Server error');
    assert.equal(h.el('[data-cb-panel="comdev"]').innerHTML, 'Q2');
});
