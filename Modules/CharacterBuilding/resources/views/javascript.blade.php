<script>
(() => {
    const root = document.getElementById('characterBuilding');
    if (!root) return;
    const { createUrl, updateUrl, deleteUrl, readUrl, csrf } = root.dataset;
    // Latest panel request per tab: an older response must not replace a newer quarter.
    const panelRequests = {};

    async function send(url, fields) {
        const body = new FormData();
        body.append('_token', csrf);
        Object.entries(fields).forEach(([key, value]) => body.append(key, value));
        const response = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Permintaan gagal. Silakan coba lagi.');
        }
        return data;
    }

    function setStatus(category, message, ok) {
        const status = root.querySelector(`[data-cb-target-status="${category}"]`);
        status.className = `fs-7 mt-3 ${ok ? 'text-success' : 'text-danger'}`;
        status.textContent = message;
    }

    function form(category) {
        return root.querySelector(`[data-cb-target-form="${category}"]`);
    }

    function setValue(select, value) {
        // A saved period may be older than the options offered by default.
        if (select.tagName === 'SELECT' && ![...select.options].some(option => option.value === value)) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            select.append(option);
        }
        select.value = value;
    }

    function clear(target) {
        target.elements.target.value = '';
        target.elements.score.value = '';
    }

    function stopEditing(category) {
        const target = form(category);
        delete target.dataset.editId;
        target.querySelector('[data-cb-submit]').textContent = 'Simpan target';
        target.querySelector('[data-cb-cancel]').hidden = true;
    }

    function startEditing(button) {
        const { category, id, year, term, target, score, label } = button.dataset;
        const edited = form(category);
        edited.dataset.editId = id;
        if (edited.elements.academic_year) {
            setValue(edited.elements.academic_year, `${year}/${Number(year) + 1}`);
            setValue(edited.elements.semester, term === '1' ? 'Ganjil' : 'Genap');
        } else {
            setValue(edited.elements.year, year);
            setValue(edited.elements.quarter, term);
        }
        edited.elements.target.value = target;
        edited.elements.score.value = score;
        edited.querySelector('[data-cb-submit]').textContent = 'Simpan perubahan';
        edited.querySelector('[data-cb-cancel]').hidden = false;
        setStatus(category, `Mengubah target ${label}. Periode, target, dan skor dapat diganti.`, true);
        edited.elements.target.focus();
    }

    function replaceList(data) {
        root.querySelector(`[data-cb-target-list="${data.category}"]`).innerHTML = data.list_html;
        // Publikasi and Community Development show the score ranges of their year.
        if (data.category === 'publikasi' || data.category === 'comdev') loadPanel(data.category);
    }

    async function loadPanel(tab) {
        const filter = root.querySelector(`[data-cb-filter="${tab}"]`);
        const panel = root.querySelector(`[data-cb-panel="${tab}"]`);
        const error = root.querySelector(`[data-cb-panel-error="${tab}"]`);
        const request = (panelRequests[tab] || 0) + 1;
        panelRequests[tab] = request;
        panel.setAttribute('aria-busy', 'true');
        try {
            const data = await send(readUrl, { tab, year: filter.elements.year.value, quarter: filter.elements.quarter.value });
            if (panelRequests[tab] !== request) return;
            panel.innerHTML = data.html;
            error.hidden = true;
        } catch (exception) {
            if (panelRequests[tab] !== request) return;
            error.textContent = exception.message;
            error.hidden = false;
        } finally {
            if (panelRequests[tab] === request) panel.removeAttribute('aria-busy');
        }
    }

    root.querySelectorAll('[data-cb-target-form]').forEach(target => {
        const category = target.dataset.cbTargetForm;
        target.addEventListener('submit', async event => {
            event.preventDefault();
            const submit = target.querySelector('[data-cb-submit]');
            if (submit.disabled) return;
            const fields = { target: target.elements.target.value, score: target.elements.score.value };
            ['year', 'quarter', 'academic_year', 'semester'].forEach(name => {
                if (target.elements[name]) fields[name] = target.elements[name].value;
            });
            const editId = target.dataset.editId;
            submit.disabled = true;
            try {
                const data = await send(editId ? updateUrl : createUrl, editId
                    ? { ...fields, id: editId, _method: 'PUT' } : { ...fields, category });
                stopEditing(category);
                clear(target);
                replaceList(data);
                setStatus(category, data.message, true);
            } catch (exception) {
                setStatus(category, exception.message, false);
            } finally {
                submit.disabled = false;
            }
        });
        target.querySelector('[data-cb-cancel]').addEventListener('click', () => {
            stopEditing(category);
            clear(target);
            setStatus(category, '', true);
        });
    });

    root.addEventListener('click', async event => {
        const edit = event.target.closest('[data-cb-edit]');
        if (edit) {
            startEditing(edit);
            return;
        }
        const remove = event.target.closest('[data-cb-delete]');
        if (!remove || remove.disabled) return;
        const { category, id, label } = remove.dataset;
        if (!window.confirm(`Hapus target ${label}?`)) return;
        remove.disabled = true;
        try {
            const data = await send(deleteUrl, { id, _method: 'DELETE' });
            if (form(category).dataset.editId === id) stopEditing(category);
            replaceList(data);
            setStatus(category, data.message, true);
        } catch (exception) {
            remove.disabled = false;
            setStatus(category, exception.message, false);
        }
    });

    root.querySelectorAll('[data-cb-filter]').forEach(filter => {
        filter.addEventListener('change', () => loadPanel(filter.dataset.cbFilter));
        filter.addEventListener('submit', event => {
            event.preventDefault();
            loadPanel(filter.dataset.cbFilter);
        });
    });
})();
</script>
