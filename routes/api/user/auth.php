<?php

use App\Http\Controllers\Api\User\AuthController;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Support\Facades\Route;

Route::post('users/login', [AuthController::class, 'login'])->name('user.login')->middleware('guest');

Route::post('users/register', [AuthController::class, 'register'])->name('user.register')->middleware('guest');

Route::post('users/logout', [AuthController::class, 'logout'])->name('user.logout')->middleware('auth:sanctum');

Route::get('/request', function () {

    dd(Browser::platformName().' ( '.Browser::browserFamily().' ) ');
});
