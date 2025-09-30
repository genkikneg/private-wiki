import './bootstrap';
import { SidebarController } from './sidebar.js';
import { FavoriteTagsController } from './favorite-tags.js';
import { FavoriteTagsManagement } from './favorite-tags-management.js';
import { MarkdownEditor } from './markdown-editor.js';
import { setupMarkdownImport } from './markdown-importer.js';

// DOM読み込み完了後にコントローラーを初期化
document.addEventListener('DOMContentLoaded', () => {
    new SidebarController();
    new FavoriteTagsController();
    
    const favoriteTagsList = document.getElementById('favorite-tags-management-list');
    if (favoriteTagsList) {
        new FavoriteTagsManagement();
    }

    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
    }

    const editorElement = document.getElementById('body-editor');
    const hiddenInput = document.getElementById('body');
    if (editorElement && hiddenInput) {
        const form = editorElement.closest('form');
        const markdownEditor = new MarkdownEditor({
            editorElement,
            hiddenInput,
            form,
        });

        const fileInput = document.getElementById('markdown-file');
        if (fileInput) {
            setupMarkdownImport({
                markdownEditor,
                fileInput,
                importButton: document.getElementById('import-markdown'),
                titleInput,
                fileNameLabel: document.getElementById('markdown-file-name'),
                errorLabel: document.getElementById('markdown-import-error'),
            });
        }
    }
});
