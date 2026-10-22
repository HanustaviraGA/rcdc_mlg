import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../Modules/Dosen/resources/views/javascript.blade.php', import.meta.url), 'utf8');
const filters = source.slice(source.indexOf('    function openDosenFilters()'), source.indexOf('    function init_table()'))
    .replace(/\{\{ now\(\)->year \}\}/g, '2026');

function harness(valid = true) {
    const values = { '#dosenFilterYear': '2026', '#dosenFilterMonth': '', '#prodi': 'All' };
    const modals = [];
    let reloads = 0;
    const context = vm.createContext({
        $: selector => ({
            val(value) {
                if (value === undefined) return values[selector];
                values[selector] = value;
                return this;
            },
            modal(action) { modals.push([selector, action]); },
        }),
        document: { getElementById: () => ({ reportValidity: () => valid }) },
        init_table() { reloads++; },
    });
    vm.runInContext(filters, context);
    return { context, values, modals, reloads: () => reloads };
}

const event = { preventDefault() {} };

test('opening filters copies applied values and discards cancelled edits when reopened', () => {
    const h = harness();
    h.context.openDosenFilters();
    assert.equal(h.values['#dosenFilterYearInput'], '2026');
    h.values['#dosenFilterYearInput'] = '2025';
    h.values['#dosenFilterProdiInput'] = 'CS';
    assert.equal(h.values['#dosenFilterYear'], '2026');
    assert.equal(h.context.isDosenHistoricalPeriod(), false);
    h.context.openDosenFilters();
    assert.equal(h.values['#dosenFilterYearInput'], '2026');
    assert.equal(h.values['#dosenFilterProdiInput'], 'All');
    assert.equal(h.reloads(), 0);
});

test('apply commits all three filters, closes modal and reloads once', () => {
    const h = harness();
    h.context.openDosenFilters();
    Object.assign(h.values, { '#dosenFilterYearInput': '2025', '#dosenFilterMonthInput': '8', '#dosenFilterProdiInput': 'CS' });
    h.context.applyDosenFilters(event);
    assert.equal(h.values['#dosenFilterYear'], '2025');
    assert.equal(h.values['#dosenFilterMonth'], '8');
    assert.equal(h.values['#prodi'], 'CS');
    assert.equal(h.context.isDosenHistoricalPeriod(), true);
    assert.deepEqual(h.modals.at(-1), ['#modalDosenFilter', 'hide']);
    assert.equal(h.reloads(), 1);
});

test('invalid year does not apply filters or close modal', () => {
    const h = harness(false);
    h.context.openDosenFilters();
    h.values['#dosenFilterYearInput'] = '';
    h.context.applyDosenFilters(event);
    assert.equal(h.values['#dosenFilterYear'], '2026');
    assert.equal(h.reloads(), 0);
    assert.deepEqual(h.modals.at(-1), ['#modalDosenFilter', 'show']);
});

test('reset restores draft defaults; apply returns to current-year editable listing', () => {
    const h = harness();
    Object.assign(h.values, { '#dosenFilterYear': '2025', '#dosenFilterMonth': '8', '#prodi': 'CS' });
    h.context.openDosenFilters();
    h.context.resetDosenFilters();
    assert.equal(h.values['#dosenFilterYear'], '2025');
    assert.equal(h.reloads(), 0);
    h.context.applyDosenFilters(event);
    assert.equal(h.values['#dosenFilterYear'], '2026');
    assert.equal(h.values['#dosenFilterMonth'], '');
    assert.equal(h.values['#prodi'], 'All');
    assert.equal(h.context.isDosenHistoricalPeriod(), false);
    assert.equal(h.reloads(), 1);
});
