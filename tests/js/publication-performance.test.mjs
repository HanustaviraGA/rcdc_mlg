import test from 'node:test';
import assert from 'node:assert/strict';
import { selectSnapshot, selectedSources, totalAvailable, csvCell, publicationCounts, filterPublications } from '../../public/js/publication-performance.js';

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

test('month limits cumulative data within the chosen quarter for every selected year', () => {
    assert.equal(selectSnapshot(source.snapshots, '2026', 'fm', 'Q3', '8').month, 8);
    assert.equal(selectSnapshot(source.snapshots, '2026', 'fm', 'Q3', '9').month, 9);
    assert.equal(selectSnapshot(source.snapshots, '2026', 'fm', 'Q3', '7').month, 3);
    assert.equal(selectSnapshot(source.snapshots, '2026', 'fm', 'Q2', '9').month, 3);
    assert.equal(selectSnapshot(source.snapshots, '2026', 'fm', 'Q1', '2'), null);
    const state = { years: ['2025', '2026'], quarter: 'Q3', month: '8', program: 'CS' };
    const selected = selectedSources(source, state, 'fm');
    assert.equal(selected[0].data, null);
    assert.equal(totalAvailable(selected, 'actual'), 4);
    assert.equal(selectSnapshot(source.snapshots, '2026', 'mhs', 'Q3', '8'), null);
});

test('FM summary counts papers rather than weights, uses explicit author roles and detects SINTA from quartile', () => {
    const rows = [
        { type: 'Seminar', firstAuthor: 'Y', weight: 0.25 },
        { type: ' conference ', firstAuthor: ' y ', weight: 3 },
        { type: 'Jurnal', firstAuthor: 'Y', quartile: 'Q1', weight: 4 },
        { type: 'Seminar', firstAuthor: 'N', weight: 2 },
        { type: 'Journal', firstAuthor: 'N', quartile: ' Jurnal SINTA 2 ', weight: 0.5 },
        { type: 'Jurnal', firstAuthor: 'N', quartile: 'jurnal  sinta', weight: 0.25 },
        { type: 'Jurnal', firstAuthor: null, quartile: 'Jurnal SINTA 3', weight: 10 },
        { type: 'Book Chapter', firstAuthor: 'Y', quartile: 'Q2', weight: 10 },
        { type: 'Jurnal', firstAuthor: '-', index: 'SINTA', quartile: 'Q3', weight: 10 },
    ];
    assert.deepEqual(publicationCounts(rows), { firstConference: 2, firstJournal: 1, nonFirstConference: 1, nonFirstJournal: 2, sinta: 3 });
    assert.deepEqual(publicationCounts([]), { firstConference: 0, firstJournal: 0, nonFirstConference: 0, nonFirstJournal: 0, sinta: 0 });
});

test('publication filters combine multiple choices, keep source paper distinct from journal title, and handle blanks', () => {
    const rows = [
        { title: 'A', category: 'Scopus FM', type: 'Jurnal', index: 'Scopus', quartile: 'Q1', firstAuthor: 'Y', paperSource: 'Internal', source: 'Journal A' },
        { title: 'B', category: 'Scopus FM', type: 'Seminar', index: 'Scopus', quartile: null, firstAuthor: 'N', paperSource: 'External', source: 'Internal' },
        { title: 'C', category: 'Non Scopus FM', type: 'Jurnal', index: 'Nscopus', quartile: 'Jurnal SINTA', firstAuthor: 'N', paperSource: 'Internal' },
    ];
    assert.equal(filterPublications(rows).length, 3);
    assert.equal(filterPublications(rows, { firstAuthor: [] }).length, 3);
    assert.deepEqual(filterPublications(rows, { type: ['Jurnal', 'Seminar'], category: ['Scopus FM'], firstAuthor: [' n '] }).map(row => row.title), ['B']);
    assert.deepEqual(filterPublications(rows, { index: ['Nscopus'], quartile: ['Jurnal SINTA'], paperSource: ['Internal'] }).map(row => row.title), ['C']);
    assert.deepEqual(filterPublications(rows, { paperSource: ['Internal'] }).map(row => row.title), ['A', 'C']);
    assert.deepEqual(filterPublications(rows, { quartile: [''] }).map(row => row.title), ['B']);
    assert.deepEqual(filterPublications(rows, { type: ['Seminar'], firstAuthor: ['Y'] }), []);
    assert.equal(rows.length, 3);
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
