<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('home', function () {
        return view('dashboard.home');
    });

    Route::get('home', function () {
        return view('dashboard.home');
    });
});

Auth::routes();
Route::group(['namespace' => 'App\Http\Controllers\Auth'],function()
{
    // ------------------------login ----------------------------//
    Route::controller(LoginController::class)->group(function () {
        Route::get('login', 'login')->name('login');
        Route::post('login', 'authenticate');
        Route::get('logout', 'logout')->name('logout');
    });

    // ----------------------- register -------------------------//
    Route::controller(RegisterController::class)->group(function () {
        Route::get('register', 'register')->name('register');
        Route::post('register','storeUser')->name('register');    
    });
});

Route::group(['namespace' => 'App\Http\Controllers'],function()
{
    Route::middleware('auth')->group(function () {
        // --------------------- main dashboard ------------------//
        Route::controller(HomeController::class)->group(function () {
            Route::get('home', 'index')->name('home');
            Route::get('analytics', 'analytics')->name('analytics');
            Route::get('finance', 'finance')->name('finance');
            Route::get('crypto', 'crypto')->name('crypto');
            Route::get('lockscreen', 'lockscreen')->name('lockscreen');
        });

        // ------------------------ Users -------------------------//
        Route::controller(UsersController::class)->group(function () {
            Route::get('users/profile', 'profile')->name('users/profile');
            Route::get('users/account/settings', 'accountSettings')->name('users/account/settings');
        });

        // ------------------------ Apps -------------------------//
        Route::controller(AppsController::class)->group(function () {
            Route::get('apps/chat', 'chat')->name('apps/chat');
            Route::get('apps/mailbox', 'mailbox')->name('apps/mailbox');
            Route::get('apps/todolist', 'todolist')->name('apps/todolist');
            Route::get('apps/notes', 'notes')->name('apps/notes');
            Route::get('apps/scrumboard', 'scrumboard')->name('apps/scrumboard');
            Route::get('apps/contacts', 'contacts')->name('apps/contacts');
            Route::get('apps/invoice/list', 'invoiceList')->name('apps/invoice/list');
            Route::get('apps/invoice/preview', 'invoicePreview')->name('apps/invoice/preview');
            Route::get('apps/invoice/add', 'invoiceAdd')->name('apps/invoice/add');
            Route::get('apps/invoice/edit', 'invoiceEdit')->name('apps/invoice/edit');
            Route::get('apps/calendar', 'calendar')->name('apps/calendar');
        });
    });
});