<x-app-layout>
    <x-slot name="header">
        <x-page-header eyebrow="Guias" title="Calculadora de fletes" description="Calcula el valor del envio segun peso, dimensiones y zona de entrega." />
    </x-slot>

    <div class="h-full flex flex-col p-3 lg:p-5" x-data="shippingCalculator()" x-init="init()">
        <div class="grid lg:grid-cols-2 gap-4 flex-1">
            {{-- Form --}}
            <div class="overflow-y-auto rounded-xl border border-gray-200 shadow-sm bg-white p-4 space-y-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Datos del envio</h3>

                <form method="POST" action="{{ route('shipping-rates.calculate') }}" @submit.prevent="submitCalc()">
                    @csrf

                    <div class="space-y-3">
                        <div>
                            <x-input-label for="origin" value="Ciudad origen" />
                            <select id="origin" name="origin" x-model="form.origin"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                <option value="Bogota">Bogota</option>
                                <option value="Medellin">Medellin</option>
                                <option value="Cali">Cali</option>
                                <option value="Barranquilla">Barranquilla</option>
                                <option value="Cartagena">Cartagena</option>
                                <option value="Bucaramanga">Bucaramanga</option>
                                <option value="Pereira">Pereira</option>
                                <option value="Santa Marta">Santa Marta</option>
                                <option value="Manizales">Manizales</option>
                                <option value="Ibague">Ibague</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="destination" value="Ciudad destino" />
                            <input id="destination" type="text" name="destination" x-model="form.destination"
                                list="destinations-list" autocomplete="off"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
                                placeholder="Escribe la ciudad de destino" />
                            <datalist id="destinations-list">
                                <option value="Bogota">
                                <option value="Medellin">
                                <option value="Cali">
                                <option value="Barranquilla">
                                <option value="Cartagena">
                                <option value="Bucaramanga">
                                <option value="Pereira">
                                <option value="Santa Marta">
                                <option value="Manizales">
                                <option value="Ibague">
                                <option value="Villavicencio">
                                <option value="Pasto">
                                <option value="Monteria">
                                <option value="Neiva">
                                <option value="Armenia">
                                <option value="Cucuta">
                                <option value="Sincelejo">
                                <option value="Popayan">
                                <option value="Valledupar">
                                <option value="Tunja">
                            </datalist>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <x-input-label for="weight_kg" value="Peso (kg)" />
                                <input id="weight_kg" type="number" name="weight_kg" x-model="form.weight_kg" step="0.1" min="0"
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
                                    placeholder="0.5" />
                            </div>
                            <div>
                                <x-input-label for="pieces" value="Piezas" />
                                <input id="pieces" type="number" name="pieces" x-model="form.pieces" min="1"
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
                                    placeholder="1" />
                            </div>
                        </div>

                        <div>
                            <x-input-label value="Tipo de servicio" />
                            <div class="mt-1 flex gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="service_type" value="estandar" x-model="form.service_type"
                                        class="text-blue-700 focus:ring-blue-700" />
                                    <span class="text-sm font-semibold text-gray-700">Estandar</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="service_type" value="expreso" x-model="form.service_type"
                                        class="text-blue-700 focus:ring-blue-700" />
                                    <span class="text-sm font-semibold text-gray-700">Expreso</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" :disabled="loading || !form.destination"
                            class="w-full rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-800 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Calcular flete</span>
                            <span x-show="loading">Calculando...</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Results --}}
            <div class="overflow-y-auto rounded-xl border border-gray-200 shadow-sm bg-white p-4 space-y-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Cotizacion</h3>

                <template x-if="error">
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" x-text="error"></div>
                </template>

                <template x-if="!result && !error">
                    <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm">Ingresa los datos del envio para ver las tarifas disponibles.</p>
                    </div>
                </template>

                <template x-if="result">
                    <div class="space-y-3">
                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Origen</span>
                                <span class="font-semibold text-gray-900" x-text="result.origin"></span>
                            </div>
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-gray-500">Destino</span>
                                <span class="font-semibold text-gray-900" x-text="result.destination"></span>
                            </div>
                            <template x-if="result.zone">
                                <div class="flex justify-between text-sm mt-1">
                                    <span class="text-gray-500">Zona</span>
                                    <span class="font-semibold text-blue-700" x-text="result.zone"></span>
                                </div>
                            </template>
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-gray-500">Peso</span>
                                <span class="font-semibold text-gray-900" x-text="result.weight_kg + ' kg'"></span>
                            </div>
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-gray-500">Piezas</span>
                                <span class="font-semibold text-gray-900" x-text="result.pieces"></span>
                            </div>
                        </div>

                        <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Tarifas disponibles</h4>

                        <template x-for="[key, rate] in Object.entries(result.rates)" :key="key">
                            <div class="rounded-lg border border-gray-200 bg-white p-3 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-900 text-sm" x-text="rate.carrier"></p>
                                        <p class="text-2xs text-gray-500" x-text="rate.service"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-blue-700">$ <span x-text="Number(rate.price).toLocaleString('es-CO')"></span></p>
                                        <p class="text-2xs text-gray-500">
                                            <span x-text="rate.estimated_days + ' dias habiles'"></span>
                                        </p>
                                    </div>
                                </div>
                                <template x-if="rate.note">
                                    <p class="text-2xs text-orange-600 mt-2" x-text="rate.note"></p>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function shippingCalculator() {
            return {
                form: {
                    origin: '{{ old("origin", "Bogota") }}',
                    destination: '{{ old("destination", "") }}',
                    weight_kg: '{{ old("weight_kg", "") }}' || null,
                    pieces: '{{ old("pieces", "1") }}' || 1,
                    service_type: '{{ old("service_type", "estandar") }}' || 'estandar',
                },
                result: {{ isset($result) ? 'null' : 'null' }},
                loading: false,
                error: null,

                init() {
                    @if(isset($result))
                        this.result = @json($result);
                        this.form = @json(old()->all());
                    @endif
                },

                async submitCalc() {
                    if (!this.form.destination) return;
                    this.loading = true;
                    this.error = null;

                    try {
                        const params = new URLSearchParams({
                            origin: this.form.origin || 'Bogota',
                            destination: this.form.destination,
                            weight_kg: this.form.weight_kg || 0,
                            pieces: this.form.pieces || 1,
                            service_type: this.form.service_type || 'estandar',
                        });

                        const resp = await fetch('{{ url("/api/shipping-rates") }}?' + params.toString());
                        if (!resp.ok) throw new Error('Error al calcular');
                        this.result = await resp.json();
                    } catch (e) {
                        this.error = 'No se pudieron obtener las tarifas. Intenta de nuevo.';
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>