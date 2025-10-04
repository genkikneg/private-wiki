export class FavoriteTagsManagement {
    constructor() {
        console.log('FavoriteTagsManagement initialized');
        this.draggedElement = null;
        this.placeholder = null;
        this.initializeSortable();
        this.initializeTagAutocomplete();
    }

    initializeSortable() {
        const favoriteTagsList = document.getElementById('favorite-tags-management-list');
        console.log('favoriteTagsList element:', favoriteTagsList);
        if (!favoriteTagsList) {
            console.log('favorite-tags-management-list element not found');
            return;
        }

        // Using class properties instead

        // Remove container-level dragstart listener since it's not working
        // We'll handle this in updateDraggableItems instead

        // dragend will be handled per item as well

        favoriteTagsList.addEventListener('dragover', (e) => {
            e.preventDefault();
            
            if (!this.draggedElement || !this.placeholder) return;
            
            // Remove placeholder if already in DOM
            if (this.placeholder.parentNode) {
                this.placeholder.parentNode.removeChild(this.placeholder);
            }
            
            const afterElement = this.getDragAfterElement(favoriteTagsList, e.clientY);
            
            if (afterElement == null) {
                favoriteTagsList.appendChild(this.placeholder);
            } else {
                favoriteTagsList.insertBefore(this.placeholder, afterElement);
            }
        });

        favoriteTagsList.addEventListener('drop', (e) => {
            e.preventDefault();
            
            if (this.draggedElement && this.placeholder && this.placeholder.parentNode) {
                // Insert the dragged element before the placeholder
                this.placeholder.parentNode.insertBefore(this.draggedElement, this.placeholder);
                this.placeholder.remove();
                
                // Reset opacity and update order
                this.draggedElement.style.opacity = '';
                this.updateOrder();
            }
        });

        // Make items draggable by adding draggable attribute
        this.updateDraggableItems();
    }

    updateDraggableItems() {
        const items = document.querySelectorAll('.favorite-tag-item');
        console.log('Updating draggable items, found:', items.length);
        
        items.forEach((item, index) => {
            console.log(`Setting up item ${index}:`, item);
            
            // Always make items draggable with explicit attribute
            item.setAttribute('draggable', 'true');
            item.draggable = true;
            
            console.log(`Item ${index} draggable attribute:`, item.getAttribute('draggable'));
            console.log(`Item ${index} draggable property:`, item.draggable);
            
            const dragHandle = item.querySelector('.drag-handle');
            console.log(`Drag handle for item ${index}:`, dragHandle);
            
            if (dragHandle) {
                dragHandle.style.cursor = 'grab';
                
                // Add click test
                dragHandle.addEventListener('click', (e) => {
                    console.log('Drag handle clicked!');
                });
                
                // Add mousedown listener to handle
                dragHandle.addEventListener('mousedown', (e) => {
                    console.log('Mousedown on drag handle');
                    dragHandle.style.cursor = 'grabbing';
                    
                    // Force draggable attribute again on mousedown
                    item.setAttribute('draggable', 'true');
                });
                
                dragHandle.addEventListener('mouseup', () => {
                    console.log('Mouseup on drag handle');
                    dragHandle.style.cursor = 'grab';
                });
                
                // Add dragstart test directly on handle
                dragHandle.addEventListener('dragstart', (e) => {
                    console.log('Dragstart on handle!');
                });
            }
            
            // Add direct dragstart listener to item - this is the main one
            item.addEventListener('dragstart', (e) => {
                console.log('Dragstart on item directly!', e.target);
                this.draggedElement = item;
                item.style.opacity = '0.5';
                
                // Create placeholder
                this.placeholder = document.createElement('div');
                this.placeholder.className = 'placeholder bg-blue-100 border-2 border-dashed border-blue-300 p-3 rounded';
                this.placeholder.innerHTML = '<div class="text-blue-500 text-center text-sm">ここにドロップ</div>';
                
                console.log('Created placeholder:', this.placeholder);
            });
            
            // Add dragend listener to item
            item.addEventListener('dragend', (e) => {
                console.log('Dragend on item!');
                item.style.opacity = '';
                if (this.placeholder && this.placeholder.parentNode) {
                    this.placeholder.remove();
                }
                this.draggedElement = null;
                this.placeholder = null;
            });
        });
    }

    getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.favorite-tag-item:not([style*="opacity: 0.5"])')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    updateOrder() {
        const items = document.querySelectorAll('#favorite-tags-management-list .favorite-tag-item');
        const favoriteTagIds = Array.from(items).map(item => {
            const id = parseInt(item.dataset.id);
            console.log('Item ID:', id, 'Element:', item); // Debug log
            return id;
        }).filter(id => !isNaN(id));

        console.log('Reordering with IDs:', favoriteTagIds); // Debug log

        if (favoriteTagIds.length === 0) {
            console.error('No valid favorite tag IDs found');
            return;
        }

        fetch('/api/favorite-tags/reorder', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                favorite_tag_ids: favoriteTagIds
            })
        })
        .then(response => {
            console.log('Response status:', response.status); // Debug log
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data); // Debug log
            if (data.status === 'success') {
                this.showMessage('順序を更新しました', 'success');
                // Update sidebar immediately
                this.updateSidebar();
            } else {
                this.showMessage('エラーが発生しました: ' + (data.message || '不明なエラー'), 'error');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            this.showMessage('通信エラーが発生しました', 'error');
        });
    }

    initializeTagAutocomplete() {
        const tagInput = document.getElementById('tag_name');
        const suggestionsList = document.getElementById('tag-suggestions-list');
        
        if (!tagInput || !suggestionsList) return;

        let debounceTimer = null;

        tagInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }

            debounceTimer = setTimeout(() => {
                if (query.length >= 1) {
                    this.fetchTagSuggestions(query, suggestionsList);
                } else {
                    suggestionsList.classList.add('hidden');
                }
            }, 300);
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!tagInput.contains(e.target) && !suggestionsList.contains(e.target)) {
                suggestionsList.classList.add('hidden');
            }
        });
    }

    fetchTagSuggestions(query, suggestionsList) {
        fetch(`/tags?search=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(tags => {
                this.displaySuggestions(tags, suggestionsList);
            })
            .catch(error => {
                console.error('Error fetching tags:', error);
                suggestionsList.classList.add('hidden');
            });
    }

    displaySuggestions(tags, suggestionsList) {
        suggestionsList.innerHTML = '';
        
        if (tags.length === 0) {
            suggestionsList.classList.add('hidden');
            return;
        }

        tags.slice(0, 10).forEach(tag => {
            const li = document.createElement('li');
            li.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm';
            li.textContent = tag.name;
            
            li.addEventListener('click', () => {
                document.getElementById('tag_name').value = tag.name;
                suggestionsList.classList.add('hidden');
            });
            
            suggestionsList.appendChild(li);
        });

        suggestionsList.classList.remove('hidden');
    }

    showMessage(message, type = 'success') {
        // Find the alert container (where session messages are displayed)
        const alertContainer = document.querySelector('.max-w-4xl');
        if (!alertContainer) return;
        
        // Create alert similar to the session alert component
        const alertEl = document.createElement('div');
        alertEl.setAttribute('x-data', '{ show: true }');
        alertEl.setAttribute('x-show', 'show');
        alertEl.setAttribute('x-transition:enter', 'transition ease-out duration-300');
        alertEl.setAttribute('x-transition:enter-start', 'opacity-0 transform scale-90');
        alertEl.setAttribute('x-transition:enter-end', 'opacity-100 transform scale-100');
        alertEl.setAttribute('x-transition:leave', 'transition ease-in duration-300');
        alertEl.setAttribute('x-transition:leave-start', 'opacity-100 transform scale-100');
        alertEl.setAttribute('x-transition:leave-end', 'opacity-0 transform scale-90');
        
        if (type === 'success') {
            alertEl.className = 'mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center justify-between';
            alertEl.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    ${message}
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            `;
        } else {
            alertEl.className = 'mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center justify-between';
            alertEl.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    ${message}
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            `;
        }
        
        // Insert at the beginning of the content area (same place as session alerts)
        alertContainer.insertBefore(alertEl, alertContainer.firstChild);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (alertEl.parentNode) {
                alertEl.remove();
            }
        }, 3000);
    }

    updateSidebar() {
        // Update sidebar favorite tags after reordering
        fetch('/api/favorite-tags')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const sidebarList = document.getElementById('sidebar-favorite-tags-list');
                    if (sidebarList) {
                        sidebarList.innerHTML = '';
                        
                        if (data.data.length > 0) {
                            data.data.forEach(favoriteTag => {
                                const button = document.createElement('button');
                                button.className = 'block w-full text-left hover:bg-gray-700 rounded px-2 py-1 text-sm text-blue-200 hover:text-blue-100 favorite-tag-btn';
                                button.setAttribute('data-tag', favoriteTag.tag.name);
                                button.setAttribute('title', `「${favoriteTag.tag.name}」で検索`);
                                button.textContent = favoriteTag.tag.name;
                                
                                // Re-add click handler for search
                                button.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    const tagName = button.dataset.tag;
                                    const currentUrl = new URL(window.location);
                                    currentUrl.searchParams.set('tag', tagName);
                                    currentUrl.searchParams.delete('title');
                                    window.location.href = currentUrl.toString();
                                });
                                
                                sidebarList.appendChild(button);
                            });
                        } else {
                            const emptyMessage = document.createElement('p');
                            emptyMessage.className = 'text-xs text-gray-400';
                            emptyMessage.textContent = 'お気に入りのタグがありません';
                            sidebarList.appendChild(emptyMessage);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error updating sidebar:', error);
            });
    }
}