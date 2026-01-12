@extends('layouts.master')

@section('content')
    <div class="p-6">
        <div class="panel p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                    📄 Buat Nota Dinas
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

            <form action="{{ route('nota.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- PENERIMA --}}
                {{-- <div class="mb-4">
                    <label class="block font-medium mb-2">Penerima</label>

                    <select id="penerima" name="penerima_id" class="form-select w-full" required>
                        <option value="">-- Pilih Penerima --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} ({{ $user->position ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div> --}}
                <div class="mb-4">
                    <label class="block font-medium mb-2">Penerima (boleh lebih dari 1)</label>

                    <select id="penerima" name="penerima_ids[]" class="form-select w-full" multiple required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} ({{ $user->primaryPosition()->name ?? '' }})
                            </option>
                        @endforeach
                    </select>

                    <p class="mt-1 text-xs text-gray-500">
                        * Untuk staf, manajer langsung akan otomatis ditambahkan sebagai penerima.
                    </p>
                </div>



                {{-- JUDUL --}}
                <div class="mb-4">
                    <label class="block font-medium mb-2">Judul</label>
                    <input type="text" name="judul" class="form-input w-full" required>
                </div>

                {{-- ISI NOTA (CKEDITOR) --}}
                <div class="mb-4">
                    <label class="block font-medium mb-2">Isi Nota Dinas</label>
                    <textarea name="isi" id="ckeditor">

                    <p style="text-align: justify;">
                        Assalamu’alaikum warahmatullahi wabarakatuh,
                    </p>

                    <p style="text-align: justify; margin-top: 15px;">
                        .....................................
                    </p>

                    <p style="text-align: justify; margin-top: 15px;">
                        Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
                    </p>

                    <p style="text-align: justify; margin-top: 15px;">
                        Wassalamu’alaikum warahmatullahi wabarakatuh.
                    </p>

                    <p style="text-align: right;">
                        {{ $tanggal }}
                    </p>
                    <p style="margin-top: 25px;">
                        Hormat saya,<br><br>
                        <strong>{{ $pengirimNama }}</strong><br>
                        {{ $pengirimJabatan }}
                    </p>
                    </textarea>
                </div>


                {{-- LAMPIRAN --}}
                <div class="mb-4">
                    <label class="block font-medium mb-2">Lampiran (PDF) - Opsional</label>
                    <input type="file" name="lampiran" accept="application/pdf" class="form-input w-full">
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-2">
                        Lampiran Lain (Word/Excel/PPT/ZIP) - Opsional
                    </label>
                    <input type="file" name="lampiran_lain"
                        accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.zip,.rar,.txt,.pdf,.jpg,.jpeg,.png"
                        class="form-input w-full">
                    <p class="mt-1 text-xs text-gray-500">
                        Contoh: .docx, .xlsx, .pptx, .csv, .zip
                    </p>
                </div>


                <button type="submit" class="btn btn-primary mt-4">
                    Kirim Nota Dinas
                </button>
            </form>
        </div>
    </div>

    {{-- CKEDITOR --}}
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('ckeditor', {
            versionCheck: false
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#penerima').select2({
                placeholder: "Cari atau pilih penerima...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>



@endsection
