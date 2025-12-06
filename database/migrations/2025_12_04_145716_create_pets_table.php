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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            // Relasi ke user yang mendonasikan (pemilik awal)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->string('name');
            $table->enum('species', ['cat', 'dog', 'other']); // Bisa ditambah jika perlu
            $table->string('breed')->nullable(); // Ras
            $table->integer('age')->nullable(); // Dalam bulan atau tahun (nanti diatur di frontend/model logic)
            $table->enum('gender', ['male', 'female']);
            $table->text('description');
            $table->text('health_status'); // Vaksin, steril, penyakit, dll
            
            // Menyimpan path gambar. Bisa string biasa atau JSON jika multiple image
            $table->text('image_url')->nullable(); 
            
            // Status alur donasi
            // pending_review: Baru submit, admin belum cek
            // available: Disetujui admin, tampil di galeri
            // adopted: Sudah diadopsi orang lain
            // rejected: Ditolak admin (misal data palsu)
            $table->enum('status', ['pending_review', 'available', 'adopted', 'rejected'])->default('pending_review');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};