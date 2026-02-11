@extends('layouts.master')

@section('content')
    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">
                {{ $jenis === 'utama' ? '🧑‍💼 Instruksi Direktur Utama' : '🏢 Instruksi Direktur Umum' }}
            </h2>

            <a href="{{ route('instruksi.' . $jenis . '.index') }}" class="text-blue hover:underline">← Kembali</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- === KIRI: Preview Surat & Info Disposisi === -->
            <div class="md:col-span-2 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md space-y-5">
                <section>
                    <p><strong>No Disposisi:</strong> {{ $disposisi->no_disposisi ?? '-' }}</p>
                    <p><strong>Perihal:</strong> {{ $disposisi->suratMasuk->perihal ?? '-' }}</p>
                    <p><strong>Pengirim:</strong> {{ $disposisi->pengirim->name ?? '-' }}</p>
                    <p><strong>Tanggal:</strong> {{ optional($disposisi->created_at)->format('d M Y') }}</p>
                    <p><strong>Catatan:</strong> {{ $disposisi->catatan ?? '-' }}</p>
                    {{-- ====================== FILE SURAT KETIK ====================== --}}
                    @if ($disposisi->suratMasuk->internalDoc)
                        <div class="mt-4 p-3 bg-gray-50 border rounded-lg">
                            <p class="font-semibold mb-2">📝 Isi Surat</p>

                            <div class="border rounded-lg p-4 bg-white" style="max-height: 300px; overflow-y: auto;">
                                {!! $disposisi->suratMasuk->internalDoc->data_isian !!}
                            </div>
                        </div>
                    @endif

                    <div class="text-right mt-2">
                        <button type="button" class="btn btn-outline-primary view-pdf"
                            data-url="{{ asset('storage/' . $disposisi->suratMasuk->file_pdf) }}">
                            <i class="ri-fullscreen-line text-base"></i> Lihat Surat
                        </button>
                    </div>

                    {{-- Lampiran Surat KETIK --}}
                    @if ($disposisi->suratMasuk->internalDoc && $disposisi->suratMasuk->internalDoc->lampiran_pdf)
                        @php
                            $lamp = $disposisi->suratMasuk->internalDoc->lampiran_pdf;
                            $ext = strtolower(pathinfo($lamp, PATHINFO_EXTENSION));
                            $isPdf = $ext === 'pdf';
                        @endphp

                        @if ($isPdf)
                            <button type="button" class="btn btn-outline-primary view-pdf mt-2"
                                data-url="{{ asset('storage/' . $lamp) }}">
                                📎 Lampiran (PDF)
                            </button>
                        @else
                            <a href="{{ asset('storage/' . $lamp) }}" download class="btn btn-outline-secondary mt-2">
                                📥 Lampiran ({{ strtoupper($ext) }})
                            </a>
                        @endif
                    @endif


                    {{-- STATUS REJECT DARI DIREKTUR --}}
                    @if ($disposisi->status == 'Ditolak Direktur' && $disposisi->reject)
                        <div class="mt-3 p-3 bg-yellow rounded-lg border">
                            <p class="font-semibold">❌ Ditolak Direktur</p>

                            <p class="text-sm">
                                Alasan: {{ $disposisi->reject->alasan }}
                            </p>

                            <p class="text-sm mt-1">
                                Ditolak oleh: <strong>{{ $disposisi->reject->direktur->name ?? 'Direktur' }}</strong>
                            </p>

                            <p class="text-xs mt-1">
                                Ditolak pada: {{ $disposisi->reject->created_at->format('d M Y H:i') }}
                            </p>
                        </div>
                    @endif


                </section>

                <!-- Penerima -->
                <section class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">📬 Diteruskan Kepada</h3>
                    @if ($disposisi->penerima && $disposisi->penerima->count())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($disposisi->penerima as $terusan)
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
                </section>

                <!-- Riwayat Instruksi -->
                <section class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h4 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">🧾 Instruksi Direksi</h4>
                    <div class=" border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-3">
                        @forelse($disposisi->instruksis as $item)
                            <div
                                class="border rounded-lg p-3 
                                {{ $item->jenis_direktur == 'utama' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-blue-50 dark:bg-blue-900/20' }}">
                                <div class="flex justify-between items-center mb-1">
                                    <p class="font-semibold text-sm">
                                        {{ $item->jenis_direktur == 'utama' ? '👤 Direktur Utama' : '👤 Direktur Umum' }}
                                    </p>
                                    <span class="text-xs text-gray-500">
                                        {{ $item->created_at->format('d M Y H:i') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-200 leading-snug">
                                    {{ $item->instruksi }}
                                </p>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm italic text-center">Belum ada instruksi dari direktur lain.</p>
                        @endforelse
                    </div>
                </section>
            </div>



            <!-- === KANAN: Form Instruksi === -->
            <div class="md:col-span-2 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md space-y-5">
                <!-- Form -->
                <div>
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-3">✍️ Form Instruksi Direktur</h3>

                    {{-- FORM UTAMA --}}
                    <form action="{{ route('instruksi.' . $jenis . '.store', $disposisi->id) }}" method="POST"
                        class="space-y-4">
                        @csrf

                        {{-- Instruksi --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Instruksi</label>
                            <textarea name="instruksi" rows="6" class="form-textarea w-full" required>{{ old('instruksi') }}</textarea>
                        </div>

                        {{-- Penerima --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Penerima Disposisi</label>
                            {{-- <select name="penerima_ids[]" id="penerima_ids" multiple class="w-full" required>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        data-self="{{ $user->id == auth()->id() ? '1' : '0' }}"
                                        {{ in_array($user->id, old('penerima_ids', $selectedPenerimaIds ?? [])) ? 'selected' : '' }}>
                                        {{ $user->primaryPosition()->name ?? '-' }}
                                        @if ($user->id == auth()->id())
                                            (Anda)
                                        @endif
                                    </option>
                                @endforeach
                            </select> --}}
                            <select name="penerima_ids[]" id="penerima_ids" multiple class="w-full" rows="2" required>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{-- penerima lama → tidak boleh dihapus --}}
                                        data-fixed="{{ in_array($user->id, $selectedPenerimaIds) ? '1' : '0' }}"
                                        {{-- user login → tidak boleh dipilih --}} data-self="{{ $user->id == auth()->id() ? '1' : '0' }}"
                                        {{ in_array($user->id, old('penerima_ids', $selectedPenerimaIds ?? [])) ? 'selected' : '' }}>
                                        {{ $user->primaryPosition()->name ?? '-' }}
                                        @if ($user->id == auth()->id())
                                            (Anda)
                                        @elseif(in_array($user->id, $selectedPenerimaIds))
                                            (Ditentukan Direktur Utama)
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                        </div>


                        {{-- Batas Waktu --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Batas Waktu</label>
                            <input type="date" name="batas_waktu" class="form-input w-full"
                                value="{{ old('batas_waktu') }}">
                        </div>

                        {{-- Proses Lanjut / Hold --}}
                        @php
                            $prosesStatus = old('proses_status', 'lanjut');
                            $holdReasonValue = old('hold_reason', '');
                        @endphp
                        <div>
                            <label class="block text-sm font-medium mb-2">Status Proses</label>
                            <input type="hidden" name="proses_status" id="proses_status" value="{{ $prosesStatus }}">

                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" id="proses_lanjut" class="form-checkbox">
                                    <span>Proses Lanjut</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" id="proses_hold" class="form-checkbox">
                                    <span>Hold</span>
                                </label>
                            </div>
                        </div>

                        <div id="hold_reason_wrap" class="hidden">
                            <label class="block text-sm font-medium mb-1">Alasan Hold</label>
                            <textarea name="hold_reason" rows="3" class="form-textarea w-full"
                                placeholder="Tulis alasan hold...">{{ $holdReasonValue }}</textarea>
                        </div>

                        {{-- TOMBOL --}}
                        {{-- @if ($disposisi->status != 'Ditolak Direktur')
                            <div class="flex justify-between gap-3">
                                <button type="button" id="btnReject" class="btn btn-outline-danger">
                                    ✖ Tolak Disposisi
                                </button>

                                <button type="submit" class="btn btn-outline-primary">
                                    Simpan Instruksi
                                </button>
                            </div>
                        @endif --}}
                        {{-- Direktur Umum TIDAK boleh isi instruksi jika bukan penerima --}}
                        @if ($jenis === 'umum' && !$isPenerimaDirekturUmum)
                            <div class="p-4 bg-gray-100 rounded border">
                                <p class="text-gray-600 text-sm italic">
                                    Anda tidak termasuk penerima disposisi ini.
                                    Anda hanya dapat melihat isinya.
                                </p>
                            </div>
                        @else
                            {{-- tampilkan form instruksi --}}
                            <div class="flex justify-between gap-3">
                                <button type="button" id="btnReject" class="btn btn-outline-danger">✖ Tolak
                                    Disposisi</button>
                                <button type="submit" class="btn btn-outline-primary">Simpan Instruksi</button>
                            </div>
                        @endif

                    </form>

                    {{-- FORM BATAL PENOLAKAN (DI LUAR FORM UTAMA) --}}
                    @if ($disposisi->status == 'Ditolak Direktur')
                        <form action="{{ route('instruksi.' . $jenis . '.cancelReject', $disposisi->id) }}" method="POST"
                            class="mt-3">
                            @csrf
                            <button class="btn btn-outline-warning" type="submit">
                                ↩ Batalkan Penolakan
                            </button>
                        </form>
                    @endif

                </div>



            </div>
        </div>

        <!-- === Modal Viewer PDF === -->
        <div id="pdfModal"
            class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-2xl w-11/12 h-[95vh] flex flex-col overflow-hidden">
                <div class="flex justify-between items-center p-3 border-b bg-gray-100 shrink-0">
                    <h3 class="font-semibold text-gray-700">📄 Lihat Surat PDF</h3>
                    <button id="closeModal"
                        class="text-red hover:text-red text-lg font-bold transition-colors duration-200">✖</button>
                </div>
                <div class="flex-1 bg-gray-900 flex">
                    <iframe id="pdfFrame" src="" class="w-full h-full border-0"></iframe>
                </div>
            </div>
        </div>

        <!-- Modal Reject -->
        <div id="rejectModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">

            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-1/2 max-w-xl">

                <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">
                    Tolak Disposisi
                </h3>

                <form id="formReject" action="{{ route('instruksi.' . $jenis . '.reject', $disposisi->id) }}"
                    method="POST">
                    @csrf
                    <label class="block text-sm font-medium mb-1">Alasan Penolakan</label>
                    <textarea name="alasan_reject" required class="w-full p-2 rounded border dark:bg-gray-700 dark:border-gray-600"
                        rows="4"></textarea>

                    <div class="flex justify-end mt-4 gap-2">
                        <button type="button" id="closeRejectModal" class="px-3 py-2 btn-outline-secondary">Batal</button>

                        <button type="submit" class="px-3 py-2 btn-outline-danger">Tolak</button>
                    </div>
                </form>
            </div>
        </div>


    </div>
@endsection

@section('scripts')
    {{-- <script>
        $(function() {
            $('#penerima_ids').select2({
                width: '100%',
                placeholder: 'Pilih penerima',
                templateResult: function(data) {
                    if (!data.element) return data.text;

                    if ($(data.element).data('self') == 1) {
                        return $('<span style="color:#999;">' + data.text +
                            ' (tidak dapat dipilih)</span>');
                    }

                    return data.text;
                }
            });

            $('#penerima_ids').on('select2:selecting', function(e) {
                if ($(e.params.args.data.element).data('self') == 1) {
                    e.preventDefault();
                }
            });

        });

        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('pdfModal');
            const closeModal = document.getElementById('closeModal');
            const iframe = document.getElementById('pdfFrame');

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.view-pdf');
                if (btn) {
                    iframe.src = `${btn.dataset.url}#toolbar=1&zoom=100`;
                    modal.classList.remove('hidden');
                }
            });

            closeModal.onclick = () => modal.classList.add('hidden');
            window.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.add('hidden');
            });
        });
    </script> --}}
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
    <script>
        $(function() {
            const $select = $('#penerima_ids');

            $select.select2({
                width: '100%',
                placeholder: 'Pilih penerima',
                closeOnSelect: false,

                templateResult: function(data) {
                    if (!data.element) return data.text;

                    const $el = $(data.element);

                    if ($el.data('self') == 1) {
                        return $('<span style="color:#999;">' + data.text +
                            ' (tidak dapat dipilih)</span>');
                    }

                    if ($el.data('fixed') == 1) {
                        return $('<strong>' + data.text + '</strong>');
                    }

                    return data.text;
                },

                templateSelection: function(data) {
                    if (!data.element) return data.text;

                    const $el = $(data.element);

                    if ($el.data('fixed') == 1) {
                        return $('<span><strong>' + data.text + '</strong></span>');
                    }

                    return data.text;
                }
            });

            /**
             * ❌ Cegah memilih user login
             */
            $select.on('select2:selecting', function(e) {
                if ($(e.params.args.data.element).data('self') == 1) {
                    e.preventDefault();
                }
            });

            /**
             * ❌ Cegah menghapus penerima lama (fixed)
             */
            $select.on('select2:unselecting', function(e) {
                if ($(e.params.args.data.element).data('fixed') == 1) {
                    e.preventDefault();
                }
            });
        });
    </script>

    <script>
        (function() {
            const hidden = document.getElementById('proses_status');
            const cbLanjut = document.getElementById('proses_lanjut');
            const cbHold = document.getElementById('proses_hold');
            const holdWrap = document.getElementById('hold_reason_wrap');

            if (!hidden || !cbLanjut || !cbHold || !holdWrap) return;

            function applyState(value) {
                const isHold = value === 'hold';
                cbHold.checked = isHold;
                cbLanjut.checked = !isHold;
                hidden.value = isHold ? 'hold' : 'lanjut';
                holdWrap.classList.toggle('hidden', !isHold);
            }

            cbLanjut.addEventListener('change', () => applyState('lanjut'));
            cbHold.addEventListener('change', () => applyState('hold'));

            applyState(hidden.value || 'lanjut');
        })();
    </script>


    <script>
        // BUKA MODAL
        document.getElementById('btnReject').addEventListener('click', () => {
            document.getElementById('rejectModal').classList.remove('hidden');
        });

        // TUTUP MODAL
        document.getElementById('closeRejectModal').addEventListener('click', () => {
            document.getElementById('rejectModal').classList.add('hidden');
        });
    </script>

    <style>
        /* container utama */
        .select2-container--default .select2-selection--multiple {
            min-height: 90px;
            /* ⬅️ tinggi awal (± 3 baris) */
            padding: 6px;
            display: flex;
            align-items: flex-start;
            /* ⬅️ cursor mulai dari atas */
            cursor: text;
        }

        /* area tag */
        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex-wrap: wrap !important;
            align-content: flex-start;
            /* ⬅️ penting */
            gap: 6px;
            width: 100%;
        }
    </style>
@endsection
