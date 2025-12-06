<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'species',
        'breed',
        'age',
        'gender',
        'description',
        'health_status',
        'image_url', // Foto Utama
        'status',
        'admin_note', // <--- TAMBAHKAN INI
    ];

    // --- RELASI ---

    // 1. Pemilik Awal (Donatur)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 2. Riwayat Adopsi
    public function adoptions()
    {
        return $this->hasMany(Adoption::class, 'pet_id');
    }
    
    // 3. Galeri Foto Tambahan (INI PENYEBAB ERROR 500 JIKA HILANG)
    public function images()
    {
        return $this->hasMany(PetImage::class, 'pet_id');
    }
    
    // 4. Wishlist
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}