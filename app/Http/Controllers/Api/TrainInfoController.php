<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RailwayOperator;
use App\Models\TrainLine;
use App\Models\OperationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrainInfoController extends Controller
{
    /**
     * 全ての鉄道事業者と最新の運行状況を取得
     */
    public function index(): JsonResponse
    {
        $operators = RailwayOperator::with(['trainLines.latestOperationStatus'])
            ->where('is_active', true)
            ->get()
            ->map(function ($operator) {
                return [
                    'id' => $operator->id,
                    'name' => $operator->name,
                    'slug' => $operator->slug,
                    'lines' => $operator->trainLines->map(function ($line) {
                        $latestStatus = $line->latestOperationStatus;
                        return [
                            'id' => $line->id,
                            'name' => $line->name,
                            'slug' => $line->slug,
                            'status' => $latestStatus?->status ?? 'unknown',
                            'status_name' => $latestStatus?->status_name ?? '不明',
                            'message' => $latestStatus?->message,
                            'checked_at' => $latestStatus?->checked_at?->format('Y-m-d H:i:s'),
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $operators,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 特定の鉄道事業者の運行状況を取得
     */
    public function show(string $slug): JsonResponse
    {
        $operator = RailwayOperator::with(['trainLines.latestOperationStatus'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$operator) {
            return response()->json([
                'success' => false,
                'message' => '鉄道事業者が見つかりません',
            ], 404);
        }

        $lines = $operator->trainLines->map(function ($line) {
            $latestStatus = $line->latestOperationStatus;
            return [
                'id' => $line->id,
                'name' => $line->name,
                'slug' => $line->slug,
                'status' => $latestStatus?->status ?? 'unknown',
                'status_name' => $latestStatus?->status_name ?? '不明',
                'message' => $latestStatus?->message,
                'checked_at' => $latestStatus?->checked_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $operator->id,
                'name' => $operator->name,
                'slug' => $operator->slug,
                'lines' => $lines,
            ],
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 遅延している路線のみを取得
     */
    public function delayed(): JsonResponse
    {
        $delayedLines = TrainLine::with(['railwayOperator', 'latestOperationStatus'])
            ->whereHas('latestOperationStatus', function ($query) {
                $query->whereIn('status', ['delay', 'suspended', 'partial_suspended']);
            })
            ->get()
            ->map(function ($line) {
                $latestStatus = $line->latestOperationStatus;
                return [
                    'operator' => [
                        'id' => $line->railwayOperator->id,
                        'name' => $line->railwayOperator->name,
                        'slug' => $line->railwayOperator->slug,
                    ],
                    'line' => [
                        'id' => $line->id,
                        'name' => $line->name,
                        'slug' => $line->slug,
                    ],
                    'status' => $latestStatus->status,
                    'status_name' => $latestStatus->status_name,
                    'message' => $latestStatus->message,
                    'checked_at' => $latestStatus->checked_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $delayedLines,
            'count' => $delayedLines->count(),
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 特定路線の運行状況履歴を取得
     */
    public function history(int $lineId): JsonResponse
    {
        $line = TrainLine::with('railwayOperator')->find($lineId);

        if (!$line) {
            return response()->json([
                'success' => false,
                'message' => '路線が見つかりません',
            ], 404);
        }

        $history = OperationStatus::where('train_line_id', $lineId)
            ->orderBy('checked_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($status) {
                return [
                    'status' => $status->status,
                    'status_name' => $status->status_name,
                    'message' => $status->message,
                    'checked_at' => $status->checked_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'operator' => [
                    'name' => $line->railwayOperator->name,
                    'slug' => $line->railwayOperator->slug,
                ],
                'line' => [
                    'id' => $line->id,
                    'name' => $line->name,
                    'slug' => $line->slug,
                ],
                'history' => $history,
            ],
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}

