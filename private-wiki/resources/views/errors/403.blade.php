@extends('layouts.app')

@section('title', 'アクセス禁止')

@section('content')
<div class="text-center py-16">
    <div class="mb-8">
        <svg class="mx-auto h-32 w-32 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
    </div>
    
    <h1 class="text-6xl font-bold text-yellow-600 mb-4">403</h1>
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">アクセスが禁止されています</h2>
    
    <p class="text-gray-600 mb-8 max-w-md mx-auto">
        このリソースへのアクセス権限がありません。<br>
        必要な権限を持っているかご確認ください。
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