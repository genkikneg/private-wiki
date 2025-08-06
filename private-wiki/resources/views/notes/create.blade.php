@extends('layouts.app')

@section('title', '新しいメモを作成')

@section('content')
    <h1 class="text-2xl font-bold mb-4">新しいメモを作成</h1>
    <form action="{{ route('notes.store') }}" method="POST">
        @csrf
        {{-- タイトル入力 --}}
        <div class="mb-4">
            <label for="title" class="block text-gray-700 font-bold mb-2">タイトル</label>
            <input type="text" name="title" id="title" class="w-full border-gray-300 rounded px-4 py-2" placeholder="タイトルを入力してください">
        </div>

        {{-- タグ入力 --}}
        <div class="mb-4">
            <label for="tags" class="block text-gray-700 font-bold mb-2">タグ</label>
            <input type="text" name="tags" id="tags" class="w-full border-gray-300 rounded px-4 py-2" placeholder="タグをカンマ区切りで入力してください">
        </div>

        {{-- ボディ入力 --}}
        <div class="mb-4">
            <label for="body" class="block text-gray-700 font-bold mb-2">内容</label>
            <textarea name="body" id="body" rows="10" class="w-full border-gray-300 rounded px-4 py-2" placeholder="内容を入力してください"></textarea>
        </div>

        {{-- 保存ボタン --}}
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">保存</button>
    </form>
@endsection