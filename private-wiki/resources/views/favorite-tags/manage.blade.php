@extends('layouts.app')

@section('title', 'お気に入りタグの管理')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">お気に入りタグの管理</h1>
        <p class="text-gray-600">お気に入りタグを追加・削除・並び替えできます</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- 現在のお気に入りタグ --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">現在のお気に入りタグ</h2>
            
            @if($favoriteTags->count() > 0)
                <div id="favorite-tags-management-list" class="space-y-2">
                    @foreach($favoriteTags as $favoriteTag)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded border favorite-tag-item" 
                             data-id="{{ $favoriteTag->id }}" 
                             draggable="true">
                            <div class="flex items-center">
                                <div class="drag-handle cursor-grab mr-3 text-gray-400 hover:text-gray-600 select-none" title="ドラッグして並び替え">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                        <circle cx="4" cy="4" r="1"/>
                                        <circle cx="12" cy="4" r="1"/>
                                        <circle cx="4" cy="8" r="1"/>
                                        <circle cx="12" cy="8" r="1"/>
                                        <circle cx="4" cy="12" r="1"/>
                                        <circle cx="12" cy="12" r="1"/>
                                    </svg>
                                </div>
                                <span class="text-blue-600 font-medium">{{ $favoriteTag->tag->name }}</span>
                            </div>
                            <form method="POST" action="{{ route('favorite-tags.remove', $favoriteTag) }}" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-800 text-sm px-2 py-1 rounded hover:bg-red-50">
                                    削除
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-4 text-xs text-gray-500">
                    ドラッグ&ドロップで並び順を変更できます
                </div>
            @else
                <p class="text-gray-500 text-center py-8">お気に入りのタグがありません</p>
            @endif
        </div>

        {{-- タグ追加フォーム --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800">タグを追加</h2>
                <span class="text-sm text-gray-500">
                    {{ $favoriteTags->count() }}/5個
                </span>
            </div>
            
            @if($favoriteTags->count() >= 5)
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                    <p class="text-yellow-800 text-sm">
                        お気に入りタグは最大5個までです。新しいタグを追加するには、既存のタグを削除してください。
                    </p>
                </div>
            @else
                <form method="POST" action="{{ route('favorite-tags.add') }}" id="tag-add-form">
                    @csrf
                    <div class="mb-4">
                        <label for="tag_name" class="block text-sm font-medium text-gray-700 mb-2">
                            タグ名を入力
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   name="tag_name" 
                                   id="tag_name" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="タグ名を入力してください"
                                   autocomplete="off">
                            <ul id="tag-suggestions-list" class="absolute bg-white border w-full z-10 hidden max-h-40 overflow-y-auto"></ul>
                        </div>
                        @error('tag_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            既存のタグ名を入力するか、新しいタグ名を作成できます
                        </p>
                    </div>
                    
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        お気に入りに追加
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="mt-8 text-center">
        <a href="{{ url('/') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
            ← ホームに戻る
        </a>
    </div>
</div>
@endsection

