export class MarkdownEditor {
  constructor({ editorElement, hiddenInput, form } = {}) {
    this.editor = editorElement ?? document.getElementById('body-editor');
    this.hiddenInput = hiddenInput ?? document.getElementById('body');
    this.form = form ?? (this.editor ? this.editor.closest('form') : null);

    if (!this.editor || !this.hiddenInput) {
      throw new Error('MarkdownEditor requires editor and hidden input elements.');
    }

    this.currentLineElement = null;
    this.isConverting = false;

    this.handleKeydown = this.handleKeydown.bind(this);
    this.handleKeyup = this.handleKeyup.bind(this);
    this.handleInput = this.handleInput.bind(this);
    this.handleClick = this.handleClick.bind(this);

    this.init();
  }

  init() {
    this.editor.addEventListener('keydown', this.handleKeydown);
    this.editor.addEventListener('keyup', this.handleKeyup);
    this.editor.addEventListener('input', this.handleInput);
    this.editor.addEventListener('click', this.handleClick);

    if (this.form) {
      this.form.addEventListener('submit', () => {
        this.convertAllLinesToMarkdown();
      });
    }

    this.ensureLineStructure();
    this.updatePlaceholder();
    this.updateHiddenInput();
  }

  loadMarkdown(markdownText = '') {
    const normalized = markdownText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    const lines = normalized.length > 0 ? normalized.split('\n') : [''];

    this.editor.innerHTML = '';
    lines.forEach((line) => {
      this.createLine(line);
    });

    const firstLine = this.editor.querySelector('.editor-line');
    this.currentLineElement = firstLine ?? null;

    if (firstLine) {
      this.convertLineToMarkdown(firstLine);
    }

    this.updateHiddenInput();
    this.updatePlaceholder();
  }

  handleKeydown(event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      const currentLine = this.getCurrentLine();
      if (currentLine) {
        this.convertLineToHTML(currentLine);
      }
      this.createNewLine();
    }
  }

  handleKeyup(event) {
    if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key)) {
      this.checkLineChange();
    }
  }

  handleInput() {
    this.updatePlaceholder();
    const currentLine = this.getCurrentLine();
    if (currentLine) {
      const currentText = currentLine.textContent || '';
      currentLine.setAttribute('data-markdown', currentText);
    }
    this.updateHiddenInput();
  }

  handleClick(event) {
    if (this.currentLineElement) {
      this.convertLineToHTML(this.currentLineElement);
    }

    const clickedLine = this.getLineFromEvent(event);
    if (clickedLine) {
      this.convertLineToMarkdown(clickedLine);
      this.currentLineElement = clickedLine;
      setTimeout(() => {
        this.setCursorToEnd(clickedLine);
      }, 0);
    }
  }

  ensureLineStructure() {
    const existingLines = this.editor.querySelectorAll('.editor-line');
    if (existingLines.length > 0) {
      return;
    }

    const initialText = this.editor.textContent || '';
    this.editor.innerHTML = '';

    const lines = initialText ? initialText.replace(/\r\n/g, '\n').split('\n') : [''];
    lines.forEach((line) => this.createLine(line));

    const firstLine = this.editor.querySelector('.editor-line');
    this.currentLineElement = firstLine ?? null;
  }

  createLine(text = '') {
    const line = document.createElement('div');
    line.classList.add('editor-line');
    line.setAttribute('contenteditable', 'true');
    line.setAttribute('data-markdown', text);
    line.textContent = text;
    this.editor.appendChild(line);
    return line;
  }

  createNewLine() {
    const newLine = this.createLine('');

    setTimeout(() => {
      const range = document.createRange();
      const selection = window.getSelection();
      range.selectNodeContents(newLine);
      range.collapse(false);
      selection.removeAllRanges();
      selection.addRange(range);
      newLine.focus();
      this.currentLineElement = newLine;
    }, 0);
  }

  getCurrentLine() {
    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0) {
      return null;
    }

    let node = selection.getRangeAt(0).startContainer;
    if (node.nodeType === Node.TEXT_NODE) {
      node = node.parentNode;
    }

    while (node && node !== this.editor) {
      if (node.classList && node.classList.contains('editor-line')) {
        return node;
      }
      node = node.parentNode;
    }

    return null;
  }

  getLineFromEvent(event) {
    let target = event.target;
    while (target && target !== this.editor) {
      if (target.classList && target.classList.contains('editor-line')) {
        return target;
      }
      target = target.parentNode;
    }
    return null;
  }

  checkLineChange() {
    const newLine = this.getCurrentLine();
    if (newLine && newLine !== this.currentLineElement) {
      if (this.currentLineElement) {
        this.convertLineToHTML(this.currentLineElement);
      }
      this.convertLineToMarkdown(newLine);
      this.currentLineElement = newLine;
      setTimeout(() => {
        this.setCursorToEnd(newLine);
      }, 0);
    }
  }

  setCursorToEnd(element) {
    const range = document.createRange();
    const selection = window.getSelection();

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
    selection.removeAllRanges();
    selection.addRange(range);
  }

  async convertLineToHTML(lineElement) {
    if (this.isConverting || !lineElement) {
      return;
    }

    const markdown = (lineElement.textContent || '').trim();
    if (!markdown) {
      lineElement.setAttribute('data-markdown', '');
      return;
    }

    this.isConverting = true;
    lineElement.setAttribute('data-markdown', markdown);

    try {
      if (this.isLineInCodeBlock(lineElement)) {
        return;
      }

      const html = this.simpleMarkdownToHTML(markdown);
      if (html !== markdown) {
        lineElement.innerHTML = html;
        lineElement.classList.add('markdown-rendered');
      }
    } finally {
      this.isConverting = false;
    }
  }

  isLineInCodeBlock(lineElement) {
    const lines = this.editor.querySelectorAll('.editor-line');
    let inCodeBlock = false;

    for (const line of lines) {
      const text = line.getAttribute('data-markdown') || line.textContent.trim();

      if (text.startsWith('```')) {
        if (line === lineElement) {
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
      .replace(/^#### (.*$)/gm, '<span class="text-base font-bold">$1</span>')
      .replace(/^### (.*$)/gm, '<span class="text-lg font-bold">$1</span>')
      .replace(/^## (.*$)/gm, '<span class="text-xl font-bold">$1</span>')
      .replace(/^# (.*$)/gm, '<span class="text-2xl font-bold">$1</span>')
      .replace(/^> (.*$)/gm, '<div class="border-l-4 border-gray-300 pl-4 italic text-gray-600">$1</div>')
      .replace(/^---$/gm, '<hr class="border-gray-300 my-2">')
      .replace(/^\*\*\*$/gm, '<hr class="border-gray-300 my-2">')
      .replace(/^- (.*$)/gm, '<span class="flex items-start"><span class="mr-2">•</span><span>$1</span></span>')
      .replace(/^\* (.*$)/gm, '<span class="flex items-start"><span class="mr-2">•</span><span>$1</span></span>')
      .replace(/^\+ (.*$)/gm, '<span class="flex items-start"><span class="mr-2">•</span><span>$1</span></span>')
      .replace(/^(\d+)\. (.*$)/gm, '<span class="flex items-start"><span class="mr-2">$1.</span><span>$2</span></span>')
      .replace(/^- \[ \] (.*$)/gm, '<span class="flex items-start"><span class="mr-2">☐</span><span>$1</span></span>')
      .replace(/^- \[x\] (.*$)/gm, '<span class="flex items-start"><span class="mr-2">☑</span><span class="line-through">$1</span></span>')
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.*?)\*/g, '<em>$1</em>')
      .replace(/`(.*?)`/g, '<code class="bg-gray-200 px-1 rounded">$1</code>')
      .replace(/~~(.*?)~~/g, '<span class="line-through">$1</span>')
      .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-blue-600 underline">$1</a>');
  }

  convertLineToMarkdown(lineElement) {
    if (!lineElement) {
      return;
    }

    if (lineElement.classList.contains('markdown-rendered')) {
      const markdown = lineElement.getAttribute('data-markdown');
      if (markdown) {
        lineElement.textContent = markdown;
        lineElement.setAttribute('data-markdown', markdown);
        lineElement.classList.remove('markdown-rendered');
      }
    } else {
      const currentText = lineElement.textContent || '';
      lineElement.setAttribute('data-markdown', currentText);
    }
  }

  convertAllLinesToMarkdown() {
    const lines = this.editor.querySelectorAll('.editor-line');
    lines.forEach((line) => this.convertLineToMarkdown(line));
    this.updateHiddenInput();
  }

  updateHiddenInput() {
    const lines = this.editor.querySelectorAll('.editor-line');
    const contentLines = Array.from(lines).map((line) => {
      const markdown = line.getAttribute('data-markdown');
      const text = markdown !== null ? markdown : (line.textContent || '');
      return text;
    });
    this.hiddenInput.value = contentLines.join('\n');
  }

  updatePlaceholder() {
    const isEmpty = !(this.hiddenInput.value.trim() || this.editor.textContent.trim());
    if (isEmpty) {
      this.editor.classList.add('empty');
    } else {
      this.editor.classList.remove('empty');
    }
  }
}
