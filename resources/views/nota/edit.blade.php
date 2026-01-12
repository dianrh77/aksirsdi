@extends('layouts.master')

@section('content')
    <div class="p-6">
        <div class="panel p-6">

            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                    ✏️ Edit Nota Dinas
                </h2>
                <a href="{{ route('nota.index') }}" class="text-blue hover:underline">← Kembali</a>
            </div>

            @if ($errors->any())
                <div class="bg-red-200 text-red-700 p-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('nota.update', $nota->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- PENERIMA --}}
                <div class="mb-4">
                    <label class="block font-medium mb-2">Penerima (boleh lebih dari 1)</label>

                    @php
                        // Ambil array user_id yang sudah jadi penerima
                        $selectedPenerima = $nota->penerima->pluck('id')->toArray();
                    @endphp

                    <select id="penerima" name="penerima_ids[]" class="form-select w-full" multiple required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}"
                                {{ in_array($user->id, $selectedPenerima) ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->primaryPosition()->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- JUDUL --}}
                <div class="mb-4">
                    <label class="block font-medium mb-2">Judul</label>
                    <input type="text" name="judul" class="form-input w-full" value="{{ old('judul', $nota->judul) }}"
                        required>
                </div>

                {{-- ISI NOTA --}}
                <div class="mb-4">
                    <label class="block font-medium mb-2">Isi Nota Dinas</label>
                    <textarea name="isi" id="ckeditor" required>{!! old('isi', $nota->isi) !!}</textarea>
                </div>

                {{-- LAMPIRAN --}}
                <div class="mb-4">
                    <label class="block font-medium mb-2">Lampiran (PDF) - Opsional</label>

                    @if ($nota->lampiran)
                        <button type="button" class="btn btn-outline-primary mb-2 view-pdf"
                            data-url="{{ asset('storage/' . $nota->lampiran) }}">
                            📎 Lihat Lampiran
                        </button>
                    @endif

                    <input type="file" name="lampiran" accept="application/pdf" class="form-input w-full">
                </div>

                {{-- LAMPIRAN LAIN (BARU) --}}
                <div class="mb-4">
                    <label class="block font-medium mb-2">Lampiran Lain - Opsional</label>

                    @if ($nota->lampiran_lain)
                        @php
                            $url = asset('storage/' . $nota->lampiran_lain);
                            $nama = $nota->lampiran_lain_nama ?? basename($nota->lampiran_lain);
                            $ext = strtolower(pathinfo($nama, PATHINFO_EXTENSION));
                            $isPdf = $ext === 'pdf';
                        @endphp

                        <div class="flex flex-wrap gap-2 mb-2">
                            @if ($isPdf)
                                <button type="button" class="btn btn-outline-primary view-pdf"
                                    data-url="{{ $url }}">
                                    📎 Lihat Lampiran Lain (PDF)
                                </button>
                            @else
                                <a href="{{ $url }}" target="_blank" class="btn btn-outline-secondary">
                                    📎 Buka Lampiran Lain
                                </a>
                            @endif

                            <a href="{{ $url }}" download="{{ $nama }}" class="btn btn-outline-success">
                                📥 Download ({{ $nama }})
                            </a>
                        </div>
                    @endif

                    <input type="file" name="lampiran_lain" class="form-input w-full">
                    <small class="text-gray-500">Kosongkan jika tidak ingin mengganti lampiran lain.</small>
                </div>


                <button type="submit" class="btn btn-warning mt-4">
                    💾 Simpan Perubahan
                </button>

            </form>
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
                <iframe id="pdfFrame" src="" class="w-full h-full"></iframe>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    {{-- CKEDITOR --}}
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('ckeditor', {
            versionCheck: false
        });
    </script>

    {{-- Select2 --}}
    <script>
        $(document).ready(function() {
            $('#penerima').select2({
                placeholder: "Cari atau pilih penerima...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    {{-- Modal PDF --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('pdfModal');
            const closeModal = document.getElementById('closeModal');
            const iframe = document.getElementById('pdfFrame');

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.view-pdf');
                if (!btn) return;

                iframe.src = btn.dataset.url + "#toolbar=1&zoom=100";
                modal.classList.remove('hidden');
            });

            closeModal.onclick = () => modal.classList.add('hidden');
        });
    </script>
@endsection
