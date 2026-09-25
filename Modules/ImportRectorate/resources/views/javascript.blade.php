<script>
(() => {
    const root = document.getElementById('monthly-import');
    const form = root.querySelector('form');
    const button = root.querySelector('#monthly-import-submit');
    const errors = root.querySelector('#monthly-import-errors');
    form.addEventListener('submit', async event => {
        event.preventDefault();
        errors.hidden = true;
        root.querySelector('#monthly-import-summary').replaceChildren();
        if (![...form.querySelectorAll('input[type="file"]')].some(input => input.files.length)) {
            errors.textContent = 'Pilih minimal satu file untuk diupload.';
            errors.hidden = false;
            return;
        }
        button.disabled = true;
        button.textContent = 'Memproses import...';
        try {
            const response = await fetch(form.action, {method: 'POST', headers: {'Accept': 'application/json'}, body: new FormData(form)});
            const data = await response.json();
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Import gagal.');
            root.querySelector('#monthly-import-summary').innerHTML = data.summary_html;
            root.querySelector('#monthly-import-history').innerHTML = data.history_html;
            form.querySelectorAll('input[type="file"]').forEach(input => input.value = '');
        } catch (error) {
            errors.textContent = error.message;
            errors.hidden = false;
        } finally {
            button.disabled = false;
            button.textContent = 'Upload dan import';
        }
    });
})();
</script>
