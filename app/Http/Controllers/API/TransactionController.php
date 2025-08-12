<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\Store;
use App\Models\BoardingHouse;
use App\Models\Room;
use App\Models\Transaction;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function index()
    {
        $transaction = Transaction::with('boardingHouse', 'room', 'room.images')
            ->whereUserId(auth()->id())
            ->paginate();

        // Akses data transaksi menggunakan properti 'items'
        foreach ($transaction->items() as $transactionData) {
            foreach ($transactionData->room->images as $image) {
                // Menambahkan URL lengkap gambar
                $image->image_url = asset('storage/boarding-houses/rooms/' . $image->image);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'List of transactions',
            'data' => $transaction,
        ]);
    }



    private function _fullyBookedChecker(Store $request)
    {
        // Ambil boarding house
        $boardingHouse = BoardingHouse::find($request->boarding_house_id);
        if (!$boardingHouse) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Boarding house not found.',
                ], JsonResponse::HTTP_NOT_FOUND)
            );
        }

        // Ambil room
        $room = Room::find($request->room_id);
        if (!$room) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Room not found.',
                ], JsonResponse::HTTP_NOT_FOUND)
            );
        }

        // Cek apakah room tersebut milik boarding house yang dipilih
        if ($room->boarding_house_id !== $boardingHouse->id) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Selected room does not belong to the selected boarding house.',
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        // Cek apakah room tersedia
        if (!$room->is_available) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Selected room is not available.',
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        // Cek jumlah transaksi aktif untuk room yang sama
        $runningTransactionsCount = Transaction::where('room_id', $room->id)
            ->whereNot('payment_status', 'canceled')
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($subQuery) use ($request) {
                        $subQuery->where('start_date', '<', $request->start_date)
                            ->where('end_date', '>', $request->end_date);
                    });
            })
            ->count();

        if ($runningTransactionsCount >= $room->capacity) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Room is fully booked for the selected dates.',
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        return true;
    }

    public function isAvailable(Store $request)
    {
        $this->_fullyBookedChecker($request);

        return response()->json([
            'success' => true,
            'message' => 'Room is available for booking.',
        ]);
    }

    public function store(Store $request)
    {
        $this->_fullyBookedChecker($request);

        $transaction = Transaction::create([
            'boarding_house_id' => $request->boarding_house_id,
            'room_id' => $request->room_id,
            'user_id' => auth()->id(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully.',
            'data' => $transaction->load(['boardingHouse', 'room']),
        ]);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        if ($transaction->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this transaction.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        // Memuat gambar dengan URL lengkap
        $transactionData = $transaction->load(['boardingHouse', 'room', 'room.images']);

        // Tambahkan URL penuh untuk gambar
        foreach ($transactionData->room->images as $image) {
            $image->image_url = asset('storage/boarding-houses/rooms/' . $image->image);
        }

        // Kirim data transaksi lengkap dengan gambar
        return response()->json([
            'success' => true,
            'message' => 'Transaction details',
            'data' => $transactionData,
        ]);

    }


}
