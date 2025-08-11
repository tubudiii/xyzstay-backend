<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\JsonResponse;

class RoomController extends Controller
{
    public function showForCheckout($slug)
    {
        $room = Room::with([
            'boardingHouse.city',
            'boardingHouse.category',
            'images'
        ])->where('slug', $slug)->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Room data for checkout',
            'data' => $room
        ]);
    }
}
