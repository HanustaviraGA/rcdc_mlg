import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const scripts = ['import_javascript', 'education_import_javascript'].map(name => readFileSync(
    new URL(`../../Modules/Dosen/resources/views/${name}.blade.php`, import.meta.url), 'utf8')
    .replace(/<\/?script[^>]*>/g, '')
    .replace(/\{\{\s*route\('([^']+)'\)\s*\}\}/g, '$1')
    .replace(/\{\{\s*csrf_token\(\)\s*\}\}/g, 'csrf-token'));

function harness() {
    const elements = new Map();
    const element = id => {
        if (!elements.has(id)) elements.set(id, { textContent: '', disabled: false, value: '', files: [], reportValidity: () => true });
        return elements.get(id);
    };
    const selections = [
        { dataset: { field: 'kode_dosen' }, value: '0' },
        { dataset: { field: 'status_pendidikan' }, value: '7' },
    ];
    const requests = [];
    let redraws = 0;
    const jquery = () => ({ addClass() {}, removeClass() {}, prop() {} });
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
        document: { getElementById: element, querySelectorAll: selector => selector === '#dosenEducationMappingRows select' ? selections : [] },
        $: jquery, FormData: FormDataMock, SUPER: { showMessage() {} }, init_table() { redraws++; },
    });
    scripts.forEach(source => vm.runInContext(source, context));
    context.renderDosenWorkbookPreview = () => {};
    const preview = { sheet: 'Detail Jenjang Pendidikan', header_row: 2,
        headers: ['Kode Dosen', 'Faculty', 'Program', 'Name', 'Type', 'Detail', 'Status', 'Pendidikan'],
        preview_data: [['D1', '', '', '', '', '', '', 'On Going S3']],
        mapping_config: { required: { kode_dosen: 'Kode Dosen', status_pendidikan: 'Status Pendidikan' }, optional: {} },
        suggested_mapping: { kode_dosen: 0, status_pendidikan: 7 } };
    const ready = () => {
        const file = { name: 'education.xlsx' };
        element('dosenEducationFile').files = [file];
        element('dosenEducationYear').value = '2026';
        element('dosenEducationMonth').value = '10';
        context.dosenEducationState.file = file;
        context.dosenEducationState.preview = preview;
        context.updateDosenEducationControls();
        return file;
    };
    return { context, element, selections, requests, preview, ready, redraws: () => redraws };
}

test('education import requires confirmation of its own mapping and submits only the status fields', () => {
    const h = harness();
    const file = h.ready();
    h.context.importDosenEducationWorkbook();
    assert.equal(h.requests.length, 0);
    h.context.confirmDosenEducationMapping();
    h.context.importDosenEducationWorkbook();
    h.context.importDosenEducationWorkbook();
    assert.equal(h.requests.length, 1);
    const request = h.requests[0].options;
    assert.equal(request.url, 'dosen.education.import');
    assert.equal(request.headers['X-CSRF-TOKEN'], 'csrf-token');
    assert.equal(request.data.values.get('dosen'), file);
    assert.equal(request.data.values.get('year'), '2026');
    assert.equal(request.data.values.get('month'), '10');
    assert.equal(request.data.values.get('header_row'), 2);
    assert.deepEqual(JSON.parse(request.data.values.get('column_mapping')), { kode_dosen: 0, status_pendidikan: 7 });
    assert.equal(h.context.dosenImportState.confirmed, false, 'Data Center workflow remains independent');
});

test('missing, duplicate and out-of-range education mappings cannot be confirmed', () => {
    for (const value of ['', '0', '-1', '99', '0.5']) {
        const h = harness();
        h.ready();
        h.selections[1].value = value;
        h.context.confirmDosenEducationMapping();
        h.context.importDosenEducationWorkbook();
        assert.equal(h.context.dosenEducationState.confirmed, false);
        assert.equal(h.element('dosenEducationSubmit').disabled, true);
        assert.equal(h.requests.length, 0);
    }
});

test('replacement cancels preview and ignores a late response, and requires fresh confirmation', () => {
    const h = harness();
    h.ready();
    h.context.confirmDosenEducationMapping();
    h.context.previewDosenEducationWorkbook();
    const old = h.requests[0];
    h.element('dosenEducationFile').files = [{ name: 'replacement.xlsx' }];
    h.context.previewDosenEducationWorkbook();
    assert.equal(old.aborted, true);
    old.options.success(h.preview);
    old.options.complete();
    assert.equal(h.context.dosenEducationState.preview, null);
    assert.equal(h.context.dosenEducationState.loading, true);
    h.requests[1].options.success(h.preview);
    h.requests[1].options.complete();
    assert.equal(h.element('dosenEducationSubmit').disabled, true);
    h.context.confirmDosenEducationMapping();
    assert.equal(h.element('dosenEducationSubmit').disabled, false);
});

test('preview and import failures retain a retryable file and its confirmed mapping', () => {
    const h = harness();
    const file = h.ready();
    h.context.previewDosenEducationWorkbook();
    h.requests[0].options.error({ responseJSON: { errors: { dosen: ['Wrong sheet'] } } }, 'error');
    h.requests[0].options.complete();
    assert.equal(h.element('dosenEducationPreviewStatus').textContent, 'Wrong sheet');
    h.context.previewDosenEducationWorkbook();
    h.requests[1].options.success(h.preview);
    h.requests[1].options.complete();
    h.context.confirmDosenEducationMapping();
    h.context.importDosenEducationWorkbook();
    h.requests[2].options.error({ responseJSON: { message: 'Missing Data Center period' } });
    h.requests[2].options.complete();
    assert.equal(h.element('dosenEducationSummary').textContent, 'Missing Data Center period');
    assert.equal(h.element('dosenEducationFile').files[0], file);
    assert.equal(h.element('dosenEducationSubmit').disabled, false);
});

test('success shows unmatched codes and refreshes the list while retaining the selected period', () => {
    const h = harness();
    h.ready();
    h.context.confirmDosenEducationMapping();
    h.context.importDosenEducationWorkbook();
    h.requests[0].options.success({ success: true, message: '1 dosen diperbarui.', summary: { unmatched_codes: ['D999'] } });
    h.requests[0].options.complete();
    assert.match(h.element('dosenEducationSummary').textContent, /D999/);
    assert.equal(h.context.dosenEducationState.file, null);
    assert.equal(h.context.dosenEducationState.confirmed, false);
    assert.equal(h.element('dosenEducationYear').value, '2026');
    assert.equal(h.element('dosenEducationMonth').value, '10');
    assert.equal(h.redraws(), 1);
});

test('reopening the page ignores preview responses from the previous page instance', () => {
    const h = harness();
    h.ready();
    h.context.previewDosenEducationWorkbook();
    const old = h.requests[0];
    vm.runInContext(scripts[1], h.context);
    old.options.success(h.preview);
    old.options.complete();
    assert.equal(old.aborted, true);
    assert.equal(h.context.dosenEducationState.preview, null);
});
