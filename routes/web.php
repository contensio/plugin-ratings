<?php

use Contensio\Ratings\Http\Controllers\Admin\RatingsController;
use Contensio\Ratings\Http\Controllers\Frontend\RateController;
use Illuminate\Support\Facades\Route;

// ── Admin routes ─────────────────────────────────────────────────────────────

Route::prefix(config('contensio.route_prefix', 'account'))
    ->middleware(['web', 'contensio.auth', 'contensio.admin'])
    ->group(function () {
        Route::get('/ratings',                    [RatingsController::class, 'index']) ->name('contensio-ratings.index');
        Route::delete('/ratings/{contentId}/reset', [RatingsController::class, 'reset']) ->name('contensio-ratings.reset');
    });

// ── Public JSON API ───────────────────────────────────────────────────────────

Route::middleware('web')->group(function () {
    Route::post('/ratings/{contentId}', [RateController::class, 'rate'])    ->name('contensio-ratings.rate');
    Route::get('/ratings/{contentId}',  [RateController::class, 'summary']) ->name('contensio-ratings.summary');
});
