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
    this.ready = Promise.resolve();

    this.handleKeydown = this.handleKeydown.bind(this);
    this.handleKeyup = this.handleKeyup.bind(this);
    this.handleInput = this.handleInput.bind(this);
    this.handleClick = this.handleClick.bind(this);
    this.handlePaste = this.handlePaste.bind(this);
    this.updateHoverTitle = this.updateHoverTitle.bind(this);
    this.handleFocus = this.handleFocus.bind(this);
    this.handleBlur = this.handleBlur.bind(this);

    this.init();
  }

  init() {
    this.editor.addEventListener('keydown', this.handleKeydown);
    this.editor.addEventListener('keyup', this.handleKeyup);
    this.editor.addEventListener('input', this.handleInput);
    this.editor.addEventListener('click', this.handleClick);
    this.editor.addEventListener('paste', this.handlePaste);
    this.editor.addEventListener('focus', this.handleFocus);
    this.editor.addEventListener('blur', this.handleBlur);

    if (this.form) {
      this.form.addEventListener('submit', () => {
        this.convertAllLinesToMarkdown();
      });
    }

    const initialMarkdown = (this.hiddenInput.value || this.editor.textContent || '').replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    if (initialMarkdown.length > 0) {
      this.ready = this.loadMarkdown(initialMarkdown);
    } else {
      this.ensureLineStructure();
      this.updatePlaceholder();
      this.updateHiddenInput();
      this.ready = Promise.resolve();
    }
  }

  async loadMarkdown(markdownText = '') {
    const normalized = markdownText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    const lines = normalized.length > 0 ? normalized.split('\n') : [''];

    this.editor.innerHTML = '';
    const createdLines = [];
    lines.forEach((line) => {
      const lineElement = this.createLine(line);
      createdLines.push(lineElement);
    });

    for (const lineElement of createdLines) {
      // 逐次変換して描画タイミングのズレを防ぐ
      await this.convertLineToHTML(lineElement);
    }

    this.currentLineElement = null;

    this.updateHiddenInput();
    this.updatePlaceholder();
  }

  handleKeydown(event) {
    if (event.key === 'Enter') {
      if (event.isComposing || event.keyCode === 229) {
        return;
      }
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
    const currentLine = this.getCurrentLine();
    if (currentLine) {
      const currentText = currentLine.textContent || '';
      currentLine.setAttribute('data-markdown', currentText);
      this.updateHoverTitle(currentLine, currentText);
    }
    this.updateHiddenInput();
    this.updatePlaceholder();
  }

  handleFocus() {
    this.editor.classList.add('is-focused');
    this.updatePlaceholder();
  }

  handleBlur() {
    this.editor.classList.remove('is-focused');
    this.updatePlaceholder();
  }

  getCollapsedCaretInfo(lineElement) {
    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0) {
      return null;
    }

    const range = selection.getRangeAt(0);
    if (!range.collapsed || !lineElement.contains(range.startContainer)) {
      return null;
    }

    const preCaretRange = range.cloneRange();
    preCaretRange.selectNodeContents(lineElement);
    preCaretRange.setEnd(range.startContainer, range.startOffset);

    return {
      offset: preCaretRange.toString().length,
    };
  }

  getSelectionOffsets() {
    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0) {
      return null;
    }

    const range = selection.getRangeAt(0);
    const lines = Array.from(this.editor.querySelectorAll('.editor-line'));
    if (lines.length === 0) {
      return null;
    }

    const lineTexts = this.getEditorMarkdownLines(lines);
    const start = this.calculateGlobalOffset(range.startContainer, range.startOffset, lines, lineTexts);
    const end = this.calculateGlobalOffset(range.endContainer, range.endOffset, lines, lineTexts);

    if (start === null || end === null) {
      return null;
    }

    return { start, end, lineTexts };
  }

  calculateGlobalOffset(node, offset, lines, lineTexts) {
    const lineElement = this.findLineElement(node);
    if (!lineElement) {
      return null;
    }

    const lineIndex = lines.indexOf(lineElement);
    if (lineIndex === -1) {
      return null;
    }

    const offsetWithinLine = this.calculateOffsetWithinLine(lineElement, node, offset);
    let globalOffset = offsetWithinLine;

    for (let i = 0; i < lineIndex; i += 1) {
      globalOffset += lineTexts[i].length;
      if (i < lineTexts.length - 1) {
        globalOffset += 1;
      }
    }

    return globalOffset;
  }

  findLineElement(node) {
    let current = node;
    while (current && current !== this.editor) {
      if (current.nodeType === Node.ELEMENT_NODE && current.classList.contains('editor-line')) {
        return current;
      }
      current = current.parentNode;
    }
    return null;
  }

  calculateOffsetWithinLine(lineElement, node, offset) {
    const range = document.createRange();
    range.selectNodeContents(lineElement);
    try {
      range.setEnd(node, offset);
    } catch (error) {
      range.setEnd(lineElement, lineElement.childNodes.length);
    }
    return range.toString().length;
  }

  getEditorMarkdownLines(lines = null) {
    const targetLines = lines ?? Array.from(this.editor.querySelectorAll('.editor-line'));
    return targetLines.map((line) => line.getAttribute('data-markdown') ?? line.textContent ?? '');
  }

  handleClick(event) {
    const clickedLine = this.getLineFromEvent(event);
    if (!clickedLine) {
      if (this.currentLineElement) {
        this.convertLineToHTML(this.currentLineElement);
        this.currentLineElement = null;
      }
      return;
    }

    if (this.currentLineElement && this.currentLineElement !== clickedLine) {
      this.convertLineToHTML(this.currentLineElement);
    }

    const wasRendered = clickedLine.classList.contains('markdown-rendered');
    const plainTextBeforeConvert = clickedLine.textContent || '';
    let collapsedCaretInfo = null;

    if (wasRendered) {
      collapsedCaretInfo = this.getCollapsedCaretInfo(clickedLine);
      if (collapsedCaretInfo) {
        collapsedCaretInfo.markdown = clickedLine.getAttribute('data-markdown') || '';
        collapsedCaretInfo.plainText = plainTextBeforeConvert;
      }
    }

    this.convertLineToMarkdown(clickedLine);
    this.currentLineElement = clickedLine;

    setTimeout(() => {
      if (wasRendered && collapsedCaretInfo) {
        const markdownAfterConvert = clickedLine.textContent || '';
        if (
          collapsedCaretInfo.plainText === collapsedCaretInfo.markdown &&
          markdownAfterConvert === collapsedCaretInfo.markdown
        ) {
          this.setCursorToOffset(clickedLine, collapsedCaretInfo.offset);
          return;
        }
      }

      const selection = window.getSelection();
      const hasActiveSelection = Boolean(
        selection &&
          selection.rangeCount > 0 &&
          !selection.isCollapsed &&
          clickedLine.contains(selection.getRangeAt(0).commonAncestorContainer),
      );

      if (wasRendered && !hasActiveSelection) {
        this.setCursorToEnd(clickedLine);
      }
    }, 0);
  }

  async handlePaste(event) {
    event.preventDefault();

    const clipboardData = event.clipboardData || window.clipboardData;
    const pastedText = clipboardData?.getData('text/plain') ?? '';

    if (!pastedText) {
      return;
    }

    const normalizedText = pastedText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    const selectionOffsets = this.getSelectionOffsets();

    if (!selectionOffsets) {
      this.insertPlainTextAtCursor(normalizedText);
      this.updateHiddenInput();
      this.updatePlaceholder();
      return;
    }

    this.updateHiddenInput();
    const currentMarkdown = selectionOffsets.lineTexts.join('\n');
    const before = currentMarkdown.slice(0, selectionOffsets.start);
    const after = currentMarkdown.slice(selectionOffsets.end);
    const newMarkdown = before + normalizedText + after;
    const newCaretOffset = selectionOffsets.start + normalizedText.length;

    await this.loadMarkdown(newMarkdown);

    this.setCursorByGlobalOffset(newCaretOffset);
    this.updateHiddenInput();
    this.updatePlaceholder();
    this.setCursorByGlobalOffset(newCaretOffset);
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
    this.updateHoverTitle(line, text);
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

  setCursorToOffset(element, offset) {
    const selection = window.getSelection();
    const range = document.createRange();
    const textNode = element.firstChild;

    if (!selection || !textNode || textNode.nodeType !== Node.TEXT_NODE) {
      this.setCursorToEnd(element);
      return;
    }

    const boundedOffset = Math.max(0, Math.min(offset, textNode.textContent.length));
    range.setStart(textNode, boundedOffset);
    range.collapse(true);
    selection.removeAllRanges();
    selection.addRange(range);
  }

  setCursorByGlobalOffset(globalOffset) {
    const lines = Array.from(this.editor.querySelectorAll('.editor-line'));
    if (lines.length === 0) {
      return;
    }

    const lineTexts = this.getEditorMarkdownLines(lines);
    const { lineIndex, offset } = this.calculateLinePositionFromGlobalOffset(globalOffset, lineTexts);
    const targetLine = lines[lineIndex] ?? lines[lines.length - 1];

    this.convertLineToMarkdown(targetLine);
    this.currentLineElement = targetLine;
    this.setCursorToOffset(targetLine, offset);
  }

  calculateLinePositionFromGlobalOffset(globalOffset, lineTexts) {
    let remaining = globalOffset;

    for (let i = 0; i < lineTexts.length; i += 1) {
      const textLength = lineTexts[i].length;
      if (remaining <= textLength) {
        return { lineIndex: i, offset: remaining };
      }

      remaining -= textLength;
      if (i < lineTexts.length - 1) {
        remaining -= 1;
      }
    }

    return {
      lineIndex: lineTexts.length - 1,
      offset: lineTexts[lineTexts.length - 1].length,
    };
  }

  insertPlainTextAtCursor(text) {
    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0) {
      return;
    }

    const range = selection.getRangeAt(0);
    range.deleteContents();

    const textNode = document.createTextNode(text);
    range.insertNode(textNode);
    range.setStartAfter(textNode);
    range.collapse(true);

    selection.removeAllRanges();
    selection.addRange(range);
  }

  async convertLineToHTML(lineElement) {
    if (this.isConverting || !lineElement) {
      return;
    }

    const rawText = lineElement.textContent || '';
    const markdown = rawText;
    const trimmed = markdown.trim();

    this.isConverting = true;
    lineElement.setAttribute('data-markdown', markdown);
    this.updateHoverTitle(lineElement, markdown);

    try {
      if (!trimmed) {
        lineElement.removeAttribute('data-type');
        lineElement.classList.remove('markdown-rendered');
        lineElement.textContent = markdown;
        return;
      }

      if (this.isLineInCodeBlock(lineElement)) {
        lineElement.removeAttribute('data-type');
        lineElement.classList.remove('markdown-rendered');
        lineElement.textContent = markdown;
        return;
      }

      const block = this.detectBlock(trimmed);
      if (!block) {
        lineElement.removeAttribute('data-type');
        lineElement.classList.remove('markdown-rendered');
        lineElement.textContent = markdown;
        return;
      }

      lineElement.setAttribute('data-type', block.type);
      lineElement.innerHTML = this.renderBlock(block);
      lineElement.classList.add('markdown-rendered');
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

  detectBlock(markdown) {
    const trimmed = markdown.trim();
    if (!trimmed) {
      return null;
    }

    const headingMatch = trimmed.match(/^(#{1,4})\s+(.*)$/);
    if (headingMatch) {
      const level = headingMatch[1].length;
      return {
        type: `heading-${level}`,
        level,
        content: headingMatch[2],
      };
    }

    if (/^(\*\*\*|---)$/.test(trimmed)) {
      return { type: 'divider', content: '' };
    }

    const quoteMatch = trimmed.match(/^> ?(.*)$/);
    if (quoteMatch) {
      return { type: 'quote', content: quoteMatch[1] };
    }

    const todoMatch = trimmed.match(/^[*-] \[( |x|X)\] (.*)$/);
    if (todoMatch) {
      const checked = todoMatch[1].toLowerCase() === 'x';
      return {
        type: checked ? 'todo-checked' : 'todo-unchecked',
        content: todoMatch[2],
        checked,
      };
    }

    const unorderedMatch = trimmed.match(/^[*+-] (.*)$/);
    if (unorderedMatch) {
      return { type: 'bullet-list-item', content: unorderedMatch[1] };
    }

    const orderedMatch = trimmed.match(/^(\d+)\. (.*)$/);
    if (orderedMatch) {
      return {
        type: 'ordered-list-item',
        content: orderedMatch[2],
        order: orderedMatch[1],
      };
    }

    return { type: 'paragraph', content: trimmed };
  }

  renderBlock(block) {
    switch (block.type) {
      case 'heading-1':
      case 'heading-2':
      case 'heading-3':
      case 'heading-4':
        return this.renderHeading(block.level, block.content);
      case 'quote':
        return `<blockquote class="border-l-4 border-gray-300 pl-4 italic text-gray-600">${this.renderInline(block.content)}</blockquote>`;
      case 'divider':
        return '<hr class="border-gray-300 my-2">';
      case 'todo-unchecked':
      case 'todo-checked':
        return this.renderTodo(block);
      case 'bullet-list-item':
        return `<div class="flex items-start gap-2"><span class="select-none">•</span><span>${this.renderInline(block.content)}</span></div>`;
      case 'ordered-list-item':
        return `<div class="flex items-start gap-2"><span class="select-none">${this.escapeHtml(block.order)}.</span><span>${this.renderInline(block.content)}</span></div>`;
      case 'paragraph':
      default:
        return `<p>${this.renderInline(block.content)}</p>`;
    }
  }

  renderHeading(level = 1, content = '') {
    const boundedLevel = Math.min(Math.max(level, 1), 4);
    const classMap = {
      1: 'text-2xl font-bold',
      2: 'text-xl font-bold',
      3: 'text-lg font-bold',
      4: 'text-base font-bold',
    };

    return `<h${boundedLevel} class="${classMap[boundedLevel]}">${this.renderInline(content)}</h${boundedLevel}>`;
  }

  renderTodo(block) {
    const checked = block.checked;
    const checkbox = `<input type="checkbox" ${checked ? 'checked' : ''} disabled class="mt-1 h-4 w-4 rounded border-gray-300">`;
    const textClass = checked ? 'line-through text-gray-500' : '';
    return `<label class="flex items-start gap-2">${checkbox}<span class="${textClass}">${this.renderInline(block.content)}</span></label>`;
  }

  renderInline(text) {
    if (!text) {
      return '';
    }

    let result = this.escapeHtml(text);

    result = result.replace(/\[([^\]]+)\]\(([^)]+)\)/g, (match, label, url) => {
      return `<a href="${url}" class="text-blue-600 underline">${label}</a>`;
    });

    result = result.replace(/`([^`]+)`/g, (match, code) => `<code class="bg-gray-200 px-1 rounded">${code}</code>`);
    result = result.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    result = result.replace(/__(.+?)__/g, '<strong>$1</strong>');
    result = result.replace(/\*(.+?)\*/g, '<em>$1</em>');
    result = result.replace(/_(.+?)_/g, '<em>$1</em>');
    result = result.replace(/~~(.+?)~~/g, '<span class="line-through">$1</span>');

    return result;
  }

  escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
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
        lineElement.removeAttribute('data-type');
        lineElement.classList.remove('markdown-rendered');
        this.updateHoverTitle(lineElement, markdown);
      }
    } else {
      const currentText = lineElement.textContent || '';
      lineElement.setAttribute('data-markdown', currentText);
      lineElement.removeAttribute('data-type');
      this.updateHoverTitle(lineElement, currentText);
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

  updateHoverTitle(lineElement, markdownText) {
    if (!lineElement) {
      return;
    }

    const raw = markdownText ?? lineElement.getAttribute('data-markdown') ?? '';
    if (raw.trim().length > 0) {
      lineElement.setAttribute('title', raw);
    } else {
      lineElement.removeAttribute('title');
    }
  }
}
