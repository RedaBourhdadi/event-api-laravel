<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\NotificationController;


Route::middleware('auth:api')->group(function () {
    Route::controller(EventController::class)->group(function () {
        Route::post('/events/create', 'createOne');
        Route::delete('/events/{id}', 'deleteOne');
    
    });

    Route::controller(AttendeeController::class)->group(
        function () {
            Route::post('/Attendee/create', 'createOne');
            Route::get('/Attendee/{id}', 'readOne');
            Route::get('/Attendee', 'readAll');
            Route::delete('/Attendee/{id}', 'deleteOne');
        }
    );

    Route::get('notifications/count', [NotificationController::class, 'getUnreadCount']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    
    Route::controller(NotificationController::class)->group(function () {
        Route::get('notifications', 'readAll');
        Route::get('notifications/{id}', 'readOne');
        Route::post('notifications', 'createOne');
        Route::delete('notifications/{id}', 'deleteOne');
    });

});

Route::controller(EventController::class)->group(function () {
    Route::get('/', 'readAllEvents');
    Route::get('/events', 'readAll');

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

