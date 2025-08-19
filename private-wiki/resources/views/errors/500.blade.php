@extends('layouts.app')

@section('title', 'サーバーエラー')

@section('content')
<div class="text-center py-16">
    <div class="mb-8">
        <svg class="mx-auto h-32 w-32 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
    </div>
    
    <h1 class="text-6xl font-bold text-red-600 mb-4">500</h1>
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">内部サーバーエラー</h2>
    
    <p class="text-gray-600 mb-8 max-w-md mx-auto">
        申し訳ございません。サーバーでエラーが発生しました。<br>
        しばらく時間をおいてから再度お試しください。
    </p>
    
    <div class="space-x-4">
        <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md text-white font-semibold hover:bg-blue-700 transition duration-300">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            ホームに戻る
        </a>
        
        <button onclick="location.reload()" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md text-white font-semibold hover:bg-gray-600 transition duration-300">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
            </svg>
            再読み込み
        </button>
    </div>
</div>
@endsection