<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\BoardingHouse; // Assuming you have a BoardingHouse model

class BoardingHouseController extends Controller
{
    public function index(): JsonResponse
    {
        $boardingHouses = BoardingHouse::with(['city', 'category', 'rooms', 'testimonials'])
            ->withCount('transactions')
            ->orderBy('transactions_count', 'desc')
            ->paginate();

        return response()->json([
            'success' => true,
            'message' => 'List of Boarding Houses',
            'data' => $boardingHouses,
        ]);
    }


    public function show(BoardingHouse $boardingHouse): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Get Detail Boarding House',
            'data' => $boardingHouse->load(['city', 'category', 'rooms', 'testimonials']),
        ]);
    }
}
