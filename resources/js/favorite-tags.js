export class FavoriteTagsController {
    constructor() {
        this.initializeFavoriteTagButtons();
    }

    initializeFavoriteTagButtons() {
        const favoriteTagButtons = document.querySelectorAll('.favorite-tag-btn');
        
        favoriteTagButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const tagName = button.dataset.tag;
                this.performTagSearch(tagName);
            });
        });
    }

    performTagSearch(tagName) {
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('tag', tagName);
        currentUrl.searchParams.delete('title');
        
        window.location.href = currentUrl.toString();
    }
}