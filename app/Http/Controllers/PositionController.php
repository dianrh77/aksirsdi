<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class PositionController extends Controller
{
    public function index()
    {
        $title = 'Hapus User!';
        $text = "Apakah kamu yakin ingin menghapus user ini?";
        confirmDelete($title, $text); // ✅ munculkan konfirmasi hapus otomatis

        $positions = Position::with(['parent', 'users'])->get();
        return view('positions.index', compact('positions'));
    }


    public function create()
    {
        $positions = Position::orderBy('name')->get();
        return view('positions.create', compact('positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Position::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        Alert::success('Berhasil!', 'Data jabatan berhasil disimpan.');
        return redirect()->route('positions.index');
    }

    public function edit(Position $position)
    {
        $allPositions = Position::all();
        return view('positions.edit', compact('position', 'allPositions'));
    }


    public function update(Request $request, Position $position)
    {
        $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:positions,id',
        ]);

        // Cegah memilih dirinya sendiri sebagai atasan
        if ($request->parent_id == $position->id) {
            return back()->with('error', 'Jabatan tidak boleh menjadi atasannya sendiri.');
        }

        $position->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        Alert::success('Berhasil!', 'Data jabatan berhasil diperbarui.');
        return redirect()->route('positions.index');
    }


    public function destroy(Position $position)
    {
        $position->delete();

        Alert::success('Berhasil!', 'Data jabatan berhasil dihapus.');
        return redirect()->route('positions.index');
    }
}
