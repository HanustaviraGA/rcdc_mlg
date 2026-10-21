import test from 'node:test';
import assert from 'node:assert/strict';
import { selectSnapshot, selectedSources, totalAvailable, csvCell } from '../../public/js/publication-performance.js';

const source = {
    snapshots: [
        { year: 2025, month: 12, fm: { actual: 99, target: 100, cards: [] } },
        { year: 2026, month: 3, fm: { actual: 2, target: 10, cards: [{ program: 'CS', actual: 2, target: 5 }] } },
        { year: 2026, month: 8, fm: { actual: 7, target: 10, cards: [{ program: 'CS', actual: 4, target: 5 }] } },
        { year: 2026, month: 9, fm: { actual: 8, target: 10, cards: [{ program: 'CS', actual: 5, target: 5 }] }, mhs: { actual: 12, target: 20, cards: [] } },
    ],
};

test('cumulative quarters select the latest eligible snapshot without adding earlier months', () => {
    assert.equal(selectSnapshot(source.snapshots, '2026', 'fm', 'Q1').fm.actual, 2);
    assert.equal(selectSnapshot(source.snapshots, '2026', 'fm', 'Q2').month, 3);
    assert.equal(selectSnapshot(source.snapshots, '2026', 'fm', 'Q3').fm.actual, 8);
    assert.equal(selectSnapshot(source.snapshots, '2026', 'fm', 'all').fm.actual, 8);
    assert.equal(source.snapshots[1].month, 3);
});

test('snapshot selection never borrows another year or publication category', () => {
    assert.equal(selectSnapshot(source.snapshots, '2024', 'fm'), null);
    assert.equal(selectSnapshot(source.snapshots, '2026', 'mhs', 'Q2'), null);
    assert.equal(selectSnapshot(source.snapshots, '2026', 'vrtt'), null);
    assert.equal(selectSnapshot(source.snapshots, '2026', 'mhs', 'Q3').mhs.actual, 12);
});

test('year totals combine one cumulative snapshot per year and preserve missing values', () => {
    const state = { years: ['2025', '2026', '2027'], quarter: 'Q4', program: '' };
    const selected = selectedSources(source, state, 'fm');
    assert.equal(totalAvailable(selected, 'actual'), 107);
    assert.equal(selected[2].data, null);
    assert.equal(totalAvailable(selectedSources(source, state, 'vrtt'), 'actual'), null);
    assert.equal(totalAvailable([{ data: { actual: 0 } }], 'actual'), 0);
});

test('program summaries use their own targets and do not invent zero for missing programs', () => {
    const state = { years: ['2026'], quarter: 'Q3', program: 'CS' };
    assert.equal(totalAvailable(selectedSources(source, state, 'fm'), 'actual'), 5);
    assert.equal(totalAvailable(selectedSources(source, { ...state, program: 'DI' }, 'fm'), 'actual'), null);
});

test('CSV export quotes cells and neutralizes spreadsheet formulas', () => {
    assert.equal(csvCell('Title "quoted"; second'), '"Title ""quoted""; second"');
    assert.equal(csvCell('=HYPERLINK("x")'), '"\'=HYPERLINK(""x"")"');
    assert.equal(csvCell(' +SUM(A1:A2)'), '"\' +SUM(A1:A2)"');
    assert.equal(csvCell(0), '"0"');
    assert.equal(csvCell(null), '""');
});
