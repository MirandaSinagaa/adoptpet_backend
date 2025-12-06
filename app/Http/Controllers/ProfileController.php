<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    // 1. UPDATE BIODATA (Nama, HP, Alamat)
    public function updateInfo(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui.',
            'data' => $user
        ]);
    }

    // 2. UPDATE PASSWORD
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed', // butuh new_password_confirmation
        ]);

        $user = $request->user();

        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password lama salah.'
            ], 400);
        }

        // Update password baru
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diganti.'
        ]);
    }

    // 3. UPDATE AVATAR (FOTO PROFIL)
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada (dan bukan link eksternal)
            if ($user->avatar && Storage::disk('public')->exists(str_replace(url('storage/'), '', $user->avatar))) {
                // Logic hapus file lama (opsional, biar storage hemat)
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $url = url('storage/' . $path);

            $user->update(['avatar' => $url]);

            return response()->json([
                'status' => 'success',
                'message' => 'Foto profil diperbarui.',
                'data' => $user
            ]);
        }

        return response()->json(['message' => 'Gagal upload.'], 400);
    }
}