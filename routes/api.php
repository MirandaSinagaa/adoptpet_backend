<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AdoptionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- PUBLIC ROUTES ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/pets', [PetController::class, 'index']);
Route::get('/pets/{id}', [PetController::class, 'show']);

Route::get('/articles', [ArticleController::class, 'index']); // User bisa baca


// --- USER ROUTES (Butuh Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']); 

    // Fitur Donasi
    Route::post('/pets/donate', [PetController::class, 'store']); 
    Route::get('/user/donations', [PetController::class, 'myDonations']); 

    // Fitur Adopsi
    Route::post('/adoptions', [AdoptionController::class, 'store']); 
    Route::get('/user/adoptions', [AdoptionController::class, 'myAdoptions']); 
    
    // Fitur Wishlist (BARU)
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);
    Route::get('/wishlist/check/{id}', [WishlistController::class, 'check']);

    // PROFILE ROUTES
    Route::put('/user/info', [ProfileController::class, 'updateInfo']);
    Route::put('/user/password', [ProfileController::class, 'updatePassword']);
    Route::post('/user/avatar', [ProfileController::class, 'updateAvatar']);

    Route::post('/pets/{id}/cancel', [PetController::class, 'cancelDonation']);


    // --- ADMIN ROUTES ---
    Route::middleware('admin')->prefix('admin')->group(function () {
        
        // Manajemen Donasi
        Route::get('/donations', [AdminController::class, 'getPendingDonations']);
        Route::patch('/donations/{id}/approve', [AdminController::class, 'approveDonation']);
        Route::patch('/donations/{id}/reject', [AdminController::class, 'rejectDonation']);
        
        // Admin Edit Hewan (BARU)
        Route::put('/pets/{id}', [AdminController::class, 'updatePet']);

        // Manajemen Adopsi
        Route::get('/adoptions', [AdminController::class, 'getPendingAdoptions']);
        Route::patch('/adoptions/{id}/approve', [AdminController::class, 'approveAdoption']);
        Route::patch('/adoptions/{id}/reject', [AdminController::class, 'rejectAdoption']);

        // DASHBOARD DATA (Route Baru)
    Route::get('/dashboard-overview', [AdminController::class, 'getDashboardOverview']);

        // Master Data Hewan
    Route::get('/pets', [AdminController::class, 'getAllPets']);
    Route::delete('/pets/{id}', [AdminController::class, 'deletePet']);

        // Route Khusus Artikel
    Route::post('/articles', [ArticleController::class, 'store']);   // Crawl & Save
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']); // Hapus
    });

});