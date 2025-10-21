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
        Schema::create('train_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('railway_operator_id')->constrained()->onDelete('cascade');
            $table->string('name'); // 路線名（例: 大阪環状線）
            $table->string('slug'); // URL用のスラッグ（例: osaka-loop）
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['railway_operator_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('train_lines');
    }
};
