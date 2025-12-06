<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('adoptions', function (Blueprint $table) {
            $table->id();
            // Relasi ke user yang ingin mengadopsi (calon adopter)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Relasi ke hewan yang ingin diadopsi
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            
            $table->text('reason'); // Alasan ingin mengadopsi
            $table->text('environment_desc'); // Kondisi rumah/lingkungan
            
            // Status pengajuan adopsi
            // pending: Menunggu admin cek
            // approved: Disetujui admin (pet status akan berubah jadi adopted)
            // rejected: Ditolak admin
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            $table->text('admin_note')->nullable(); // Alasan jika ditolak atau catatan tambahan
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adoptions');
    }
};