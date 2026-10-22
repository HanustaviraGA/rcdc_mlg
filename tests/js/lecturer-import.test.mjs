import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../Modules/Dosen/resources/views/import_javascript.blade.php', import.meta.url), 'utf8')
    .replace(/<\/?script[^>]*>/g, '')
    .replace(/\{\{\s*route\('([^']+)'\)\s*\}\}/g, '$1')
    .replace(/\{\{\s*csrf_token\(\)\s*\}\}/g, 'csrf-token');

function harness() {
    const elements = new Map();
    const element = id => {
        if (!elements.has(id)) elements.set(id, { textContent: '', disabled: false, value: '', files: [], reportValidity: () => true });
        return elements.get(id);
    };
    const selections = [];
    const requests = [];
    const values = {};
    const jquery = selector => ({
        addClass() { return this; }, removeClass() { return this; }, prop() { return this; },
        val(value) { if (value !== undefined) values[selector] = value; return values[selector]; },
    });
    jquery.ajax = options => {
        const request = { options, aborted: false, abort() { this.aborted = true; } };
        requests.push(request);
        return request;
    };
    class FormDataMock {
        constructor() { this.values = new Map(); }
        append(key, value) { this.values.set(key, value); }
    }
    const context = vm.createContext({
        document: { getElementById: element, querySelectorAll: () => selections },
        $: jquery, FormData: FormDataMock,
        SUPER: { showMessage() {} }, init_table() {},
    });
    vm.runInContext(source, context);
    // Rendering is exercised by the backend preview tests; here we test workflow state.
    context.renderDosenWorkbookPreview = () => {};
    const preview = { sheet: 'Malang', header_row: 1, headers: ['ID', 'Person'], preview_data: [['D1', 'Person']],
        mapping_config: { required: { kode_dosen: 'Kode Dosen', nama_dosen: 'Nama Dosen' }, optional: {} }, suggested_mapping: {} };
    const map = (code = '0', name = '1') => {
        selections.splice(0, selections.length,
            { dataset: { field: 'kode_dosen' }, value: code },
            { dataset: { field: 'nama_dosen' }, value: name });
    };
    const ready = () => {
        const file = { name: 'lecturers.xlsx' };
        element('dosen').files = [file];
        context.dosenImportState.file = file;
        context.dosenImportState.preview = preview;
        map();
        return file;
    };
    return { context, element, requests, preview, map, ready, selections };
}

test('required index zero is valid; missing and duplicate selections cannot be confirmed', () => {
    const h = harness();
    h.ready();
    h.context.updateDosenImportControls();
    assert.equal(h.element('dosenMappingConfirm').disabled, false);
    assert.equal(h.element('dosenImportSubmit').disabled, true);
    h.context.confirmDosenMapping();
    assert.equal(h.element('dosenImportSubmit').disabled, false);
    h.map('', '1');
    h.context.dosenImportState.confirmed = false;
    h.context.confirmDosenMapping();
    assert.equal(h.context.dosenImportState.confirmed, false);
    assert.match(h.element('dosenMappingStatus').textContent, /Kode Dosen/);
    h.map('0', '0');
    assert.equal(h.context.validateDosenMapping(), false);
});

test('required-only upload ignores every unselected optional mapping', () => {
    const h = harness();
    h.ready();
    h.preview.mapping_config.optional = { jja: 'JJA', email_1: 'Email 1', note: 'NOTE' };
    h.selections.push(...Object.keys(h.preview.mapping_config.optional).map(field => ({ dataset: { field }, value: '' })));
    h.context.confirmDosenMapping();
    assert.equal(h.context.dosenImportState.confirmed, true);
    assert.equal(h.element('dosenImportSubmit').disabled, false);
    h.context.onAdd();
    assert.equal(h.requests.length, 1);
    assert.deepEqual(JSON.parse(h.requests[0].options.data.values.get('column_mapping')), { kode_dosen: 0, nama_dosen: 1 });
});

test('mapped optional column with blank preview values does not block upload and remains in mapping', () => {
    const h = harness();
    h.ready();
    h.preview.headers.push('Rank', 'Email');
    h.preview.preview_data = [['D1', 'Person', '', null]];
    h.preview.mapping_config.optional = { jja: 'JJA', email_1: 'Email 1', note: 'NOTE' };
    h.selections.push(
        { dataset: { field: 'jja' }, value: '2' },
        { dataset: { field: 'email_1' }, value: '3' },
        { dataset: { field: 'note' }, value: '' });
    assert.equal(h.context.dosenPreviewValue(h.preview.preview_data[0], 2), '');
    assert.equal(h.context.dosenPreviewValue(h.preview.preview_data[0], 3), '');
    h.context.confirmDosenMapping();
    assert.equal(h.element('dosenImportSubmit').disabled, false);
    h.context.onAdd();
    assert.equal(h.requests.length, 1);
    assert.deepEqual(JSON.parse(h.requests[0].options.data.values.get('column_mapping')), {
        kode_dosen: 0, nama_dosen: 1, jja: 2, email_1: 3,
    });
});

test('replacement file invalidates confirmation and stale preview cannot overwrite the replacement', () => {
    const h = harness();
    h.ready();
    h.context.confirmDosenMapping();
    h.context.previewDosenWorkbook();
    const first = h.requests[0];
    assert.equal(h.context.dosenImportState.confirmed, false);
    assert.equal(h.element('dosenImportSubmit').disabled, true);
    h.element('dosen').files = [{ name: 'replacement.xlsx' }];
    h.context.previewDosenWorkbook();
    assert.equal(first.aborted, true);
    first.options.success({ ...h.preview, sheet: 'Stale sheet' });
    first.options.complete();
    assert.equal(h.context.dosenImportState.preview, null);
    assert.equal(h.context.dosenImportState.loading, true);
    h.requests[1].options.success(h.preview);
    h.requests[1].options.complete();
    assert.equal(h.context.dosenImportState.preview.sheet, 'Malang');
    assert.equal(h.context.dosenImportState.loading, false);
    assert.equal(h.element('dosenImportSubmit').disabled, true);
});

test('failed preview stays unconfirmed and retry can recover', () => {
    const h = harness();
    h.ready();
    h.context.previewDosenWorkbook();
    h.requests[0].options.error({ responseJSON: { errors: { dosen: ['File invalid'] } } }, 'error');
    h.requests[0].options.complete();
    assert.equal(h.element('dosenPreviewStatus').textContent, 'File invalid');
    assert.equal(h.element('dosenImportSubmit').disabled, true);
    h.context.previewDosenWorkbook();
    h.requests[1].options.success(h.preview);
    h.requests[1].options.complete();
    h.context.confirmDosenMapping();
    assert.equal(h.element('dosenImportSubmit').disabled, false);
});

test('confirmed import submits selected column mapping and year/month, with no second concurrent upload', () => {
    const h = harness();
    const file = h.ready();
    h.element('dosenImportYear').value = '2025';
    h.element('dosenImportMonth').value = '8';
    h.context.confirmDosenMapping();
    h.context.onAdd();
    h.context.onAdd();
    assert.equal(h.requests.length, 1);
    const request = h.requests[0].options;
    assert.equal(request.url, 'dosen.read');
    assert.equal(request.headers['X-CSRF-TOKEN'], 'csrf-token');
    assert.equal(request.data.values.get('dosen'), file);
    assert.equal(request.data.values.get('year'), '2025');
    assert.equal(request.data.values.get('month'), '8');
    assert.equal(request.data.values.get('header_row'), 1);
    assert.deepEqual(JSON.parse(request.data.values.get('column_mapping')), { kode_dosen: 0, nama_dosen: 1 });
});

test('historical upload success retains the current table filter instead of switching to history', () => {
    const h = harness();
    h.ready();
    h.element('dosenImportYear').value = '2025';
    h.element('dosenImportMonth').value = '8';
    const years = h.element('dosenAvailableYears');
    years.options = [];
    years.appendChild = option => years.options.push(option);
    h.context.Option = class { constructor(label, value) { this.label = label; this.value = value; } };
    h.context.confirmDosenMapping();
    h.context.onAdd();
    h.requests[0].options.success({ success: true, summary: {} });
    h.requests[0].options.complete();
    assert.equal(years.options[0].value, '2025');
    assert.equal(h.context.dosenImportState.confirmed, false);
    assert.equal(h.element('dosenImportSubmit').disabled, true);
    assert.equal(h.element('dosenImportYear').value, '2025');
});

test('reopening the backoffice page does not redeclare lexical state or accept an old response', () => {
    const h = harness();
    h.ready();
    h.context.previewDosenWorkbook();
    const pending = h.requests[0];
    vm.runInContext(source, h.context);
    assert.equal(pending.aborted, true);
    pending.options.success(h.preview);
    pending.options.complete();
    assert.equal(h.context.dosenImportState.preview, null);
    assert.equal(h.context.dosenImportState.confirmed, false);
});
