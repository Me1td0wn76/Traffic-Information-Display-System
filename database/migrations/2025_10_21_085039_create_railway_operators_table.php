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
        Schema::create('railway_operators', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 事業者名（例: JR西日本）
            $table->string('slug')->unique(); // URL用のスラッグ（例: jr-west）
            $table->string('yahoo_url')->nullable(); // Yahoo路線情報のURL
            $table->boolean('is_active')->default(true); // アクティブ状態
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('railway_operators');
    }
};
