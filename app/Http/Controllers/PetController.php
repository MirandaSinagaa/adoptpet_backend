<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\PetImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PetController extends Controller
{
    // 1. PUBLIC: Get All Available Pets
    public function index()
    {
        $pets = Pet::with('user:id,name')
            ->where('status', 'available')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pets
        ]);
    }

    // 2. PUBLIC: Get Single Pet Detail (Include Extra Images)
    public function show($id)
    {
        // Load relasi 'images' untuk foto tambahan
        $pet = Pet::with(['user:id,name', 'images'])->find($id);

        if (!$pet) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hewan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pet
        ]);
    }

    // 3. USER: Create Donation (Support Multi-Upload)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'species' => 'required|in:cat,dog,other',
            'breed' => 'nullable|string|max:255',
            'age' => 'nullable|integer',
            'gender' => 'required|in:male,female',
            'description' => 'required|string',
            'health_status' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Foto Utama
            'extra_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Foto Tambahan (Array)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // 1. Simpan Foto Utama
        $mainImagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('pets', 'public');
            $mainImagePath = url('storage/' . $path);
        }

        // 2. Buat Data Pet
        $pet = Pet::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'species' => $request->species,
            'breed' => $request->breed,
            'age' => $request->age,
            'gender' => $request->gender,
            'description' => $request->description,
            'health_status' => $request->health_status,
            'image_url' => $mainImagePath,
            'status' => 'pending_review',
        ]);

        // 3. Simpan Foto Tambahan (Jika ada)
        if ($request->hasFile('extra_images')) {
            foreach ($request->file('extra_images') as $file) {
                $path = $file->store('pets_extra', 'public');
                PetImage::create([
                    'pet_id' => $pet->id,
                    'image_url' => url('storage/' . $path)
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan donasi berhasil dikirim.',
            'data' => $pet
        ], 201);
    }

    // 4. USER: Get My Donations
    public function myDonations(Request $request)
    {
        $pets = Pet::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pets
        ]);
    }
}