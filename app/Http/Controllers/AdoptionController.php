<?php

namespace App\Http\Controllers;

use App\Models\Adoption;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdoptionController extends Controller
{
    // 1. Ajukan Adopsi
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'pet_id' => 'required|exists:pets,id',
            'reason' => 'required|string',
            'environment_desc' => 'required|string', // Penjelasan kondisi lingkungan rumah
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek apakah hewan ini statusnya masih 'available'
        $pet = Pet::find($request->pet_id);
        if ($pet->status !== 'available') {
            return response()->json([
                'status' => 'error',
                'message' => 'Maaf, hewan ini tidak tersedia untuk diadopsi saat ini.'
            ], 400);
        }

        // Cek apakah user sudah pernah request untuk hewan ini (mencegah spam)
        $existing = Adoption::where('user_id', $request->user()->id)
            ->where('pet_id', $request->pet_id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah mengajukan adopsi untuk hewan ini, mohon tunggu konfirmasi admin.'
            ], 400);
        }

        // Simpan Data
        $adoption = Adoption::create([
            'user_id' => $request->user()->id,
            'pet_id' => $request->pet_id,
            'reason' => $request->reason,
            'environment_desc' => $request->environment_desc,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan adopsi berhasil. Admin akan meninjau profil Anda.',
            'data' => $adoption
        ], 201);
    }

    // 2. Lihat Riwayat Adopsi Saya (Untuk Dashboard User)
    public function myAdoptions(Request $request)
    {
        $adoptions = Adoption::with('pet') // Sertakan data hewannya
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $adoptions
        ]);
    }
}