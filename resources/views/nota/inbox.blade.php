@extends('layouts.master')

@section('content')
    <div class="p-6" x-data="notaKanban">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                📨 Nota Dinas Masuk
            </h2>

            <div class="flex items-center gap-3 flex-wrap">
                <!-- Search -->
                <div class="relative">
                    <input type="text" x-model="searchQuery" placeholder="Cari nota dinas..."
                        class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg
                               bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200
                               focus:ring focus:ring-blue-500 focus:outline-none">
                    <i class="ri-search-line absolute left-2 top-2 text-gray-400"></i>
                </div>

                <!-- Toggle Arsip -->
                <button
                    @click="activeTab = (activeTab === 'arsip' ? 'aktif' : 'arsip')"
                    class="px-3 py-2 rounded-lg text-sm font-semibold border border-gray-300 dark:border-gray-700
                           bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200
                           hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <span x-text="activeTab === 'arsip' ? 'Tutup Arsip' : 'Lihat Arsip'"></span>
                </button>
            </div>
        </div>

        <!-- =========================
             TAB: AKTIF (KANBAN)
        ========================== -->
        <div x-show="activeTab === 'aktif'" x-cloak>

            <!-- KANBAN GRID -->
            <div class="grid grid-cols-1 gap-6"
                :class="{
                    'md:grid-cols-3 gap-6': columns.length === 3,
                    'md:grid-cols-2 gap-6': columns.length === 2,
                    'md:grid-cols-1 gap-6': columns.length === 1
                }">

                <!-- Template kolom -->
                <template x-for="(col, index) in columns" :key="index">
                    <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow">

                        <!-- Header Kolom -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-md font-bold text-gray-700 dark:text-gray-200" x-text="col.title"></h3>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="col.badge">
                                <span x-text="filtered(col.items).length"></span> Nota
                            </span>
                        </div>

                        <!-- List Items -->
                        <div class="space-y-2 min-h-[50px]">
                            <template x-for="item in filtered(col.items)" :key="item.id">
                                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition">

                                    <div class="flex justify-between items-start gap-2">
                                        <h4 class="font-semibold text-gray-800 dark:text-white"
                                            x-text="item.nota?.nomor_nota ?? '-'">
                                        </h4>

                                        <span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded"
                                            x-text="formatDate(item.created_at)"></span>
                                    </div>

                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 truncate"
                                        x-text="item.nota?.judul ?? '-'">
                                    </p>

                                    <p class="text-xs mt-1 text-gray-500">
                                        Dari:
                                        <span class="font-medium"
                                            x-text="item.nota?.pengirim?.primary_position?.name ?? item.nota?.pengirim?.name ?? '-'"></span>
                                    </p>

                                    <div class="mt-3 border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between">

                                        <!-- Jika manager dan ini kolom validasi -->
                                        <template x-if="col.key === 'validasi'">
                                            <a :href="`/nota-dinas/inbox/validasi/${item.id}`"
                                                class="btn btn-sm btn-outline-success">
                                                Review
                                            </a>
                                        </template>

                                        <!-- Selain itu -->
                                        <template x-if="col.key !== 'validasi'">
                                            <a :href="`/nota-dinas/inbox/reply/${item.nota.id}`"
                                                class="btn btn-sm btn-outline-primary">
                                                Lihat
                                            </a>
                                        </template>

                                    </div>
                                </div>
                            </template>

                            <!-- Jika Kosong -->
                            <template x-if="filtered(col.items).length === 0">
                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                    Tidak ada data ditemukan.
                                </p>
                            </template>
                        </div>

                    </div>
                </template>

            </div>
        </div>

        <!-- =========================
             TAB: ARSIP (TERSEMBUNYI)
        ========================== -->
        <div x-show="activeTab === 'arsip'" x-cloak class="mt-6">
            <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow p-4">

                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200">
                        Arsip Nota Dinas (Selesai sebelum bulan ini)
                    </h3>

                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                        <span x-text="filtered(arsip).length"></span> Arsip
                    </span>
                </div>

                <div class="space-y-2">
                    <template x-for="item in filtered(arsip)" :key="item.id">
                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            <div class="flex justify-between items-start gap-3">
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-gray-800 dark:text-white truncate"
                                        x-text="item.nota?.nomor_nota ?? '-'"></h4>

                                    <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1 mt-1">
                                        <p class="truncate">
                                            <strong>Judul:</strong>
                                            <span x-text="item.nota?.judul ?? '-'"></span>
                                        </p>

                                        <p>
                                            <strong>Dari:</strong>
                                            <span x-text="item.nota?.pengirim?.primary_position?.name ?? item.nota?.pengirim?.name ?? '-'"></span>
                                        </p>

                                        <p>
                                            <strong>Selesai:</strong>
                                            <span x-text="formatDate(item.updated_at)"></span>
                                        </p>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <a :href="`/nota-dinas/inbox/reply/${item.nota.id}`" class="btn btn-sm btn-outline-primary">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="filtered(arsip).length === 0">
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                            Tidak ada arsip nota dinas.
                        </p>
                    </template>
                </div>

            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('notaKanban', () => ({

                // data dari controller
                validasi: @json($notaValidasi ?? []),
                diterima: @json($notaDiterima ?? []),
                selesai: @json($notaSelesai ?? []),
                arsip: @json($notaArsip ?? []),

                level: {{ (int) $level }},
                searchQuery: '',
                activeTab: 'aktif',

                formatDate(date) {
                    if (!date) return '-';
                    const d = new Date(date);
                    return d.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });
                },

                get columns() {
                    let cols = [];

                    if (this.level === 3) {
                        cols.push({
                            key: 'validasi',
                            title: 'Perlu Divalidasi',
                            items: this.validasi,
                            badge: 'bg-yellow text-black'
                        });
                    }

                    cols.push({
                        key: 'diterima',
                        title: 'Diterima',
                        items: this.diterima,
                        badge: 'bg-blue text-white'
                    });

                    cols.push({
                        key: 'selesai',
                        title: 'Selesai',
                        items: this.selesai,
                        badge: 'bg-green text-white'
                    });

                    return cols;
                },

                filtered(list) {
                    const q = (this.searchQuery || '').toLowerCase();
                    if (!q) return list;

                    return list.filter(i =>
                        (i.nota?.nomor_nota && i.nota.nomor_nota.toLowerCase().includes(q)) ||
                        (i.nota?.judul && i.nota.judul.toLowerCase().includes(q)) ||
                        (i.nota?.pengirim?.name && i.nota.pengirim.name.toLowerCase().includes(q)) ||
                        (i.nota?.pengirim?.primary_position?.name && i.nota.pengirim.primary_position.name.toLowerCase().includes(q))
                    );
                },

            }));
        });
    </script>
@endsection
