# 近畿地方 交通情報表示システム

大阪を中心とした近畿地方の主要鉄道事業者の運行情報を表示するシステムです。

##  重要な注意事項

**このシステムはYahoo!路線情報からスクレイピングを行います。**

### スクレイピングについて

サーバーへの負荷を最小限にするため、以下の対策を実施しています：
- **30分に1回のみ実行**（キャッシュ機能を使用）
- 各リクエスト間に2秒の待機時間
- 適切なUser-Agentの設定

**注意**: 本番環境での使用は自己責任でお願いします。Yahoo!の利用規約に従ってください。

## 対象鉄道事業者

- JR西日本（29路線）
- 近畿日本鉄道（14路線）
- 阪急電鉄（9路線）
- 大阪メトロ（9路線）
- 南海電鉄（9路線）

合計: 約70路線の運行情報を表示

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

### 0. PHP設定の確認（初回のみ）

このプロジェクトを実行するには、以下のPHP拡張機能が有効になっている必要があります。

#### 必要な拡張機能

- **openssl** - SSL/TLS通信に必要（Composerやスクレイピングで使用）
- **fileinfo** - ファイルタイプの検出に必要
- **mbstring** - マルチバイト文字列処理に必要（Laravel必須）
- **pdo_sqlite** - SQLiteデータベースの使用に必要

#### php.iniの設定方法

1. **php.iniファイルの場所を確認:**
   ```bash
   php --ini
   ```

2. **php.iniファイルを編集:**
   
   以下の行を探して、行頭の `;` を削除して有効化します:
   
   ```ini
   ;extension_dir = "ext"    → extension_dir = "ext"
   ;extension=openssl        → extension=openssl
   ;extension=fileinfo       → extension=fileinfo
   ;extension=mbstring       → extension=mbstring
   ;extension=pdo_sqlite     → extension=pdo_sqlite
   ```

3. **設定を確認:**
   ```bash
   php -m | findstr "openssl fileinfo mbstring pdo_sqlite"
   ```
   
   上記のコマンドで4つの拡張機能が表示されればOKです。

#### Windows (Scoop)でPHPをインストールしている場合

通常、php.iniは以下のパスにあります:
```
C:\Users\<ユーザー名>\scoop\apps\php8.4\current\php.ini
```

PowerShellで一括設定する場合:
```powershell
$phpIni = "C:\Users\<ユーザー名>\scoop\apps\php8.4\current\php.ini"
$content = Get-Content $phpIni
$content = $content -replace '^;extension_dir = "ext"', 'extension_dir = "ext"'
$content = $content -replace '^;extension=openssl', 'extension=openssl'
$content = $content -replace '^;extension=fileinfo', 'extension=fileinfo'
$content = $content -replace '^;extension=mbstring', 'extension=mbstring'
$content = $content -replace '^;extension=pdo_sqlite', 'extension=pdo_sqlite'
$content | Set-Content $phpIni
```

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

### 4. 運行情報のスクレイピング

```bash
# 全事業者の運行情報をスクレイピング
php artisan train:scrape

# 特定の事業者のみスクレイピングする場合
# php artisan train:scrape jr-west
```

**注意**: このコマンドはYahoo!路線情報からスクレイピングを行います。30分間キャッシュされるため、頻繁な実行は行われません。

### 5.暗号化キーのセットアップ

```bash  
# APP_KEYに暗号化キーを自動で入れる。  
php artisan key:generate  
```

#### スクレイピングのログ確認

スクレイピングの実行状況は `storage/logs/laravel.log` に詳細に記録されます:

```bash
# ログの確認
Get-Content storage\logs\laravel.log -Tail 50
```

**ログに記録される情報:**
- スクレイピング開始・完了時刻
- キャッシュヒット/ミス（30分以内の再実行はキャッシュから取得）
- 実際にスクレイピングした事業者数
- 各事業者の路線数・遅延数・更新数
- キャッシュの有効期限（次回スクレイピング時刻）

**キャッシュの動作例:**
```
初回実行: cache_hits=0, cache_misses=5, actual_scrapes=5 (全事業者をスクレイピング)
2回目(30分以内): cache_hits=5, cache_misses=0, actual_scrapes=0 (全てキャッシュから取得)
30分後: cache_hits=0, cache_misses=5, actual_scrapes=5 (再度スクレイピング)
```


### 5. 開発サーバーの起動

```bash
php artisan serve
```

ブラウザで `http://localhost:8000` にアクセスしてください。

## 定期実行の設定

運行情報を定期的に更新するには、Laravelのタスクスケジューラを使用します。

### タスクスケジューラの設定

`app/Console/Kernel.php` の `schedule` メソッドに以下を追加:

```php
protected function schedule(Schedule $schedule)
{
    // 30分ごとに運行情報を取得（キャッシュが無効になるタイミング）
    $schedule->command('train:scrape')
        ->everyThirtyMinutes()
        ->withoutOverlapping();
}
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
