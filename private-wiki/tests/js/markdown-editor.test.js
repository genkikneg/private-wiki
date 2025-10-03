import { MarkdownEditor } from '../../resources/js/markdown-editor.js';

describe('MarkdownEditor の行変換', () => {
  const setup = ({ initialBody = '' } = {}) => {
    document.body.innerHTML = `
      <form>
        <div id="body-editor" data-placeholder="プレースホルダー"></div>
        <input type="hidden" id="body" value="">
      </form>
    `;

    const editor = document.getElementById('body-editor');
    const hiddenInput = document.getElementById('body');
    const form = document.querySelector('form');

    editor.textContent = initialBody;
    hiddenInput.value = initialBody;

    const markdownEditor = new MarkdownEditor({
      editorElement: editor,
      hiddenInput,
      form,
    });

    return { editor, hiddenInput, markdownEditor };
  };

  afterEach(() => {
    document.body.innerHTML = '';
  });

  test('Enter 相当の変換で見出しブロックに変換される', async () => {
    const { editor, markdownEditor } = setup();
    const line = editor.querySelector('.editor-line');
    line.textContent = '# タイトル';

    await markdownEditor.convertLineToHTML(line);

    expect(line.getAttribute('data-type')).toBe('heading-1');
    expect(line.innerHTML).toContain('<h1');
    expect(line.innerHTML).toContain('タイトル');
    expect(line.classList.contains('markdown-rendered')).toBe(true);
  });

  test('Enter 相当の変換で箇条書きブロックに変換される', async () => {
    const { editor, markdownEditor } = setup();
    const line = editor.querySelector('.editor-line');
    line.textContent = '- 箇条書き';

    await markdownEditor.convertLineToHTML(line);

    expect(line.getAttribute('data-type')).toBe('bullet-list-item');
    expect(line.innerHTML).toContain('箇条書き');
    expect(line.classList.contains('markdown-rendered')).toBe(true);
  });

  test('loadMarkdown 直後に各行がHTMLでプレビューされる', async () => {
    const { editor, markdownEditor } = setup();

    await markdownEditor.loadMarkdown('# 見出し\n- 箇条書き');

    const lines = editor.querySelectorAll('.editor-line');
    expect(lines).toHaveLength(2);
    expect(lines[0].classList.contains('markdown-rendered')).toBe(true);
    expect(lines[0].innerHTML).toContain('<h1');
    expect(lines[0].getAttribute('title')).toBe('# 見出し');
    expect(lines[1].getAttribute('title')).toBe('- 箇条書き');
  });

  test('HTML に変換された行はホバー表示用に title 属性へ Markdown を保持する', async () => {
    const { editor, markdownEditor } = setup();
    const line = editor.querySelector('.editor-line');
    line.textContent = '# 見出し';

    await markdownEditor.convertLineToHTML(line);

    expect(line.getAttribute('title')).toBe('# 見出し');
  });

  test('空行は HTML 変換後に title 属性が付かない', async () => {
    const { editor, markdownEditor } = setup();
    const line = editor.querySelector('.editor-line');
    line.textContent = '   ';

    await markdownEditor.convertLineToHTML(line);

    expect(line.hasAttribute('title')).toBe(false);
  });

  test('コードブロック内の行は変換されない', async () => {
    const { editor, markdownEditor } = setup();
    await markdownEditor.loadMarkdown('```\nconsole.log("hi");\n```');
    const lines = editor.querySelectorAll('.editor-line');

    await markdownEditor.convertLineToHTML(lines[1]);

    expect(lines[1].classList.contains('markdown-rendered')).toBe(false);
    expect(lines[1].getAttribute('data-type')).toBeNull();
    expect(lines[1].textContent).toBe('console.log("hi");');
  });

  test('コードブロック内のインデント付き行も保持される', async () => {
    const { editor, markdownEditor } = setup();
    await markdownEditor.loadMarkdown('```\n    console.log("hi");\n```');
    const lines = editor.querySelectorAll('.editor-line');

    await markdownEditor.convertLineToHTML(lines[1]);

    expect(lines[1].textContent).toBe('    console.log("hi");');
    expect(lines[1].getAttribute('data-markdown')).toBe('    console.log("hi");');
  });

  test('Markdown へ戻すとブロック情報が消える', async () => {
    const { editor, markdownEditor } = setup();
    const line = editor.querySelector('.editor-line');
    line.textContent = '## 小見出し';

    await markdownEditor.convertLineToHTML(line);
    markdownEditor.convertLineToMarkdown(line);

    expect(line.textContent).toBe('## 小見出し');
    expect(line.getAttribute('data-type')).toBeNull();
    expect(line.classList.contains('markdown-rendered')).toBe(false);
  });

  test('クリックでMarkdown編集モードに戻る', async () => {
    const { editor, markdownEditor } = setup();
    await markdownEditor.loadMarkdown('# 見出し');
    const line = editor.querySelector('.editor-line');

    expect(line.classList.contains('markdown-rendered')).toBe(true);

    line.dispatchEvent(new MouseEvent('click', { bubbles: true }));

    expect(line.classList.contains('markdown-rendered')).toBe(false);
    expect(line.textContent).toBe('# 見出し');
  });

  test('Enter キーが IME 確定中は新しい行を作らない', async () => {
    const { editor, markdownEditor } = setup();
    await markdownEditor.loadMarkdown('日本語の行');
    const preventDefault = jest.fn();

    markdownEditor.handleKeydown({
      key: 'Enter',
      isComposing: true,
      preventDefault,
      keyCode: 13,
    });

    expect(preventDefault).not.toHaveBeenCalled();
    expect(editor.querySelectorAll('.editor-line')).toHaveLength(1);

    preventDefault.mockClear();

    markdownEditor.handleKeydown({
      key: 'Enter',
      isComposing: false,
      preventDefault,
      keyCode: 13,
    });

    expect(preventDefault).toHaveBeenCalled();
    expect(editor.querySelectorAll('.editor-line')).toHaveLength(2);
  });

  test('編集中の行で選択したテキストはクリック後も選択状態を維持する', () => {
    jest.useFakeTimers();

    try {
      const { editor } = setup();
      const line = editor.querySelector('.editor-line');
      line.textContent = 'abcdef';

      const textNode = line.firstChild;
      const selection = window.getSelection();
      const range = document.createRange();
      range.setStart(textNode, 1);
      range.setEnd(textNode, 4);
      selection.removeAllRanges();
      selection.addRange(range);

      line.dispatchEvent(new MouseEvent('click', { bubbles: true }));

      jest.runOnlyPendingTimers();

      const resultSelection = window.getSelection();
      expect(resultSelection.rangeCount).toBe(1);
      const activeRange = resultSelection.getRangeAt(0);
      expect(activeRange.startOffset).toBe(1);
      expect(activeRange.endOffset).toBe(4);
    } finally {
      jest.useRealTimers();
    }
  });

  test('レンダリング済みの行をクリックしてもキャレット位置が維持される', async () => {
    jest.useFakeTimers();

    try {
      const { editor, markdownEditor } = setup();
      const line = editor.querySelector('.editor-line');
      line.textContent = 'abcdef';

      await markdownEditor.convertLineToHTML(line);

      const targetNode = line.querySelector('p')?.firstChild ?? line.firstChild;
      const selection = window.getSelection();
      const range = document.createRange();
      range.setStart(targetNode, 2);
      range.collapse(true);
      selection.removeAllRanges();
      selection.addRange(range);

      line.dispatchEvent(new MouseEvent('click', { bubbles: true }));

      jest.runOnlyPendingTimers();

      const resultSelection = window.getSelection();
      expect(resultSelection.rangeCount).toBe(1);
      const caretRange = resultSelection.getRangeAt(0);
      expect(caretRange.startContainer.nodeType).toBe(Node.TEXT_NODE);
      expect(caretRange.startOffset).toBe(2);
      expect(line.textContent).toBe('abcdef');
    } finally {
      jest.useRealTimers();
    }
  });

  test('既存のMarkdownを持つエディタは初期化時にHTMLプレビューが表示される', async () => {
    const { editor, markdownEditor } = setup({ initialBody: '# 見出し' });

    await markdownEditor.ready;

    const line = editor.querySelector('.editor-line');
    expect(line).not.toBeNull();
    expect(line.classList.contains('markdown-rendered')).toBe(true);
    expect(line.innerHTML).toContain('<h1');
    expect(line.getAttribute('title')).toBe('# 見出し');
  });

  test('入力を全て削除するとプレースホルダーが再表示される', () => {
    const { editor, hiddenInput } = setup();
    const line = editor.querySelector('.editor-line');

    expect(editor.classList.contains('empty')).toBe(true);

    const setCaretToLine = () => {
      const selection = window.getSelection();
      const range = document.createRange();
      range.selectNodeContents(line);
      range.collapse(false);
      selection.removeAllRanges();
      selection.addRange(range);
    };

    line.textContent = 'サンプル';
    setCaretToLine();
    editor.dispatchEvent(new Event('input', { bubbles: true }));

    expect(editor.classList.contains('empty')).toBe(false);
    expect(hiddenInput.value).toBe('サンプル');

    line.textContent = '';
    setCaretToLine();
    editor.dispatchEvent(new Event('input', { bubbles: true }));

    expect(hiddenInput.value).toBe('');
    expect(editor.classList.contains('empty')).toBe(true);
  });

  test('フォーカス時はプレースホルダーを隠すためのクラスが付与される', () => {
    const { editor } = setup();

    expect(editor.classList.contains('empty')).toBe(true);
    expect(editor.classList.contains('is-focused')).toBe(false);

    editor.dispatchEvent(new Event('focus'));

    expect(editor.classList.contains('is-focused')).toBe(true);
    expect(editor.classList.contains('empty')).toBe(true);

    editor.dispatchEvent(new Event('blur'));

    expect(editor.classList.contains('is-focused')).toBe(false);
    expect(editor.classList.contains('empty')).toBe(true);
  });

});
