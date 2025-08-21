@extends('layouts.app')

@section('title', '新しいメモを作成')

@section('content')
    <h1 class="text-2xl font-bold mb-4">新しいメモを作成</h1>
    
    <x-validation-errors />
    
    <form action="{{ route('notes.store') }}" method="POST">
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

    <script>
        // タイトルのinputでエンターキー押下時のフォーム送信を阻止
        document.getElementById('title').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        // Markdownエディター機能
        class MarkdownEditor {
            constructor() {
                this.editor = document.getElementById('body-editor');
                this.hiddenInput = document.getElementById('body');
                this.lines = new Map(); // 行データを管理
                this.currentLineElement = null;
                this.isConverting = false;
                this.init();
            }

            init() {
                this.editor.addEventListener('keydown', this.handleKeydown.bind(this));
                this.editor.addEventListener('keyup', this.handleKeyup.bind(this));
                this.editor.addEventListener('input', this.handleInput.bind(this));
                this.editor.addEventListener('click', this.handleClick.bind(this));
                
                // プレースホルダー表示
                this.updatePlaceholder();
                
                // フォーム送信前にhidden inputを更新
                document.querySelector('form').addEventListener('submit', (e) => {
                    this.convertAllLinesToMarkdown();
                });

                // 初期化時に最初の行を作成
                this.ensureLineStructure();
                
                console.log('Markdown editor initialized');
            }

            handleKeydown(e) {
                console.log('Key pressed:', e.key);
                
                if (e.key === 'Enter') {
                    e.preventDefault();
                    
                    // 現在の行をHTML変換してから新しい行を作成
                    const currentLine = this.getCurrentLine();
                    if (currentLine) {
                        this.convertLineToHTML(currentLine);
                    }
                    
                    // 新しい行を作成
                    this.createNewLine();
                }
            }

            handleKeyup(e) {
                // 矢印キーでの行移動を検出
                if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                    this.checkLineChange();
                }
            }

            handleInput(e) {
                this.updatePlaceholder();
                
                // 現在の行のdata-markdown属性を更新
                const currentLine = this.getCurrentLine();
                if (currentLine) {
                    const currentText = currentLine.textContent || '';
                    currentLine.setAttribute('data-markdown', currentText);
                }
                
                this.updateHiddenInput();
            }

            handleClick(e) {
                // 前の行をHTML変換
                if (this.currentLineElement) {
                    this.convertLineToHTML(this.currentLineElement);
                }
                
                // クリックした行をMarkdown表示に戻す
                const clickedLine = this.getLineFromEvent(e);
                if (clickedLine) {
                    this.convertLineToMarkdown(clickedLine);
                    this.currentLineElement = clickedLine;
                    
                    // クリック後にカーソルを最後尾に設定
                    setTimeout(() => {
                        this.setCursorToEnd(clickedLine);
                    }, 10);
                    
                    console.log('Clicked line converted to Markdown');
                }
            }

            checkLineChange() {
                const newLine = this.getCurrentLine();
                if (newLine && newLine !== this.currentLineElement) {
                    // 前の行をHTML変換
                    if (this.currentLineElement) {
                        this.convertLineToHTML(this.currentLineElement);
                        console.log('Line changed, converting previous line to HTML');
                    }
                    
                    // 新しい行をMarkdown表示に戻す
                    this.convertLineToMarkdown(newLine);
                    this.currentLineElement = newLine;
                    
                    // カーソルを最後尾に設定
                    setTimeout(() => {
                        this.setCursorToEnd(newLine);
                    }, 10);
                    
                    console.log('New line set to Markdown mode');
                }
            }

            setCursorToEnd(element) {
                const range = document.createRange();
                const sel = window.getSelection();
                
                // 要素の最後尾にカーソルを設定
                if (element.childNodes.length > 0) {
                    const lastChild = element.childNodes[element.childNodes.length - 1];
                    if (lastChild.nodeType === Node.TEXT_NODE) {
                        range.setStart(lastChild, lastChild.textContent.length);
                    } else {
                        range.setStart(element, element.childNodes.length);
                    }
                } else {
                    range.setStart(element, 0);
                }
                range.collapse(true);
                
                sel.removeAllRanges();
                sel.addRange(range);
            }

            getCurrentLine() {
                const selection = window.getSelection();
                if (selection.rangeCount === 0) return null;
                
                let node = selection.getRangeAt(0).startContainer;
                
                // テキストノードの場合、親要素を取得
                if (node.nodeType === Node.TEXT_NODE) {
                    node = node.parentNode;
                }
                
                // 現在の行を見つける
                while (node && node !== this.editor) {
                    if (node.classList && node.classList.contains('editor-line')) {
                        return node;
                    }
                    node = node.parentNode;
                }
                return null;
            }

            getLineFromEvent(e) {
                let target = e.target;
                while (target && target !== this.editor) {
                    if (target.classList && target.classList.contains('editor-line')) {
                        return target;
                    }
                    target = target.parentNode;
                }
                return null;
            }

            ensureLineStructure() {
                if (this.editor.children.length === 0) {
                    const firstLine = document.createElement('div');
                    firstLine.classList.add('editor-line');
                    firstLine.setAttribute('contenteditable', 'true');
                    firstLine.setAttribute('data-markdown', ''); // 空の原文で初期化
                    this.editor.appendChild(firstLine);
                    firstLine.focus();
                }
            }


            createNewLine() {
                const newLine = document.createElement('div');
                newLine.classList.add('editor-line');
                newLine.setAttribute('contenteditable', 'true');
                newLine.setAttribute('data-markdown', ''); // 空の原文で初期化
                
                // カーソル位置の後に挿入
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    let currentLine = range.startContainer;
                    
                    if (currentLine.nodeType === Node.TEXT_NODE) {
                        currentLine = currentLine.parentNode;
                    }
                    
                    while (currentLine && !currentLine.classList.contains('editor-line')) {
                        currentLine = currentLine.parentNode;
                    }
                    
                    if (currentLine) {
                        currentLine.parentNode.insertBefore(newLine, currentLine.nextSibling);
                    } else {
                        this.editor.appendChild(newLine);
                    }
                } else {
                    this.editor.appendChild(newLine);
                }
                
                // 新しい行にカーソルを設定（最後尾に）
                setTimeout(() => {
                    const range = document.createRange();
                    const sel = window.getSelection();
                    
                    // 行の最後尾にカーソルを設定
                    if (newLine.childNodes.length > 0) {
                        const lastChild = newLine.childNodes[newLine.childNodes.length - 1];
                        if (lastChild.nodeType === Node.TEXT_NODE) {
                            range.setStart(lastChild, lastChild.textContent.length);
                        } else {
                            range.setStart(newLine, newLine.childNodes.length);
                        }
                    } else {
                        range.setStart(newLine, 0);
                    }
                    range.collapse(true);
                    
                    sel.removeAllRanges();
                    sel.addRange(range);
                    
                    newLine.focus();
                    this.currentLineElement = newLine;
                    
                    console.log('New line created and focused at end');
                }, 10);
            }

            async convertLineToHTML(lineElement) {
                if (this.isConverting || !lineElement) return;
                
                const markdown = lineElement.textContent.trim();
                if (!markdown) return;
                
                console.log('Converting to HTML:', markdown);
                
                try {
                    this.isConverting = true;
                    
                    // Markdownの原文を保存
                    lineElement.setAttribute('data-markdown', markdown);
                    
                    // コードブロック内かどうかチェック
                    if (this.isLineInCodeBlock(lineElement)) {
                        // コードブロック内の行は変換しない
                        console.log('Line is in code block, skipping conversion');
                        return;
                    }
                    
                    // 通常のHTML変換を実行
                    const html = this.simpleMarkdownToHTML(markdown);
                    if (html !== markdown) {
                        lineElement.innerHTML = html;
                        lineElement.classList.add('markdown-rendered');
                        console.log('Line converted to HTML');
                    }
                    
                } catch (error) {
                    console.error('Markdown conversion error:', error);
                } finally {
                    this.isConverting = false;
                }
            }

            isLineInCodeBlock(lineElement) {
                // 現在の行がコードブロック内かどうかを判定
                const lines = this.editor.querySelectorAll('.editor-line');
                let inCodeBlock = false;
                
                for (let line of lines) {
                    const text = line.getAttribute('data-markdown') || line.textContent.trim();
                    
                    if (text.startsWith('```')) {
                        if (line === lineElement) {
                            // 現在の行が```の行の場合は変換しない
                            return true;
                        }
                        inCodeBlock = !inCodeBlock;
                    } else if (line === lineElement) {
                        return inCodeBlock;
                    }
                }
                
                return false;
            }


            simpleMarkdownToHTML(markdown) {
                return markdown
                    // 見出し（最初に処理）
                    .replace(/^#### (.*$)/gm, '<span class="text-base font-bold">$1</span>')
                    .replace(/^### (.*$)/gm, '<span class="text-lg font-bold">$1</span>')
                    .replace(/^## (.*$)/gm, '<span class="text-xl font-bold">$1</span>')
                    .replace(/^# (.*$)/gm, '<span class="text-2xl font-bold">$1</span>')
                    
                    // 引用
                    .replace(/^> (.*$)/gm, '<div class="border-l-4 border-gray-300 pl-4 italic text-gray-600">$1</div>')
                    
                    // 水平線
                    .replace(/^---$/gm, '<hr class="border-gray-300 my-2">')
                    .replace(/^\*\*\*$/gm, '<hr class="border-gray-300 my-2">')
                    
                    // リスト
                    .replace(/^- (.*$)/gm, '<span class="flex items-start"><span class="mr-2">•</span><span>$1</span></span>')
                    .replace(/^\* (.*$)/gm, '<span class="flex items-start"><span class="mr-2">•</span><span>$1</span></span>')
                    .replace(/^\+ (.*$)/gm, '<span class="flex items-start"><span class="mr-2">•</span><span>$1</span></span>')
                    .replace(/^(\d+)\. (.*$)/gm, '<span class="flex items-start"><span class="mr-2">$1.</span><span>$2</span></span>')
                    
                    // チェックボックス
                    .replace(/^- \[ \] (.*$)/gm, '<span class="flex items-start"><span class="mr-2">☐</span><span>$1</span></span>')
                    .replace(/^- \[x\] (.*$)/gm, '<span class="flex items-start"><span class="mr-2">☑</span><span class="line-through">$1</span></span>')
                    
                    // インライン要素（最後に処理）
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/`(.*?)`/g, '<code class="bg-gray-200 px-1 rounded">$1</code>')
                    .replace(/~~(.*?)~~/g, '<span class="line-through">$1</span>')
                    
                    // リンク
                    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-blue-600 underline">$1</a>');
            }

            convertLineToMarkdown(lineElement) {
                if (lineElement && lineElement.classList.contains('markdown-rendered')) {
                    const markdown = lineElement.getAttribute('data-markdown');
                    if (markdown) {
                        lineElement.textContent = markdown;
                        // data-markdown属性も更新（textContentと一致させる）
                        lineElement.setAttribute('data-markdown', markdown);
                        lineElement.classList.remove('markdown-rendered');
                        console.log('Line converted back to Markdown');
                    }
                } else if (lineElement) {
                    // HTML変換されていない行も、data-markdown属性を現在のtextContentで更新
                    const currentText = lineElement.textContent || '';
                    lineElement.setAttribute('data-markdown', currentText);
                }
            }

            convertAllLinesToMarkdown() {
                const lines = this.editor.querySelectorAll('.editor-line');
                lines.forEach(line => {
                    this.convertLineToMarkdown(line);
                });
                this.updateHiddenInput();
            }

            updateHiddenInput() {
                const lines = this.editor.querySelectorAll('.editor-line');
                const contentLines = Array.from(lines).map(line => {
                    // data-markdown属性がある場合は原文を使用、なければtextContentを使用
                    const markdown = line.getAttribute('data-markdown');
                    const text = markdown || line.textContent || '';
                    // 空行も含めて保持する
                    return text;
                });
                
                // 最後の空行を除去しない（すべての行を保持）
                const content = contentLines.join('\n');
                this.hiddenInput.value = content;
                console.log('Hidden input updated lines:', contentLines);
                console.log('Hidden input final content:', content);
            }

            updatePlaceholder() {
                const isEmpty = !this.editor.textContent.trim();
                if (isEmpty) {
                    this.editor.classList.add('empty');
                } else {
                    this.editor.classList.remove('empty');
                }
            }
        }

        // エディターを初期化
        new MarkdownEditor();
    </script>

    <style>
        #body-editor.empty:before {
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
            /* 変換された行のスタイルを削除 - 通常のテキストと同じ見た目に */
        }
        
        #body-editor:focus {
            outline: none;
        }
    </style>
@endsection