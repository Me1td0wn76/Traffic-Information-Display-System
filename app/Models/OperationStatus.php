<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationStatus extends Model
{
    protected $fillable = [
        'train_line_id',
        'status',
        'message',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    /**
     * 路線との関連
     */
    public function trainLine(): BelongsTo
    {
        return $this->belongsTo(TrainLine::class);
    }

    /**
     * 遅延・運休かどうか
     */
    public function isDelayed(): bool
    {
        return in_array($this->status, ['delay', 'suspended', 'partial_suspended']);
    }

    /**
     * ステータスの日本語名を取得
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'normal' => '平常運転',
            'delay' => '遅延',
            'suspended' => '運休',
            'partial_suspended' => '一部運休',
            default => '不明',
        };
    }
}
