<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Route Rahasia buat Admin
Route::get('/bikin-admin-miranda', function () {
    // 1. Cek apakah user sudah ada?
    $user = User::where('email', 'miranda@gmail.com')->first();
    
    if (!$user) {
        // Kalau belum ada, buat baru
        $user = new User();
        $user->email = 'miranda@gmail.com';
        $user->name = 'Miranda Admin';
        $user->password = Hash::make('123456789');
        $user->phone_number = '081234567890';
        $user->address = 'Baktiserage, buleleng';
        $user->role = 'admin'; // SET JADI ADMIN
        $user->save();
        return "Sukses! User Admin Baru Dibuat.";
    } else {
        // Kalau sudah ada, update jadi admin
        $user->role = 'admin';
        $user->password = Hash::make('123456789'); // Reset password biar yakin
        $user->save();
        return "Sukses! User Lama Diubah Jadi Admin.";
    }
});

Route::get('/', function () {
    return view('welcome');
});
