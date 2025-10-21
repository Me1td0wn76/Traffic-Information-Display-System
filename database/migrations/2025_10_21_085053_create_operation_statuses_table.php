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
        Schema::create('operation_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_line_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['normal', 'delay', 'suspended', 'partial_suspended'])->default('normal');
            // normal: 平常運転, delay: 遅延, suspended: 運休, partial_suspended: 一部運休
            $table->text('message')->nullable(); // 遅延・運休の詳細メッセージ
            $table->timestamp('checked_at'); // スクレイピング実行日時
            $table->timestamps();

            $table->index(['train_line_id', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_statuses');
    }
};
