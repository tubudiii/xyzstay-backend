<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'boarding_house_id',
        'name',
        'slug',
        'room_type',
        'square_feet',
        'capacity',
        'price_per_day',
        'is_available',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }
    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
