import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch(() => {});
    });
}

const shouldUppercaseField = (field) => {
    return false;
};

const uppercaseFieldValue = (field) => {
    return;
};

document.addEventListener('input', (event) => {
    uppercaseFieldValue(event.target);
});

document.addEventListener('change', (event) => {
    uppercaseFieldValue(event.target);
});
