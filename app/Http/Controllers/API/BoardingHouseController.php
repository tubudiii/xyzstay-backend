<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\BoardingHouse;

class BoardingHouseController extends Controller
{
    public function index(): JsonResponse
    {
        $boardingHouses = BoardingHouse::with([
            'city',
            'category',
            'rooms.images', // tambahkan eager load images
            'testimonials'
        ])
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
            'data' => $boardingHouse->load([
                'city',
                'category',
                'rooms.images', // tambahkan eager load images
                'testimonials'
            ]),
        ]);
    }

    public function showByRoomSlug($slug): JsonResponse
    {
        $room = Room::where('slug', $slug)
            ->with([
                'boardingHouse.city',
                'boardingHouse.category',
                'boardingHouse.rooms.images',
                'boardingHouse.testimonials'
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Get Boarding House by Room Slug',
            'data' => $room->boardingHouse,
        ]);
    }

}
