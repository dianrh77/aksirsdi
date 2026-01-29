@extends('layouts.master')

@section('content')
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                Perihal: {{ $penerima->disposisi->suratMasuk->perihal ?? '-' }}
            </h2>
            <a href="{{ route('disposisi_masuk.index') }}" class="text-blue hover:underline">← Kembali</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">

            <!-- ====================== -->
            <!-- KIRI: DETAIL DISPOSISI (SCROLLABLE) -->
            <!-- ====================== -->
            <div class="md:col-span-2 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md
                flex flex-col min-h-0 overflow-hidden self-start"
                style="height: 70vh;">

                {{-- inner scroll --}}
                <div
                    class="flex-1 min-h-0 overflow-y-auto pr-2 space-y-5
                    rounded-lg border border-gray-200 dark:border-gray-700
                    bg-gray-50 dark:bg-gray-900 p-3">

                    <div class="flex justify-between items-start">
                        <div>
                            <p><strong>No Disposisi:</strong> {{ $penerima->disposisi->no_disposisi ?? '-' }}</p>
                            <p><strong>Jenis Disposisi:</strong> {{ $penerima->disposisi->jenis_disposisi ?? '-' }}</p>
                            <p><strong>Tanggal:</strong> {{ optional($penerima->disposisi->created_at)->format('d M Y') }}
                            </p>

                            <div class="text-right mt-2">
                                @if ($penerima->disposisi->suratMasuk->internalDoc)
                                    <button type="button" class="btn btn-outline-primary view-ketik"
                                        data-content='@json($penerima->disposisi->suratMasuk->internalDoc->data_isian)'>
                                        📝 Isi Surat
                                    </button>
                                @endif

                                <button type="button" class="btn btn-outline-primary view-pdf mt-2"
                                    data-url="{{ asset('storage/' . $penerima->disposisi->suratMasuk->file_pdf) }}">
                                    📄 Surat PDF
                                </button>

                                {{-- Lampiran Surat KETIK --}}
                                @if ($penerima->disposisi->suratMasuk->internalDoc && $penerima->disposisi->suratMasuk->internalDoc->lampiran_pdf)
                                    @php
                                        $lamp = $penerima->disposisi->suratMasuk->internalDoc->lampiran_pdf;
                                        $ext = strtolower(pathinfo($lamp, PATHINFO_EXTENSION));
                                        $isPdf = $ext === 'pdf';
                                    @endphp

                                    @if ($isPdf)
                                        <button type="button" class="btn btn-outline-primary view-pdf mt-2"
                                            data-url="{{ asset('storage/' . $lamp) }}">
                                            📎 Lampiran (PDF)
                                        </button>
                                    @else
                                        <a href="{{ asset('storage/' . $lamp) }}" download
                                            class="btn btn-outline-secondary mt-2">
                                            📥 Lampiran ({{ strtoupper($ext) }})
                                        </a>
                                    @endif
                                @endif

                                <a href="{{ route('disposisi_masuk.print', $penerima->id) }}" target="_blank"
                                    class="btn btn-outline-primary mt-2">
                                    🖨️ Cetak (1x, termasuk lampiran)
                                </a>
                            </div>
                        </div>

                        @if ($penerima->status !== 'Selesai')
                            <form id="selesaiForm" action="{{ route('disposisi_masuk.selesai', $penerima->id) }}"
                                method="POST" class="inline">
                                @csrf
                                <button type="button" class="btn btn-success mt-2" id="btnTandaiSelesai">
                                    Tandai Selesai
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Instruksi dari Direksi -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-3">🧭 Instruksi Direksi</h3>

                        @php
                            $instruksiUtama = $penerima->disposisi->instruksis
                                ->where('jenis_direktur', 'utama')
                                ->first();
                            $instruksiUmum = $penerima->disposisi->instruksis->where('jenis_direktur', 'umum')->first();
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 rounded-lg border bg-gray-50 dark:bg-gray-900">
                                <p class="font-semibold text-gray-800 dark:text-gray-100">Direktur Utama:</p>
                                <p class="text-gray-700 dark:text-gray-300 mt-1 text-sm">
                                    {{ $instruksiUtama->instruksi ?? 'Belum ada instruksi dari Direktur Utama.' }}
                                </p>
                                @if (!empty($instruksiUtama?->batas_waktu))
                                    <p class="text-xs text-gray-500 mt-2">
                                        ⏰ Batas Waktu:
                                        {{ \Carbon\Carbon::parse($instruksiUtama->batas_waktu)->format('d M Y') }}
                                    </p>
                                @endif
                            </div>

                            <div class="p-4 rounded-lg border bg-gray-50 dark:bg-gray-900">
                                <p class="font-semibold text-gray-800 dark:text-gray-100">Direktur Keuangan & Umum:</p>
                                <p class="text-gray-700 dark:text-gray-300 mt-1 text-sm">
                                    {{ $instruksiUmum->instruksi ?? 'Belum ada instruksi dari Direktur Umum.' }}
                                </p>
                                @if (!empty($instruksiUmum?->batas_waktu))
                                    <p class="text-xs text-gray-500 mt-2">
                                        ⏰ Batas Waktu:
                                        {{ \Carbon\Carbon::parse($instruksiUmum->batas_waktu)->format('d M Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Diteruskan ke -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">📬 Diteruskan Kepada</h3>
                        @if ($penerima->disposisi->penerima && $penerima->disposisi->penerima->count())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($penerima->disposisi->penerima as $terusan)
                                    <span
                                        class="px-3 py-1 rounded-full text-sm
                                {{ $terusan->status == 'Belum Dibaca' ? 'bg-yellow' : ($terusan->status == 'Dibaca' ? 'bg-blue' : 'bg-green') }}">
                                        {{ $terusan->penerima->primaryPosition()->name ?? 'User Tidak Dikenal' }}
                                        ({{ $terusan->status }})
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm italic">Belum ada penerima lain.</p>
                        @endif
                    </div>

                </div>
            </div>

            <!-- ====================== -->
            <!-- KANAN: CHAT FEEDBACK (SCROLLABLE) -->
            <!-- ====================== -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md flex flex-col min-h-0 overflow-hidden self-start"
                style="height: 70vh;">

                <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-4 shrink-0">
                    💬 Feedback & Diskusi
                </h3>

                <div id="chat-area"
                    class="flex-1 min-h-0 overflow-y-auto p-3 rounded-lg border border-gray-200 dark:border-gray-700
                   bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 space-y-4 relative">

                    @if ($riwayatFeedback->count())
                        @foreach ($riwayatFeedback as $item)
                            @php $isSaya = $item->user && $item->user->id == auth()->id(); @endphp

                            <div class="flex {{ $isSaya ? 'justify-end' : 'justify-start' }}">
                                <div
                                    class="max-w-[75%] px-4 py-3 rounded-2xl shadow-sm border relative
                            {{ $isSaya ? 'bg-green rounded-br-none' : 'bg-blue rounded-bl-none' }}">

                                    <div class="flex justify-between items-start">
                                        <p class="font-semibold text-sm">
                                            {{ $isSaya ? 'Anda' : optional($item->user->primaryPosition())->name ?? 'User Tidak Dikenal' }}
                                        </p>
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400 ml-2 whitespace-nowrap">
                                            {{ optional($item->updated_at)->format('d M Y H:i') }}
                                        </span>
                                    </div>

                                    <p class="mt-1 text-[13px] leading-snug">{!! nl2br(e($item->feedback)) !!}</p>

                                    @if ($item->lampiran && $item->lampiran->count())
                                        <div class="mt-2 space-y-1">
                                            @foreach ($item->lampiran as $lamp)
                                                @php
                                                    $ext = strtolower(pathinfo($lamp->file_name, PATHINFO_EXTENSION));
                                                    $isPdf = $ext === 'pdf';
                                                @endphp

                                                @if ($isPdf)
                                                    <button type="button" class="btn btn-outline-primary view-pdf"
                                                        data-url="{{ asset('storage/' . $lamp->file_path) }}">
                                                        📎 {{ $lamp->file_name }}
                                                    </button>
                                                @else
                                                    <a href="{{ asset('storage/' . $lamp->file_path) }}"
                                                        download="{{ $lamp->file_name }}"
                                                        class="btn btn-outline-secondary">
                                                        📥 {{ $lamp->file_name }}
                                                    </a>
                                                @endif

                                                @if ($item->user_id == auth()->id())
                                                    <form action="{{ route('disposisi_masuk.hapusLampiran', $lamp->id) }}"
                                                        method="POST" class="inline formHapusLampiran">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-outline-danger btn-sm btnHapusLampiran">
                                                            🗑 Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500 text-sm italic text-center">Belum ada feedback.</p>
                    @endif

                    <div
                        class="absolute left-1/2 top-0 bottom-0 border-l border-dashed border-gray-300 dark:border-gray-600 opacity-20">
                    </div>
                </div>
            </div>
        </div>


        <!-- Form Input Feedback di bawah -->
        {{-- <form method="POST" action="{{ route('disposisi_masuk.feedback', $penerima->id) }}" enctype="multipart/form-data"
            class="mt-6">
            @csrf
            <div class="flex items-center gap-2">
                <textarea name="feedback" rows="2"
                    class="flex-1 border rounded-xl px-3 py-2 text-sm text-gray-800 dark:text-gray-100 dark:bg-gray-900 resize-none focus:ring-2 focus:ring-emerald-400"
                    placeholder="Tulis tanggapan atau update status disposisi..." required>{{ old('feedback') }}</textarea>

                <input type="file" name="lampiran[]" class="hidden" id="uploadLampiran" multiple>
                <label for="uploadLampiran"
                    class="btn btn-outline-primary rounded-xl px-5 py-4 transition flex items-center text-sm font-semibold shadow">
                    📎 Lampirkan File
                </label>
                <button type="submit"
                    class="btn btn-outline-success rounded-xl px-5 py-4 transition flex items-center gap-1 text-sm font-semibold shadow">
                    <i class="ri-send-plane-line"></i> Kirim Feedback
                </button>
            </div>

            <!-- Preview nama file yang dipilih -->
            <div id="previewLampiran" class="mt-2 text-md text-blue"></div>

        </form> --}}
        <form method="POST" action="{{ route('disposisi_masuk.feedback', $penerima->id) }}" enctype="multipart/form-data"
            class="mt-6">
            @csrf

            <div class="flex flex-col md:flex-row md:items-center gap-2">
                {{-- Textarea: full width & lebih tinggi di mobile --}}
                <textarea name="feedback" rows="3"
                    class="w-full md:flex-1 border rounded-xl px-3 py-2 text-sm
                   text-gray-800 dark:text-gray-100 dark:bg-gray-900 resize-none
                   focus:ring-2 focus:ring-emerald-400
                   min-h-[90px] md:min-h-[44px]"
                    placeholder="Tulis tanggapan atau update status disposisi..." required>{{ old('feedback') }}</textarea>

                {{-- Tombol-tombol: di mobile jadi 2 kolom biar rapi --}}
                <div class="grid grid-cols-2 md:flex gap-2 w-full md:w-auto">
                    <input type="file" name="lampiran[]" class="hidden" id="uploadLampiran" multiple>
                    <label for="uploadLampiran"
                        class="btn btn-outline-primary w-full md:w-auto flex items-center justify-center h-12 px-4">
                        📎 Lampiran
                    </label>

                    <button type="submit"
                        class="btn btn-outline-success w-full md:w-auto flex items-center justify-center h-12 px-6">
                        ➤ Kirim
                    </button>
                </div>

            </div>

            <div id="previewLampiran" class="mt-2 text-sm text-blue"></div>
        </form>



        @if ($subordinateUsers->count() > 0)
            <div class="mt-8 bg-white dark:bg-gray-800 border rounded-xl p-5 shadow-md">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                    📤 Teruskan Disposisi
                </h3>

                <form action="{{ route('disposisi_masuk.teruskan', $penerima->id) }}" method="POST">
                    @csrf

                    <div class="flex items-end gap-4">

                        <!-- LABEL + SELECT -->
                        <div class="flex flex-col flex-1">
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                Pilih User yang Akan Menerima Disposisi
                            </label>

                            <select name="penerima_ids[]" id="penerima_baru" multiple class="w-full">
                                @foreach ($subordinateUsers as $u)
                                    <option value="{{ $u->id }}"
                                        data-self="{{ $u->id == auth()->id() ? '1' : '0' }}">
                                        {{ optional($u->primaryPosition())->name }} — {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                            <textarea name="catatan_teruskan" rows="2"
                                class="w-full border rounded-lg px-3 mt-2 py-2 text-sm dark:bg-gray-900 dark:text-white"
                                placeholder="Catatan penerusan (opsional), contoh: Tolong cari harga pembanding"></textarea>

                        </div>

                        <!-- TOMBOL -->
                        <button type="submit" class="btn btn-outline-primary px-5 py-2 whitespace-nowrap">
                            ➜ Teruskan
                        </button>

                    </div>
                </form>

            </div>
        @endif
    </div>

    <!-- Modal Viewer PDF -->
    <div id="pdfModal"
        class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-2xl w-11/12 h-[95vh] flex flex-col overflow-hidden">
            <div class="flex justify-between items-center p-3 border-b bg-gray-100 shrink-0">
                <h3 class="font-semibold text-gray-700">📄 Lihat Surat PDF</h3>
                <button id="closeModal"
                    class="text-red hover:text-red text-lg font-bold transition-colors duration-200">✖</button>
            </div>
            <div class="flex-1 bg-gray-900 flex">
                <iframe id="pdfFrame" src="" class="w-full h-full" style="border:none;"></iframe>
            </div>
        </div>
    </div>

    <!-- Modal Isi Mode Ketik -->
    <div id="ketikModal"
        class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">

        <div class="bg-white rounded-lg shadow-2xl w-full max-w-3xl h-[70vh] flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="flex justify-between items-center p-3 border-b bg-gray-100">
                <h3 class="font-semibold text-gray-700">📝 Isi Surat</h3>
                <button id="closeKetikModal" class="text-red text-lg font-bold">✖</button>
            </div>

            <!-- CONTENT SCROLL -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div id="ketikContent">
                </div>
            </div>
        </div>
    </div>



@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('pdfModal');
            const closeModal = document.getElementById('closeModal');
            const iframe = document.getElementById('pdfFrame');

            // Scroll otomatis ke bawah pada area chat
            const chatArea = document.getElementById('chat-area');
            chatArea.scrollTop = chatArea.scrollHeight;

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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('btnTandaiSelesai').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Disposisi ini akan ditandai sebagai selesai!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, tandai selesai',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-outline-primary',
                    cancelButton: 'btn btn-outline-danger'
                },
                buttonsStyling: false // WAJIB, agar customClass berfungsi
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('selesaiForm').submit();
                }
            });
        });
    </script>
    <style>
        .swal2-actions .btn {
            margin: 0 6px !important;
            /* kasih jarak kanan & kiri */
        }
    </style>

    <script>
        $(function() {
            $('#penerima_baru').select2({
                placeholder: "Pilih bawahan",
                allowClear: true,
                width: '100%',
                templateResult: function(data) {
                    if (!data.element) return data.text;

                    // Jika ini adalah user sendiri → tampilkan abu-abu
                    if ($(data.element).data('self') == 1) {
                        return $('<span style="color:#999;">' + data.text +
                            ' (Tidak dapat dipilih)</span>');
                    }

                    return data.text;
                }
            });

            // Cegah memilih dirinya sendiri
            $('#penerima_baru').on('select2:selecting', function(e) {
                let isSelf = $(e.params.args.data.element).data('self');
                if (isSelf == 1) {
                    e.preventDefault();
                }
            });
        });
    </script>

    <script>
        document.addEventListener('click', function(e) {

            // OPEN mode ketik modal
            let btn = e.target.closest('.view-ketik');
            if (btn) {
                let html = JSON.parse(btn.dataset.content);
                document.getElementById('ketikContent').innerHTML = html;
                document.getElementById('ketikModal').classList.remove('hidden');
            }
        });

        // CLOSE
        document.getElementById('closeKetikModal').onclick = () => {
            document.getElementById('ketikModal').classList.add('hidden');
        };
    </script>

    <script>
        document.getElementById('uploadLampiran').addEventListener('change', function() {
            let list = document.getElementById('previewLampiran');
            list.innerHTML = '';

            [...this.files].forEach(f => {
                list.innerHTML += `<div>📎 ${f.name}</div>`;
            });
        });
    </script>

    <script>
        document.addEventListener('click', function(e) {
            let btn = e.target.closest('.btnHapusLampiran');

            if (btn) {
                let form = btn.closest('form');

                Swal.fire({
                    title: 'Hapus Lampiran?',
                    text: "Lampiran ini akan dihapus permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-outline-danger',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    </script>
@endsection
