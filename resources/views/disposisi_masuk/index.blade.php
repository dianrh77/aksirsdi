@extends('layouts.master')

@section('content')
    <div class="p-6" x-data="disposisiKanban">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <i data-lucide="inbox" class="w-6 h-6"></i>
                Disposisi Masuk
            </h2>

            <div class="flex items-center gap-3 flex-wrap">
                <!-- 🔍 Input Pencarian -->
                <div class="relative">
                    <input type="text" x-model="searchQuery" placeholder="Tracking disposisi..."
                        class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring focus:ring-blue-500 focus:outline-none">
                    <i data-lucide="search" class="absolute left-2 top-2 w-5 h-5 text-gray-400 dark:text-gray-500"></i>
                </div>

                <!-- 🗃️ Toggle Arsip -->
                <button
                    @click="activeTab = (activeTab === 'arsip' ? 'aktif' : 'arsip')"
                    class="px-3 py-2 rounded-lg text-sm font-semibold border border-gray-300 dark:border-gray-700
                           bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <span x-text="activeTab === 'arsip' ? 'Tutup Arsip' : 'Lihat Arsip'"></span>
                </button>
            </div>
        </div>

        <!-- =========================
             TAB: AKTIF (KANBAN)
        ========================== -->
        <div x-show="activeTab === 'aktif'" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <template x-for="(status, index) in statusList" :key="index">
                    <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200" x-text="status.title"></h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                :class="{
                                    'bg-yellow': status.title === 'Diterima',
                                    'bg-blue': status.title === 'Diproses',
                                    'bg-green': status.title === 'Selesai'
                                }"
                                x-text="filteredTasks(status.tasks).length + ' Disposisi'">
                            </span>
                        </div>

                        <div :id="'list-' + index"
                            class="min-h-[10px] space-y-1 bg-white dark:bg-gray-900 rounded-xl p-3 shadow-inner transition">
                            <template x-for="task in filteredTasks(status.tasks)" :key="task.id">
                                <div
                                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                                           rounded-xl shadow-sm p-4 hover:shadow-md transition">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-800 dark:text-white"
                                                x-text="task.disposisi_no ?? 'No. Disposisi Tidak Ada'"></h4>
                                        </div>
                                        <span class="text-xs px-2 py-1 rounded-full bg-gray-200 text-gray-700"
                                            x-text="task.tanggal_disposisi"></span>
                                    </div>

                                    <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                                        <p><strong>Perihal:</strong> <span x-text="task.perihal ?? '-'"></span></p>
                                        <p><strong>Jenis Disposisi:</strong> <span x-text="task.jenis_disposisi ?? '-'"></span></p>
                                        <p><strong>Catatan:</strong> <span x-text="task.catatan ?? '-'"></span></p>
                                    </div>

                                    <div class="flex justify-between items-center mt-3 border-t pt-2">
                                        <a :href="`/disposisi-masuk/${task.id}`" class="btn btn-sm btn-outline-primary">
                                            <i class="ri-file-text-line text-base"></i> <span>Lihat Detail</span>
                                        </a>
                                    </div>
                                </div>
                            </template>

                            <template x-if="filteredTasks(status.tasks).length === 0">
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
                        Arsip Disposisi (Selesai sebelum bulan ini)
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700"
                        x-text="filteredTasks(archiveTasks).length + ' Arsip'"></span>
                </div>

                <div class="space-y-2">
                    <template x-for="task in filteredTasks(archiveTasks)" :key="task.id">
                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            <div class="flex justify-between items-start gap-3">
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-gray-800 dark:text-white truncate"
                                        x-text="task.disposisi_no ?? '-'"></h4>

                                    <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1 mt-1">
                                        <p><strong>Perihal:</strong> <span x-text="task.perihal ?? '-'"></span></p>
                                        <p><strong>Jenis Disposisi:</strong> <span x-text="task.jenis_disposisi ?? '-'"></span></p>
                                        <p><strong>Catatan:</strong> <span x-text="task.catatan ?? '-'"></span></p>
                                        <p><strong>Selesai:</strong> <span x-text="task.tanggal_selesai ?? '-'"></span></p>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <a :href="`/disposisi-masuk/${task.id}`" class="btn btn-sm btn-outline-primary">
                                        <i class="ri-file-text-line text-base"></i> <span>Detail</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="filteredTasks(archiveTasks).length === 0">
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                            Tidak ada arsip ditemukan.
                        </p>
                    </template>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- biar x-cloak bener2 ngumpet saat load -->
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('disposisiKanban', () => ({
                searchQuery: '',
                activeTab: 'aktif',

                statusList: [
                    { title: 'Diterima', tasks: @json($belumDibaca ?? []) },
                    { title: 'Diproses', tasks: @json($diproses ?? []) },
                    { title: 'Selesai',  tasks: @json($selesai ?? []) },
                ],

                // arsip dari controller
                archiveTasks: @json($arsip ?? []),

                filteredTasks(tasks) {
                    if (!this.searchQuery) return tasks;
                    const q = this.searchQuery.toLowerCase();
                    return tasks.filter(t =>
                        (t.disposisi_no && t.disposisi_no.toLowerCase().includes(q)) ||
                        (t.perihal && t.perihal.toLowerCase().includes(q)) ||
                        (t.pengirim && t.pengirim.toLowerCase().includes(q)) ||
                        (t.catatan && t.catatan.toLowerCase().includes(q))
                    );
                },

                init() {
                    this.$nextTick(() => lucide.createIcons());
                }
            }));
        });
    </script>
@endsection
