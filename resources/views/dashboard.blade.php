<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('status'))
                <div class="p-6 bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-200 text-lg rounded-lg">
                    {{ session('status') }}
                </div>
            @endif
            
            @foreach ($lowStockMedications as $med)
                <div x-data="{ open: false }" class="p-6 bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200 text-lg rounded-lg shadow-lg flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-xl dark:text-red-100">¡Stock Bajo!</h3>
                        <p>Quedan {{ $med->current_stock }} unidad(es) de <strong>{{ $med->name }}</strong>.</p>
                    </div>
                    
                    <button @click="open = true" class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-lg font-semibold text-xl text-white hover:bg-red-700 active:bg-red-800 transition ease-in-out duration-150">
                        Añadir Stock
                    </button>

                    <div x-show="open" 
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 z-50 flex items-center justify-center p-4" 
                         style="background-color: rgba(0, 0, 0, 0.5); display: none;">
                        <div @click.away="open = false" 
                             x-show="open"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-90"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-90"
                             class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-8 w-full max-w-lg mx-auto">
                            
                            <h3 class="text-3xl font-light text-gray-900 dark:text-gray-100 mb-6">Añadir stock para: <strong>{{ $med->name }}</strong></h3>
                            
                            <form action="{{ route('medications.addStock', $med) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label for="new_stock_quantity_{{ $med->id }}" class="block text-xl font-medium text-gray-700 dark:text-gray-300">
                                        ¿Cuántos/as <strong class="text-blue-500">{{ $med->stock_unit }}</strong> nuevos/as vas a añadir?
                                    </label>
                                    <input type="number" name="new_stock_quantity" id="new_stock_quantity_{{ $med->id }}" min="1" required
                                           class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="Ej: 30" value="1">
                                </div>
                                <div class="flex items-center justify-end space-x-6 pt-6 mt-4 border-t dark:border-gray-700">
                                    <button type="button" @click="open = false" class="text-lg text-gray-600 dark:text-gray-300 hover:underline">
                                        Cancelar
                                    </button>
                                    <button type="submit" 
                                            class="inline-flex items-center px-8 py-4 bg-blue-600 border border-transparent rounded-lg font-semibold text-lg text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800">
                                        Confirmar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="text-center">
                <a href="{{ route('medications.create') }}" 
                   class="inline-flex items-center px-8 py-5 bg-blue-600 border border-transparent rounded-lg font-semibold text-xl text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="-ml-1 mr-3 h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Añadir Nuevo Medicamento
                </a>
            </div>

            <div id="push-notification-area" 
                 class="text-center p-6 bg-yellow-100 dark:bg-yellow-900 border-l-4 border-yellow-500 text-yellow-800 dark:text-yellow-100 text-lg rounded-lg shadow-lg" 
                 style="display: none;">
                <p class="font-bold">¡Activa las alertas!</p>
                <p>Permite las notificaciones para recibir recordatorios de tus tomas.</p>
                <button id="enable-push-notifications" 
                   class="mt-4 inline-flex items-center px-6 py-3 bg-yellow-600 border border-transparent rounded-lg font-semibold text-xl text-white hover:bg-yellow-700 active:bg-yellow-800 transition ease-in-out duration-150">
                    Activar Alertas
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    
                    @if ($medicationsToday->isEmpty())
                        <div class="text-center text-gray-600 dark:text-gray-300 text-xl p-8">
                            <p>No tienes medicamentos programados para hoy.</p>
                            <p class="mt-2">¡Añade uno para empezar!</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach ($medicationsToday as $medicationName => $takes)
                                <section>
                                    @php $medication = $takes->first()->medication; @endphp

                                    <div class="flex justify-between items-center gap-4 p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <div class="flex items-center gap-4">
                                            @if ($medication->photo_path)
                                                <img src="{{ Storage::url($medication->photo_path) }}" 
                                                     alt="Foto de {{ $medicationName }}"
                                                     class="h-24 w-24 rounded-lg object-cover border-2 border-gray-200 dark:border-gray-700 shadow-sm flex-shrink-0">
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-blue-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                  <path d="M18 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C2.1 3.75 0 5.765 0 8.25c0 7.22 9.343 10.312 9.343 10.312S18 15.47 18 8.25z" />
                                                </svg>
                                            @endif
                                            
                                            <h3 class="text-3xl font-light text-gray-900 dark:text-gray-100">
                                                {{ $medicationName }}
                                            </h3>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <a href="{{ route('medications.show', $medication) }}" class="text-lg text-blue-600 hover:underline whitespace-nowrap">
                                                Ver historial y detalles &rarr;
                                            </a>
                                        </div>
                                    </div>
                                    
                                </section>
                            @endforeach
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>





