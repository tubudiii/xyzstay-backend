<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardingHouseResource;
use App\Models\Room;
use Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\BoardingHouse;

class BoardingHouseController extends Controller
{

    public function recommend(Request $request): JsonResponse
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required',
            ], 400);
        }

        // Call FastAPI endpoint
        $response = Http::post("https://xyz-recomender.trisnautama.site/recommend", [
            'user_id' => (string) $userId,
            'ratings' => []
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get recommendation from FastAPI',
            ], 500);
        }

        $responseData = $response->json();

        if (!$responseData || !is_array($responseData)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid recommendation result',
            ], 500);
        }

        // Buat map [boarding_house_id => predicted_score]
        $scoreMap = collect($responseData)->mapWithKeys(function ($item) {
            return [$item['boarding_house_id'] => $item['predicted_score']];
        });

        $recommendedIds = $scoreMap->keys()->toArray();

        if (empty($recommendedIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No recommendation IDs received',
            ], 500);
        }

        // Ambil boarding house sesuai urutan rekomendasi
        $boardingHouses = BoardingHouse::with(['testimonials'])
            ->whereIn('id', $recommendedIds)
            ->orderByRaw('FIELD(id, ' . implode(',', $recommendedIds) . ')')
            ->get();

        // Tambahkan predicted_score ke setiap house
        $boardingHousesWithScore = $boardingHouses->map(function ($house) use ($scoreMap) {
            $house->predicted_score = $scoreMap[$house->id] ?? null;
            return $house;
        });

        return response()->json([
            'success' => true,
            'message' => 'Recommended Boarding Houses',
            'data' => $boardingHousesWithScore,
        ]);
    }


    public function index(): JsonResponse
    {
        $perPage = request()->get('per_page', 15); // default tetap 15 kalau tidak dikirim

        $boardingHouses = BoardingHouse::with([
            'city',
            'category',
            'rooms.images',
            'testimonials'
        ])
            ->withCount('transactions')
            ->withAvg('testimonials', 'rating') // ⭐️ hitung rata-rata rating
            ->orderBy('transactions_count', 'desc')
            ->paginate($perPage);

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
