<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Helper\DocxHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::all();
        return view('template.index', compact('templates'));
    }

    public function create()
    {
        return view('template.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_template' => 'required|string|max:255',
            'file_template' => 'required|mimes:docx|max:4096',
        ]);

        // Simpan file DOCX
        $path = $request->file('file_template')->store('templates', 'public');

        // Simpan template ke DB
        $template = Template::create([
            'nama_template' => $request->nama_template,
            'file_template' => $path,
            'uploaded_by'   => auth()->id(),
            'position_id'   => auth()->user()->primaryPosition()->id,
        ]);

        /* ===========================================================
     *  AJAX MODE (dipanggil dari modal di halaman surat_masuk)
     * =========================================================== */
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'template' => [
                    'id'            => $template->id,
                    'nama_template' => $template->nama_template,
                ]
            ]);
        }

        /* ===========================================================
     *  NORMAL MODE (halaman CRUD template biasa)
     * =========================================================== */
        return redirect()
            ->route('template.index')
            ->with('success', 'Template berhasil ditambahkan!');
    }




    /* ============================================================
     *  ✏ EDIT TEMPLATE
     * ============================================================ */
    public function edit($id)
    {
        $template = Template::findOrFail($id);
        return view('template.edit', compact('template'));
    }


    /* ============================================================
     *  🔄 UPDATE TEMPLATE
     * ============================================================ */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_template' => 'required',
            'file_template' => 'nullable|mimes:docx|max:4096',
        ]);

        $template = Template::findOrFail($id);

        // update nama template
        $template->nama_template = $request->nama_template;

        // kalau user upload file baru → ganti file lama
        if ($request->hasFile('file_template')) {

            // hapus file lama
            if ($template->file_template && Storage::disk('public')->exists($template->file_template)) {
                Storage::disk('public')->delete($template->file_template);
            }

            // upload baru
            $newPath = $request->file('file_template')->store('templates', 'public');
            $template->file_template = $newPath;
        }

        $template->save();

        return redirect()->route('template.index')
            ->with('success', 'Template berhasil diperbarui!');
    }


    /* ============================================================
     *  🗑 HAPUS TEMPLATE
     * ============================================================ */
    /* ============================================================
 *  🗑 HAPUS TEMPLATE + PDF CACHED
 * ============================================================ */
    public function destroy($id)
    {
        $template = Template::findOrFail($id);

        // Hapus file DOCX asli
        if ($template->file_template && Storage::disk('public')->exists($template->file_template)) {
            Storage::disk('public')->delete($template->file_template);
        }

        // ===== Hapus FILE PDF HASIL CONVERT (CACHE) =====
        $generatedPdf = 'generated/' . pathinfo($template->file_template, PATHINFO_FILENAME) . '.pdf';

        if (Storage::disk('public')->exists($generatedPdf)) {
            Storage::disk('public')->delete($generatedPdf);
        }

        // Hapus data DB
        $template->delete();

        return redirect()->route('template.index')
            ->with('success', 'Template & file cached berhasil dihapus!');
    }


    public function preview($id)
    {
        $template = Template::findOrFail($id);

        $docxPath = storage_path('app/public/' . $template->file_template);

        // Convert (atau ambil cache)
        $pdfRelative = DocxHelper::convertToPdf($docxPath);
        $pdfPath = storage_path('app/public/' . $pdfRelative);

        return response()->file($pdfPath);
    }

    public function raw($id)
    {
        $template = Template::findOrFail($id);

        $docx = storage_path('app/public/' . $template->file_template);

        // Convert DOCX → HTML
        $phpWord = IOFactory::load($docx);
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

        // file sementara
        $tmp = storage_path('app/public/tmp_template_' . time() . '.html');
        $htmlWriter->save($tmp);

        // ambil isi HTML
        $html = file_get_contents($tmp);

        // HAPUS FILE HTML TEMPORARY
        if (file_exists($tmp)) {
            unlink($tmp);
        }

        return response()->json([
            'html' => $html
        ]);
    }
}
