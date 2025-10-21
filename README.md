# 近畿地方 交通情報表示システム

大阪を中心とした近畿地方の主要鉄道事業者の運行情報を表示するデモシステムです。

## ⚠️ 重要な注意事項

**このシステムはモックデータ（デモ用シミュレーションデータ）を使用しています。**

Yahoo!路線情報のスクレイピングは利用規約で禁止されているため、実際の運行情報は取得していません。
表示される遅延・運休情報はランダムに生成されたデモデータです。

### 実際の運行情報を取得する方法

実際の運用環境では、以下のいずれかの方法をご検討ください：

1. **各鉄道会社の公式API**
   - JR西日本、近鉄、阪急などは有料APIを提供している場合があります
   - 各社に直接お問い合わせください

2. **公共交通オープンデータ**
   - 国土交通省の公共交通オープンデータセンター
   - 静的な時刻表データが中心ですが、一部リアルタイムデータも提供

3. **手動入力システム**
   - 管理画面を構築し、手動で運行情報を入力

## 対象鉄道事業者

- JR西日本（8路線）
- 近畿日本鉄道（5路線）
- 阪急電鉄（5路線）
- 大阪メトロ（9路線）
- 南海電鉄（5路線）

## 機能

### Web画面
- ダッシュボード: 全事業者の運行状況を一覧表示
- 事業者詳細: 各事業者の路線別運行状況を表示
- リアルタイム更新: 遅延・運休情報をわかりやすく表示

### API
- `GET /api/train` - 全ての鉄道事業者と運行状況を取得
- `GET /api/train/delayed` - 遅延している路線のみを取得
- `GET /api/train/{slug}` - 特定の鉄道事業者の運行状況を取得
- `GET /api/train/line/{lineId}/history` - 特定路線の運行状況履歴を取得

## セットアップ

### 1. 依存パッケージのインストール

```bash
composer install
```

### 2. 環境設定

`.env`ファイルを編集してデータベース設定を確認:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

### 3. データベースのセットアップ

```bash
# マイグレーション実行
php artisan migrate

# 初期データの投入
php artisan db:seed --class=RailwayOperatorSeeder
```

### 4. モックデータの生成

```bash
# 全事業者の運行情報（モックデータ）を生成
php artisan train:scrape

# 特定の事業者のみ生成する場合
# php artisan train:scrape jr-west
```

**注意**: このコマンドはランダムなデモデータを生成します。実際のWeb APIからデータを取得するわけではありません。

### 5. 開発サーバーの起動

```bash
php artisan serve
```

ブラウザで `http://localhost:8000` にアクセスしてください。

## 定期実行の設定

モックデータを定期的に更新するには、Laravelのタスクスケジューラを使用します。

### タスクスケジューラの設定

`app/Console/Kernel.php` の `schedule` メソッドに以下を追加:

```php
protected function schedule(Schedule $schedule)
{
    // 5分ごとに運行情報を取得
    $schedule->command('train:scrape')
             ->everyFiveMinutes()
             ->withoutOverlapping();
}
```

### Cronの設定（本番環境）

サーバーのcrontabに以下を追加:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## API使用例

### 全ての運行状況を取得

```bash
curl http://localhost:8000/api/train
```

### 遅延している路線のみ取得

```bash
curl http://localhost:8000/api/train/delayed
```

### JR西日本の運行状況を取得

```bash
curl http://localhost:8000/api/train/jr-west
```

## 開発

### ディレクトリ構造

```
app/
├── Console/Commands/
│   └── ScrapeTrainInfo.php      # スクレイピングコマンド
├── Http/Controllers/
│   ├── Api/
│   │   └── TrainInfoController.php  # API コントローラー
│   └── TrainDashboardController.php # Web コントローラー
├── Models/
│   ├── RailwayOperator.php      # 鉄道事業者モデル
│   ├── TrainLine.php            # 路線モデル
│   └── OperationStatus.php      # 運行状況モデル
└── Services/
    └── TrainScraperService.php  # スクレイピングサービス
```

### スクレイピングについて

Yahoo!路線情報からHTMLをスクレイピングしています。

**注意事項:**
- Yahoo!のHTML構造が変更された場合、スクレイピングが動作しなくなる可能性があります
- サーバーに負荷をかけないよう、適切な間隔でスクレイピングを実行してください

## ライセンス

このプロジェクトはMITライセンスの下で公開されています。

## 免責事項

このシステムで表示される運行情報は、Yahoo!路線情報から取得したものです。
正確な情報については、各鉄道会社の公式サイトをご確認ください。


<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
