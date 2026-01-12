<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;

if (!function_exists('page_title')) {
    function page_title(?string $routeName = null): string
    {
        $routeName = $routeName ?? Route::currentRouteName();

        // Kalau tidak ada route name, fallback ke nama default
        if (!$routeName) {
            return config('app.name');
        }

        $parts = explode('.', $routeName);
        $model = str_replace('_', ' ', $parts[0] ?? '');
        $action = $parts[1] ?? '';

        $title = match ($action) {
            'index' => "Daftar $model",
            'create', 'store' => "Tambah $model",
            'edit', 'update' => "Edit $model",
            'show' => "Detail $model",
            'destroy' => "Hapus $model",
            default => $model,
        };

        // Jadikan huruf pertama tiap kata kapital
        return Str::title("AKSI - $title");
    }
}
