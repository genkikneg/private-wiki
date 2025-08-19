@extends('layouts.app')

@section('title', 'メモを編集')

@section('content')
    <h1 class="text-2xl font-bold mb-4">メモを編集</h1>
    
    <x-validation-errors />
    
    <form action="{{ route('notes.update', $note->id) }}" method="POST">
        @csrf
        @method('PUT')
        {{-- タイトル入力 --}}
        <div class="mb-4">
            <label for="title" class="block text-gray-700 font-bold mb-2">タイトル<span class="text-red-500 ml-1">*</span></label>
            <input type="text" name="title" id="title" value="{{ old('title', $note->title) }}" maxlength="255" class="w-full border-gray-300 rounded px-4 py-2 @error('title') border-red-500 @enderror" placeholder="タイトルを入力してください" required>
            <div class="text-sm text-gray-500 mt-1">最大255文字</div>
            @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- タグ入力 --}}
        <div class="mb-4 relative">
            <label for="tags-input" class="block text-gray-700 font-bold mb-2">タグ</label>
            <x-tag-input name="tags" :value="old('tags', $tagNames)" placeholder="タグを入力してください（Enterで追加）" />
            <div class="text-sm text-gray-500 mt-1">各タグは50文字以内、最大20個まで。文字、数字、アンダースコア、ハイフンが使用可能。</div>
            @error('tags')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- ボディ入力 --}}
        <div class="mb-4">
            <label for="body" class="block text-gray-700 font-bold mb-2">内容<span class="text-red-500 ml-1">*</span></label>
            <textarea name="body" id="body" rows="10" maxlength="65535" class="w-full border-gray-300 rounded px-4 py-2 @error('body') border-red-500 @enderror" placeholder="内容を入力してください（Markdown記法が使用できます）" required>{{ old('body', $note->body) }}</textarea>
            <div class="text-sm text-gray-500 mt-1">最大65535文字。Markdown記法が使用できます。</div>
            @error('body')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 保存・キャンセルボタン --}}
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors duration-200">更新</button>
            <a href="{{ route('notes.show', $note->id) }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors duration-200">キャンセル</a>
        </div>
    </form>

    <script>
        // タイトルのinputでエンターキー押下時のフォーム送信を阻止
        document.getElementById('title').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    </script>
@endsection