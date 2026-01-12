@extends('layouts.master')
@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <div x-data="suratKeluarList">
            <script src="{{ asset('assets/js/simple-datatables.js') }}"></script>

            <div class="panel border-[#e0e6ed] px-0 dark:border-[#1b2e4b]">
                <div class="flex items-center justify-between px-5 mb-5">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                        Surat Keluar
                    </h2>
                    <a href="{{ route('surat_keluar.create') }}"
                        class="btn btn-primary flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm hover:shadow-md transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Tambah Surat
                    </a>
                </div>


                <div class="invoice-table px-5 pb-5">
                    <table id="myTable" class="whitespace-nowrap w-full"></table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Viewer PDF -->
    <div id="pdfModal"
        class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-2xl w-11/12 h-[95vh] flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="flex justify-between items-center p-3 border-b bg-gray-100 shrink-0">
                <h3 class="font-semibold text-gray-700">📄 Lihat Surat PDF</h3>
                <button id="closeModal"
                    class="text-red-500 hover:text-red-700 text-lg font-bold transition-colors duration-200">✖</button>
            </div>

            <!-- PDF viewer -->
            <div class="flex-1 bg-gray-900 flex">
                <iframe id="pdfFrame" src="" class="w-full h-full" style="border:none;"></iframe>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('suratKeluarList', () => ({
                items: @json($data),
                datatable: null,
                dataArr: [],

                init() {
                    this.prepareData();
                    this.initTable();
                },

                prepareData() {
                    this.dataArr = this.items.map(i => [
                        i.no_surat ?? '-',
                        i.tujuan_surat ?? '-',
                        i.perihal ?? '-',
                        i.tgl_surat ?? '-',
                        i.status ?? 'Baru',
                        i.id
                    ]);
                },

                initTable() {
                    this.datatable = new simpleDatatables.DataTable('#myTable', {
                        data: {
                            headings: [
                                'Nomor Surat',
                                'Tujuan Surat',
                                'Perihal',
                                'Tanggal Surat',
                                'Status',
                                'Aksi'
                            ],
                            data: this.dataArr
                        },
                        columns: [{
                                select: 4,
                                render: (data) => {
                                    const color = data === 'Baru' ? 'success' :
                                        'warning';
                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },
                            {
                                select: 5,
                                sortable: false,
                                render: (data) => `
                                <div class="flex items-left justify-content-center gap-1">
                                    <a href="/surat_keluar/${data}/edit"
                                    class="btn btn-sm btn-outline-warning"
                                    title="Edit Surat">
                                    ✏️ Edit
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-success view-pdf"
                                            data-id="${data}"
                                            title="Lihat PDF">
                                        👁️ Lihat
                                    </button>
                                </div>
                            `
                            }
                        ],
                    });
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('pdfModal');
            const closeModal = document.getElementById('closeModal');
            const iframe = document.getElementById('pdfFrame');

            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('view-pdf')) {
                    const id = e.target.dataset.id;
                    const url = `/surat_keluar/${id}/file`;
                    iframe.src = `${url}#toolbar=1&zoom=100`;
                    modal.classList.remove('hidden');
                }
            });

            closeModal.onclick = () => modal.classList.add('hidden');
            window.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.add('hidden');
            });
        });
    </script>
@endsection
