export class SidebarController {
  constructor() {
    this.sidebar = document.getElementById('sidebar');
    this.mainContent = document.getElementById('main-content');
    this.toggleButton = document.getElementById('sidebar-toggle');
    this.mainToggleButton = document.getElementById('main-sidebar-toggle');
    this.collapsed = false;
    this.storageKey = 'sidebar-collapsed';
    this.storageAvailable = typeof window !== 'undefined' && 'localStorage' in window;
    
    this.init();
  }

  init() {
    this.restoreState();
    this.bindEvents();
    this.updateAccessibility();
  }

  restoreState() {
    if (!this.storageAvailable) {
      return;
    }

    try {
      const savedState = localStorage.getItem(this.storageKey);
      if (savedState === 'true') {
        this.collapsed = true;
        this.applyCollapsedState();
      }
    } catch (error) {
      this.storageAvailable = false;
    }
  }

  bindEvents() {
    if (this.toggleButton) {
      this.toggleButton.addEventListener('click', () => this.toggle());
    }
    
    if (this.mainToggleButton) {
      this.mainToggleButton.addEventListener('click', () => this.toggle());
    }
  }

  isCollapsed() {
    return this.collapsed;
  }

  toggle() {
    if (this.collapsed) {
      this.expand();
    } else {
      this.collapse();
    }
  }

  collapse() {
    this.collapsed = true;
    this.applyCollapsedState();
    this.saveState();
    this.updateAccessibility();

    if (this.toggleButton) {
      this.toggleButton.blur();
    }

    if (this.mainToggleButton) {
      this.mainToggleButton.focus();
    }
  }

  expand() {
    this.collapsed = false;
    this.applyExpandedState();
    this.saveState();
    this.updateAccessibility();

    if (this.mainToggleButton) {
      this.mainToggleButton.blur();
    }

    if (this.toggleButton) {
      this.toggleButton.focus();
    }
  }

  applyCollapsedState() {
    if (this.sidebar) {
      this.sidebar.classList.add('collapsed');
      this.sidebar.style.transform = 'translateX(-100%)';
    }
    
    if (this.mainContent) {
      this.mainContent.classList.remove('ml-64');
      this.mainContent.classList.add('ml-0');
    }
    
    // メインエリアのトグルボタンを表示
    if (this.mainToggleButton) {
      this.mainToggleButton.classList.remove('hidden');
    }
  }

  applyExpandedState() {
    if (this.sidebar) {
      this.sidebar.classList.remove('collapsed');
      this.sidebar.style.transform = 'translateX(0)';
    }
    
    if (this.mainContent) {
      this.mainContent.classList.remove('ml-0');
      this.mainContent.classList.add('ml-64');
    }
    
    // メインエリアのトグルボタンを非表示
    if (this.mainToggleButton) {
      this.mainToggleButton.classList.add('hidden');
    }
  }

  saveState() {
    if (!this.storageAvailable) {
      return;
    }

    try {
      localStorage.setItem(this.storageKey, String(this.collapsed));
    } catch (error) {
      this.storageAvailable = false;
    }
  }

  updateAccessibility() {
    if (this.toggleButton) {
      this.toggleButton.setAttribute('aria-expanded', String(!this.collapsed));

      if (this.collapsed) {
        this.toggleButton.setAttribute('tabindex', '-1');
      } else {
        this.toggleButton.removeAttribute('tabindex');
      }
    }

    if (this.sidebar) {
      this.sidebar.setAttribute('aria-hidden', String(this.collapsed));

      if (this.collapsed) {
        this.sidebar.setAttribute('inert', '');
      } else {
        this.sidebar.removeAttribute('inert');
      }
    }

    if (this.mainToggleButton) {
      this.mainToggleButton.setAttribute('aria-expanded', String(!this.collapsed));

      if (this.collapsed) {
        this.mainToggleButton.removeAttribute('tabindex');
      } else {
        this.mainToggleButton.setAttribute('tabindex', '-1');
      }
    }
  }
}
