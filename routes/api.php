<?php

use App\Http\Controllers\API\BoardingHouseController;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\RoomController;
use App\Http\Controllers\API\TestimonialController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->put('user/{id}', [UserController::class, 'update']);
Route::middleware('auth:sanctum')->get('user/{id}', [UserController::class, 'show']);
Route::middleware('auth:sanctum')->get(
    '/recommendations',
    [BoardingHouseController::class, 'recommend']
);
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Detail Login User',
        'data' => [
            'user' => $request->user(),
            'roles' => $request->user()->getRoleNames(), // ← Tambahkan baris ini
        ],
    ]);
});

Route::get('cities', [CityController::class, 'index']);
Route::resource('boarding-house', BoardingHouseController::class)->only('index', 'show');

Route::post('transaction/is-available', [TransactionController::class, 'isAvailable'])
    ->middleware('auth:sanctum');

Route::get('boarding-house/by-room/{slug}', [BoardingHouseController::class, 'showByRoomSlug']);

// routes/api.php
Route::get('room/{slug}/checkout', [RoomController::class, 'showForCheckout']);


Route::resource('transaction', TransactionController::class)
    ->only(['store', 'index', 'show'])
    ->middleware('auth:sanctum');

//categories
Route::get('categories', [CategoriesController::class, 'index']);
Route::post('categories', [CategoriesController::class, 'store']);
Route::get('categories/{slug}', [CategoriesController::class, 'showCategoryBySlug']);

// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/testimonial', [TestimonialController::class, 'store']);
    Route::put('/testimonial/{id}', [TestimonialController::class, 'update']);
});
Route::get('/testimonial/{slug?}', [TestimonialController::class, 'view']);


Route::put('payment/{id}', [PaymentController::class, 'update'])->middleware('auth:sanctum');
Route::post('midtrans/webhook', [PaymentController::class, 'webHookHandler']);



require __DIR__ . '/auth.php';
