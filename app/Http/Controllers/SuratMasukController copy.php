<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helper\WppHelper;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class SuratMasukController extends Controller
{

    private function isBawahan($manager, $suratPositionId)
    {
        $managerPos = $manager->primaryPosition();

        // Ambil semua posisi anak (rekursif)
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

    public function index()
    {
        $user = Auth::user();
        $position = $user->primaryPosition();
        $level = $user->getLevel(); // DU=1, Direktur=2, Manajer=3, Kasi=4, Staff=5
        $userId = $user->id;
        // =============================================
        // 1. KESEKRETARIATAN → lihat semua internal & eksternal
        // =============================================
        if ($user->role_name === 'kesekretariatan') {

            // INTERNAL: surat dia buat + surat yg sudah lolos manager
            $internal = SuratMasuk::where('jenis_surat', 'internal')
                ->where(function ($q) use ($userId) {
                    $q->where('created_by', $userId)
                        ->orWhereIn('status', ['menunggu_kesra', 'siap_disposisi', 'didisposisi']);
                })
                ->latest()
                ->get();

            // EKSTERNAL: semua
            $external = SuratMasuk::where('jenis_surat', 'eksternal')
                ->latest()
                ->get();

            return view('surat_masuk.index', compact('internal', 'external', 'level'));
        }


        // =============================================
        // 2. MANAJER → melihat surat dia + bawahan
        // =============================================
        if ($level === 3) {

            // semua posisi bawahan
            $childPositions = $this->getAllChildrenPositions($position);

            $internal = SuratMasuk::where('jenis_surat', 'internal')
                ->where(function ($q) use ($userId, $childPositions) {
                    $q->where('created_by', $userId)                 // surat dia
                        ->orWhereIn('position_id', $childPositions);   // surat bawahan
                })
                ->latest()
                ->get();

            // eksternal tidak boleh
            $external = collect([]);

            return view('surat_masuk.index', compact('internal', 'external', 'level'));
        }


        // =============================================
        // 3. KASI / KARU / STAFF → hanya surat miliknya
        // =============================================
        if ($level > 3) {

            $internal = SuratMasuk::where('jenis_surat', 'internal')
                ->where('created_by', $userId)
                ->latest()
                ->get();

            $external = collect([]);

            return view('surat_masuk.index', compact('internal', 'external', 'level'));
        }


        // =============================================
        // 4. DIREKTUR / DU → menu ini tidak dipakai (kosong)
        // =============================================
        $internal = collect([]);
        $external = collect([]);

        return view('surat_masuk.index', compact('internal', 'external', 'level'));
    }



    public function create()
    {
        // dd(Auth::user());
        return view('surat_masuk.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'no_surat' => 'required|string|max:255',
                'tgl_surat' => 'required|date',
                'asal_surat' => 'required|string|max:255',
                'perihal' => 'required|string',
                'jenis_surat' => 'required|in:internal,eksternal',
                'file_pdf' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            $path = $request->file('file_pdf')->store('surat_masuk', 'public');

            $userLevel = Auth::user()->getLevel();

            // Jika pembuat surat adalah Manager atau Direktur → langsung ke Kesekretariatan
            if ($userLevel <= 3) {
                $status = 'menunggu_kesra';
            }
            // Jika pembuat surat adalah Kasi/Karu/Staf → harus validasi manager dulu
            else {
                $status = 'menunggu_manager';
            }

            $surat = SuratMasuk::create([
                'no_surat' => $request->no_surat,
                'tgl_surat' => $request->tgl_surat,
                'asal_surat' => $request->asal_surat,
                'perihal' => $request->perihal,
                'jenis_surat' => $request->jenis_surat,
                'file_pdf' => $path,
                // tambahan penting:
                'position_id' => Auth::user()->primaryPosition()->id,
                'created_by' => Auth::id(),
                'status' => $status, // ← status dinamis sesuai level pembuat
            ]);

            // ============================
            // 🔔 KIRIM NOTIF KE KESEKRETARIATAN
            // ============================
            $penerima = User::where('role_name', 'kesekretariatan')->get();

            if ($penerima->count() > 0) {
                foreach ($penerima as $userKS) {
                    if (!$userKS->phone_number) {
                        continue;
                    }

                    $message = "📥 *AKSI RSDI - Surat Masuk Baru*\n\n"
                        . "Jenis Surat: *" . ucfirst($surat->jenis_surat) . "*\n"
                        . "Nomor Surat: {$surat->no_surat}\n"
                        . "Asal Surat: {$surat->asal_surat}\n"
                        . "Perihal: {$surat->perihal}\n"
                        . "Tanggal Surat: {$surat->tgl_surat}\n\n"
                        . "Surat ini baru saja diinput oleh *" . Auth::user()->name . "*.\n"
                        . "Silakan lakukan pengecekan dan disposisi.\n\n"
                        . "🔗 Cek di aplikasi:\nhttps://aksi.rsu-darulistiqomah.com";

                    // Kirim via WPPConnect
                    WppHelper::sendMessage($userKS->phone_number, $message);
                }
            }
            // ============================

            Alert::toast('Data surat masuk berhasil disimpan.', 'success');
            return redirect()->route('surat_masuk.index');
        } catch (\Exception $e) {
            Alert::error('Gagal!', $e->getMessage());
            return back()->withInput();
        }
    }


    public function showFile($id)
    {
        $surat = SuratMasuk::findOrFail($id);
        $path = storage_path('app/public/' . $surat->file_pdf);

        if (!file_exists($path)) {
            Alert::error('404!', 'File tidak ditemukan.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ]);
    }

    public function edit($id)
    {
        $title = 'Hapus Surat Masuk!';
        $text = "Apakah kamu yakin untuk menghapus?";
        confirmDelete($title, $text);
        $data = SuratMasuk::findOrFail($id);
        return view('surat_masuk.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'no_surat' => 'required',
            'asal_surat' => 'required',
            'perihal' => 'required',
            'tgl_surat' => 'required|date',
            'file_pdf' => 'nullable|mimes:pdf|max:2048',
            'jenis_surat' => 'required|in:internal,eksternal',
        ]);

        try {
            $surat = SuratMasuk::findOrFail($id);
            $surat->update($request->only('no_surat', 'asal_surat', 'perihal', 'tgl_surat', 'jenis_surat'));

            if ($request->hasFile('file_pdf')) {
                $file = $request->file('file_pdf');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/surat_masuk'), $filename);
                $surat->file_pdf = $filename;
                $surat->save();
            }

            Alert::toast('Data surat masuk berhasil diperbarui!', 'success');
            return redirect()->route('surat_masuk.index');
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        // Hapus file PDF jika ada
        $filePath = public_path('uploads/surat_masuk/' . $surat->file_pdf);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $surat->delete();

        \RealRashid\SweetAlert\Facades\Alert::toast('Data surat berhasil dihapus!', 'success');
        return redirect()->route('surat_masuk.index');
    }

    public function validasi($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        // Pastikan hanya manajer yang bisa validasi
        if (Auth::user()->getLevel() !== 3) {
            abort(403, 'Anda tidak memiliki izin untuk validasi surat ini.');
        }

        // Hanya surat bawahan yang boleh divalidasi
        if (! $this->isBawahan(Auth::user(), $surat->position_id)) {
            abort(403, 'Surat ini bukan dari bawahan Anda.');
        }

        // Update status jadi menunggu kesekretariatan
        $surat->status = 'menunggu_kesra';
        $surat->save();

        // Option: Notifikasi ke Kesra
        // ...

        \RealRashid\SweetAlert\Facades\Alert::toast('Surat berhasil divalidasi.', 'success');
        return redirect()->route('surat_masuk.index');
    }

    public function tolak($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        if (Auth::user()->getLevel() !== 3) {
            abort(403, 'Anda tidak memiliki izin untuk menolak surat ini.');
        }

        if (! $this->isBawahan(Auth::user(), $surat->position_id)) {
            abort(403, 'Surat ini bukan dari bawahan Anda.');
        }

        $surat->status = 'ditolak_manager';
        $surat->save();

        \RealRashid\SweetAlert\Facades\Alert::toast('Surat telah ditolak.', 'success');
        return redirect()->route('surat_masuk.index');
    }
}
