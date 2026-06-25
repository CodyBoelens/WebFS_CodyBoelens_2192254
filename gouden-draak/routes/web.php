<?php

use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Api\HelpRequestController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Website\MenuPdfController;
use App\Http\Controllers\Website\TakeawayController;
use App\Http\Controllers\Kassa\KassaController;
use App\Http\Controllers\Tablet\TabletController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Taal wisselen (UC-13) ---
Route::post('/locale/{locale}', LocaleController::class)->name('locale.switch');

// --- Publieke website ---
Route::get('/', fn() => Inertia::render('Website/Home'))->name('home');
Route::get('/menu', fn() => Inertia::render('Website/Menu'))->name('menu');
Route::get('/aanbiedingen', fn() => Inertia::render('Website/Aanbiedingen'))->name('aanbiedingen');

// UC-14: PDF menu download
Route::get('/menu/pdf', MenuPdfController::class)->name('menu.pdf');

// UC-16: Afhaal bestelling
Route::get('/bestellen', fn() => Inertia::render('Website/Bestellen'))->name('bestellen');
Route::post('/bestellen', [TakeawayController::class, 'store'])->name('bestellen.store');
Route::get('/bestellen/bedankt/{order}', [TakeawayController::class, 'bedankt'])->name('bestellen.bedankt');

// --- Kassa (Desktop) ---
Route::prefix('kassa')->name('kassa.')->middleware('auth')->group(function () {
    Route::get('/', [KassaController::class, 'index'])->name('index');
    Route::post('/order', [KassaController::class, 'newOrder'])->name('order.store');
    Route::put('/order/{order}/item/{item}', [KassaController::class, 'updateItem'])->name('order.item.update');
    Route::delete('/order/{order}/item/{item}', [KassaController::class, 'removeItem'])->name('order.item.remove');
    Route::put('/order/{order}/betaald', [KassaController::class, 'markPaid'])->name('order.paid');
});

// --- Tablet ---
Route::prefix('tablet/{table}')->name('tablet.')->group(function () {
    Route::get('/', [TabletController::class, 'index'])->name('index');
    Route::post('/bestellen', [TabletController::class, 'order'])->name('order');
    Route::post('/hulp', [TabletController::class, 'requestHelp'])->name('help');
});

// --- Admin (beveiligd) ---
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', fn() => Inertia::render('Admin/Dashboard'))->name('dashboard');

    // UC-20: Menu CRUD
    Route::resource('menu', MenuController::class);

    // UC-19: Verkoop rapportages
    Route::get('rapportages', [SalesReportController::class, 'index'])->name('rapportages.index');
    Route::get('rapportages/{report}/download', [SalesReportController::class, 'download'])->name('rapportages.download');

    // US-5: Hulpverzoeken beheer
    Route::get('hulpverzoeken', fn() => Inertia::render('Admin/HulpVerzoeken'))->name('hulpverzoeken');
    Route::put('hulpverzoeken/{helpRequest}/afmelden', [HelpRequestController::class, 'dismiss'])->name('hulpverzoeken.dismiss');
});

// --- Auth ---
Route::get('/login', fn() => Inertia::render('Auth/Login'))->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'destroy'])->name('logout');
