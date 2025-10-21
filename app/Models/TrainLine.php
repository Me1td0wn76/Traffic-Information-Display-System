<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainLine extends Model
{
    protected $fillable = [
        'railway_operator_id',
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 鉄道事業者との関連
     */
    public function railwayOperator(): BelongsTo
    {
        return $this->belongsTo(RailwayOperator::class);
    }

    /**
     * 運行状況との関連
     */
    public function operationStatuses(): HasMany
    {
        return $this->hasMany(OperationStatus::class);
    }

    /**
     * 最新の運行状況を取得
     */
    public function latestOperationStatus()
    {
        return $this->hasOne(OperationStatus::class)->latestOfMany('checked_at');
    }
}
