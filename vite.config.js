import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/inventory.css', 'resources/js/inventory.js', 'resources/css/shipment-create.css', 'resources/css/shipments.css', 'resources/js/shipments.js', 'resources/css/quick-products.css', 'resources/js/quick-products.js'],
            refresh: true,
        }),
    ],
});
