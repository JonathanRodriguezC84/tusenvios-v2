document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('input[name="search"]');
    const filterForm = searchInput?.closest('form');

    if (searchInput && filterForm) {
        let timer;
        searchInput.addEventListener('input', () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => filterForm.requestSubmit(), 400);
        });

        filterForm.querySelectorAll('select, input[type="date"]').forEach((el) => {
            el.addEventListener('change', () => filterForm.requestSubmit());
        });
    }

    const toastData = document.getElementById('sh-toast-data');
    if (toastData) {
        const container = document.createElement('div');
        container.id = 'sh-toast-container';
        container.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;display:grid;gap:0.5rem;max-width:28rem;';
        document.body.appendChild(container);

        JSON.parse(toastData.textContent || '[]').forEach((msg) => {
            const el = document.createElement('div');
            el.style.cssText = `border-radius:0.5rem;padding:0.85rem 1rem;font-size:0.82rem;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.12);${msg.type === 'error' ? 'border:1px solid #fecaca;background:#fef2f2;color:#991b1b' : 'border:1px solid #a7f3d0;background:#ecfdf5;color:#065f46'}`;
            el.textContent = msg.text;
            container.appendChild(el);
            window.setTimeout(() => el.remove(), 4000);
        });
    }
});
