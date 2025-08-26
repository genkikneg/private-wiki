# Private Wiki

個人用のナレッジ管理システム。Markdownで記事を作成し、タグによる高度な検索機能を備えたプライベートWikiアプリケーションです。

## 🚀 特徴

- **Markdown対応**: 記事はMarkdown形式で作成・編集
- **高度なタグ検索**: AND/OR演算子を使った柔軟な検索システム
- **サイドバーお気に入り**: よく使うタグを固定表示
- **ダークモード**: 目に優しい暗いテーマ
- **日本語UI**: 完全日本語対応のインターフェース
- **レスポンシブデザイン**: モバイル・デスクトップ対応

## 🛠 技術スタック

- **Backend**: Laravel 12 (PHP ^8.2)
- **Database**: SQLite
- **Frontend**: Tailwind CSS v4 + Vite
- **Markdown**: League CommonMark
- **開発環境**: Laravel Pail, Concurrently

## 📦 インストール

### 前提条件
- PHP ^8.2
- Node.js
- Composer

### セットアップ

1. **リポジトリをクローン**
```bash
git clone <repository-url>
cd private-wiki-1/private-wiki
```

2. **依存関係をインストール**
```bash
composer install
npm install
```

3. **環境設定**
```bash
cp .env.example .env
php artisan key:generate
```

4. **データベース設定**
```bash
php artisan migrate --seed
```

5. **開発サーバー起動**
```bash
composer dev
```

アプリケーションが http://localhost:8000 で利用可能になります。

## 🔧 開発コマンド

### 基本コマンド
```bash
composer dev              # 全開発サービスを同時実行 (推奨)
php artisan serve         # Laravelサーバーのみ
npm run dev              # Vite開発サーバー (ホットリロード)
npm run build            # 本番用アセットビルド
```

### データベース
```bash
php artisan migrate                # マイグレーション実行
php artisan migrate:fresh --seed   # DB初期化+サンプルデータ
```

### テスト
```bash
composer test            # PHPUnitテスト実行 (推奨)
php artisan test         # 代替テストコマンド
```

### コード品質
```bash
./vendor/bin/pint        # Laravel Pint (PHP CS Fixer)
```

## 🔍 検索システム

高度なタグベース検索をサポート：

- **OR検索**: `tag1,tag2` (tag1またはtag2を含む記事)
- **AND検索**: `tag1::tag2` (tag1とtag2を両方含む記事)
- **複合検索**: `tag1::tag2,tag3` (tag1とtag2を含む、またはtag3を含む記事)

## 🏗 アーキテクチャ

### コアモデル
- `Note`: 記事モデル (`title`, `body`)
- `Tag`: タグモデル
- `note_tag`: 多対多リレーション管理

### 主要コントローラー
- `NoteController`: 記事のCRUD操作
- `TagController`: タグ候補のAPI提供

### フロントエンド構成
- `resources/views/layouts/app.blade.php`: メインレイアウト
- `resources/views/home.blade.php`: 記事一覧・検索画面
- `resources/views/notes/create.blade.php`: 記事作成フォーム

## 🧪 テスト

PHPUnitを使用したテスト環境を構築済み。インメモリSQLiteデータベースを使用し、開発データと分離されています。

## 📝 ライセンス

MIT License

## 🤝 コントリビューション

1. Fork this repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 サポート

問題や質問がある場合は、[GitHub Issues](https://github.com/genkikneg/private-wiki/issues)で報告してください。