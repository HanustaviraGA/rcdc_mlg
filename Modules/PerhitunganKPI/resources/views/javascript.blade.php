<script>
(() => {
    const root = document.getElementById('imported-kpi');
    const form = root.querySelector('#kpi-filters');
    const counter = root.querySelector('#kpi-count');
    const errorBox = root.querySelector('#kpi-error');
    const body = root.querySelector('#table_kpi');
    let timer;
    let controller;
    let requestId = 0;
    const apply = async () => {
        const id = ++requestId;
        controller?.abort();
        controller = new AbortController();
        errorBox.hidden = true;
        counter.textContent = 'Memuat data...';
        body.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(@json(route('perhitungankpi.init_table')), {
                method: 'POST', headers: {'Accept': 'application/json'},
                body: new FormData(form), signal: controller.signal
            });
            const data = await response.json();
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Data gagal dimuat.');
            if (id !== requestId || !root.isConnected) return;
            body.innerHTML = data.html;
            counter.textContent = data.count + ' baris dari ' + data.lecturers + ' dosen';
        } catch (error) {
            if (error.name === 'AbortError' || id !== requestId) return;
            errorBox.textContent = error.message;
            errorBox.hidden = false;
            counter.textContent = 'Filter belum diterapkan. Tabel masih menampilkan hasil sebelumnya.';
        } finally {
            if (id === requestId) body.removeAttribute('aria-busy');
        }
    };
    form.addEventListener('submit', event => { event.preventDefault(); clearTimeout(timer); apply(); });
    form.querySelector('input[type="search"]').addEventListener('input', () => {
        clearTimeout(timer);
        controller?.abort();
        ++requestId;
        timer = setTimeout(apply, 250);
    });
    form.querySelectorAll('select').forEach(input => input.addEventListener('change', () => { clearTimeout(timer); apply(); }));
    form.addEventListener('reset', () => { clearTimeout(timer); setTimeout(apply, 0); });
})();
</script>
