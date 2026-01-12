@extends('layouts.master')

@section('content')
    <div class="p-6">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                📄 Detail Nota Dinas
            </h2>
            <a href="{{ route('nota.index') }}" class="text-blue hover:underline">← Kembali</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- ================== CARD KIRI: DETAIL NOTA ================== --}}
            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md space-y-6">

                {{-- DETAIL --}}
                <div>
                    <p><strong>No Nota:</strong> {{ $nota->nomor_nota }}</p>
                    <p><strong>Judul:</strong> {{ $nota->judul }}</p>
                    <p><strong>Pengirim:</strong> {{ $nota->pengirim->position }}</p>
                    <p><strong>Penerima:</strong> {{ $nota->penerima->position }}</p>
                    <p><strong>Tanggal:</strong> {{ optional($nota->created_at)->format('d M Y') }}</p>

                    @if ($nota->lampiran)
                        <div class="text-right mt-2">
                            <button type="button" class="btn btn-outline-primary view-pdf"
                                data-url="{{ asset('storage/' . $nota->lampiran) }}">
                                <i class="ri-fullscreen-line text-base"></i> Lihat Lampiran
                            </button>
                        </div>
                    @endif
                </div>

                {{-- ISI --}}
                <div class="border-t border-gray-300 dark:border-gray-700 pt-4 prose dark:prose-invert">
                    {!! $nota->isi !!}
                </div>

            </div>

            {{-- ================== CARD KANAN: BALASAN ================== --}}
            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md space-y-6">

                <h3 class="text-lg font-semibold mb-3">💬 Balasan</h3>

                @if ($balasan)
                    <p><strong>Dibalas oleh:</strong> {{ $balasan->user->name }}</p>
                    <p><strong>Tanggal Balasan:</strong> {{ $balasan->created_at->format('d M Y H:i') }}</p>

                    <div class="border-t border-gray-300 dark:border-gray-700 mt-3 prose dark:prose-invert">
                        {!! $balasan->balasan !!}
                    </div>

                    @if ($balasan->lampiran)
                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-outline-primary view-pdf"
                                data-url="{{ asset('storage/' . $balasan->lampiran) }}">
                                📎 Lihat Lampiran Balasan
                            </button>
                        </div>
                    @endif
                @else
                    <p class="text-gray-500">Belum ada balasan.</p>
                @endif

                {{-- BADGE STATUS --}}
                <div class="mb-3">
                    @if ($nota->status === 'selesai')
                        Status :
                        <span class="px-3 py-1 rounded-full text-md font-semibold bg-green text-white">
                            ✔ Selesai
                        </span>
                    @else
                        Status :
                        <span class="px-3 py-1 rounded-full text-md font-semibold bg-yellow text-gray-900">
                            ⏳ Belum Dibalas
                        </span>
                    @endif
                </div>

            </div>

        </div>

    </div>

    {{-- ================== MODAL PDF ================== --}}
    <div id="pdfModal"
        class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-2xl w-11/12 h-[95vh] flex flex-col overflow-hidden">
            <div class="flex justify-between items-center p-3 border-b bg-gray-100 shrink-0">
                <h3 class="font-semibold text-gray-700">📄 Lihat Lampiran PDF</h3>
                <button id="closeModal"
                    class="text-red hover:text-red text-lg font-bold transition-colors duration-200">✖</button>
            </div>
            <div class="flex-1 bg-gray-900 flex">
                <iframe id="pdfFrame" src="" class="w-full h-full" style="border:none;"></iframe>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('pdfModal');
            const closeModal = document.getElementById('closeModal');
            const iframe = document.getElementById('pdfFrame');

            document.addEventListener('click', (e) => {
                if (e.target.closest('.view-pdf')) {
                    const url = e.target.closest('.view-pdf').dataset.url;
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
