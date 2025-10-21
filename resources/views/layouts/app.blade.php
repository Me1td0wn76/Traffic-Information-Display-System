<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '近畿地方 電車運行情報')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .status-normal { background-color: #10b981; }
        .status-delay { background-color: #f59e0b; }
        .status-suspended { background-color: #ef4444; }
        .status-partial-suspended { background-color: #f97316; }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-2xl font-bold">
                    🚃 近畿地方 電車運行情報
                </a>
                <div class="text-sm">
                    最終更新: <span id="last-update">{{ now()->format('Y年m月d日 H:i') }}</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-6">
        <div class="container mx-auto px-4 text-center">
            <p class="text-sm">
                &copy; {{ date('Y') }} 近畿地方 電車運行情報システム<br>
                <span class="text-gray-400 text-xs">
                    ※運行情報はYahoo!路線情報から取得しています。正確な情報は各鉄道会社の公式サイトをご確認ください。
                </span>
            </p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
