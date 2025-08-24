<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'My Wiki')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-900">

    {{-- 左固定サイドバー --}}
    <aside id="sidebar" class="sidebar w-64 h-screen fixed top-0 left-0 bg-gray-800 text-white px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">📚 Private Wiki</h2>
            <button id="sidebar-toggle" 
                    class="text-white hover:text-gray-300 p-1 rounded focus:outline-none focus:ring-2 focus:ring-gray-500"
                    aria-label="サイドバーを折り畳む/展開する"
                    title="サイドバーを折り畳む/展開する">
                ☰
            </button>
        </div>
        <nav class="space-y-2">
            <a href="{{ url('/') }}" class="block hover:bg-gray-700 rounded px-2 py-1">ホーム</a>
            <a href="{{ route('bug-timeline.index') }}" class="block hover:bg-gray-700 rounded px-2 py-1">🐛 バグタイムライン</a>
            <a href="{{ route('favorite-tags.manage') }}" class="block hover:bg-gray-700 rounded px-2 py-1">⭐ タグ管理</a>
            <a href="#" class="block hover:bg-gray-700 rounded px-2 py-1">設定</a>
        </nav>
        
        {{-- お気に入りタグセクション --}}
        <div class="mt-6 pt-4 border-t border-gray-700" id="favorite-tags-section">
            <h3 class="text-sm font-semibold text-gray-300 mb-3">お気に入りタグ</h3>
            <div id="sidebar-favorite-tags-list" class="space-y-1">
                @if(isset($favoriteTags) && $favoriteTags->count() > 0)
                    @foreach($favoriteTags as $favoriteTag)
                        <button 
                            class="block w-full text-left hover:bg-gray-700 rounded px-2 py-1 text-sm text-blue-200 hover:text-blue-100 favorite-tag-btn"
                            data-tag="{{ $favoriteTag->tag->name }}"
                            title="「{{ $favoriteTag->tag->name }}」で検索"
                        >
                            {{ $favoriteTag->tag->name }}
                        </button>
                    @endforeach
                @else
                    <p class="text-xs text-gray-400">お気に入りのタグがありません</p>
                @endif
            </div>
        </div>
        
        {{-- ユーザー情報とログアウト --}}
        @auth
        <div class="mt-auto pt-4 border-t border-gray-700">
            <p class="text-sm text-gray-300 mb-2">{{ Auth::user()->name }}</p>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-red-400 hover:text-red-300">
                    ログアウト
                </button>
            </form>
        </div>
        @endauth
    </aside>

    {{-- サイドバーの右側にメインコンテンツ（左余白 w-64分） --}}
    <div id="main-content" class="main-content ml-64 min-h-screen px-8 py-6">
        {{-- サイドバー折り畳み時のトグルボタン --}}
        <button id="main-sidebar-toggle" 
                class="fixed top-4 left-4 z-50 bg-gray-800 hover:bg-gray-700 text-white p-2 rounded shadow-lg hidden"
                aria-label="サイドバーを展開する"
                title="サイドバーを展開する">
            ☰
        </button>
        
        <x-alert />
        @yield('content')
    </div>

    {{-- フローティングボタン --}}
    <a href="{{ route('notes.create') }}" 
        class="fixed bottom-6 right-6 bg-blue-600 hover:bg-blue-700 text-white w-16 h-16 flex items-center justify-center rounded-full shadow-lg text-2xl z-50">
        +
    </a>

    @vite(['resources/js/app.js'])
</body>
</html>