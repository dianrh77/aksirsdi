<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use App\Models\User;
use Carbon\Carbon;

class DashboardKesekretariatanController extends Controller
{
    public function index()
    {
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // Statistik utama
        $suratMasuk = SuratMasuk::whereYear('tgl_surat', $tahunIni)
            ->whereMonth('tgl_surat', $bulanIni)
            ->count();

        $suratKeluar = SuratKeluar::whereYear('tgl_surat', $tahunIni)
            ->whereMonth('tgl_surat', $bulanIni)
            ->count();

        $jumlahDisposisi = Disposisi::count();
        $disposisiBelum = Disposisi::whereNotIn('status', ['selesai', 'rejected'])->count();

        // Total per tahun
        $totalMasuk = SuratMasuk::whereYear('tgl_surat', $tahunIni)->count();
        $totalKeluar = SuratKeluar::whereYear('tgl_surat', $tahunIni)->count();

        // Total user
        $totalUser = User::count();

        // Grafik per bulan
        $bulan = collect(range(1, 12))->map(fn($m) => Carbon::create()->month($m)->translatedFormat('F'));

        $chartMasuk = collect(range(1, 12))->map(
            fn($m) =>
            SuratMasuk::whereYear('tgl_surat', $tahunIni)
                ->whereMonth('tgl_surat', $m)
                ->count()
        );

        $chartKeluar = collect(range(1, 12))->map(
            fn($m) =>
            SuratKeluar::whereYear('tgl_surat', $tahunIni)
                ->whereMonth('tgl_surat', $m)
                ->count()
        );

        // Aktivitas terbaru
        $aktivitas = SuratMasuk::latest()->take(5)->get();

        return view('dashboard.kesekretariatan', compact(
            'suratMasuk',
            'totalMasuk',
            'suratKeluar',
            'totalKeluar',
            'jumlahDisposisi',
            'disposisiBelum',
            'chartMasuk',
            'chartKeluar',
            'bulan',
            'aktivitas',
            'totalUser'
        ));
    }
}
