<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Nota - {{ $nota->nomor_nota }}</title>

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

        /* TABLE PRINT */
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
            width: 18%;
            white-space: nowrap;
            font-weight: 700;
        }

        .value {
            width: 82%;
        }

        .wrap {
            word-break: break-word;
        }

        /* feedback bubbles inside table cell */
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

        /* Lampiran PDF pages */
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
        <span id="printStatus" class="muted" style="align-self:center;">Menyiapkan lampiran...</span>
    </div>

    <div class="page">
        <table class="sheet">
            <tr>
                <th colspan="2" class="items-center">NOTA DINAS</th>
            </tr>

            <tr>
                <td class="label">No Nota</td>
                <td class="value wrap">{{ $nota->nomor_nota }}</td>
            </tr>
            <tr>
                <td class="label">Judul</td>
                <td class="value wrap">{{ $nota->judul }}</td>
            </tr>
            <tr>
                <td class="label">Pengirim</td>
                <td class="value wrap">
                    {{ $nota->pengirim->primaryPosition()->name ?? $nota->pengirim->name }}
                </td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="value wrap">{{ optional($nota->created_at)->format('d M Y H:i') }}</td>
            </tr>
            {{-- <tr>
                <td class="label">Validasi</td>
                <td class="value wrap">
                    Telah divalidasi oleh <b>{{ $validatorName }}</b>
                    pada {{ $validatedAt }}
                </td>
            </tr> --}}


            <tr>
                <td class="section-title" colspan="2">ISI NOTA</td>
            </tr>
            <tr>
                <td colspan="2" class="wrap">
                    {!! $nota->isi !!}
                </td>
            </tr>

            <tr>
                <td class="section-title" colspan="2">TANGGAPAN / SARAN</td>
            </tr>
            <tr>
                <td colspan="2" class="wrap">
                    @forelse($feedback as $f)
                        <div class="bubble">
                            <div class="head">
                                <div class="name">{{ $f->user->name ?? 'User' }}</div>
                                <div class="time">{{ optional($f->created_at)->format('d M Y H:i') }}</div>
                            </div>

                            <div style="margin-top:6px;">{{ $f->pesan }}</div>

                            @if ($f->lampiran)
                                @php
                                    $ext = strtolower(pathinfo($f->lampiran, PATHINFO_EXTENSION));
                                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                @endphp
                                @if ($isImg)
                                    <div style="margin-top:8px;">
                                        <img src="{{ asset('storage/' . $f->lampiran) }}" alt="Lampiran Feedback">
                                    </div>
                                @else
                                    <div class="muted" style="margin-top:8px;">
                                        Lampiran feedback ({{ strtoupper($ext) }}): {{ basename($f->lampiran) }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    @empty
                        <i class="muted">Tidak ada tanggapan.</i>
                    @endforelse
                </td>
            </tr>

            <tr>
                <td class="section-title" colspan="2">LAMPIRAN NOTA</td>
            </tr>
            <tr>
                <td colspan="2">
                    <div id="lampiranArea" class="wrap"></div>

                    @if (!$nota->lampiran)
                        <div class="muted" style="margin-top:6px;">Tidak ada lampiran.</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- PDF.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <script>
        const btnPrint = document.getElementById('btnPrint');
        const statusEl = document.getElementById('printStatus');
        const lampiranArea = document.getElementById('lampiranArea');

        const lampiranUrl = @json($nota->lampiran ? asset('storage/' . $nota->lampiran) : null);

        function readyToPrint(msg = 'Siap dicetak.') {
            btnPrint.disabled = false;
            statusEl.textContent = msg;
            btnPrint.addEventListener('click', () => window.print());
        }

        async function renderPdfToCanvas(url) {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";

            const loadingTask = pdfjsLib.getDocument(url);
            const pdf = await loadingTask.promise;

            statusEl.textContent = `Memuat lampiran (${pdf.numPages} halaman)...`;

            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const page = await pdf.getPage(pageNum);

                // scale print clarity
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
                lampiranArea.appendChild(wrap);

                await page.render({
                    canvasContext: ctx,
                    viewport
                }).promise;

                statusEl.textContent = `Render lampiran: halaman ${pageNum}/${pdf.numPages}...`;
            }
        }

        (async function init() {
            if (!lampiranUrl) {
                readyToPrint('Siap dicetak (tanpa lampiran).');
                return;
            }

            const ext = (lampiranUrl.split('.').pop() || '').toLowerCase();

            if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                const img = document.createElement('img');
                img.src = lampiranUrl;
                img.alt = 'Lampiran Nota';
                lampiranArea.appendChild(img);

                img.onload = () => readyToPrint('Siap dicetak (lampiran gambar siap).');
                img.onerror = () => readyToPrint('Lampiran gagal dimuat, tapi nota tetap bisa dicetak.');
                return;
            }

            if (ext === 'pdf') {
                try {
                    await renderPdfToCanvas(lampiranUrl);
                    readyToPrint('Siap dicetak (lampiran PDF sudah dirender).');
                } catch (e) {
                    console.error(e);
                    const a = document.createElement('a');
                    a.href = lampiranUrl;
                    a.target = "_blank";
                    a.textContent = "Lampiran tidak bisa dirender otomatis. Klik untuk buka lampiran.";
                    lampiranArea.appendChild(a);
                    readyToPrint('Lampiran gagal dirender, tapi nota tetap bisa dicetak.');
                }
                return;
            }

            const a = document.createElement('a');
            a.href = lampiranUrl;
            a.target = "_blank";
            a.textContent = "Lampiran (format non-PDF/gambar). Klik untuk buka.";
            lampiranArea.appendChild(a);
            readyToPrint('Siap dicetak (lampiran berupa link).');
        })();
    </script>
</body>

</html>
