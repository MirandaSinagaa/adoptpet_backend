<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adoption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pet_id',
        'reason',
        'environment_desc',
        'status',
        'admin_note',
    ];

    // --- RELASI ---

    // Pengajuan adopsi dilakukan oleh satu User (Calon Adopter)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Pengajuan adopsi tertuju pada satu Hewan
    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}