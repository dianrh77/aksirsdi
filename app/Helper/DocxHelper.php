<?php

namespace App\Helper;

class DocxHelper
{
    public static function convertToPdf($docxPath)
    {
        $outputDir = storage_path('app/public/generated');

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // Tentukan nama PDF berdasarkan DOCX sumber
        $filename = pathinfo($docxPath, PATHINFO_FILENAME) . ".pdf";
        $pdfPath  = $outputDir . '/' . $filename;

        // 🔥 Jika PDF SUDAH ADA → langsung pakai (super cepat)
        if (file_exists($pdfPath)) {
            return 'generated/' . $filename;
        }

        // Conver DOCX → PDF dengan LibreOffice
        $escapedDocx = escapeshellarg($docxPath);
        $escapedOut  = escapeshellarg($outputDir);

        $command = '"C:\Program Files\LibreOffice\program\soffice.exe" ' .
            '--headless --convert-to pdf --outdir ' . $escapedOut . ' ' . $escapedDocx;

        exec($command, $output, $result);

        if ($result !== 0 || !file_exists($pdfPath)) {
            throw new \Exception("Konversi gagal. Command error.");
        }

        return 'generated/' . $filename;
    }
}
