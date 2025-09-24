<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BoardingHouseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'thumbnail' => $this->thumbnail,
            'city_id' => $this->city_id,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'address' => $this->address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'testimonials' => $this->whenLoaded('testimonials'),
            // 🚀 predicted_score ditambahkan manual
            'predicted_score' => $this->predicted_score ?? null,
            // 'average_rating' => round($this->testimonials_avg_rating, 1), // contoh hasil 4.3
            'rooms' => $this->whenLoaded('rooms'),

        ];
    }
}
