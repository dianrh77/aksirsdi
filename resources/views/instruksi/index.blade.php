{{-- @extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" x-data="instruksiTabs">
        <!-- ================= TAB HEADER ================= -->
        <ul class="menulistcustom mb-4">
            <li :class="{ active: activeTab === 'belum' }">
                <button type="button" @click="switchTab('belum')">
                    Belum Diinstruksi
                    <span class="badgecount" x-text="belum.length"></span>
                </button>
            </li>
            <li :class="{ active: activeTab === 'sudah' }">
                <button type="button" @click="switchTab('sudah')">
                    Sudah Diinstruksi
                    <span class="badgecount" x-text="sudah.length"></span>
                </button>
            </li>
        </ul>

        <!-- ================= PANEL ================= -->
        <div class="panel border-[#e0e6ed] px-0 dark:border-[#1b2e4b]">
            <div class="flex items-center justify-between px-5 mb-5">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Daftar Disposisi – {{ $jenis === 'utama' ? 'Direktur Utama' : 'Direktur Umum' }}
                </h2>
            </div>

            <div class="invoice-table px-5 pb-5">
                <div x-show="activeTab === 'belum'" x-cloak>
                    <table id="tableBelum" class="whitespace-nowrap w-full"></table>
                </div>

                <div x-show="activeTab === 'sudah'" x-cloak>
                    <table id="tableSudah" class="whitespace-nowrap w-full"></table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/simple-datatables.js') }}"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('instruksiTabs', () => ({
                all: @json($disposisi),

                belum: [],
                sudah: [],

                tableBelum: null,
                tableSudah: null,

                activeTab: 'belum',

                init() {
                    this.splitData();
                    this.initTables();
                },

                switchTab(tab) {
                    this.activeTab = tab;
                },

                splitData() {
                    this.belum = this.all.filter(i => i.aksi === 'buat');
                    this.sudah = this.all.filter(i => i.aksi === 'lihat');
                },

                rows(items) {
                    return items.map(i => [
                        JSON.stringify({
                            id: i.id,
                            aksi: i.aksi
                        }),
                        i.no_disposisi ?? '-',
                        i.asal_surat ?? '-',
                        i.perihal ?? '-',
                        i.jenis_disposisi ?? '-',
                        i.manager_approval ?? '-',
                        i.status ?? '-',
                        i.umur_disposisi ?? '-',
                        i.created_at ?? '-',
                        i.pengirim ?? '-',
                    ]);
                },

                initTables() {
                    this.tableBelum = this.createTable(
                        '#tableBelum',
                        this.rows(this.belum)
                    );

                    this.tableSudah = this.createTable(
                        '#tableSudah',
                        this.rows(this.sudah)
                    );
                },

                createTable(selector, dataRows) {
                    return new simpleDatatables.DataTable(selector, {
                        searchable: true,
                        perPage: 10,
                        fixedHeight: false,
                        data: {
                            headings: [
                                'Aksi',
                                'No Disposisi',
                                'Asal Surat',
                                'Perihal',
                                'Jenis',
                                'Manager Approval',
                                'Status',
                                'Umur',
                                'Tanggal',
                                'Pengirim (Jabatan)',
                            ],
                            data: dataRows
                        },
                        columns: [
                            // Jenis disposisi
                            {
                                select: 4,
                                render: (data) => {
                                    const val = (data || '').toLowerCase();
                                    const color =
                                        val === 'biasa' ? 'secondary' :
                                        val === 'penting' ? 'warning' :
                                        'danger';
                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },

                            // Status
                            {
                                select: 6,
                                render: (data) => {
                                    let color = 'secondary';
                                    if (data === 'Selesai') color = 'success';
                                    else if (data === 'Menunggu') color = 'warning';
                                    else if (data === 'Diproses') color = 'info';
                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },

                            // Umur
                            {
                                select: 7,
                                render: (data) => {
                                    let totalHours = 0;
                                    const h = String(data).match(/(\d+)\s*hari/);
                                    const j = String(data).match(/(\d+)\s*jam/);
                                    if (h) totalHours += parseInt(h[1]) * 24;
                                    if (j) totalHours += parseInt(j[1]);

                                    let color = 'secondary';
                                    if (totalHours < 72) color = 'success';
                                    else if (totalHours <= 144) color = 'warning';
                                    else color = 'danger';

                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },

                            // Aksi
                            {
                                select: 0,
                                sortable: false,
                                render: (cell) => {
                                    const meta = JSON.parse(cell);
                                    const id = meta.id;
                                    const aksi = meta.aksi;

                                    const tracking = `/kesekretariatan/disposisi/${id}`;
                                    const buat = `/instruksi/{{ $jenis }}/${id}`;
                                    const lihat =
                                        `/instruksi/{{ $jenis }}/${id}/edit`;

                                    let html =
                                        `<div class="flex items-center gap-1 justify-center">`;

                                    html += `
                                <a href="${tracking}" class="btn btn-sm btn-outline-info">
                                    👣 Tracking
                                </a>
                            `;

                                    if (aksi === 'buat') {
                                        html += `
                                    <a href="${buat}" class="btn btn-sm btn-outline-primary">
                                        📝 Buat Instruksi
                                    </a>
                                `;
                                    } else {
                                        html += `
                                    <a href="${lihat}" class="btn btn-sm btn-outline-success">
                                        👁️ Lihat Instruksi
                                    </a>
                                `;
                                    }

                                    html += `</div>`;
                                    return html;
                                }
                            }
                        ]
                    });
                }
            }));
        });
    </script>
@endsection --}}

@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" x-data="instruksiTabs">
        <!-- ================= TAB HEADER ================= -->
        <ul class="menulistcustom mb-4">
            {{-- KHUSUS DIREKTUR UMUM: 3 TAB --}}
            @if ($jenis === 'umum')
                <li :class="{ active: activeTab === 'belum_saya' }">
                    <button type="button" @click="switchTab('belum_saya')">
                        Belum Diinstruksi (Saya Penerima)
                        <span class="badgecount" x-text="belumSaya.length"></span>
                    </button>
                </li>
                <li :class="{ active: activeTab === 'monitoring' }">
                    <button type="button" @click="switchTab('monitoring')">
                        Disposisi lain (Monitoring)
                        <span class="badgecount" x-text="monitoring.length"></span>
                    </button>
                </li>
                <li :class="{ active: activeTab === 'sudah' }">
                    <button type="button" @click="switchTab('sudah')">
                        Sudah Diinstruksi
                        <span class="badgecount" x-text="sudah.length"></span>
                    </button>
                </li>
            @else
                {{-- DEFAULT (Direktur Utama): 2 TAB --}}
                <li :class="{ active: activeTab === 'belum' }">
                    <button type="button" @click="switchTab('belum')">
                        Belum Diinstruksi
                        <span class="badgecount" x-text="belum.length"></span>
                    </button>
                </li>
                <li :class="{ active: activeTab === 'sudah' }">
                    <button type="button" @click="switchTab('sudah')">
                        Sudah Diinstruksi
                        <span class="badgecount" x-text="sudah.length"></span>
                    </button>
                </li>
            @endif
        </ul>

        <!-- ================= PANEL ================= -->
        <div class="panel border-[#e0e6ed] px-0 dark:border-[#1b2e4b]">
            <div class="flex items-center justify-between px-5 mb-5">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Daftar Disposisi – {{ $jenis === 'utama' ? 'Direktur Utama' : 'Direktur Umum' }}
                </h2>
            </div>

            <div class="invoice-table px-5 pb-5">
                {{-- KHUSUS UMUM --}}
                @if ($jenis === 'umum')
                    <div x-show="activeTab === 'belum_saya'" x-cloak>
                        <table id="tableBelumSaya" class="whitespace-nowrap w-full"></table>
                    </div>

                    <div x-show="activeTab === 'monitoring'" x-cloak>
                        <table id="tableMonitoring" class="whitespace-nowrap w-full"></table>
                    </div>

                    <div x-show="activeTab === 'sudah'" x-cloak>
                        <table id="tableSudah" class="whitespace-nowrap w-full"></table>
                    </div>
                @else
                    {{-- DEFAULT UTAMA --}}
                    <div x-show="activeTab === 'belum'" x-cloak>
                        <table id="tableBelum" class="whitespace-nowrap w-full"></table>
                    </div>

                    <div x-show="activeTab === 'sudah'" x-cloak>
                        <table id="tableSudah" class="whitespace-nowrap w-full"></table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/simple-datatables.js') }}"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('instruksiTabs', () => ({
                all: @json($disposisi),

                // default (utama)
                belum: [],
                sudah: [],

                // khusus umum
                belumSaya: [],
                monitoring: [],

                // datatables
                tableBelum: null,
                tableSudah: null,
                tableBelumSaya: null,
                tableMonitoring: null,

                activeTab: '{{ $jenis === 'umum' ? 'belum_saya' : 'belum' }}',

                init() {
                    this.splitData();
                    this.initTables();
                },

                switchTab(tab) {
                    this.activeTab = tab;
                },

                splitData() {
                    // ===== DEFAULT UTAMA =====
                    this.belum = this.all.filter(i => i.aksi === 'buat');
                    this.sudah = this.all.filter(i => i.aksi === 'lihat');

                    // ===== KHUSUS UMUM (pakai field group dari controller) =====
                    this.belumSaya = this.all.filter(i => i.group === 'belum_saya');
                    this.monitoring = this.all.filter(i => i.group === 'monitoring');

                    // untuk tab sudah (umum) tetap sama: group=sudah
                    // tapi kalau controller kamu hanya mengisi aksi, ini tetap aman karena sudah ada this.sudah di atas
                    // kalau mau lebih presisi untuk umum:
                    if ('{{ $jenis }}' === 'umum') {
                        this.sudah = this.all.filter(i => i.group === 'sudah');
                    }
                },

                rows(items) {
                    return items.map(i => [
                        JSON.stringify({
                            id: i.id,
                            aksi: i.aksi
                        }),
                        i.no_disposisi ?? '-',
                        i.asal_surat ?? '-',
                        i.perihal ?? '-',
                        i.jenis_disposisi ?? '-',
                        i.manager_approval ?? '-',
                        i.status ?? '-',
                        i.umur_disposisi ?? '-',
                        i.created_at ?? '-',
                        i.pengirim ?? '-',
                    ]);
                },

                initTables() {
                    const jenis = '{{ $jenis }}';

                    if (jenis === 'umum') {
                        this.tableBelumSaya = this.createTable('#tableBelumSaya', this.rows(this
                            .belumSaya));
                        this.tableMonitoring = this.createTable('#tableMonitoring', this.rows(this
                            .monitoring));
                        this.tableSudah = this.createTable('#tableSudah', this.rows(this.sudah));
                    } else {
                        this.tableBelum = this.createTable('#tableBelum', this.rows(this.belum));
                        this.tableSudah = this.createTable('#tableSudah', this.rows(this.sudah));
                    }
                },

                createTable(selector, dataRows) {
                    return new simpleDatatables.DataTable(selector, {
                        searchable: true,
                        perPage: 10,
                        fixedHeight: false,
                        data: {
                            headings: [
                                'Aksi',
                                'No Disposisi',
                                'Asal Surat',
                                'Perihal',
                                'Jenis',
                                'Manager Approval',
                                'Status',
                                'Umur',
                                'Tanggal',
                                'Pengirim (Jabatan)',
                            ],
                            data: dataRows
                        },
                        columns: [
                            // Jenis disposisi
                            {
                                select: 4,
                                render: (data) => {
                                    const val = (data || '').toLowerCase();
                                    const color =
                                        val === 'biasa' ? 'secondary' :
                                        val === 'penting' ? 'warning' :
                                        'danger';
                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },

                            // Status
                            {
                                select: 6,
                                render: (data) => {
                                    let color = 'secondary';
                                    if (data === 'Selesai') color = 'success';
                                    else if (data === 'Menunggu') color = 'warning';
                                    else if (data === 'Diproses') color = 'info';
                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },

                            // Umur
                            {
                                select: 7,
                                render: (data) => {
                                    let totalHours = 0;
                                    const h = String(data).match(/(\d+)\s*hari/);
                                    const j = String(data).match(/(\d+)\s*jam/);
                                    if (h) totalHours += parseInt(h[1]) * 24;
                                    if (j) totalHours += parseInt(j[1]);

                                    let color = 'secondary';
                                    if (totalHours < 72) color = 'success';
                                    else if (totalHours <= 144) color = 'warning';
                                    else color = 'danger';

                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },

                            // Aksi
                            {
                                select: 0,
                                sortable: false,
                                render: (cell) => {
                                    const meta = JSON.parse(cell);
                                    const id = meta.id;
                                    const aksi = meta.aksi;

                                    const tracking = `/kesekretariatan/disposisi/${id}`;
                                    const buat = `/instruksi/{{ $jenis }}/${id}`;
                                    const lihat =
                                        `/instruksi/{{ $jenis }}/${id}/edit`;

                                    let html =
                                        `<div class="flex items-center gap-1 justify-center">`;

                                    html += `
                                        <a href="${tracking}" class="btn btn-sm btn-outline-info">
                                            👣 Tracking
                                        </a>
                                    `;

                                    if (aksi === 'buat') {
                                        html += `
                                            <a href="${buat}" class="btn btn-sm btn-outline-primary">
                                                📝 Buat Instruksi
                                            </a>
                                        `;
                                    } else {
                                        html += `
                                            <a href="${lihat}" class="btn btn-sm btn-outline-success">
                                                👁️ Lihat Instruksi
                                            </a>
                                        `;
                                    }

                                    html += `</div>`;
                                    return html;
                                }
                            }
                        ]
                    });
                }
            }));
        });
    </script>
@endsection
