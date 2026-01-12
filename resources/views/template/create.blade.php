@extends('layouts.master')

@section('content')
    <div class="p-6 max-w-xl mx-auto">

        <div class="panel p-6">
            <h3 class="text-xl font-semibold mb-4">Tambah Template Baru</h3>

            <form action="{{ route('template.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block mb-1 font-medium">Nama Template</label>
                    <input type="text" name="nama_template" class="form-input w-full" required>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">Upload File Template (DOCX)</label>
                    <input type="file" name="file_template" accept=".docx" class="form-input w-full" required>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('template.index') }}" class="btn btn-outline-danger mr-2">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>

    </div>
@endsection
