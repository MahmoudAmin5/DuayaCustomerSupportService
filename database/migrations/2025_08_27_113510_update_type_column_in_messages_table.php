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
        Schema::table('messages', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'file', 'voice', 'video') DEFAULT 'text'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'file', 'voice') DEFAULT 'text'");
        });
    }
};
