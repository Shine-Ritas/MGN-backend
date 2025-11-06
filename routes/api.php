<?php

use App\Http\Controllers\Api\Admin\ApplicationConfigController;
use App\Http\Controllers\Api\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')
    ->name('api.')
    ->group(function () {
        \App\Services\Route\RouteHelper::includedRouteFiles(__DIR__.'/api');
        Route::controller(ApplicationConfigController::class)->group(function () {
            Route::get('/application-configs', 'index')->name('application-configs.index');
        });

        Route::get('/public/categories', [CategoryController::class, 'all']);
    });

Route::get('/tesdt', function () {

    // $response = $client->request('GET', 'https://api.bunny.net/storagezone/1224107', [
    //     'headers' => [
    //       'AccessKey' => 'a36dd36e-265b-4a53-bf89-5c5b2a8546ea',
    //       'accept' => 'application/json',
    //     ],
    //   ]);

    $storage_size = Http::withHeaders([
        'AccessKey' => config('control.bunnycdn.papi'),
        'accept' => 'application/json',
    ])->get(
        config('control.bunnycdn.api.getZone').config('control.bunnycdn.storage_zone_id')
    )->json();

    // calculate into megabyte only
    $storage_size = $storage_size['StorageUsed'] / 1024 / 1024;
    dd($storage_size);

    return response()->json($storage_size);
});
