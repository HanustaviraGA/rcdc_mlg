import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../Modules/RoadmapLRC/resources/views/upload_javascript.blade.php', import.meta.url), 'utf8')
    .replace(/<\/?script[^>]*>/g, '');

function element(attrs = {}) {
    return {
        dataset: {}, disabled: false, hidden: false, checked: false, innerHTML: '', textContent: '', listeners: {},
        addEventListener(name, listener) { this.listeners[name] = listener; },
        fire(name) {
            const event = { prevented: false, preventDefault() { this.prevented = true; } };
            return { event, done: this.listeners[name]?.(event) };
        },
        ...attrs,
    };
}

function harness() {
    const file = element({ files: [{ name: 'roadmap.xlsx', size: 42560 }] });
    const form = element({ dataset: { previewUrl: '/preview', importUrl: '/import' }, reset() { file.files = []; } });
    const previewButton = element();
    const importButton = element({ disabled: true });
    const confirmed = element();
    const confirmation = element({ hidden: true });
    const preview = element();
    const status = element();
    const error = element({ hidden: true });
    const modal = element();
    const table = element({ innerHTML: 'previous table' });
    const period = element();
    const pageStatus = element();
    const nodes = {
        '#roadmapUploadForm': form, '#roadmapUploadFile': file, '#roadmapUploadPreviewButton': previewButton,
        '#roadmapUploadImportButton': importButton, '#roadmapUploadConfirmed': confirmed,
        '#roadmapUploadConfirmation': confirmation, '#roadmapUploadPreview': preview,
        '#roadmapUploadStatus': status, '#roadmapUploadError': error, '#roadmapUploadModal': modal,
        '[data-roadmap-table]': table, '[data-roadmap-period]': period, '[data-roadmap-status]': pageStatus,
    };
    const root = { dataset: { csrf: 'token' }, querySelector(selector) { return nodes[selector]; } };
    const requests = [];
    let hidden = 0;
    class FormData {
        fields = {};
        append(key, value) { this.fields[key] = value; }
    }
    vm.runInNewContext(source, {
        document: { getElementById: () => root }, FormData,
        bootstrap: { Modal: { getOrCreateInstance: () => ({ hide() { hidden++; } }) } },
        fetch: (url, options) => new Promise((resolve, reject) => requests.push({
            url, options, fields: options.body.fields,
            respond(data, ok = true) { resolve({ ok, json: async () => data }); }, reject,
        })),
    });
    return { file, form, previewButton, importButton, confirmed, confirmation, preview, status, error,
        modal, table, period, pageStatus, requests, hidden: () => hidden };
}

async function ready(h) {
    const { done } = h.form.fire('submit');
    h.requests.at(-1).respond({ preview_hash: 'hash', preview_html: '<table>preview</table>' });
    await done;
}

test('preview sends the selected file and enables import only after confirmation', async () => {
    const h = harness();
    const pending = h.form.fire('submit');
    assert.equal(h.file.disabled, true);
    assert.equal(h.previewButton.disabled, true);
    assert.equal(h.importButton.disabled, true);
    assert.equal(h.modal.fire('hide.bs.modal').event.prevented, true);
    await h.form.fire('submit').done;
    assert.equal(h.requests.length, 1);
    assert.equal(h.requests[0].url, '/preview');
    assert.equal(h.requests[0].fields.roadmap, h.file.files[0]);
    assert.equal(h.requests[0].options.headers['X-CSRF-TOKEN'], 'token');
    h.requests[0].respond({ preview_hash: 'hash', preview_html: '<table>preview</table>' });
    await pending.done;
    assert.equal(h.file.disabled, false);
    assert.equal(h.preview.innerHTML, '<table>preview</table>');
    assert.equal(h.confirmation.hidden, false);
    assert.equal(h.importButton.disabled, true);
    h.confirmed.checked = true;
    h.confirmed.fire('change');
    assert.equal(h.importButton.disabled, false);
    assert.equal(h.modal.fire('hide.bs.modal').event.prevented, false);
});

test('changing files clears the preview and confirmation and blocks stale imports', async () => {
    const h = harness();
    await ready(h);
    h.confirmed.checked = true;
    h.confirmed.fire('change');
    h.file.files = [{ name: 'other.xlsx', size: 100 }];
    h.file.fire('change');
    assert.equal(h.preview.innerHTML, '');
    assert.equal(h.confirmed.checked, false);
    assert.equal(h.confirmation.hidden, true);
    assert.equal(h.importButton.disabled, true);
    await h.importButton.fire('click').done;
    assert.equal(h.requests.length, 1);
});

test('import sends the preview hash, refreshes the table, and resets the form', async () => {
    const h = harness();
    await ready(h);
    h.confirmed.checked = true;
    h.confirmed.fire('change');
    const importedFile = h.file.files[0];
    const pending = h.importButton.fire('click');
    assert.equal(h.importButton.disabled, true);
    await h.importButton.fire('click').done;
    assert.equal(h.requests.length, 2);
    assert.equal(h.requests[1].url, '/import');
    assert.equal(h.requests[1].fields.preview_hash, 'hash');
    assert.equal(h.requests[1].fields.confirmed, '1');
    assert.equal(h.requests[1].fields.roadmap, importedFile);
    h.requests[1].respond({ table_html: '<table>updated</table>', period_label: ', 2026–2035', message: 'Upload selesai.' });
    await pending.done;
    assert.equal(h.table.innerHTML, '<table>updated</table>');
    assert.equal(h.period.textContent, ', 2026–2035');
    assert.equal(h.pageStatus.textContent, 'Upload selesai.');
    assert.equal(h.hidden(), 1);
    assert.equal(h.file.files.length, 0);
    assert.equal(h.importButton.disabled, true);
});

test('preview failures remove prior confirmation and never replace the saved table', async () => {
    const h = harness();
    await ready(h);
    h.confirmed.checked = true;
    const pending = h.form.fire('submit');
    h.requests.at(-1).respond({ errors: { roadmap: ['Sheet JJP tidak ditemukan.'] } }, false);
    await pending.done;
    assert.equal(h.error.hidden, false);
    assert.equal(h.error.textContent, 'Sheet JJP tidak ditemukan.');
    assert.equal(h.preview.innerHTML, '');
    assert.equal(h.confirmed.checked, false);
    assert.equal(h.importButton.disabled, true);
    assert.equal(h.table.innerHTML, 'previous table');
});

test('failed import keeps the preview available for retry and the saved table intact', async () => {
    const h = harness();
    await ready(h);
    h.confirmed.checked = true;
    const pending = h.importButton.fire('click');
    h.requests.at(-1).reject(new Error('Network error'));
    await pending.done;
    assert.equal(h.error.textContent, 'Network error');
    assert.equal(h.importButton.disabled, false);
    assert.equal(h.preview.innerHTML, '<table>preview</table>');
    assert.equal(h.table.innerHTML, 'previous table');
    assert.equal(h.hidden(), 0);
});

test('unsupported and oversized files are rejected before requests are sent', async () => {
    for (const file of [{ name: 'bad.csv', size: 10 }, { name: 'huge.xlsx', size: 21 * 1024 * 1024 }]) {
        const h = harness();
        h.file.files = [file];
        await h.form.fire('submit').done;
        assert.equal(h.requests.length, 0);
        assert.equal(h.error.hidden, false);
        assert.equal(h.importButton.disabled, true);
    }
});
