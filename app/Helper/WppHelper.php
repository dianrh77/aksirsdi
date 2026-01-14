<?php

namespace App\Helper;

use Illuminate\Support\Facades\Http;

class WppHelper
{
    public static function sendMessage($number, $message)
    {
        // NONAKTIFKAN
        return true;

        // Format nomor otomatis
        if (substr($number, 0, 1) === '0') {
            $number = '62' . substr($number, 1);
        }

        $response = Http::post('http://localhost:3000/send-message', [
            'number' => $number,
            'message' => $message,
        ]);

        // \Log::info('WPP Response:', $response->json());
        return $response->json();
    }
}
