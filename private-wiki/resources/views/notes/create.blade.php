@extends('layouts.app')

@section('title', '新しいメモを作成')

@section('content')
    <h1 class="text-2xl font-bold mb-4">新しいメモを作成</h1>
    
    <x-validation-errors />
    
    <form action="{{ route('notes.store') }}" method="POST">
        @csrf
        {{-- タイトル入力 --}}
        <div class="mb-4">
            <label for="title" class="block text-gray-700 font-bold mb-2">タイトル</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" class="w-full border-gray-300 rounded px-4 py-2 @error('title') border-red-500 @enderror" placeholder="タイトルを入力してください">
            @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- タグ入力 --}}
        <div class="mb-4">
            <label for="tags" class="block text-gray-700 font-bold mb-2">タグ</label>
            <input type="text" name="tags" id="tags" value="{{ old('tags') }}" class="w-full border-gray-300 rounded px-4 py-2 @error('tags') border-red-500 @enderror" placeholder="タグをカンマ区切りで入力してください">
            @error('tags')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- ボディ入力 --}}
        <div class="mb-4">
            <label for="body" class="block text-gray-700 font-bold mb-2">内容</label>
            <textarea name="body" id="body" rows="10" class="w-full border-gray-300 rounded px-4 py-2 @error('body') border-red-500 @enderror" placeholder="内容を入力してください">{{ old('body') }}</textarea>
            @error('body')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 保存ボタン --}}
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors duration-200">保存</button>
    </form>

    <script>
        // タイトルとタグのinputでエンターキー押下時のフォーム送信を阻止
        document.getElementById('title').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        document.getElementById('tags').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    </script>
@endsection