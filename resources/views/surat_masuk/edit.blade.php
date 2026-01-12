@extends('layouts.master')

@section('content')
    <div class="p-6">
        <div class="flex flex-col xl:flex-row gap-6">
            {{-- nyobaaa ini --}}
            {{-- PANEL KIRI --}}
            <div class="panel flex-1 px-6 py-8">

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-semibold">Edit Surat (Mode Ketik)</h3>
                        <p class="text-gray-500 text-sm">Mengedit versi ke-{{ $internal->version }} dari surat ini.</p>
                    </div>
                    <img src="{{ asset('assets/images/logo.png') }}" class="w-20">
                </div>

                {{-- ALERT Versi --}}
                <div class="p-3 bg-yellow-100 border border-yellow-300 rounded mb-4">
                    <p class="text-yellow-700 text-sm">
                        <strong>Catatan:</strong> Perubahan Anda akan menghasilkan <strong>versi baru</strong>
                        surat ini. Versi sebelumnya tetap tersimpan sebagai arsip.
                    </p>
                </div>

                <form action="{{ route('surat_masuk.update_ketik', $surat->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- INFORMASI SURAT --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 font-semibold">Nomor Surat</label>
                            <input type="text" value="{{ $surat->no_surat }}" class="form-input w-full bg-gray-100"
                                disabled>
                        </div>

                        <div>
                            <label class="block mb-1 font-semibold">Tanggal Surat</label>
                            <input type="date" value="{{ $surat->tgl_surat }}" class="form-input w-full bg-gray-100"
                                disabled>
                        </div>

                        <div class="col-span-2">
                            <label class="block mb-1 font-semibold">Perihal</label>
                            <input type="text" value="{{ $surat->perihal }}" class="form-input w-full bg-gray-100"
                                disabled>
                        </div>
                    </div>

                    {{-- TEMPLATE --}}
                    <div class="mt-6">
                        <label class="block font-semibold mb-1">Template</label>
                        <input type="text"
                            value="{{ $internal->template_id ? $internal->template->nama_template : 'Tidak menggunakan template' }}"
                            class="form-input w-full bg-gray-100" disabled>
                    </div>

                    {{-- EDITOR --}}
                    <div class="mt-6">
                        <label class="block mb-1 font-semibold">Isi Surat (Mode Ketik)</label>
                        <textarea id="editor" name="editor_text" class="form-input w-full h-72">{!! $internal->data_isian !!}</textarea>
                    </div>

                    {{-- LAMPIRAN --}}
                    <div class="mt-6">
                        <label class="block font-semibold">Lampiran (Opsional)</label>

                        @if ($internal->lampiran_pdf)
                            <p class="text-sm mb-1">
                                Lampiran saat ini:
                                <a href="{{ asset('storage/' . $internal->lampiran_pdf) }}" target="_blank"
                                    class="text-blue-600 underline">Lihat Lampiran</a>
                            </p>
                        @endif

                        <input type="file" name="lampiran_pdf" accept="application/pdf" class="form-input w-full">
                    </div>

                    {{-- BUTTON --}}
                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('surat_masuk.index') }}" class="btn btn-outline-danger mr-3">Batal</a>
                        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    </div>
                </form>
            </div>

            {{-- PANEL KANAN --}}
            <div class="panel w-full xl:w-96">
                <h4 class="text-lg font-semibold mb-4">Informasi Revisi</h4>

                <ul class="list-disc list-inside text-gray-600 text-sm space-y-2">
                    <li>Versi aktif: <strong>{{ $internal->version }}</strong></li>
                    <li>Setiap edit akan membuat versi baru.</li>
                    <li>Arsip versi sebelumnya tetap tersimpan aman.</li>
                    <li>Gunakan Mode Ketik untuk revisi isi surat.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- TinyMCE --}}
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7.3.0/tinymce.min.js"></script>

    <script>
        tinymce.init({
            selector: '#editor',
            height: 850,
            license_key: 'gpl',
            plugins: 'pagebreak table lists advlist',
            toolbar: 'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | table',

            indent_use_five: true,

            style_formats: [{
                    title: 'Line Spacing 1.0',
                    selector: 'p',
                    attributes: {
                        style: 'line-height: 1;'
                    }
                },
                {
                    title: 'Line Spacing 1.15',
                    selector: 'p',
                    attributes: {
                        style: 'line-height: 1.15;'
                    }
                },
                {
                    title: 'Line Spacing 1.5',
                    selector: 'p',
                    attributes: {
                        style: 'line-height: 1.5;'
                    }
                },
                {
                    title: 'Line Spacing 2.0',
                    selector: 'p',
                    attributes: {
                        style: 'line-height: 2;'
                    }
                },
            ],

            content_style: `
                body {
                    background: #ddd;
                    padding: 20px;
                    display: flex;
                    justify-content: center;
                }
                .page {
                    width: 210mm;
                    min-height: 297mm;
                    padding: 25mm;
                    background: white;
                    box-shadow: 0 0 4px rgba(0,0,0,0.2);
                    position: relative;
                }
                p { margin: 0 0 10px; }
                .boundary-overlay {
                    position: absolute;
                    left: 0;
                    width: 100%;
                    border-top: 2px dashed #999;
                    opacity: .6;
                    pointer-events: none;
                    z-index: 9999;
                }
            `,

            setup: function(editor) {
                window.myTiny = editor;

                const A4_HEIGHT = 1123;

                function drawBoundaries() {
                    const page = editor.getBody().querySelector('.page');
                    if (!page) return;

                    page.querySelectorAll('.boundary-overlay').forEach(el => el.remove());

                    const contentHeight = page.scrollHeight;
                    const count = Math.floor(contentHeight / A4_HEIGHT);

                    for (let i = 1; i <= count; i++) {
                        const overlay = editor.getDoc().createElement('div');
                        overlay.className = 'boundary-overlay';
                        overlay.style.top = (i * A4_HEIGHT) + 'px';
                        page.appendChild(overlay);
                    }
                }

                editor.on('init', function() {
                    const cur = editor.getContent();
                    if (!cur.includes('class="page"')) {
                        editor.setContent(`<div class="page">${cur}</div>`);
                    }
                    drawBoundaries();
                });

                editor.on('keyup paste input NodeChange setcontent', function() {
                    drawBoundaries();
                });

                // TAB → INDENT
                editor.on('keydown', function(e) {
                    if (e.key === 'Tab') {
                        e.preventDefault();
                        if (e.shiftKey) editor.execCommand('Outdent');
                        else editor.execCommand('Indent');
                    }
                });
            }
        });

        // CLEAN sebelum submit
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector(
                'form[action="{{ route('surat_masuk.update_ketik', $surat->id) }}"]');

            if (!form) return;

            form.addEventListener('submit', function() {
                const ed = tinymce.get('editor');
                let content = ed.getContent();

                content = content.replace(/<div class="boundary-overlay"[\s\S]*?<\/div>/gi, '');
                content = content.replace(/<div class="page">/gi, '');
                content = content.replace(/<\/div>\s*$/gi, '');

                ed.setContent(content);
                tinymce.triggerSave();
            });
        });
    </script>
@endsection
