const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

const script = readFileSync(path.join(__dirname, '../../public/js/community-development.js'), 'utf8');
const semesterLecturer = { code: 'D1', name: 'Semester lecturer', score: 5 };
const periodicLecturer = { code: 'D1', name: 'Periodic lecturer', score: 6 };

function searchLecturer(source, code = 'D1', enter = false) {
    const elements = new Map();
    const document = {
        getElementById(id) {
            if (!elements.has(id)) {
                elements.set(id, {
                    value: '', innerHTML: '', listeners: {},
                    addEventListener(event, callback) { this.listeners[event] = callback; },
                });
            }
            return elements.get(id);
        },
    };
    const context = vm.createContext({ document, window: {}, Intl, source });
    vm.runInContext(script, context);
    vm.runInContext('initializeCommunityDevelopment(source)', context);
    const input = document.getElementById('searchLecturer');
    input.value = code;
    if (enter) input.listeners.keydown({ key: 'Enter', preventDefault() {} });
    else document.getElementById('searchLecturerBtn').listeners.click();
    return document.getElementById('searchLecturerResult').innerHTML;
}

test('self-check reads periodic lecturers without a Rasio sheet', () => {
    const result = searchLecturer({
        programs: [{ lecturers: [semesterLecturer] }],
        periodicPrograms: [], periodicUnassignedLecturers: [periodicLecturer],
    });
    assert.match(result, /Periodic lecturer/);
    assert.match(result, /Skor <strong>6<\/strong>/);
    assert.doesNotMatch(result, /Semester lecturer/);
});

test('self-check supports periodic lecturers grouped by program', () => {
    const result = searchLecturer({
        programs: [{ lecturers: [semesterLecturer] }],
        periodicPrograms: [{ label: 'Computer Science', lecturers: [periodicLecturer] }],
    });
    assert.match(result, /Periodic lecturer/);
    assert.match(result, /Computer Science/);
});

test('missing periodic imports never fall back to semester data', () => {
    for (const periodic of [{}, { periodicPrograms: [], periodicUnassignedLecturers: [] }]) {
        const result = searchLecturer({
            programs: [{ lecturers: [semesterLecturer] }],
            unassignedLecturers: [semesterLecturer], ...periodic,
        });
        assert.match(result, /Belum ada data periodik untuk pengecekan mandiri/);
        assert.doesNotMatch(result, /Semester lecturer/);
    }
});

test('a code found only in semester data is not returned by periodic search', () => {
    const result = searchLecturer({
        programs: [{ lecturers: [semesterLecturer] }],
        unassignedLecturers: [semesterLecturer], periodicPrograms: [],
        periodicUnassignedLecturers: [{ ...periodicLecturer, code: 'D2' }],
    });
    assert.match(result, /tidak ditemukan pada data periodik ini/);
    assert.doesNotMatch(result, /Semester lecturer/);
});

test('Enter searches periodic data with trimmed, case-insensitive lecturer codes', () => {
    const result = searchLecturer({ periodicUnassignedLecturers: [periodicLecturer] }, '  d1  ', true);
    assert.match(result, /Periodic lecturer/);
});

test('empty lecturer codes prompt for input', () => {
    const result = searchLecturer({ periodicUnassignedLecturers: [periodicLecturer] }, '   ');
    assert.match(result, /Masukkan kode dosen terlebih dahulu/);
});
