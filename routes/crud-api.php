<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;


Route::middleware('auth:api')->group(function () {
    Route::controller(EventController::class)->group(function () {
        Route::get('/events', 'readAll');
        Route::post('/events/create', 'createOne');
    
    });
    Route::controller(UploadController::class)->group(
        function () {
            Route::post('/', 'createOne');
            Route::get('/{id}', 'readOne');
            Route::get('/test', 'readAll');
            Route::post('/{id}', 'updateOne');
            Route::delete('/{id}', 'deleteOne');
            Route::delete('/', 'deleteMulti');
        }
    );
});




// Route::controller(EventController::class)->group(
//     function () {
//         // Route::post('/', 'createOne');
//         // Route::get('/{id}', 'readOne');
//         Route::get('/events', 'readAll');
//         // Route::put('/{id}', 'updateOne');
//         // Route::patch('/{id}', 'patchOne');
//         // Route::delete('/{id}', 'deleteOne');
//     }
// );




    // function () {
    //     Route::controller(UploadController::class)->group(
    //         function () {
    //             Route::post('/', 'createOne');
    //             Route::get('/{id}', 'readOne');
    //             Route::get('/test', 'readAll');
    //             Route::post('/{id}', 'updateOne');
    //             Route::delete('/{id}', 'deleteOne');
    //             Route::delete('/', 'deleteMulti');
    //         }
    //     );
    // };

