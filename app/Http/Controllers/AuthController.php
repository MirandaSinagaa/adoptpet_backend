<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered; // Import Event Registered

class AuthController extends Controller
{
    // 1. REGISTER (Updated dengan Email Verification)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'phone_number' => $request->phone_number,
            'address' => $request->address,
        ]);

        // KIRIM EMAIL VERIFIKASI
        // Ini akan otomatis mengirim email berisi link ke user
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. Silakan cek email Anda untuk verifikasi.',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    // 2. LOGIN (Cek Manual)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah'
            ], 401);
        }

        // OPSIONAL: Jika ingin melarang login sebelum verifikasi, buka komentar di bawah ini:
        /*
        if (!$user->hasVerifiedEmail()) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'Email belum diverifikasi. Cek inbox Anda.'
             ], 403);
        }
        */

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    // 3. LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }

    // 4. GET USER PROFILE
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
    }

    // 5. VERIFIKASI EMAIL (Handler saat link diklik)
    public function verifyEmail(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['message' => 'Link tidak valid atau kadaluwarsa.'], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // Redirect ke Frontend Vercel (Halaman Login dengan pesan sukses)
        return redirect('https://adoptpet-frontend.vercel.app/login?verified=true');
    }
}