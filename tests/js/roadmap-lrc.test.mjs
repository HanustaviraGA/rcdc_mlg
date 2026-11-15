import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../Modules/RoadmapLRC/resources/views/javascript.blade.php', import.meta.url), 'utf8')
    .replace(/<\/?script[^>]*>/g, '');

// A small DOM: elements are registered by the selectors the script asks for.
class Element {
    constructor(attrs = {}) {
        this.dataset = {};
        this.listeners = {};
        this.hidden = false;
        this.disabled = false;
        this.className = '';
        this.textContent = '';
        this.innerHTML = '';
        this.value = '';
        this.focused = 0;
        this.selectors = {};
        Object.assign(this, attrs);
    }
    querySelector(selector) { return this.selectors[selector] ?? null; }
    closest() { return null; }
    addEventListener(name, handler) { (this.listeners[name] ??= []).push(handler); }
    async fire(name, event = {}) {
        const fired = { prevented: false, preventDefault() { this.prevented = true; }, target: this, ...event };
        for (const handler of this.listeners[name] || []) await handler(fired);
        return fired;
    }
    focus() { this.focused++; }
}

// A number input of the table, inside the cell its data-roadmap-cell names.
function numberInput(cell, dataset) {
    const input = new Element({ dataset });
    const td = new Element({ dataset: { roadmapCell: cell } });
    input.closest = selector => ({ '[data-roadmap-edit]': input, '[data-roadmap-cell]': td })[selector] ?? null;
    return input;
}

function harness() {
    const form = new Element({ elements: { nilai: new Element(), catatan: new Element() } });
    const title = new Element();
    const error = new Element({ hidden: true });
    const submit = new Element();
    const modal = new Element({ selectors: { '[data-roadmap-form]': form, '[data-roadmap-modal-title]': title, '[data-roadmap-error]': error, '[data-roadmap-submit]': submit } });
    const status = new Element({ className: 'fs-7' });
    const table = new Element();
    const root = new Element({
        dataset: { updateUrl: '/backoffice/roadmaplrc/update', csrf: 'token' },
        selectors: { '[data-roadmap-modal]': modal, '[data-roadmap-status]': status, '[data-roadmap-table]': table },
    });
    const modals = [];
    const bootstrap = { Modal: { getOrCreateInstance: element => ({
        show() { modals.push(['show', element]); },
        hide() { modals.push(['hide', element]); },
    }) } };
    const requests = [];
    class FormData {
        constructor() { this.values = new Map(); }
        append(key, value) { this.values.set(key, String(value)); }
    }
    vm.runInNewContext(source, {
        document: { getElementById: id => (id === 'roadmapLrc' ? root : null) },
        FormData, bootstrap,
        fetch: (url, options) => new Promise(resolve => requests.push({
            url, method: options.method, fields: Object.fromEntries(options.body.values),
            respond(data, ok = true) { resolve({ ok, json: async () => data }); },
        })),
    });
    const input = numberInput('CS-2026-fm', { id: '11', field: 'fm', label: 'Jumlah FM CS 2026', value: '21', note: '' });
    return { root, modal, form, title, error, submit, status, table, modals, requests, input };
}

test('clicking a number opens the modal with its value and note', async () => {
    const h = harness();
    h.error.hidden = false;
    h.error.textContent = 'old error';
    await h.root.fire('click', { target: numberInput('CS-2026-s3', { id: '11', field: 's3', label: 'S3 CS 2026', value: '3', note: 'Sheet JJP: 4.' }) });
    assert.equal(h.title.textContent, 'Ubah S3 CS 2026');
    assert.equal(h.form.elements.nilai.value, '3');
    assert.equal(h.form.elements.catatan.value, 'Sheet JJP: 4.');
    assert.equal(h.error.hidden, true);
    assert.equal(h.error.textContent, '');
    assert.deepEqual(h.modals, [['show', h.modal]]);
    await h.modal.fire('shown.bs.modal');
    assert.equal(h.form.elements.nilai.focused, 1, 'the value is ready to type');

    // A number without a note clears the previous one.
    await h.root.fire('click', { target: h.input });
    assert.equal(h.form.elements.catatan.value, '');
    // Clicks elsewhere do nothing.
    await h.root.fire('click', { target: new Element() });
    assert.equal(h.modals.length, 2);
});

test('enter or space on a focused number opens the modal, other keys do not', async () => {
    const h = harness();
    const tab = await h.root.fire('keydown', { key: 'Tab', target: h.input });
    assert.equal(tab.prevented, false);
    assert.equal(h.modals.length, 0);
    for (const key of ['Enter', ' ']) {
        const event = await h.root.fire('keydown', { key, target: h.input });
        assert.equal(event.prevented, true);
    }
    assert.equal(h.modals.length, 2);
    assert.equal(h.title.textContent, 'Ubah Jumlah FM CS 2026');
});

test('saving sends the number and note, replaces the table and returns to the new input', async () => {
    const h = harness();
    await h.root.fire('click', { target: h.input });
    h.form.elements.nilai.value = '22';
    h.form.elements.catatan.value = 'Sesuai Data Center.';
    const saving = h.form.fire('submit');
    assert.equal(h.submit.disabled, true);
    await h.form.fire('submit');
    assert.equal(h.requests.length, 1, 'a second submit waits for the first');
    const [request] = h.requests;
    assert.equal(request.url, '/backoffice/roadmaplrc/update');
    assert.equal(request.method, 'POST');
    assert.deepEqual(request.fields, { _token: 'token', id: '11', field: 'fm', nilai: '22', catatan: 'Sesuai Data Center.', _method: 'PUT' });
    request.respond({ message: 'Jumlah FM CS 2026 disimpan: 22.', table_html: '<table>22</table>' });
    await saving;
    assert.equal(h.table.innerHTML, '<table>22</table>');
    assert.equal(h.status.textContent, 'Jumlah FM CS 2026 disimpan: 22.');
    assert.match(h.status.className, /text-success/);
    assert.equal(h.submit.disabled, false);
    assert.deepEqual(h.modals.at(-1), ['hide', h.modal]);

    const rendered = new Element();
    h.table.selectors['[data-roadmap-cell="CS-2026-fm"] [data-roadmap-edit]'] = rendered;
    await h.modal.fire('hidden.bs.modal');
    assert.equal(rendered.focused, 1);
    // Closed, the modal has nothing to save.
    await h.form.fire('submit');
    assert.equal(h.requests.length, 1);
});

test('a rejected number shows the error in the open modal and keeps the values', async () => {
    const h = harness();
    await h.root.fire('click', { target: h.input });
    h.form.elements.nilai.value = '-1';
    const saving = h.form.fire('submit');
    h.requests[0].respond({ message: 'The given data was invalid.', errors: { nilai: ['Nilai minimal 0.'] } }, false);
    await saving;
    assert.equal(h.error.hidden, false);
    assert.equal(h.error.textContent, 'Nilai minimal 0.');
    assert.equal(h.form.elements.nilai.value, '-1');
    assert.equal(h.submit.disabled, false);
    assert.equal(h.table.innerHTML, '');
    assert.deepEqual(h.modals, [['show', h.modal]], 'the modal stays open');

    const forbidden = h.form.fire('submit');
    h.requests[1].respond({ message: 'Silakan login untuk mengubah roadmap.' }, false);
    await forbidden;
    assert.equal(h.error.textContent, 'Silakan login untuk mengubah roadmap.');
});
