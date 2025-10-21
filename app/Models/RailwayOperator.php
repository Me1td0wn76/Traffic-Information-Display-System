<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RailwayOperator extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'yahoo_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 路線との関連
     */
    public function trainLines(): HasMany
    {
        return $this->hasMany(TrainLine::class);
    }

    /**
     * アクティブな路線のみ取得
     */
    public function activeTrainLines(): HasMany
    {
        return $this->trainLines()->where('is_active', true);
    }
}
