<?php

use App\Http\Controllers\Api\Admin\ApplicationConfigController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Models\Mogou;
use App\Repo\Admin\SubMogouRepo\SubMogouStorageUploadRepo;
use App\Services\Api\DataClient;
use HydraStorage\HydraStorage\Service\Option\MediaOption;
use HydraStorage\HydraStorage\Traits\HydraMedia;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Benchmark;
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
        \App\Services\Route\RouteHelper::includedRouteFiles(__DIR__ . '/api');
        Route::controller(ApplicationConfigController::class)->group(function () {
            Route::get('/application-configs', 'index')->name('application-configs.index');
        });

        Route::get("/public/categories", [CategoryController::class, "all"]);
    });



Route::get('/test', function () {
    $watermark = Storage::disk('local')->get('public/wm.png');

    dd((new SubMogouStorageUploadRepo())->getWaterMarkImage());
    $path = storage_path('app/public/template.jpg');
    $uploadedFile = new UploadedFile(
        $path,
        'template.jpg',
        mime_content_type($path), // e.g. image/jpeg
        null,
        true // mark test mode (so Laravel won’t check is_uploaded_file)
    );
    $mediaOption = MediaOption::create()
        ->setQuality(60);

    // if($request->has('water_mark')){
    $mediaOption = $mediaOption->setWaterMark($watermark, 'center', 100);
    // }
    $mediaOption = $mediaOption->get();
    $testClass = (new class () {
        use HydraMedia;
        public function test($template, $mediaOption)
        {
            return $this->storeMedia($template, '', true, $mediaOption, 'local');
        }
    });

    $testClass->test($uploadedFile, $mediaOption);

});