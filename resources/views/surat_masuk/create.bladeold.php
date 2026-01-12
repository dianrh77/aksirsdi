@extends('layouts.master')

@section('content')
    <div class="p-6">
        <div class="flex flex-col xl:flex-row gap-6">
            <div class="panel flex-1 px-6 py-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Tambah Surat Masuk</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Isi data surat masuk dengan lengkap di bawah ini
                        </p>
                    </div>
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="w-20">
                </div>

                <form action="{{ route('surat_masuk.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Nomor Surat</label>
                            <input type="text" name="no_surat" class="form-input w-full"
                                placeholder="Masukkan nomor surat" required>
                        </div>

                        <div>
                            <label for="jenis_surat" class="block text-sm font-medium mb-1">Jenis Surat</label>
                            <select name="jenis_surat" id="jenis_surat" class="form-select w-full" required>
                                <option value="internal">Internal</option>

                                @if (auth()->user()->role_name === 'kesekretariatan')
                                    <option value="eksternal">Eksternal</option>
                                @endif
                            </select>
                        </div>


                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Tanggal Surat</label>
                            <input type="date" name="tgl_surat" class="form-input w-full" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Asal Surat</label>
                            <input type="text" name="asal_surat" class="form-input w-full"
                                value="{{ Auth::user()->name }} - {{ Auth::user()->primaryPosition()->name ?? '' }}"
                                placeholder="Masukkan asal surat" required>

                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Perihal</label>
                            <textarea name="perihal" rows="4" class="form-input w-full resize-none"
                                placeholder="Tuliskan perihal surat dengan lengkap" required></textarea>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label>Pilih Template Surat (opsional)</label>
                        <select name="template_id" class="form-select w-full">
                            <option value="">-- Tanpa Template --</option>
                            @foreach ($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->nama_template }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="mt-6">
                        <label class="block mb-1 text-gray-700 dark:text-gray-300">File PDF</label>
                        <input type="file" name="file_pdf" class="form-input w-full" accept="application/pdf" required>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('surat_masuk.index') }}"
                            class="btn btn-outline-danger ltr:mr-3 rtl:ml-3">Batal</a>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>

            <div class="panel w-full xl:w-96">
                <h4 class="text-lg font-semibold mb-4">Panduan Upload</h4>
                <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 text-sm space-y-2">
                    <li>Pastikan file berformat <strong>PDF</strong>.</li>
                    <li>Ukuran maksimal file <strong>2 MB</strong>.</li>
                    <li>Isi semua data dengan benar sesuai surat fisik.</li>
                    <li>Setelah disimpan, surat akan otomatis tercatat dalam sistem.</li>
                </ul>
                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4 text-center text-sm text-gray-400">
                    <p>Sistem Surat Masuk v1.0</p>
                </div>
            </div>
        </div>
    </div>
@endsection
