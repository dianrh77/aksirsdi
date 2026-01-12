<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Disposisi - {{ $penerima->disposisi->no_disposisi ?? '-' }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #111;
        }

        .toolbar {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-bottom: 16px;
        }

        .btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
        }

        .btn-primary {
            border-color: #2563eb;
            color: #2563eb;
        }

        .muted {
            color: #666;
            font-size: 12px;
        }

        .sheet {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .sheet td,
        .sheet th {
            border: 1px solid #ddd;
            padding: 10px;
            vertical-align: top;
        }

        .sheet th {
            background: #f5f5f5;
            text-align: left;
        }

        .section-title {
            font-weight: 700;
            background: #f5f5f5;
        }

        .label {
            width: 22%;
            white-space: nowrap;
            font-weight: 700;
        }

        .value {
            width: 78%;
        }

        .wrap {
            word-break: break-word;
        }

        .bubble {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .bubble .head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .bubble .name {
            font-weight: 700;
        }

        .bubble .time {
            font-size: 11px;
            color: #666;
            white-space: nowrap;
        }

        .pdf-page {
            margin: 0 0 14px 0;
            page-break-after: always;
        }

        .pdf-page:last-child {
            page-break-after: auto;
        }

        canvas {
            max-width: 100% !important;
            height: auto !important;
            display: block;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        @media print {
            .toolbar {
                display: none !important;
            }

            body {
                margin: 0;
            }

            .page {
                padding: 15mm;
            }
        }
    </style>
</head>

<body>

    <div class="toolbar">
        <button class="btn btn-primary" id="btnPrint" disabled>🖨️ Print</button>
        <button class="btn" onclick="window.close()">✖ Tutup</button>
        <span id="printStatus" class="muted" style="align-self:center;">Menyiapkan surat...</span>
    </div>

    <div class="page">
        <table class="sheet">
            <tr>
                <th colspan="2">DISPOSISI</th>
            </tr>

            <tr>
                <td class="label">No Disposisi</td>
                <td class="value wrap">{{ $penerima->disposisi->no_disposisi ?? '-' }}</td>
            </tr>

            <tr>
                <td class="label">Perihal</td>
                <td class="value wrap">{{ $penerima->disposisi->suratMasuk->perihal ?? '-' }}</td>
            </tr>

            <tr>
                <td class="label">Asal Surat</td>
                <td class="value wrap">{{ $penerima->disposisi->suratMasuk->asal_surat ?? '-' }}</td>
            </tr>

            <tr>
                <td class="label">Pengirim</td>
                <td class="value wrap">
                    {{ optional($penerima->disposisi->pengirim?->primaryPosition())->name ??
                        ($penerima->disposisi->pengirim->name ?? '-') }}
                </td>
            </tr>

            <tr>
                <td class="label">Tanggal</td>
                <td class="value wrap">{{ optional($penerima->disposisi->created_at)->format('d M Y H:i') }}</td>
            </tr>

            <tr>
                <td class="label">Jenis</td>
                <td class="value wrap">{{ $penerima->disposisi->jenis_disposisi ?? '-' }}</td>
            </tr>

            <tr>
                <td class="section-title" colspan="2">INSTRUKSI DIREKSI</td>
            </tr>
            <tr>
                <td colspan="2" class="wrap">
                    @php
                        $instruksiUtama = $penerima->disposisi->instruksis->where('jenis_direktur', 'utama')->first();
                        $instruksiUmum = $penerima->disposisi->instruksis->where('jenis_direktur', 'umum')->first();
                    @endphp

                    <div style="margin-bottom:10px;">
                        <b>Direktur Utama:</b><br>
                        {{ $instruksiUtama->instruksi ?? 'Belum ada instruksi.' }}
                        @if (!empty($instruksiUtama?->batas_waktu))
                            <div class="muted">Batas waktu:
                                {{ \Carbon\Carbon::parse($instruksiUtama->batas_waktu)->format('d M Y') }}</div>
                        @endif
                    </div>

                    <div>
                        <b>Direktur Umum:</b><br>
                        {{ $instruksiUmum->instruksi ?? 'Belum ada instruksi.' }}
                        @if (!empty($instruksiUmum?->batas_waktu))
                            <div class="muted">Batas waktu:
                                {{ \Carbon\Carbon::parse($instruksiUmum->batas_waktu)->format('d M Y') }}</div>
                        @endif
                    </div>
                </td>
            </tr>

            <tr>
                <td class="section-title" colspan="2">FEEDBACK / DISKUSI</td>
            </tr>
            <tr>
                <td colspan="2" class="wrap">
                    @forelse($riwayatFeedback as $f)
                        @php
                            $nama = optional($f->user?->primaryPosition())->name ?? ($f->user->name ?? 'User');
                        @endphp

                        <div class="bubble">
                            <div class="head">
                                <div class="name">{{ $nama }}</div>
                                <div class="time">{{ optional($f->created_at)->format('d M Y H:i') }}</div>
                            </div>

                            <div style="margin-top:6px;">
                                {{ $f->feedback }}
                            </div>

                            @if ($f->lampiran && $f->lampiran->count())
                                <div style="margin-top:8px;">
                                    <div class="muted"><b>Lampiran:</b></div>
                                    @foreach ($f->lampiran as $lamp)
                                        @php
                                            $ext = strtolower(pathinfo($lamp->file_name, PATHINFO_EXTENSION));
                                            $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                        @endphp

                                        @if ($isImg)
                                            <div style="margin-top:6px;">
                                                <img src="{{ asset('storage/' . $lamp->file_path) }}" alt="Lampiran">
                                            </div>
                                        @else
                                            <div class="muted" style="margin-top:4px;">
                                                {{ $lamp->file_name }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <i class="muted">Belum ada feedback.</i>
                    @endforelse
                </td>
            </tr>

            <tr>
                <td class="section-title" colspan="2">SURAT (PDF)</td>
            </tr>
            <tr>
                <td colspan="2">
                    <div id="suratArea" class="wrap"></div>
                    <div id="fallbackLink" class="muted" style="margin-top:6px;"></div>
                </td>
            </tr>

        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <script>
        const btnPrint = document.getElementById('btnPrint');
        const statusEl = document.getElementById('printStatus');
        const suratArea = document.getElementById('suratArea');
        const fallbackLink = document.getElementById('fallbackLink');

        const suratUrl = @json(
            $penerima->disposisi?->suratMasuk?->file_pdf
                ? asset('storage/' . $penerima->disposisi->suratMasuk->file_pdf)
                : null);

        function readyToPrint(msg = 'Siap dicetak.') {
            btnPrint.disabled = false;
            statusEl.textContent = msg;
            btnPrint.addEventListener('click', () => window.print());
        }

        async function renderPdfToCanvas(url) {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";

            const pdf = await pdfjsLib.getDocument(url).promise;
            statusEl.textContent = `Memuat surat (${pdf.numPages} halaman)...`;

            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const page = await pdf.getPage(pageNum);
                const viewport = page.getViewport({
                    scale: 1.6
                });

                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                const wrap = document.createElement('div');
                wrap.className = 'pdf-page';
                wrap.appendChild(canvas);
                suratArea.appendChild(wrap);

                await page.render({
                    canvasContext: ctx,
                    viewport
                }).promise;
                statusEl.textContent = `Render surat: halaman ${pageNum}/${pdf.numPages}...`;
            }
        }

        (async function init() {
            if (!suratUrl) {
                readyToPrint('Siap dicetak (tanpa surat PDF).');
                return;
            }

            try {
                await renderPdfToCanvas(suratUrl);
                readyToPrint('Siap dicetak (surat PDF sudah dirender).');
            } catch (e) {
                console.error(e);
                fallbackLink.innerHTML =
                    `Surat gagal dirender otomatis. <a href="${suratUrl}" target="_blank">Klik untuk buka surat</a>.`;
                readyToPrint('Surat gagal dirender, tapi tetap bisa dicetak.');
            }
        })();
    </script>

</body>

</html>
