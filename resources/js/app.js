import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

if ('serviceWorker' in navigator) {
    // Only /sw.js (registered in layouts/app.blade.php) should control this origin.
    // A second, different service worker used to be registered here too; having two
    // competing workers fight over control of the same pages caused the first click
    // on a link to silently do nothing until a second click landed.
    window.addEventListener('load', () => {
        navigator.serviceWorker.getRegistrations().then((registrations) => {
            registrations.forEach((registration) => {
                if (registration.active?.scriptURL?.endsWith('/service-worker.js')) {
                    registration.unregister();
                }
            });
        }).catch(() => {});
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
