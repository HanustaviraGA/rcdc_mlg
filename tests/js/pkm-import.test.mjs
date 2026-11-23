import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const sources = ['javascript', 'partner_javascript'].map(name => readFileSync(
    new URL(`../../Modules/ImportPKM/resources/views/${name}.blade.php`, import.meta.url), 'utf8',
).replace(/<\/?script[^>]*>/g, ''));

function harness(confirmDelete = true) {
    const elements = new Map(), requests = [], confirmations = [], buttons = [];
    const el = id => {
        if (!elements.has(id)) elements.set(id, {
            id, value: '', files: [], style: {}, dataset: {}, listeners: {}, isConnected: true,
            textContent: '', innerHTML: `${id}: original`, hidden: false, disabled: false,
            addEventListener(type, callback) { (this.listeners[type] ??= []).push(callback); },
            fire(type, event = {}) {
                return Promise.all((this.listeners[type] ??= []).map(callback => callback({ preventDefault() {}, ...event })));
            },
            querySelector(selector) { return el(selector.slice(1)); },
            querySelectorAll(selector) {
                if (selector === '[data-delete-import]') return buttons.filter(button => button.root === id);
                if (selector.includes('input') || selector === 'select, input') {
                    return [...elements.values()].filter(input => input.id.startsWith(id === 'pkmImport' ? 'pkm' : 'partner') && input.id !== id);
                }
                if (selector.endsWith(' select')) return [{ dataset: { field: 'code' }, value: '0' }];
                return [];
            },
        });
        return elements.get(id);
    };
    el('pkmImportType').value = 'semester';
    el('periodicHistoryCard').style.display = 'none';
    el('partnerImportContainer').style.display = 'none';
    for (const root of ['pkmImport', 'partnerImport']) {
        Object.assign(el(root).dataset, { csrf: 'csrf-token', uploadUrl: `/${root}/upload` });
    }
    const window = { confirm(message) { confirmations.push(message); return confirmDelete; } };
    const context = vm.createContext({
        document: { getElementById: el }, window, AbortController,
        FormData: class {
            values = new Map();
            append(key, value) { this.values.set(key, value); }
        },
        fetch(url, options) {
            return new Promise((resolve, reject) => requests.push({
                url, options, reject,
                respond(data, ok = true) { resolve({ ok, json: async () => data }); },
            }));
        },
    });
    sources.forEach(source => vm.runInContext(source, context));
    const select = async type => {
        el('pkmImportType').value = type;
        await el('pkmImportType').fire('change');
    };
    const addButton = (type, id = 1) => {
        const root = type === 'partners' ? 'partnerImport' : 'pkmImport';
        const button = {
            root, disabled: false, textContent: 'Hapus',
            dataset: {
                label: type, period: type === 'partners' ? '2026' : '2026-09',
                url: type === 'partners' ? `/partners/${id}` : `/imports/${id}`,
            },
        };
        buttons.push(button);
        // Exercise delegated clicks, including clicks on nested content and newly added rows.
        return { button, click: () => el(root).fire('click', { target: { closest: () => button } }) };
    };
    const prepareUpload = type => {
        const partners = type === 'partners';
        const input = el(partners ? 'partnerWorkbook' : 'pkmWorkbook');
        const file = { name: 'workbook.xlsx' };
        input.files = [file];
        const sheet = { headers: ['Code'], sheet: 'Data', header_row: 1, mapping_config: { required: { code: 'Kode' } } };
        Object.assign(window[partners ? 'partnerImportState' : 'pkmImportState'], {
            file, confirmed: true,
            preview: partners ? sheet : { sheets: { rasio: sheet, keterlibatan: sheet } },
        });
    };
    return { el, requests, confirmations, window, select, addButton, prepareUpload };
}

test('switching among all import types shows exactly the matching history', async () => {
    const h = harness();
    for (const type of ['semester', 'periodic', 'partners', 'periodic', 'semester', 'partners', 'semester']) {
        await h.select(type);
        assert.equal(h.el('pkmHistoryCard').style.display, type === 'semester' ? 'block' : 'none');
        assert.equal(h.el('periodicHistoryCard').style.display, type === 'periodic' ? 'block' : 'none');
        assert.equal(h.el('partnerImportContainer').style.display, type === 'partners' ? 'block' : 'none');
        assert.equal(h.el('pkmImportForm').style.display, type === 'partners' ? 'none' : 'block');
    }
});

for (const type of ['semester', 'periodic', 'partners']) {
    const partners = type === 'partners';
    const prefix = partners ? 'partner' : 'pkm';
    const success = {
        success: true, message: 'Data berhasil dihapus.',
        history_html: `${type}: remaining`, periodic_history_html: 'periodic: remaining',
    };

    test(`${type}: delete sends CSRF, refreshes the right history and supports subsequent rows`, async () => {
        const h = harness();
        await h.select(type);
        for (const id of [1, 2]) {
            const { button, click } = h.addButton(type, id);
            const pending = click();
            const request = h.requests.at(-1);
            assert.equal(request.url, button.dataset.url);
            assert.equal(request.options.method, 'DELETE');
            assert.equal(request.options.headers['X-CSRF-TOKEN'], 'csrf-token');
            assert.equal(request.options.headers.Accept, 'application/json');
            assert.equal(button.disabled, true);
            assert.equal(button.textContent, 'Menghapus...');
            request.respond(success);
            await pending;
            assert.equal(h.el(`${prefix}ImportHistory`).innerHTML, success.history_html);
            assert.equal(h.el('periodicImportHistory').innerHTML, partners ? 'periodicImportHistory: original' : success.periodic_history_html);
            const otherHistory = partners ? 'pkmImportHistory' : 'partnerImportHistory';
            assert.equal(h.el(otherHistory).innerHTML, `${otherHistory}: original`);
            assert.equal(h.el(`${prefix}ImportSummary`).textContent, success.message);
            assert.equal(button.disabled, false);
        }
        assert.equal(h.confirmations.length, 2);
    });

    test(`${type}: cancelling confirmation leaves data untouched`, async () => {
        const h = harness(false);
        await h.addButton(type).click();
        assert.equal(h.requests.length, 0);
        assert.equal(h.el(`${prefix}ImportHistory`).innerHTML, `${prefix}ImportHistory: original`);
    });

    test(`${type}: repeated clicks and uploads are blocked while deleting`, async () => {
        const h = harness();
        await h.select(type);
        h.prepareUpload(type);
        const { click } = h.addButton(type);
        const pending = click();
        await click();
        await h.el(`${prefix}ImportForm`).fire('submit');
        assert.equal(h.requests.length, 1);
        assert.equal(h.el(`${prefix}ImportSubmit`).disabled, true);
        h.requests[0].respond(success);
        await pending;
        assert.equal(h.el(`${prefix}ImportSubmit`).disabled, false);
    });

    test(`${type}: failed deletion preserves history and allows retry`, async () => {
        const h = harness();
        const { button, click } = h.addButton(type);
        const pending = click();
        h.requests[0].respond({ success: false, message: 'Data tidak ditemukan.' }, false);
        await pending;
        assert.equal(h.el(`${prefix}ImportHistory`).innerHTML, `${prefix}ImportHistory: original`);
        assert.equal(h.el(`${prefix}ImportErrors`).textContent, 'Data tidak ditemukan.');
        assert.equal(button.disabled, false);
        assert.equal(button.textContent, 'Hapus');
        const retry = click();
        h.requests[1].respond(success);
        await retry;
        assert.equal(h.el(`${prefix}ImportErrors`).hidden, true);
    });

    test(`${type}: responses arriving after navigation do not update the detached page`, async () => {
        const h = harness();
        const { click } = h.addButton(type);
        const pending = click();
        h.el(`${prefix}Import`).isConnected = false;
        h.requests[0].respond(success);
        await pending;
        assert.equal(h.el(`${prefix}ImportHistory`).innerHTML, `${prefix}ImportHistory: original`);
    });

    test(`${type}: uploads refresh history without reloading the page`, async () => {
        const h = harness();
        await h.select(type);
        h.prepareUpload(type);
        const { button, click } = h.addButton(type);
        const pending = h.el(`${prefix}ImportForm`).fire('submit');
        await click();
        assert.equal(h.requests.length, 1);
        assert.equal(h.confirmations.length, 0);
        assert.equal(button.disabled, true);
        assert.equal(h.requests[0].options.method, 'POST');
        if (!partners) assert.equal(h.el('pkmImportType').disabled, true);
        h.requests[0].respond({ ...success, summary: {} });
        await pending;
        assert.equal(h.el(`${prefix}ImportHistory`).innerHTML, success.history_html);
        assert.equal(button.disabled, false);
        if (!partners) {
            assert.equal(h.el('periodicImportHistory').innerHTML, success.periodic_history_html);
            assert.equal(h.el('pkmImportType').disabled, false);
        }
    });
}
