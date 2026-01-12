@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <div class="flex flex-col xl:flex-row gap-6">
            <div class="panel flex-1 px-6 py-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Tambah Surat Keluar</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Isi data surat keluar dengan lengkap di bawah ini
                        </p>
                    </div>
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="w-20">
                </div>

                <form action="{{ route('surat_keluar.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Nomor Surat</label>
                            <input type="text" name="no_surat" class="form-input w-full"
                                placeholder="Masukkan nomor surat" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Tanggal Surat</label>
                            <input type="date" name="tgl_surat" class="form-input w-full" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Tujuan Surat</label>
                            <input type="text" name="tujuan_surat" class="form-input w-full"
                                placeholder="Masukkan tujuan surat" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Perihal</label>
                            <textarea name="perihal" rows="4" class="form-input w-full resize-none"
                                placeholder="Tuliskan perihal surat dengan lengkap" required></textarea>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block mb-1 text-gray-700 dark:text-gray-300">File PDF</label>
                        <input type="file" name="file_pdf" class="form-input w-full" accept="application/pdf" required>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('surat_keluar.index') }}"
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
                    <p>Sistem Surat Keluar v1.0</p>
                </div>
            </div>
        </div>
    </div>
@endsection
