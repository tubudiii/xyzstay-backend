<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

require __DIR__ . '/auth.php';
