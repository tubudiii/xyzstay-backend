<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\Room;

class Store extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasRole('customer');
    }

    public function rules(): array
    {
        return [
            'boarding_house_id' => 'required|exists:boarding_houses,id',
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $room = Room::find($this->room_id);

            if (!$room) {
                $validator->errors()->add('room_id', 'Room not found.');
            } elseif (!$room->is_available) {
                $validator->errors()->add('room_id', 'Selected room is not available.');
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
