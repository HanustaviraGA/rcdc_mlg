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
    replaceChildren(...children) { this.children = []; this.append(...children); }
    after(child) { this.parent.append(child); }
    setAttribute(key, value) { this.attributes[key] = value; }
    addEventListener(name, handler) { (this.listeners[name] ??= []).push(handler); }
    async fire(name) {
        const event = { preventDefault() {} };
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

function harness() {
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
    form.append(year);
    root.append(form, el('monthly-import-submit'), el('monthly-import-errors'), el('monthly-import-summary'), el('monthly-import-history'));
    const inputs = ['fm_file', 'mhs_file', 'research_file'].map(name => {
        const input = el(name);
        input.tagName = 'input';
        input.type = 'file';
        input.name = name;
        const container = new Element();
        container.className = 'mb-6';
        container.append(input);
        form.append(container);
        return input;
    });
    el('cm-preview-table').append(el('cm-preview-headers'), el('cm-preview-body'));
    document.getElementById = id => el(id) || null;
    document.createElement = tag => new Element(tag);
    const requests = [];
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
    const submit = () => form.fire('submit');
    return { el, inputs, requests, modal, flush, choose, selects, change, reopen, confirm, submit };
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
    assert.equal(h.el('monthly-import-submit').disabled, true);
    const file = await ready(h);
    assert.equal(h.selects()[0].value, '0');
    assert.equal(h.el('cm-confirm-btn').disabled, false);
    assert.equal(h.el('cm-validation-warnings').hidden, false);
    assert.equal(h.el('cm-validation-errors').hidden, true);
    assert.equal(h.el('monthly-import-submit').disabled, true);
    await h.confirm();
    assert.equal(h.modal.shown, false);
    assert.equal(h.el('monthly-import-submit').disabled, false);
    const uploading = h.submit();
    assert.equal(h.requests.length, 2);
    const request = h.requests[1];
    assert.equal(request.url, '/upload');
    assert.equal(request.options.body.values.get('fm_file'), file);
    assert.deepEqual(JSON.parse(request.options.body.values.get('column_mapping[fm_file]')), { code: 0, title: 1 });
    await h.submit();
    assert.equal(h.requests.length, 2, 'second concurrent upload blocked');
    request.respond({ summary_html: 'done', history_html: 'history' });
    await uploading;
    assert.equal(h.el('fm_file').files.length, 0);
    assert.equal(h.el('monthly-import-submit').disabled, true);
});

test('missing required and duplicate optional mappings stay blocked after content transition and direct confirm', async () => {
    const h = harness();
    await ready(h, 'fm_file', preview({ code: 0 }));
    assert.equal(h.el('cm-content').hidden, false);
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    await h.confirm();
    assert.equal(h.el('monthly-import-submit').disabled, true);
    await h.change('title', '1');
    assert.equal(h.el('cm-confirm-btn').disabled, false);
    await h.change('extra', '0');
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    assert.match(h.el('cm-validation-error-list').innerHTML, /kolom sumber yang sama/);
    await h.confirm();
    assert.equal(h.el('monthly-import-submit').disabled, true);
    await h.change('extra', '');
    await h.confirm();
    assert.equal(h.el('monthly-import-submit').disabled, false);
    await h.reopen('fm_file');
    await h.change('title', '');
    assert.equal(h.el('monthly-import-submit').disabled, true, 'edits invalidate confirmed mapping');
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
    assert.equal(h.el('monthly-import-submit').disabled, false);
    const replacement = await h.choose('fm_file', { name: oldFile.name });
    assert.notEqual(replacement, oldFile);
    assert.equal(h.el('monthly-import-submit').disabled, true);
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    h.requests[1].respond(preview());
    await h.flush();
    assert.equal(h.selects()[0].value, '0', 'replacement does not inherit saved mapping');
    assert.equal(h.el('monthly-import-submit').disabled, true);
    await h.confirm();
    assert.equal(h.el('monthly-import-submit').disabled, false);
    await h.choose('fm_file', null);
    assert.equal(h.el('monthly-import-submit').disabled, true);
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
    assert.equal(h.el('monthly-import-submit').disabled, false);

    h.el('upload-year').value = '2026';
    await h.el('upload-year').fire('change');
    assert.equal(h.modal.shown, false, 'active FM preview closes');
    assert.equal(h.el('cm-confirm-btn').disabled, true);
    assert.equal(h.el('monthly-import-submit').disabled, true);
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
    assert.equal(h.el('monthly-import-submit').disabled, true, 'new year requires explicit confirmation');
    await h.confirm();
    assert.equal(h.el('monthly-import-submit').disabled, false);
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
    assert.equal(h.el('monthly-import-submit').disabled, false);
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
    assert.equal(h.el('monthly-import-submit').disabled, true, 'FM still needs reconfirmation');
    h.modal.hide();
    await h.reopen('fm_file');
    h.requests[2].respond(preview());
    await h.flush();
    await h.confirm();
    assert.equal(h.el('monthly-import-submit').disabled, false, 'student confirmation was preserved');
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
    assert.equal(h.el('monthly-import-submit').disabled, true, 'FM still unconfirmed');
    await h.reopen('fm_file');
    h.requests[3].respond(preview());
    await h.flush();
    await h.confirm();
    assert.equal(h.el('monthly-import-submit').disabled, false);
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
    assert.equal(h.el('monthly-import-submit').disabled, false);
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
    assert.equal(h.el('monthly-import-submit').disabled, false, 'failed upload can retry confirmed file');
});

test('every referenced modal ID exists in the owned views; optional hint uses informational styling', () => {
    const ids = new Set([...markup.matchAll(/\bid="([^"]+)"/g)].map(match => match[1]));
    for (const [, id] of source.matchAll(/getElementById\('([^']+)'\)/g)) assert.ok(ids.has(id), `missing ${id}`);
    assert.match(markup, /id="cm-validation-warnings" class="alert alert-info/);
    assert.match(markup, /Kolom opsional boleh kosong dan tidak menghalangi import/);
});
