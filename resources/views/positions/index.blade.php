@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">

        <div x-data="positionsList">
            <script src="{{ asset('assets/js/simple-datatables.js') }}"></script>

            <div class="panel border-[#e0e6ed] px-0 dark:border-[#1b2e4b]">
                <div class="flex items-center justify-between px-5 mb-5">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">📌 Master Jabatan</h2>

                    <a href="{{ route('positions.create') }}"
                        class="btn btn-primary flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm hover:shadow-md transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Tambah Jabatan
                    </a>
                </div>
                <div class="panel p-6 mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xl font-semibold">Struktur Organisasi RSDI</h2>

                        <div class="flex gap-2">
                            <button type="button" id="zoomIn" class="btn btn-sm btn-outline-primary">＋</button>
                            <button type="button" id="zoomOut" class="btn btn-sm btn-outline-primary">－</button>
                            <button type="button" id="zoomReset" class="btn btn-sm btn-outline-secondary">Reset</button>
                        </div>
                    </div>

                    <div id="orgchartViewer" class="orgchart-viewer">
                        @include('positions.orgchart', ['positions' => $positions])
                    </div>
                </div>


                <div class="invoice-table px-5 pb-5">
                    <table id="positionTable" class="whitespace-nowrap w-full"></table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener("alpine:init", () => {
            Alpine.data("positionsList", () => ({
                positions: @json($positions),
                dataArr: [],
                datatable: null,

                init() {
                    this.prepareData();
                    this.initTable();
                },

                prepareData() {
                    this.dataArr = this.positions.map(p => [
                        p.name,
                        p.id
                    ]);
                },

                initTable() {
                    this.datatable = new simpleDatatables.DataTable('#positionTable', {
                        data: {
                            headings: ['Nama Jabatan', 'Aksi'],
                            data: this.dataArr
                        },
                        columns: [{
                            select: 1,
                            sortable: false,
                            render: (id) => `
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="/positions/${id}/edit" 
                                           class="btn btn-sm btn-outline-warning">✏️ Edit</a>

                                        <a href="/positions/${id}" 
                                        class="btn btn-sm btn-outline-danger"
                                        data-confirm-delete="true">
                                            🗑️ Hapus
                                        </a>
                                    </div>
                                `
                        }]
                    });
                }
            }))
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>

    <script>
        mermaid.initialize({
            startOnLoad: true
        });
    </script>

    <style>
        .orgchart-viewer {
            width: 100%;
            height: 70vh;
            /* bebas: 70vh enak */
            min-height: 520px;
            overflow: hidden;
            /* biar pan-zoom halus */
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            padding: 10px;
            position: relative;
        }

        /* jangan dipaksa mengecil */
        .orgchart-viewer svg {
            max-width: none !important;
            max-height: none !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/svg-pan-zoom@3.6.1/dist/svg-pan-zoom.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {

            // render mermaid dulu (karena startOnLoad: true kadang tidak konsisten untuk di-hook)
            if (window.mermaid) {
                try {
                    await mermaid.run();
                } catch (e) {}
            }

            const viewer = document.getElementById('orgchartViewer');
            const svg = viewer.querySelector('.mermaid svg') || viewer.querySelector('svg');
            if (!svg) return;

            // penting: tunggu 1 frame biar ukuran svg stabil
            requestAnimationFrame(() => {
                const panZoom = svgPanZoom(svg, {
                    zoomEnabled: true,
                    panEnabled: true,
                    controlIconsEnabled: false,
                    fit: true,
                    center: true,
                    minZoom: 0.2,
                    maxZoom: 10,
                });

                // ✅ ini yang bikin TIDAK kecil-kecil:
                // setelah fit+center, langsung zoom sedikit
                panZoom.resize();
                panZoom.fit();
                panZoom.center();
                panZoom.zoomBy(1.8); // kalau masih kecil, naikkan jadi 2.2

                // tombol zoom
                document.getElementById('zoomIn').addEventListener('click', () => panZoom.zoomIn());
                document.getElementById('zoomOut').addEventListener('click', () => panZoom.zoomOut());
                document.getElementById('zoomReset').addEventListener('click', () => {
                    panZoom.resetZoom();
                    panZoom.resize();
                    panZoom.fit();
                    panZoom.center();
                    panZoom.zoomBy(1.8);
                });

                window.addEventListener('resize', () => {
                    panZoom.resize();
                    panZoom.fit();
                    panZoom.center();
                    panZoom.zoomBy(1.8);
                });
            });
        });
    </script>
@endsection
