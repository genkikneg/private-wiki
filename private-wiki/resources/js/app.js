import './bootstrap';
import { SidebarController } from './sidebar.js';

// DOM読み込み完了後にサイドバーコントローラーを初期化
document.addEventListener('DOMContentLoaded', () => {
    new SidebarController();
});
