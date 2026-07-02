document.addEventListener('DOMContentLoaded', () => {
    const inventoryPage = document.querySelector('.inventory-page-v01');
    const viewButtons = Array.from(document.querySelectorAll('[data-inventory-view]'));
    const selectVisible = document.getElementById('inventory-select-visible');
    const selectedCount = document.getElementById('inventory-selected-count');
    const checkboxes = Array.from(document.querySelectorAll('.inventory-product-checkbox-v01'));

    const applyInventoryView = (view) => {
        const compact = view !== 'detail';
        inventoryPage?.classList.toggle('is-compact', compact);

        viewButtons.forEach((button) => button.classList.toggle('is-active', button.dataset.inventoryView === (compact ? 'compact' : 'detail')));
        window.localStorage?.setItem('inventory_view_v02', compact ? 'compact' : 'detail');
    };

    viewButtons.forEach((button) => {
        button.addEventListener('click', () => applyInventoryView(button.dataset.inventoryView));
    });

    document.querySelectorAll('[data-inventory-open-detail]').forEach((button) => {
        button.addEventListener('click', () => {
            const productId = button.dataset.inventoryOpenDetail;
            applyInventoryView('detail');
            window.setTimeout(() => {
                document.getElementById(`inventory-product-${productId}`)?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            }, 80);
        });
    });

    applyInventoryView(window.localStorage?.getItem('inventory_view_v02') || 'compact');

    const updateSelectedCount = () => {
        const checked = checkboxes.filter((checkbox) => checkbox.checked).length;

        if (selectedCount) {
            selectedCount.textContent = `${checked} sel.`;
        }

        if (selectVisible) {
            selectVisible.checked = checked > 0 && checked === checkboxes.length;
            selectVisible.indeterminate = checked > 0 && checked < checkboxes.length;
        }
    };

    selectVisible?.addEventListener('change', () => {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = selectVisible.checked;
        });
        updateSelectedCount();
    });

    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelectedCount));
    updateSelectedCount();

    const searchInput = document.querySelector('input[name="search"]');
    const filterForm = searchInput?.closest('form');

    if (searchInput && filterForm) {
        let searchTimer;

        searchInput.addEventListener('input', () => {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => {
                filterForm.submit();
            }, 500);
        });

        const filterSelects = filterForm.querySelectorAll('select');
        filterSelects.forEach((select) => {
            select.addEventListener('change', () => {
                filterForm.submit();
            });
        });
    }

    const toastContainer = (() => {
        const existing = document.getElementById('inventory-toast-container');
        if (existing) return existing;

        const container = document.createElement('div');
        container.id = 'inventory-toast-container';
        container.className = 'inventory-toast-container';
        document.body.appendChild(container);
        return container;
    })();

    window.showInventoryToast = (message, type = 'success') => {
        const toast = document.createElement('div');
        toast.className = `inventory-toast-v01 is-${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add('is-open');
        });

        window.setTimeout(() => {
            toast.classList.remove('is-open');
            window.setTimeout(() => toast.remove(), 400);
        }, 4000);
    };

    const toastMessages = document.getElementById('inventory-toast-data');
    if (toastMessages) {
        const messages = JSON.parse(toastMessages.textContent || '[]');
        messages.forEach((msg) => {
            window.showInventoryToast(msg.text, msg.type);
        });
    }

    function makeStockEditable(p) {
        const currentStock = parseInt(p.dataset.stockValue, 10);
        const updateUrl = p.dataset.updateUrl;
        const row = p.closest('.inventory-simple-row-v01');

        const input = document.createElement('input');
        input.type = 'number';
        input.min = '0';
        input.value = currentStock;
        input.className = 'inventory-field-v01';
        input.style.width = '5rem';
        input.style.minHeight = '1.8rem';

        p.replaceWith(input);
        input.focus();
        input.select();

        const restore = (value) => {
            const newP = document.createElement('p');
            newP.className = 'text-xl font-black text-gray-950';
            newP.dataset.stockValue = value;
            newP.dataset.updateUrl = updateUrl;
            newP.textContent = value;
            newP.style.cursor = 'pointer';
            newP.title = 'Click para editar stock';
            input.replaceWith(newP);
            makeStockEditable(newP);
        };

        const save = () => {
            const newValue = parseInt(input.value, 10);
            if (isNaN(newValue) || newValue === currentStock) {
                restore(currentStock);
                return;
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch(updateUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ stock: newValue }),
            })
                .then((res) => {
                    if (!res.ok) throw new Error('Error');
                    return res.json();
                })
                .then(() => {
                    restore(newValue);
                    window.showInventoryToast(`Stock actualizado a ${newValue}`, 'success');
                })
                .catch(() => {
                    restore(currentStock);
                    window.showInventoryToast('Error al actualizar stock', 'error');
                });
        };

        input.addEventListener('blur', save);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { input.blur(); }
            if (e.key === 'Escape') { restore(currentStock); }
        });
    }

    document.querySelectorAll('[data-stock-value]').forEach((p) => makeStockEditable(p));
});
