@extends('layouts.master')

@section('content')
    <div class="p-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">
                {{ $jenis === 'utama' ? '🧑‍💼 Edit Instruksi Direktur Utama' : '🏢 Edit Instruksi Direktur Umum' }}
            </h2>

            <a href="{{ route('instruksi.' . $jenis . '.index') }}" class="text-blue hover:underline">← Kembali</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            <!-- ================== PANEL KIRI ================== -->
            <div class="md:col-span-2 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md space-y-5">

                {{-- INFORMASI SURAT --}}
                <section>
                    <p><strong>No Disposisi:</strong> {{ $disposisi->no_disposisi }}</p>
                    <p><strong>Perihal:</strong> {{ $disposisi->suratMasuk->perihal }}</p>
                    <p><strong>Pengirim:</strong> {{ $disposisi->pengirim->name }}</p>
                    <p><strong>Tanggal:</strong> {{ $disposisi->created_at->format('d M Y') }}</p>
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
                </section>

                {{-- PENERIMA --}}
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

                {{-- RIWAYAT INSTRUKSI --}}
                <section class="border-t pt-4">
                    <h4 class="font-semibold mb-2">🧾 Instruksi Direktur Lain</h4>

                    <div class="border rounded-lg p-3 space-y-3">
                        @forelse($disposisi->instruksis->where('direktur_id','!=',auth()->id()) as $item)
                            <div
                                class="border rounded-lg p-3
                            {{ $item->jenis_direktur == 'utama' ? 'bg-green-50' : 'bg-blue-50' }}">
                                <div class="flex justify-between mb-1">
                                    <strong class="text-sm">
                                        {{ $item->jenis_direktur == 'utama' ? '👤 Direktur Utama' : '👤 Direktur Umum' }}
                                    </strong>
                                    <span class="text-xs text-gray-500">
                                        {{ $item->created_at->format('d M Y H:i') }}
                                    </span>
                                </div>

                                <p class="text-sm">{{ $item->instruksi }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500 italic text-center">Belum ada instruksi lain.</p>
                        @endforelse
                    </div>
                </section>

            </div>

            <!-- ================== PANEL KANAN: FORM ================== -->
            <div class="md:col-span-2 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md">

                <h3 class="font-semibold mb-3">✏️ Edit Instruksi</h3>

                <form action="{{ route('instruksi.' . $jenis . '.update', $instruksi->disposisi_id) }}" method="POST"
                    class="space-y-4">

                    @csrf
                    @method('PUT')

                    {{-- Instruksi --}}
                    <div>
                        <label class="font-medium">Instruksi</label>
                        <textarea name="instruksi" rows="6" class="form-textarea w-full" required>{{ old('instruksi', $instruksi->instruksi) }}</textarea>
                    </div>

                    {{-- Penerima --}}
                    <div>
                        <label class="font-medium">Penerima Disposisi</label>
                        <select name="penerima_ids[]" id="penerima_ids" multiple class="w-full" required>
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
                        </select>
                    </div>


                    {{-- Batas waktu --}}
                    <div>
                        <label class="font-medium">Batas Waktu</label>
                        <input type="date" name="batas_waktu" class="form-input w-full"
                            value="{{ old('batas_waktu', $instruksi->batas_waktu ? date('Y-m-d', strtotime($instruksi->batas_waktu)) : '') }}">
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('instruksi.' . $jenis . '.index') }}" class="btn btn-outline-secondary">Batal</a>

                        <button type="submit" class="btn btn-primary">Update Instruksi</button>
                    </div>

                </form>
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

    </div>
@endsection

@section('scripts')
    <script>
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

        document.addEventListener('click', e => {
            const btn = e.target.closest('.view-pdf');
            if (btn) {
                document.getElementById('pdfFrame').src = btn.dataset.url + "#toolbar=1&zoom=100";
                document.getElementById('pdfModal').classList.remove('hidden');
            }
        });

        document.getElementById('closeModal').onclick = () =>
            document.getElementById('pdfModal').classList.add('hidden');
    </script>
@endsection
