<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use App\Models\DisposisiFeedback;
use App\Models\DisposisiPenerima;
use App\Models\FeedbackAttachment;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class DisposisiController extends Controller
{
    public function index()
    {
        $data = Disposisi::with([
            'pengirim:id,name',
            'suratMasuk:id,asal_surat,perihal'  // <-- tambahkan ini
        ])
            ->select('id', 'no_disposisi', 'jenis_disposisi', 'pengirim_id', 'finished_at', 'surat_id', 'status', 'created_at')
            ->latest()
            ->get()
            ->map(function ($item) {
                $created = $item->created_at;
                $finished = $item->finished_at;

                // Jika selesai → hitung sampai finished_at
                // Jika belum selesai → hitung sampai sekarang
                $end = ($item->status === 'Selesai' && $finished)
                    ? $finished
                    : now();

                // Ambil selisih lengkap
                $diff = $created->diff($end);

                // Generate format otomatis
                $parts = [];

                if ($diff->d > 0) {
                    $parts[] = $diff->d . ' hari';
                }

                if ($diff->h > 0) {
                    $parts[] = $diff->h . ' jam';
                }

                if ($diff->i > 0) {
                    $parts[] = $diff->i . ' menit';
                }

                // Kalau semuanya 0 (misal baru dibuat kurang 1 menit)
                if (empty($parts)) {
                    $umur = '0 menit';
                } else {
                    $umur = implode(' ', $parts);
                }

                return [
                    'id' => $item->id,
                    'no_disposisi' => $item->no_disposisi,
                    'pengirim_nama' => $item->pengirim?->name ?? '-',
                    'asal_surat' => $item->suratMasuk?->asal_surat ?? '-',
                    'perihal' => $item->suratMasuk?->perihal ?? '-',
                    'jenis_disposisi' => $item->jenis_disposisi,
                    'status' => $item->status,
                    'umur_disposisi' => $umur, // hasil otomatis
                    'created_at' => $item->created_at->format('Y-m-d H:i'),
                ];
            });

        // dd($data);

        return view('disposisi.index', compact('data'));
    }


    public function generateNo()
    {
        $bulan = date('m'); // selalu 2 digit, misal '04'
        $tahun = date('Y');

        // Ambil disposisi terakhir di bulan & tahun ini
        $last = \App\Models\Disposisi::whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->latest('id')
            ->first();


        if ($last) {
            $parts = explode('-', $last->no_disposisi);
            //dd($parts);
            // Ambil nomor urut terakhir
            $lastNumber = (int) end($parts);
        } else {
            $lastNumber = 0; // reset ke 0 jika bulan baru
        }



        // Tambah 1 dan pad ke 4 digit
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        // Format akhir: DISP-2025-04-0001
        $noDisposisi = 'DISP-' . $tahun . '-' . $bulan . '-' . $nextNumber;

        return response()->json(['no_disposisi' => $noDisposisi]);
    }

    public function getSuratDetail($id)
    {
        $surat = \App\Models\SuratMasuk::with('creator') // <- relasi ikut dimuat
            ->select('id', 'no_surat', 'perihal', 'asal_surat', 'tgl_surat', 'created_by')
            ->find($id);

        if (!$surat) {
            return response()->json(['error' => 'Surat tidak ditemukan'], 404);
        }

        return response()->json([
            'no_surat'      => $surat->no_surat,
            'perihal'       => $surat->perihal,
            'asal_surat'    => $surat->asal_surat,
            'tgl_surat'     => $surat->tgl_surat,
            'created_by'    => $surat->created_by,
            'pembuat_nama'  => $surat->creator->name ?? '-'
        ]);
    }


    public function create(Request $request)
    {
        // hanya surat yang belum punya disposisi
        $suratMasuk = SuratMasuk::whereDoesntHave('disposisi')->get();

        $selectedSuratId = $request->get('surat_id');
        $selectedSurat = null;

        if ($selectedSuratId) {
            $selectedSurat = SuratMasuk::with('creator')->find($selectedSuratId);

            if ($selectedSurat) {
                $selectedSurat->pembuat_nama = $selectedSurat->creator->name ?? '-';
            }
        }

        return view('disposisi.create', [
            'suratMasuk' => $suratMasuk,
            'selectedSuratId' => $selectedSuratId,
            'selectedSurat' => $selectedSurat,
        ]);
    }



    public function store(Request $request)
    {
        $request->validate([
            'no_disposisi' => 'required|string|max:50',
            'surat_id' => 'nullable|integer|exists:surat_masuks,id',
            'pengirim_id' => 'required|integer',
            'catatan' => 'nullable|string',
            'jenis_disposisi' => 'required|in:biasa,penting,rahasia',
        ]);

        $disposisi = \App\Models\Disposisi::create([
            'no_disposisi' => $request->no_disposisi,
            'surat_id' => $request->surat_id,
            'pengirim_id' => $request->pengirim_id,
            'catatan' => $request->catatan,
            'jenis_disposisi' => $request->jenis_disposisi,
            'status' => 'Dibuat',
        ]);

        // Kirim notif WA ke Direktur Utama
        $direkturUtama = \App\Models\User::where('role_name', 'direktur_utama')->first();

        if ($direkturUtama && $direkturUtama->phone_number) {

            $message = "📨 *AKSI RSDI - DISPOSISI BARU*\n\n"
                . "Nomor: {$disposisi->no_disposisi}\n"
                . "Asal Surat: {$disposisi->suratMasuk->asal_surat}\n"
                . "Perihal: {$disposisi->suratMasuk->perihal}\n\n"
                . "Jenis: {$disposisi->jenis_disposisi}\n"
                . "Catatan: " . ($disposisi->catatan ?? '-') . "\n\n"
                . "Silakan isi instruksi pada aplikasi.\n"
                . "Silakan cek aplikasi:\nhttps://aksi.rsu-darulistiqomah.com";

            \App\Helper\WppHelper::sendMessage($direkturUtama->phone_number, $message);
        }

        if ($request->filled('surat_id')) {
            \App\Models\SuratMasuk::where('id', $request->surat_id)
                ->update(['status' => 'didisposisi']);
        }

        \RealRashid\SweetAlert\Facades\Alert::success('Berhasil', 'Disposisi berhasil disimpan, Selanjutnya Menunggu Instruksi Direktur');
        return redirect()->route('disposisi.index');
    }


    public function show($id)
    {
        $disposisi = Disposisi::with('penerimas')->findOrFail($id);
        return view('disposisi.detail', compact('disposisi'));
    }

    public function feedbackDirektur(Request $request, $id)
    {
        $validated = $request->validate([
            'feedback' => 'required|string',
            'lampiran.*' => 'nullable|file|max:5120',
        ]);

        $disposisi = Disposisi::findOrFail($id);

        // ✅ tentukan anchor disposisi_penerima_id untuk menyimpan feedback
        // pilihan aman: pakai penerima pertama dari disposisi tsb
        // (agar relasi feedback->penerima tetap valid)
        $anchorPenerima = DisposisiPenerima::where('disposisi_id', $disposisi->id)
            ->orderBy('id')
            ->first();

        if (!$anchorPenerima) {
            Alert::error('Gagal', 'Belum ada penerima disposisi, tidak bisa menambahkan feedback.');
            return back();
        }

        // Simpan feedback
        $feedback = DisposisiFeedback::create([
            'disposisi_penerima_id' => $anchorPenerima->id,
            'user_id' => Auth::id(),
            'feedback' => $validated['feedback'],
        ]);

        // Simpan lampiran
        if ($request->hasFile('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                $path = $file->store('feedback_lampiran', 'public');

                FeedbackAttachment::create([
                    'feedback_id' => $feedback->id,
                    'file_path'  => $path,
                    'file_name'  => $file->getClientOriginalName(),
                ]);
            }
        }

        Alert::success('Berhasil', 'Feedback berhasil ditambahkan.');
        return back();
    }
}
