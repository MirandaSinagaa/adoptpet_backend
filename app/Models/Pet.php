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
        'image_url',
        'status',
        'admin_note',
        'cancellation_reason', // <--- BARU
    ];

    // --- RELASI ---
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function adoptions()
    {
        return $this->hasMany(Adoption::class, 'pet_id');
    }
    
    public function images()
    {
        return $this->hasMany(PetImage::class, 'pet_id');
    }
    
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}