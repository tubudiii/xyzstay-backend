<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'code',
        'boarding_house_id',
        'room_id',
        'name',
        'email',
        'phone_number',
        'start_date',
        'end_date',
        'price_per_day',
        'total_days',
        'fee',
        'total_price',
        'payment_method',
        'payment_status',
        'transaction_date',
    ];

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function setBoardingHouseAttribute($value)
    // {
    //     $boardingHouse = BoardingHouse::find($value);
    //     $totalDays = Carbon::createFromDate($this->attributes['start_date'])->diffInDays($this->attributes['end_date']) + 1;
    //     $totalPrice = $boardingHouse->price_per_day * $totalDays;
    //     $fee = $totalPrice * 0.1; // Assuming a 10% fee

    //     $this->attributes['boarding_house_id'] = $value;
    //     $this->attributes['price_per_day'] = $boardingHouse->price_per_day;
    //     $this->attributes['total_days'] = $totalDays;
    //     $this->attributes['fee'] = $fee;
    //     $this->attributes['total_price'] = $totalPrice;
    // }

    // EVENT creating

    protected static function booted(): void
    {
        static::creating(function ($transaction) {
            $user = Auth::user();

            // Generate kode transaksi jika belum ada
            if (empty($transaction->code)) {
                $transaction->code = 'XYZ-' . now()->format('Ymd-His') . '-' . strtoupper(uniqid());
            }

            // Isi name & email dari user yang login jika belum diisi
            if ($user) {
                $transaction->user_id = $user->id;

                if (empty($transaction->name)) {
                    $transaction->name = $user->name;
                }

                if (empty($transaction->email)) {
                    $transaction->email = $user->email;
                }
            }

            // Pastikan tanggal ada
            if (!$transaction->start_date || !$transaction->end_date) {
                return;
            }

            // Hitung total hari
            $totalDays = Carbon::parse($transaction->start_date)
                ->diffInDays(Carbon::parse($transaction->end_date)) + 1;

            // Ambil room berdasarkan ID
            $room = \App\Models\Room::find($transaction->room_id);

            if ($room) {
                $pricePerDay = $room->price_per_day;
                $totalPrice = $pricePerDay * $totalDays;
                $fee = $totalPrice * 0.1;

                // Isi field transaksi berdasarkan room
                $transaction->price_per_day = $pricePerDay;
                $transaction->total_days = $totalDays;
                $transaction->fee = $fee;
                $transaction->total_price = $totalPrice;
            }
        });
    }

}
