@extends('layouts.master')

@section('content')
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                📄 Detail Disposisi
            </h2>
            <a href="{{ route('disposisi.index') }}" class="text-blue hover:underline">← Kembali</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- ============================= --}}
            {{--      BAGIAN KIRI DETAIL       --}}
            {{-- ============================= --}}
            <div class="md:col-span-2 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md space-y-5">

                <div class="flex justify-between items-start">
                    <div>
                        <p><strong>No Disposisi:</strong> {{ $penerima->no_disposisi }}</p>
                        <p><strong>Perihal:</strong> {{ $penerima->suratMasuk->perihal }}</p>
                        <p><strong>Tanggal:</strong> {{ $penerima->created_at->format('d M Y') }}</p>

                        <p class="mt-1">
                            <strong>Umur Disposisi:</strong>
                            <span class="badge badge-outline-{{ $badgeColor }}">
                                {{ $umur_disposisi }}
                            </span>
                        </p>

                        <p class="mt-1">
                            <strong>Status:</strong>
                            <span
                                class="badge {{ $penerima->status === 'Selesai' ? 'badge-outline-success' : 'badge-outline-warning' }}">
                                {{ $penerima->status }}
                            </span>
                        </p>

                        {{-- TOMBOL LIHAT SURAT --}}
                        <div class="text-right mt-2">

                            {{-- Mode Ketik Internal --}}
                            @if ($penerima->suratMasuk->internalDoc)
                                <button type="button" class="btn btn-outline-primary view-ketik"
                                    data-content='@json($penerima->suratMasuk->internalDoc->data_isian)'>
                                    📝 Isi Surat
                                </button>
                            @endif

                            {{-- PDF Utama --}}
                            <button type="button" class="btn btn-outline-primary view-pdf mt-2"
                                data-url="{{ asset('storage/' . $penerima->suratMasuk->file_pdf) }}">
                                📄 Surat PDF
                            </button>

                            {{-- Lampiran Surat KETIK --}}
                            @if ($penerima->suratMasuk->internalDoc && $penerima->suratMasuk->internalDoc->lampiran_pdf)
                                @php
                                    $lamp = $penerima->suratMasuk->internalDoc->lampiran_pdf;
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

                        </div>

                    </div>

                </div>

                {{-- ============================= --}}
                {{--     INSTRUKSI DIREKSI         --}}
                {{-- ============================= --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-3">🧭 Instruksi dari Direksi</h3>

                    @php
                        $instruksiUtama = $penerima->instruksis->where('jenis_direktur', 'utama')->first();
                        $instruksiUmum = $penerima->instruksis->where('jenis_direktur', 'umum')->first();
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Direktur Utama --}}
                        <div class="p-4 rounded-lg border bg-gray-50 dark:bg-gray-900">
                            <p class="font-semibold">Direktur Utama:</p>
                            <p class="mt-1 text-sm">
                                {{ $instruksiUtama->instruksi ?? 'Belum ada instruksi.' }}
                            </p>

                            @if ($instruksiUtama?->batas_waktu)
                                <p class="text-xs text-gray-500 mt-2">
                                    ⏰ {{ \Carbon\Carbon::parse($instruksiUtama->batas_waktu)->format('d M Y') }}
                                </p>
                            @endif
                        </div>

                        {{-- Direktur Umum --}}
                        <div class="p-4 rounded-lg border bg-gray-50 dark:bg-gray-900">
                            <p class="font-semibold">Direktur Umum:</p>
                            <p class="mt-1 text-sm">
                                {{ $instruksiUmum->instruksi ?? 'Belum ada instruksi.' }}
                            </p>

                            @if ($instruksiUmum?->batas_waktu)
                                <p class="text-xs text-gray-500 mt-2">
                                    ⏰ {{ \Carbon\Carbon::parse($instruksiUmum->batas_waktu)->format('d M Y') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>


                {{-- ============================= --}}
                {{--         DITERUSKAN KE         --}}
                {{-- ============================= --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="font-semibold mb-2">📬 Diteruskan Kepada</h3>

                    @if ($penerima->penerima->count())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($penerima->penerima as $terusan)
                                <span
                                    class="px-3 py-1 rounded-full text-sm 
                                    {{ $terusan->status == 'Belum Dibaca' ? 'bg-yellow' : ($terusan->status == 'Dibaca' ? 'bg-blue' : 'bg-green') }}">
                                    {{ optional($terusan->penerima->primaryPosition())->name ?? 'User' }}
                                    ({{ $terusan->status }})
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm italic">Belum diteruskan.</p>
                    @endif
                </div>

            </div>

            {{-- ============================= --}}
            {{--   BAGIAN KANAN: FEEDBACK       --}}
            {{-- ============================= --}}
            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md flex flex-col max-h-[80vh]">
                <h3 class="font-semibold mb-4">💬 Feedback & Diskusi</h3>

                <div id="chat-area"
                    class="flex-1 overflow-y-auto p-3 rounded-lg border bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 space-y-4 relative">

                    @foreach ($riwayatFeedback as $item)
                        @php $isSaya = $item->user_id == auth()->id(); @endphp

                        <div class="flex {{ $isSaya ? 'justify-end' : 'justify-start' }}">
                            <div
                                class="max-w-[75%] px-4 py-3 rounded-2xl shadow-sm border
                                {{ $isSaya ? 'bg-green rounded-br-none' : 'bg-blue rounded-bl-none' }}">

                                <div class="flex justify-between">
                                    <p class="font-semibold text-sm">
                                        {{ $isSaya ? 'Anda' : $item->user->primaryPosition()->name }}
                                    </p>
                                    <span class="text-[10px] text-gray-500">
                                        {{ $item->updated_at->format('d M Y H:i') }}
                                    </span>
                                </div>

                                <p class="mt-1 text-[13px]">{{ $item->feedback }}</p>

                                {{-- Lampiran Feedback --}}
                                @foreach ($item->lampiran ?? [] as $lamp)
                                    @php $ext = strtolower(pathinfo($lamp->file_name, PATHINFO_EXTENSION)); @endphp

                                    @if ($ext === 'pdf')
                                        <button type="button" class="btn btn-outline-primary view-pdf mt-2"
                                            data-url="{{ asset('storage/' . $lamp->file_path) }}">
                                            📎 {{ $lamp->file_name }}
                                        </button>
                                    @else
                                        <a href="{{ asset('storage/' . $lamp->file_path) }}" download
                                            class="btn btn-outline-secondary mt-2">
                                            📥 {{ $lamp->file_name }}
                                        </a>
                                    @endif

                                    {{-- Hapus Lampiran --}}
                                    @if ($item->user_id == auth()->id())
                                        <form action="{{ route('disposisi_masuk.hapusLampiran', $lamp->id) }}"
                                            method="POST" class="inline formHapusLampiran">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger btn-sm btnHapusLampiran">
                                                🗑 Hapus
                                            </button>
                                        </form>
                                    @endif
                                @endforeach

                            </div>
                        </div>
                    @endforeach

                    <div class="absolute left-1/2 top-0 bottom-0 border-l border-dashed opacity-20"></div>
                </div>
            </div>
        </div>

        {{-- ============================= --}}
        {{--   FORM TAMBAH FEEDBACK        --}}
        {{-- ============================= --}}
        <form method="POST" action="{{ route('disposisi.feedbackDirektur', $penerima->id) }}"
            enctype="multipart/form-data" class="mt-6">
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

        </form>

        @if (isset($subordinateUsers) && $subordinateUsers->count() > 0)
            <div class="mt-8 bg-white dark:bg-gray-800 border rounded-xl p-5 shadow-md">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">📤 Teruskan Disposisi</h3>

                <form action="{{ route('disposisi.teruskan', $penerima->id) }}" method="POST">
                    @csrf

                    <div class="flex items-end gap-4">
                        <div class="flex flex-col flex-1">
                            <label class="block text-sm font-medium mb-1">Pilih Penerima</label>

                            <select name="penerima_ids[]" id="penerima_lintas" multiple class="w-full">
                                @foreach ($subordinateUsers as $u)
                                    <option value="{{ $u->id }}">
                                        {{ optional($u->primaryPosition())->name ?? '-' }} — {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>

                            <textarea name="catatan_teruskan" rows="2"
                                class="w-full border rounded-lg px-3 mt-2 py-2 text-sm dark:bg-gray-900 dark:text-white"
                                placeholder="Catatan penerusan (opsional)"></textarea>
                        </div>

                        <button type="submit" class="btn btn-outline-primary px-5 py-2 whitespace-nowrap">
                            ➜ Teruskan
                        </button>
                    </div>
                </form>
            </div>
        @endif

    </div>

    {{-- ============================================= --}}
    {{--                  MODAL PDF                    --}}
    {{-- ============================================= --}}
    <div id="pdfModal"
        class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-2xl w-11/12 h-[90vh] flex flex-col overflow-hidden">
            <div class="flex justify-between items-center p-3 border-b bg-gray-100">
                <h3 class="font-semibold">📄 Lihat Surat PDF</h3>
                <button id="closeModal" class="text-red text-lg font-bold">✖</button>
            </div>
            <div class="flex-1 bg-gray-900 flex">
                <iframe id="pdfFrame" src="" class="w-full h-full"></iframe>
            </div>
        </div>
    </div>

    {{-- ============================================= --}}
    {{--              MODAL MODE KETIK                 --}}
    {{-- ============================================= --}}
    <div id="ketikModal"
        class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">

        <div class="bg-white rounded-lg shadow-2xl w-full max-w-3xl h-[70vh] flex flex-col overflow-hidden">
            <div class="flex justify-between items-center p-3 border-b bg-gray-100">
                <h3 class="font-semibold text-gray-700">📝 Isi Surat</h3>
                <button id="closeKetikModal" class="text-red text-lg font-bold">✖</button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div id="ketikContent"></div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- PDF --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('pdfModal');
            const closeModal = document.getElementById('closeModal');
            const iframe = document.getElementById('pdfFrame');

            document.addEventListener('click', (e) => {
                if (e.target.closest('.view-pdf')) {
                    iframe.src = e.target.closest('.view-pdf').dataset.url;
                    modal.classList.remove('hidden');
                }
            });

            closeModal.onclick = () => modal.classList.add('hidden');
        });
    </script>

    {{-- Mode Ketik --}}
    <script>
        document.addEventListener('click', function(e) {
            let btn = e.target.closest('.view-ketik');
            if (btn) {
                document.getElementById('ketikContent').innerHTML = JSON.parse(btn.dataset.content);
                document.getElementById('ketikModal').classList.remove('hidden');
            }
        });

        document.getElementById('closeKetikModal').onclick = () => {
            document.getElementById('ketikModal').classList.add('hidden');
        };
    </script>

    {{-- Preview Lampiran --}}
    <script>
        document.getElementById('uploadLampiran').addEventListener('change', function() {
            let preview = document.getElementById('previewLampiran');
            preview.innerHTML = '';

            [...this.files].forEach(f => {
                preview.innerHTML += `<div>📎 ${f.name}</div>`;
            });
        });
    </script>

    {{-- Hapus Lampiran --}}
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

    <script>
    $(function() {
        $('#penerima_lintas').select2({
            placeholder: "Cari nama / jabatan...",
            allowClear: true,
            width: '100%'
        });
    });
</script>

@endsection
