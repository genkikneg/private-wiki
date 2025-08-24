export class SidebarController {
  constructor() {
    this.sidebar = document.getElementById('sidebar');
    this.mainContent = document.getElementById('main-content');
    this.toggleButton = document.getElementById('sidebar-toggle');
    this.mainToggleButton = document.getElementById('main-sidebar-toggle');
    this.collapsed = false;
    this.storageKey = 'sidebar-collapsed';
    
    this.init();
  }

  init() {
    this.restoreState();
    this.bindEvents();
    this.updateAccessibility();
  }

  restoreState() {
    const savedState = localStorage.getItem(this.storageKey);
    if (savedState === 'true') {
      this.collapsed = true;
      this.applyCollapsedState();
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
  }

  expand() {
    this.collapsed = false;
    this.applyExpandedState();
    this.saveState();
    this.updateAccessibility();
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
    localStorage.setItem(this.storageKey, String(this.collapsed));
  }

  updateAccessibility() {
    if (this.toggleButton) {
      this.toggleButton.setAttribute('aria-expanded', String(!this.collapsed));
    }
    
    if (this.sidebar) {
      this.sidebar.setAttribute('aria-hidden', String(this.collapsed));
    }
  }
}