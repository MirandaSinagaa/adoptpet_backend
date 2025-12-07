<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            // Ubah enum jadi string agar bisa terima status 'canceled' tanpa error database
            $table->string('status')->change(); 
            
            // Tambah kolom alasan
            $table->text('cancellation_reason')->nullable()->after('admin_note');
        });
    }

    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason');
            // Kembalikan ke enum (opsional, biarkan string saja lebih aman)
        });
    }
};