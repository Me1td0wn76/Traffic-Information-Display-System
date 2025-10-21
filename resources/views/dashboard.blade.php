@extends('layouts.app')

@section('title', '近畿地方 電車運行情報')

@section('content')
<div class="space-y-4">
    <!-- ヘッダー情報 -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg shadow-lg p-4">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">近畿地方 電車運行情報</h1>
                <p class="text-blue-100 text-sm mt-1">最終更新: <span id="last-update">{{ now()->format('Y年m月d日 H:i') }}</span></p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold">{{ $operators->sum(fn($op) => $op->trainLines->count()) }}</div>
                <div class="text-blue-100 text-sm">路線</div>
            </div>
        </div>
    </div>

    <!-- 遅延アラート -->
    @if($delayedCount > 0)
    <div class="bg-red-100 border-l-4 border-red-600 p-3 rounded-lg shadow">
        <div class="flex items-center">
            <svg class="h-6 w-6 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="font-bold text-red-800 text-lg">⚠️ 現在 {{ $delayedCount }} 路線で遅延または運休が発生しています</p>
        </div>
    </div>
    @endif

    <!-- 全事業者・全路線表示 -->
    @foreach($operators as $operator)
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- 事業者ヘッダー -->
        <div class="bg-gray-800 text-white px-4 py-3">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">{{ $operator->name }}</h2>
                @php
                    $delayedLines = $operator->trainLines->filter(function($line) {
                        $status = $line->latestOperationStatus;
                        return $status && in_array($status->status, ['delay', 'suspended', 'partial_suspended']);
                    });
                    $totalLines = $operator->trainLines->count();
                @endphp
                <div class="flex items-center gap-3">
                    <span class="text-gray-300 text-sm">{{ $totalLines }}路線</span>
                    @if($delayedLines->count() > 0)
                        <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                            遅延 {{ $delayedLines->count() }}件
                        </span>
                    @else
                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                            ✓ 正常運転
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 路線一覧 -->
        <div class="divide-y divide-gray-200">
            @forelse($operator->trainLines as $line)
                @php
                    $status = $line->latestOperationStatus;
                    $statusInfo = match($status?->status ?? 'unknown') {
                        'normal' => ['color' => 'bg-green-50 border-green-200', 'badge' => 'bg-green-500 text-white', 'text' => '平常運転', 'icon' => '✓'],
                        'delay' => ['color' => 'bg-yellow-50 border-yellow-200', 'badge' => 'bg-yellow-500 text-white', 'text' => '遅延', 'icon' => '⚠'],
                        'suspended' => ['color' => 'bg-red-50 border-red-200', 'badge' => 'bg-red-600 text-white', 'text' => '運転見合わせ', 'icon' => '✕'],
                        'partial_suspended' => ['color' => 'bg-orange-50 border-orange-200', 'badge' => 'bg-orange-500 text-white', 'text' => '一部運休', 'icon' => '!'],
                        default => ['color' => 'bg-gray-50 border-gray-200', 'badge' => 'bg-gray-500 text-white', 'text' => '情報なし', 'icon' => '?'],
                    };
                @endphp
                <div class="px-4 py-3 {{ $statusInfo['color'] }} border-l-4 hover:bg-opacity-75 transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-gray-900 text-lg">{{ $line->name }}</h3>
                                <span class="{{ $statusInfo['badge'] }} px-3 py-1 rounded-full text-sm font-bold whitespace-nowrap">
                                    {{ $statusInfo['icon'] }} {{ $statusInfo['text'] }}
                                </span>
                            </div>
                            @if($status && $status->message && $status->message !== '平常運転')
                                <p class="text-gray-700 text-sm mt-2 leading-relaxed">
                                    {{ $status->message }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-3 text-gray-500 text-center">
                    路線データがありません
                </div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script>
    // 5分ごとに自動リロード（掲示板用）
    setTimeout(() => {
        location.reload();
    }, 5 * 60 * 1000);

    // 更新時刻の表示を更新
    setInterval(() => {
        const now = new Date();
        const formatted = now.getFullYear() + '年' +
            String(now.getMonth() + 1).padStart(2, '0') + '月' +
            String(now.getDate()).padStart(2, '0') + '日 ' +
            String(now.getHours()).padStart(2, '0') + ':' +
            String(now.getMinutes()).padStart(2, '0');
        document.getElementById('last-update').textContent = formatted;
    }, 1000);
</script>
@endpush
@endsection
