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
            <label for="body-editor" class="block text-gray-700 font-bold mb-2">内容<span class="text-red-500 ml-1">*</span></label>
            <div id="body-editor" 
                 contenteditable="true" 
                 class="w-full border-gray-300 rounded px-4 py-2 min-h-[240px] border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('body') border-red-500 @enderror" 
                 data-placeholder="内容を入力してください（Markdown記法が使用できます）"
                 style="white-space: pre-wrap;">{{ old('body', $note->body) }}</div>
            <textarea name="body" id="body" class="hidden" required>{{ old('body', $note->body) }}</textarea>
            <div class="text-sm text-gray-500 mt-1">最大65535文字。Markdown記法が使用できます。行ごとにリアルタイムプレビューされます。</div>
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

    <style>
        #body-editor.empty:not(.is-focused):before {
            content: attr(data-placeholder);
            color: #9ca3af;
            pointer-events: none;
        }
        
        .editor-line {
            min-height: 1.5em;
            margin: 2px 0;
            outline: none;
        }
        
        .editor-line.markdown-rendered {
            /* markdownがHTMLに変換された行のスタイルは必要に応じて調整 */
        }
        
        #body-editor:focus {
            outline: none;
        }
    </style>
@endsection
