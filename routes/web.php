<?php

use App\Http\Controllers\CartItemController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::controller(PublicSiteController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/about', 'about')->name('about');
    Route::get('/services', 'services')->name('services');
    Route::get('/portfolio', 'portfolio')->name('portfolio');
    Route::get('/hire-collection', 'catalogue')->name('catalogue');
    Route::get('/hire-collection/{product:slug}', 'product')->name('catalogue.product');
    Route::get('/hire-basket', 'cart')->name('cart');
    Route::get('/checkout', 'checkout')->name('checkout');
    Route::get('/booking-confirmation/{booking:reference}', 'bookingConfirmation')
        ->middleware('signed')
        ->name('booking.confirmation');
});

Route::post('/hire-basket/items', [CartItemController::class, 'store'])
    ->name('cart.items.store');

Route::get('/deploy/{token}', function (Request $request, $token) {
    $configuredToken = (string) config('services.deploy.token');
    $providedToken = (string) $token;

    abort_if($configuredToken === '', 404);

    abort_unless(
        hash_equals($configuredToken, $providedToken),
        404
    );

    $result = Process::timeout(600)->run(
        '/bin/bash /home/centerthisco/repositories/CenterThis/deploy.sh'
    );

    if ($result->failed()) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Deployment failed. Check storage/logs/deploy.log on the server.',
            'error' => $result->errorOutput(),
        ], 500);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Deployment completed.',
        'output' => $result->output(),
    ]);
});
