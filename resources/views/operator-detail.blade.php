@extends('layouts.app')

@section('title', $operator->name . ' - 運行情報')

@section('content')
<div class="space-y-6">
    <!-- パンくずリスト -->
    <nav class="text-sm">
        <ol class="flex items-center space-x-2">
            <li><a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline">ホーム</a></li>
            <li><span class="text-gray-500">/</span></li>
            <li class="text-gray-700">{{ $operator->name }}</li>
        </ol>
    </nav>

    <!-- タイトル -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-3xl font-bold text-gray-800">{{ $operator->name }} 運行情報</h1>
        <p class="text-gray-600 mt-2">
            最終更新: {{ now()->format('Y年m月d日 H:i') }}
        </p>
    </div>

    <!-- 概要 -->
    @php
        $normalLines = $operator->trainLines->filter(function($line) {
            $status = $line->latestOperationStatus;
            return $status && $status->status === 'normal';
        });
        $delayedLines = $operator->trainLines->filter(function($line) {
            $status = $line->latestOperationStatus;
            return $status && in_array($status->status, ['delay', 'suspended', 'partial_suspended']);
        });
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $operator->trainLines->count() }}</div>
                <div class="text-gray-600 mt-2">総路線数</div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $normalLines->count() }}</div>
                <div class="text-gray-600 mt-2">平常運転</div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-center">
                <div class="text-3xl font-bold {{ $delayedLines->count() > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $delayedLines->count() }}
                </div>
                <div class="text-gray-600 mt-2">遅延・運休</div>
            </div>
        </div>
    </div>

    <!-- 路線一覧 -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">路線別運行状況</h2>
        </div>

        <div class="divide-y divide-gray-200">
            @forelse($operator->trainLines as $line)
                @php
                    $status = $line->latestOperationStatus;
                    $statusColor = match($status?->status ?? 'unknown') {
                        'normal' => 'green',
                        'delay' => 'yellow',
                        'suspended' => 'red',
                        'partial_suspended' => 'orange',
                        default => 'gray',
                    };
                    $statusIcon = match($status?->status ?? 'unknown') {
                        'normal' => '✓',
                        'delay' => '⚠',
                        'suspended' => '✗',
                        'partial_suspended' => '△',
                        default => '?',
                    };
                @endphp

                <div class="p-6 hover:bg-gray-50 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl">{{ $statusIcon }}</span>
                                <h3 class="text-xl font-semibold text-gray-800">{{ $line->name }}</h3>
                                <span class="px-3 py-1 rounded-full text-sm font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                    {{ $status?->status_name ?? '不明' }}
                                </span>
                            </div>

                            @if($status && $status->message)
                                <div class="mt-3 p-4 bg-{{ $statusColor }}-50 rounded-lg border-l-4 border-{{ $statusColor }}-500">
                                    <p class="text-sm text-gray-700">{{ $status->message }}</p>
                                </div>
                            @endif

                            @if($status)
                                <div class="mt-2 text-sm text-gray-500">
                                    確認日時: {{ $status->checked_at->format('Y年m月d日 H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">
                    路線データがありません
                </div>
            @endforelse
        </div>
    </div>

    <!-- 戻るボタン -->
    <div class="text-center">
        <a href="{{ route('dashboard') }}"
           class="inline-block bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition">
            ← ダッシュボードに戻る
        </a>
    </div>
</div>
@endsection
