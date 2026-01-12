@extends('layouts.master')
@section('content')
    <div class="p-6" x-data="notaInbox">

        <!-- Tabs Header -->
        <ul class="menulistcustom mb-5">

            @if ($level == 3)
                <li :class="{ active: activeTab === 'validasi' }">
                    <button type="button" @click="activeTab = 'validasi'">
                        Validasi Manager
                        <span class="badgecount">{{ $notaValidasi->count() }}</span>
                    </button>
                </li>
            @endif

            <li :class="{ active: activeTab === 'diterima' }">
                <button type="button" @click="activeTab = 'diterima'">
                    Diterima
                    <span class="badgecount">{{ $notaDiterima->count() }}</span>
                </button>
            </li>

            <li :class="{ active: activeTab === 'selesai' }">
                <button type="button" @click="activeTab = 'selesai'">
                    Selesai
                    <span class="badgecount">{{ $notaSelesai->count() }}</span>
                </button>
            </li>

        </ul>

        <!-- Panel -->
        <div class="panel border-[#e0e6ed] dark:border-[#1b2e4b] p-5">

            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4" x-text="headerTitle"></h2>

            @if ($level == 3)
                <div x-show="activeTab === 'validasi'">
                    <table id="tableValidasi" class="whitespace-nowrap w-full"></table>
                </div>
            @endif

            <div x-show="activeTab === 'diterima'">
                <table id="tableDiterima" class="whitespace-nowrap w-full"></table>
            </div>

            <div x-show="activeTab === 'selesai'">
                <table id="tableSelesai" class="whitespace-nowrap w-full"></table>
            </div>

        </div>
    </div>
@endsection


@section('scripts')
    <script src="{{ asset('assets/js/simple-datatables.js') }}"></script>

    <script>
        document.addEventListener('alpine:init', () => {

            Alpine.data('notaInbox', () => ({

                validasi: @json($notaValidasi),
                diterima: @json($notaDiterima),
                selesai: @json($notaSelesai),

                level: {{ (int) $level }},
                activeTab: '{{ $level == 3 ? 'validasi' : 'diterima' }}',

                get headerTitle() {
                    if (this.activeTab === 'validasi') return 'Validasi Manager';
                    if (this.activeTab === 'diterima') return 'Nota Dinas Diterima';
                    return 'Nota Dinas Selesai';
                },

                badge(status) {
                    let color = 'secondary';
                    if (status === 'baru') color = 'warning';
                    else if (status === 'dibaca' || status === 'diproses') color = 'info';
                    else if (status === 'selesai') color = 'success';
                    else if (status === 'pending_manager') color = 'secondary';

                    return `<span class="badge badge-outline-${color}">${status}</span>`;
                },

                init() {
                    if (this.level === 3) this.initValidasi();

                    this.initDiterima();
                    this.initSelesai();
                },

                initValidasi() {
                    new simpleDatatables.DataTable("#tableValidasi", {
                        data: {
                            headings: ["Nomor", "Judul", "Dari", "Status", "Aksi"],
                            data: this.validasi.map(i => [
                                i.nota.nomor_nota,
                                i.nota.judul,
                                i.nota.pengirim.name ?? '-',
                                this.badge(i.status),
                                `<a href="/nota-dinas/inbox/validasi/${i.id}"
                            class="btn btn-sm btn-outline-primary">Review</a>`
                            ])
                        }
                    });
                },

                initDiterima() {
                    new simpleDatatables.DataTable("#tableDiterima", {
                        data: {
                            headings: ["Nomor", "Judul", "Dari", "Status", "Aksi"],
                            data: this.diterima.map(i => [
                                i.nota.nomor_nota,
                                i.nota.judul,
                                i.nota.pengirim.name ?? '-',
                                this.badge(i.status),
                                `<a href="/nota-dinas/inbox/reply/${i.nota.id}"
                    class="btn btn-sm btn-outline-primary">Lihat</a>`
                            ])
                        }
                    });
                },

                initSelesai() {
                    new simpleDatatables.DataTable("#tableSelesai", {
                        data: {
                            headings: ["Nomor", "Judul", "Dari", "Status", "Aksi"],
                            data: this.selesai.map(i => [
                                i.nota.nomor_nota,
                                i.nota.judul,
                                i.nota.pengirim.name ?? '-',
                                this.badge(i.status),
                                `<a href="/nota-dinas/inbox/reply/${i.nota.id}"
                    class="btn btn-sm btn-outline-primary">Lihat</a>`
                            ])
                        }
                    });
                }


            }));

        });
    </script>
@endsection
