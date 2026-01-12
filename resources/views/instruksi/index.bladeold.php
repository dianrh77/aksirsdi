@extends('layouts.master')
@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <div x-data="instruksiList">
            <script src="{{ asset('assets/js/simple-datatables.js') }}"></script>

            <div class="panel border-[#e0e6ed] px-0 dark:border-[#1b2e4b]">
                <div class="flex items-center justify-between px-5 mb-5">
                    <h2 class="text-lg font-semibold">
                        Daftar Disposisi untuk {{ $jenis === 'utama' ? 'Direktur Utama' : 'Direktur Umum' }}
                    </h2>

                </div>

                <div class="invoice-table px-5 pb-5">
                    <table id="instruksiTable" class="whitespace-nowrap w-full"></table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('instruksiList', () => ({
                items: @json($disposisi),
                datatable: null,
                dataArr: [],

                init() {
                    this.prepareData();
                    this.initTable();
                },

                prepareData() {
                    this.dataArr = this.items.map(i => {
                        // cek apakah sudah ada instruksi dari direktur login
                        const sudahInstruksi = i.instruksis?.some(
                            ins => ins.jenis_direktur === '{{ $jenis }}'
                        );

                        return [
                            sudahInstruksi ? 'lihat' : 'buat',
                            i.no_disposisi ?? '-',
                            i.pengirim?.name ?? '-',
                            i.jenis_disposisi ?? '-',
                            i.created_at ? new Date(i.created_at).toLocaleString('id-ID') :
                            '-',
                            i.status ?? 'Menunggu',
                            i.id
                        ];
                    });
                },

                initTable() {
                    this.datatable = new simpleDatatables.DataTable('#instruksiTable', {
                        searchable: true,
                        fixedHeight: false,
                        perPage: 10,
                        data: {
                            headings: [
                                'Aksi',
                                'No Disposisi',
                                'Pengirim',
                                'Jenis Disposisi',
                                'Tanggal',
                                'Status',
                                'id'

                            ],
                            data: this.dataArr
                        },
                        columns: [{
                                select: 3,
                                render: (data) => {
                                    const color = data === 'biasa' ? 'secondary' :
                                        data === 'penting' ? 'warning' :
                                        'secondary';
                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },
                            {
                                select: 5, // kolom status
                                render: (data) => {
                                    let color = 'secondary';
                                    if (data === 'Selesai') color = 'success';
                                    else if (data === 'Menunggu') color = 'warning';
                                    else if (data === 'Diproses') color = 'info';
                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },
                            {
                                select: 0, // kolom aksi
                                sortable: false,
                                render: (data, cell, row) => {
                                    const id = row.cells[6]
                                        .data; // tetap ambil ID dari kolom terakhir
                                    if (data === 'buat') {
                                        return `
                                                <div class="flex items-center justify-center">
                                                    <a href="/instruksi/${'{{ $jenis }}'}/${id}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Buat Instruksi">📝 Buat Instruksi</a>
                                                </div>`;
                                    } else {
                                        return `
                                                <div class="flex items-center justify-center">
                                                    <a href="/instruksi/${'{{ $jenis }}'}/${id}/edit"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Lihat Instruksi">👁️ Lihat</a>
                                                </div>`;
                                    }
                                }
                            },
                            {
                                select: 6, // kolom ID
                                hidden: true // 👈 sembunyikan kolom ID dari tampilan
                            }
                        ]

                    });
                }
            }));
        });
    </script>
@endsection
