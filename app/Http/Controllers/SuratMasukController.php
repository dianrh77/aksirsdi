<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Template;
use App\Helper\WppHelper;
use App\Models\Disposisi;

use App\Helper\DocxHelper;
use App\Models\SuratMasuk;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SuratInternalDoc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class SuratMasukController extends Controller
{
    /* ============================================================
     *  FUNGSI HIRARKI (TIDAK DIUBAH)
     * ============================================================ */
    private function isBawahan($manager, $suratPositionId)
    {
        $managerPos = $manager->primaryPosition();
        $childPositions = $this->getAllChildrenPositions($managerPos);
        return $childPositions->contains($suratPositionId);
    }

    private function getAllChildrenPositions($position)
    {
        $result = collect();
        foreach ($position->children as $child) {
            $result->push($child->id);
            $result = $result->merge($this->getAllChildrenPositions($child));
        }
        return $result;
    }

    private function posisiTerakhirSurat(?\App\Models\SuratMasuk $surat): array
    {
        // default
        $result = [
            'text' => 'Belum didisposisi',
            'time' => null,
            'state' => 'none',
        ];

        // kalau surat belum punya disposisi
        if (!$surat || !$surat->disposisi) return $result;

        $disposisi = $surat->disposisi;

        // 1) ambil FORWARD terakhir dari feedback
        $lastForward = \App\Models\DisposisiFeedback::whereHas('penerima', function ($q) use ($disposisi) {
            $q->where('disposisi_id', $disposisi->id);
        })
            ->where('feedback', 'like', '➡️ Disposisi Diteruskan:%')
            ->latest('created_at')
            ->first();

        if ($lastForward) {
            return [
                'text' => $lastForward->feedback,
                'time' => optional($lastForward->created_at)->format('d M Y H:i'),
                'state' => 'forward',
            ];
        }

        // 2) fallback: belum ada forward → penerima awal disposisi
        $receivers = \App\Models\DisposisiPenerima::with(['penerima.positions']) // ✅ bukan primaryPosition
            ->where('disposisi_id', $disposisi->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // ambil nama jabatan penerima
        $receiverNames = $receivers
            ->map(function ($r) {
                $u = $r->penerima;
                if (!$u) return null;

                // ambil posisi primary dari pivot (is_primary)
                $primaryPos = $u->positions?->firstWhere('pivot.is_primary', 1);

                return $primaryPos?->name ?? $u->name;
            })
            ->filter()
            ->unique()
            ->implode(', ');


        return [
            'text'  => $receiverNames
                ? "Diteruskan kepada: {$receiverNames}"
                : "Belum ada instruksi Direktur",
            'time'  => optional($disposisi->created_at)->format('d M Y H:i'),
            'state' => 'initial',
        ];
    }


    /* ============================================================
 *  INDEX
 * ============================================================ */
    public function index()
    {
        $user = Auth::user();
        $position = $user->primaryPosition();
        $level = $user->getLevel();
        $userId = $user->id;

        // dd($level);

        /* ===============================
     *  ROLE: KESEKRETARIATAN
     * =============================== */
        if ($user->role_name === 'kesekretariatan') {

            $internal = SuratMasuk::where('jenis_surat', 'internal')
                ->where(fn($q) => $q->where('created_by', $userId)
                    ->orWhereIn('status', ['menunggu_kesra', 'siap_disposisi', 'didisposisi']))
                // ->latest()->get();
                ->with('disposisi.pengirim')   // ✅ tambah ini
                ->latest()
                ->get()
                ->map(function ($s) {
                    $pos = $this->posisiTerakhirSurat($s);
                    $s->posisi_terakhir = $pos['text'];
                    $s->posisi_waktu = $pos['time'];
                    $s->posisi_state = $pos['state'];
                    return $s;
                });

            $internalHold = SuratMasuk::where('jenis_surat', 'internal')
                ->where(fn($q) => $q->where('created_by', $userId)
                    ->orWhereIn('status', ['menunggu_kesra', 'siap_disposisi', 'didisposisi']))
                ->whereHas('disposisi', function ($q) {
                    $q->where('status', 'Hold');
                })
                ->with('disposisi.pengirim', 'disposisi.instruksis')
                ->latest()
                ->get()
                ->map(function ($s) {
                    $pos = $this->posisiTerakhirSurat($s);
                    $s->posisi_terakhir = $pos['text'];
                    $s->posisi_waktu = $pos['time'];
                    $s->posisi_state = $pos['state'];
                    $s->status = 'hold';
                    $holdInstruksi = $s->disposisi?->instruksis
                        ?->where('proses_status', 'hold')
                        ?->sortByDesc('created_at')
                        ?->first();
                    $s->hold_reason = $holdInstruksi?->hold_reason ?? '-';
                    return $s;
                });

            $internalMenunggu = collect([]);   // untuk Alpine
            $internalLainnya = collect([]);    // untuk Alpine

            $external = SuratMasuk::where('jenis_surat', 'eksternal')
                // ->latest()->get();
                ->with('disposisi.pengirim')   // ✅ tambah ini
                ->latest()
                ->get()
                ->map(function ($s) {
                    $pos = $this->posisiTerakhirSurat($s);
                    $s->posisi_terakhir = $pos['text'];
                    $s->posisi_waktu = $pos['time'];
                    $s->posisi_state = $pos['state'];
                    return $s;
                });

            return view('surat_masuk.index', [
                'internal' => $internal,
                'internalHold' => $internalHold,
                'internalMenunggu' => $internalMenunggu,
                'internalLainnya' => $internalLainnya,
                'external' => $external,
                'level' => $level
            ]);
        }

        /* ===============================
     *  ROLE: MANAJER (LEVEL 3)
     * =============================== */
        if ($level === 3) {

            $childPositions = $this->getAllChildrenPositions($position);

            $internalMenunggu = SuratMasuk::where('jenis_surat', 'internal')
                ->where('status', 'menunggu_manager')
                ->where(fn($q) => $q->where('created_by', $userId)
                    ->orWhereIn('position_id', $childPositions))
                // ->latest()->get();
                ->with('disposisi.pengirim')   // ✅ tambah ini
                ->latest()
                ->get()
                ->map(function ($s) {
                    $pos = $this->posisiTerakhirSurat($s);
                    $s->posisi_terakhir = $pos['text'];
                    $s->posisi_waktu = $pos['time'];
                    $s->posisi_state = $pos['state'];
                    return $s;
                });

            $internalLainnya = SuratMasuk::where('jenis_surat', 'internal')
                ->where('status', '!=', 'menunggu_manager')
                ->where(fn($q) => $q->where('created_by', $userId)
                    ->orWhereIn('position_id', $childPositions))
                // ->latest()->get();
                ->with('disposisi.pengirim')   // ✅ tambah ini
                ->latest()
                ->get()
                ->map(function ($s) {
                    $pos = $this->posisiTerakhirSurat($s);
                    $s->posisi_terakhir = $pos['text'];
                    $s->posisi_waktu = $pos['time'];
                    $s->posisi_state = $pos['state'];
                    return $s;
                });

            $internalHold = SuratMasuk::where('jenis_surat', 'internal')
                ->where(fn($q) => $q->where('created_by', $userId)
                    ->orWhereIn('position_id', $childPositions))
                ->whereHas('disposisi', function ($q) {
                    $q->where('status', 'Hold');
                })
                ->with('disposisi.pengirim', 'disposisi.instruksis')
                ->latest()
                ->get()
                ->map(function ($s) {
                    $pos = $this->posisiTerakhirSurat($s);
                    $s->posisi_terakhir = $pos['text'];
                    $s->posisi_waktu = $pos['time'];
                    $s->posisi_state = $pos['state'];
                    $s->status = 'hold';
                    $holdInstruksi = $s->disposisi?->instruksis
                        ?->where('proses_status', 'hold')
                        ?->sortByDesc('created_at')
                        ?->first();
                    $s->hold_reason = $holdInstruksi?->hold_reason ?? '-';
                    return $s;
                });

            return view('surat_masuk.index', [
                'internal' => collect([]), // biar Alpine tidak error
                'internalHold' => $internalHold,
                'internalMenunggu' => $internalMenunggu,
                'internalLainnya' => $internalLainnya,
                'external' => collect([]),
                'level' => $level
            ]);
        }


        /* ===============================
     *  ROLE: STAFF / KASI / KARU
     * =============================== */
        if ($level > 3) {

            // $internal = SuratMasuk::where('jenis_surat', 'internal')
            //     ->where('created_by', $userId)
            //     ->latest()->get();
            $internal = SuratMasuk::where('jenis_surat', 'internal')
                ->where(fn($q) => $q->where('created_by', $userId))
                // ->orWhereIn('status', ['menunggu_kesra', 'siap_disposisi', 'didisposisi']))
                ->with('disposisi.pengirim')   // ✅ tambah ini
                ->latest()
                ->get()
                ->map(function ($s) {
                    $pos = $this->posisiTerakhirSurat($s);
                    $s->posisi_terakhir = $pos['text'];
                    $s->posisi_waktu = $pos['time'];
                    $s->posisi_state = $pos['state'];
                    return $s;
                });

            $internalHold = SuratMasuk::where('jenis_surat', 'internal')
                ->where('created_by', $userId)
                ->whereHas('disposisi', function ($q) {
                    $q->where('status', 'Hold');
                })
                ->with('disposisi.pengirim', 'disposisi.instruksis')
                ->latest()
                ->get()
                ->map(function ($s) {
                    $pos = $this->posisiTerakhirSurat($s);
                    $s->posisi_terakhir = $pos['text'];
                    $s->posisi_waktu = $pos['time'];
                    $s->posisi_state = $pos['state'];
                    $s->status = 'hold';
                    $holdInstruksi = $s->disposisi?->instruksis
                        ?->where('proses_status', 'hold')
                        ?->sortByDesc('created_at')
                        ?->first();
                    $s->hold_reason = $holdInstruksi?->hold_reason ?? '-';
                    return $s;
                });

            return view('surat_masuk.index', [
                'internal' => $internal,
                'internalHold' => $internalHold,
                'internalMenunggu' => collect([]),
                'internalLainnya' => collect([]),
                'external' => collect([]),
                'level' => $level
            ]);
        }


        /* ===============================
     *  DEFAULT (Jaga-jaga)
     * =============================== */
        return view('surat_masuk.index', [
            'internal' => collect([]),
            'internalHold' => collect([]),
            'internalMenunggu' => collect([]),
            'internalLainnya' => collect([]),
            'external' => collect([]),
            'level' => $level
        ]);
    }



    /* ============================================================
     *  CREATE
     * ============================================================ */
    public function create()
    {
        $templates = Template::orderBy('nama_template')->get();
        return view('surat_masuk.create', compact('templates'));
    }


    /* ======================================================
    *  STORE (MODE KETIK DENGAN PERBAIKAN TOTAL)
    * ====================================================== */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'no_surat'       => 'required|string|max:255',
                'tgl_surat'      => 'required|date',
                'asal_surat'     => 'required|string|max:255',
                'perihal'        => 'required|string',
                'jenis_surat'    => 'required|in:internal,eksternal',
                'mode_surat'     => 'required|in:pdf,ketik',
                'file_pdf'       => 'nullable|mimes:pdf|max:20480',
                'lampiran_pdf'   => 'nullable|mimes:pdf|max:20480'
            ]);

            $user = Auth::user();
            $userLevel = $user->getLevel();
            $userRole = $user->role_name;

            /* ============================================================
         * 1. Tentukan STATUS AWAL berdasarkan pembuat surat
         * ============================================================ */
            if ($userLevel == 3) {
                // Manager → langsung disposisi otomatis
                $status = 'didisposisi';
            } elseif ($userRole === 'kesekretariatan') {
                $status = 'menunggu_kesra';
            } else {
                // staf / kasi / karu
                $status = 'menunggu_manager';
            }

            /* ============================================================
         * 2. SIMPAN SURAT MASUK
         * ============================================================ */
            $surat = SuratMasuk::create([
                'no_surat'     => $request->no_surat,
                'tgl_surat'    => $request->tgl_surat,
                'asal_surat'   => $request->asal_surat,
                'perihal'      => $request->perihal,
                'jenis_surat'  => $request->jenis_surat,
                'file_pdf'     => 'pending.pdf',
                'position_id'  => $user->primaryPosition()->id,
                'created_by'   => $user->id,
                'status'       => $status,
            ]);

            /* ============================================================
         * 3. MODE PDF UPLOAD
         * ============================================================ */
            if ($request->mode_surat === 'pdf') {

                if (!$request->hasFile('file_pdf')) {
                    Alert::error('Gagal!', 'File PDF wajib diupload.');
                    return back()->withInput();
                }

                // surat utama
                $path = $request->file('file_pdf')->store('surat_masuk', 'public');
                $surat->update(['file_pdf' => $path]);

                // lampiran (optional)
                $lampiranPath = null;
                if ($request->hasFile('lampiran_pdf')) {
                    $lampiranPath = $request->file('lampiran_pdf')->store('lampiran', 'public');
                }

                // SIMPAN lampiran ke SuratInternalDoc (biar konsisten karena kolom lampiran_pdf ada di sini)
                if ($lampiranPath) {
                    SuratInternalDoc::create([
                        'surat_id'     => $surat->id,
                        'template_id'  => null,
                        'data_isian'   => null,
                        'file_docx'    => null,
                        'file_pdf'     => $path,         // opsional: simpan juga referensi surat utamanya
                        'lampiran_pdf' => $lampiranPath,
                        'version'      => 1,
                        'is_active'    => true,
                    ]);
                }
            }


            /* ============================================================
         * 4. MODE KETIK HTML → PDF
         * ============================================================ */
            if ($request->mode_surat === 'ketik') {

                if (!$request->editor_text || trim($request->editor_text) == "") {
                    Alert::error('Gagal!', 'Isi surat tidak boleh kosong.');
                    return back()->withInput();
                }

                $clean = $request->editor_text;

                $clean = preg_replace('/<div class="page">/i', '', $clean);
                $clean = preg_replace('/<\/div>\s*$/i', '', $clean);
                $clean = preg_replace('/<div class="boundary-overlay"[\s\S]*?<\/div>/i', '', $clean);

                $pdfHtml = "
                <style>
                    @page { margin: 25mm; }
                    body { 
                        font-family: 'Times New Roman', serif;
                        font-size: 12pt;
                        line-height: 1.5;
                        text-align: justify;
                    }
                    p { margin: 0 0 10px; }
                    span { font-family: 'Times New Roman'; font-size: 12pt; }
                </style>
            " . $clean;

                $pdf = Pdf::loadHTML($pdfHtml)->setPaper('a4', 'portrait');

                $pdfName = 'surat_' . time() . '.pdf';
                Storage::disk('public')->put('surat_masuk/' . $pdfName, $pdf->output());

                $pdfRelative = 'surat_masuk/' . $pdfName;

                $lampiranPath = null;
                if ($request->hasFile('lampiran_pdf')) {
                    $lampiranPath = $request->file('lampiran_pdf')->store('lampiran', 'public');
                }

                SuratInternalDoc::create([
                    'surat_id'      => $surat->id,
                    'template_id'   => $request->template_id,
                    'data_isian'    => $clean,
                    'file_docx'     => null,
                    'file_pdf'      => $pdfRelative,
                    'lampiran_pdf'  => $lampiranPath,
                ]);

                $surat->update(['file_pdf' => $pdfRelative]);
            }

            /* ============================================================
         * 5. KHUSUS MANAGER → LANGSUNG BUAT DISPOSISI OTOMATIS
         * ============================================================ */
            if ($userLevel == 3) {

                // Generate nomor disposisi
                $bulan = date('m');
                $tahun = date('Y');

                $last = \App\Models\Disposisi::whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun)
                    ->latest('id')
                    ->first();

                $lastNumber = $last ? (int) explode('-', $last->no_disposisi)[3] : 0;
                $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

                $noDisposisi = "DISP-{$tahun}-{$bulan}-{$nextNumber}";

                $pengirimId = $surat->created_by; // pembuat surat

                $catatanAuto = "Surat dibuat & divalidasi langsung oleh Manager (" . $user->name . ").";

                $jenis = $request->jenis_disposisi_manager ?? 'biasa';

                $disposisi = Disposisi::create([
                    'no_disposisi'     => $noDisposisi,
                    'surat_id'         => $surat->id,
                    'pengirim_id'      => $pengirimId,
                    'catatan'          => $catatanAuto,
                    'jenis_disposisi'  => $jenis,
                    'status'           => 'Dibuat',
                ]);


                // Kirim notif ke Direktur
                $direktur = \App\Models\User::where('role_name', 'direktur_utama')->first();
                if ($direktur && $direktur->phone_number) {
                    $msg = "📨 *DISPOSISI BARU (DARI MANAGER)*\n\n"
                        . "No Disposisi : {$disposisi->no_disposisi}\n"
                        . "No Surat     : {$surat->no_surat}\n"
                        . "Perihal      : {$surat->perihal}\n"
                        . "Asal Surat   : {$surat->asal_surat}\n"
                        . "Pembuat Surat: {$user->name}\n\n"
                        . "Jenis        : {$jenis}\n"
                        . "Catatan      : {$catatanAuto}\n\n"
                        . "Silakan isi instruksi pada aplikasi.\n"
                        . "https://aksi.rsu-darulistiqomah.com";


                    WppHelper::sendMessage($direktur->phone_number, $msg);
                }

                // Notif informasi ke Kesra
                $kesraList = \App\Models\User::where('role_name', 'kesekretariatan')->get();
                foreach ($kesraList as $ks) {
                    if (!$ks->phone_number) continue;

                    $msgKs = "ℹ️ *INFO DISPOSISI BARU (MANAGER)*\n\n"
                        . "No Disposisi : {$disposisi->no_disposisi}\n"
                        . "No Surat     : {$surat->no_surat}\n"
                        . "Perihal      : {$surat->perihal}\n"
                        . "Asal Surat   : {$surat->asal_surat}\n\n"
                        . "Status Surat : *didisposisi otomatis ke Direktur*\n"
                        . "Validator    : {$user->name}\n"
                        . "Jenis        : {$jenis}\n"
                        . "Catatan      : {$catatanAuto}\n\n"
                        . "Ini hanya notifikasi informasi.\n"
                        . "https://aksi.rsu-darulistiqomah.com";


                    WppHelper::sendMessage($ks->phone_number, $msgKs);
                }
            }

            /* ============================================================
         * 6. NOTIFIKASI NORMAL (untuk pembuat surat non-manager)
         * ============================================================ */
            if ($userLevel > 3) {
                // pembuat surat adalah staf → notif ke manager
                $posisiUser = $user->primaryPosition();
                $posisiManager = \App\Models\Position::find($posisiUser->parent_id);

                if ($posisiManager) {
                    $manager = \App\Models\User::whereHas('positions', function ($q) use ($posisiManager) {
                        $q->where('positions.id', $posisiManager->id)->where('position_user.is_primary', 1);
                    })->first();

                    if ($manager && $manager->phone_number) {

                        $msgManager = "📨 *Permintaan Verifikasi Surat Baru*\n\n"
                            . "Nomor: {$surat->no_surat}\n"
                            . "Perihal: {$surat->perihal}\n"
                            . "Penginput: " . $user->name . "\n\n"
                            . "Silakan dilakukan verifikasi.\n"
                            . "https://aksi.rsu-darulistiqomah.com";

                        WppHelper::sendMessage($manager->phone_number, $msgManager);
                    }
                }
            } elseif ($userRole === 'kesekretariatan') {

                foreach (\App\Models\User::where('role_name', 'kesekretariatan')->get() as $ks) {
                    if (!$ks->phone_number) continue;

                    $msg = "📥 *Surat Baru Perlu Diproses*\n\n"
                        . "Nomor: {$surat->no_surat}\n"
                        . "Perihal: {$surat->perihal}\n"
                        . "Dari: {$surat->asal_surat}\n\n"
                        . "Silakan proses lebih lanjut.\n"
                        . "https://aksi.rsu-darulistiqomah.com";

                    WppHelper::sendMessage($ks->phone_number, $msg);
                }
            }

            Alert::success('Berhasil!', 'Surat berhasil disimpan.');
            return redirect()->route('surat_masuk.index');
        } catch (\Exception $e) {
            Alert::error('Terjadi Kesalahan!', $e->getMessage());
            return back()->withInput();
        }
    }





    /* ============================================================
     *  VIEW FILE PDF
     * ============================================================ */
    public function showFile($id)
    {
        $surat = SuratMasuk::findOrFail($id);
        $path = storage_path('app/public/' . $surat->file_pdf);

        return response()->file($path);
    }


    /* ============================================================
     *  VALIDASI & TOLAK SURAT (untuk manajer)
     * ============================================================ */
    public function validasi($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        if (Auth::user()->getLevel() !== 3) abort(403);
        if (!$this->isBawahan(Auth::user(), $surat->position_id)) abort(403);

        $surat->update(['status' => 'menunggu_kesra']);

        /* ======================
     * NOTIFIKASI KE KESRA
     * ====================== */
        $kesraUsers = User::where('role_name', 'kesekretariatan')->get();

        foreach ($kesraUsers as $ks) {
            if (!$ks->phone_number) continue;

            $msg = "📥 *Surat Baru Perlu Diproses*\n\n"
                . "Nomor: {$surat->no_surat}\n"
                . "Perihal: {$surat->perihal}\n"
                . "Asal Surat: {$surat->asal_surat}\n"
                . "Divalidasi Oleh: " . Auth::user()->name . "\n\n"
                . "Silakan proses di aplikasi.\n"
                . "https://aksi.rsu-darulistiqomah.com";

            WppHelper::sendMessage($ks->phone_number, $msg);
        }

        Alert::success('OK!', 'Surat berhasil divalidasi.');
        return back();
    }

    // public function validasiPopup(Request $request)
    // {
    //     $request->validate([
    //         'surat_id'        => 'required|integer|exists:surat_masuks,id',
    //         'jenis_disposisi' => 'required|in:biasa,penting,rahasia',
    //         'catatan'         => 'required|string'
    //     ]);

    //     $surat = SuratMasuk::findOrFail($request->surat_id);

    //     // Hanya manager level 3 & validasi bawahan
    //     if (Auth::user()->getLevel() !== 3) abort(403);
    //     if (!$this->isBawahan(Auth::user(), $surat->position_id)) abort(403);

    //     // 1. Status surat langsung didisposisi
    //     $surat->update(['status' => 'didisposisi']);

    //     // 2. Pengirim disposisi = pembuat surat
    //     $pengirimId = $surat->created_by;

    //     // Tambahkan catatan otomatis
    //     $finalCatatan = $request->catatan
    //         . "\n\n✓ Sudah divalidasi oleh Manager (" . Auth::user()->name . ")";

    //     // 3. Generate nomor disposisi
    //     $bulan = date('m');
    //     $tahun = date('Y');

    //     $last = \App\Models\Disposisi::whereMonth('created_at', $bulan)
    //         ->whereYear('created_at', $tahun)
    //         ->latest('id')
    //         ->first();

    //     if ($last) {
    //         $parts      = explode('-', $last->no_disposisi);
    //         $lastNumber = (int) end($parts);
    //     } else {
    //         $lastNumber = 0;
    //     }

    //     $nextNumber   = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    //     $noDisposisi  = "DISP-{$tahun}-{$bulan}-{$nextNumber}";

    //     // 4. Buat disposisi
    //     $disposisi = \App\Models\Disposisi::create([
    //         'no_disposisi'     => $noDisposisi,
    //         'surat_id'         => $surat->id,
    //         'pengirim_id'      => $pengirimId,
    //         'catatan'          => $finalCatatan,
    //         'jenis_disposisi'  => $request->jenis_disposisi,
    //         'status'           => 'Dibuat',
    //     ]);

    //     // Ambil nama pembuat surat (optional, kalau relasi creator belum ada)
    //     $pembuat = \App\Models\User::find($surat->created_by);

    //     /* =======================================================
    //     * 5. NOTIF KE DIREKTUR UTAMA
    //     * ======================================================= */
    //     $direktur = \App\Models\User::where('role_name', 'direktur_utama')->first();

    //     if ($direktur && $direktur->phone_number) {
    //         $message = "📨 *DISPOSISI BARU (DARI SURAT MASUK)*\n\n"
    //             . "No Disposisi : {$disposisi->no_disposisi}\n"
    //             . "No Surat     : {$surat->no_surat}\n"
    //             . "Perihal      : {$surat->perihal}\n"
    //             . "Asal Surat   : {$surat->asal_surat}\n"
    //             . "Pembuat Surat: " . ($pembuat->name ?? '-') . "\n\n"
    //             . "Jenis        : {$disposisi->jenis_disposisi}\n"
    //             . "Catatan      : {$finalCatatan}\n\n"
    //             . "Silakan isi instruksi pada aplikasi.\n"
    //             . "https://aksi.rsu-darulistiqomah.com";

    //         \App\Helper\WppHelper::sendMessage($direktur->phone_number, $message);
    //     }

    //     /* =======================================================
    //  * 6. NOTIF INFO KE KESEKRETARIATAN
    //  * ======================================================= */
    //     $kesras = \App\Models\User::where('role_name', 'kesekretariatan')->get();

    //     foreach ($kesras as $ks) {
    //         if (!$ks->phone_number) continue;

    //         $msgKesra = "ℹ️ *INFO DISPOSISI BARU (VALIDASI MANAGER)*\n\n"
    //             . "No Disposisi : {$disposisi->no_disposisi}\n"
    //             . "No Surat     : {$surat->no_surat}\n"
    //             . "Perihal      : {$surat->perihal}\n"
    //             . "Asal Surat   : {$surat->asal_surat}\n\n"
    //             . "Status Surat : sudah *didisposisi langsung ke Direktur*.\n"
    //             . "Validator    : " . Auth::user()->name . "\n\n"
    //             . "Catatan Manager:\n{$finalCatatan}\n\n"
    //             . "Ini hanya notifikasi informasi.\n"
    //             . "https://aksi.rsu-darulistiqomah.com";

    //         \App\Helper\WppHelper::sendMessage($ks->phone_number, $msgKesra);
    //     }

    //     \RealRashid\SweetAlert\Facades\Alert::success(
    //         'Berhasil!',
    //         'Surat divalidasi, disposisi dibuat, dan notifikasi dikirim.'
    //     );

    //     return redirect()->route('surat_masuk.index');
    // }

    public function validasiPopup(Request $request)
    {
        $request->validate([
            'surat_id'        => 'required|integer|exists:surat_masuks,id',
            'jenis_disposisi' => 'required|in:biasa,penting,rahasia',
            'catatan'         => 'required|string'
        ]);

        // Hanya manager level 3
        if (Auth::user()->getLevel() !== 3) abort(403);

        /**
         * =========================================================
         * 1) TRANSAKSI + LOCK: cegah double create (double click)
         * =========================================================
         */
        $tx = DB::transaction(function () use ($request) {

            // Lock surat row agar request paralel tidak bisa lewat bareng
            $surat = SuratMasuk::where('id', $request->surat_id)
                ->lockForUpdate()
                ->firstOrFail();

            // validasi bawahan
            if (!$this->isBawahan(Auth::user(), $surat->position_id)) abort(403);

            // Jika disposisi sudah ada untuk surat ini -> STOP create (anti double)
            $existing = Disposisi::where('surat_id', $surat->id)->first();
            if ($existing) {
                return [
                    'created'   => false,
                    'disposisi' => $existing,
                    'surat'     => $surat,
                    'finalCatatan' => $existing->catatan,
                ];
            }

            // Status surat langsung didisposisi
            $surat->update(['status' => 'didisposisi']);

            // Pengirim disposisi = pembuat surat
            $pengirimId = $surat->created_by;

            // Tambahkan catatan otomatis
            $finalCatatan = $request->catatan
                . "\n\n✓ Sudah divalidasi oleh Manager (" . Auth::user()->name . ")";

            // Generate nomor disposisi (safe)
            $bulan = date('m');
            $tahun = date('Y');

            // Lock query "last row" bulan ini agar nomor tidak bentrok saat paralel
            $last = Disposisi::whereMonth('created_at', $bulan)
                ->whereYear('created_at', $tahun)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $lastNumber = 0;
            if ($last) {
                $parts = explode('-', $last->no_disposisi);
                $lastNumber = (int) end($parts);
            }

            $nextNumber  = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $noDisposisi = "DISP-{$tahun}-{$bulan}-{$nextNumber}";

            // Buat disposisi
            $disposisi = Disposisi::create([
                'no_disposisi'     => $noDisposisi,
                'surat_id'         => $surat->id,
                'pengirim_id'      => $pengirimId,
                'catatan'          => $finalCatatan,
                'jenis_disposisi'  => $request->jenis_disposisi,
                'status'           => 'Dibuat',
            ]);

            return [
                'created'   => true,
                'disposisi' => $disposisi,
                'surat'     => $surat,
                'finalCatatan' => $finalCatatan,
            ];
        });

        /**
         * =========================================================
         * 2) KIRIM WA DI LUAR TRANSAKSI (biar DB sudah aman dulu)
         * =========================================================
         */
        $disposisi    = $tx['disposisi'];
        $surat        = $tx['surat'];
        $finalCatatan = $tx['finalCatatan'];

        // Ambil nama pembuat surat
        $pembuat = User::find($surat->created_by);

        /* =======================================================
     * NOTIF KE DIREKTUR UTAMA
     * ======================================================= */
        $direktur = User::where('role_name', 'direktur_utama')->first();

        if ($direktur && $direktur->phone_number) {
            $message = "📨 *DISPOSISI BARU (DARI SURAT MASUK)*\n\n"
                . "No Disposisi : {$disposisi->no_disposisi}\n"
                . "No Surat     : {$surat->no_surat}\n"
                . "Perihal      : {$surat->perihal}\n"
                . "Asal Surat   : {$surat->asal_surat}\n"
                . "Pembuat Surat: " . ($pembuat->name ?? '-') . "\n\n"
                . "Jenis        : {$disposisi->jenis_disposisi}\n"
                . "Catatan      : {$finalCatatan}\n\n"
                . "Silakan isi instruksi pada aplikasi.\n"
                . "https://aksi.rsu-darulistiqomah.com";

            \App\Helper\WppHelper::sendMessage($direktur->phone_number, $message);
        }

        /* =======================================================
     * NOTIF INFO KE KESEKRETARIATAN
     * ======================================================= */
        $kesras = User::where('role_name', 'kesekretariatan')->get();

        foreach ($kesras as $ks) {
            if (!$ks->phone_number) continue;

            $msgKesra = "ℹ️ *INFO DISPOSISI BARU (VALIDASI MANAGER)*\n\n"
                . "No Disposisi : {$disposisi->no_disposisi}\n"
                . "No Surat     : {$surat->no_surat}\n"
                . "Perihal      : {$surat->perihal}\n"
                . "Asal Surat   : {$surat->asal_surat}\n\n"
                . "Status Surat : sudah *didisposisi langsung ke Direktur*.\n"
                . "Validator    : " . Auth::user()->name . "\n\n"
                . "Catatan Manager:\n{$finalCatatan}\n\n"
                . "Ini hanya notifikasi informasi.\n"
                . "https://aksi.rsu-darulistiqomah.com";

            \App\Helper\WppHelper::sendMessage($ks->phone_number, $msgKesra);
        }

        Alert::success(
            'Berhasil!',
            $tx['created']
                ? 'Surat divalidasi, disposisi dibuat, dan notifikasi dikirim.'
                : 'Disposisi sudah pernah dibuat (klik dobel terdeteksi). Notifikasi tetap dikirim.'
        );

        return redirect()->route('surat_masuk.index');
    }



    public function tolak($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        if (Auth::user()->getLevel() !== 3) abort(403);
        if (!$this->isBawahan(Auth::user(), $surat->position_id)) abort(403);

        $surat->update(['status' => 'ditolak_manager']);

        /* ============================
     * NOTIFIKASI KE PENGINPUT SURAT
     * ============================ */
        $creator = User::find($surat->created_by);

        if ($creator && $creator->phone_number) {
            $msg = "❌ *Surat Anda Ditolak Manager*\n\n"
                . "Nomor: {$surat->no_surat}\n"
                . "Perihal: {$surat->perihal}\n"
                . "Asal Surat: {$surat->asal_surat}\n"
                . "Ditolak Oleh: " . Auth::user()->name . "\n\n"
                . "Silakan cek kembali dan perbaiki jika diperlukan.\n"
                . "https://aksi.rsu-darulistiqomah.com";

            WppHelper::sendMessage($creator->phone_number, $msg);
        }

        Alert::success('Ditolak', 'Surat telah ditolak.');
        return back();
    }


    public function editKetik($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $internal = SuratInternalDoc::where('surat_id', $id)
            ->where('is_active', true)
            ->first();

        if (!$internal) {
            Alert::error('Perhatian', 'Surat ini bukan dibuat dengan mode ketik.');
            return back();
        }

        $templates = Template::orderBy('nama_template')->get();

        return view('surat_masuk.edit', compact('surat', 'internal', 'templates'));
    }

    public function updateKetik(Request $request, $id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $request->validate([
            'editor_text'  => 'required|string',
            'lampiran_pdf' => 'nullable|mimes:pdf|max:20480',
        ]);

        $old = SuratInternalDoc::where('surat_id', $id)
            ->where('is_active', true)
            ->first();

        if (!$old) {
            Alert::error('Error', 'Tidak ada versi dokumen aktif.');
            return back();
        }

        // Nonaktifkan versi lama
        $old->update(['is_active' => false]);
        $newVersion = $old->version + 1;

        /* ============================
     * 1. CLEAN HTML EDITOR
     * ============================ */
        $clean = $request->editor_text;

        // Hapus wrapper page
        $clean = preg_replace('/<div class="page">/i', '', $clean);
        $clean = preg_replace('/<\/div>\s*$/i', '', $clean);

        // Hapus boundary
        $clean = preg_replace('/<div class="boundary-overlay"[\s\S]*?<\/div>/i', '', $clean);

        // Hapus style Word
        $clean = preg_replace('/margin-left:\s*[0-9\.]+in;/i', 'margin-left:40px;', $clean);
        $clean = preg_replace('/padding-left:\s*[0-9\.]+px;/i', 'margin-left:40px;', $clean);

        // Normalkan paragraf
        $clean = preg_replace('/<p([^>]*)>/i', '<p style="margin:0 0 10px; text-align:justify;">', $clean);


        /* ============================
     * 2. CSS KHUSUS DOMPDF (SAMA DENGAN STORE)
     * ============================ */
        $pdfHtml = "
        <style>
            @page { margin: 25mm; }
            body {
                font-family: 'Times New Roman', serif;
                font-size: 12pt;
                line-height: 1.5;
                text-align: justify;
            }
            p { margin: 0 0 10px; }
            span {
                font-family: 'Times New Roman' !important;
                font-size: 12pt !important;
            }
        </style>
    " . $clean;

        // Generate PDF
        $pdf = Pdf::loadHTML($pdfHtml)->setPaper('a4', 'portrait');

        $pdfName = "surat_edit_" . time() . ".pdf";
        Storage::disk('public')->put("surat_masuk/$pdfName", $pdf->output());
        $pdfRelative = "surat_masuk/$pdfName";

        /* ============================
     * 3. Lampiran
     * ============================ */
        $lampiranPath = $old->lampiran_pdf;
        if ($request->hasFile('lampiran_pdf')) {
            $lampiranPath = $request->file('lampiran_pdf')->store('lampiran', 'public');
        }

        /* ============================
     * 4. Simpan versi baru
     * ============================ */
        SuratInternalDoc::create([
            'surat_id'      => $surat->id,
            'template_id'   => $old->template_id,
            'data_isian'    => $clean,
            'file_docx'     => null,
            'file_pdf'      => $pdfRelative,
            'lampiran_pdf'  => $lampiranPath,
            'version'       => $newVersion,
            'is_active'     => true,
        ]);

        // Update surat utama
        $surat->update(['file_pdf' => $pdfRelative]);

        Alert::success('Berhasil', 'Surat berhasil diperbarui.');
        return redirect()->route('surat_masuk.index');
    }
}
