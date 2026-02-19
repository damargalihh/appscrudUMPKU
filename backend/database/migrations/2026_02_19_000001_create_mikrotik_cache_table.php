<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_cache', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();       // e.g. hotspot_users, profiles, active_users, system_info, queues, user_stats
            $table->longText('data');               // JSON encoded data
            $table->timestamp('fetched_at');        // when data was fetched from MikroTik
            $table->timestamps();

            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_cache');
    }
};
