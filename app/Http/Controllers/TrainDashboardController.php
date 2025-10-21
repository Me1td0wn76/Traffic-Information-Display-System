<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RailwayOperator;
use App\Models\TrainLine;

class TrainDashboardController extends Controller
{
    /**
     * ダッシュボード画面を表示
     */
    public function index()
    {
        $operators = RailwayOperator::with(['trainLines.latestOperationStatus'])
            ->where('is_active', true)
            ->get();

        $delayedCount = TrainLine::whereHas('latestOperationStatus', function ($query) {
            $query->whereIn('status', ['delay', 'suspended', 'partial_suspended']);
        })->count();

        return view('dashboard', compact('operators', 'delayedCount'));
    }

    /**
     * 特定の鉄道事業者の詳細画面を表示
     */
    public function show(string $slug)
    {
        $operator = RailwayOperator::with(['trainLines.latestOperationStatus'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('operator-detail', compact('operator'));
    }
}

