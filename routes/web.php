<?php

use App\Http\Controllers\ForbiddenContentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Jetstream;

Route::group(['middleware' => ['verified']], function () {
    Route::get('/', Home::class)
        ->name('home');

    require __DIR__.'/resources/page.php';
    require __DIR__.'/resources/story.php';
    require __DIR__.'/resources/story-comment.php';
});

Route::group(['middleware' => ['auth:sanctum', 'json']], function () {
    require __DIR__.'/resources/user.php';
});

Route::get('/age-rating-guidelines', [PageController::class, 'ageRatings'])->name('age-ratings.show');
Route::get('/cookie-policy', [PageController::class, 'cookie'])->name('cookie.show');

if (Jetstream::hasTermsAndPrivacyPolicyFeature()) {
    Route::get('/terms-of-service', [PageController::class, 'terms'])->name('terms.show');
    Route::get('/privacy-policy', [PageController::class, 'policy'])->name('policy.show');
}

Route::get('/restricted', ForbiddenContentController::class)->name('content.forbidden');

Route::get('/age-not-allowed', fn () => view('age-not-allowed')
)->name('age.not-allowed');

Route::get('/sitemap.xml', [SitemapController::class, 'index']);

Route::fallback([PageController::class, 'fallback']);
