@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <!-- Ucapan Selamat Datang -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                Assalamu'alaikum, {{ Auth::user()->name ?? 'Pengguna' }} ,
                {{ Auth::user()->primaryPosition()->name ?? 'Pengguna' }} 👋
            </h2>
        </div>


        <!-- Statistik -->
        <div class="pt-5">
            <div class="mb-6 grid grid-cols-1 gap-6 text-white sm:grid-cols-2 xl:grid-cols-4">

                <!-- Total Surat Masuk -->
                <div class="panel bg-gradient-to-r from-blue-500 to-blue-400">
                    <div class="flex justify-between">
                        <div class="text-md font-semibold">Surat Masuk Bulan Ini</div>
                    </div>
                    <div class="mt-5 flex items-center">
                        <div class="text-3xl font-bold ltr:mr-3 rtl:ml-3">{{ $suratMasuk }}</div>
                        <div class="badge bg-white/30">+{{ $suratMasuk }}</div>
                    </div>
                    <div class="mt-5 flex items-center font-semibold">
                        <svg width="24" height="24" fill="none" class="h-5 w-5 shrink-0 ltr:mr-2 rtl:ml-2"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.5"
                                d="M3.27489 15.2957C2.42496 14.1915 2 13.6394 2 12C2 10.3606 2.42496 9.80853 3.27489 8.70433C4.97196 6.49956 7.81811 4 12 4C16.1819 4 19.028 6.49956 20.7251 8.70433C21.575 9.80853 22 10.3606 22 12C22 13.6394 21.575 14.1915 20.7251 15.2957C19.028 17.5004 16.1819 20 12 20C7.81811 20 4.97196 17.5004 3.27489 15.2957Z"
                                stroke="currentColor" stroke-width="1.5" />
                            <path
                                d="M15 12C15 13.6569 13.6569 15 12 15C10.3431 15 9 13.6569 9 12C9 10.3431 10.3431 9 12 9C13.6569 9 15 10.3431 15 12Z"
                                stroke="currentColor" stroke-width="1.5" />
                        </svg>
                        Total tahun {{ date('Y') }}: {{ $totalMasuk }}
                    </div>
                </div>

                <!-- Total Surat Keluar -->
                <div class="panel bg-gradient-to-r from-fuchsia-500 to-fuchsia-400">
                    <div class="flex justify-between">
                        <div class="text-md font-semibold">Surat Keluar Bulan Ini</div>
                    </div>
                    <div class="mt-5 flex items-center">
                        <div class="text-3xl font-bold ltr:mr-3 rtl:ml-3">{{ $suratKeluar }}</div>
                        <div class="badge bg-white/30">+{{ $suratKeluar }}</div>
                    </div>
                    <div class="mt-5 flex items-center font-semibold">
                        <svg width="24" height="24" fill="none" class="h-5 w-5 shrink-0 ltr:mr-2 rtl:ml-2"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.5"
                                d="M3.27489 15.2957C2.42496 14.1915 2 13.6394 2 12C2 10.3606 2.42496 9.80853 3.27489 8.70433C4.97196 6.49956 7.81811 4 12 4C16.1819 4 19.028 6.49956 20.7251 8.70433C21.575 9.80853 22 10.3606 22 12C22 13.6394 21.575 14.1915 20.7251 15.2957C19.028 17.5004 16.1819 20 12 20C7.81811 20 4.97196 17.5004 3.27489 15.2957Z"
                                stroke="currentColor" stroke-width="1.5" />
                            <path
                                d="M15 12C15 13.6569 13.6569 15 12 15C10.3431 15 9 13.6569 9 12C9 10.3431 10.3431 9 12 9C13.6569 9 15 10.3431 15 12Z"
                                stroke="currentColor" stroke-width="1.5" />
                        </svg>
                        Total tahun {{ date('Y') }}: {{ $totalKeluar }}
                    </div>
                </div>

                <!-- Total Disposisi -->
                <div class="panel bg-gradient-to-r from-cyan-500 to-cyan-400">
                    <div class="flex justify-between">
                        <div class="text-md font-semibold">Total Disposisi</div>
                    </div>
                    <div class="mt-5 flex items-center">
                        <div class="text-3xl font-bold ltr:mr-3 rtl:ml-3">{{ $jumlahDisposisi }}</div>
                        <div class="badge bg-white/30">{{ $disposisiBelum }} Belum</div>
                    </div>
                    <div class="mt-5 flex items-center font-semibold">
                        <svg width="24" height="24" fill="none" class="h-5 w-5 shrink-0 ltr:mr-2 rtl:ml-2"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.5"
                                d="M3.27489 15.2957C2.42496 14.1915 2 13.6394 2 12C2 10.3606 2.42496 9.80853 3.27489 8.70433C4.97196 6.49956 7.81811 4 12 4C16.1819 4 19.028 6.49956 20.7251 8.70433C21.575 9.80853 22 10.3606 22 12C22 13.6394 21.575 14.1915 20.7251 15.2957C19.028 17.5004 16.1819 20 12 20C7.81811 20 4.97196 17.5004 3.27489 15.2957Z"
                                stroke="currentColor" stroke-width="1.5" />
                            <path
                                d="M15 12C15 13.6569 13.6569 15 12 15C10.3431 15 9 13.6569 9 12C9 10.3431 10.3431 9 12 9C13.6569 9 15 10.3431 15 12Z"
                                stroke="currentColor" stroke-width="1.5" />
                        </svg>
                        {{ $disposisiBelum }} belum selesai
                    </div>
                </div>

                <!-- Total Pengguna -->
                <div class="panel bg-gradient-to-r from-violet-500 to-violet-400">
                    <div class="flex justify-between">
                        <div class="text-md font-semibold">Total Pengguna</div>
                    </div>
                    <div class="mt-5 flex items-center">
                        <div class="text-3xl font-bold ltr:mr-3 rtl:ml-3">{{ $totalUser }}</div>
                        <div class="badge bg-white/30">User Terdaftar</div>
                    </div>
                    <div class="mt-5 flex items-center font-semibold">
                        <svg width="24" height="24" fill="none" class="h-5 w-5 shrink-0 ltr:mr-2 rtl:ml-2"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.5"
                                d="M12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2ZM4 20C4 16.6863 6.68629 14 10 14H14C17.3137 14 20 16.6863 20 20V21H4V20Z"
                                stroke="currentColor" stroke-width="1.5" />
                        </svg>
                        Jumlah total user sistem
                    </div>
                </div>

            </div>

            <!-- GRAFIK -->
            <div class="grid gap-6 xl:grid-cols-3">
                <!-- Grafik Surat -->
                <div class="panel h-full xl:col-span-2">
                    <div class="mb-5 flex items-center dark:text-white-light">
                        <h5 class="text-lg font-semibold">📈 Grafik Surat Masuk & Keluar Tahun {{ date('Y') }}</h5>
                    </div>
                    <div class="relative overflow-hidden mt-2">
                        <div id="chartSurat" class="rounded-lg bg-white dark:bg-black p-2"></div>
                    </div>
                </div>

                <!-- Grafik Disposisi -->
                <div class="panel h-full">
                    <div class="mb-5 flex items-center">
                        <h5 class="text-lg font-semibold dark:text-white-light">📬 Status Disposisi</h5>
                    </div>
                    <div class="overflow-hidden">
                        <div id="chartDisposisi" class="rounded-lg bg-white dark:bg-black p-2"></div>
                    </div>
                </div>
            </div>

            <!-- Aktivitas Terbaru -->
            <div class="panel mt-6">
                <h5 class="text-lg font-semibold dark:text-white-light mb-3">📜 Aktivitas Terbaru</h5>
                <ul>
                    @foreach ($aktivitas as $item)
                        <li class="border-b border-gray-200 dark:border-gray-700 py-2">
                            <span class="font-semibold">{{ $item->no_surat ?? '-' }}</span> —
                            {{ $item->perihal ?? 'Tidak ada perihal' }}
                            <span class="text-gray-500 text-sm float-right">
                                {{ \Carbon\Carbon::parse($item->tgl_surat)->translatedFormat('d F Y') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const bulan = @json($bulan);
            const chartMasuk = @json($chartMasuk);
            const chartKeluar = @json($chartKeluar);
            const disposisiTotal = {{ $jumlahDisposisi }};
            const disposisiBelum = {{ $disposisiBelum }};

            // Grafik Surat
            var optionsSurat = {
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                        name: 'Surat Masuk',
                        data: chartMasuk
                    },
                    {
                        name: 'Surat Keluar',
                        data: chartKeluar
                    }
                ],
                xaxis: {
                    categories: bulan
                },
                colors: ['#3b82f6', '#ec4899'],
                dataLabels: {
                    enabled: false
                },
                legend: {
                    position: 'top'
                },
                grid: {
                    borderColor: '#eee'
                },
            };
            new ApexCharts(document.querySelector("#chartSurat"), optionsSurat).render();

            // Grafik Disposisi
            var optionsDisposisi = {
                chart: {
                    type: 'donut',
                    height: 330
                },
                series: [disposisiBelum, disposisiTotal - disposisiBelum],
                labels: ['Belum Selesai', 'Selesai'],
                colors: ['#ef4444', '#22c55e'],
                legend: {
                    position: 'bottom'
                }
            };
            new ApexCharts(document.querySelector("#chartDisposisi"), optionsDisposisi).render();
        });
    </script>
@endsection
