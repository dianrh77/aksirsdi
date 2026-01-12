@extends('layouts.master')

@section('content')
    <div class="p-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                Perihal:</strong> {{ $nota->judul }}
            </h2>
            <a href="{{ route('nota.inbox') }}" class="text-blue hover:underline">← Kembali</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- ====================== -->
            <!-- KIRI: DETAIL NOTA -->
            <!-- ====================== -->
            <div class="md:col-span-2 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md space-y-5">

                <div class="flex justify-between items-start">
                    <div>
                        <p><strong>No Nota:</strong> {{ $nota->nomor_nota }}</p>
                        {{-- <p><strong>Perihal:</strong> {{ $nota->judul }}</p> --}}
                        <p><strong>Pengirim:</strong> {{ $nota->pengirim->primaryPosition()->name }}</p>
                        <p><strong>Tanggal:</strong> {{ $nota->created_at->format('d M Y') }}</p>

                        @if ($nota->lampiran)
                            <button type="button" class="btn btn-outline-primary mt-2 view-pdf"
                                data-url="{{ asset('storage/' . $nota->lampiran) }}">
                                📎 Lampiran Nota
                            </button>
                        @endif

                        {{-- ✅ Lampiran Lain (NotaDinas) --}}
                        @if ($nota->lampiran_lain)
                            @php
                                $url = asset('storage/' . $nota->lampiran_lain);
                                $nama = $nota->lampiran_lain_nama ?? basename($nota->lampiran_lain);
                                $ext = strtolower(pathinfo($nama, PATHINFO_EXTENSION));
                                $isPdf = $ext === 'pdf';
                            @endphp

                            @if ($isPdf)
                                <button type="button" class="btn btn-outline-primary mt-2 view-pdf"
                                    data-url="{{ $url }}">
                                    📎 Lampiran Lain: {{ $nama }}
                                </button>
                            @else
                                <a href="{{ $url }}" target="_blank" class="btn btn-outline-secondary mt-2">
                                    📎 Lampiran Lain: {{ $nama }}
                                </a>
                            @endif
                        @endif
                        
                        <a href="{{ route('nota.inbox.print', $nota->id) }}" target="_blank"
                            class="btn btn-outline-primary mt-2">
                            🖨️ Cetak (1x, termasuk lampiran)
                        </a>

                    </div>

                    <!-- Tombol Tandai Selesai -->
                    @php
                        $penerimaRow = $nota->penerima->where('id', auth()->id())->first();
                    @endphp

                    @if ($penerimaRow && $penerimaRow->pivot->status != 'selesai')
                        <form id="selesaiForm" action="{{ route('nota.inbox.selesai', $nota->id) }}" method="POST"
                            class="inline">
                            @csrf
                            <button type="button" id="btnTandaiSelesai" class="btn btn-success">
                                ✔ Tandai Selesai
                            </button>
                        </form>
                    @endif
                </div>

                <div class="border-t pt-4 prose dark:prose-invert">
                    {!! $nota->isi !!}
                </div>

                <!-- Diteruskan ke (Penerima Nota + Status) -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">📬 Diteruskan Kepada</h3>

                    @if ($nota->penerima && $nota->penerima->count())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($nota->penerima as $u)
                                @php
                                    $status = strtolower($u->pivot->status ?? 'baru');
                                    $tipe = strtolower($u->pivot->tipe ?? 'langsung');

                                    // warna badge berdasarkan status
                                    $badge = in_array($status, ['baru', 'pending_manager'])
                                        ? 'bg-yellow'
                                        : (in_array($status, ['dibaca', 'diproses', 'validasi'])
                                            ? 'bg-blue'
                                            : (in_array($status, ['selesai'])
                                                ? 'bg-green'
                                                : (in_array($status, ['rejected', 'ditolak'])
                                                    ? 'bg-red'
                                                    : 'bg-gray-300')));

                                    // label status biar enak dibaca
                                    $labelStatus = match ($status) {
                                        'pending_manager' => 'Pending Manager',
                                        default => ucfirst($status),
                                    };

                                    $labelTipe = match ($tipe) {
                                        'validasi' => 'Validasi',
                                        default => 'Langsung',
                                    };
                                @endphp

                                <span class="px-3 py-1 rounded-full text-sm {{ $badge }}">
                                    {{ optional($u->primaryPosition())->name ?? ($u->name ?? 'User Tidak Dikenal') }}
                                    <span class="opacity-80">({{ $labelTipe }} • {{ $labelStatus }})</span>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm italic">Belum ada penerima.</p>
                    @endif
                </div>

            </div>

            <!-- ====================== -->
            <!-- KANAN: CHAT / FEEDBACK -->
            <!-- ====================== -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md flex flex-col max-h-[80vh]">

                <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-4">
                    💬 Diskusi & Balasan
                </h3>

                <div id="chat-area"
                    class="flex-1 overflow-y-auto space-y-4 rounded-lg p-3 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">

                    @forelse ($feedback as $item)
                        @php $isSaya = $item->user_id == auth()->id(); @endphp

                        <div class="flex {{ $isSaya ? 'justify-end' : 'justify-start' }}">
                            <div
                                class="max-w-[75%] px-4 py-3 rounded-2xl shadow border
                                {{ $isSaya ? 'bg-green rounded-br-none' : 'bg-blue rounded-bl-none' }}">

                                <p class="font-semibold text-sm">
                                    {{ $isSaya ? 'Anda' : $item->user->name }}
                                </p>

                                <p class="text-sm mt-1">{{ $item->pesan }}</p>

                                @once
                                    @php
                                        function short_file($name, $limit = 25)
                                        {
                                            return strlen($name) > $limit ? substr($name, 0, $limit) . '…' : $name;
                                        }
                                    @endphp
                                @endonce


                                @if ($item->lampiran)
                                    @php
                                        $fileName = basename($item->lampiran);
                                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                        $isPdf = $ext === 'pdf';
                                        $short = strlen($fileName) > 25 ? substr($fileName, 0, 25) . '…' : $fileName;
                                    @endphp

                                    @if ($isPdf)
                                        <button type="button" class="btn btn-outline-primary view-pdf"
                                            data-url="{{ asset('storage/' . $item->lampiran) }}">
                                            📎 {{ $short }}
                                        </button>
                                    @else
                                        <a href="{{ asset('storage/' . $item->lampiran) }}" download="{{ $fileName }}"
                                            class="btn btn-outline-secondary">
                                            📥 {{ $short }}
                                        </a>
                                    @endif

                                    {{-- Tombol Hapus jika milik user --}}
                                    @if ($item->user_id == auth()->id())
                                        <form action="{{ route('nota.inbox.lampiran.hapus', $item->id) }}" method="POST"
                                            class="inline formHapusLampiran">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger btn-sm btnHapusLampiran">
                                                🗑 Hapus
                                            </button>
                                        </form>
                                    @endif
                                @endif


                                <p class="text-[10px] text-gray-500 mt-1">
                                    {{ $item->created_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 italic">Belum ada balasan.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ====================== -->
        <!-- FORM INPUT BALASAN -->
        <!-- ====================== -->
        <form action="{{ route('nota.inbox.reply.store', $nota->id) }}" method="POST" enctype="multipart/form-data"
            class="mt-6">
            @csrf

            <div class="flex flex-col md:flex-row md:items-center gap-2">
                {{-- Textarea: full di mobile --}}
                <textarea name="pesan" rows="3" required
                    class="w-full md:flex-1 border rounded-xl px-3 py-2 text-sm dark:bg-gray-900 resize-none
                   focus:ring-2 focus:ring-emerald-400 min-h-[90px] md:min-h-[44px]"
                    placeholder="Tulis balasan..."></textarea>

                {{-- Tombol: rapih di mobile --}}
                <div class="grid grid-cols-2 md:flex gap-2 w-full md:w-auto">
                    <input type="file" name="lampiran[]" id="lampiran" class="hidden" multiple>

                    <label for="lampiran"
                        class="btn btn-outline-primary w-full md:w-auto flex items-center justify-center h-12 px-4">
                        📎 Lampiran
                    </label>

                    <button type="submit"
                        class="btn btn-outline-success w-full md:w-auto flex items-center justify-center h-12 px-6">
                        ➤ Kirim
                    </button>
                </div>
            </div>

            <div id="previewLampiran" class="mt-2 text-sm text-blue"></div>
        </form>


        <!-- ====================== -->
        <!-- MODAL PDF -->
        <!-- ====================== -->
        <div id="pdfModal" class="fixed inset-0 hidden bg-black bg-opacity-60 z-50 flex items-center justify-center">
            <div class="bg-white w-11/12 h-[90vh] rounded-lg shadow-lg overflow-hidden">
                <div class="p-3 border-b flex justify-between">
                    <h3 class="font-semibold">📄 Lihat Lampiran</h3>
                    <button id="closePdf" class="text-red text-lg font-bold">✖</button>
                </div>
                <iframe id="pdfFrame" class="w-full h-full"></iframe>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        // auto-scroll chat
        document.addEventListener('DOMContentLoaded', () => {
            let chat = document.getElementById('chat-area');
            chat.scrollTop = chat.scrollHeight;
        });

        // preview lampiran
        document.getElementById('lampiran').addEventListener('change', function() {
            let wrap = document.getElementById('previewLampiran');
            wrap.innerHTML = '';
            [...this.files].forEach(f => wrap.innerHTML += "📎 " + f.name + "<br>");
        });

        // pdf modal
        document.addEventListener('click', e => {
            let btn = e.target.closest('.view-pdf');
            if (!btn) return;

            document.getElementById('pdfFrame').src = btn.dataset.url + "#toolbar=1&zoom=100";
            document.getElementById('pdfModal').classList.remove('hidden');
        });

        document.getElementById('closePdf').onclick = () =>
            document.getElementById('pdfModal').classList.add('hidden');
    </script>

    <!-- Swal -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const btn = document.getElementById('btnTandaiSelesai');
        if (btn) {
            btn.addEventListener('click', () => {
                Swal.fire({
                    title: 'Tandai selesai?',
                    text: 'Anda tidak bisa membatalkannya.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, selesai',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) document.getElementById('selesaiForm').submit();
                });
            });
        }
    </script>

    <script>
        document.addEventListener('click', function(e) {
            let btn = e.target.closest('.btnHapusLampiran');
            if (!btn) return;

            let form = btn.closest('form');

            Swal.fire({
                title: 'Hapus lampiran?',
                text: 'Lampiran ini akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-outline-danger',
                    cancelButton: 'btn btn-outline-secondary'
                },
                buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    </script>
@endsection
