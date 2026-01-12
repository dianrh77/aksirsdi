<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\HomeController;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WppTestController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\NotaDinasController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DisposisiMasukController;
use App\Http\Controllers\NotaDinasBalasanController;
use App\Http\Controllers\DashboardPenerimaController;
use App\Http\Controllers\DisposisiInstruksiController;
use App\Http\Controllers\DashboardKesekretariatanController;

Route::get('/', function () {
    return view('auth.login');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('home', [DashboardKesekretariatanController::class, 'index'])
        ->name('dashboard.home');
});

Auth::routes();
Route::group(['namespace' => 'App\Http\Controllers\Auth'], function () {
    // ------------------------login ----------------------------//
    Route::controller(LoginController::class)->group(function () {
        Route::get('login', 'login')->name('login');
        Route::post('login', 'authenticate');
        Route::get('logout', 'logout')->name('logout');
    });

    // ----------------------- register -------------------------//
    Route::controller(RegisterController::class)->group(function () {
        Route::get('register', 'register')->name('register');
        Route::post('register', 'storeUser')->name('register');
    });
});


Route::group(['namespace' => 'App\Http\Controllers'], function () {
    Route::middleware('auth')->group(function () {
        // --------------------- main dashboard ------------------//
        Route::get('/dashboard/kesekretariatan', [DashboardKesekretariatanController::class, 'index'])
            ->name('dashboard.kesekretariatan');
        Route::get('/dashboard/penerima', [DashboardPenerimaController::class, 'index'])
            ->name('dashboard.penerima');


        Route::middleware([RoleMiddleware::class . ':kesekretariatan'])->group(function () {
            Route::get('/users', [UsersController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
            Route::post('/users', [UsersController::class, 'store'])->name('users.store');
            Route::get('/users/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
            Route::put('/users/{id}', [UsersController::class, 'update'])->name('users.update');
            Route::delete('/users/{id}', [UsersController::class, 'destroy'])->name('users.destroy');
        });

        // ------------------------ Surat Masuk -------------------------//
        Route::prefix('surat_masuk')->group(function () {

            // Semua user bisa melihat daftar & file
            Route::get('/', [SuratMasukController::class, 'index'])->name('surat_masuk.index');
            Route::get('/{id}/file', [SuratMasukController::class, 'showFile'])->name('surat_masuk.file');

            // Semua user bisa create & store surat
            Route::get('/create', [SuratMasukController::class, 'create'])->name('surat_masuk.create');
            Route::post('/', [SuratMasukController::class, 'store'])->name('surat_masuk.store');

            // Route::get('/{id}/edit', [SuratMasukController::class, 'edit'])->name('surat_masuk.edit');
            // Route::put('/{id}', [SuratMasukController::class, 'update'])->name('surat_masuk.update');

            Route::get('/{id}/edit', [SuratMasukController::class, 'editKetik'])->name('surat_masuk.edit_ketik');
            Route::post('/{id}/update-ketik', [SuratMasukController::class, 'updateKetik'])->name('surat_masuk.update_ketik');

            // Hanya Kesekretariatan yang boleh hapus
            Route::middleware([RoleMiddleware::class . ':kesekretariatan'])->group(function () {
                Route::delete('/{id}', [SuratMasukController::class, 'destroy'])->name('surat_masuk.destroy');
            });
        });

        // ------------------------ Surat Keluar -------------------------//
        Route::resource('surat_keluar', SuratKeluarController::class);
        Route::get('surat_keluar/{id}/file', [SuratKeluarController::class, 'showFile'])->name('surat_keluar.file');

        // ------------------------ Disposisi -------------------------//
        Route::prefix('disposisi')->group(function () {
            Route::get('/generate-no', [DisposisiController::class, 'generateNo'])->name('disposisi.generateNo'); // <-- letakkan paling atas

            Route::get('/', [DisposisiController::class, 'index'])->name('disposisi.index');
            Route::get('/create', [DisposisiController::class, 'create'])->name('disposisi.create');
            Route::post('/', [DisposisiController::class, 'store'])->name('disposisi.store');
            Route::get('/{id}', [DisposisiController::class, 'show'])->name('disposisi.show');
            Route::get('/{id}/edit', [DisposisiController::class, 'edit'])->name('disposisi.edit');
            Route::put('/{id}', [DisposisiController::class, 'update'])->name('disposisi.update');
            Route::delete('/{id}', [DisposisiController::class, 'destroy'])->name('disposisi.destroy');
            Route::post('/{id}/feedback', [DisposisiController::class, 'feedbackDirektur'])
                ->name('disposisi.feedbackDirektur');

            // 🔹 route baru untuk ambil data surat by id
            Route::get('/surat/{id}/detail', [DisposisiController::class, 'getSuratDetail'])->name('disposisi.getSuratDetail');
            Route::post('/{disposisi}/teruskan', [DisposisiMasukController::class, 'teruskanFromDisposisi'])
                ->name('disposisi.teruskan');
        });

        // ---------------------- Instruksi Direktur Utama ---------------------- //
        Route::middleware([RoleMiddleware::class . ':direktur_utama'])->group(function () {
            Route::prefix('instruksi')->group(function () {
                Route::get('/utama', [DisposisiInstruksiController::class, 'index'])
                    ->defaults('jenis', 'utama')->name('instruksi.utama.index');

                Route::get('/utama/{id}', [DisposisiInstruksiController::class, 'show'])
                    ->defaults('jenis', 'utama')->name('instruksi.utama.show');

                Route::post('/utama/{id}', [DisposisiInstruksiController::class, 'store'])
                    ->defaults('jenis', 'utama')->name('instruksi.utama.store');

                Route::get('/utama/{disposisi}/edit', [DisposisiInstruksiController::class, 'edit'])
                    ->defaults('jenis', 'utama')->name('instruksi.utama.edit');

                Route::put('/utama/{disposisi}', [DisposisiInstruksiController::class, 'update'])
                    ->defaults('jenis', 'utama')->name('instruksi.utama.update');

                Route::post('/utama/{id}/reject', [DisposisiInstruksiController::class, 'reject'])
                    ->defaults('jenis', 'utama')->name('instruksi.utama.reject');

                Route::post('/utama/{id}/cancel-reject', [DisposisiInstruksiController::class, 'cancelReject'])
                    ->defaults('jenis', 'utama')->name('instruksi.utama.cancelReject');
            });
        });

        // ---------------------- Instruksi Direktur Umum ---------------------- //
        Route::middleware([RoleMiddleware::class . ':direktur_umum'])->group(function () {
            Route::prefix('instruksi')->group(function () {
                Route::get('/umum', [DisposisiInstruksiController::class, 'index'])
                    ->defaults('jenis', 'umum')->name('instruksi.umum.index');

                Route::get('/umum/{id}', [DisposisiInstruksiController::class, 'show'])
                    ->defaults('jenis', 'umum')->name('instruksi.umum.show');

                Route::post('/umum/{id}', [DisposisiInstruksiController::class, 'store'])
                    ->defaults('jenis', 'umum')->name('instruksi.umum.store');

                Route::get('/umum/{disposisi}/edit', [DisposisiInstruksiController::class, 'edit'])
                    ->defaults('jenis', 'umum')->name('instruksi.umum.edit');

                Route::put('/umum/{disposisi}', [DisposisiInstruksiController::class, 'update'])
                    ->defaults('jenis', 'umum')->name('instruksi.umum.update');

                Route::post('/umum/{id}/reject', [DisposisiInstruksiController::class, 'reject'])
                    ->defaults('jenis', 'umum')->name('instruksi.umum.reject');

                Route::post('/umum/{id}/cancel-reject', [DisposisiInstruksiController::class, 'cancelReject'])
                    ->defaults('jenis', 'umum')->name('instruksi.umum.cancelReject');
            });
        });



        // ------------------------ Disposisi Masuk -------------------------//
        Route::prefix('disposisi-masuk')->middleware('auth')->group(function () {
            Route::get('/', [App\Http\Controllers\DisposisiMasukController::class, 'index'])->name('disposisi_masuk.index');
            Route::post('/{id}/update-status', [DisposisiMasukController::class, 'updateStatus'])->name('disposisi_masuk.updateStatus');
            Route::get('/{id}', [DisposisiMasukController::class, 'show'])->name('disposisi_masuk.show');
            Route::post('/{id}/feedback', [DisposisiMasukController::class, 'feedback'])->name('disposisi_masuk.feedback');
            Route::post('/{id}/selesai', [DisposisiMasukController::class, 'selesai'])
                ->name('disposisi_masuk.selesai');
            Route::post('/{id}/teruskan', [DisposisiMasukController::class, 'teruskan'])
                ->name('disposisi_masuk.teruskan');
            Route::delete(
                '/lampiran/{id}',
                [DisposisiMasukController::class, 'hapusLampiran']
            )->name('disposisi_masuk.hapusLampiran');
            Route::get('/{id}/print', [DisposisiMasukController::class, 'print'])
                ->name('disposisi_masuk.print');
        });

        Route::middleware([RoleMiddleware::class . ':kesekretariatan,direktur_utama,direktur_umum'])->group(function () {
            Route::prefix('kesekretariatan')->group(function () {
                Route::get('disposisi/{id}', [DisposisiMasukController::class, 'showForKesekretariatan'])
                    ->name('kesekretariatan.disposisi.show');
            });
        });

        Route::get('/wpp/test', [WppTestController::class, 'index'])->name('wpp.test');
        Route::post('/wpp/test/send', [WppTestController::class, 'sendTest'])->name('wpp.test.send');


        // Nota Dinas
        Route::prefix('nota-dinas')->group(function () {

            Route::get('/', [NotaDinasController::class, 'index'])->name('nota.index');
            Route::get('/create', [NotaDinasController::class, 'create'])->name('nota.create');
            Route::post('/', [NotaDinasController::class, 'store'])->name('nota.store');

            Route::get('/{id}', [NotaDinasController::class, 'show'])->name('nota.show');

            // reply form
            Route::get(
                '/inbox/reply/{id}',
                [NotaDinasController::class, 'reply']
            )->name('nota.inbox.reply');

            // simpan balasan
            Route::post(
                '/inbox/reply/{id}',
                [NotaDinasController::class, 'replyStore']
            )->name('nota.inbox.reply.store');

            Route::delete(
                '/lampiran/{id}/hapus',
                [NotaDinasController::class, 'hapusLampiran']
            )->name('nota.inbox.lampiran.hapus');

            Route::get('/inbox/{id}/print', [NotaDinasController::class, 'printView'])
                ->name('nota.inbox.print');




            Route::get('/{id}/edit', [NotaDinasController::class, 'edit'])->name('nota.edit');
            Route::put('/{id}', [NotaDinasController::class, 'update'])->name('nota.update');

            // Validasi manager
            Route::get('/inbox/validasi/{penerima}', [NotaDinasController::class, 'showValidasi'])
                ->name('nota.inbox.validasi.show');

            Route::post('/inbox/validasi/{penerima}', [NotaDinasController::class, 'approveValidasi'])
                ->name('nota.inbox.validasi.approve');

            Route::post('/inbox/validasi/{penerimaId}/reject', [NotaDinasController::class, 'rejectValidasi'])
                ->name('nota.inbox.validasi.reject');


            Route::post('/inbox/{id}/selesai', [NotaDinasController::class, 'tandaiSelesai'])
                ->name('nota.inbox.selesai');
        });

        Route::get('/nota-masuk', [NotaDinasController::class, 'inbox'])
            ->name('nota.inbox');

        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

        Route::middleware([RoleMiddleware::class . ':kesekretariatan'])->group(function () {
            Route::resource('positions', PositionController::class);
        });

        Route::get('/sotk', function () {
            $positions = \App\Models\Position::all();
            return view('sotk', compact('positions'));
        });

        // VALIDASI & TOLAK oleh Manajer
        Route::get('/surat_masuk/{id}/validasi', [SuratMasukController::class, 'validasi'])
            ->name('surat_masuk.validasi');
        Route::post('/surat_masuk/validasi_popup', [SuratMasukController::class, 'validasiPopup'])
            ->name('surat_masuk.validasi_popup');


        Route::get('/surat_masuk/{id}/tolak', [SuratMasukController::class, 'tolak'])
            ->name('surat_masuk.tolak');

        Route::prefix('template')->name('template.')->group(function () {

            Route::get('/', [TemplateController::class, 'index'])->name('index');
            Route::get('/create', [TemplateController::class, 'create'])->name('create');
            Route::post('/store', [TemplateController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [TemplateController::class, 'edit'])->name('edit');
            Route::post('/{id}/update', [TemplateController::class, 'update'])->name('update');
            Route::delete('/{id}/delete', [TemplateController::class, 'destroy'])->name('delete');
            Route::get('/view/{id}', [TemplateController::class, 'view'])->name('template.view');
            Route::get('/{id}/preview', [TemplateController::class, 'preview'])->name('template.preview');
            Route::get('/raw/{id}', [TemplateController::class, 'raw'])->name('template.raw');
        });
    });
});
