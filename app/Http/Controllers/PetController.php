<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\PetImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PetController extends Controller
{
    // 1. PUBLIC: Get All Available
    public function index()
    {
        $pets = Pet::with('user:id,name')
            ->where('status', 'available')
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'data' => $pets]);
    }

    // 2. PUBLIC: Detail
    public function show($id)
    {
        $pet = Pet::with(['user:id,name', 'images'])->find($id);

        if (!$pet) {
            return response()->json(['status' => 'error', 'message' => 'Hewan tidak ditemukan'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $pet]);
    }

    // 3. USER: Create Donation
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'extra_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('pets', 'public');
            $imagePath = url('storage/' . $path);
        }

        $pet = Pet::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'species' => $request->species,
            'breed' => $request->breed,
            'age' => $request->age,
            'gender' => $request->gender,
            'description' => $request->description,
            'health_status' => $request->health_status,
            'image_url' => $imagePath,
            'status' => 'pending_review',
        ]);

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

        return response()->json(['status' => 'success', 'data' => $pets]);
    }

    // 5. [BARU] USER: Cancel Donation
    public function cancelDonation(Request $request, $id)
    {
        // Cari hewan milik user ini
        $pet = Pet::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$pet) {
            return response()->json(['status' => 'error', 'message' => 'Hewan tidak ditemukan atau bukan milik Anda.'], 404);
        }

        // Cek status, tidak boleh cancel kalau sudah adopted
        if ($pet->status === 'adopted') {
            return response()->json(['status' => 'error', 'message' => 'Tidak bisa membatalkan donasi yang sudah diadopsi orang lain.'], 400);
        }

        $request->validate([
            'reason' => 'required|string|min:5'
        ]);

        $pet->update([
            'status' => 'canceled',
            'cancellation_reason' => $request->reason
        ]);

        return response()->json(['status' => 'success', 'message' => 'Donasi berhasil dibatalkan.']);
    }
}