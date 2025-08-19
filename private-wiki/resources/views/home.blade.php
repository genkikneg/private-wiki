@extends('layouts.app')

@section('title', 'ホーム')

@section('content')
    <form method="GET" action="/" class="mb-8 flex flex-col md:flex-row gap-4 items-center" id="search-form">
        <input
            type="text"
            name="title"
            value="{{ request('title') }}"
            placeholder="タイトルで検索"
            class="w-full md:w-1/3 px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
        >
        <div class="w-full md:w-1/3 relative">
            <div
                id="tag-container"
                class="flex flex-wrap items-center px-4 py-2 border rounded focus-within:ring-2 focus-within:ring-blue-400 bg-white"
            >
                <input
                    type="text"
                    id="tag-input"
                    class="flex-grow border-none focus:outline-none placeholder-gray-400"
                    placeholder="タグを入力"
                    autocomplete="off"
                >
            </div>
            <ul id="tag-suggestions" class="absolute bg-white border w-full z-10 hidden"></ul>
            <input type="hidden" name="tag" id="tag-hidden">
        </div>
        <button
            type="submit"
            class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
        >
            検索
        </button>
    </form>

    {{-- 記事一覧 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($notes as $note)
            <a href="{{ route('notes.show', $note->id) }}" class="bg-white p-4 rounded shadow mb-4 h-40 flex flex-col justify-between hover:bg-blue-50 transition">
                <div>
                    <h2 class="text-xl font-semibold truncate">{{ $note->title }}</h2>
                </div>
                <div class="mt-2 flex flex-wrap gap-2 items-center justify-between">
                    <div class="flex flex-wrap gap-2 items-center">
                        @if($note->tags && count($note->tags))
                            @foreach($note->tags as $tag)
                                <span class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded">{{ $tag->name }}</span>
                            @endforeach
                        @endif
                    </div>
                    <span class="text-gray-500 text-xs ml-auto">
                        最終更新: {{ \Carbon\Carbon::parse($note->updated_at)->format('Y/m/d H:i') }}
                    </span>
                </div>
            </a>
        @endforeach
    </div>
    <div class="mt-6">
        {{-- 現在のページ数表示 --}}
        @if($notes->hasPages())
            <div class="text-center mb-4">
                <span class="text-sm text-gray-600">
                    ページ {{ $notes->currentPage() }} / {{ $notes->lastPage() }} 
                    （全 {{ $notes->total() }} 件中 {{ $notes->firstItem() }}-{{ $notes->lastItem() }} 件を表示）
                </span>
            </div>
        @endif
        
        {{-- ページネーションリンク --}}
        <div class="flex justify-center">
            {{ $notes->links() }}
        </div>
    </div>

    <script>
    const tagContainer = document.getElementById('tag-container');
    const tagInput = document.getElementById('tag-input');
    const tagSuggestions = document.getElementById('tag-suggestions');
    const tagHidden = document.getElementById('tag-hidden');
    let tags = [];

    function renderTags() {
        // すべてのタイルを消去
        tagContainer.querySelectorAll('.tag').forEach(tag => tag.remove());

        // タイルを順に表示
        tags.forEach((tag, index) => {
            const span = document.createElement('span');
            span.className = 'tag bg-blue-200 text-blue-800 text-xs px-2 py-1 rounded flex items-center gap-1 mr-2';
            span.textContent = tag;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.textContent = '×';
            remove.className = 'ml-1 text-xs text-red-500';
            remove.onclick = () => {
                tags.splice(index, 1);
                renderTags();
            };

            span.appendChild(remove);
            tagContainer.insertBefore(span, tagInput);
        });

        // hidden にセット（そのまま送信）
        tagHidden.value = tags.join(',');
    }

    function tryCombineTags() {
        if (tags.length >= 3) {
            const last = tags.length - 1;
            if (tags[last - 1] === '::') {
                const combined = `${tags[last - 2]}::${tags[last]}`;
                // 3つ削除して結合タイルを挿入
                tags.splice(last - 2, 3, combined);
            }
        }
    }

    tagInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = tagInput.value.trim();
            if (!value) return;

            tags.push(value);  // タグを追加
            tagInput.value = ''; // 入力欄をクリア
            tryCombineTags();  // 条件がそろえば結合
            renderTags();

            // 検索候補をクリアして非表示にする
            tagSuggestions.innerHTML = '';
            tagSuggestions.classList.add('hidden');
        }
    });

    // タグ候補クリック時
    tagSuggestions.addEventListener('mousedown', function (e) {
        if (e.target.tagName === 'LI') {
            const tag = e.target.textContent.trim();
            if (tag) {
                tags.push(tag);
                tryCombineTags();
                renderTags();
                tagInput.value = '';
                tagSuggestions.innerHTML = '';
                tagSuggestions.classList.add('hidden');
            }
        }
    });

    // 候補表示
    tagInput.addEventListener('input', function () {
        const query = tagInput.value.trim();
        if (!query) {
            tagSuggestions.innerHTML = '';
            tagSuggestions.classList.add('hidden');
            return;
        }

        fetch(`/tags?query=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                tagSuggestions.innerHTML = '';
                let hasSuggestions = false;

                data.forEach(tag => {
                    if (!tags.includes(tag.name)) {
                        const li = document.createElement('li');
                        li.textContent = tag.name;
                        li.className = 'px-2 py-1 cursor-pointer hover:bg-blue-100';
                        li.addEventListener('mousedown', (e) => {
                            e.preventDefault();
                            tags.push(tag.name);
                            tryCombineTags();
                            renderTags();
                            tagInput.value = '';
                            tagSuggestions.innerHTML = '';
                            tagSuggestions.classList.add('hidden');
                        });
                        tagSuggestions.appendChild(li);
                        hasSuggestions = true;
                    }
                });

                tagSuggestions.classList.toggle('hidden', !hasSuggestions);
            });
    });

    // 初期値読み込み
    document.addEventListener('DOMContentLoaded', function () {
        const initial = "{{ request('tag') }}";
        if (initial) {
            tags = initial.split(',').map(t => t.trim()).filter(t => t);
            renderTags();
        }
    });
</script>

    <style>
    #tag-container {
        background-color: #ffffff;
    }

    #tag-input::placeholder {
        color: #9ca3af;
        opacity: 1;
        font-size: 1rem;
    }

    .tag button {
        font-size: 1.25rem;
        line-height: 1;
        padding: 0 0.5rem;
        cursor: pointer;
    }
    </style>
@endsection