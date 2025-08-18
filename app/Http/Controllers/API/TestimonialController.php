<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TestimonialRequest; // Optional: jika Anda membuat request validation
use App\Models\BoardingHouse;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;


class TestimonialController extends Controller
{
    /**
     * Store a new testimonial.
     */
    public function store(Request $request)
    {
        \Log::info('AUTH_USER', ['user' => auth()->user(), 'id' => auth()->id()]);
        // Validasi input
        $validated = $request->validate([
            'boarding_house_id' => 'required|exists:boarding_houses,id',
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'rating' => 'required|integer|between:1,5',
            'photo' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        // Cek apakah user sudah punya testimonial di boarding house ini
        $existing = Testimonial::where('boarding_house_id', $validated['boarding_house_id'])
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial already exists for this user and boarding house.',
                'data' => $existing,
            ], 409);
        }

        // Proses foto jika ada
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('testimonials', 'public');
        } else {
            $photoPath = null;
        }

        // Simpan testimonial
        $testimonial = Testimonial::create([
            'boarding_house_id' => $validated['boarding_house_id'],
            'user_id' => auth()->id(), // pastikan user sudah login
            'name' => $validated['name'],
            'content' => $validated['content'],
            'rating' => $validated['rating'],
            'photo' => $photoPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial created successfully.',
            'data' => $testimonial,
        ], 201);
    }


    public function update(Request $request, $id)
    {

        $testimonial = Testimonial::find($id);
        \Log::info('TESTIMONIAL_UPDATE', [
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'all' => $request->all(),
            'has_name' => $request->has('name'),
        ]);
        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found.',
            ], 404);
        }

        // Validasi (partial)
        $validated = validator($request->all(), [
            'boarding_house_id' => 'sometimes|exists:boarding_houses,id',
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'rating' => 'sometimes|integer|between:1,5',
            'photo' => 'sometimes|nullable|image|mimes:jpg,png,jpeg,gif|max:2048',
            'remove_photo' => 'sometimes|boolean',
        ])->validate();

        // Ambil field non-file utk di-update
        $dataToUpdate = Arr::except($validated, ['photo', 'remove_photo']);

        // Isi dulu data teks/angka
        if (!empty($dataToUpdate)) {
            $testimonial->fill($dataToUpdate);
        }

        // Hapus foto lama jika diminta
        if ($request->boolean('remove_photo') && $testimonial->photo) {
            if (Storage::disk('public')->exists($testimonial->photo)) {
                Storage::disk('public')->delete($testimonial->photo);
            }
            $testimonial->photo = null;
        }

        // Ganti foto jika ada upload baru
        if ($request->hasFile('photo')) {
            if ($testimonial->photo && Storage::disk('public')->exists($testimonial->photo)) {
                Storage::disk('public')->delete($testimonial->photo);
            }
            $testimonial->photo = $request->file('photo')->store('testimonials', 'public');
        }

        // Simpan hanya jika ada perubahan
        $testimonial->save();


        // Ambil ulang dari DB biar timestamp dan casting pasti terbaru
        $testimonial->refresh();

        // Tambah URL foto (jika ada)
        $testimonial->photo_url = $testimonial->photo ? asset('storage/' . $testimonial->photo) : null;

        return response()->json([
            'success' => true,
            'message' => 'Testimonial updated successfully.',
            'data' => $testimonial,
        ]);
    }

    public function view($slug = null)
    {
        if ($slug) {
            // Cari boarding house berdasarkan slug
            $boardingHouse = BoardingHouse::where('slug', $slug)->first();

            // Jika boarding house tidak ditemukan
            if (!$boardingHouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Boarding house not found.',
                ], 404);
            }

            // Ambil testimonial berdasarkan boarding house id
            $testimonials = $boardingHouse->testimonials;
        } else {
            // Ambil semua testimonial jika slug tidak ada
            $testimonials = Testimonial::all();
        }

        // Menambahkan URL penuh untuk foto
        foreach ($testimonials as $testimonial) {
            if ($testimonial->photo) {
                $testimonial->photo_url = asset('storage/' . $testimonial->photo);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Testimonials fetched successfully.',
            'data' => $testimonials,
        ]);
    }

}
