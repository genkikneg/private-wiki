<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'My Wiki')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-900">

    {{-- 左固定サイドバー --}}
    <aside class="w-64 h-screen fixed top-0 left-0 bg-gray-800 text-white px-4 py-6">
        <h2 class="text-2xl font-bold mb-6">📚 Private Wiki</h2>
        <nav class="space-y-2">
            <a href="{{ url('/') }}" class="block hover:bg-gray-700 rounded px-2 py-1">ホーム</a>
            <a href="#" class="block hover:bg-gray-700 rounded px-2 py-1">メモ一覧</a>
            <a href="#" class="block hover:bg-gray-700 rounded px-2 py-1">設定</a>
        </nav>
    </aside>

    {{-- サイドバーの右側にメインコンテンツ（左余白 w-64分） --}}
    <div class="ml-64 min-h-screen px-8 py-6">
        <x-alert />
        @yield('content')
    </div>

    {{-- フローティングボタン --}}
    <a href="{{ route('notes.create') }}" 
        class="fixed bottom-6 right-6 bg-blue-600 hover:bg-blue-700 text-white w-16 h-16 flex items-center justify-center rounded-full shadow-lg text-2xl z-50">
        +
    </a>

</body>
</html>