@extends('layouts.app')

@section('title', '近畿地方 電車運行情報 - ダッシュボード')

@section('content')
<div class="space-y-6">
    <!-- 概要カード -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $operators->count() }}</div>
                <div class="text-gray-600 mt-2">鉄道事業者</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $operators->sum(fn($op) => $op->trainLines->count()) }}</div>
                <div class="text-gray-600 mt-2">監視中の路線</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold {{ $delayedCount > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $delayedCount }}
                </div>
                <div class="text-gray-600 mt-2">遅延・運休</div>
            </div>
        </div>
    </div>

    <!-- 遅延情報アラート -->
    @if($delayedCount > 0)
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800">
                    現在 {{ $delayedCount }} 路線で遅延または運休が発生しています
                </p>
            </div>
        </div>
    </div>
    @else
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">
                    現在、全路線で平常運転中です
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- 鉄道事業者一覧 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($operators as $operator)
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-800">{{ $operator->name }}</h3>
                    @php
                        $delayedLines = $operator->trainLines->filter(function($line) {
                            $status = $line->latestOperationStatus;
                            return $status && in_array($status->status, ['delay', 'suspended', 'partial_suspended']);
                        });
                    @endphp
                    @if($delayedLines->count() > 0)
                        <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                            {{ $delayedLines->count() }} 件
                        </span>
                    @else
                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                            正常
                        </span>
                    @endif
                </div>

                <div class="space-y-2 mb-4">
                    @forelse($operator->trainLines->take(5) as $line)
                        @php
                            $status = $line->latestOperationStatus;
                            $statusClass = match($status?->status ?? 'unknown') {
                                'normal' => 'bg-green-100 text-green-800',
                                'delay' => 'bg-yellow-100 text-yellow-800',
                                'suspended' => 'bg-red-100 text-red-800',
                                'partial_suspended' => 'bg-orange-100 text-orange-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-700">{{ $line->name }}</span>
                            <span class="px-2 py-1 rounded text-xs {{ $statusClass }}">
                                {{ $status?->status_name ?? '不明' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">路線データがありません</p>
                    @endforelse
                </div>

                <a href="{{ route('operator.show', $operator->slug) }}"
                   class="block w-full text-center bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
                    詳細を見る
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    // 自動更新機能（オプション）
    setInterval(() => {
        document.getElementById('last-update').textContent = new Date().toLocaleString('ja-JP');
    }, 60000); // 1分ごとに更新時刻を更新
</script>
@endpush
@endsection
