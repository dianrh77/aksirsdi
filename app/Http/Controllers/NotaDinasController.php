<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Helper\WppHelper;
use App\Models\NotaDinas;
use Illuminate\Http\Request;
use App\Models\NotaDinasBalasan;
use App\Models\NotaDinasFeedback;
use App\Models\NotaDinasPenerima;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class NotaDinasController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $notaBaru = NotaDinas::with(['pengirim', 'penerimas.user'])
            ->where('pengirim_id', $userId)
            ->whereIn('status', ['baru', 'menunggu_validasi'])
            ->latest()
            ->get();

        $notaSelesai = NotaDinas::with(['pengirim', 'penerimas.user'])
            ->where('pengirim_id', $userId)
            ->where('status', 'selesai')
            ->latest()
            ->get();

        $notaReject = NotaDinas::with(['pengirim', 'penerimas.user'])
            ->where('pengirim_id', $userId)
            ->where('status', 'rejected')
            ->latest()
            ->get();

        return view('nota.index', compact('notaBaru', 'notaSelesai', 'notaReject'));
    }

    public function printView($id)
    {
        $nota = NotaDinas::with(['pengirim', 'penerima'])->findOrFail($id);

        $feedback = NotaDinasFeedback::with('user')
            ->where('nota_dinas_id', $nota->id)
            ->orderBy('created_at')
            ->get();

        // cari validator (manager)
        $validator = $nota->penerima
            ->first(function ($u) {
                return $u->pivot->status === 'selesai' || $u->pivot->status === 'validasi';
            });

        // $validatorName = optional($validator?->primaryPosition())->name
        //     ?? optional($validator)->name
        //     ?? '-';

        // $validatedAt = optional($validator?->pivot?->updated_at)
        //     ? \Carbon\Carbon::parse($validator->pivot->updated_at)->format('d M Y H:i')
        //     : '-';


        return view('nota.print', compact(
            'nota',
            'feedback',
            // 'validatorName',
            // 'validatedAt'
        ));
    }



    public function create()
    {
        $users = User::where('id', '!=', Auth::id())
            ->whereNotIn('role_name', ['direktur_utama', 'direktur_umum'])
            ->orderBy('name')
            ->get();

        $pengirimNama    = Auth::user()->name;
        $pengirimJabatan = Auth::user()->primaryPosition()->name ?? '-';
        $tanggal         = Carbon::now()->translatedFormat('d F Y');

        return view('nota.create', compact('users', 'pengirimNama', 'pengirimJabatan', 'tanggal'));
    }

    private function findNearestManagerPosition($position)
    {
        while ($position && $position->parent) {
            $position = $position->parent;

            // Jika level parent ini adalah Manager (level 3)
            if ($position->getLevel() == 3) {
                return $position;
            }
        }

        return null;
    }

    private function generateNomorNota()
    {
        $today = date('Y-m-d');

        $countToday = NotaDinas::whereDate('created_at', $today)->count() + 1;

        return 'ND-' . date('Ymd') . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'penerima_ids'   => 'required|array|min:1',
                'penerima_ids.*' => 'integer|exists:users,id',
                'judul'          => 'required|string',
                'isi'            => 'required|string',
                'lampiran'       => 'nullable|mimes:pdf,jpg,jpeg,png|max:20480',
                // lampiran lain (baru)
                'lampiran_lain'  => 'nullable|file|max:20480',

            ]);

            $user        = Auth::user();
            $userLevel   = $user->getLevel();
            $penerimaIds = $validated['penerima_ids'];

            $managerId = null;

            /*
        |--------------------------------------------------------------------------
        | STAFF → WAJIB VALIDASI MANAGER
        |--------------------------------------------------------------------------
        */
            if ($userLevel > 3) {

                $posisiUser = $user->primaryPosition();
                $managerPos = $this->findNearestManagerPosition($posisiUser);

                if ($managerPos) {
                    $manager = User::whereHas('positions', function ($q) use ($managerPos) {
                        $q->where('positions.id', $managerPos->id)
                            ->where('position_user.is_primary', 1);
                    })
                        ->first();

                    if ($manager) {
                        $managerId = $manager->id;

                        // Tambahkan manager sebagai penerima pertama bila belum ada
                        if (!in_array($managerId, $penerimaIds)) {
                            array_unshift($penerimaIds, $managerId);
                        }
                    }
                }
            }

            // Hilangkan duplikat
            $penerimaIds = array_values(array_unique($penerimaIds));

            // Upload lampiran
            $lampiranPath = null;
            if ($request->hasFile('lampiran')) {
                $lampiranPath = $request->file('lampiran')->store('nota_dinas', 'public');
            }

            // Upload lampiran lain
            $lampiranLainPath = null;
            $lampiranLainNama = null;

            if ($request->hasFile('lampiran_lain')) {
                $lampiranLainNama = $request->file('lampiran_lain')->getClientOriginalName();
                $lampiranLainPath = $request->file('lampiran_lain')->store('nota_dinas_lain', 'public');
            }

            // Create MASTER nota
            $nomorNota = $this->generateNomorNota();

            $nota = NotaDinas::create([
                'pengirim_id' => $user->id,
                'nomor_nota'  => $nomorNota,
                'judul'       => $validated['judul'],
                'isi'         => $validated['isi'],
                'lampiran'    => $lampiranPath,
                // baru:
                'lampiran_lain' => $lampiranLainPath,
                'lampiran_lain_nama' => $lampiranLainNama,
                'status'      => ($userLevel > 3 ? 'menunggu_validasi' : 'baru'),
            ]);

            /*
        |--------------------------------------------------------------------------
        | SIMPAN PENERIMA
        |--------------------------------------------------------------------------
        */
            foreach ($penerimaIds as $pid) {

                // Default tipe
                $tipe   = 'langsung';
                $status = 'baru';

                if ($userLevel > 3) {  // STAFF / KASI / KARU
                    if ($pid == $managerId) {
                        // Manager → Validasi
                        $tipe   = 'validasi';
                        $status = 'baru';
                    } else {
                        // Semua penerima lain pending dulu
                        $status = 'pending_manager';
                    }
                }

                NotaDinasPenerima::create([
                    'nota_dinas_id' => $nota->id,
                    'user_id'       => $pid,
                    'tipe'          => $tipe,
                    'status'        => $status,
                ]);

                /*
            |--------------------------------------------------------------------------
            | NOTIFIKASI WA
            |--------------------------------------------------------------------------
            */

                $penerima = User::find($pid);
                if (!$penerima || !$penerima->phone_number) continue;

                /* =======================================================
            | CASE A: PENGIRIM STAFF → Notif hanya untuk MANAGER
            ======================================================== */
                if ($userLevel > 3 && $pid == $managerId) {

                    $message =
                        "🔔 *VALIDASI NOTA DINAS DIPERLUKAN*\n\n" .
                        "Nomor : {$nota->nomor_nota}\n" .
                        "Judul : {$nota->judul}\n" .
                        "Dari  : {$user->primaryPosition()->name} ({$user->name})\n\n" .
                        "Silakan validasi melalui aplikasi:\n" .
                        "https://aksi.rsu-darulistiqomah.com";

                    WppHelper::sendMessage($penerima->phone_number, $message);
                }

                /* =======================================================
            | CASE B: PENGIRIM MANAGER/DIREKTUR → Notif langsung ke semua penerima
            ======================================================== */
                if ($userLevel <= 3) {

                    $message =
                        "📄 *NOTA DINAS BARU*\n\n" .
                        "Nomor : {$nota->nomor_nota}\n" .
                        "Judul : {$nota->judul}\n" .
                        "Dari  : {$user->primaryPosition()->name} ({$user->name})\n" .
                        "Kepada: {$penerima->primaryPosition()->name} ({$penerima->name})\n\n" .
                        "Silakan cek aplikasi:\n" .
                        "https://aksi.rsu-darulistiqomah.com";

                    WppHelper::sendMessage($penerima->phone_number, $message);
                }
            }

            Alert::success('Berhasil!', 'Nota dinas berhasil dikirim.');
            return redirect()->route('nota.index');
        } catch (\Exception $e) {
            Alert::error('Gagal!', $e->getMessage());
            return back()->withInput();
        }
    }


    // public function inbox()
    // {
    //     $user   = auth()->user();
    //     $userId = $user->id;
    //     $level  = $user->getLevel();

    //     // ============================================
    //     // 1. VALIDASI MANAGER
    //     // Manager = level 3 → ambil nota validasi
    //     // Selain itu → kosong
    //     // ============================================
    //     if ($level == 3) {
    //         $notaValidasi = NotaDinasPenerima::with('nota.pengirim')
    //             ->where('user_id', $userId)
    //             ->where('tipe', 'validasi')
    //             ->whereIn('status', ['baru', 'dibaca'])
    //             ->orderByDesc('created_at')
    //             ->get();
    //     } else {
    //         $notaValidasi = collect(); // kosongkan untuk non-manager
    //     }

    //     // ============================================
    //     // 2. NOTA DITERIMA NORMAL (untuk semua user)
    //     // ============================================
    //     $notaDiterima = NotaDinasPenerima::with('nota.pengirim')
    //         ->where('user_id', $userId)
    //         ->where('tipe', '!=', 'validasi') // kecuali validasi
    //         ->whereIn('status', ['baru', 'dibaca', 'diproses'])
    //         ->orderByDesc('created_at')
    //         ->get();

    //     // ============================================
    //     // 3. NOTA SELESAI
    //     // ============================================
    //     $notaSelesai = NotaDinasPenerima::with('nota.pengirim')
    //         ->where('user_id', $userId)
    //         ->where('status', 'selesai')
    //         ->orderByDesc('created_at')
    //         ->get();

    //     return view('nota.inbox', compact(
    //         'notaValidasi',
    //         'notaDiterima',
    //         'notaSelesai',
    //         'level'
    //     ));
    // }
    public function inbox()
    {
        $user   = auth()->user();
        $userId = $user->id;
        $level  = $user->getLevel();

        // ===============================
        // 1. VALIDASI MANAGER (AKTIF)
        // ===============================
        if ($level == 3) {
            $notaValidasi = NotaDinasPenerima::with('nota.pengirim')
                ->where('user_id', $userId)
                ->where('tipe', 'validasi')
                ->whereIn('status', ['baru', 'dibaca'])
                //->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->orderByDesc('created_at')
                ->get();
        } else {
            $notaValidasi = collect();
        }

        // ===============================
        // 2. NOTA DITERIMA (AKTIF)
        // ===============================
        $notaDiterima = NotaDinasPenerima::with('nota.pengirim')
            ->where('user_id', $userId)
            ->where('tipe', '!=', 'validasi')
            ->whereIn('status', ['baru', 'dibaca', 'diproses'])
            // ->whereMonth('created_at', now()->month)
            // ->whereYear('created_at', now()->year)
            ->orderByDesc('created_at')
            ->get();

        // ===============================
        // 3. NOTA SELESAI (AKTIF BULAN INI)
        // ===============================
        $notaSelesai = NotaDinasPenerima::with('nota.pengirim')
            ->where('user_id', $userId)
            ->where('status', 'selesai')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->orderByDesc('updated_at')
            ->get();

        // ===============================
        // 4. 🗃️ ARSIP (SELESAI BULAN LALU)
        // ===============================
        $notaArsip = NotaDinasPenerima::with('nota.pengirim')
            ->where('user_id', $userId)
            ->where('status', 'selesai')
            ->where('updated_at', '<', now()->startOfMonth())
            ->orderByDesc('updated_at')
            ->get();

        return view('nota.inbox', compact(
            'notaValidasi',
            'notaDiterima',
            'notaSelesai',
            'notaArsip',
            'level'
        ));
    }



    public function reply($id)
    {
        $nota = NotaDinas::with(['pengirim', 'penerima'])->findOrFail($id);

        $feedback = NotaDinasFeedback::with('user')
            ->where('nota_dinas_id', $id)
            ->orderBy('created_at')
            ->get();

        return view('nota.reply', compact('nota', 'feedback'));
    }



    public function replyStore(Request $request, $id)
    {
        $request->validate([
            'pesan' => 'required|string',
            'lampiran.*' => 'nullable|file|max:20480'
        ]);

        $nota = NotaDinas::findOrFail($id);

        $paths = [];

        if ($request->hasFile('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                $paths[] = $file->store('nota_feedback', 'public');
            }
        }

        foreach ($paths as $path) {
            NotaDinasFeedback::create([
                'nota_dinas_id' => $nota->id,
                'user_id' => Auth::id(),
                'pesan' => $request->pesan,
                'lampiran' => $path
            ]);
        }

        if (!$paths) {
            NotaDinasFeedback::create([
                'nota_dinas_id' => $nota->id,
                'user_id' => Auth::id(),
                'pesan' => $request->pesan
            ]);
        }

        // =========================
        // NOTIF WA: ADA BALASAN
        // =========================
        $pengirimBalasan = Auth::user();

        // kumpulkan penerima notif: pengirim nota + semua penerima nota (pivot)
        $targets = collect();

        if ($nota->pengirim) {
            $targets->push($nota->pengirim);
        }

        // $nota->penerima adalah Collection User dari belongsToMany
        $targets = $targets->merge($nota->penerima);

        // buang duplikat & buang diri sendiri
        $targets = $targets
            ->unique('id')
            ->reject(fn($u) => $u->id === $pengirimBalasan->id);

        $message =
            "💬 *BALASAN NOTA DINAS*\n\n" .
            "Nomor : {$nota->nomor_nota}\n" .
            "Judul : {$nota->judul}\n" .
            "Dari  : {$pengirimBalasan->primaryPosition()->name} ({$pengirimBalasan->name})\n\n" .
            "Pesan : {$request->pesan}\n\n" .
            "Silakan cek aplikasi:\n" .
            "https://aksi.rsu-darulistiqomah.com";

        foreach ($targets as $userTarget) {
            if (!empty($userTarget->phone_number)) {
                WppHelper::sendMessage($userTarget->phone_number, $message);
            }
        }

        Alert::success('Berhasil', 'Balasan berhasil dikirim.');
        return back();
    }




    public function show($id)
    {
        try {
            $nota = NotaDinas::with(['pengirim', 'penerima'])->findOrFail($id);

            $balasan = NotaDinasBalasan::with('user')
                ->where('nota_dinas_id', $id)
                ->first();

            return view('nota.show', compact('nota', 'balasan'));
        } catch (\Exception $e) {
            Alert::error('Gagal!', $e->getMessage());
            return redirect()->route('nota.inbox');
        }
    }

    public function edit($id)
    {
        $nota = NotaDinas::findOrFail($id);
        $users = User::all(); // untuk select penerima

        // hanya nota yang belum selesai bisa diedit
        if ($nota->status !== 'baru') {
            abort(403, 'Nota sudah tidak bisa diedit.');
        }

        return view('nota.edit', compact('nota', 'users'));
    }

    public function update(Request $request, $id)
    {
        $nota = NotaDinas::with('penerima')->findOrFail($id);

        // Hanya nota yang masih menunggu (baru / menunggu_validasi)
        if (!in_array($nota->status, ['baru', 'menunggu_validasi'])) {
            abort(403, 'Nota sudah tidak bisa diedit.');
        }

        $validated = $request->validate([
            'penerima_ids'   => 'required|array|min:1',
            'penerima_ids.*' => 'integer|exists:users,id',
            'judul'          => 'required|string',
            'isi'            => 'required|string',
            'lampiran'       => 'nullable|mimes:pdf,jpg,jpeg,png|max:20480',
            'lampiran_lain'  => 'nullable|file|mimes:doc,docx,xls,xlsx,ppt,pptx,csv,zip,rar,txt,pdf,jpg,jpeg,png|max:20480',
        ]);

        $user        = Auth::user();
        $userLevel   = $user->getLevel();
        $penerimaIds = $validated['penerima_ids'];

        /*
    |--------------------------------------------------------------------------
    | 1. CARI MANAGER (kalau staf)
    |--------------------------------------------------------------------------
    */
        $managerId = null;

        if ($userLevel > 3) {
            $posisiUser = $user->primaryPosition();
            $managerPos = $this->findNearestManagerPosition($posisiUser);

            if ($managerPos) {
                $manager = User::whereHas('positions', function ($q) use ($managerPos) {
                    $q->where('positions.id', $managerPos->id)
                        ->where('position_user.is_primary', 1);
                })->first();

                if ($manager) {
                    $managerId = $manager->id;

                    // Pastikan manager ikut menjadi penerima pertama
                    if (!in_array($managerId, $penerimaIds)) {
                        array_unshift($penerimaIds, $managerId);
                    }
                }
            }
        }

        // Hilangkan duplikat
        $penerimaIds = array_values(array_unique($penerimaIds));

        /*
    |--------------------------------------------------------------------------
    | 2. UPDATE MASTER NOTA (judul, isi, lampiran)
    |--------------------------------------------------------------------------
    */
        if ($request->hasFile('lampiran')) {
            if ($nota->lampiran && Storage::disk('public')->exists($nota->lampiran)) {
                Storage::disk('public')->delete($nota->lampiran);
            }
            $nota->lampiran = $request->file('lampiran')->store('nota_dinas', 'public');
        }

        if ($request->hasFile('lampiran_lain')) {
            if ($nota->lampiran_lain && Storage::disk('public')->exists($nota->lampiran_lain)) {
                Storage::disk('public')->delete($nota->lampiran_lain);
            }

            $nota->lampiran_lain_nama = $request->file('lampiran_lain')->getClientOriginalName();
            $nota->lampiran_lain = $request->file('lampiran_lain')->store('nota_dinas_lain', 'public');
        }

        $nota->judul = $validated['judul'];
        $nota->isi   = $validated['isi'];

        // Jika staf mengedit → tetap status menunggu_validasi
        $nota->status = ($userLevel > 3 ? 'menunggu_validasi' : 'baru');
        $nota->save();

        /*
        |--------------------------------------------------------------------------
        | 3. RESET PENERIMA LAMA DAN BUAT BARU
        |--------------------------------------------------------------------------
        */
        NotaDinasPenerima::where('nota_dinas_id', $nota->id)->delete();

        foreach ($penerimaIds as $pid) {

            $tipe   = 'langsung';
            $status = 'baru';

            // Jika staf → manager harus validasi
            if ($userLevel > 3) {
                if ($pid == $managerId) {
                    $tipe   = 'validasi';
                    $status = 'baru';
                } else {
                    $status = 'pending_manager';
                }
            }

            NotaDinasPenerima::create([
                'nota_dinas_id' => $nota->id,
                'user_id'       => $pid,
                'tipe'          => $tipe,
                'status'        => $status,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | 4. NOTIFIKASI WA
    |--------------------------------------------------------------------------
    | - Staf → notif hanya untuk manager
    | - Manager/direktur → notif ke semua penerima
    |--------------------------------------------------------------------------
    */

        foreach ($penerimaIds as $pid) {

            $penerima = User::find($pid);
            if (!$penerima || !$penerima->phone_number) continue;

            if ($userLevel > 3 && $pid == $managerId) {
                // Notifikasi ke manager
                $msg =
                    "🔔 *VALIDASI NOTA DINAS DIPERLUKAN (UPDATE)*\n\n" .
                    "Nomor : {$nota->nomor_nota}\n" .
                    "Judul : {$nota->judul}\n" .
                    "Dari  : {$user->primaryPosition()->name} ({$user->name})\n\n" .
                    "Silakan validasi melalui aplikasi:\n" .
                    "https://aksi.rsu-darulistiqomah.com";

                WppHelper::sendMessage($penerima->phone_number, $msg);
            }

            if ($userLevel <= 3) {
                // Manager / Direktur kirim ke semua penerima
                $msg =
                    "📄 *NOTA DINAS DIUPDATE*\n\n" .
                    "Nomor : {$nota->nomor_nota}\n" .
                    "Judul : {$nota->judul}\n" .
                    "Dari  : {$user->primaryPosition()->name} ({$user->name})\n" .
                    "Kepada: {$penerima->primaryPosition()->name} ({$penerima->name})\n\n" .
                    "Silakan cek aplikasi:\nhttps://aksi.rsu-darulistiqomah.com";

                WppHelper::sendMessage($penerima->phone_number, $msg);
            }
        }

        Alert::toast('Nota Dinas berhasil diperbarui.', 'success');
        return redirect()->route('nota.index');
    }


    // Tampilkan halaman validasi (manager)
    public function showValidasi($penerimaId)
    {
        $penerima = NotaDinasPenerima::with('nota.pengirim')
            ->findOrFail($penerimaId);

        if ($penerima->user_id !== auth()->id() || $penerima->tipe !== 'validasi') {
            abort(403, 'Anda tidak berhak memvalidasi nota ini.');
        }

        $nota = $penerima->nota;

        // Ambil list penerima yang menunggu validasi manager
        $penerimaTujuan = NotaDinasPenerima::with('user')
            ->where('nota_dinas_id', $nota->id)
            ->where('status', 'pending_manager')
            ->orderBy('id')
            ->get();

        return view('nota.validasi', compact('nota', 'penerima', 'penerimaTujuan'));
    }


    // Approve validasi
    public function approveValidasi($penerimaId)
    {
        $penerima = NotaDinasPenerima::with('nota.pengirim')
            ->findOrFail($penerimaId);

        if ($penerima->user_id !== auth()->id() || $penerima->tipe !== 'validasi') {
            abort(403, 'Anda tidak berhak memvalidasi nota ini.');
        }

        $nota = $penerima->nota;

        // Tandai row manager sebagai selesai
        $penerima->update([
            'status'        => 'selesai',
            'waktu_selesai' => now(),
        ]);

        // Ubah semua penerima yang statusnya pending_manager → baru
        $penerimaPending = NotaDinasPenerima::where('nota_dinas_id', $nota->id)
            ->where('status', 'pending_manager')
            ->get();

        foreach ($penerimaPending as $row) {
            $row->update([
                'status'       => 'baru',
                'waktu_dibaca' => null,
            ]);

            // Kirim WA ke penerima yang sekarang aktif
            $userPenerima = User::find($row->user_id);

            if ($userPenerima && $userPenerima->phone_number) {
                $pengirim = $nota->pengirim;

                $message = "📄 *NOTA DINAS BARU*\n\n" .
                    "Nomor : {$nota->nomor_nota}\n" .
                    "Judul : {$nota->judul}\n" .
                    "Dari  : {$pengirim->primaryPosition()->name} ({$pengirim->name})\n" .
                    "Kepada: {$userPenerima->primaryPosition()->name} ({$userPenerima->name})\n\n" .
                    "Silakan cek aplikasi:\nhttps://aksi.rsu-darulistiqomah.com";

                WppHelper::sendMessage($userPenerima->phone_number, $message);
            }
        }

        // Update status master nota
        $nota->update([
            'status' => 'baru',
        ]);

        Alert::success('Berhasil', 'Nota dinas telah divalidasi dan dikirim ke penerima.');
        return redirect()->route('nota.inbox');
    }

    public function rejectValidasi(Request $request, $penerimaId)
    {
        $request->validate([
            'alasan' => 'required|string|max:1000',
        ]);

        $penerima = NotaDinasPenerima::with('nota.pengirim')->findOrFail($penerimaId);

        // hanya manager yang ditugaskan validasi
        if ($penerima->user_id !== auth()->id() || $penerima->tipe !== 'validasi') {
            abort(403, 'Anda tidak berhak mereject nota ini.');
        }

        $nota = $penerima->nota;

        // 1) tandai row validasi manager sebagai rejected
        $penerima->update([
            'status' => 'rejected',
            'waktu_selesai' => now(),
        ]);

        // 2) tandai semua penerima pending_manager jadi rejected (biar proses stop)
        NotaDinasPenerima::where('nota_dinas_id', $nota->id)
            ->where('status', 'pending_manager')
            ->update([
                'status' => 'rejected',
                'waktu_selesai' => now(),
            ]);

        // 3) update master nota
        $nota->update([
            'status' => 'rejected',
            // kalau tabel nota_dinas kamu ada kolom waktu_selesai boleh isi juga
            // 'waktu_selesai' => now(),
        ]);

        // 4) simpan alasan reject sebagai feedback (biar ada jejak di thread)
        NotaDinasFeedback::create([
            'nota_dinas_id' => $nota->id,
            'user_id'       => auth()->id(),
            'pesan'         => "❌ REJECT VALIDASI\n\nAlasan: " . $request->alasan,
        ]);

        // 5) notif WA ke pengirim nota
        $pengirim = $nota->pengirim;

        if ($pengirim && $pengirim->phone_number) {
            $manager = auth()->user();

            $message =
                "❌ *NOTA DINAS DITOLAK (REJECT)*\n\n" .
                "Nomor : {$nota->nomor_nota}\n" .
                "Judul : {$nota->judul}\n" .
                "Oleh  : {$manager->primaryPosition()->name} ({$manager->name})\n\n" .
                "Alasan:\n{$request->alasan}\n\n" .
                "Silakan perbaiki & kirim ulang via aplikasi:\n" .
                "https://aksi.rsu-darulistiqomah.com";

            WppHelper::sendMessage($pengirim->phone_number, $message);
        }

        Alert::warning('Ditolak', 'Nota dinas berhasil direject.');
        return redirect()->route('nota.inbox');
    }

    public function tandaiSelesai($id)
    {
        $nota = NotaDinas::findOrFail($id);

        // Update status penerima yang menekan tombol selesai
        NotaDinasPenerima::where('nota_dinas_id', $nota->id)
            ->where('user_id', auth()->id())
            ->update([
                'status' => 'selesai',
                'waktu_selesai' => now()
            ]);

        // Update master nota dinas
        $nota->update([
            'status' => 'selesai',
            'waktu_selesai' => now()
        ]);


        // ================================
        // 🔔 Kirim Notifikasi WA ke Pengirim
        // ================================
        $pengirim = User::find($nota->pengirim_id);

        if ($pengirim && $pengirim->phone_number) {

            $penerima = Auth::user(); // yang menyelesaikan

            $message =
                "✔️ *NOTA DINAS SELESAI*\n\n" .
                "Nomor : {$nota->nomor_nota}\n" .
                "Judul : {$nota->judul}\n" .
                "Diselesaikan oleh : {$penerima->primaryPosition()->name} ({$penerima->name})\n\n" .
                "Silakan cek aplikasi:\n" .
                "https://aksi.rsu-darulistiqomah.com";

            WppHelper::sendMessage($pengirim->phone_number, $message);
        }

        Alert::success('Selesai!', 'Nota dinas telah ditandai selesai.');
        return redirect()->route('nota.inbox');
    }


    public function hapusLampiran($id)
    {
        $file = NotaDinasFeedback::findOrFail($id);

        // hanya pemilik yang boleh hapus
        if ($file->user_id != auth()->id()) {
            abort(403, 'Anda tidak berhak menghapus lampiran');
        }

        // hapus file fisik
        if ($file->lampiran && Storage::disk('public')->exists($file->lampiran)) {
            Storage::disk('public')->delete($file->lampiran);
        }

        // hapus row
        $file->delete();

        Alert::success('Berhasil', 'Lampiran berhasil dihapus.');
        return back();
    }
}
