@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <div class="flex flex-col xl:flex-row gap-6">
            <div class="panel flex-1 px-6 py-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Edit Surat Keluar</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Perbarui data Surat Keluar di bawah ini</p>
                    </div>
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="w-14">
                </div>

                <form action="{{ route('surat_keluar.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Nomor Surat</label>
                            <input type="text" name="no_surat" value="{{ old('no_surat', $data->no_surat) }}"
                                class="form-input w-full" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Tanggal Surat</label>
                            <input type="date" name="tgl_surat" value="{{ old('tgl_surat', $data->tgl_surat) }}"
                                class="form-input w-full" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Tujuan Surat</label>
                            <input type="text" name="tujuan_surat" value="{{ old('tujuan_surat', $data->tujuan_surat) }}"
                                class="form-input w-full" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Perihal</label>
                            <textarea name="perihal" rows="4" class="form-input w-full resize-none" required>{{ old('perihal', $data->perihal) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block mb-1 text-gray-700 dark:text-gray-300">File PDF (Opsional)</label>
                        <input type="file" name="file_pdf" class="form-input w-full" accept="application/pdf">
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('surat_keluar.index') }}"
                            class="btn btn-outline-danger ltr:mr-3 rtl:ml-3">Batal</a>
                        <button type="submit" class="btn btn-primary ltr:mr-3">Update</button>
                        <a href="{{ route('surat_keluar.destroy', $data->id) }}" class="btn btn-danger"
                            data-confirm-delete="true">Delete</a>

                    </div>
                </form>
            </div>

            <div class="panel w-full xl:w-96">
                <h4 class="text-lg font-semibold mb-4">Panduan Edit</h4>
                <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 text-sm space-y-2">
                    <li>Pastikan nomor dan tanggal surat benar.</li>
                    <li>Jika tidak ingin mengganti file, biarkan kosong.</li>
                    <li>Periksa perihal agar sesuai dengan isi surat.</li>
                </ul>
                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4 text-center text-sm text-gray-400">
                    <p>Sistem Surat Keluar v1.0</p>
                </div>
            </div>
        </div>
    </div>
@endsection
