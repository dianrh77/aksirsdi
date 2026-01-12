@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white">
                Assalamu'alaikum, {{ Auth::user()->name ?? 'Pengguna' }}
                ,{{ Auth::user()->primaryPosition()->name ?? 'Pengguna' }} 👋
            </h2>
            {{-- <p class="text-gray-600 dark:text-gray-400">
                Berikut ringkasan aktivitas disposisi & nota dinas yang kamu terima.
            </p> --}}
        </div>

        <div class="pt-5">

            {{-- PANEL STATISTIK --}}
            <div class="mb-6 grid grid-cols-1 gap-6 text-white sm:grid-cols-2 xl:grid-cols-4">

                {{-- Disposisi Bulan Ini --}}
                <a href="{{ route('disposisi_masuk.index') }}" class="card-panel">
                    <div
                        class="panel bg-gradient-to-r from-blue-500 to-blue-400 hover:scale-105 transform transition duration-200">
                        <div class="text-md font-semibold">Disposisi Bulan Ini</div>
                        <div class="mt-5 text-3xl font-bold">{{ $bulanIniDisposisi }}</div>
                        <p class="mt-3 font-semibold">Jumlah disposisi diterima bulan ini</p>
                    </div>
                </a>

                {{-- Nota Dinas Bulan Ini --}}
                <a href="{{ route('nota.inbox') }}" class="card-panel">
                    <div
                        class="panel bg-gradient-to-r from-cyan-500 to-cyan-400 hover:scale-105 transform transition duration-200">
                        <div class="text-md font-semibold">Nota Dinas Bulan Ini</div>
                        <div class="mt-5 text-3xl font-bold">{{ $bulanIniNota }}</div>
                        <p class="mt-3 font-semibold">Jumlah nota dinas diterima bulan ini</p>
                    </div>
                </a>

                {{-- Disposisi Belum Selesai --}}
                <a href="{{ route('disposisi_masuk.index') }}" class="card-panel">
                    <div
                        class="panel bg-gradient-to-r from-fuchsia-500 to-fuchsia-400 hover:scale-105 transform transition duration-200">
                        <div class="text-md font-semibold">Disposisi Belum Selesai</div>
                        <div class="mt-5 text-3xl font-bold">{{ $disposisiBelumSelesai }}</div>
                        <p class="mt-3 font-semibold">Perlu segera diperiksa</p>
                    </div>
                </a>

                {{-- Nota Dinas Belum Selesai --}}
                <a href="{{ route('nota.inbox') }}" class="card-panel">
                    <div
                        class="panel bg-gradient-to-r from-violet-500 to-violet-400 hover:scale-105 transform transition duration-200">
                        <div class="text-md font-semibold">Nota Dinas Belum Selesai</div>
                        <div class="mt-5 text-3xl font-bold">{{ $notaBelumSelesai }}</div>
                        <p class="mt-3 font-semibold">Perlu segera diperiksa</p>
                    </div>
                </a>

            </div>


            {{-- GRAFIK + AKTIVITAS --}}
            <div class="grid gap-6 xl:grid-cols-3">
                <div class="panel h-full xl:col-span-2">
                    <div class="mb-5 flex items-center">
                        <h5 class="text-lg font-semibold dark:text-white-light">📈 Statistik Aktivitas Saya</h5>
                    </div>
                    <div id="chartStatus" class="p-2 rounded-lg bg-white dark:bg-black"></div>
                </div>

                {{-- Aktivitas Gabungan --}}
                <div class="panel h-full">
                    <div class="mb-5 flex items-center">
                        <h5 class="text-lg font-semibold dark:text-white-light">📬 Aktivitas Terbaru</h5>
                    </div>
                    <ul>
                        @forelse ($aktivitas as $item)
                            <li class="border-b border-gray-200 dark:border-gray-700 py-2">

                                {{-- NOMOR --}}
                                <span class="font-semibold">
                                    {{ $item['nomor'] }}
                                </span>

                                {{-- CATATAN / PERIHAL --}}
                                — {{ $item['judul'] }}

                                {{-- BADGE JENIS --}}
                                <span class="px-2 py-1 text-xs rounded bg-gray-200 dark:bg-gray-700 ml-2">
                                    {{ ucfirst($item['jenis']) }}
                                </span>

                                {{-- TANGGAL --}}
                                <span class="text-gray-500 text-sm float-right">
                                    {{ \Carbon\Carbon::parse($item['tanggal'])->translatedFormat('d F Y') }}
                                </span>
                            </li>
                        @empty
                            <li class="text-gray-500 italic">Belum ada aktivitas</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chartStatus = @json($chartStatus);

            const optionsStatus = {
                chart: {
                    type: 'donut',
                    height: 350
                },
                series: Object.values(chartStatus),
                labels: Object.keys(chartStatus),
                colors: ['#f59e0b', '#10b981', '#8b5cf6'],
                legend: {
                    position: 'bottom'
                },
            };

            new ApexCharts(document.querySelector("#chartStatus"), optionsStatus).render();
        });
    </script>
@endsection
