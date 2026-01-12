<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helper\WppHelper;
use App\Models\Disposisi;
use Illuminate\Http\Request;
use App\Models\DisposisiReject;
use App\Models\DisposisiPenerima;
use App\Models\DisposisiInstruksi;
use RealRashid\SweetAlert\Facades\Alert;

class DisposisiInstruksiController extends Controller
{
    // public function index($jenis)
    // {
    //     abort_unless(in_array($jenis, ['utama', 'umum']), 404);

    //     // // === Direktur Utama ===
    //     // if ($jenis === 'utama') {
    //     //     $disposisi = Disposisi::with(['pengirim:id,name', 'instruksis'])
    //     //         ->orderByDesc('created_at')
    //     //         ->get();
    //     // }

    //     // // === Direktur Umum ===
    //     // else {
    //     //     // hanya tampilkan disposisi yang sudah ada instruksi dari direktur utama
    //     //     $disposisi = Disposisi::with(['pengirim:id,name', 'instruksis'])
    //     //         ->whereHas('instruksis', function ($q) {
    //     //             $q->where('jenis_direktur', 'utama');
    //     //         })
    //     //         ->orderByDesc('created_at')
    //     //         ->get();
    //     // }

    //     $disposisi = Disposisi::with(['pengirim:id,name', 'instruksis'])
    //         ->orderByDesc('created_at')
    //         ->get();

    //     return view('instruksi.index', compact('disposisi', 'jenis'));
    // }


    // private function primaryPositionFromUser(?User $user)
    // {
    //     if (!$user) return null;

    //     return $user->positions
    //         ?->firstWhere('pivot.is_primary', 1)
    //         ?? $user->positions?->first();
    // }

    // /* =====================================================
    //  *  HELPER: Naik ke parent sampai ketemu manager (level 3)
    //  * ===================================================== */
    // private function findNearestManagerPosition($position)
    // {
    //     while ($position && $position->parent) {
    //         $position = $position->parent;

    //         if (method_exists($position, 'getLevel') && $position->getLevel() == 3) {
    //             return $position;
    //         }
    //     }
    //     return null;
    // }

    // /* =====================================================
    //  *  HELPER: Cari nama manager approval
    //  * ===================================================== */
    // private function findManagerNameOfUser(?User $user): string
    // {
    //     if (!$user) return '-';

    //     $pos = $this->primaryPositionFromUser($user);
    //     if (!$pos) return '-';

    //     /**
    //      * ✅ FIX UTAMA:
    //      * Kalau pembuat surat SUDAH manager,
    //      * maka manager approval = dirinya sendiri
    //      */
    //     if (method_exists($pos, 'getLevel') && $pos->getLevel() == 3) {
    //         return optional($user->primaryPosition())->name
    //             ?? $user->name
    //             ?? '-';
    //     }

    //     // naik ke parent sampai ketemu manager
    //     $mgrPos = $this->findNearestManagerPosition($pos);
    //     if (!$mgrPos) return '-';

    //     $managerUser = User::whereHas('positions', function ($q) use ($mgrPos) {
    //         $q->where('positions.id', $mgrPos->id)
    //             ->where('position_user.is_primary', 1);
    //     })->first();

    //     return optional($managerUser?->primaryPosition())->name
    //         ?? ($managerUser?->name ?? '-');
    // }

    // /* =====================================================
    //  *  INDEX – LIST DISPOSISI INSTRUKSI
    //  * ===================================================== */
    // public function index($jenis)
    // {
    //     abort_unless(in_array($jenis, ['utama', 'umum']), 404);

    //     $rows = Disposisi::with([
    //         'pengirim',
    //         'instruksis',
    //         'suratMasuk:id,asal_surat,perihal,created_by',
    //         'suratMasuk.creator:id,name',
    //         'suratMasuk.creator.positions' => function ($q) {
    //             $q->with('parent.parent.parent.parent');
    //         },
    //     ])
    //         ->select(
    //             'id',
    //             'no_disposisi',
    //             'jenis_disposisi',
    //             'pengirim_id',
    //             'finished_at',
    //             'surat_id',
    //             'status',
    //             'created_at'
    //         )
    //         ->where('status', '!=', 'Ditolak Direktur')
    //         ->orderByDesc('created_at')
    //         ->get()
    //         ->map(function ($item) use ($jenis) {

    //             /* ================= UMUR DISPOSISI ================= */
    //             $created  = $item->created_at;
    //             $finished = $item->finished_at;

    //             $end = ($item->status === 'Selesai' && $finished)
    //                 ? $finished
    //                 : now();

    //             $diff = $created->diff($end);

    //             $parts = [];
    //             if ($diff->d > 0) $parts[] = $diff->d . ' hari';
    //             if ($diff->h > 0) $parts[] = $diff->h . ' jam';
    //             if ($diff->i > 0) $parts[] = $diff->i . ' menit';

    //             $umur = empty($parts) ? '0 menit' : implode(' ', $parts);

    //             /* ================= PENGIRIM (JABATAN) ================= */
    //             $pengirimJabatan = optional($item->pengirim?->primaryPosition())->name
    //                 ?? optional($item->pengirim)->name
    //                 ?? '-';

    //             /* ================= RULE INSTRUKSI ================= */
    //             $sudahInstruksi = $item->instruksis?->contains(
    //                 fn($ins) => $ins->jenis_direktur === $jenis
    //             ) ?? false;

    //             /* ================= MANAGER APPROVAL ================= */
    //             $creator = $item->suratMasuk?->creator;
    //             $managerApproval = $this->findManagerNameOfUser($creator);

    //             return [
    //                 'id'               => $item->id,
    //                 'aksi'             => $sudahInstruksi ? 'lihat' : 'buat',

    //                 'no_disposisi'     => $item->no_disposisi ?? '-',
    //                 'asal_surat'       => $item->suratMasuk?->asal_surat ?? '-',
    //                 'perihal'          => $item->suratMasuk?->perihal ?? '-',
    //                 'jenis_disposisi'  => $item->jenis_disposisi ?? '-',

    //                 'status'           => $item->status ?? 'Menunggu',
    //                 'umur_disposisi'   => $umur,
    //                 'created_at'       => optional($item->created_at)->format('Y-m-d H:i'),

    //                 // kolom tambahan
    //                 'manager_approval' => $managerApproval,
    //                 'pengirim'         => $pengirimJabatan,
    //             ];
    //         });

    //     return view('instruksi.index', [
    //         'disposisi' => $rows,
    //         'jenis'     => $jenis,
    //     ]);
    // }
    /* =====================================================
     *  HELPER: Ambil primary position dari user
     * ===================================================== */
    private function primaryPositionFromUser(?User $user)
    {
        if (!$user) return null;

        // pastikan relasi positions sudah di-load jika perlu
        return $user->positions
            ?->firstWhere('pivot.is_primary', 1)
            ?? $user->positions?->first();
    }

    /* =====================================================
     *  HELPER: Naik ke parent sampai ketemu manager (level 3)
     * ===================================================== */
    private function findNearestManagerPosition($position)
    {
        // NOTE: kamu sebelumnya cek method getLevel() ada di Position
        while ($position && $position->parent) {
            $position = $position->parent;

            if (method_exists($position, 'getLevel') && $position->getLevel() == 3) {
                return $position;
            }
        }
        return null;
    }

    /* =====================================================
     *  HELPER: Cari nama manager approval dari pembuat surat
     * ===================================================== */
    private function findManagerNameOfUser(?User $user): string
    {
        if (!$user) return '-';

        $pos = $this->primaryPositionFromUser($user);
        if (!$pos) return '-';

        // Kalau pembuat surat sudah manager
        if (method_exists($pos, 'getLevel') && $pos->getLevel() == 3) {
            return optional($user->primaryPosition())->name
                ?? $user->name
                ?? '-';
        }

        // naik ke parent sampai ketemu manager
        $mgrPos = $this->findNearestManagerPosition($pos);
        if (!$mgrPos) return '-';

        // cari user yg primary position-nya manager tersebut
        $managerUser = User::whereHas('positions', function ($q) use ($mgrPos) {
            $q->where('positions.id', $mgrPos->id)
                ->where('position_user.is_primary', 1);
        })->first();

        return optional($managerUser?->primaryPosition())->name
            ?? ($managerUser?->name ?? '-');
    }

    /* =====================================================
     *  INDEX – LIST DISPOSISI INSTRUKSI
     * ===================================================== */
    public function index($jenis)
    {
        abort_unless(in_array($jenis, ['utama', 'umum']), 404);

        $authId = auth()->id();

        $rows = Disposisi::with([
            'pengirim',
            'instruksis',
            'penerima', // ✅ untuk cek DU sebagai penerima (relasi ke DisposisiPenerima)
            'suratMasuk:id,asal_surat,perihal,created_by',
            'suratMasuk.creator:id,name',
            'suratMasuk.creator.positions' => function ($q) {
                // tetap seperti versi kamu
                $q->with('parent.parent.parent.parent');
            },
        ])
            ->select(
                'id',
                'no_disposisi',
                'jenis_disposisi',
                'pengirim_id',
                'finished_at',
                'surat_id',
                'status',
                'created_at'
            )
            ->where('status', '!=', 'Ditolak Direktur')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) use ($jenis, $authId) {

                /* ================= UMUR DISPOSISI ================= */
                $created  = $item->created_at;
                $finished = $item->finished_at;

                $end = ($item->status === 'Selesai' && $finished)
                    ? $finished
                    : now();

                $diff = $created->diff($end);

                $parts = [];
                if ($diff->d > 0) $parts[] = $diff->d . ' hari';
                if ($diff->h > 0) $parts[] = $diff->h . ' jam';
                if ($diff->i > 0) $parts[] = $diff->i . ' menit';

                $umur = empty($parts) ? '0 menit' : implode(' ', $parts);

                /* ================= PENGIRIM (JABATAN) ================= */
                $pengirimJabatan = optional($item->pengirim?->primaryPosition())->name
                    ?? optional($item->pengirim)->name
                    ?? '-';

                /* ================= SUDAH INSTRUKSI? ================= */
                $sudahInstruksi = $item->instruksis?->contains(
                    fn($ins) => $ins->jenis_direktur === $jenis
                ) ?? false;

                /* ================= MANAGER APPROVAL ================= */
                $creator = $item->suratMasuk?->creator;
                $managerApproval = $this->findManagerNameOfUser($creator);

                /* ================= DU SEBAGAI PENERIMA? =================
                   Asumsi: $item->penerima berisi model DisposisiPenerima
                   yang punya kolom penerima_id
                */
                $duSebagaiPenerima = $item->penerima?->contains(function ($p) use ($authId) {
                    return (int)($p->penerima_id ?? 0) === (int)$authId;
                }) ?? false;

                /* ================= AKSI (untuk tab lama) ================= */
                $aksi = $sudahInstruksi ? 'lihat' : 'buat';

                /* ================= GROUP TAB KHUSUS UMUM ================= */
                $group = null;
                if ($jenis === 'umum') {
                    $group = $sudahInstruksi
                        ? 'sudah'
                        : ($duSebagaiPenerima ? 'belum_saya' : 'monitoring');
                }

                return [
                    'id'               => $item->id,
                    'aksi'             => $aksi,
                    'group'            => $group, // null untuk utama, string untuk umum

                    'no_disposisi'     => $item->no_disposisi ?? '-',
                    'asal_surat'       => $item->suratMasuk?->asal_surat ?? '-',
                    'perihal'          => $item->suratMasuk?->perihal ?? '-',
                    'jenis_disposisi'  => $item->jenis_disposisi ?? '-',

                    'manager_approval' => $managerApproval,
                    'status'           => $item->status ?? 'Menunggu',
                    'umur_disposisi'   => $umur,
                    'created_at'       => optional($item->created_at)->format('Y-m-d H:i'),
                    'pengirim'         => $pengirimJabatan,
                ];
            });

        return view('instruksi.index', [
            'disposisi' => $rows,
            'jenis'     => $jenis,
        ]);
    }

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

    private function getActivePosition($user, $jenis)
    {
        if ($jenis === 'utama') {
            return $user->positions()
                ->where('name', 'LIKE', '%Direktur Utama%')
                ->first();
        }

        if ($jenis === 'umum') {
            return $user->positions()
                ->where('name', 'LIKE', '%Direktur Keuangan & Umum%')
                ->first();
        }

        return $user->primaryPosition(); // fallback
    }


    private function getDirectChildUsersForPosition($position, $jenis)
    {
        if (!$position) return collect();

        $level1 = $position->children;

        if ($jenis === 'utama') {

            $level2 = collect();

            foreach ($level1 as $child) {

                // ➤ SKIP "Direktur Keuangan & Umum"
                if (stripos($child->name, 'Direktur Keuangan') !== false) {
                    continue;
                }

                $level2 = $level2->merge($child->children);
            }

            $allPositions = $level1->pluck('id')
                ->merge($level2->pluck('id'))
                ->unique();
        } else {

            // Direktur Umum → ambil anak langsung saja
            $allPositions = $level1->pluck('id');
        }

        return User::whereHas('positions', function ($q) use ($allPositions) {
            $q->whereIn('positions.id', $allPositions)
                ->where('position_user.is_primary', 1);
        })
            ->orderBy('name')
            ->get();
    }


    // public function show($id, $jenis = 'utama')
    // {
    //     abort_unless(in_array($jenis, ['utama', 'umum']), 404);

    //     $disposisi = Disposisi::with([
    //         'suratMasuk',
    //         'suratMasuk.internalDoc',   // << TAMBAHKAN INI
    //         'pengirim',
    //         'instruksis',
    //         'penerimas'
    //     ])->findOrFail($id);


    //     // Cegah direktur umum memberi instruksi sebelum direktur utama
    //     if ($jenis === 'umum' && !$disposisi->instruksis->where('jenis_direktur', 'utama')->count()) {
    //         Alert::warning('Belum Ada Instruksi', 'Instruksi dari Direktur Utama belum dibuat.');
    //         // return redirect()->route('instruksi.index', 'utama');
    //     }

    //     $instruksiUtama = DisposisiInstruksi::where('disposisi_id', $id)
    //         ->where('jenis_direktur', 'utama')
    //         ->first();


    //     // Ambil ID penerima yang sudah ada untuk form select
    //     $selectedPenerimaIds = $disposisi->penerimas->pluck('id')->toArray();

    //     $isPenerimaDirekturUmum = $disposisi->penerimas
    //         ->where('id', auth()->id())
    //         ->count() > 0;

    //     // === Jika Direktur Umum membuka dan dia adalah penerima → tandai Dibaca ===
    //     if ($jenis === 'umum' && $isPenerimaDirekturUmum) {
    //         DisposisiPenerima::where('disposisi_id', $id)
    //             ->where('penerima_id', auth()->id())        // pastikan kolom benar
    //             ->update(['status' => 'Dibaca']);
    //     }

    //     $loginUser = auth()->user();

    //     $activePosition = $this->getActivePosition($loginUser, $jenis);

    //     // User bawahan 1 tingkat
    //     $users = $this->getDirectChildUsersForPosition($activePosition, $jenis);

    //     // Tambahkan user login (disable di select2)
    //     $users->prepend($loginUser);

    //     return view('instruksi.form', compact('disposisi', 'instruksiUtama', 'jenis', 'selectedPenerimaIds', 'isPenerimaDirekturUmum', 'users'));
    // }
    // public function show($id, $jenis = 'utama')
    // {
    //     abort_unless(in_array($jenis, ['utama', 'umum']), 404);

    //     $disposisi = Disposisi::with([
    //         'suratMasuk',
    //         'suratMasuk.internalDoc',
    //         'pengirim',
    //         'instruksis',
    //         'penerimas' // pastikan ini relasi user penerima (many-to-many)
    //     ])->findOrFail($id);

    //     // Cegah direktur umum memberi instruksi sebelum direktur utama
    //     if ($jenis === 'umum' && !$disposisi->instruksis->where('jenis_direktur', 'utama')->count()) {
    //         Alert::warning('Belum Ada Instruksi', 'Instruksi dari Direktur Utama belum dibuat.');
    //         // return redirect()->route('instruksi.index', 'utama');
    //     }

    //     // Ambil instruksi utama
    //     $instruksiUtama = DisposisiInstruksi::where('disposisi_id', $id)
    //         ->where('jenis_direktur', 'utama')
    //         ->first();

    //     // Ambil ID penerima yang sudah ada
    //     $selectedPenerimaIds = $disposisi->penerimas->pluck('id')->toArray();

    //     $isPenerimaDirekturUmum = $disposisi->penerimas
    //         ->where('id', auth()->id())
    //         ->count() > 0;

    //     if ($jenis === 'umum' && $isPenerimaDirekturUmum) {
    //         DisposisiPenerima::where('disposisi_id', $id)
    //             ->where('penerima_id', auth()->id())
    //             ->where('status', 'Belum Dibaca')
    //             ->update(['status' => 'Dibaca']);
    //     }

    //     $loginUser = auth()->user();

    //     $activePosition = $this->getActivePosition($loginUser, $jenis);

    //     // 1) daftar bawahan yang boleh dipilih sesuai rule lama
    //     $users = $this->getDirectChildUsersForPosition($activePosition, $jenis);

    //     // 2) ✅ FIX: tambahkan penerima yang sudah terpilih sebelumnya (agar tidak hilang di select)
    //     if (!empty($selectedPenerimaIds)) {
    //         $selectedUsers = User::with('positions') // supaya primaryPosition() aman dipanggil
    //             ->whereIn('id', $selectedPenerimaIds)
    //             ->get();

    //         $users = $users->merge($selectedUsers);
    //     }

    //     // 3) Tambahkan user login (disable di select2)
    //     $users = $users->prepend($loginUser);

    //     // 4) rapikan (hapus duplikat)
    //     $users = $users->unique('id')->values();

    //     return view('instruksi.form', compact(
    //         'disposisi',
    //         'instruksiUtama',
    //         'jenis',
    //         'selectedPenerimaIds',
    //         'isPenerimaDirekturUmum',
    //         'users'
    //     ));
    // }
    public function show($id, $jenis = 'utama')
    {
        abort_unless(in_array($jenis, ['utama', 'umum']), 404);

        $disposisi = Disposisi::with([
            'suratMasuk',
            'suratMasuk.internalDoc',
            'pengirim',
            'instruksis',
            'penerimas', // pastikan ini relasi user penerima (many-to-many)
        ])->findOrFail($id);

        // Cegah direktur umum memberi instruksi sebelum direktur utama
        if ($jenis === 'umum' && !$disposisi->instruksis->where('jenis_direktur', 'utama')->count()) {
            Alert::warning('Belum Ada Instruksi', 'Instruksi dari Direktur Utama belum dibuat.');
            // kalau kamu ingin redirect, aktifkan ini:
            // return redirect()->route('instruksi.index', 'utama');
        }

        // Ambil instruksi utama (untuk ditampilkan di view)
        $instruksiUtama = DisposisiInstruksi::where('disposisi_id', $id)
            ->where('jenis_direktur', 'utama')
            ->first();

        // Ambil ID penerima yang sudah ada
        $selectedPenerimaIds = $disposisi->penerimas->pluck('id')->toArray();

        // Cek apakah direktur umum termasuk penerima disposisi ini
        $isPenerimaDirekturUmum = $disposisi->penerimas
            ->where('id', auth()->id())
            ->count() > 0;

        // Jika direktur umum membuka disposisi dan dia adalah penerima -> tandai "Dibaca"
        if ($jenis === 'umum' && $isPenerimaDirekturUmum) {
            DisposisiPenerima::where('disposisi_id', $id)
                ->where('penerima_id', auth()->id())
                ->where('status', 'Belum Dibaca')
                ->update([
                    'status' => 'Dibaca',
                    // kalau ada kolom waktu_baca di tabel:
                    // 'waktu_baca' => now(),
                ]);
        }

        $loginUser = auth()->user();

        // Ambil posisi aktif sesuai mode (utama/umum)
        $activePosition = $this->getActivePosition($loginUser, $jenis);

        // ==============================
        // LIST USER YANG BOLEH DIPILIH
        // ==============================
        if ($jenis === 'umum') {
            // DIREKTUR UMUM: lintas semua user (kecuali diri sendiri)
            $users = User::with('positions')
                ->where('id', '!=', $loginUser->id)
                // opsional filter user aktif kalau ada:
                // ->where('is_active', 1)
                // opsional exclude role direksi:
                // ->whereNotIn('role_name', ['direktur_utama','direktur_umum'])
                ->orderBy('name')
                ->get();
        } else {
            // DIREKTUR UTAMA: sesuai rule lama (bawahan)
            // $users = $this->getDirectChildUsersForPosition($activePosition, $jenis);
            // DIREKTUR UMUM: lintas semua user (kecuali diri sendiri)
            $users = User::with('positions')
                ->where('id', '!=', $loginUser->id)
                // opsional filter user aktif kalau ada:
                // ->where('is_active', 1)
                // opsional exclude role direksi:
                // ->whereNotIn('role_name', ['direktur_utama','direktur_umum'])
                ->orderBy('name')
                ->get();
        }

        // Pastikan penerima yang sudah terpilih sebelumnya tetap muncul di select
        if (!empty($selectedPenerimaIds)) {
            $selectedUsers = User::with('positions')
                ->whereIn('id', $selectedPenerimaIds)
                ->get();

            $users = $users->merge($selectedUsers);
        }

        // Tambahkan user login (untuk disable di select2)
        $users = $users->prepend($loginUser);

        // Rapikan list
        $users = $users->unique('id')->values();

        return view('instruksi.form', compact(
            'disposisi',
            'instruksiUtama',
            'jenis',
            'selectedPenerimaIds',
            'isPenerimaDirekturUmum',
            'users'
        ));
    }


    private function isUnderDirekturUmum($user)
    {
        // Ambil posisi "Direktur Umum"
        $du = \App\Models\Position::where('name', 'LIKE', '%Direktur Keuangan & Umum%')->first();

        if (!$du) {
            return false; // fallback aman
        }

        $pos = $user->primaryPosition();

        if (!$pos) {
            return false;
        }

        // Jika user itu sendiri adalah Direktur Umum → TIDAK dianggap bawahannya
        if ($pos->id === $du->id) {
            return false;
        }

        // Telusuri rantai parent
        while ($pos) {
            if ($pos->parent_id === $du->id) {
                return true; // posisinya langsung di bawah DU
            }

            if ($pos->id === $du->id) {
                return false; // user direktur umum → bukan bawahannya
            }

            $pos = $pos->parent; // naik ke parent
        }

        return false;
    }



    public function store(Request $request, $id, $jenis = 'utama')
    {
        abort_unless(in_array($jenis, ['utama', 'umum']), 404);

        $request->validate([
            'instruksi' => 'required|string|max:1000',
            'penerima_ids' => 'required|array|min:1',
            'batas_waktu' => 'nullable|date',
        ]);

        // ============================
        // Direktur UMUM harus penerima sah
        // ============================
        if ($jenis === 'umum') {
            $isPenerima = DisposisiPenerima::where('disposisi_id', $id)
                ->where('penerima_id', auth()->id())
                ->exists();

            if (!$isPenerima) {
                Alert::error('Akses Ditolak', 'Anda tidak berhak memberikan instruksi pada disposisi ini.');
                return back();
            }
        }

        // ============================
        // Direktur UMUM hanya boleh isi jika DUt sudah isi instruksi
        // ============================
        if ($jenis === 'umum') {
            $utamaAda = DisposisiInstruksi::where('disposisi_id', $id)
                ->where('jenis_direktur', 'utama')
                ->exists();

            if (!$utamaAda) {
                Alert::warning('Belum Bisa', 'Instruksi Direktur Utama belum dibuat.');
                return back();
            }
        }

        // ============================
        // Simpan instruksi
        // ============================
        $instruksi = DisposisiInstruksi::updateOrCreate(
            [
                'disposisi_id' => $id,
                'direktur_id' => auth()->id(),
                'jenis_direktur' => $jenis,
            ],
            [
                'instruksi' => $request->instruksi,
                'batas_waktu' => $request->batas_waktu,
            ]
        );

        $disposisi = Disposisi::findOrFail($id);
        $syncData = [];

        // =====================================================
        // AUTO-TAMBAH DIREKTUR UMUM jika ada penerima bawahan DU
        // =====================================================
        $direkturUmum = User::where('role_name', 'direktur_umum')->first();

        if ($jenis === 'utama' && $direkturUmum) {
            $penerimaIds = $request->penerima_ids;

            // cek apakah ada penerima yang berada di bawah DU
            $adaBawahanDU = false;
            foreach ($penerimaIds as $pid) {
                $u = User::find($pid);
                if ($u && $this->isUnderDirekturUmum($u)) {
                    $adaBawahanDU = true;
                    break;
                }
            }

            // kalau ada bawahan DU tapi DU belum dipilih → tambahkan DU
            if ($adaBawahanDU && !in_array($direkturUmum->id, $penerimaIds)) {
                $penerimaIds[] = $direkturUmum->id;
                // override request input supaya bawahnya pakai list baru
                $request->merge(['penerima_ids' => $penerimaIds]);
            }
        }

        // =====================================================
        // LOGIKA STATUS PENERIMA BERDASARKAN HIRARKI
        // =====================================================
        foreach ($request->penerima_ids as $pid) {

            $user = User::find($pid);

            if ($jenis === 'utama') {

                // ✅ Direktur Umum harus langsung "Belum Dibaca" (dia yang menindaklanjuti)
                if ($user && $user->role_name === 'direktur_umum') {
                    $syncData[$pid] = ['status' => 'Belum Dibaca'];
                }
                // Jika PENERIMA berada di bawah Direktur Umum → pending DU
                else if ($user && $this->isUnderDirekturUmum($user)) {
                    $syncData[$pid] = ['status' => 'Pending DU'];
                }
                // selain itu → langsung kirim
                else {
                    $syncData[$pid] = ['status' => 'Belum Dibaca'];
                }
            }

            if ($jenis === 'umum') {
                // Direktur Umum finalisasi → semua jadi Belum Dibaca,
                // KECUALI dirinya sendiri (sudah menginstruksikan)
                if ((int) $pid === (int) auth()->id()) {
                    $syncData[$pid] = [
                        'status' => 'Diproses', // atau 'Selesai' kalau itu yang kamu mau
                        'waktu_baca'  => now(),
                        'waktu_tindak' => now(),
                    ];
                } else {
                    $syncData[$pid] = ['status' => 'Belum Dibaca'];
                }
            }
        }

        // Sync penerima dengan flag baru
        $disposisi->penerimas()->sync($syncData);

        // =====================================================
        // KIRIM WA: hanya jika status = Belum Dibaca
        // =====================================================
        foreach ($request->penerima_ids as $userId) {

            $p = $syncData[$userId]['status'];

            // Skip: Penerima masih pending untuk DU
            if ($p === 'Pending DU') continue;

            $user = User::find($userId);
            if (!$user || empty($user->phone_number)) continue;

            // ============================
            // ❌ Jangan kirim ke Direktur Umum
            // ============================
            if ($user->role_name === 'direktur_umum') {
                continue;
            }

            $message = $jenis === 'utama'
                ? $this->formatMessageUtama($disposisi, $request)
                : $this->formatMessageUmum($disposisi, $request);

            WppHelper::sendMessage($user->phone_number, $message);
        }


        // ============================
        // Update status disposisi
        // ============================
        $status = $jenis === 'utama'
            ? 'Diteruskan ke Penerima'
            : 'Diteruskan ke Penerima';

        $disposisi->update(['status' => $status]);

        // // WA Khusus notifikasi ke Direktur Umum setelah DUt input
        // if ($jenis === 'utama') {

        //     $direkturUmum = User::where('role_name', 'direktur_umum')->first();

        //     // pastikan ada PENDING DU baru notif DUm
        //     $adaPending = collect($syncData)->contains(fn($x) => $x['status'] === 'Pending DU');

        //     if ($direkturUmum && $adaPending) {
        //         if ($direkturUmum->phone_number) {

        //             $messageDU =
        //                 "📨 *AKSI RSDI - Instruksi Menunggu Tindakan*\n\n" .
        //                 "Nomor Disposisi: {$disposisi->no_disposisi}\n" .
        //                 "Asal Surat: {$disposisi->suratMasuk->asal_surat}\n" .
        //                 "Perihal: {$disposisi->suratMasuk->perihal}\n\n" .
        //                 "Terdapat penerima yang membutuhkan persetujuan Anda.\n" .
        //                 "Silakan buka aplikasi untuk melanjutkan.\n\n" .
        //                 "https://aksi.rsu-darulistiqomah.com";

        //             WppHelper::sendMessage($direkturUmum->phone_number, $messageDU);
        //         }
        //     }
        // }
        // WA Khusus notifikasi ke Direktur Umum setelah DUt input
        if ($jenis === 'utama') {

            $direkturUmum = User::where('role_name', 'direktur_umum')->first();

            if ($direkturUmum && !empty($direkturUmum->phone_number)) {

                // 1) ada penerima yang pending DU?
                $adaPending = collect($syncData)->contains(fn($x) => ($x['status'] ?? null) === 'Pending DU');

                // 2) apakah DU termasuk penerima (dipilih)?
                $duMasukPenerima = array_key_exists($direkturUmum->id, $syncData);

                // ✅ Kirim notif jika ada pending ATAU DU dipilih sebagai penerima
                if ($adaPending || $duMasukPenerima) {

                    // Prioritas pesan: Pending DU > Disposisi baru untuk DU
                    if ($adaPending) {
                        $messageDU =
                            "📨 *AKSI RSDI - Instruksi Menunggu Tindakan*\n\n" .
                            "Nomor Disposisi: {$disposisi->no_disposisi}\n" .
                            "Asal Surat: {$disposisi->suratMasuk->asal_surat}\n" .
                            "Perihal: {$disposisi->suratMasuk->perihal}\n\n" .
                            "Terdapat penerima yang membutuhkan persetujuan Anda.\n" .
                            "Silakan buka aplikasi untuk melanjutkan.\n\n" .
                            "https://aksi.rsu-darulistiqomah.com";
                    } else {
                        // DU dipilih sebagai penerima langsung (misal hanya DU saja)
                        $messageDU =
                            "📨 *AKSI RSDI - DISPOSISI BARU*\n\n" .
                            "Nomor Disposisi: {$disposisi->no_disposisi}\n" .
                            "Asal Surat: {$disposisi->suratMasuk->asal_surat}\n" .
                            "Perihal: {$disposisi->suratMasuk->perihal}\n\n" .
                            "Anda menerima disposisi baru dari Direktur Utama.\n" .
                            "Silakan buka aplikasi untuk melihat dan menindaklanjuti:\n\n" .
                            "https://aksi.rsu-darulistiqomah.com";
                    }

                    WppHelper::sendMessage($direkturUmum->phone_number, $messageDU);
                }
            }
        }


        Alert::success('Berhasil', "Instruksi Direktur " . ucfirst($jenis) . " berhasil disimpan!");
        return redirect()->route('instruksi.' . $jenis . '.index');
    }


    public function edit($disposisi_id, $jenis = 'utama')
    {
        $disposisi = Disposisi::with([
            'suratMasuk',
            'suratMasuk.internalDoc',
            'pengirim',
            'instruksis',
            'penerimas'
        ])->findOrFail($disposisi_id);

        $instruksi = $disposisi->instruksis()
            ->where('jenis_direktur', $jenis)
            ->firstOrFail();

        $selectedPenerimaIds = $disposisi->penerimas->pluck('id')->toArray();

        // === Tandai Dibaca jika DU membuka edit dan dia salah satu penerima ===
        if ($jenis === 'umum') {
            $isPenerimaDU = $disposisi->penerimas
                ->where('id', auth()->id())
                ->count() > 0;

            if ($isPenerimaDU) {
                DisposisiPenerima::where('disposisi_id', $disposisi_id)
                    ->where('penerima_id', auth()->id())
                    ->where('status', 'Belum Dibaca')
                    ->update(['status' => 'Dibaca']);
            }
        }

        // List penerima
        // $users = User::whereHas('positions', function ($q) {
        //     $q->where(function ($q2) {
        //         $q2->where('name', 'LIKE', '%Direktur%')
        //             ->orWhere('name', 'LIKE', '%Manager%')
        //             ->orWhere('name', 'LIKE', '%Manajer%');
        //     })->where('position_user.is_primary', true);
        // })
        //     ->orderBy('name')
        //     ->get();
        // $loginUser = auth()->user();
        // $loginPosition = $loginUser->primaryPosition();
        // $subordinatePositionIds = $this->getAllSubordinatePositions($loginPosition);


        // $users = User::whereHas('positions', function ($q) use ($subordinatePositionIds) {
        //     $q->whereIn('positions.id', $subordinatePositionIds);
        // })
        //     ->orderBy('name')
        //     ->get();

        // $loginUser = auth()->user();

        // $activePosition = $this->getActivePosition($loginUser, $jenis);

        // // User bawahan 1 tingkat
        // $users = $this->getDirectChildUsersForPosition($activePosition, $jenis);

        // // Tambahkan user login (disable di select2)
        // $users->prepend($loginUser);
        $loginUser = auth()->user();

        // Ambil posisi aktif sesuai mode (utama/umum)
        $activePosition = $this->getActivePosition($loginUser, $jenis);

        // ==============================
        // LIST USER YANG BOLEH DIPILIH
        // ==============================
        if ($jenis === 'umum') {
            // DIREKTUR UMUM: lintas semua user (kecuali diri sendiri)
            $users = User::with('positions')
                ->where('id', '!=', $loginUser->id)
                // opsional filter user aktif kalau ada:
                // ->where('is_active', 1)
                // opsional exclude role direksi:
                // ->whereNotIn('role_name', ['direktur_utama','direktur_umum'])
                ->orderBy('name')
                ->get();
        } else {
            // DIREKTUR UTAMA: sesuai rule lama (bawahan)
            // $users = $this->getDirectChildUsersForPosition($activePosition, $jenis);
            // DIREKTUR UMUM: lintas semua user (kecuali diri sendiri)
            $users = User::with('positions')
                ->where('id', '!=', $loginUser->id)
                // opsional filter user aktif kalau ada:
                // ->where('is_active', 1)
                // opsional exclude role direksi:
                // ->whereNotIn('role_name', ['direktur_utama','direktur_umum'])
                ->orderBy('name')
                ->get();
        }

        // Pastikan penerima yang sudah terpilih sebelumnya tetap muncul di select
        if (!empty($selectedPenerimaIds)) {
            $selectedUsers = User::with('positions')
                ->whereIn('id', $selectedPenerimaIds)
                ->get();

            $users = $users->merge($selectedUsers);
        }

        // Tambahkan user login (untuk disable di select2)
        $users = $users->prepend($loginUser);

        // Rapikan list
        $users = $users->unique('id')->values();

        return view('instruksi.edit', compact(
            'disposisi',
            'instruksi',
            'jenis',
            'selectedPenerimaIds',
            'users'
        ));
    }



    public function update(Request $request, $disposisi_id, $jenis = 'utama')
    {
        $request->validate([
            'instruksi' => 'required|string|max:1000',
            'batas_waktu' => 'nullable|date',
            'penerima_ids' => 'required|array|min:1',
        ]);

        // ==== Direktur UMUM hanya boleh update jika dia penerima ====
        if ($jenis === 'umum') {

            $isPenerima = DisposisiPenerima::where('disposisi_id', $disposisi_id)
                ->where('penerima_id', auth()->id())
                ->exists();

            // if (!$isPenerima) {
            //     Alert::error('Akses Ditolak', 'Anda tidak berhak mengubah instruksi pada disposisi ini.');
            //     return back();
            // }

            // cek apakah DU sudah ada instruksi dari DUt
            $utamaAda = DisposisiInstruksi::where('disposisi_id', $disposisi_id)
                ->where('jenis_direktur', 'utama')
                ->exists();

            if (!$utamaAda) {
                Alert::warning('Belum Bisa', 'Instruksi Direktur Utama belum dibuat.');
                return back();
            }
        }

        // ==== Update Isi Instruksi ====
        $instruksi = DisposisiInstruksi::where('disposisi_id', $disposisi_id)
            ->where('jenis_direktur', $jenis)
            ->firstOrFail();

        $instruksi->update([
            'instruksi' => $request->instruksi,
            'batas_waktu' => $request->batas_waktu,
        ]);

        // ==== Update Penerima ====
        $disposisi = $instruksi->disposisi()->first();

        $syncData = [];

        foreach ($request->penerima_ids as $uid) {

            $user = User::find($uid);

            if ($jenis === 'utama') {

                // Jika penerima berada di bawah Direktur Umum → Pending DU
                // dd([
                //     'user' => $user->name,
                //     'pos' => $user->primaryPosition(),
                //     'parent' => $user->primaryPosition()?->parent,
                //     'isUnderDU' => $this->isUnderDirekturUmum($user)
                // ]);

                if ($this->isUnderDirekturUmum($user)) {
                    $syncData[$uid] = ['status' => 'Pending DU'];
                } else {
                    $syncData[$uid] = ['status' => 'Belum Dibaca'];
                }
            }

            if ($jenis === 'umum') {
                // Direktur Umum finalisasi semua penerima
                $syncData[$uid] = ['status' => 'Belum Dibaca'];
            }
        }

        // apply perubahan penerima
        $disposisi->penerimas()->sync($syncData);

        // ==== Kirim WA ====
        foreach ($request->penerima_ids as $uid) {

            $status = $syncData[$uid]['status'];

            // Jika masih Pending DU → jangan kirim WA
            if ($status === 'Pending DU') continue;

            $user = User::find($uid);

            if (!$user || !$user->phone_number) continue;

            $message = $jenis === 'utama'
                ? $this->formatMessageUtama($disposisi, $request)
                : $this->formatMessageUmum($disposisi, $request);

            WppHelper::sendMessage($user->phone_number, $message);
        }

        // ==== Notifikasi ke Direktur Umum saat Direktur Utama mengedit ====
        if ($jenis === 'utama') {

            $direkturUmum = User::where('role_name', 'direktur_umum')->first();

            $adaPending = collect($syncData)
                ->contains(fn($row) => $row['status'] === 'Pending DU');

            if ($direkturUmum && $direkturUmum->phone_number && $adaPending) {

                $messageDU =
                    "📨 *AKSI RSDI - Instruksi Perlu Tindakan Direktur Umum*\n\n" .
                    "Nomor Disposisi: {$disposisi->no_disposisi}\n" .
                    "Perihal: {$disposisi->suratMasuk->perihal}\n\n" .
                    "Beberapa penerima menunggu persetujuan Anda.\n" .
                    "Silakan cek aplikasi.\n\n" .
                    "https://aksi.rsu-darulistiqomah.com";

                WppHelper::sendMessage($direkturUmum->phone_number, $messageDU);
            }
        }

        Alert::success('Berhasil', 'Instruksi berhasil diperbarui.');
        return redirect()->route('instruksi.' . $jenis . '.index');
    }


    public function reject(Request $request, $id, $jenis = 'utama')
    {
        $request->validate([
            'alasan_reject' => 'required|max:2000'
        ]);

        $disposisi = Disposisi::findOrFail($id);

        // simpan alasan ke tabel khusus
        DisposisiReject::create([
            'disposisi_id' => $id,
            'direktur_id' => auth()->id(),
            'alasan' => $request->alasan_reject
        ]);

        // ubah status disposisi
        $disposisi->status = 'Ditolak Direktur';
        $disposisi->save();

        // hilangkan penerima karena tidak diteruskan
        DisposisiPenerima::where('disposisi_id', $id)->delete();

        return redirect()->back()->with('success', 'Disposisi berhasil ditolak.');
    }

    public function cancelReject(Request $request, $id, $jenis = 'utama')
    {
        $disposisi = Disposisi::findOrFail($id);

        // hapus data reject
        DisposisiReject::where('disposisi_id', $id)->delete();

        // kembalikan status disposisi
        $disposisi->status = 'didisposisi'; // bisa 'Diteruskan', sesuaikan workflow kamu
        $disposisi->save();

        return redirect()->back()->with('success', 'Penolakan berhasil dibatalkan.');
    }

    private function formatMessageUtama($disposisi, $request)
    {
        return "📨 *AKSI RSDI - DISPOSISI BARU*\n\n"
            . "Nomor: {$disposisi->no_disposisi}\n"
            . "Asal Surat: {$disposisi->suratMasuk->asal_surat}\n"
            . "Perihal: {$disposisi->suratMasuk->perihal}\n"
            . "Instruksi Direktur Utama: {$request->instruksi}\n"
            . "Batas Waktu: " . ($request->batas_waktu ?? '-') . "\n\n"
            . "Silakan cek aplikasi:\nhttps://aksi.rsu-darulistiqomah.com";
    }

    private function formatMessageUmum($disposisi, $request)
    {
        return "📨 *AKSI RSDI - DISPOSISI BARU*\n\n"
            . "Nomor: {$disposisi->no_disposisi}\n"
            . "Asal Surat: {$disposisi->suratMasuk->asal_surat}\n"
            . "Perihal: {$disposisi->suratMasuk->perihal}\n"
            . "Instruksi Direktur Umum Keu SDI: {$request->instruksi}\n"
            . "Batas Waktu: " . ($request->batas_waktu ?? '-') . "\n\n"
            . "Silakan cek aplikasi:\nhttps://aksi.rsu-darulistiqomah.com";
    }
}
