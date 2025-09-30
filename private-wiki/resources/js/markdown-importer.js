export function setupMarkdownImport({
  markdownEditor,
  fileInput,
  importButton,
  titleInput,
  fileNameLabel,
  errorLabel,
} = {}) {
  if (!markdownEditor || !fileInput) {
    return;
  }

  const clearError = () => {
    if (errorLabel) {
      errorLabel.textContent = '';
      errorLabel.classList.add('hidden');
    }
  };

  const showError = (message) => {
    if (errorLabel) {
      errorLabel.textContent = message;
      errorLabel.classList.remove('hidden');
    }
  };

  const updateFileName = (name) => {
    if (fileNameLabel) {
      fileNameLabel.textContent = name || '';
    }
  };

  const importFromFile = (file) => {
    if (!file) {
      showError('Markdownファイルを選択してください。');
      return;
    }

    const fileName = file.name || '';
    const lowerName = fileName.toLowerCase();
    const isMarkdown = lowerName.endsWith('.md') || file.type === 'text/markdown';

    if (!isMarkdown) {
      showError('拡張子が .md のファイルを選択してください。');
      return;
    }

    clearError();

    const reader = new FileReader();
    reader.onload = (event) => {
      const content = (event.target?.result ?? '').toString();
      markdownEditor.loadMarkdown(content);
      updateFileName(fileName);

      if (titleInput && !titleInput.value.trim()) {
        const baseName = fileName.replace(/\.[^.]+$/, '');
        titleInput.value = baseName;
      }
    };

    reader.onerror = () => {
      showError('ファイルの読み込みに失敗しました。');
    };

    reader.readAsText(file);
  };

  const handleFileSelection = () => {
    clearError();
    const file = fileInput.files && fileInput.files[0];
    if (file) {
      updateFileName(file.name);
      importFromFile(file);
    } else {
      updateFileName('');
    }
  };

  fileInput.addEventListener('change', handleFileSelection);

  if (importButton) {
    importButton.addEventListener('click', (event) => {
      event.preventDefault();
      const file = fileInput.files && fileInput.files[0];
      if (file) {
        importFromFile(file);
      } else {
        fileInput.click();
      }
    });
  }
}
