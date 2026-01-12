@extends('layouts.master')

@section('content')
    <div class="p-6">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                ✅ Validasi Nota Dinas
            </h2>
            <a href="{{ route('nota.inbox') }}" class="text-blue hover:underline">← Kembali</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- DETAIL NOTA --}}
            <div class="md:col-span-2 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md space-y-5">

                <div>
                    <p><strong>No Nota:</strong> {{ $nota->nomor_nota }}</p>
                    <p><strong>Judul:</strong> {{ $nota->judul }}</p>
                    <p><strong>Pengirim:</strong>
                        {{ $nota->pengirim->primaryPosition()->name ?? $nota->pengirim->name }}
                    </p>
                    <p><strong>Tanggal:</strong> {{ optional($nota->created_at)->format('d M Y') }}</p>

                    @if ($nota->lampiran)
                        <div class="text-right mt-2">
                            <button type="button" class="btn btn-outline-primary view-pdf"
                                data-url="{{ asset('storage/' . $nota->lampiran) }}">
                                📎 Lihat Lampiran
                            </button>
                        </div>
                    @endif
                </div>

                <div class="border-t border-gray-300 dark:border-gray-700 pt-4 prose dark:prose-invert">
                    {!! $nota->isi !!}
                </div>
            </div>

            {{-- VALIDASI PANEL --}}
            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md space-y-4">

                <h3 class="font-semibold text-gray-700 dark:text-gray-200">
                    Tindakan Manager
                </h3>

                {{-- LIST PENERIMA --}}
                <div>
                    <h4 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">
                        📬 Penerima Setelah Validasi
                    </h4>

                    @if ($penerimaTujuan->count() > 0)
                        <ul class="list-disc ml-5 text-gray-700 dark:text-gray-300 text-sm">
                            @foreach ($penerimaTujuan as $p)
                                <li>
                                    {{ $p->user->primaryPosition()->name ?? $p->user->name }}
                                    <span class="text-xs text-gray-500">
                                        ({{ $p->user->name }})
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500 text-sm">Tidak ada penerima setelah validasi.</p>
                    @endif
                </div>

                <hr>

                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Dengan menekan tombol di bawah, Anda menyetujui nota dinas ini dan
                    meneruskannya ke penerima yang sudah ditentukan.
                </p>

                <form action="{{ route('nota.inbox.validasi.approve', $penerima->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success w-full">
                        ✅ Setujui & Kirim ke Penerima
                    </button>
                </form>

                <hr class="my-3">

                <button type="button" id="btnReject" class="btn btn-danger w-full">
                    ❌ Reject Nota Dinas
                </button>

                {{-- Modal Reject --}}
                <div id="rejectModal"
                    class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-11/12 md:w-1/2 p-5">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold text-gray-700 dark:text-gray-200">Alasan Reject</h3>
                            <button type="button" id="closeRejectModal"
                                class="text-red hover:text-red text-lg font-bold">✖</button>
                        </div>

                        <form action="{{ route('nota.inbox.validasi.reject', $penerima->id) }}" method="POST">
                            @csrf

                            <textarea name="alasan" rows="4" class="w-full border rounded-lg p-3 dark:bg-gray-900 dark:text-white"
                                placeholder="Tulis alasan reject..." required></textarea>

                            <div class="mt-4 flex justify-end gap-2">
                                <button type="button" id="cancelReject" class="btn btn-secondary">Batal</button>
                                <button type="submit" class="btn btn-danger">Kirim Reject</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>

        {{-- Modal PDF --}}
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

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('pdfModal');
            const closeModal = document.getElementById('closeModal');
            const iframe = document.getElementById('pdfFrame');

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.view-pdf');
                if (btn) {
                    const url = btn.dataset.url;
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('rejectModal');
            const btn = document.getElementById('btnReject');
            const close = document.getElementById('closeRejectModal');
            const cancel = document.getElementById('cancelReject');

            if (btn) btn.onclick = () => modal.classList.remove('hidden');
            if (close) close.onclick = () => modal.classList.add('hidden');
            if (cancel) cancel.onclick = () => modal.classList.add('hidden');

            window.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.add('hidden');
            });
        });
    </script>
@endsection
