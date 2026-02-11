@extends('layouts.master')
@section('content')
    <div class="p-6" x-data="suratMasukList">

        <!-- Tabs Header -->
        <ul class="menulistcustom">

            {{-- TAB UNTUK MANAJER --}}
            @if ($level === 3)
                <li :class="{ active: activeTab === 'internalMenunggu' }">
                    <button type="button" @click="activeTab = 'internalMenunggu'">
                        Menunggu Manager
                        <span class="badgecount">{{ $internalMenunggu->count() }}</span>
                    </button>
                </li>

                <li :class="{ active: activeTab === 'internalLainnya' }">
                    <button type="button" @click="activeTab = 'internalLainnya'">
                        Proses
                        <span class="badgecount">{{ $internalLainnya->count() }}</span>
                    </button>
                </li>
                <li :class="{ active: activeTab === 'internalHold' }">
                    <button type="button" @click="activeTab = 'internalHold'">
                        Hold
                        <span class="badgecount">{{ $internalHold->count() }}</span>
                    </button>
                </li>
            @else
                {{-- TAB UNTUK NON-MANAJER --}}
                <li :class="{ active: activeTab === 'internal' }">
                    <button type="button" @click="activeTab = 'internal'">
                        Internal
                        <span class="badgecount">
                            {{-- {{ $internal->whereIn('status', ['menunggu_kesra', 'menunggu_manager'])->count() }} --}}
                            {{ $internal->count() }}
                        </span>
                    </button>
                </li>
                <li :class="{ active: activeTab === 'internalHold' }">
                    <button type="button" @click="activeTab = 'internalHold'">
                        Hold
                        <span class="badgecount">{{ $internalHold->count() }}</span>
                    </button>
                </li>
            @endif

            {{-- TAB EXTERNAL KHUSUS KESEKRETARIATAN --}}
            @if (auth()->user()->role_name === 'kesekretariatan')
                <li :class="{ active: activeTab === 'external' }">
                    <button type="button" @click="activeTab = 'external'">
                        External
                        <span class="badgecount">
                            {{-- {{ $external->where('status', 'baru')->count() }} --}}
                            {{ $external->count() }}
                        </span>
                    </button>
                </li>
            @endif
        </ul>

        <!-- Panel -->
        <div class="panel border-[#e0e6ed] px-0 dark:border-[#1b2e4b]">
            <div class="flex items-center justify-between px-5 mb-5">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white" x-text="headerTitle"></h2>

                <a href="{{ route('surat_masuk.create') }}"
                    class="btn btn-primary flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Tambah Surat
                </a>
            </div>

            <div class="invoice-table px-5 pb-5">

                {{-- TABLE UNTUK MANAJER --}}
                @if ($level === 3)
                    <div x-show="activeTab === 'internalMenunggu'">
                        <table id="tableInternalMenunggu" class="whitespace-nowrap w-full"></table>
                    </div>

                    <div x-show="activeTab === 'internalLainnya'">
                        <table id="tableInternalLainnya" class="whitespace-nowrap w-full"></table>
                    </div>

                    <div x-show="activeTab === 'internalHold'">
                        <table id="tableInternalHold" class="whitespace-nowrap w-full"></table>
                    </div>
                @else
                    {{-- TABLE INTERNAL NON-MANAJER --}}
                    <div x-show="activeTab === 'internal'">
                        <table id="tableInternal" class="whitespace-nowrap w-full"></table>
                    </div>

                    <div x-show="activeTab === 'internalHold'">
                        <table id="tableInternalHold" class="whitespace-nowrap w-full"></table>
                    </div>
                @endif

                {{-- TABLE EKSTERNAL --}}
                <div x-show="activeTab === 'external'">
                    <table id="tableExternal" class="whitespace-nowrap w-full"></table>
                </div>
            </div>

        </div>


        <!-- PDF Modal -->
        <div id="pdfModal"
            class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-2xl w-11/12 h-[95vh] flex flex-col overflow-hidden">
                <div class="flex justify-between items-center p-3 border-b bg-gray-100">
                    <h3 class="font-semibold text-gray-700">📄 Lihat Surat PDF</h3>
                    <button id="closeModal" class="text-red-500 hover:text-red-700 text-lg font-bold">✖</button>
                </div>
                <div class="flex-1 bg-gray-900">
                    <iframe id="pdfFrame" src="" class="w-full h-full"></iframe>
                </div>
            </div>
        </div>

        <!-- MODAL VALIDASI -->
        <div id="modalValidasi" class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center">
            <div class="bg-white w-90 p-5 rounded-lg shadow-xl">
                <h2 class="text-lg font-semibold mb-3">Validasi</h2>
                <form x-ref="formValidasi" method="POST" action="{{ route('surat_masuk.validasi_popup') }}">
                    @csrf
                    <input type="hidden" name="surat_id" x-model="selectedSurat">
                    <div class="mb-3">
                        <label class="font-medium">Jenis Surat</label>
                        <select name="jenis_disposisi" class="form-select w-full" required>
                            <option value="biasa">Biasa</option>
                            <option value="penting">Penting</option>
                            <option value="rahasia">Rahasia</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="font-medium">Catatan</label>
                        <textarea name="catatan" rows="3" class="form-textarea w-full" placeholder="Tambahkan catatan ..." required></textarea>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="closeValidasiModal()" class="btn btn-outline-secondary">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="{{ asset('assets/js/simple-datatables.js') }}"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('suratMasukList', () => ({

                internal: @json($internal ?? []),
                external: @json($external ?? []),
                internalMenunggu: @json($internalMenunggu ?? []),
                internalLainnya: @json($internalLainnya ?? []),
                internalHold: @json($internalHold ?? []),

                activeTab: '{{ $level === 3 ? 'internalMenunggu' : 'internal' }}',
                role: '{{ auth()->user()->role_name }}',
                level: {{ (int) $level }},

                // ===============================
                //  POP UP VALIDASI
                // ===============================
                selectedSurat: null,

                openValidasiModal(id) {
                    this.selectedSurat = id;
                    document.getElementById('modalValidasi').classList.remove('hidden');
                },

                closeValidasiModal() {
                    document.getElementById('modalValidasi').classList.add('hidden');
                },

                // ===============================
                //  JUDUL TAB
                // ===============================
                get headerTitle() {
                    if (this.activeTab === 'internalMenunggu') return 'Surat Menunggu Manager';
                    if (this.activeTab === 'internalLainnya') return 'Surat Internal Lainnya';
                    if (this.activeTab === 'internalHold') return 'Surat Internal - Hold';
                    if (this.activeTab === 'internal') return 'Surat Internal';
                    return 'Surat Masuk External';
                },

                // ===============================
                // INIT TABLE
                // ===============================
                init() {
                    if (this.level === 3) {
                        this.initTable('internalMenunggu');
                        this.initTable('internalLainnya');
                        this.initTable('internalHold');
                    } else {
                        this.initTable('internal');
                        this.initTable('internalHold');
                    }

                    this.initTable('external');
                },

                prepareData(items, type) {
                    return items.map(i => {
                        const base = [
                        i.id,
                        i.no_surat ?? '-',
                        i.perihal ?? '-',
                        i.tgl_surat ?? '-',
                        JSON.stringify({
                            text: i.posisi_terakhir ?? 'Belum didisposisi',
                            time: i.posisi_waktu ?? null,
                            state: i.posisi_state ?? 'none'
                        }),
                        i.status ?? 'Baru',
                        i.jenis_surat ?? '-',
                        i.asal_surat ?? '-',
                        ];

                        if (type === 'internalHold') {
                            base.push(i.hold_reason ?? '-');
                        }

                        return base;
                    });
                },


                initTable(type) {
                    let items =
                        type === 'internalMenunggu' ? this.internalMenunggu :
                        type === 'internalLainnya' ? this.internalLainnya :
                        type === 'internalHold' ? this.internalHold :
                        type === 'internal' ? this.internal :
                        this.external;

                    let tableId =
                        type === 'internalMenunggu' ? '#tableInternalMenunggu' :
                        type === 'internalLainnya' ? '#tableInternalLainnya' :
                        type === 'internalHold' ? '#tableInternalHold' :
                        type === 'internal' ? '#tableInternal' :
                        '#tableExternal';

                    const headings = [
                        'AKSI', 'Nomor Surat', 'Perihal', 'Tanggal Surat',
                        'Posisi Terakhir', 'Status', 'Jenis Surat',
                        'Asal Surat'
                    ];

                    if (type === 'internalHold') {
                        headings.push('Alasan Hold');
                    }

                    new simpleDatatables.DataTable(tableId, {
                        data: {
                            headings,
                            data: this.prepareData(items, type)
                        },

                        columns: [{
                                select: 0,
                                sortable: false,
                                render: (data, cell, row) => {
                                    const status = row.cells[5].data.toLowerCase();
                                    let btn = `<div class='flex items-center gap-1'>`;

                                    // EDIT
                                    if ((this.level === 3 || this.level === 4) &&
                                        status === 'menunggu_manager') {
                                        btn +=
                                            `<a href="/surat_masuk/${data}/edit" class="btn btn-sm btn-outline-warning">✏️ Edit</a>`;
                                    }

                                    // PREVIEW
                                    btn +=
                                        `<button class="btn btn-sm btn-outline-success view-pdf" data-id="${data}">👁️ Preview</button>`;

                                    // VALIDASI (POPUP)
                                    if (this.level === 3 && status ===
                                        'menunggu_manager') {
                                        btn += `
                                    <a href="javascript:void(0)"
                                       @click="openValidasiModal(${data})"
                                       class="btn btn-sm btn-outline-success">
                                        ✔ Validasi
                                    </a>
                                    <a href="/surat_masuk/${data}/tolak" class="btn btn-sm btn-outline-danger">✖ Tolak</a>
                                `;
                                    }

                                    // DISPOSISI KESRA
                                    if (this.role === 'kesekretariatan' && status ===
                                        'menunggu_kesra') {
                                        btn +=
                                            `<a href="/disposisi/create?surat_id=${data}" class="btn btn-sm btn-outline-primary">📨 Disposisi</a>`;
                                    }

                                    btn += `</div>`;
                                    return btn;
                                }
                            },
                            {
                                select: 5,
                                render: (data) => {
                                    let color = 'warning';
                                    if (data === 'didisposisi') color = 'success';
                                    else if (data === 'ditolak_manager') color =
                                        'danger';
                                    else if (data === 'hold') color = 'danger';

                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },
                            {
                                select: 4,
                                sortable: false,
                                render: (cell) => {
                                    const d = JSON.parse(cell);

                                    // warna badge
                                    let color = 'secondary';
                                    if (d.state === 'initial') color = 'primary';
                                    if (d.state === 'forward') color = 'warning';

                                    // biar baris baru terbaca di HTML
                                    const text = (d.text ?? '-').replaceAll('\n',
                                        '<br>');

                                    return `
                                    <div class="text-sm">
                                        <div class="mt-1 leading-snug">${text}</div>
                                        ${d.time ? `<div class="text-xs text-gray-500 mt-1">Update: ${d.time}</div>` : ''}
                                    </div>
                                    `;
                                }
                            },

                        ]
                    });
                }

            }));
        });
    </script>

    <!-- Modal PDF Script -->
    <script>
        const modal = document.getElementById('pdfModal');
        const closeModal = document.getElementById('closeModal');
        const iframe = document.getElementById('pdfFrame');

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('view-pdf')) {
                iframe.src = `/surat_masuk/${e.target.dataset.id}/file#toolbar=1&zoom=100`;
                modal.classList.remove('hidden');
            }
        });

        closeModal.onclick = () => modal.classList.add('hidden');
        window.addEventListener('click', e => {
            if (e.target === modal) modal.classList.add('hidden');
        });
    </script>
@endsection
