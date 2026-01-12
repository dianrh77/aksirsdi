<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Disposisi;
use App\Models\NotaDinas;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use App\Models\DisposisiPenerima;
use App\Models\NotaDinasPenerima;
use Illuminate\Support\Facades\Auth;

class DashboardPenerimaController extends Controller
{
    // public function index()
    // {
    //     $userId = Auth::id();
    //     $tahunIni = now()->year;
    //     $bulanIni = now()->month;

    //     // ---------------------------------
    //     // 1️⃣ DISPOSISI
    //     // ---------------------------------
    //     $disposisiSaya = DisposisiPenerima::where('penerima_id', $userId);

    //     $totalDisposisi = $disposisiSaya->count();
    //     $disposisiSelesai = (clone $disposisiSaya)->where('status', 'Selesai')->count();
    //     $disposisiBelumSelesai = (clone $disposisiSaya)
    //         ->where('status', '!=', 'Selesai')
    //         ->count();

    //     $bulanIniDisposisi = (clone $disposisiSaya)
    //         ->whereMonth('created_at', $bulanIni)
    //         ->whereYear('created_at', $tahunIni)
    //         ->count();

    //     // ---------------------------------
    //     // 2️⃣ NOTA DINAS
    //     // ---------------------------------
    //     $notaSaya = NotaDinas::where('penerima_id', $userId);

    //     $notaSelesai = (clone $notaSaya)->where('status', 'Selesai')->count();
    //     $totalNota = $notaSaya->count();
    //     $notaBelumSelesai = (clone $notaSaya)
    //         ->where('status', '!=', 'Selesai')
    //         ->count();

    //     $bulanIniNota = (clone $notaSaya)
    //         ->whereMonth('created_at', $bulanIni)
    //         ->whereYear('created_at', $tahunIni)
    //         ->count();

    //     // Aktivitas Disposisi
    //     $disposisiAktivitas = DisposisiPenerima::where('penerima_id', $userId)
    //         ->with('disposisi:id,no_disposisi,catatan')
    //         ->select('id', 'disposisi_id', 'created_at')
    //         ->latest()
    //         ->take(5)
    //         ->get()
    //         ->map(function ($x) {
    //             return [
    //                 'jenis'   => 'disposisi',
    //                 'nomor'   => $x->disposisi->no_disposisi ?? '-',
    //                 'judul'   => $x->disposisi->catatan ?? '-',
    //                 'tanggal' => $x->created_at,
    //             ];
    //         });

    //     // Aktivitas Nota Dinas
    //     $notaAktivitas = NotaDinas::where('penerima_id', $userId)
    //         ->select('id', 'nomor_nota', 'judul', 'created_at')
    //         ->latest()
    //         ->take(5)
    //         ->get()
    //         ->map(function ($x) {
    //             return [
    //                 'jenis'   => 'nota',
    //                 'nomor'   => $x->nomor_nota,
    //                 'judul'   => $x->judul,
    //                 'tanggal' => $x->created_at,
    //             ];
    //         });

    //     // Gabungkan dua aktivitas, urutkan tanggal terbaru, ambil 5 teratas
    //     $aktivitas = collect()
    //         ->merge($disposisiAktivitas)
    //         ->merge($notaAktivitas)
    //         ->sortByDesc('tanggal')
    //         ->take(5)
    //         ->values();


    //     // ---------------------------------
    //     // 4️⃣ Grafik Status (Total Gabungan)
    //     // ---------------------------------
    //     $chartStatus = [
    //         'Belum Selesai' => $disposisiBelumSelesai + $notaBelumSelesai,
    //         'Selesai'      => $disposisiSelesai + $notaSelesai,
    //     ];

    //     return view('dashboard.penerima', compact(
    //         'bulanIniDisposisi',
    //         'bulanIniNota',
    //         'totalDisposisi',
    //         'totalNota',
    //         'disposisiSelesai',
    //         'disposisiBelumSelesai',
    //         'notaBelumSelesai',
    //         'notaSelesai',
    //         'aktivitas',
    //         'chartStatus'
    //     ));
    // }
    public function index()
    {
        $userId = Auth::id();
        $tahunIni = now()->year;
        $bulanIni = now()->month;

        // ====================================================
        // 1️⃣ DISPOSISI
        // ====================================================
        $disposisiSaya = DisposisiPenerima::where('penerima_id', $userId);

        $totalDisposisi      = $disposisiSaya->count();
        $disposisiSelesai    = (clone $disposisiSaya)->where('status', 'Selesai')->count();
        $disposisiBelumSelesai = (clone $disposisiSaya)->where('status', '!=', 'Selesai')->count();

        $bulanIniDisposisi = (clone $disposisiSaya)
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->count();


        // ====================================================
        // 2️⃣ NOTA DINAS (VERSI BARU)
        // ====================================================
        $notaSaya = NotaDinasPenerima::where('user_id', $userId);

        $totalNota = $notaSaya->count();

        $notaSelesai = (clone $notaSaya)
            ->where('status', 'selesai')
            ->count();

        $notaBelumSelesai = (clone $notaSaya)
            ->whereNotIn('status', ['selesai', 'rejected'])->count();

        $bulanIniNota = (clone $notaSaya)
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->count();


        // ====================================================
        // 3️⃣ AKTIVITAS TERBARU
        // ====================================================

        // Aktivitas Disposisi
        $disposisiAktivitas = DisposisiPenerima::where('penerima_id', $userId)
            ->with('disposisi:id,no_disposisi,catatan')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($x) {
                return [
                    'jenis'   => 'disposisi',
                    'nomor'   => $x->disposisi->no_disposisi ?? '-',
                    'judul'   => $x->disposisi->catatan ?? '-',
                    'tanggal' => $x->created_at,
                ];
            });

        // Aktivitas Nota Dinas VIA PIVOT
        $notaAktivitas = NotaDinasPenerima::where('user_id', $userId)
            ->with('nota:id,nomor_nota,judul')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($x) {
                return [
                    'jenis'   => 'nota',
                    'nomor'   => $x->nota->nomor_nota,
                    'judul'   => $x->nota->judul,
                    'tanggal' => $x->created_at,
                ];
            });

        $aktivitas = collect()
            ->merge($disposisiAktivitas)
            ->merge($notaAktivitas)
            ->sortByDesc('tanggal')
            ->take(5)
            ->values();


        // ====================================================
        // 4️⃣ Grafik Status Gabungan
        // ====================================================

        $chartStatus = [
            'Belum Selesai' => $disposisiBelumSelesai + $notaBelumSelesai,
            'Selesai'       => $disposisiSelesai + $notaSelesai,
        ];



        return view('dashboard.penerima', compact(
            'bulanIniDisposisi',
            'bulanIniNota',
            'totalDisposisi',
            'totalNota',
            'disposisiSelesai',
            'disposisiBelumSelesai',
            'notaSelesai',
            'notaBelumSelesai',
            'aktivitas',
            'chartStatus'
        ));
    }
}
