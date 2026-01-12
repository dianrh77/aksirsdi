@extends('layouts.master')

@section('content')
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold">Daftar Template Surat</h3>
            <a href="{{ route('template.create') }}" class="btn btn-primary">+ Tambah Template</a>
        </div>

        <div class="panel p-5">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="text-left border-b">
                        <th class="p-2">Nama Template</th>
                        <th class="p-2">File</th>
                        <th class="p-2">Upload By</th>
                        <th class="p-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($templates as $t)
                        <tr class="border-b">
                            <td class="p-2">{{ $t->nama_template }}</td>

                            <td>
                                <button class="btn btn-sm btn-primary preview-template" data-id="{{ $t->id }}">
                                    👁️ Preview
                                </button>
                            </td>

                            <td>
                                {{ $t->uploader->name ?? '-' }}
                                -
                                {{ $t->uploader->primaryPosition()->name ?? '-' }}
                            </td>


                            <td class="p-2 flex gap-2">

                                {{-- <a href="{{ route('template.edit', $t->id) }}" class="btn btn-sm btn-outline-warning">✏
                                    Edit</a> --}}

                                <form action="{{ route('template.delete', $t->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin hapus template ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">🗑 Hapus</button>
                                </form>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($templates->count() === 0)
                <p class="text-center text-gray-500 mt-4">Belum ada template.</p>
            @endif
        </div>
    </div>

    <!-- PDF Modal -->
    <div id="pdfModal"
        class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-2xl w-11/12 h-[95vh] flex flex-col overflow-hidden">
            <div class="flex justify-between items-center p-3 border-b bg-gray-100">
                <h3 class="font-semibold text-gray-700">📄 Lihat Surat PDF</h3>
                <button id="closeModal" class="text-red-500 hover:text-red-700 text-lg font-bold">✖</button>
            </div>
            <div class="flex-1 bg-gray-900">
                <iframe id="pdfFrame" src="" class="w-full h-full"></iframe>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('preview-template')) {
                const id = e.target.dataset.id;

                // arahkan ke route preview LibreOffice
                document.getElementById('pdfFrame').src = `/template/${id}/preview`;

                // tampilkan modal
                document.getElementById('pdfModal').classList.remove('hidden');
            }
        });

        // close modal
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('pdfModal').classList.add('hidden');

            // kosongkan iframe saat modal ditutup (agar PDF tidak tetap loading)
            document.getElementById('pdfFrame').src = "";
        };
    </script>
@endsection
