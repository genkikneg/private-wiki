@extends('layouts.app')

@section('title', '新しいメモを作成')

@section('content')
    <h1 class="text-2xl font-bold mb-4">新しいメモを作成</h1>
    
    <x-validation-errors />
    
    <form action="{{ route('notes.store') }}" method="POST" id="note-form">
        @csrf
        {{-- タイトル入力 --}}
        <div class="mb-4">
            <label for="title" class="block text-gray-700 font-bold mb-2">タイトル<span class="text-red-500 ml-1">*</span></label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" maxlength="255" class="w-full border-gray-300 rounded px-4 py-2 @error('title') border-red-500 @enderror" placeholder="タイトルを入力してください" required>
            <div class="text-sm text-gray-500 mt-1">最大255文字</div>
            @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- タグ入力 --}}
        <div class="mb-4 relative">
            <label for="tags-input" class="block text-gray-700 font-bold mb-2">タグ</label>
            <x-tag-input name="tags" :value="old('tags')" placeholder="タグを入力してください（Enterで追加）" />
            <div class="text-sm text-gray-500 mt-1">各タグは50文字以内、最大20個まで。文字、数字、アンダースコア、ハイフンが使用可能。</div>
            @error('tags')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Markdownファイル読み込み --}}
        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2" for="markdown-file">Markdownファイルから読み込み</label>
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    id="import-markdown"
                    data-testid="markdown-file-trigger"
                    class="inline-flex items-center gap-2 rounded border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-600 transition-colors duration-200 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    aria-controls="markdown-file"
                >
                    ファイルを選択
                </button>
                <span id="markdown-file-name" class="text-sm text-gray-500"></span>
            </div>
            <input
                type="file"
                id="markdown-file"
                data-testid="markdown-file-input"
                accept=".md,text/markdown"
                class="hidden"
            >
            <p id="markdown-import-error" class="text-sm text-red-500 hidden mt-2"></p>
            <p class="text-xs text-gray-500 mt-1">ファイルを選択すると自動的にエディタへ読み込まれます。データはサーバーへアップロードされません。</p>
        </div>

        {{-- ボディ入力 --}}
        <div class="mb-4">
            <label for="body-editor" class="block text-gray-700 font-bold mb-2">内容<span class="text-red-500 ml-1">*</span></label>
            <div id="body-editor" 
                 contenteditable="true" 
                 class="w-full border-gray-300 rounded px-4 py-2 min-h-[240px] border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('body') border-red-500 @enderror" 
                 data-placeholder="内容を入力してください（Markdown記法が使用できます）"
                 style="white-space: pre-wrap;">{{ old('body') }}</div>
            <textarea name="body" id="body" class="hidden" required>{{ old('body') }}</textarea>
            <div class="text-sm text-gray-500 mt-1">最大65535文字。Markdown記法が使用できます。行ごとにリアルタイムプレビューされます。</div>
            @error('body')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 保存ボタン --}}
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors duration-200">保存</button>
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
