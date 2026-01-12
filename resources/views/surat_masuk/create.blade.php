@extends('layouts.master')

@section('content')
    <div class="p-6">
        <div class="flex flex-col xl:flex-row gap-6">

            <!-- PANEL KIRI -->
            <div class="panel flex-1 px-6 py-8">

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-semibold">Tambah Surat Masuk</h3>
                        <p class="text-gray-500 text-sm">Isi data surat masuk dengan lengkap.</p>
                    </div>
                    <img src="{{ asset('assets/images/logo.png') }}" class="w-20">
                </div>

                <form action="{{ route('surat_masuk.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- INFORMASI DASAR --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                        <div>
                            <label class="block mb-1 font-semibold">Nomor Surat</label>
                            <input type="text" name="no_surat" class="form-input w-full" required>
                        </div>

                        <div>
                            <label class="block mb-1 font-semibold">Jenis Surat</label>
                            <select name="jenis_surat" class="form-select w-full" required>
                                <option value="internal">Internal</option>
                                @if (auth()->user()->role_name === 'kesekretariatan')
                                    <option value="eksternal">Eksternal</option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-semibold">Tanggal Surat</label>
                            <input type="date" name="tgl_surat" class="form-input w-full" required>
                        </div>

                        <div>
                            <label class="block mb-1 font-semibold">Asal Surat (Bisa disesuaikan)</label>
                            <input type="text" name="asal_surat" class="form-input w-full"
                                value="{{ Auth::user()->primaryPosition()->name ?? '' }}" required>
                        </div>

                        <div class="col-span-2">
                            <label class="block mb-1 font-semibold">Perihal</label>
                            <textarea name="perihal" rows="3" class="form-input w-full" required></textarea>
                        </div>
                    </div>

                    {{-- MODE PEMBUATAN --}}
                    <div class="mt-6">
                        <label class="font-semibold">Opsi Surat</label>
                        <select name="mode_surat" id="mode_surat" class="form-select w-full mt-1" required>
                            <option value="pdf">Upload PDF</option>
                            <option value="ketik">Ketik Surat</option>
                        </select>
                    </div>

                    {{-- MODE PDF --}}
                    <div id="pdfBox" class="mt-6">
                        <label class="block mb-1 font-semibold">File PDF (Surat Utama)</label>
                        <input type="file" name="file_pdf" class="form-input w-full" accept="application/pdf">

                        {{-- UPLOAD LAMPIRAN OPTIONAL (MODE PDF) --}}
                        <div class="mt-5">
                            <label class="block font-semibold">Upload Lampiran (optional)</label>
                            <input type="file" name="lampiran_pdf" accept="application/pdf" class="form-input w-full">
                            <p class="text-xs text-gray-500 mt-1">Lampiran ini akan disertakan tetapi bukan surat utama.</p>
                        </div>
                    </div>


                    {{-- MODE KETIK --}}
                    <div id="ketikBox" class="hidden mt-6">

                        <label class="block font-semibold mb-1">Template Surat (opsional)</label>

                        <div class="flex gap-2 mb-3">
                            <select name="template_id" id="template_id" class="form-select w-full">
                                <option value="">— Tidak Dipilih —</option>
                                @foreach ($templates as $t)
                                    <option value="{{ $t->id }}">{{ $t->nama_template }}</option>
                                @endforeach
                            </select>

                            <button type="button" onclick="openTemplateModal()"
                                class="btn btn-outline-primary text-xs whitespace-nowrap">
                                + Template
                            </button>
                        </div>


                        <label class="block mb-1 font-semibold">Isi Surat</label>
                        <textarea rows="6" id="editor" name="editor_text" class="form-input w-full h-72"></textarea>

                        {{-- UPLOAD LAMPIRAN OPTIONAL --}}
                        <div class="mt-5">
                            <label class="block font-semibold">Upload Lampiran (optional)</label>
                            <input type="file" name="lampiran_pdf" accept="application/pdf" class="form-input w-full">
                            <p class="text-xs text-gray-500 mt-1">Lampiran ini akan disertakan tetapi bukan surat utama.</p>
                        </div>
                    </div>

                    @if (auth()->user()->getLevel() == 3)
                        <div>
                            <label class="block mb-1 font-semibold">Jenis Surat (Khusus Manager)</label>
                            <select name="jenis_disposisi_manager" class="form-select w-full">
                                <option value="">-- Pilih Jenis Surat --</option>
                                <option value="biasa">Biasa</option>
                                <option value="penting">Penting</option>
                                <option value="rahasia">Rahasia</option>
                            </select>
                        </div>
                    @endif

                    {{-- SUBMIT --}}
                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('surat_masuk.index') }}" class="btn btn-outline-danger mr-3">Batal</a>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>

            <!-- PANEL KANAN -->
            <div class="panel w-full xl:w-96">
                <h4 class="text-lg font-semibold mb-4">Panduan Input</h4>
                <ul class="list-disc list-inside text-gray-600 space-y-2 text-sm">
                    <li>Pilih mode surat sesuai kebutuhan.</li>
                    <li>Mode ketik memungkinkan pemilihan template.</li>
                    <li>Bisa upload lampiran PDF tambahan saat mode ketik.</li>
                    <li>Mode upload PDF langsung menjadikan PDF sebagai surat utama.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ====================== MODAL TAMBAH TEMPLATE ====================== --}}
    <div id="modalTemplate" class="fixed inset-0 bg-black/50 hidden justify-center items-center z-50">

        <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Tambah Template Baru</h3>

            <form id="formTemplate" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block font-medium">Nama Template</label>
                    <input type="text" name="nama_template" class="form-input w-full" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium">Upload File Template (DOCX)</label>
                    <input type="file" name="file_template" class="form-input w-full" accept=".docx" required>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="closeTemplateModal()"
                        class="btn btn-outline-danger mr-2">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection


@section('scripts')
    {{-- CKEditor --}}
    {{-- <script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7.3.0/tinymce.min.js"></script>

    <script>
        // INIT TINYMCE
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

            /* garis batas halaman hanya visual (overlay) */
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

                const A4_HEIGHT = 1123; // px kira-kira tinggi konten 1 halaman A4

                // ==========================
                // GARIS BATAS HALAMAN (VISUAL)
                // ==========================
                function drawBoundaries() {
                    const page = editor.getBody().querySelector('.page');
                    if (!page) return;

                    // hapus overlay lama
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

                // bungkus awal ke .page
                editor.on('init', function() {
                    const cur = editor.getContent();
                    if (!cur.includes('class="page"')) {
                        editor.setContent(`<div class="page">${cur}</div>`);
                    }
                    drawBoundaries();
                });

                // update boundary saat konten berubah
                editor.on('keyup paste input NodeChange setcontent', function() {
                    drawBoundaries();
                });

                // TAB → indent, SHIFT+TAB → outdent
                editor.on('keydown', function(e) {
                    if (e.key === 'Tab') {
                        e.preventDefault();
                        if (e.shiftKey) {
                            editor.execCommand('Outdent');
                        } else {
                            editor.execCommand('Indent');
                        }
                    }
                });
            }
        });
    </script>

    <script>
        // Toggle mode (PDF / Ketik)
        document.getElementById('mode_surat').addEventListener('change', function() {
            let mode = this.value;

            document.getElementById('pdfBox').classList.toggle('hidden', mode !== 'pdf');
            document.getElementById('ketikBox').classList.toggle('hidden', mode !== 'ketik');
        });

        // LOAD TEMPLATE INTO TINYMCE
        document.getElementById('template_id').addEventListener('change', function() {
            const id = this.value;

            if (!id) {
                if (window.myTiny) window.myTiny.setContent('<div class="page"></div>');
                return;
            }

            fetch(`/template/raw/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (window.myTiny) {
                        // tetap bungkus dengan .page agar layout A4
                        window.myTiny.setContent(`<div class="page">${data.html}</div>`);
                    }
                });
        });

        // BERSIHKAN HTML sebelum form disubmit
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[action="{{ route('surat_masuk.store') }}"]');

            if (!form) return;

            form.addEventListener('submit', function() {
                const ed = tinymce.get('editor');
                if (!ed) return;

                let content = ed.getContent();

                // buang .boundary-overlay
                content = content.replace(/<div class="boundary-overlay"[\s\S]*?<\/div>/gi, '');

                // buang wrapper .page
                content = content.replace(/<div class="page">/gi, '');
                content = content.replace(/<\/div>\s*$/gi, ''); // closing page terakhir

                ed.setContent(content);
                tinymce.triggerSave();
            });
        });
    </script>

    <script>
        function openTemplateModal() {
            document.getElementById('modalTemplate').classList.remove('hidden');
            document.getElementById('modalTemplate').classList.add('flex');
        }

        function closeTemplateModal() {
            document.getElementById('modalTemplate').classList.add('hidden');
            document.getElementById('modalTemplate').classList.remove('flex');
        }

        document.getElementById("formTemplate").addEventListener("submit", function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            fetch("{{ route('template.store') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "X-Requested-With": "XMLHttpRequest" // <- penting supaya $request->ajax() = true
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {

                    if (data.success) {
                        // Tambah ke dropdown
                        const select = document.getElementById("template_id");
                        const option = new Option(data.template.nama_template, data.template.id);
                        select.add(option);

                        // Auto-pilih template baru
                        select.value = data.template.id;

                        // Trigger event change -> otomatis fetch /template/raw/{id}
                        const event = new Event('change');
                        select.dispatchEvent(event);

                        // Tutup modal
                        closeTemplateModal();
                    } else {
                        alert("Gagal menyimpan template.");
                    }

                })
                .catch(err => {
                    console.error(err);
                    alert("Terjadi kesalahan saat menyimpan template.");
                });
        });
    </script>
@endsection
