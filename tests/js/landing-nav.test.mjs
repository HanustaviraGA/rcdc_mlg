import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

// The header navigation of the public pages (Constructo's main.js), with its Dashboard dropdown.
const source = readFileSync(new URL('../../public/landing/assets/js/main.js', import.meta.url), 'utf8');

class Element {
    constructor(classes = []) {
        this.classes = new Set(classes);
        this.listeners = {};
        this.classList = {
            add: name => this.classes.add(name),
            remove: name => this.classes.delete(name),
            toggle: name => (this.classes.has(name) ? this.classes.delete(name) : this.classes.add(name)),
            contains: name => this.classes.has(name),
        };
    }
    addEventListener(name, handler) { (this.listeners[name] ??= []).push(handler); }
    click() {
        const event = { prevented: false, stopped: false, preventDefault() { this.prevented = true; }, stopImmediatePropagation() { this.stopped = true; } };
        for (const handler of this.listeners.click || []) {
            handler.call(this, event);
            if (event.stopped) break;
        }
        return event;
    }
}

function harness({ mobile }) {
    const body = new Element(mobile ? ['mobile-nav-active'] : []);
    const toggle = new Element(['mobile-nav-toggle', 'bi-list']);
    const home = new Element();
    const dropdownItem = new Element(['dropdown']);
    const parent = new Element();
    const list = new Element();
    const lrc = new Element();
    for (const link of [home, lrc]) link.parentElement = new Element();
    parent.parentElement = dropdownItem;
    parent.nextElementSibling = list;
    const document = {
        querySelector: selector => ({
            body, '#header': new Element(['sticky-top']), '.mobile-nav-toggle': toggle, '.scroll-top': new Element(),
            '.mobile-nav-active': body.classes.has('mobile-nav-active') ? body : null,
        })[selector] ?? null,
        querySelectorAll: selector => ({ '#navmenu a': [home, parent, lrc], '.navmenu .dropdown > a': [parent] })[selector] ?? [],
        addEventListener() {},
    };
    vm.runInNewContext(source, { document, window: { addEventListener() {}, scrollY: 0 }, GLightbox: () => ({}) });
    return { body, parent, list, home, lrc };
}

test('on mobile the whole Dashboard link opens its list and keeps the menu open', () => {
    const h = harness({ mobile: true });
    const event = h.parent.click();
    assert.equal(event.prevented, true, 'the "#" link does not jump to the top');
    assert.equal(h.parent.classList.contains('active'), true);
    assert.equal(h.list.classList.contains('dropdown-active'), true);
    assert.equal(h.body.classList.contains('mobile-nav-active'), true, 'the menu stays open');

    h.parent.click();
    assert.equal(h.list.classList.contains('dropdown-active'), false, 'a second tap closes the list');
    assert.equal(h.body.classList.contains('mobile-nav-active'), true);
});

test('on mobile a dashboard link inside the list closes the menu like any other link', () => {
    const h = harness({ mobile: true });
    h.parent.click();
    h.lrc.click();
    assert.equal(h.body.classList.contains('mobile-nav-active'), false);
});

test('on desktop the Dashboard link stays in place and the list opens on hover or focus', () => {
    const h = harness({ mobile: false });
    const event = h.parent.click();
    assert.equal(event.prevented, true);
    assert.equal(h.parent.classList.contains('active'), false);
    assert.equal(h.list.classList.contains('dropdown-active'), false);
    const css = readFileSync(new URL('../../public/landing/assets/css/main.css', import.meta.url), 'utf8');
    assert.match(css, /\.navmenu \.dropdown:hover>ul,\s*\.navmenu \.dropdown:focus-within>ul \{/);
});
