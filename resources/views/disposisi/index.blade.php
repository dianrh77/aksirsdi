@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <div x-data="disposisiList">
            <script src="{{ asset('assets/js/simple-datatables.js') }}"></script>

            <div class="panel border-[#e0e6ed] px-0 dark:border-[#1b2e4b]">
                <div class="flex items-center justify-between px-5 mb-5">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                        Disposisi
                    </h2>
                    <a href="{{ route('disposisi.create') }}"
                        class="btn btn-primary flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm hover:shadow-md transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Tambah Disposisi
                    </a>
                </div>

                <div class="invoice-table px-5 pb-5">
                    <table id="disposisiTable" class="whitespace-nowrap w-full"></table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div id="detailModal"
        class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-2xl w-11/12 md:w-2/3 lg:w-1/2 flex flex-col overflow-hidden">
            <div class="flex justify-between items-center p-3 border-b bg-gray-100">
                <h3 class="font-semibold text-gray-700">📋 Detail Disposisi</h3>
                <button id="closeModal"
                    class="text-red-500 hover:text-red-700 text-lg font-bold transition-colors duration-200">✖</button>
            </div>
            <div class="p-5 text-gray-800" id="detailContent">
                <p>Memuat data...</p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('disposisiList', () => ({
                items: @json($data),
                datatable: null,
                dataArr: [],

                init() {
                    this.prepareData();
                    this.initTable();
                },

                prepareData() {
                    this.dataArr = this.items.map(i => [
                        i.id, // ← Aksi pakai ini
                        i.no_disposisi ?? '-',
                        i.pengirim_nama ?? '-',
                        i.asal_surat ?? '-',
                        i.perihal ?? '-',
                        i.jenis_disposisi ?? '-',
                        i.status ?? 'Dibuat',
                        i.umur_disposisi ?? '-',
                        i.created_at ?? '-'
                    ]);

                    Alpine.store('rows', this.dataArr);
                },


                initTable() {
                    this.datatable = new simpleDatatables.DataTable('#disposisiTable', {
                        data: {
                            headings: [
                                'Aksi',
                                'Nomor Disposisi',
                                'Dari',
                                'Asal Surat',
                                'Perihal',
                                'Jenis',
                                'Status',
                                'Umur',
                                'Tanggal'
                            ],

                            data: this.dataArr
                        },
                        columns: [{
                                select: 5,
                                render: (data) => {
                                    const color = data === 'biasa' ? 'secondary' :
                                        data === 'penting' ? 'warning' :
                                        'danger';
                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },
                            {
                                select: 6,
                                render: (data) => {
                                    const color = data === 'Dibuat' ? 'secondary' :
                                        data === 'Proses' ? 'warning' :
                                        data === 'Diteruskan ke Penerima' ? 'info' :
                                        data === 'Selesai' ? 'success' :
                                        'secondary';
                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },
                            {
                                select: 7,
                                render: (data, cell) => {
                                    try {

                                        let totalHours = 0;

                                        // Ambil angka hari
                                        const hariMatch = data.match(/(\d+)\s*hari/);
                                        if (hariMatch) {
                                            totalHours += parseInt(hariMatch[1]) * 24;
                                        }

                                        // Ambil angka jam
                                        const jamMatch = data.match(/(\d+)\s*jam/);
                                        if (jamMatch) {
                                            totalHours += parseInt(jamMatch[1]);
                                        }

                                        // menit tidak mempengaruhi jam jadi dilewatkan

                                        // Tentukan warna
                                        let color = "secondary";
                                        if (totalHours < 72) color = "success";
                                        else if (totalHours <= 144) color = "warning";
                                        else color = "danger";

                                        return `<span class="badge badge-outline-${color}">${data}</span>`;
                                    } catch (e) {
                                        console.error("Render umur error:", e);
                                        return `<span class="badge badge-outline-secondary">${data}</span>`;
                                    }
                                }
                            },
                            {
                                select: 0,
                                sortable: false,
                                render: (data) => {
                                    const url =
                                        "{{ url('kesekretariatan/disposisi') }}/" +
                                        data;
                                    return `
                                        <div class="flex items-center gap-1">
                                            <a href="${url}" class="btn btn-sm btn-outline-info" title="Lihat">
                                                👁️ Lihat
                                            </a>
                                        </div>
                                    `;
                                }
                            }

                        ],
                    });
                }
            }));
        });


        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('detailModal');
            const closeModal = document.getElementById('closeModal');
            const detailContent = document.getElementById('detailContent');

            document.addEventListener('click', async (e) => {
                if (e.target.classList.contains('view-detail')) {
                    const id = e.target.dataset.id;
                    const url = `/disposisi/${id}`;
                    const res = await fetch(url);
                    const html = await res.text();
                    detailContent.innerHTML = html;
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
