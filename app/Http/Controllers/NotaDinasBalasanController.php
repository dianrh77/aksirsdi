<?php

namespace App\Http\Controllers;

use App\Models\NotaDinas;
use App\Models\NotaDinasBalasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Helper\WppHelper;

class NotaDinasBalasanController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'balasan' => 'required|string',
            'lampiran' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $nota = NotaDinas::findOrFail($id);

        $path = null;
        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('nota_dinas_balasan', 'public');
        }

        NotaDinasBalasan::create([
            'nota_dinas_id' => $id,
            'user_id' => Auth::id(),
            'balasan' => $request->balasan,
            'lampiran' => $path,
        ]);

        // Update status
        $nota->update(['status' => 'dibalas']);

        // Kirim notif WA ke pengirim pertama
        $pengirim = $nota->pengirim;

        if ($pengirim->phone_number) {

            $message = "💬 *Balasan Nota Dinas*\n\n"
                . "Nomor: {$nota->nomor_nota}\n"
                . "Judul: {$nota->judul}\n"
                . "Dibalas oleh: " . Auth::user()->name . "\n\n"
                . "Silakan buka aplikasi untuk membaca balasan.";

            WppHelper::sendMessage($pengirim->phone_number, $message);
        }

        return back()->with('success', 'Balasan berhasil dikirim!');
    }
}
