<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

// for side bar menu active
if (!function_exists('set_active')) {
    function set_active($routes, $activeClass = 'active')
    {
        $currentName = Route::currentRouteName(); // contoh: surat_masuk.index
        $currentPath = Request::path();           // contoh: surat_masuk/create

        if (is_array($routes)) {
            foreach ($routes as $route) {
                // cocokkan berdasarkan nama route atau path prefix
                if (
                    str_starts_with($currentName, $route) ||
                    str_starts_with($currentPath, $route)
                ) {
                    return $activeClass;
                }
            }
        } else {
            if (
                str_starts_with($currentName, $routes) ||
                str_starts_with($currentPath, $routes)
            ) {
                return $activeClass;
            }
        }

        return '';
    }
}

// for side bar menu x-show
function x_show($routes)
{
    if (is_array($routes)) {
        foreach ($routes as $r) {
            if (Request::is($r . '*')) {
                return 'true';
            }
        }
    } else {
        if (Request::is($routes . '*')) {
            return 'true';
        }
    }
    return 'false';
}
