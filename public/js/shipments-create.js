(function () {
    var D = window.TE_createForm || {};

    function recipientAutocomplete() {
        return {
            suggestions: [],
            showSuggestions: false,
            init: function () {
                var nameInput = document.querySelector('[name="recipient_name"]');
                if (!nameInput) return;
                var self = this;
                var debounce = null;
                nameInput.addEventListener('input', function () {
                    clearTimeout(debounce);
                    debounce = setTimeout(function () {
                        var q = nameInput.value.trim();
                        if (q.length < 2) { self.suggestions = []; self.showSuggestions = false; return; }
                        fetch(D.recipientsSearchUrl + '?q=' + encodeURIComponent(q), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(function (r) { return r.json(); })
                        .then(function (data) { self.suggestions = data; self.showSuggestions = data.length > 0; })
                        .catch(function () { self.suggestions = []; self.showSuggestions = false; });
                    }, 300);
                });
                nameInput.addEventListener('blur', function () { setTimeout(function () { self.showSuggestions = false; }, 200); });
            },
            fillRecipient: function (s) {
                function fill(name, val) { var el = document.querySelector('[name="' + name + '"]'); if (el && val) { el.value = val; el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true })); } }
                fill('recipient_name', s.name);
                fill('recipient_lastname', s.lastname);
                fill('recipient_phone', s.phone);
                fill('recipient_alt_phone', s.alt_phone);
                fill('recipient_document', s.document);
                fill('recipient_address', s.address);
                fill('recipient_neighborhood', s.neighborhood);
                fill('recipient_locality', s.locality);
                fill('recipient_city', s.city);
                if (s.city) {
                    var dept = document.querySelector('[name="recipient_department"]');
                    if (dept) dept.dispatchEvent(new Event('change', { bubbles: true }));
                }
                this.showSuggestions = false;
            }
        };
    }

    function shipmentCreateForm() {
        return {
            currentStep: D.errorsExist ? 'client' : 'client',
            steps: [
                { key: 'client', short: 'Cliente', label: 'Cliente y direccion' },
                { key: 'product', short: 'Producto', label: 'Producto, cobro y envio' },
            ],
            preview: {
                recipient: D.oldRecipientName || '',
                address: D.oldRecipientAddress || '',
                locality: D.oldLocality || '',
                content: D.oldContentDesc || '',
                paymentMethod: D.oldPaymentMethod || 'cod',
                shipping: D.oldShippingValue || 0,
                collection: D.oldCollectionValue || 0
            },
            init: function () {
                var self = this;
                this.$nextTick(function () { window.dispatchEvent(new CustomEvent('shipment-form-ready')); });
            },
            money: function (v) {
                return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(v || 0));
            },
            currentStepIndex: function () {
                return this.steps.findIndex(function (step) { return step.key === this.currentStep; }.bind(this));
            },
            currentStepLabel: function () {
                var idx = this.currentStepIndex();
                return this.steps[idx] ? this.steps[idx].label : 'Crear guia';
            },
            goToStep: function (stepKey) {
                this.currentStep = stepKey;
                var self = this;
                this.$nextTick(function () {
                    var panel = document.querySelector('[data-step-panel]:not([style*="display: none"])');
                    if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },
            previousStep: function () {
                var index = Math.max(this.currentStepIndex() - 1, 0);
                this.goToStep(this.steps[index].key);
            },
            nextStep: function () {
                if (!this.stepReady(this.currentStep)) return;
                var index = Math.min(this.currentStepIndex() + 1, this.steps.length - 1);
                this.goToStep(this.steps[index].key);
            },
            stepReady: function (step) {
                var requiredByStep = {
                    client: ['recipient_name', 'recipient_lastname', 'recipient_phone', 'recipient_department', 'recipient_locality', 'recipient_address', 'recipient_neighborhood'],
                    product: ['content_description', 'shipping_value', 'collection_value'],
                };
                var missing = (requiredByStep[step] || []).find(function (name) {
                    var el = document.querySelector('[name="' + name + '"]');
                    return !el || String(el.value || '').trim() === '';
                });
                if (!missing) return true;
                var el = missing === 'content_description'
                    ? document.querySelector('.te-product-name')
                    : document.querySelector('[name="' + missing + '"]');
                if (el) {
                    el.focus();
                    el.classList.add('ring-2', 'ring-amber-300', 'border-amber-400');
                    window.setTimeout(function () { el.classList.remove('ring-2', 'ring-amber-300', 'border-amber-400'); }, 1400);
                }
                return false;
            }
        };
    }

    var upperCaseFields = ['recipient_name', 'recipient_lastname', 'recipient_address', 'recipient_neighborhood', 'content_description', 'recipient_notes'];
    upperCaseFields.forEach(function (name) {
        document.querySelectorAll('[name="' + name + '"]').forEach(function (el) {
            el.classList.add('uppercase');
            var busy = false;
            el.addEventListener('input', function () {
                if (busy) return;
                var s = el.selectionStart, e = el.selectionEnd;
                var up = (el.value || '').toLocaleUpperCase('es-CO');
                if (el.value === up) return;
                busy = true;
                el.value = up;
                if (typeof el.setSelectionRange === 'function' && s != null) el.setSelectionRange(s, e);
                el.dispatchEvent(new Event('input', { bubbles: true }));
                busy = false;
            });
        });
    });

    document.getElementById('delivery_zone_id').addEventListener('change', function () {
        var sel = document.getElementById('delivery_zone_id');
        var opt = sel.options[sel.selectedIndex];
        var price = opt.dataset.price;
        var ship = document.getElementById('shipping_value');
        if (price && ship) { ship.value = price; ship.dispatchEvent(new Event('input', { bubbles: true })); }
    });

    document.getElementById('recipient_locality').addEventListener('blur', function () {
        var loc = document.getElementById('recipient_locality').value;
        var nbh = document.getElementById('recipient_neighborhood').value;
        var txt = (loc + ' ' + nbh).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
        if (!txt) return;
        var match = (D.deliveryZones || []).find(function (z) {
            return z.keywords.toLowerCase().split(',').map(function (k) { return k.trim(); }).some(function (k) { return txt.includes(k); });
        });
        if (match) {
            document.getElementById('delivery_zone_id').value = match.id;
            document.getElementById('delivery_zone_id').dispatchEvent(new Event('change'));
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        var lines = document.getElementById('product_lines');
        var content = document.getElementById('content_description');
        var pieces = document.getElementById('pieces');
        var collect = document.querySelector('[name="collection_value"]');
        var quickSelect = document.getElementById('quick_product_select');
        var quickBtn = document.getElementById('add_quick_product');
        var quickCards = document.querySelectorAll('.te-quick-product-card');
        var packageType = document.getElementById('package_type');
        var invSelect = document.getElementById('inventory_product_select');
        var invBtn = document.getElementById('add_inventory_product');
        if (!lines || !content) return;

        function up(v) { return String(v || '').toLocaleUpperCase('es-CO'); }
        function money(v) { return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(v || 0)); }
        function esc(v) { return String(v || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
        var form = lines.closest('form');

        function fieldValue(name) {
            var el = form.querySelector('[name="' + name + '"]');
            return el ? el.value.trim() : '';
        }

        function setText(id, value) {
            var el = document.getElementById(id);
            if (el) el.textContent = value;
        }

        function updateReadyItem(key, ready) {
            var item = document.querySelector('[data-ready-item="' + key + '"]');
            if (!item) return;
            var dot = item.querySelector('.te-ready-dot');
            item.className = 'flex items-center justify-between gap-2 rounded-md px-2.5 py-2 ' + (ready ? 'bg-emerald-50 text-emerald-800' : 'bg-white text-gray-500');
            if (dot) {
                dot.textContent = ready ? 'Listo' : 'Pendiente';
                dot.className = 'te-ready-dot ' + (ready ? 'text-emerald-700' : 'text-gray-400');
            }
        }

        function updateReadiness() {
            var paymentMethod = fieldValue('payment_method');
            var shippingValue = Number(document.getElementById('shipping_value').value || 0);
            var collectionValue = Number(fieldValue('collection_value') || 0);
            var zoneSelect = document.getElementById('delivery_zone_id');
            var selectedZone = zoneSelect.options[zoneSelect.selectedIndex];
            var zoneName = selectedZone.value ? (selectedZone.textContent || '').split(' - ')[0].trim() : '';
            var moneyReady = paymentMethod === 'cod'
                ? collectionValue > 0 && shippingValue >= 0
                : Boolean(paymentMethod && shippingValue >= 0);
            var checks = {
                client: Boolean(fieldValue('recipient_name') && fieldValue('recipient_phone')),
                address: Boolean(fieldValue('recipient_address') && fieldValue('recipient_neighborhood') && fieldValue('recipient_locality')),
                tariff: shippingValue > 0,
                product: Boolean(content.value.trim()),
                money: moneyReady,
            };
            var readyCount = 0;
            var totalChecks = Object.keys(checks).length;
            for (var key in checks) {
                if (checks[key]) readyCount++;
            }
            var percent = Math.round((readyCount / totalChecks) * 100);
            for (var key in checks) {
                updateReadyItem(key, checks[key]);
            }
            var bar = document.getElementById('te-ready-bar');
            if (bar) bar.style.width = percent + '%';
            setText('te-ready-percent', percent + '%');
            setText('te-ready-label', percent === 100 ? 'Guia lista para crear' : (totalChecks - readyCount) + ' paso(s) pendiente(s)');
            setText('te-summary-product', content.value.trim() || 'Sin producto agregado');
            var recipient = [fieldValue('recipient_name'), fieldValue('recipient_lastname')].filter(Boolean).join(' ');
            var destination = [recipient, fieldValue('recipient_locality')].filter(Boolean).join(' - ');
            setText('te-summary-destination', destination || 'Sin destinatario');
            setText('te-summary-zone', shippingValue > 0
                ? (zoneName || 'Tarifa manual') + ' - ' + money(shippingValue)
                : 'Sin tarifa asignada');
            var hint = document.getElementById('te-money-hint');
            if (hint) {
                if (paymentMethod === 'cod' && collectionValue <= 0) {
                    hint.className = 'rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800';
                    hint.textContent = 'Contraentrega sin recaudo: agrega el valor que debe pagar el cliente.';
                } else if (paymentMethod === 'cod') {
                    hint.className = 'rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800';
                    hint.textContent = 'Recaudo confirmado: ' + money(collectionValue) + '.';
                } else {
                    hint.className = 'rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-bold text-blue-800';
                    hint.textContent = 'Pago sin recaudo contraentrega. Revisa solo el valor del envio.';
                }
            }
        }

        function sync() {
            var qty = 0, price = 0;
            var invItems = [];
            var rows = lines.querySelectorAll('.te-product-row');
            rows.forEach(function (row) {
                var n = row.querySelector('.te-product-name');
                var p = row.querySelector('.te-product-price');
                var q = row.querySelector('.te-product-quantity');
                var name = up(n ? n.value : '').trim();
                var pv = Number(p ? p.value || 0 : 0);
                var qv = Math.max(Number(q ? q.value || 1 : 1), 1);
                if (n && n.value !== name) n.value = name;
                if (!name) return;
                qty += qv;
                price += pv * qv;
                var invId = row.dataset.inventoryId;
                if (invId) invItems.push({ id: Number(invId), quantity: qv });
            });
            content.value = Array.from(rows).map(function (row) {
                var n = row.querySelector('.te-product-name');
                var p = row.querySelector('.te-product-price');
                var q = row.querySelector('.te-product-quantity');
                var name = up(n ? n.value : '').trim();
                var pv = Number(p ? p.value || 0 : 0);
                var qv = Math.max(Number(q ? q.value || 1 : 1), 1);
                if (!name) return null;
                return name + ' x ' + qv + (pv > 0 ? ' - ' + money(pv) : '');
            }).filter(Boolean).join(' + ');
            content.dispatchEvent(new Event('input', { bubbles: true }));
            if (pieces) pieces.value = Math.max(qty, 1);
            if (collect && price > 0) { collect.value = price; collect.dispatchEvent(new Event('input', { bubbles: true })); }
            var invField = document.getElementById('inventory_items');
            if (invField) invField.value = JSON.stringify(invItems);
            updateReadiness();
        }

        function makeRow(name, price, qty, inventoryId) {
            name = name || '';
            price = price || 0;
            qty = qty || 1;
            var r = document.createElement('div');
            r.className = 'te-product-row';
            if (inventoryId) r.dataset.inventoryId = inventoryId;
            r.innerHTML = '<input type="text" value="' + esc(up(name)) + '" placeholder="Producto" class="te-product-name uppercase"><input type="number" min="0" step="100" value="' + price + '" placeholder="Precio" class="te-product-price"><input type="number" min="1" step="1" value="' + qty + '" placeholder="Cant" class="te-product-quantity"><button type="button" title="Eliminar">×</button>';
            r.querySelectorAll('.te-product-price, .te-product-quantity').forEach(function (i) { i.addEventListener('input', sync); });
            var pn = r.querySelector('.te-product-name');
            if (pn) { pn.addEventListener('input', sync); pn.addEventListener('change', sync); }
            var btn = r.querySelector('button');
            if (btn) btn.addEventListener('click', function () { r.remove(); sync(); });
            return r;
        }

        function addProductLine(name, price, qty, inventoryId, type) {
            if (!name) return;
            price = price || 0;
            qty = qty || 1;
            var normalizedName = up(name).trim();
            var existingRows = lines.querySelectorAll('.te-product-row');
            var existing = Array.from(existingRows).find(function (row) {
                var rowName = up(row.querySelector('.te-product-name').value || '').trim();
                var rowInventoryId = row.dataset.inventoryId || null;
                return rowName === normalizedName && String(rowInventoryId || '') === String(inventoryId || '');
            });
            if (existing) {
                var quantity = existing.querySelector('.te-product-quantity');
                if (quantity) quantity.value = Math.max(Number(quantity.value || 1), 1) + Number(qty || 1);
                if (type && packageType) {
                    packageType.value = type;
                    packageType.dispatchEvent(new Event('change', { bubbles: true }));
                }
                sync();
                return;
            }
            var emptyRow = Array.from(existingRows).find(function (r) {
                var pn = r.querySelector('.te-product-name');
                return pn && !pn.value.trim();
            });
            if (emptyRow) {
                emptyRow.querySelector('.te-product-name').value = normalizedName;
                emptyRow.querySelector('.te-product-price').value = price;
                emptyRow.querySelector('.te-product-quantity').value = qty;
                if (inventoryId) emptyRow.dataset.inventoryId = inventoryId;
            } else {
                lines.appendChild(makeRow(name, price, qty, inventoryId));
            }
            if (type && packageType) {
                packageType.value = type;
                packageType.dispatchEvent(new Event('change', { bubbles: true }));
            }
            sync();
        }

        if (quickBtn) {
            quickBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var opt = quickSelect.options[quickSelect.selectedIndex];
                var name = quickSelect.value || '';
                var type = opt ? opt.dataset.packageType || null : null;
                var price = opt ? opt.dataset.price || 0 : 0;
                addProductLine(name, price, 1, null, type);
                if (quickSelect) quickSelect.value = '';
            });
        }

        if (D.quickProductPrefill && D.quickProductPrefill.name) {
            addProductLine(D.quickProductPrefill.name, D.quickProductPrefill.price || 0, 1, null, D.quickProductPrefill.package_type || null);
        }

        quickCards.forEach(function (card) {
            card.addEventListener('click', function () {
                addProductLine(card.dataset.name || '', card.dataset.price || 0, 1, null, card.dataset.packageType || null);
            });
        });

        if (invBtn) {
            invBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var sel = invSelect;
                var opt = sel.options[sel.selectedIndex];
                var name = opt ? opt.dataset.name : null;
                var price = opt ? opt.dataset.price || 0 : 0;
                var invId = opt ? opt.value || null : null;
                var type = opt ? opt.dataset.packageType || 'merchandise' : 'merchandise';
                if (!name) return;
                addProductLine(name, price, 1, invId, type);
                if (sel) sel.value = '';
            });
        }

        if (!lines.querySelector('.te-product-row')) {
            lines.appendChild(makeRow());
            sync();
        }

        var head = document.createElement('div');
        head.className = 'te-products-head';
        head.innerHTML = '<span>Producto</span><span>Precio</span><span>Cant</span><span></span>';
        lines.before(head);

        if (form) {
            form.querySelectorAll('input, select, textarea').forEach(function (el) {
                el.addEventListener('input', updateReadiness);
                el.addEventListener('change', updateReadiness);
            });
            form.addEventListener('submit', function () { sync(); }, true);
        }
        updateReadiness();
    });

    async function loadCities(departmentId) {
        var citySelect = document.getElementById('recipient_locality');
        citySelect.innerHTML = '<option value="">Cargando...</option>';
        if (!departmentId) {
            citySelect.innerHTML = '<option value="">Selecciona un departamento</option>';
            return;
        }
        try {
            var res = await fetch('/api/cities?department_id=' + encodeURIComponent(departmentId));
            var cities = await res.json();
            citySelect.innerHTML = '<option value="">Seleccionar</option>';
            cities.forEach(function (city) {
                var opt = document.createElement('option');
                opt.value = city.name;
                opt.textContent = city.name;
                citySelect.appendChild(opt);
            });
        } catch (e) {
            citySelect.innerHTML = '<option value="">Error al cargar</option>';
        }
    }

    if (D.oldRecipientDepartment && D.oldRecipientLocality) {
        document.addEventListener('DOMContentLoaded', async function () {
            await loadCities(D.oldRecipientDepartment);
            var citySelect = document.getElementById('recipient_locality');
            var options = Array.from(citySelect.options);
            var match = options.find(function (opt) { return opt.value === D.oldRecipientLocality; });
            if (match) citySelect.value = match.value;
        });
    }
})();
