import { MarkdownEditor } from '../../resources/js/markdown-editor.js';
import { setupMarkdownImport } from '../../resources/js/markdown-importer.js';

describe('Markdown file import', () => {
  let editorElement;
  let hiddenInput;
  let form;
  let markdownEditor;
  let originalFileReader;

  beforeEach(() => {
    document.body.innerHTML = `
      <form id="note-form">
        <input type="text" id="title" value="">
        <div id="body-editor" data-placeholder="プレースホルダー"></div>
        <textarea id="body"></textarea>
        <input type="file" id="markdown-file" accept=".md,text/markdown">
        <p id="markdown-file-name"></p>
        <p id="markdown-import-error" class="hidden"></p>
      </form>
    `;

    editorElement = document.getElementById('body-editor');
    hiddenInput = document.getElementById('body');
    form = document.getElementById('note-form');

    markdownEditor = new MarkdownEditor({
      editorElement,
      hiddenInput,
      form,
    });

    originalFileReader = global.FileReader;
  });

  afterEach(() => {
    global.FileReader = originalFileReader;
    jest.restoreAllMocks();
  });

  test('loadMarkdown() populates editor lines and hidden input', () => {
    markdownEditor.loadMarkdown('行1\n行2');

    const lines = editorElement.querySelectorAll('.editor-line');
    expect(lines).toHaveLength(2);
    expect(lines[0].getAttribute('data-markdown')).toBe('行1');
    expect(lines[1].textContent).toBe('行2');
    expect(hiddenInput.value).toBe('行1\n行2');
  });

  test('auto-imports and sets title when file selection changes', () => {
    const fileInput = document.getElementById('markdown-file');
    const titleInput = document.getElementById('title');

    const mockFile = new File(['# 見出し\n本文'], 'sample.md', { type: 'text/markdown' });
    const filesGetter = jest.fn(() => [mockFile]);
    Object.defineProperty(fileInput, 'files', {
      get: filesGetter,
    });

    global.FileReader = jest.fn().mockImplementation(() => ({
      onload: null,
      onerror: null,
      readAsText(file) {
        this.onload({ target: { result: '# 見出し\n本文' } });
      },
    }));

    setupMarkdownImport({
      markdownEditor,
      fileInput,
      titleInput,
      fileNameLabel: document.getElementById('markdown-file-name'),
      errorLabel: document.getElementById('markdown-import-error'),
    });

    fileInput.dispatchEvent(new Event('change'));

    expect(hiddenInput.value).toBe('# 見出し\n本文');
    expect(titleInput.value).toBe('sample');
    expect(filesGetter).toHaveBeenCalled();
  });

  test('shows error when selected file is not markdown', () => {
    const fileInput = document.getElementById('markdown-file');
    const errorLabel = document.getElementById('markdown-import-error');

    const mockFile = new File(['content'], 'image.png', { type: 'image/png' });
    Object.defineProperty(fileInput, 'files', {
      get: () => [mockFile],
    });

    setupMarkdownImport({
      markdownEditor,
      fileInput,
      fileNameLabel: document.getElementById('markdown-file-name'),
      errorLabel,
    });

    fileInput.dispatchEvent(new Event('change'));

    expect(errorLabel.classList.contains('hidden')).toBe(false);
    expect(errorLabel.textContent).toContain('.md');
  });

  test('handles missing title input gracefully', () => {
    const fileInput = document.getElementById('markdown-file');

    const mockFile = new File(['内容'], 'note.md', { type: 'text/markdown' });
    Object.defineProperty(fileInput, 'files', {
      get: () => [mockFile],
    });

    global.FileReader = jest.fn().mockImplementation(() => ({
      onload: null,
      onerror: null,
      readAsText(file) {
        this.onload({ target: { result: '内容' } });
      },
    }));

    setupMarkdownImport({
      markdownEditor,
      fileInput,
      fileNameLabel: document.getElementById('markdown-file-name'),
      errorLabel: document.getElementById('markdown-import-error'),
    });

    fileInput.dispatchEvent(new Event('change'));

    expect(hiddenInput.value).toBe('内容');
  });
});
