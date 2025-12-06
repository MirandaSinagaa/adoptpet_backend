<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    // 1. Get My Wishlist
    public function index(Request $request)
    {
        $wishlists = Wishlist::with('pet')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();
            
        return response()->json(['status' => 'success', 'data' => $wishlists]);
    }

    // 2. Toggle Wishlist (Add/Remove)
    public function toggle(Request $request)
    {
        $request->validate(['pet_id' => 'required|exists:pets,id']);
        
        $user_id = $request->user()->id;
        $pet_id = $request->pet_id;
        
        // Cek apakah sudah ada
        $existing = Wishlist::where('user_id', $user_id)->where('pet_id', $pet_id)->first();
        
        if ($existing) {
            $existing->delete();
            return response()->json(['status' => 'success', 'message' => 'Dihapus dari favorit', 'is_wishlisted' => false]);
        } else {
            Wishlist::create(['user_id' => $user_id, 'pet_id' => $pet_id]);
            return response()->json(['status' => 'success', 'message' => 'Ditambahkan ke favorit', 'is_wishlisted' => true]);
        }
    }
    
    // 3. Cek Status Wishlist (Untuk tombol di frontend)
    public function check(Request $request, $id)
    {
        $exists = Wishlist::where('user_id', $request->user()->id)->where('pet_id', $id)->exists();
        return response()->json(['status' => 'success', 'is_wishlisted' => $exists]);
    }
}