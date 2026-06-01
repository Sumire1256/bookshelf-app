# BookShelf 書籍レビューアプリ

## 概要
Bookshelfは書籍のレビューと管理ができるWebアプリケーションです。  

ユーザーは書籍を登録し、レビューや評価を投稿できます。  
また、気に入った書籍をお気に入りに登録したり、他のユーザーのレビューにいいねをすることができます。  
その他にも、ランキングや、マイ読書レポートなどの機能を提供します。

## 🪄使用技術一覧
### バックエンド
![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white)

### フロントエンド
![Vite](https://img.shields.io/badge/Vite-5.0-646CFF?style=for-the-badge&logo=vite&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)

### 開発ツール
![Laravel Sail](https://img.shields.io/badge/Laravel_Sail-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)

### データベース
![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

### インフラ・開発環境
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![phpMyAdmin](https://img.shields.io/badge/phpMyAdmin-6C78AF?style=for-the-badge&logo=phpmyadmin&logoColor=white)

## 🔍目次
1. [主な機能](#主な機能)
2. [ER図](#er図)
3. [環境構築手順](#️環境構築手順)
4. [APIエンドポイント一覧](#apiエンドポイント一覧)
5. [開発環境URL](#開発環境url)
6. [ログイン情報](#ログイン情報)
7. [テストの実行方法](#テストの実行方法)
8. [作成者](#作成者)

## 📌主な機能
- 書籍の登録・編集・削除
- レビューの投稿・編集・削除
- 書籍お気に入り機能
- いいね機能
- ジャンル管理
- 書籍ランキング表示
- マイ読書レポート
- 書籍検索・フィルタ・ソート
- ISBN検索（Google Books API連携）
- 公開API

## 🧩ER図
```mermaid
%%{init: {'theme': 'default'}}%%
erDiagram
	direction TB
	users {
		bigint id PK ""  
		varchar name  ""  
		varchar email UK ""  
		timestamp email_verified_at  "nullable"  
		varchar password  ""  
		varchar remember_token  ""  
		timestamp created_at  ""  
		timestamp updated_at  ""  
	}

	books {
		bigint id PK ""  
		bigint user_id FK ""  
		varchar title  ""  
		varchar author  ""  
		varchar isbn UK "nullable"  
		date published_date  "nullable"  
		text description  "nullable"  
		varchar image_url  "nullable"  
		timestamp created_at  ""  
		timestamp updated_at  ""  
	}

	favorites {
		bigint user_id PK,FK ""  
		bigint book_id PK,FK ""  
		timestamp created_at  ""  
		timestamp updated_at  ""  
	}

	genres {
		bigint id PK ""  
		varchar name  ""  
		timestamp created_at  ""  
		timestamp updated_at  ""  
	}

	book_genre {
		bigint book_id PK,FK ""  
		bigint genre_id PK,FK ""  
		timestamp created_at  ""  
		timestamp updated_at  ""  
	}

	reviews {
		bigint id PK ""  
		bigint user_id FK ""  
		bigint book_id FK ""  
		tinyint rating  "1～5"  
		text comment  ""  
		timestamp created_at  ""  
		timestamp updated_at  ""  
	}

	review_likes {
		bigint user_id PK,FK ""  
		bigint review_id PK,FK ""  
		timestamp created_at  ""  
		timestamp updated_at  ""  
	}

	users||--o{books:"has many"
	users||--o{favorites:"has many (中間)"
	users||--o{reviews:"has many"
	users||--o{review_likes:"has many (中間)"
	books||--o{favorites:"has many (中間)"
	books||--o{book_genre:"has many"
	books||--o{reviews:"has many"
	genres||--o{book_genre:"has many"
	reviews||--o{review_likes:"has many (中間)"
```

## ⛏️環境構築手順

1. リポジトリのクローン
以下のコマンドを実行してリポジトリをクローンし、プロジェクトのディレクトリに移動します。  
```
git clone https://github.com/Sumire1256/bookshelf-app.git
cd bookshelf-app
```
2. `.env.example` から `.env` を作成
```
cp .env.example .env
```
3. composerでパッケージをインストール
```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer install
```
4. Sailを起動
```
sail up -d
```

### Laravel環境構築
1. アプリケーションキーを生成
```
sail artisan key:generate
```
2. データベースのマイグレーションと初期データ投入
```
sail artisan migrate:fresh --seed
```
3. フロントエンドのパッケージをインストール・ビルド
```
sail npm install

# 開発時
sail npm run dev

# 本番・デモ確認時
sail npm run build
```

## 📱APIエンドポイント一覧
### 認証
| メソッド | URI | 説明 | 認証 |
|---------|-----|------|------|
![POST](https://img.shields.io/badge/POST-f97316?style=flat-square) | /api/v1/login | ログインしてトークンを取得する | 不要
![POST](https://img.shields.io/badge/POST-f97316?style=flat-square) | /api/v1/logout | ログアウトしてトークンを削除する | Sanctum

### 書籍
HTTPメソッド | URI | 説明 | 認証
--- | --- | ---| ---
![GET](https://img.shields.io/badge/GET-22c55e?style=flat-square) | /api/v1/books | 書籍一覧を取得する（検索・ページネーション付き） | 不要
![GET](https://img.shields.io/badge/GET-22c55e?style=flat-square) | /api/v1/books/{book} | 書籍詳細を取得する（ジャンル情報・レビュー含む） | 不要
![POST](https://img.shields.io/badge/POST-f97316?style=flat-square) | /api/v1/books | 書籍を新規登録する | Sanctum
![PUT](https://img.shields.io/badge/PUT-3b82f6?style=flat-square) | /api/v1/books/{book} | 書籍を更新する | Sanctum + BookPolicy(所有者のみ)
![DELETE](https://img.shields.io/badge/DELETE-ef4444?style=flat-square) | /api/v1/books/{book}| 書籍を削除する | Sanctum + BookPolicy(所有者のみ)

## 🌐開発環境URL
- アプリ: http://localhost
- phpMyAdmin: http://localhost:8080

## 🔓ログイン情報
| 名前 | メールアドレス | パスワード |
|------|-------------|-----------|
| 山田太郎 | yamada@example.com | password |
| 鈴木花子 | suzuki@example.com | password |
| 田中一郎 | tanaka@example.com | password |
| 佐藤美咲 | sato@example.com | password |
| 高橋健太 | takahashi@example.com | password |

## 💯テストの実行方法
- 全テスト実行
```
sail artisan test
```

- カバレッジ確認
```
sail artisan test --coverage
```

- コードフォーマット確認
```
sail bin pint --test
```

## 作成者
👩🏻‍💻 https://github.com/Sumire1256
