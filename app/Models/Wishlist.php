<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id', 'pet_id'];
    
    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}