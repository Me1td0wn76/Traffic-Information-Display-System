<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '近畿地方 電車運行情報')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .status-normal { background-color: #10b981; }
        .status-delay { background-color: #f59e0b; }
        .status-suspended { background-color: #ef4444; }
        .status-partial-suspended { background-color: #f97316; }
    </style>
</head>
<body>
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
