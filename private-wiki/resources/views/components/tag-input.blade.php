@props(['name' => 'tags', 'value' => '', 'placeholder' => 'タグを入力してください'])

<div class="mt-1">
    <div id="{{ $name }}-container" class="flex flex-wrap items-center gap-1 p-2 border border-gray-300 rounded-md focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 bg-white min-h-[2.5rem]">
        <input type="text" 
               id="{{ $name }}-input" 
               placeholder="{{ $placeholder }}" 
               class="flex-1 min-w-32 border-0 outline-none focus:ring-0 bg-transparent"
               autocomplete="off">
    </div>
    <input type="hidden" name="{{ $name }}" id="{{ $name }}-hidden" value="{{ $value }}">
    <ul id="{{ $name }}-suggestions" class="hidden absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-40 overflow-y-auto mt-1"></ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tagContainer = document.getElementById('{{ $name }}-container');
    const tagInput = document.getElementById('{{ $name }}-input');
    const tagSuggestions = document.getElementById('{{ $name }}-suggestions');
    const tagHidden = document.getElementById('{{ $name }}-hidden');
    let tags = [];

    // 初期値の設定
    if (tagHidden.value) {
        tags = tagHidden.value.split(',').filter(tag => tag.trim());
        renderTags();
    }

    function renderTags() {
        // すべてのタグを消去
        tagContainer.querySelectorAll('.tag').forEach(tag => tag.remove());

        // タグを順に表示
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

        // hidden フィールドに値をセット
        tagHidden.value = tags.join(',');
    }

    function tryCombineTags() {
        if (tags.length >= 3) {
            const last = tags.length - 1;
            if (tags[last - 1] === '::') {
                const combined = `${tags[last - 2]}::${tags[last]}`;
                tags.splice(last - 2, 3, combined);
            }
        }
    }

    tagInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.isComposing) {
            e.preventDefault();
            const value = tagInput.value.trim();
            if (!value) return;

            if (!tags.includes(value)) {
                tags.push(value);
                tryCombineTags();
                renderTags();
            }
            
            // 重複でも入力欄は常にクリア
            setTimeout(() => {
                tagInput.value = '';
                tagInput.dispatchEvent(new Event('input'));
            }, 0);
            tagSuggestions.innerHTML = '';
            tagSuggestions.classList.add('hidden');
        }
    });

    // タグ候補クリック時
    tagSuggestions.addEventListener('mousedown', function (e) {
        if (e.target.tagName === 'LI') {
            const tag = e.target.textContent.trim();
            if (tag && !tags.includes(tag)) {
                tags.push(tag);
                tryCombineTags();
                renderTags();
            }
            // 重複でも入力欄は常にクリア
            tagInput.value = '';
            tagSuggestions.innerHTML = '';
            tagSuggestions.classList.add('hidden');
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
                        tagSuggestions.appendChild(li);
                        hasSuggestions = true;
                    }
                });

                if (hasSuggestions) {
                    tagSuggestions.classList.remove('hidden');
                } else {
                    tagSuggestions.classList.add('hidden');
                }
            })
            .catch(err => {
                console.error('タグ候補の取得に失敗しました:', err);
                tagSuggestions.innerHTML = '';
                tagSuggestions.classList.add('hidden');
            });
    });

    // フォーカス外れたら候補を隠す
    tagInput.addEventListener('blur', function() {
        setTimeout(() => {
            tagSuggestions.classList.add('hidden');
        }, 200);
    });
});
</script>