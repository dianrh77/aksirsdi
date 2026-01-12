@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" x-data="notaDinas">

        <!-- Tabs -->
        <ul class="menulistcustom">
            <li :class="{ active: activeTab === 'baru' }">
                <button type="button" @click="activeTab = 'baru'">
                    Nota Baru
                    <span class="badgecount">{{ $notaBaru->count() }}</span>
                </button>
            </li>

            <li :class="{ active: activeTab === 'selesai' }">
                <button type="button" @click="activeTab = 'selesai'">
                    Nota Selesai
                    <span class="badgecount">{{ $notaSelesai->count() }}</span>
                </button>
            </li>

            <li :class="{ active: activeTab === 'rejected' }">
                <button type="button" @click="activeTab = 'rejected'">
                    Nota Ditolak
                    <span class="badgecount">{{ $notaReject->count() }}</span>
                </button>
            </li>
        </ul>


        <!-- Panel -->
        <div class="panel border-[#e0e6ed] px-0 dark:border-[#1b2e4b]">

            <div class="flex items-center justify-between px-5 mb-5">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white" x-text="title"></h2>

                <a href="{{ route('nota.create') }}"
                    class="btn btn-primary flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm">
                    + Buat Nota Dinas
                </a>
            </div>

            <div class="invoice-table px-5 pb-5">

                <div x-show="activeTab === 'baru'">
                    <table id="tableBaru" class="whitespace-nowrap w-full"></table>
                </div>

                <div x-show="activeTab === 'selesai'">
                    <table id="tableSelesai" class="whitespace-nowrap w-full"></table>
                </div>

                <div x-show="activeTab === 'rejected'">
                    <table id="tableReject" class="whitespace-nowrap w-full"></table>
                </div>

            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/simple-datatables.js') }}"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('notaDinas', () => ({

                baru: @json($notaBaru),
                selesai: @json($notaSelesai),
                rejected: @json($notaReject),
                activeTab: 'baru',

                get title() {
                    if (this.activeTab === 'baru') return 'Nota Dinas Baru';
                    if (this.activeTab === 'selesai') return 'Nota Dinas Selesai';
                    return 'Nota Dinas Ditolak';
                },

                init() {
                    this.initTable('baru');
                    this.initTable('selesai');
                    this.initTable('rejected');
                },

                prepare(items) {
                    return items.map(i => [
                        JSON.stringify({
                            id: i.id,
                            status: i.status
                        }),
                        i.nomor_nota,
                        i.judul,
                        (i.penerimas && i.penerimas.length > 0) ?
                        i.penerimas.map(p => p.user?.name ?? '-').join(' -- ') :
                        '-',
                        i.status
                    ]);
                },

                badge(status) {
                    if (status === 'baru' || status === 'menunggu_validasi') return 'warning';
                    if (status === 'selesai') return 'success';
                    if (status === 'rejected') return 'danger';
                    return 'secondary';
                },

                initTable(type) {
                    let items = (type === 'baru') ? this.baru : (type === 'selesai') ? this.selesai :
                        this.rejected;
                    let tableId = (type === 'baru') ? '#tableBaru' : (type === 'selesai') ?
                        '#tableSelesai' : '#tableReject';

                    new simpleDatatables.DataTable(tableId, {
                        data: {
                            headings: ['Aksi', 'Nomor Nota', 'Judul', 'Kepada', 'Status'],
                            data: this.prepare(items)
                        },
                        columns: [{
                                select: 4,
                                render: (status) => `
                            <span class="badge badge-outline-${this.badge(status)}">${status}</span>
                        `
                            },
                            {
                                select: 0,
                                render: (cell) => {
                                    let data = JSON.parse(cell);
                                    let id = data.id;
                                    let status = data.status;

                                    let actions =
                                        `<div class="flex items-center gap-1">
                                <a href="/nota-dinas/inbox/reply/${id}" class="btn btn-sm btn-outline-primary">👁️ Lihat</a>`;

                                    // hanya bisa edit saat masih baru/menunggu_validasi
                                    if (status === 'baru' || status ===
                                        'menunggu_validasi') {
                                        actions +=
                                            ` <a href="/nota-dinas/${id}/edit" class="btn btn-sm btn-outline-warning">✏️ Edit</a>`;
                                    }

                                    actions += `</div>`;
                                    return actions;
                                }
                            }
                        ]
                    });
                }

            }))
        });
    </script>
@endsection
