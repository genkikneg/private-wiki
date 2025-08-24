import './bootstrap';
import { SidebarController } from './sidebar.js';
import { FavoriteTagsController } from './favorite-tags.js';
import { FavoriteTagsManagement } from './favorite-tags-management.js';

// DOM読み込み完了後にコントローラーを初期化
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing controllers...');
    new SidebarController();
    new FavoriteTagsController();
    
    // お気に入りタグ管理ページの場合のみ初期化
    const favoriteTagsList = document.getElementById('favorite-tags-management-list');
    console.log('Checking for favorite-tags-management-list:', favoriteTagsList);
    if (favoriteTagsList) {
        console.log('Initializing FavoriteTagsManagement...');
        new FavoriteTagsManagement();
    }
});
