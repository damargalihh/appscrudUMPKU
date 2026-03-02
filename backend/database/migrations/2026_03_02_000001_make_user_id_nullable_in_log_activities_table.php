<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_activities', function (Blueprint $table) {
            // Hapus foreign key lama
            $table->dropForeign(['user_id']);

            // Ubah user_id menjadi nullable (untuk log hotspot user tanpa akun admin)
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Tambahkan kembali foreign key dengan SET NULL on delete
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('log_activities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
