import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const base = '../../Modules/ImportRectorate/resources/views/';
const source = readFileSync(new URL(`${base}javascript.blade.php`, import.meta.url), 'utf8')
    .replace(/<\/?script[^>]*>/g, '')
    .replace(/\{\{\s*route\(["']([^"']+)["']\)\s*\}\}/g, '$1')
    .replace(/\{\{\s*csrf_token\(\)\s*\}\}/g, 'csrf-token');
const markup = ['index', 'column-mapping'].map(name => readFileSync(new URL(`${base}${name}.blade.php`, import.meta.url), 'utf8')).join('\n');

// Small DOM implementation: run the shipped script, including rendering and events,
// without exporting private state or introducing a browser dependency.
class Element {
    constructor(tag = 'div') {
        this.tagName = tag;
        this.children = [];
        this.dataset = {};
        this.attributes = {};
        this.listeners = {};
        this.hidden = false;
        this.disabled = false;
        this.files = [];
        this._value = '';
        this._text = '';
        this._html = '';
        this.className = '';
        const classes = new Set();
        this.classList = {
            add: (...names) => names.forEach(name => classes.add(name)),
            remove: (...names) => names.forEach(name => classes.delete(name)),
            toggle: (name, force) => force ? classes.add(name) : classes.delete(name),
        };
    }
    set value(value) {
        this._value = String(value);
        if (this.tagName === 'select' && !this.children.some(option => option.value === this._value)) this._value = '';
        if (this.type === 'file' && value === '') this.files = [];
    }
    get value() { return this._value; }
    set textContent(value) { this._text = String(value); this._html = ''; }
    get textContent() { return this._text; }
    set innerHTML(value) { this._html = value; this._text = ''; this.children = []; }
    get innerHTML() {
        return this._html || this._text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    append(...children) { children.forEach(child => { child.parent = this; this.children.push(child); }); }
    appendChild(child) { this.append(child); return child; }
    replaceChildren(...children) { this.children = []; this._html = ''; this._text = ''; this.append(...children); }
    after(child) { this.parent.append(child); }
    setAttribute(key, value) { this.attributes[key] = value; }
    addEventListener(name, handler) { (this.listeners[name] ??= []).push(handler); }
    async fire(name, properties = {}) {
        const event = { preventDefault() {}, ...properties };
        for (const handler of this.listeners[name] || []) await handler(event);
        if (name === 'click') await this.onclick?.(event);
    }
    matches(selector) {
        if (selector.startsWith('#')) return this.id === selector.slice(1);
        if (selector.startsWith('.')) return this.className.split(' ').includes(selector.slice(1));
        if (selector === 'input[type="file"]') return this.tagName === 'input' && this.type === 'file';
        const name = selector.match(/^\[name="([^"]+)"\]$/);
        if (name) return this.name === name[1];
        const attr = selector.match(/^\[data-([\w-]+)(?:="([^"]*)")?\]$/);
        if (attr) {
            const key = attr[1].replace(/-([a-z])/g, (_, char) => char.toUpperCase());
            return key in this.dataset && (attr[2] === undefined || String(this.dataset[key]) === attr[2]);
        }
        return this.tagName === selector;
    }
    querySelectorAll(selector) {
        const parts = selector.split(' ');
        const found = [];
        const visit = node => node.children.forEach(child => {
            if (child.matches(parts[0])) {
                if (parts.length === 1) found.push(child);
                else found.push(...child.querySelectorAll(parts.slice(1).join(' ')));
            }
            visit(child);
        });
        visit(this);
        return found;
    }
    querySelector(selector) { return this.querySelectorAll(selector)[0] || null; }
    closest(selector) { return this.matches(selector) ? this : this.parent?.closest(selector); }
    scrollIntoView() {}
}

function harness({ confirmDelete = true } = {}) {
    const document = new Element('document');
    const elements = new Map();
    for (const [, id] of markup.matchAll(/\bid="([^"]+)"/g)) {
        const node = new Element();
        node.id = id;
        elements.set(id, node);
        document.append(node);
    }
    const el = id => elements.get(id);
    const root = el('monthly-import');
    const form = el('monthly-import-form');
    form.tagName = 'form';
    form.action = '/upload';
    const year = el('upload-year');
    year.tagName = 'input';
    year.name = 'year';
    year.value = '2025';
    const month = el('upload-month');
    month.name = 'month';
    month.value = '9';
    form.append(year, month);
    root.append(form, el('monthly-import-errors'), el('monthly-import-summary'), el('monthly-import-history'), el('monthly-import-notice'));
    const inputs = ['fm_file', 'mhs_file', 'research_file'].map(name => {
        const input = el(name);
        input.tagName = 'input';
        input.type = 'file';
        input.name = name;
        const container = new Element();
        container.className = 'mb-6';
        const button = el(`${name}-submit`);
        button.tagName = 'button';
        button.dataset.importFile = name;
        button.innerHTML = `Upload dan import ${name}`;
        container.append(input, button);
        form.append(container);
        return input;
    });
    el('cm-preview-table').append(el('cm-preview-headers'), el('cm-preview-body'));
    document.getElementById = id => el(id) || null;
    document.createElement = tag => new Element(tag);
    const requests = [];
    const confirmations = [];
    class FormDataMock {
        constructor() { this.values = new Map(); }
        append(key, value) { this.values.set(key, value); }
    }
    const modal = {
        shown: false,
        show() { this.shown = true; },
        hide() { this.shown = false; el('column-mapping-modal').fire('hide.bs.modal'); },
    };
    vm.runInNewContext(source, {
        document, AbortController, FormData: FormDataMock,
        window: { confirm(message) { confirmations.push(message); return confirmDelete; } },
        bootstrap: { Modal: { getOrCreateInstance: () => modal } },
        fetch(url, options) {
            return new Promise((resolve, reject) => requests.push({
                url, options, reject,
                respond(data, ok = true) { resolve({ ok, json: async () => data }); },
            }));
        },
    });
    const flush = async () => { for (let i = 0; i < 8; i++) await Promise.resolve(); };
    const choose = async (name, file = { name: 'report.xlsx' }) => {
        el(name).files = file ? [file] : [];
        await el(name).fire('change');
        return file;
    };
    const selects = () => el('cm-mapping-rows').querySelectorAll('select');
    const change = async (field, value) => {
        const select = selects().find(select => select.dataset.field === field);
        select.value = value;
        await select.fire('change');
    };
    const reopen = async name => {
        // A click starts an async fetch; do not wait for its response here.
        void el(name).closest('.mb-6').querySelector('.cm-reopen-link').fire('click');
        await flush();
    };
    const confirm = () => el('cm-confirm-btn').fire('click');
    const button = (name = 'fm_file') => el(`${name}-submit`);
    const submit = (name = 'fm_file') => form.fire('submit', { submitter: button(name) });
    const addDeleteButton = (type = 'fm', period = '2026-09') => {
        const button = new Element('button');
        const labels = { fm: 'Publikasi FM', mhs: 'Publikasi Mahasiswa', hibah: 'Hibah' };
        Object.assign(button.dataset, {
            deleteImport: '', label: labels[type], period,
            url: `/backoffice/importrectorate/imports/${type}/${period.slice(0, 4)}/${Number(period.slice(5))}`,
        });
        button.innerHTML = 'Hapus';
        el('monthly-import-history').append(button);
        const icon = new Element('i');
        button.append(icon);
        return { button, click: () => el('monthly-import-history').fire('click', { target: icon }) };
    };
    return { el, inputs, requests, confirmations, modal, flush, choose, selects, change, reopen, confirm, submit, button, addDeleteButton };
}

const preview = (mapping = { code: 0, title: 1 }) => ({
    success: true, file_info: { name: 'report.xlsx', sheet: 'MALANG' },
    headers: ['Code', 'Title', 'Extra'], preview_data: [['D001', 'Paper', 'value']],
    mapping_config: { required: { code: 'Kode', title: 'Judul' }, optional: { extra: 'Tambahan' } },
    suggested_mapping: mapping,
});

async function ready(h, name = 'fm_file', data = preview()) {
    const file = await h.choose(name);
    h.requests.at(-1).respond(data);
    await h.flush();
    return file;
}

test('initial config validates index zero; optional absence is informational; confirm precedes upload', async () => {
    const h = harness();
    assert.equal(h.button().disabled, true);
    const file = await ready(h);
    assert.equal(h.selects()[0].value, '0');
    assert.equal(h.el('cm-confirm-btn').disabled, false);
    assert.equal(h.el('cm-validation-warnings').hidden, false);
    assert.equal(h.el('cm-validation-errors').hidden, true);
    assert.equal(h.button().disabled, true);
    await h.confirm();
    assert.equal(h.modal.shown, false);
    assert.equal(h.button().disabled, false);
    const uploading = h.submit();
    assert.equal(h.requests.length, 2);
    const request = h.requests[1];
    assert.equal(request.url, '/upload');
    assert.equal(request.options.body.values.get('_token'), 'csrf-token');
    assert.equal(request.options.body.values.get('year'), '2025');
    assert.equal(request.options.body.values.get('month'), '9');
    assert.equal(request.options.body.values.get('fm_file'), file);
    assert.deepEqual(JSON.parse(request.options.body.values.get('column_mapping[fm_file]')), { code: 0, title: 1 });
    await h.submit();
    assert.equal(h.requests.length, 2, 'second concurrent upload blocked');
    request.respond({ summary_html: 'done', history_html: 'history' });
    await uploading;
    assert.equal(h.el('fm_file').files.length, 0);
    assert.equal(h.button().disabled, true);
});

test('missing required and duplicate optional mappings stay blocked after content transition and direct confirm', async () => {
    const h = harness();
    await ready(h, 'fm_file', preview({ code: 0 }));
    assert.equal(h.el('cm-content').hidden, false);
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    await h.confirm();
    assert.equal(h.button().disabled, true);
    await h.change('title', '1');
    assert.equal(h.el('cm-confirm-btn').disabled, false);
    await h.change('extra', '0');
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    assert.match(h.el('cm-validation-error-list').innerHTML, /kolom sumber yang sama/);
    await h.confirm();
    assert.equal(h.button().disabled, true);
    await h.change('extra', '');
    await h.confirm();
    assert.equal(h.button().disabled, false);
    await h.reopen('fm_file');
    await h.change('title', '');
    assert.equal(h.button().disabled, true, 'edits invalidate confirmed mapping');
    await h.submit();
    assert.equal(h.requests.length, 1, 'unconfirmed submit never reaches upload');
});

test('invalid indices are not confirmed, and empty/null suggestions never become index zero', async () => {
    const h = harness();
    await ready(h, 'fm_file', preview({ code: null, title: '' }));
    assert.deepEqual(h.selects().map(select => select.value), ['', '', '']);
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    // Simulate tampered option values, which must still pass integer/range validation.
    for (const value of ['-1', '3', '0.5', 'oops']) {
        const select = h.selects()[0];
        select._value = value;
        await select.fire('change');
        assert.equal(h.el('cm-confirm-btn').disabled, true);
        assert.match(h.el('cm-validation-error-list').innerHTML, /tidak valid/);
    }
});

test('reopen preserves confirmed/manual mapping; same-name replacement invalidates the actual file', async () => {
    const h = harness();
    const oldFile = await ready(h);
    await h.change('code', '2');
    await h.confirm();
    await h.reopen('fm_file');
    assert.equal(h.requests.length, 1, 'confirmed file can reopen cached preview');
    assert.equal(h.selects()[0].value, '2');
    h.modal.hide();
    assert.equal(h.button().disabled, false);
    const replacement = await h.choose('fm_file', { name: oldFile.name });
    assert.notEqual(replacement, oldFile);
    assert.equal(h.button().disabled, true);
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    h.requests[1].respond(preview());
    await h.flush();
    assert.equal(h.selects()[0].value, '0', 'replacement does not inherit saved mapping');
    assert.equal(h.button().disabled, true);
    await h.confirm();
    assert.equal(h.button().disabled, false);
    await h.choose('fm_file', null);
    assert.equal(h.button().disabled, true);
});

test('year changes clear confirmed FM preview, mapping and draft before suggesting the new status column', async () => {
    const h = harness();
    const fmPreview = status => ({
        ...preview({ code: 0, title: 1, status }),
        headers: ['Code', 'Title', 'St2025', 'St2026'],
        mapping_config: { required: { code: 'Kode', title: 'Judul' }, optional: { status: 'Status' } },
    });
    await ready(h, 'fm_file', fmPreview(2));
    assert.equal(h.requests[0].options.body.values.get('year'), '2025');
    await h.confirm();
    await h.reopen('fm_file');
    assert.equal(h.requests.length, 1, 'same-year preview remains cached');
    assert.equal(h.button().disabled, false);

    h.el('upload-year').value = '2026';
    await h.el('upload-year').fire('change');
    assert.equal(h.modal.shown, false, 'active FM preview closes');
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    assert.equal(h.button().disabled, true);
    const badge = h.el('fm_file').closest('.mb-6').querySelector('.cm-status-badge');
    assert.equal(badge.children[0].textContent, 'Pemetaan belum dikonfirmasi.');
    await h.confirm();
    await h.submit();
    assert.equal(h.requests.length, 1, 'old confirmation cannot be submitted');

    await h.reopen('fm_file');
    assert.equal(h.requests.length, 2, 'year change discards cached preview');
    assert.equal(h.requests[1].options.body.values.get('year'), '2026');
    h.requests[1].respond(fmPreview(3));
    await h.flush();
    assert.equal(h.selects().find(select => select.dataset.field === 'status').value, '3', 'new suggestion replaces saved mapping and draft');
    assert.equal(h.button().disabled, true, 'new year requires explicit confirmation');
    await h.confirm();
    assert.equal(h.button().disabled, false);
});

test('year changes abort loading FM previews and ignore late responses for the old year', async () => {
    const h = harness();
    await h.choose('fm_file');
    const oldRequest = h.requests[0];
    h.el('upload-year').value = '2026';
    await h.el('upload-year').fire('change');
    assert.equal(oldRequest.options.signal.aborted, true);
    assert.equal(h.modal.shown, false);
    await h.reopen('fm_file');
    oldRequest.respond(preview({ code: 2, title: 1 }));
    await h.flush();
    assert.equal(h.el('cm-loading').hidden, false, 'late old-year response cannot render');
    h.requests[1].respond(preview());
    await h.flush();
    assert.equal(h.selects()[0].value, '0');
    await h.confirm();
    assert.equal(h.button().disabled, false);
});

test('year changes invalidate only FM while preserving other active previews and confirmations', async () => {
    const h = harness();
    await ready(h);
    await h.confirm();
    await ready(h, 'mhs_file');
    await h.confirm();
    await h.reopen('mhs_file');
    h.el('upload-year').value = '2026';
    await h.el('upload-year').fire('change');
    assert.equal(h.modal.shown, true, 'non-FM preview stays open');
    assert.equal(h.el('cm-confirm-btn').disabled, false);
    assert.equal(h.button().disabled, true, 'FM still needs reconfirmation');
    assert.equal(h.button('mhs_file').disabled, false, 'student upload is independent of FM');
    h.modal.hide();
    await h.reopen('fm_file');
    h.requests[2].respond(preview());
    await h.flush();
    await h.confirm();
    assert.equal(h.button().disabled, false, 'student confirmation was preserved');
    await h.reopen('mhs_file');
    assert.equal(h.requests.length, 3, 'student preview cache was preserved');
});

test('out-of-order replacement and cross-file previews cannot overwrite active content', async () => {
    const h = harness();
    await h.choose('fm_file');
    const first = h.requests[0];
    await h.choose('fm_file', { name: 'replacement.xlsx' });
    assert.equal(first.options.signal.aborted, true);
    const second = h.requests[1];
    await h.choose('mhs_file', { name: 'student.xlsx' });
    assert.equal(second.options.signal.aborted, true);
    // The schema comes entirely from the server, not legacy student NIM fields.
    const student = preview({ request_code: 0 });
    student.mapping_config = { required: { request_code: 'Request Code' }, optional: {} };
    student.file_info.name = 'student.xlsx';
    h.requests[2].respond(student);
    await h.flush();
    first.respond(preview());
    second.respond(preview());
    await h.flush();
    assert.equal(h.el('cm-file-name').textContent, 'student.xlsx');
    assert.deepEqual(h.selects().map(select => select.dataset.field), ['request_code']);
    await h.confirm();
    assert.equal(h.button().disabled, true, 'FM still unconfirmed');
    assert.equal(h.button('mhs_file').disabled, false, 'confirmed student file can upload');
    await h.reopen('fm_file');
    h.requests[3].respond(preview());
    await h.flush();
    await h.confirm();
    assert.equal(h.button().disabled, false);
});

test('closing a loading modal ignores late completion and offers an explicit retry', async () => {
    const h = harness();
    await h.choose('research_file');
    h.modal.hide();
    assert.equal(h.requests[0].options.signal.aborted, true);
    h.requests[0].respond(preview());
    await h.flush();
    assert.equal(h.modal.shown, false);
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    await h.reopen('research_file');
    assert.equal(h.requests.length, 2);
    h.requests[1].respond(preview());
    await h.flush();
    await h.confirm();
    assert.equal(h.button('research_file').disabled, false);
});

test('preview HTTP, network and malformed-config failures are safe and retryable without stale error DOM', async () => {
    const h = harness();
    await h.choose('fm_file');
    h.requests[0].respond({ errors: { file: ['Kolom Kode tidak ditemukan.'] } }, false);
    await h.flush();
    assert.equal(h.el('cm-error').hidden, false);
    assert.equal(h.el('cm-error-message').textContent, 'Kolom Kode tidak ditemukan.');
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    await h.reopen('fm_file');
    h.requests[1].reject(new Error('Network offline'));
    await h.flush();
    assert.equal(h.el('cm-error-message').textContent, 'Network offline');
    await h.reopen('fm_file');
    h.requests[2].respond({ success: true, headers: [] });
    await h.flush();
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    await h.reopen('fm_file');
    h.requests[3].respond(preview());
    await h.flush();
    assert.equal(h.el('cm-confirm-btn').disabled, false);
    await h.confirm();
    const uploading = h.submit();
    h.requests[4].respond({ message: 'Import failed' }, false);
    await uploading;
    assert.equal(h.el('monthly-import-errors').textContent, 'Import failed');
    assert.equal(h.button().disabled, false, 'failed upload can retry confirmed file');
});

for (const name of ['fm_file', 'mhs_file', 'research_file']) {
    test(`${name} uploads independently while other sources have failed or pending previews`, async () => {
        const h = harness();
        const others = h.inputs.filter(input => input.name !== name);
        const file = await ready(h, name);
        await h.confirm();
        await h.choose(others[0].name);
        h.requests.at(-1).respond({ message: 'Invalid workbook' }, false);
        await h.flush();
        await h.choose(others[1].name);
        const pending = h.requests.at(-1);
        assert.equal(h.button(name).disabled, false, 'other previews do not block this upload');
        const uploading = h.submit(name);
        const request = h.requests.at(-1);
        assert.equal(pending.options.signal.aborted, true);
        assert.equal(request.url, '/upload');
        assert.equal(request.options.body.values.get(name), file);
        assert.deepEqual([...request.options.body.values.keys()].sort(), [
            '_token', 'year', 'month', name, `column_mapping[${name}]`,
        ].sort());
        assert.ok(h.inputs.every(input => input.disabled), 'file selections stay stable during upload');
        assert.equal(h.el('upload-year').disabled, true);
        assert.equal(h.el('upload-month').disabled, true);
        pending.respond(preview());
        request.respond({ summary_html: `done ${name}`, history_html: `history ${name}` });
        await uploading;
        assert.equal(h.el(name).files.length, 0);
        assert.ok(others.every(input => input.files.length === 1), 'other file selections are preserved');
        assert.ok(others.every(input => h.button(input.name).disabled), 'other mappings still require confirmation');
        assert.ok(h.inputs.every(input => !input.disabled));
        assert.equal(h.el('upload-year').disabled, false);
        assert.equal(h.el('monthly-import-summary').innerHTML, `done ${name}`);
        assert.equal(h.el('monthly-import-history').innerHTML, `history ${name}`);
    });
}

test('sequential uploads preserve other confirmed files and manual mappings, including after a failure', async () => {
    const h = harness();
    const files = new Map();
    for (const name of ['fm_file', 'mhs_file', 'research_file']) {
        files.set(name, await ready(h, name));
        await h.change('code', '2');
        await h.confirm();
    }
    let uploading = h.submit('mhs_file');
    h.requests.at(-1).respond({ message: 'Student import failed' }, false);
    await uploading;
    for (const input of h.inputs) {
        assert.equal(input.files[0], files.get(input.name), 'failed import retains every file');
        assert.equal(h.button(input.name).disabled, false);
        assert.equal(h.button(input.name).innerHTML, `Upload dan import ${input.name}`);
    }
    for (const name of ['fm_file', 'mhs_file', 'research_file']) {
        uploading = h.submit(name);
        const request = h.requests.at(-1);
        assert.equal(request.options.body.values.get(name), files.get(name));
        assert.deepEqual(JSON.parse(request.options.body.values.get(`column_mapping[${name}]`)), { code: 2, title: 1 });
        assert.equal([...request.options.body.values.keys()].filter(key => key.endsWith('_file')).length, 1);
        request.respond({ summary_html: name, history_html: name });
        await uploading;
        assert.equal(h.button(name).disabled, true);
    }
    assert.ok(h.inputs.every(input => input.files.length === 0));
});

test('a confirmed source cannot submit an unconfirmed source or a form without a source button', async () => {
    const h = harness();
    await ready(h);
    await h.confirm();
    await ready(h, 'mhs_file');
    await h.submit('mhs_file');
    await h.submit('research_file');
    await h.submit(null);
    assert.equal(h.requests.length, 2, 'invalid submissions never upload any source');
    assert.equal(h.button().disabled, false);
    assert.equal(h.el('monthly-import-errors').hidden, false);
});

test('every referenced modal ID exists in the owned views; optional hint uses informational styling', () => {
    const ids = new Set([...markup.matchAll(/\bid="([^"]+)"/g)].map(match => match[1]));
    for (const [, id] of source.matchAll(/getElementById\('([^']+)'\)/g)) assert.ok(ids.has(id), `missing ${id}`);
    assert.match(markup, /id="cm-validation-warnings" class="alert alert-info/);
    assert.match(markup, /Kolom opsional boleh kosong dan tidak menghalangi import/);
});

test('cancelled deletion names the source and period without making a request', async () => {
    const h = harness({ confirmDelete: false });
    const deletion = h.addDeleteButton('mhs', '2025-12');
    await deletion.click();
    assert.equal(h.confirmations.length, 1);
    assert.match(h.confirmations[0], /Publikasi Mahasiswa periode 2025-12/);
    assert.equal(h.requests.length, 0);
    assert.equal(deletion.button.disabled, false);
});

test('deletion uses the clicked row, blocks concurrent mutations and retains selected files and mappings', async () => {
    const h = harness();
    const file = await ready(h);
    await h.confirm();
    h.el('upload-year').value = '2030';
    h.el('upload-month').value = '1';
    const first = h.addDeleteButton('hibah', '2026-09');
    const other = h.addDeleteButton('mhs', '2026-08');
    const deleting = first.click();
    const request = h.requests.at(-1);
    assert.equal(request.url, '/backoffice/importrectorate/imports/hibah/2026/9');
    assert.equal(request.options.method, 'DELETE');
    assert.equal(request.options.headers['X-CSRF-TOKEN'], 'csrf-token');
    assert.equal(request.options.headers.Accept, 'application/json');
    assert.equal(first.button.disabled, true);
    assert.equal(other.button.disabled, true);
    assert.equal(h.button().disabled, true);
    assert.ok(h.inputs.every(input => input.disabled));
    await other.click();
    await h.submit();
    assert.equal(h.requests.length, 2, 'upload and second deletion are blocked');
    request.respond({ message: 'Hibah periode 2026-09 berhasil dihapus.', history_html: 'remaining sources' });
    await deleting;
    assert.equal(h.el('monthly-import-history').innerHTML, 'remaining sources');
    assert.equal(h.el('monthly-import-notice').hidden, false);
    assert.match(h.el('monthly-import-notice').textContent, /Hibah periode 2026-09/);
    assert.equal(h.el('fm_file').files[0], file);
    assert.equal(h.button().disabled, false, 'confirmed mapping survives deletion');
    assert.ok(h.inputs.every(input => !input.disabled));
    assert.equal(h.el('upload-year').disabled, false);

    const next = h.addDeleteButton('mhs');
    const nextDeletion = next.click();
    h.requests.at(-1).respond({ message: 'Deleted', history_html: 'Belum ada laporan bulanan.' });
    await nextDeletion;
    assert.equal(h.el('monthly-import-history').innerHTML, 'Belum ada laporan bulanan.', 'delegated listener survives replacing the history');
});

for (const failure of ['http', 'network']) {
    test(`failed deletion (${failure}) preserves the history and can be retried`, async () => {
        const h = harness();
        const deletion = h.addDeleteButton();
        const deleting = deletion.click();
        const request = h.requests.at(-1);
        if (failure === 'http') request.respond({ message: 'Data import tidak ditemukan.' }, false);
        else request.reject(new Error('Network offline'));
        await deleting;
        assert.equal(h.el('monthly-import-errors').hidden, false);
        assert.equal(h.el('monthly-import-history').children[0], deletion.button);
        assert.equal(deletion.button.disabled, false);
        assert.equal(deletion.button.innerHTML, 'Hapus');
        assert.equal(h.el('monthly-import-notice').hidden, true);
        const retry = deletion.click();
        h.requests.at(-1).respond({ message: 'Deleted', history_html: 'updated' });
        await retry;
        assert.equal(h.el('monthly-import-history').innerHTML, 'updated');
    });
}

test('deletion stays blocked during upload and works after the refreshed history arrives', async () => {
    const h = harness();
    await ready(h);
    await h.confirm();
    const deletion = h.addDeleteButton();
    const uploading = h.submit();
    assert.equal(deletion.button.disabled, true);
    await deletion.click();
    assert.equal(h.confirmations.length, 0);
    assert.equal(h.requests.length, 2);
    h.requests.at(-1).respond({ summary_html: 'uploaded', history_html: 'new history' });
    await uploading;
    const refreshed = h.addDeleteButton();
    const deleting = refreshed.click();
    h.requests.at(-1).respond({ message: 'Deleted', history_html: 'empty' });
    await deleting;
    assert.equal(h.el('monthly-import-summary').innerHTML, '');
    assert.equal(h.el('monthly-import-history').innerHTML, 'empty');
});
