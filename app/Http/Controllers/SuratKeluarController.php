<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class SuratKeluarController extends Controller
{
    public function index()
    {
        $data = SuratKeluar::with('user')->latest()->get();
        return view('surat_keluar.index', compact('data'));
    }

    public function create()
    {
        return view('surat_keluar.create');
    }

    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'no_surat' => 'required|string|max:255',
                'tgl_surat' => 'required|date',
                'tujuan_surat' => 'required|string|max:255',
                'perihal' => 'required|string',
                'file_pdf' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            // Simpan data
            $path = $request->file('file_pdf')->store('surat_keluar', 'public');

            SuratKeluar::create([
                'no_surat' => $request->no_surat,
                'tgl_surat' => $request->tgl_surat,
                'tujuan_surat' => $request->tujuan_surat,
                'perihal' => $request->perihal,
                'file_pdf' => $path,
                'created_by' => Auth::id(),
            ]);

            // Tampilkan notifikasi sukses
            Alert::success('Berhasil!', 'Data surat masuk berhasil disimpan.');
            return redirect()->route('surat_keluar.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Jika validasi gagal, tampilkan notifikasi error
            Alert::error('Gagal!', 'Periksa kembali inputan Anda.');
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            // Jika terjadi error lain
            Alert::error('Terjadi Kesalahan', $e->getMessage());
            return back()->withInput();
        }
    }

    public function showFile($id)
    {
        $surat = SuratKeluar::findOrFail($id);
        $path = storage_path('app/public/' . $surat->file_pdf);

        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ]);
    }

    public function edit($id)
    {
        $title = 'Hapus Surat Keluar!';
        $text = "Apakah kamu yakin untuk menghapus?";
        confirmDelete($title, $text);
        $data = SuratKeluar::findOrFail($id);
        return view('surat_keluar.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'no_surat' => 'required',
            'tujuan_surat' => 'required',
            'perihal' => 'required',
            'tgl_surat' => 'required|date',
            'file_pdf' => 'nullable|mimes:pdf|max:2048',
        ]);

        try {
            $surat = SuratKeluar::findOrFail($id);
            $surat->update($request->only('no_surat', 'tujuan_surat', 'perihal', 'tgl_surat'));

            if ($request->hasFile('file_pdf')) {
                $file = $request->file('file_pdf');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/surat_keluar'), $filename);
                $surat->file_pdf = $filename;
                $surat->save();
            }

            Alert::success('Berhasil', 'Data surat masuk berhasil diperbarui!');
            return redirect()->route('surat_keluar.index');
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        $surat = SuratKeluar::findOrFail($id);

        // Hapus file PDF jika ada
        $filePath = public_path('uploads/surat_keluar/' . $surat->file_pdf);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $surat->delete();

        \RealRashid\SweetAlert\Facades\Alert::success('Berhasil', 'Data surat berhasil dihapus!');
        return redirect()->route('surat_keluar.index');
    }
}
