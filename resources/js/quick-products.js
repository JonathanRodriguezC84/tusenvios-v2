document.addEventListener('DOMContentLoaded', () => {
    const data = document.getElementById('qp-toast-data');

    if (data) {
        const container = document.createElement('div');
        container.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;display:grid;gap:0.5rem;max-width:28rem;';
        document.body.appendChild(container);

        JSON.parse(data.textContent || '[]').forEach((msg) => {
            const el = document.createElement('div');
            el.style.cssText = `border-radius:0.5rem;padding:0.85rem 1rem;font-size:0.82rem;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.12);${msg.type === 'error' ? 'border:1px solid #fecaca;background:#fef2f2;color:#991b1b' : 'border:1px solid #a7f3d0;background:#ecfdf5;color:#065f46'}`;
            el.textContent = msg.text;
            container.appendChild(el);
            window.setTimeout(() => el.remove(), 4000);
        });
    }

    const searchForm = document.querySelector('[data-qp-search-form]');
    const searchInput = document.querySelector('[data-qp-search-input]');
    if (searchForm && searchInput) {
        let searchTimer = null;
        let lastSubmitted = searchInput.value.trim();

        const submitSearch = () => {
            const current = searchInput.value.trim();
            if (current === lastSubmitted) return;
            lastSubmitted = current;
            searchForm.submit();
        };

        searchInput.addEventListener('input', () => {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(submitSearch, 450);
        });

        searchInput.addEventListener('search', () => {
            window.clearTimeout(searchTimer);
            submitSearch();
        });
    }

    document.querySelectorAll('.qp-row-editable').forEach((row) => {
        const editBtn = row.querySelector('.qp-row-edit-btn');
        const saveBtn = row.querySelector('.qp-row-save-btn');
        const fields = row.querySelectorAll('.qp-field');

        if (!editBtn) return;

        editBtn.addEventListener('click', () => {
            row.classList.add('is-editing');
            fields.forEach((field) => { field.disabled = false; });
        });

        saveBtn.addEventListener('click', (e) => {
            row.classList.add('is-editing');
            fields.forEach((field) => { field.disabled = false; });
            if (!row.checkValidity()) {
                e.preventDefault();
                row.reportValidity();
            }
        });
    });
});
