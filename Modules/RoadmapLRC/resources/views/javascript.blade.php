<script>
(() => {
    const root = document.getElementById('roadmapLrc');
    if (!root) return;
    const { updateUrl, csrf } = root.dataset;
    const modal = root.querySelector('[data-roadmap-modal]');
    const form = modal.querySelector('[data-roadmap-form]');
    const title = modal.querySelector('[data-roadmap-modal-title]');
    const error = modal.querySelector('[data-roadmap-error]');
    const submit = modal.querySelector('[data-roadmap-submit]');
    const status = root.querySelector('[data-roadmap-status]');
    const table = root.querySelector('[data-roadmap-table]');
    // The number in the modal: its row id, field and the data-roadmap-cell holding its input.
    let editing = null;

    async function send(fields) {
        const body = new FormData();
        body.append('_token', csrf);
        Object.entries(fields).forEach(([key, value]) => body.append(key, value));
        const response = await fetch(updateUrl, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Gagal menyimpan. Silakan coba lagi.');
        }
        return data;
    }

    function open(input) {
        const { id, field, label, value, note } = input.dataset;
        editing = { id, field, cell: input.closest('[data-roadmap-cell]').dataset.roadmapCell };
        title.textContent = `Ubah ${label}`;
        form.elements.nilai.value = value;
        form.elements.catatan.value = note || '';
        error.textContent = '';
        error.hidden = true;
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }

    root.addEventListener('click', event => {
        const input = event.target.closest('[data-roadmap-edit]');
        if (input) open(input);
    });
    root.addEventListener('keydown', event => {
        const input = event.target.closest('[data-roadmap-edit]');
        if (!input || (event.key !== 'Enter' && event.key !== ' ')) return;
        event.preventDefault();
        open(input);
    });
    modal.addEventListener('shown.bs.modal', () => form.elements.nilai.focus());
    // Back to the edited number; after a save the table holds a new input for it.
    modal.addEventListener('hidden.bs.modal', () => {
        if (editing) table.querySelector(`[data-roadmap-cell="${editing.cell}"] [data-roadmap-edit]`)?.focus();
        editing = null;
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!editing || submit.disabled) return;
        submit.disabled = true;
        error.hidden = true;
        try {
            const data = await send({
                id: editing.id, field: editing.field, nilai: form.elements.nilai.value, catatan: form.elements.catatan.value, _method: 'PUT',
            });
            table.innerHTML = data.table_html;
            status.className = 'fs-7 mb-4 text-success';
            status.textContent = data.message;
            bootstrap.Modal.getOrCreateInstance(modal).hide();
        } catch (exception) {
            error.textContent = exception.message;
            error.hidden = false;
        } finally {
            submit.disabled = false;
        }
    });
})();
</script>
