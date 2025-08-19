@extends('layouts.app')

@section('title', 'ページが見つかりません')

@section('content')
<div class="text-center py-16">
    <div class="mb-8">
        <svg class="mx-auto h-32 w-32 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.034 0-3.9.785-5.291 2.09M20 12a8 8 0 11-16 0 8 8 0 0116 0z" />
        </svg>
    </div>
    
    <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">ページが見つかりません</h2>
    
    <p class="text-gray-600 mb-8 max-w-md mx-auto">
        お探しのページは削除されたか、URLが変更された可能性があります。
    </p>
    
    <div class="space-x-4">
        <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md text-white font-semibold hover:bg-blue-700 transition duration-300">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            ホームに戻る
        </a>
        
        <button onclick="window.history.back()" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md text-white font-semibold hover:bg-gray-600 transition duration-300">
            前のページに戻る
        </button>
    </div>
</div>
@endsection