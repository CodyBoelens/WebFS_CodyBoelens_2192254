<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Website\MenuPdfController;
use App\Http\Controllers\Website\TakeawayController;
use App\Http\Controllers\Kassa\KassaController;
use App\Http\Controllers\Tablet\TabletController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- Taal wisselen (UC-13) ---
Route::post('/locale/{locale}', LocaleController::class)->name('locale.switch');

// --- Publieke website ---
Route::get('/', fn() => Inertia::render('Website/Home', [
    'promotions' => \App\Models\Promotion::active()
        ->with(['products' => fn($q) => $q->with('promotions')])
        ->orderBy('valid_until', 'asc')
        ->limit(1)
        ->get()
        ->map(fn($promo) => [
            'id'               => $promo->id,
            'name'             => $promo->name,
            'description'      => $promo->description,
            'discount_percent' => $promo->discount_percent,
            'discount_amount'  => $promo->discount_amount,
            'valid_from'       => $promo->valid_from->toDateString(),
            'valid_until'      => $promo->valid_until->toDateString(),
            'products'         => $promo->products->map(fn($p) => [
                'id'            => $p->id,
                'menu_number'   => $p->menu_number,
                'name'          => $p->name,
                'price'         => (float) $p->price,
                'current_price' => (float) $p->current_price,
            ]),
        ]),
]))->name('home');
Route::get('/nieuws', fn() => Inertia::render('Website/Nieuws', ['nieuws' => []]))->name('nieuws');
Route::get('/contact', fn() => Inertia::render('Website/Contact'))->name('contact');
Route::get('/menu', fn() => Inertia::render('Website/Menu', [
    'categories' => \App\Models\Category::where('active', true)
        ->orderBy('sort_order')
        ->get()
        ->map(fn($cat) => [
            'id'   => $cat->id,
            'name' => $cat->name,
            'products' => \App\Models\Product::with('promotions')
                ->where('category_id', $cat->id)
                ->where('active', true)
                ->orderByRaw("CAST(menu_number AS INTEGER), LENGTH(menu_number), menu_number")
                ->get()
                ->map(fn($p) => [
                    'id'            => $p->id,
                    'menu_number'   => $p->menu_number,
                    'name'          => $p->name,
                    'description'   => $p->description,
                    'price'         => (float) $p->price,
                    'current_price' => (float) $p->current_price,
                    'image'         => $p->image ? asset('storage/' . $p->image) : null,
                    'allergens'     => $p->allergens ?? [],
                ]),
        ]),
]))->name('menu');
Route::get('/aanbiedingen', fn() => Inertia::render('Website/Aanbiedingen', [
    'promotions' => \App\Models\Promotion::active()
        ->with(['products' => fn($q) => $q->with('promotions')])
        ->get()
        ->map(fn($promo) => [
            'id'               => $promo->id,
            'name'             => $promo->name,
            'description'      => $promo->description,
            'discount_percent' => $promo->discount_percent,
            'discount_amount'  => $promo->discount_amount,
            'valid_from'       => $promo->valid_from->toDateString(),
            'valid_until'      => $promo->valid_until->toDateString(),
            'products'         => $promo->products->map(fn($p) => [
                'id'            => $p->id,
                'menu_number'   => $p->menu_number,
                'name'          => $p->name,
                'price'         => (float) $p->price,
                'current_price' => (float) $p->current_price,
            ]),
        ]),
]))->name('aanbiedingen');
Route::get('/bestellen', fn() => Inertia::render('Website/Bestellen', [
    'products'   => \App\Models\Product::with(['category', 'promotions'])->where('active', true)->get()->map(fn($p) => [
        'id'            => $p->id,
        'menu_number'   => $p->menu_number,
        'name'          => $p->name,
        'price'         => $p->price,
        'current_price' => $p->current_price,
        'category_id'   => $p->category_id,
    ]),
    'categories' => \App\Models\Category::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
]))->name('bestellen');

Route::get('/menu/pdf', MenuPdfController::class)->name('menu.pdf');

// UC-16: Afhaal bestelling
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
    Route::post('/betalen', [TabletController::class, 'markPaid'])->name('paid');
});

// --- Admin (beveiligd) ---
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // UC-20: Menu CRUD
    Route::resource('menu', MenuController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('promotions', PromotionController::class)->except(['show']);

    // UC-19: Verkoop rapportages
    Route::get('rapportages', [SalesReportController::class, 'index'])->name('rapportages.index');
    Route::get('rapportages/{report}/download', [SalesReportController::class, 'download'])->name('rapportages.download');

    // Bestellingen beheren
    Route::get('bestellingen', [\App\Http\Controllers\Admin\OrdersController::class, 'index'])->name('bestellingen.index');
    Route::put('bestellingen/{order}/status', [\App\Http\Controllers\Admin\OrdersController::class, 'updateStatus'])->name('bestellingen.status');

    // US-5: Hulpverzoeken beheer (redirect na dismiss via web)
    Route::get('hulpverzoeken', fn() => Inertia::render('Admin/HulpVerzoeken'))->name('hulpverzoeken');
    Route::put('hulpverzoeken/{helpRequest}/afmelden', [\App\Http\Controllers\Admin\HelpRequestWebController::class, 'dismiss'])->name('hulpverzoeken.dismiss');
});

// --- Auth ---
Route::get('/login', fn() => Inertia::render('Auth/Login'))->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'destroy'])->name('logout');
