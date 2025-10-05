import { SidebarController } from '../../resources/js/sidebar.js';

describe('SidebarController', () => {
  let sidebarController;
  let mockSidebar;
  let mockMainContent;
  let mockToggleButton;
  let mockMainToggleButton;

  beforeEach(() => {
    // DOM要素のモックを作成
    document.body.innerHTML = `
      <aside id="sidebar" class="w-64 h-screen fixed top-0 left-0 bg-gray-800 text-white px-4 py-6">
        <h2 class="text-2xl font-bold mb-6">📚 Private Wiki</h2>
        <button id="sidebar-toggle" class="absolute top-4 right-4 text-white hover:text-gray-300">
          ☰
        </button>
        <nav class="space-y-2">
          <a href="/" class="block hover:bg-gray-700 rounded px-2 py-1">ホーム</a>
        </nav>
      </aside>
      <div id="main-content" class="ml-64 min-h-screen px-8 py-6">
        <button id="main-sidebar-toggle" class="fixed top-4 left-4 z-50 bg-gray-800 hover:bg-gray-700 text-white p-2 rounded shadow-lg hidden">
          ☰
        </button>
        <h1>メインコンテンツ</h1>
      </div>
    `;

    mockSidebar = document.getElementById('sidebar');
    mockMainContent = document.getElementById('main-content');
    mockToggleButton = document.getElementById('sidebar-toggle');
    mockMainToggleButton = document.getElementById('main-sidebar-toggle');
    
    sidebarController = new SidebarController();
  });

  afterEach(() => {
    window.localStorage.clear();
    jest.clearAllMocks();
  });

  const renderSidebarDom = () => {
    document.body.innerHTML = `
      <aside id="sidebar" class="w-64 h-screen fixed top-0 left-0 bg-gray-800 text-white px-4 py-6">
        <h2 class="text-2xl font-bold mb-6">📚 Private Wiki</h2>
        <button id="sidebar-toggle" class="absolute top-4 right-4 text-white hover:text-gray-300">
          ☰
        </button>
        <nav class="space-y-2">
          <a href="/" class="block hover:bg-gray-700 rounded px-2 py-1">ホーム</a>
        </nav>
      </aside>
      <div id="main-content" class="ml-64 min-h-screen px-8 py-6">
        <button id="main-sidebar-toggle" class="fixed top-4 left-4 z-50 bg-gray-800 hover:bg-gray-700 text-white p-2 rounded shadow-lg hidden">
          ☰
        </button>
        <h1>メインコンテンツ</h1>
      </div>
    `;

    mockSidebar = document.getElementById('sidebar');
    mockMainContent = document.getElementById('main-content');
    mockToggleButton = document.getElementById('sidebar-toggle');
    mockMainToggleButton = document.getElementById('main-sidebar-toggle');
  };

  describe('初期化', () => {
    test('SidebarControllerが正しくインスタンス化される', () => {
      expect(sidebarController).toBeInstanceOf(SidebarController);
    });

    test('初期状態ではサイドバーが展開されている', () => {
      expect(sidebarController.isCollapsed()).toBe(false);
      expect(mockSidebar.classList.contains('collapsed')).toBe(false);
    });

    test('サイドバーのトグルボタンがクリックイベントリスナーを持つ', () => {
      const spy = jest.spyOn(sidebarController, 'toggle');
      mockToggleButton.click();
      expect(spy).toHaveBeenCalled();
    });

    test('メインエリアのトグルボタンがクリックイベントリスナーを持つ', () => {
      const spy = jest.spyOn(sidebarController, 'toggle');
      mockMainToggleButton.click();
      expect(spy).toHaveBeenCalled();
    });
  });

  describe('ローカルストレージが利用できない環境での挙動', () => {
    test('localStorageアクセスが失敗してもハンバーガートグルが機能する', () => {
      window.localStorage.getItem.mockImplementation(() => {
        throw new Error('storage blocked');
      });
      window.localStorage.setItem.mockImplementation(() => {
        throw new Error('storage blocked');
      });

      renderSidebarDom();

      expect(() => {
        sidebarController = new SidebarController();
      }).not.toThrow();

      mockToggleButton.click();
      expect(mockSidebar.style.transform).toBe('translateX(-100%)');
      expect(mockToggleButton.getAttribute('aria-expanded')).toBe('false');

      window.localStorage.getItem.mockReset();
      window.localStorage.setItem.mockReset();
    });
  });

  describe('折り畳み/展開機能', () => {
    test('toggle()メソッドでサイドバーが折り畳まれる', () => {
      sidebarController.toggle();
      
      expect(sidebarController.isCollapsed()).toBe(true);
      expect(mockSidebar.classList.contains('collapsed')).toBe(true);
      expect(mockSidebar.style.transform).toBe('translateX(-100%)');
      expect(mockMainContent.classList.contains('ml-64')).toBe(false);
      expect(mockMainContent.classList.contains('ml-0')).toBe(true);
      expect(mockMainToggleButton.classList.contains('hidden')).toBe(false);
    });

    test('再度toggle()を呼ぶとサイドバーが展開される', () => {
      // 一度折り畳む
      sidebarController.toggle();
      expect(sidebarController.isCollapsed()).toBe(true);
      
      // 再度展開
      sidebarController.toggle();
      expect(sidebarController.isCollapsed()).toBe(false);
      expect(mockSidebar.classList.contains('collapsed')).toBe(false);
      expect(mockSidebar.style.transform).toBe('translateX(0)');
      expect(mockMainContent.classList.contains('ml-64')).toBe(true);
      expect(mockMainContent.classList.contains('ml-0')).toBe(false);
      expect(mockMainToggleButton.classList.contains('hidden')).toBe(true);
    });

    test('collapse()メソッドでサイドバーを明示的に折り畳める', () => {
      sidebarController.collapse();
      
      expect(sidebarController.isCollapsed()).toBe(true);
      expect(mockSidebar.classList.contains('collapsed')).toBe(true);
    });

    test('expand()メソッドでサイドバーを明示的に展開できる', () => {
      sidebarController.collapse();
      sidebarController.expand();
      
      expect(sidebarController.isCollapsed()).toBe(false);
      expect(mockSidebar.classList.contains('collapsed')).toBe(false);
    });
  });

  describe('状態の永続化', () => {
    test('折り畳み状態がlocalStorageに保存される', () => {
      sidebarController.toggle();
      
      expect(window.localStorage.setItem).toHaveBeenCalledWith('sidebar-collapsed', 'true');
    });

    test('展開状態がlocalStorageに保存される', () => {
      sidebarController.collapse();
      sidebarController.expand();
      
      expect(window.localStorage.setItem).toHaveBeenCalledWith('sidebar-collapsed', 'false');
    });

    test('localStorageから状態を復元できる', () => {
      window.localStorage.getItem.mockReturnValue('true');
      
      const newController = new SidebarController();
      expect(newController.isCollapsed()).toBe(true);
    });

    test('localStorageに値がない場合はデフォルト状態（展開）になる', () => {
      window.localStorage.getItem.mockReturnValue(null);
      
      const newController = new SidebarController();
      expect(newController.isCollapsed()).toBe(false);
    });
  });

  describe('アクセシビリティ', () => {
    test('トグルボタンにaria-expanded属性が設定される', () => {
      expect(mockToggleButton.getAttribute('aria-expanded')).toBe('true');

      sidebarController.toggle();
      expect(mockToggleButton.getAttribute('aria-expanded')).toBe('false');
    });

    test('サイドバーにaria-hidden属性が設定される', () => {
      sidebarController.toggle();
      expect(mockSidebar.getAttribute('aria-hidden')).toBe('true');

      sidebarController.expand();
      expect(mockSidebar.getAttribute('aria-hidden')).toBe('false');
    });

    test('折り畳み時にフォーカスがメイン側トグルに移動する', () => {
      mockToggleButton.focus();

      sidebarController.collapse();

      expect(document.activeElement).toBe(mockMainToggleButton);
    });

    test('折り畳み時はサイドバー側トグルがフォーカス不可能になる', () => {
      sidebarController.collapse();

      expect(mockToggleButton.getAttribute('tabindex')).toBe('-1');

      sidebarController.expand();
      expect(mockToggleButton.hasAttribute('tabindex')).toBe(false);
    });
  });
});
