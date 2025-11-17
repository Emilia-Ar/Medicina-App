<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl lg:text-3xl text-gray-800 dark:text-gray-100 leading-tight">
            Mis tomas de hoy: {{ $medication->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8">
                <div x-data="{ stockModal: false, deleteModal: false }">

                    <div class="flex flex-col md:flex-row gap-8">
                        @if ($medication->photo_path)
                            <div>
                                <img src="{{ Storage::url($medication->photo_path) }}" alt="Foto de {{ $medication->name }}"
                                     class="w-48 h-48 object-cover rounded-lg shadow-md border-4 border-gray-200 dark:border-gray-700">
                            </div>
                        @endif
                        
                        <div class="space-y-4 text-xl text-gray-800 dark:text-gray-200 flex-1">
                            <p><strong>Descripción:</strong> {{ $medication->description ?? 'No especificada' }}</p>
                            <p>
                                <strong>Horario:</strong> Cada 
                                <strong>{{ $medication->frequency_hours }} horas</strong>,
                                empezando a las
                                <strong>{{ \Carbon\Carbon::parse($medication->start_time)->format('h:i A') }}</strong>.
                            </p>

                            <!--           ICONO DE DOSIS PRINCIPAL         -->
                            

                            <div class="flex items-center text-xl text-gray-800 dark:text-gray-200">
                                <strong class="flex-shrink-0">Dosis:</strong>
                                <div class="flex items-center ml-3">

                                    <div class="w-8 h-8 flex items-center justify-center text-blue-500" aria-hidden="true">
                                        @switch($medication->dose_type)

                                            {{-- GOTAS --}}
                                            @case('drop')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                                    viewBox="0 0 24 24" fill="#2563eb" stroke="#2563eb" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 2s7 7 7 12a7 7 0 0 1-14 0c0-5 7-12 7-12z"/>
                                                </svg>
                                                @break

                                            {{-- MEDIA PASTILLA --}}
                                            @case('half')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                                    viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10" fill="none"/>
                                                    <path d="M12 2 A10 10 0 0 1 12 22 Z" fill="#2563eb"/>
                                                </svg>
                                                @break

                                            {{-- UN CUARTO DE PASTILLA --}}
                                            @case('quarter')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                                    viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10" fill="none"/>
                                                    <path d="M12 12 L12 2 A10 10 0 0 1 22 12 Z" fill="#2563eb"/>
                                                </svg>
                                                @break

                                            {{-- INYECTABLE --}}
                                            @default
                                                @if($medication->stock_unit === 'inyectables')
                                                    <i class="fa-solid fa-syringe fa-lg text-blue-500"></i>
                                                @else
                                                    {{-- PASTILLA ENTERA --}}
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                                        viewBox="0 0 24 24" fill="#2563eb" stroke="#2563eb" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10"/>
                                                    </svg>
                                                @endif

                                        @endswitch
                                    </div>

                                    <span class="ms-2">
                                        {{ $medication->dose_quantity }}
                                        @if($medication->dose_type === 'half') media(s)
                                        @elseif($medication->dose_type === 'quarter') cuarto(s)
                                        @elseif($medication->dose_type === 'drop') gota(s)
                                        @else unidad(es)
                                        @endif
                                        por toma.
                                    </span>
                                </div>
                            </div>
                            <div>
                                <strong>Stock:</strong>
                                
                                @php $contableTypes = ['unit', 'half', 'quarter']; @endphp

                                @if (in_array($medication->dose_type, $contableTypes))
                                    @php
                                        $stockPercentage = ($medication->current_stock > 0 && $medication->total_stock > 0) ? ($medication->current_stock / $medication->total_stock) * 100 : 0;
                                        $stockColor = 'bg-green-500';
                                        if ($stockPercentage < 50) $stockColor = 'bg-yellow-500';
                                        if ($stockPercentage < 20) $stockColor = 'bg-red-500';
                                    @endphp
                                    <span class="font-bold {{ str_replace('bg-', 'text-', $stockColor) }} dark:{{ str_replace('bg-', 'text-', $stockColor) }}-300">
                                        {{ $medication->current_stock }} / {{ $medication->total_stock }} {{ $medication->stock_unit }}
                                    </span>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-6 mt-2">
                                        <div class="{{ $stockColor }} h-6 rounded-full" style="width: {{ $stockPercentage }}%"></div>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="font-bold text-blue-600 dark:text-blue-400 text-2xl">
                                            {{ $medication->current_stock }} {{ $medication->stock_unit }} restantes
                                        </span>
                                        
                                        <form action="{{ route('medications.useStock', $medication) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="px-4 py-2 bg-yellow-500 text-white text-sm font-bold rounded-lg hover:bg-yellow-600"
                                                    title="Presiona esto cuando se te acabe un gotero/inyectable">
                                                Marcar 1 como Agotado
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end flex-wrap gap-4">
                        <a href="{{ route('medications.edit', $medication) }}"
                           class="inline-flex items-center px-6 py-3 bg-gray-600 dark:bg-gray-500 border border-transparent rounded-lg font-semibold text-xl text-white hover:bg-gray-700 active:bg-gray-800">
                            Editar
                        </a>
                        <button @click="stockModal = true"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-xl text-white hover:bg-blue-700 active:bg-blue-800">
                            Añadir Stock
                        </button>
                        <button @click="deleteModal = true"
                                class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-lg font-semibold text-xl text-white hover:bg-red-700 active:bg-red-800">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>

            @if($pastPendingTakes->isNotEmpty())
                <div class="p-6 bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200 text-lg rounded-lg shadow-lg">
                    <h3 class="font-bold text-xl dark:text-red-100">¡Atención! Tienes {{ $pastPendingTakes->count() }} tomas retrasadas.</h3>
                    <p>Por favor, revisa y marca si las tomaste.</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8">
                <h3 class="text-3xl font-light text-gray-900 dark:text-gray-100 mb-6">
                    Tomas Programadas para Hoy
                </h3>

                <div class="space-y-6">

                    @forelse ($todaysTakes as $take)

                        @php
                            $now = now();
                            $scheduledTime = $take->scheduled_at;
                            $startTime = $scheduledTime->copy()->subMinutes(30);
                            $endTime = $scheduledTime->copy()->addMinutes(30);

                            $isCompleted = $take->completed_at;
                            $isActiveWindow = $now->between($startTime, $endTime);
                            $isMissed = !$isCompleted && $now > $endTime;
                            $isFuture = !$isCompleted && $now < $startTime;
                        @endphp

                        <div @class([
                            'p-6 rounded-lg border-l-8 shadow-md flex items-center justify-between',
                            'bg-yellow-100 dark:bg-yellow-900 border-yellow-400 dark:border-yellow-600' => $isActiveWindow && !$isCompleted,
                            'bg-red-100 dark:bg-red-900 border-red-400 dark:border-red-600' => $isMissed,
                            'bg-green-100 dark:bg-green-900 border-green-400 dark:border-green-600 opacity-70' => $isCompleted,
                            'bg-gray-100 dark:bg-gray-700 border-gray-400 dark:border-gray-600 opacity-60' => $isFuture,
                        ])>

                            <div>
                                <span class="text-4xl font-bold text-gray-800 dark:text-gray-100">
                                    {{ $take->scheduled_at->format('h:i A') }}
                                </span>

                                <p class="text-xl text-gray-700 dark:text-gray-300 mt-2 flex items-center">

                                    
                                    <!-- ÍCONO DENTRO DE CADA TOMA -->
                                    

                                    @switch($take->medication->dose_type)

                                        @case('drop')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="#2563eb" stroke="#2563eb" stroke-width="2">
                                                <path d="M12 2s7 7 7 12a7 7 0 0 1-14 0c0-5 7-12 7-12z"/>
                                            </svg>
                                            @break

                                        @case('half')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
                                                <circle cx="12" cy="12" r="10" fill="none"/>
                                                <path d="M12 2 A10 10 0 0 1 12 22 Z" fill="#2563eb"/>
                                            </svg>
                                            @break

                                        @case('quarter')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
                                                <circle cx="12" cy="12" r="10" fill="none"/>
                                                <path d="M12 12 L12 2 A10 10 0 0 1 22 12 Z" fill="#2563eb"/>
                                            </svg>
                                            @break

                                        @default
                                            @if($take->medication->stock_unit === 'inyectables')
                                                <i class="fa-solid fa-syringe text-blue-500 text-2xl mr-2"></i>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="#2563eb" stroke="#2563eb" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"/>
                                                </svg>
                                            @endif

                                    @endswitch

                                    <span class="ml-2">
                                        Dosis: {{ $take->medication->dose_quantity }}
                                        @if($take->medication->dose_type === 'half') media(s)
                                        @elseif($take->medication->dose_type === 'quarter') cuarto(s)
                                        @elseif($take->medication->dose_type === 'drop') gota(s)
                                        @else unidad(es)
                                        @endif
                                    </span>
                                </p>
                            </div>

                            @if ($isCompleted)
                                <div class="text-green-700 dark:text-green-300 text-lg font-semibold flex items-center">
                                    ✔ Tomada a las {{ $take->completed_at->format('h:i A') }}
                                </div>

                            @elseif ($isActiveWindow)
                                <form action="{{ route('takes.complete', $take) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" type_COMPLETAR>
                                        ✔ Marcar como Tomada
                                    </button>
                                </form>

                            @elseif ($isMissed)
                                <button type="button" type_COMPLETAR_DISABLED_RED disabled>
                                    ✖ Toma Omitida
                                </button>

                            @elseif ($isFuture)
                                <button type="button" type_COMPLETAR_DISABLED disabled>
                                    ⏳ Aún no es la hora
                                </button>
                            @endif
                        </div>

                    @empty
                        <div class="text-center text-gray-600 dark:text-gray-400 text-xl p-8">
                            <p>No hay más tomas programadas por hoy para este medicamento.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <style>
        [type_COMPLETAR] {
            width: 100%;
            padding: 1rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: white;
            background-color: #16a34a;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease-in-out;
        }

        [type_COMPLETAR]:hover {
            background-color: #15803d;
        }

        [type_COMPLETAR_DISABLED] {
            width: 100%;
            padding: 1rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: #9ca3af;
            background-color: #e5e7eb;
            border: none;
            border-radius: 0.5rem;
            cursor: not-allowed;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            opacity: 0.7;
        }

        .dark [type_COMPLETAR_DISABLED] {
            background-color: #374151;
            color: #6b7280;
        }

        [type_COMPLETAR_DISABLED_RED] {
            width: 100%;
            padding: 1rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: #b91c1c;
            background-color: #fecaca;
            border-radius: 0.5rem;
            cursor: not-allowed;
            display: inline-flex;
            justify-content: center;
            opacity: 0.8;
        }

        .dark [type_COMPLETAR_DISABLED_RED] {
            background-color: #450a0a;
            color: #f87171;
        }
    </style>
</x-app-layout>
