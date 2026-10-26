<?php

use App\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// -- OAuth Routes (authenticated) ------------------------------------
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/oauth/youtube/redirect', [OAuthController::class, 'youtubeRedirect'])
        ->name('oauth.youtube.redirect');
    Route::get('/oauth/youtube/callback', [OAuthController::class, 'youtubeCallback'])
        ->name('oauth.youtube.callback');
});