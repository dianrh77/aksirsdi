<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helper\WppHelper;
use App\Models\Disposisi;
use Illuminate\Http\Request;
use App\Models\DisposisiFeedback;
use App\Models\DisposisiPenerima;
use App\Models\FeedbackAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class DisposisiMasukController extends Controller
{

    private function getAllSubordinatePositions($position)
    {
        $ids = collect([$position->id]);

        if ($position->children && $position->children->count()) {
            foreach ($position->children as $child) {
                $ids = $ids->merge($this->getAllSubordinatePositions($child));
            }
        }

        return $ids->unique()->values();
    }


    public function index()
    {
        $userId = Auth::id();

        $belumDibaca = DisposisiPenerima::with(['disposisi.suratMasuk', 'disposisi.pengirim'])
            ->where('penerima_id', $userId)
            ->where('status', 'Belum Dibaca')
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'disposisi_no' => $d->disposisi->no_disposisi ?? '-',
                    'tanggal_disposisi' => optional($d->disposisi)->tanggal_disposisi
                        ? date('d M Y', strtotime($d->disposisi->tanggal_disposisi))
                        : '-',
                    'perihal' => optional($d->disposisi->suratMasuk)->perihal
                        ? optional($d->disposisi->suratMasuk)->perihal .
                        ' (' . (optional($d->disposisi->suratMasuk)->asal_surat ?? '-') . ')'
                        : '-',
                    'catatan' => $d->disposisi->catatan ?? '-',
                    'jenis_disposisi' => $d->disposisi->jenis_disposisi ?? '-',
                    'status' => $d->status,
                ];
            });

        $diproses = DisposisiPenerima::with(['disposisi.suratMasuk', 'disposisi.pengirim'])
            ->where('penerima_id', $userId)
            ->where('status', 'Diproses')
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'disposisi_no' => $d->disposisi->no_disposisi ?? '-',
                    'tanggal_disposisi' => optional($d->disposisi)->tanggal_disposisi
                        ? date('d M Y', strtotime($d->disposisi->tanggal_disposisi))
                        : '-',
                    'perihal' => optional($d->disposisi->suratMasuk)->perihal
                        ? optional($d->disposisi->suratMasuk)->perihal .
                        ' (' . (optional($d->disposisi->suratMasuk)->asal_surat ?? '-') . ')'
                        : '-',
                    'catatan' => $d->disposisi->catatan,
                    'jenis_disposisi' => $d->disposisi->jenis_disposisi ?? '-',
                    'status' => $d->status,
                ];
            });

        // selesai bulan ini (masih tampil di aktif)
        $selesai = DisposisiPenerima::with(['disposisi.suratMasuk', 'disposisi.pengirim'])
            ->where('penerima_id', $userId)
            ->where('status', 'Selesai')
            ->whereMonth('waktu_selesai', now()->month)
            ->whereYear('waktu_selesai', now()->year)
            ->orderByDesc(
                Disposisi::select('tanggal_disposisi')
                    ->whereColumn('disposisis.id', 'disposisi_penerimas.disposisi_id')
            )
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'disposisi_no' => $d->disposisi->no_disposisi ?? '-',
                'tanggal_disposisi' => optional($d->disposisi)->tanggal_disposisi
                    ? date('d M Y', strtotime($d->disposisi->tanggal_disposisi))
                    : '-',
                'perihal' => optional($d->disposisi->suratMasuk)->perihal
                    ? optional($d->disposisi->suratMasuk)->perihal .
                    ' (' . (optional($d->disposisi->suratMasuk)->asal_surat ?? '-') . ')'
                    : '-',
                'catatan' => $d->disposisi->catatan ?? '-',
                'jenis_disposisi' => $d->disposisi->jenis_disposisi ?? '-',
                'status' => $d->status,
            ]);


        // arsip: selesai sebelum bulan ini
        $arsip = DisposisiPenerima::with(['disposisi.suratMasuk', 'disposisi.pengirim'])
            ->where('penerima_id', $userId)
            ->where('status', 'Selesai')
            ->where('waktu_selesai', '<', now()->startOfMonth())
            ->orderByDesc('waktu_selesai')
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'disposisi_no' => $d->disposisi->no_disposisi ?? '-',
                'tanggal_selesai' => $d->waktu_selesai ? date('d M Y', strtotime($d->waktu_selesai)) : '-',
                'tanggal_disposisi' => optional($d->disposisi)->tanggal_disposisi
                    ? date('d M Y', strtotime($d->disposisi->tanggal_disposisi))
                    : '-',
                'perihal' => optional($d->disposisi->suratMasuk)->perihal
                    ? optional($d->disposisi->suratMasuk)->perihal .
                    ' (' . (optional($d->disposisi->suratMasuk)->asal_surat ?? '-') . ')'
                    : '-',
                'catatan' => $d->disposisi->catatan ?? '-',
                'jenis_disposisi' => $d->disposisi->jenis_disposisi ?? '-',
                'status' => $d->status,
            ]);

        return view('disposisi_masuk.index', compact('belumDibaca', 'diproses', 'selesai', 'arsip'));
    }


    public function updateStatus($id, Request $request)
    {
        $data = DisposisiPenerima::findOrFail($id);
        $data->status = $request->status;
        if ($request->status === 'Dibaca') $data->waktu_baca = now();
        if ($request->status === 'Ditindaklanjuti') $data->waktu_tindak = now();
        $data->save();

        return response()->json(['success' => true]);
    }

    private function getDirectChildPositions($position)
    {
        return $position->children->pluck('id');
    }


    // public function show($id)
    // {
    //     $penerima = DisposisiPenerima::with([
    //         'disposisi.suratMasuk',
    //         'disposisi.pengirim',
    //         'disposisi.penerima',
    //         'disposisi.instruksis'
    //     ])->findOrFail($id);

    //     // Update status → Belum Dibaca → Diproses
    //     if ($penerima->status === 'Belum Dibaca') {
    //         $penerima->update([
    //             'status' => 'Diproses',
    //             'waktu_baca' => now(),
    //         ]);
    //     }

    //     // Ambil semua feedback untuk disposisi ini
    //     $riwayatFeedback = \App\Models\DisposisiFeedback::with('user')
    //         ->whereHas('penerima', function ($q) use ($penerima) {
    //             $q->where('disposisi_id', $penerima->disposisi_id);
    //         })
    //         ->orderBy('created_at', 'asc')
    //         ->get();

    //     // ==============================
    //     // LOGIKA TERUSKAN KE BAWAHAN
    //     // ==============================

    //     $loginUser = auth()->user();
    //     $loginPosition = $loginUser->primaryPosition();

    //     // Default : tidak bisa teruskan
    //     $subordinateUsers = collect();

    //     // Hanya Manager yang boleh meneruskan
    //     if ($loginPosition && $loginPosition->is_manager) {

    //         // Ambil posisi bawahan (recursive)
    //         $subordinatePositionIds = $this->getAllSubordinatePositions($loginPosition);

    //         // Ambil bawahan berdasarkan posisi
    //         $subordinateUsers = User::whereHas('positions', function ($q) use ($subordinatePositionIds) {
    //             $q->whereIn('positions.id', $subordinatePositionIds);
    //         })
    //             ->where('id', '!=', auth()->id())
    //             ->orderBy('name')
    //             ->get();

    //         // Hilangkan bawahan yang sudah menjadi penerima disposisi
    //         $existingReceiverIds = $penerima->disposisi->penerima->pluck('penerima_id')->toArray();

    //         $subordinateUsers = $subordinateUsers->filter(function ($u) use ($existingReceiverIds) {
    //             return !in_array($u->id, $existingReceiverIds);
    //         })->values();
    //     }

    //     return view('disposisi_masuk.show', compact(
    //         'penerima',
    //         'riwayatFeedback',
    //         'subordinateUsers'
    //     ));
    // }

    private function getAllSubordinatePositionsFromMany($positions)
    {
        $ids = collect();

        foreach ($positions as $pos) {
            if (!$pos) continue;
            $ids = $ids->merge($this->getAllSubordinatePositions($pos));
        }

        return $ids->unique()->values();
    }

    public function show($id)
    {
        // Ambil data disposisi penerima
        $penerima = DisposisiPenerima::with([
            'disposisi.suratMasuk',
            'disposisi.pengirim',
            'disposisi.penerima',
            'disposisi.instruksis'
        ])->findOrFail($id);

        // Mark as processed
        if ($penerima->status === 'Belum Dibaca') {
            $penerima->update([
                'status' => 'Diproses',
                'waktu_baca' => now(),
            ]);
        }

        // Ambil semua feedback
        $riwayatFeedback = \App\Models\DisposisiFeedback::with('user')
            ->whereHas('penerima', function ($q) use ($penerima) {
                $q->where('disposisi_id', $penerima->disposisi_id);
            })
            ->orderBy('created_at')
            ->get();

        // =======================================
        // LOGIKA TERUSKAN KE BAWAHAN
        // =======================================

        $loginUser  = auth()->user();
        $loginPos   = $loginUser->primaryPosition();
        $loginLevel = $loginUser->getLevel();

        // default tidak boleh teruskan
        $subordinateUsers = collect();

        if ($loginPos) {

            // =======================================================
            // 1️⃣ KHUSUS MANAGER KEUANGAN
            // =======================================================
            if (str_contains(strtolower($loginPos->name), 'manager keuangan')) {

                // 1) Ambil semua manager (level 3)
                $managerUsers = User::all()->filter(fn($u) => $u->getLevel() == 3)->values();

                // 2) Ambil primary position dari semua manager
                $managerPositions = $managerUsers
                    ->map(fn($u) => $u->primaryPosition())
                    ->filter()
                    ->values();

                // 3) Ambil SEMUA posisi bawahan dari SEMUA manager (rekursif sampai bawah)
                $allPosIds = $this->getAllSubordinatePositionsFromMany($managerPositions);

                // 4) Ambil semua user yang primary position-nya berada di tree itu
                $allUsersUnderManagers = User::whereHas('positions', function ($q) use ($allPosIds) {
                    $q->whereIn('positions.id', $allPosIds)
                        ->where('position_user.is_primary', 1);
                })->get();

                // 5) Gabungkan: semua manager + semua bawahan manager (rekursif)
                $subordinateUsers = $managerUsers->merge($allUsersUnderManagers)
                    ->where('id', '!=', $loginUser->id)
                    ->unique('id')
                    ->sortBy('name')
                    ->values();
            }


            // =======================================================
            // 2️⃣ MANAGER LAIN — mengikuti aturan lama
            // =======================================================
            elseif ($loginLevel == 3) {

                // Bawahan langsung 1 tingkat
                $childPosIds = $loginPos->children->pluck('id');

                $subordinateUsers = User::whereHas('positions', function ($q) use ($childPosIds) {
                    $q->whereIn('positions.id', $childPosIds)
                        ->where('position_user.is_primary', 1);
                })
                    ->where('id', '!=', $loginUser->id)
                    ->orderBy('name')
                    ->get();
            }

            // =======================================================
            // 3️⃣ Hapus user yang sudah menerima disposisi ini sebelumnya
            // =======================================================
            $existingReceiverIds = $penerima->disposisi->penerima->pluck('penerima_id')->toArray();

            $subordinateUsers = $subordinateUsers->filter(function ($u) use ($existingReceiverIds) {
                return !in_array($u->id, $existingReceiverIds);
            })->values();
        }

        return view('disposisi_masuk.show', compact(
            'penerima',
            'riwayatFeedback',
            'subordinateUsers'
        ));
    }




    // public function feedback(Request $request, $id)
    // {
    //     $validated = $request->validate([
    //         'feedback' => 'required|string',
    //         'lampiran.*' => 'nullable|file|max:5120',
    //     ]);

    //     $penerima = DisposisiPenerima::findOrFail($id);

    //     // Simpan feedback baru
    //     $feedback = DisposisiFeedback::create([
    //         'disposisi_penerima_id' => $penerima->id,
    //         'user_id' => Auth::id(),
    //         'feedback' => $validated['feedback'],
    //     ]);

    //     // ====== SIMPAN LAMPIRAN (JIKA ADA) ======
    //     if ($request->hasFile('lampiran')) {
    //         foreach ($request->file('lampiran') as $file) {

    //             // simpan file ke storage/app/public/feedback_lampiran
    //             $path = $file->store('feedback_lampiran', 'public');

    //             // simpan ke tabel feedback_attachments
    //             \App\Models\FeedbackAttachment::create([
    //                 'feedback_id' => $feedback->id,
    //                 'file_path'  => $path,
    //                 'file_name'  => $file->getClientOriginalName(),
    //             ]);
    //         }
    //     }
    //     // ========================================

    //     // Update status penerima jika perlu
    //     if ($penerima->status !== 'Diproses') {
    //         $penerima->update([
    //             'status' => 'Diproses',
    //             'waktu_tindak' => now(),
    //         ]);
    //     }

    //     Alert::info('Selesai', 'Feedback Berhasil Ditambahkan');
    //     return redirect()->back();
    // }

    public function feedback(Request $request, $id)
    {
        $validated = $request->validate([
            'feedback' => 'required|string',
            'lampiran.*' => 'nullable|file|max:20480',
        ]);

        // Ambil penerima + load relasi biar siap dipakai notif
        $penerima = DisposisiPenerima::with([
            'disposisi.suratMasuk',
            'disposisi.pengirim',
            'disposisi.penerima.penerima', // <--- penting: penerima list + user-nya
        ])->findOrFail($id);

        // Simpan feedback baru
        $feedback = DisposisiFeedback::create([
            'disposisi_penerima_id' => $penerima->id,
            'user_id' => Auth::id(),
            'feedback' => $validated['feedback'],
        ]);

        // ====== SIMPAN LAMPIRAN (JIKA ADA) ======
        if ($request->hasFile('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                $path = $file->store('feedback_lampiran', 'public');

                \App\Models\FeedbackAttachment::create([
                    'feedback_id' => $feedback->id,
                    'file_path'  => $path,
                    'file_name'  => $file->getClientOriginalName(),
                ]);
            }
        }

        // Update status penerima jika perlu
        if ($penerima->status !== 'Diproses') {
            $penerima->update([
                'status' => 'Diproses',
                'waktu_tindak' => now(),
            ]);
        }

        // =========================
        // NOTIF WA: ADA FEEDBACK / BALASAN DISPOSISI
        // =========================
        $disposisi = $penerima->disposisi;
        $pengirimBalasan = Auth::user();

        // kumpulkan target: pengirim disposisi + semua penerima disposisi
        $targets = collect();

        // 1) pengirim disposisi (User)
        if ($disposisi && $disposisi->pengirim) {
            $targets->push($disposisi->pengirim);
        }

        // 2) semua penerima disposisi (DisposisiPenerima -> User penerima)
        if ($disposisi && $disposisi->penerima) {
            $receiverUsers = $disposisi->penerima
                ->map(fn($dp) => $dp->penerima) // dp = DisposisiPenerima
                ->filter(); // buang null
            $targets = $targets->merge($receiverUsers);
        }

        // buang duplikat & buang diri sendiri
        $targets = $targets
            ->unique('id')
            ->reject(fn($u) => $u->id === $pengirimBalasan->id);

        $no   = $disposisi->no_disposisi ?? '-';
        $perihal = optional($disposisi->suratMasuk)->perihal ?? '-';
        $fromPos = optional($pengirimBalasan->primaryPosition())->name ?? '-';
        $fromName = $pengirimBalasan->name ?? '-';

        $message =
            "💬 *FEEDBACK DISPOSISI*\n\n" .
            "No. Disposisi : {$no}\n" .
            "Perihal      : {$perihal}\n" .
            "Dari         : {$fromPos} ({$fromName})\n\n" .
            "Pesan:\n{$validated['feedback']}\n\n" .
            "Silakan cek aplikasi:\n" .
            "https://aksi.rsu-darulistiqomah.com";

        foreach ($targets as $userTarget) {
            if (!empty($userTarget->phone_number)) {
                // kalau mau lebih aman, bisa dibungkus try-catch
                WppHelper::sendMessage($userTarget->phone_number, $message);
            }
        }

        Alert::toast('Feedback Berhasil Ditambahkan', 'success');
        return redirect()->back();
    }

    public function selesai($id)
    {
        $penerima = DisposisiPenerima::findOrFail($id);

        // Update status untuk penerima disposisi
        if ($penerima->status !== 'Selesai') {
            $penerima->update([
                'status' => 'Selesai',
                'waktu_selesai' => now(),
            ]);
        }

        $disposisi = $penerima->disposisi;

        // HANYA update disposisi utama jika belum selesai sebelumnya
        if ($disposisi->status !== 'Selesai') {
            $disposisi->update([
                'status' => 'Selesai',
                'finished_at' => now(),
            ]);
        }

        /* ======================================================
        *   NOTIFIKASI WA KE KESEKRETARIATAN
        * ====================================================== */

        // $kesraList = User::where('role_name', 'kesekretariatan')->get();

        // foreach ($kesraList as $ks) {
        //     if (!$ks->phone_number) continue;

        //     $msg = "🟢 *Disposisi Telah Selesai*\n\n" .
        //         "No. Disposisi: *{$disposisi->no_disposisi}*\n" .
        //         "Perihal: *{$disposisi->suratMasuk->perihal}*\n" .
        //         "Diselesaikan oleh: *" . auth()->user()->name . "*\n" .
        //         "Pada: " . now()->format('d M Y H:i') . "\n\n" .
        //         "Silakan dipantau di aplikasi.\n" .
        //         "https://aksi.rsu-darulistiqomah.com";

        //     WppHelper::sendMessage($ks->phone_number, $msg);
        // }


        Alert::info('Selesai', 'Disposisi berhasil ditandai selesai.');
        return redirect()->route('disposisi_masuk.index');
    }


    // Hanya untuk kesekretariatan (view-only)
    // public function showForKesekretariatan($id)
    // {
    //     $penerima = Disposisi::with([
    //         'suratMasuk',
    //         'pengirim',
    //         'penerima',
    //         'instruksis',
    //         'reject',
    //     ])->findOrFail($id);

    //     // dd($penerima);
    //     // Ambil semua feedback terkait disposisi ini
    //     $riwayatFeedback = DisposisiFeedback::with('user')
    //         ->whereHas('penerima', function ($q) use ($penerima) {
    //             $q->where('disposisi_id', $penerima->id);
    //         })
    //         ->orderBy('created_at', 'asc')
    //         ->get();

    //     // Hitung umur disposisi
    //     $created = $penerima->created_at;
    //     $finished = $penerima->finished_at;

    //     // Jika selesai, hitung sampai finished_at
    //     $end = ($penerima->status === 'Selesai' && $finished)
    //         ? $finished
    //         : now();

    //     // Ambil selisih lengkap
    //     $diff = $created->diff($end);

    //     // Generate format otomatis
    //     $parts = [];

    //     if ($diff->d > 0) $parts[] = $diff->d . ' hari';
    //     if ($diff->h > 0) $parts[] = $diff->h . ' jam';
    //     if ($diff->i > 0) $parts[] = $diff->i . ' menit';

    //     $umur_disposisi = empty($parts) ? '0 menit' : implode(' ', $parts);

    //     $badgeColor = $penerima->status === 'Selesai' ? 'success' : 'primary';

    //     // dd($penerima);
    //     return view('disposisi_masuk.show_kesekretariatan', compact(
    //         'penerima',
    //         'riwayatFeedback',
    //         'umur_disposisi',
    //         'badgeColor'
    //     ));
    // }
    public function showForKesekretariatan($id)
    {
        $penerima = Disposisi::with([
            'suratMasuk',
            'pengirim',
            'penerima',        // list DisposisiPenerima
            'instruksis',
            'reject',
        ])->findOrFail($id);

        $riwayatFeedback = DisposisiFeedback::with('user')
            ->whereHas('penerima', function ($q) use ($penerima) {
                $q->where('disposisi_id', $penerima->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // ===== umur disposisi (punyamu) =====
        $created = $penerima->created_at;
        $finished = $penerima->finished_at;
        $end = ($penerima->status === 'Selesai' && $finished) ? $finished : now();
        $diff = $created->diff($end);

        $parts = [];
        if ($diff->d > 0) $parts[] = $diff->d . ' hari';
        if ($diff->h > 0) $parts[] = $diff->h . ' jam';
        if ($diff->i > 0) $parts[] = $diff->i . ' menit';
        $umur_disposisi = empty($parts) ? '0 menit' : implode(' ', $parts);

        $badgeColor = $penerima->status === 'Selesai' ? 'success' : 'primary';

        // =========================
        // DIREKSI: BOLEH TERUSKAN KE SIAPA SAJA
        // =========================
        $loginUser = auth()->user();

        // siapa saja yang sudah pernah jadi penerima disposisi ini
        $existingReceiverIds = $penerima->penerima->pluck('penerima_id')->toArray();

        // ambil semua user, kecuali diri sendiri, dan kecuali yang sudah penerima
        $subordinateUsers = User::query()
            ->where('id', '!=', $loginUser->id)
            ->whereNotIn('id', $existingReceiverIds)
            ->orderBy('name')
            ->get();

        return view('disposisi_masuk.show_kesekretariatan', compact(
            'penerima',
            'riwayatFeedback',
            'umur_disposisi',
            'badgeColor',
            'subordinateUsers'
        ));
    }


    public function teruskan(Request $request, $id)
    {
        $loginUser = auth()->user();
        $loginPos  = $loginUser->primaryPosition();

        // ❗ Blokir jika bukan Manager
        if (!$loginPos || !$loginPos->is_manager) {
            Alert::error('Akses Ditolak', 'Anda tidak berhak meneruskan disposisi.');
            return back();
        }

        // Validasi input
        $validated = $request->validate([
            'penerima_ids' => 'required|array|min:1',
            'catatan_teruskan' => 'nullable|string|max:500'
        ]);

        $penerima  = DisposisiPenerima::findOrFail($id);
        $disposisi = $penerima->disposisi;

        $success     = [];
        $duplicate   = [];

        foreach ($validated['penerima_ids'] as $uid) {

            // ❗ Cek apakah user ini sudah menjadi penerima sebelumnya
            $alreadyExists = DisposisiPenerima::where('disposisi_id', $disposisi->id)
                ->where('penerima_id', $uid)
                ->exists();

            if ($alreadyExists) {
                $duplicate[] = $uid;
                continue;
            }

            // Tambahkan penerima baru
            DisposisiPenerima::create([
                'disposisi_id' => $disposisi->id,
                'penerima_id'  => $uid,
                'status'       => 'Belum Dibaca',
            ]);

            $success[] = $uid;

            // ===================================================
            // 🔔 NOTIFIKASI WA UNTUK PENERIMA BARU
            // ===================================================
            $user = User::find($uid);

            if ($user && $user->phone_number) {

                $msg = "📨 *AKSI RSDI - DISPOSISI BARU*\n\n" .
                    "No. Disposisi: *{$disposisi->no_disposisi}*\n" .
                    "Perihal: *{$disposisi->suratMasuk->perihal}*\n" .
                    "Diteruskan oleh: *" . auth()->user()->name . "*\n\n" .
                    "Silakan buka aplikasi untuk melihat dan menindaklanjuti:\n" .
                    "https://aksi.rsu-darulistiqomah.com";

                WppHelper::sendMessage($user->phone_number, $msg);
            }
        }

        // ======================================================
        // 🆕 AUTO FEEDBACK (JABATAN SEMUA — penerima & pengirim)
        // ======================================================
        if (count($success) > 0) {

            // Ambil JABATAN penerima baru
            $positionNames = User::whereIn('id', $success)
                ->get()
                ->map(function ($u) {
                    return optional($u->primaryPosition())->name;
                })
                ->filter()
                ->implode(', ');

            $from = optional($loginUser->primaryPosition())->name ?? $loginUser->name;
            $to   = $positionNames ?: '-';
            $note = trim($validated['catatan_teruskan'] ?? '');

            $feedbackText = "➡️ Disposisi Diteruskan: {$from} , kepada ➡️ {$to}";
            if ($note) $feedbackText .= "\n📝 Catatan: {$note}";

            DisposisiFeedback::create([
                'disposisi_penerima_id' => $penerima->id,
                'user_id'               => $loginUser->id,
                'feedback'              => $feedbackText,
            ]);
        }


        // ====================== ALERT UI =======================
        if (count($success) > 0 && count($duplicate) == 0) {
            Alert::success('Berhasil', 'Disposisi berhasil diteruskan kepada bawahan.');
        }

        if (count($success) > 0 && count($duplicate) > 0) {
            Alert::info(
                'Sebagian Berhasil',
                "Disposisi berhasil diteruskan ke " . count($success) . " bawahan. 
            " . count($duplicate) . " penerima sudah ada sebelumnya."
            );
        }

        if (count($success) == 0 && count($duplicate) > 0) {
            Alert::warning(
                'Tidak Ada Perubahan',
                'Semua bawahan yang Anda pilih sudah pernah menerima disposisi ini.'
            );
        }

        return back();
    }


    public function hapusLampiran($id)
    {
        $lamp = FeedbackAttachment::findOrFail($id);

        // Cegah hapus jika bukan pemilik
        if ($lamp->feedback->user_id != auth()->id()) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus lampiran ini.');
        }

        // Hapus file di storage
        if (Storage::exists($lamp->file_path)) {
            Storage::delete($lamp->file_path);
        }

        // Hapus database
        $lamp->delete();

        Alert::success('Berhasil', 'Lampiran berhasil dihapus.');
        return back();
    }

    public function print($id)
    {
        // Ambil data disposisi penerima (sama seperti show)
        $penerima = DisposisiPenerima::with([
            'disposisi.suratMasuk.internalDoc',
            'disposisi.pengirim',
            'disposisi.penerima.penerima.positions', // biar nama jabatan aman
            'disposisi.instruksis',
        ])->findOrFail($id);

        // Ambil semua feedback (SAMA PERSIS seperti show) + lampiran
        $riwayatFeedback = \App\Models\DisposisiFeedback::with(['user.positions', 'lampiran'])
            ->whereHas('penerima', function ($q) use ($penerima) {
                $q->where('disposisi_id', $penerima->disposisi_id);
            })
            ->orderBy('created_at')
            ->get();

        return view('disposisi_masuk.print', compact('penerima', 'riwayatFeedback'));
    }

    public function teruskanFromDisposisi(Request $request, $disposisiId)
    {
        $validated = $request->validate([
            'penerima_ids' => 'required|array|min:1',
            'penerima_ids.*' => 'integer|exists:users,id',
            'catatan_teruskan' => 'nullable|string|max:500'
        ]);

        $loginUser = auth()->user();

        // (opsional) batasi hanya role tertentu
        // if (!in_array($loginUser->role_name, ['direktur_utama','direktur_umum','kesekretariatan'])) {
        //     Alert::error('Akses Ditolak', 'Anda tidak berhak meneruskan disposisi.');
        //     return back();
        // }

        $disposisi = Disposisi::with('suratMasuk')->findOrFail($disposisiId);

        $success = [];
        $duplicate = [];

        foreach ($validated['penerima_ids'] as $uid) {
            $alreadyExists = DisposisiPenerima::where('disposisi_id', $disposisi->id)
                ->where('penerima_id', $uid)
                ->exists();

            if ($alreadyExists) {
                $duplicate[] = $uid;
                continue;
            }

            DisposisiPenerima::create([
                'disposisi_id' => $disposisi->id,
                'penerima_id'  => $uid,
                'status'       => 'Belum Dibaca',
            ]);

            $success[] = $uid;

            // notif WA (sekarang DRYRUN → masuk log)
            $user = User::find($uid);
            if ($user && $user->phone_number) {
                $msg = "📨 *DISPOSISI BARU*\n\n" .
                    "No. Disposisi: *{$disposisi->no_disposisi}*\n" .
                    "Perihal: *" . (optional($disposisi->suratMasuk)->perihal ?? '-') . "*\n" .
                    "Diteruskan oleh: *{$loginUser->name}*\n\n" .
                    "Silakan cek aplikasi:\nhttps://aksi.rsu-darulistiqomah.com";

                WppHelper::sendMessage($user->phone_number, $msg);
            }
        }

        // Auto feedback agar ada jejak (pakai anchor penerima pertama)
        if (count($success) > 0) {
            $anchorPenerima = DisposisiPenerima::where('disposisi_id', $disposisi->id)
                ->orderBy('id')
                ->first();

            $toNames = User::whereIn('id', $success)
                ->get()
                ->map(fn($u) => (optional($u->primaryPosition())->name ?? '-') . " ({$u->name})")
                ->implode(', ');

            $from = optional($loginUser->primaryPosition())->name ?? $loginUser->name;
            $note = trim($validated['catatan_teruskan'] ?? '');

            $feedbackText = "➡️ Disposisi Diteruskan oleh: {$from}\n➡️ Kepada: {$toNames}";
            if ($note) $feedbackText .= "\n📝 Catatan: {$note}";

            if ($anchorPenerima) {
                DisposisiFeedback::create([
                    'disposisi_penerima_id' => $anchorPenerima->id,
                    'user_id'               => $loginUser->id,
                    'feedback'              => $feedbackText,
                ]);
            }
        }

        if (count($success) > 0 && count($duplicate) == 0) {
            Alert::success('Berhasil', 'Disposisi berhasil diteruskan.');
        } elseif (count($success) > 0) {
            Alert::info('Sebagian Berhasil', 'Sebagian diteruskan, sebagian sudah pernah menerima.');
        } else {
            Alert::warning('Tidak Ada Perubahan', 'Semua user yang dipilih sudah pernah menerima disposisi ini.');
        }

        return back();
    }
}
