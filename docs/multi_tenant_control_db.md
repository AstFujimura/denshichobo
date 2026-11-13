# マルチテナント化メモ：制御DBとマイグレーションオーケストレーション

作成日: 2026-11-12  
対象: Binder（TAMERU / 名刺 / Rapid / Skett 等）のサーバー集約・テナント化  

**他開発者向け説明資料（図解HTML・A4印刷向け）**: [`multi_tenant_architecture.html`](./multi_tenant_architecture.html)（ブラウザで開く）

---

## 1. 方針（決定事項）

現状は **顧客ごと別EC2・別DB・`config/prefix.php`・`.env`** で分離している。  
これを **1サーバー集約**し、スーパーユーザーがテナント単位で環境を制御する。

| 項目 | 決定 |
|------|------|
| 業務データの分離 | **共有テーブル＋`tenant_id` は採用しない**（危険・改修コスト大） |
| DB単位 | **テナントごとにDB名を変えて作成**（同一MySQLインスタンス上で可） |
| スキーマ変更 | **マイグレーションのオーケストレーション**で全テナントへ適用 |
| メタ情報 | **制御DB（1つ）** に集約 |

### 構成イメージ

```text
[制御DB] 例: binder_control
  tenants / tenant_features / tenant_settings
  tenant_ai_settings / tenant_migration_runs
  platform_admins

[テナントDB] 例: binder_astec, binder_xxx
  users, files, cards, groups ...（現行業務スキーマ）
  ※ versions 相当の機能フラグは持たない（制御DBへ移管）
```

リクエスト流れ:

1. URLプレフィックスでテナント特定（サブドメイン方式は採用しない）
2. 制御DBから設定を読む
3. 当該リクエストだけテナントDBへ接続切替
4. 機能・AI・ストレージ設定は制御DB（キャッシュ可）

---

## 2. 制御DB名

| 項目 | 案 |
|------|-----|
| DB名 | `binder_control` |
| 接続名（Laravel） | `control`（`config/database.php` に追加） |
| 環境変数例 | `CONTROL_DB_DATABASE=binder_control` など |

業務用の `DB_DATABASE` は「デフォルト／開発用の1テナント」または動的切替のベースとし、本番の顧客データは制御DBの `tenants.db_database` を正とする。

---

## 3. テーブル定義

### 3.1 `tenants`（テナント台帳）

顧客（テナント）の正本。プレフィックス・DB名・状態・ストレージ方針を持つ。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| `id` | BIGINT UNSIGNED PK | NO | 内部ID |
| `code` | VARCHAR(64) UNIQUE | NO | システム用コード（英小文字・数字・`_` 推奨）。DB名生成にも利用可 |
| `name` | VARCHAR(255) | NO | 表示名（会社名など） |
| `prefix` | VARCHAR(64) UNIQUE | NO | URLプレフィックス（現行 `config('prefix.prefix')` 相当）。空文字は不可推奨 |
| `db_database` | VARCHAR(128) UNIQUE | NO | テナントDB名（例: `binder_astec`） |
| `status` | VARCHAR(32) | NO | `provisioning` / `active` / `suspended` / `archived` |
| `storage_mode` | VARCHAR(16) | NO | `cloud` / `onpre`（現行 `prefix.server` 相当をテナント単位へ） |
| `storage_prefix` | VARCHAR(255) | NO | S3キーやローカル相対パスの先頭（通常は `prefix` と同値で開始可） |
| `file_upload_path` | VARCHAR(1024) | YES | onpre 時の保存ルート（現行 `custom.file_upload_path` 相当）。cloud なら NULL |
| `notes` | TEXT | YES | 運用メモ |
| `provisioned_at` | TIMESTAMP | YES | DB作成・初期migrate完了日時 |
| `suspended_at` | TIMESTAMP | YES | 停止日時 |
| `created_at` / `updated_at` | TIMESTAMP | YES | Laravel timestamps |

**インデックス**

- UNIQUE(`code`), UNIQUE(`prefix`), UNIQUE(`db_database`)
- INDEX(`status`)

**status の意味**

| 値 | 意味 |
|----|------|
| `provisioning` | DB作成・migrate・シード中。一般ログイン不可 |
| `active` | 利用中 |
| `suspended` | 一時停止（ログイン拒否、データは保持） |
| `archived` | 解約後アーカイブ（接続しない。dump済み想定） |

**現行からの移行対応**

- `config/prefix.php` の `prefix` → `tenants.prefix`
- `config/prefix.php` の `server` → `tenants.storage_mode`
- `config/custom.php` の `file_upload_path` → `tenants.file_upload_path`

---

### 3.2 `tenant_features`（機能ON/OFF）

現行 `versions` テーブル（1行フラグ）の置き換え。  
**カラム追加で機能を増やす方式はやめ**、行追加で拡張する。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| `id` | BIGINT UNSIGNED PK | NO | |
| `tenant_id` | BIGINT UNSIGNED | NO | `tenants.id` FK |
| `feature_key` | VARCHAR(64) | NO | 機能キー（下表） |
| `enabled` | BOOLEAN | NO | 既定 false（アプリ側でキーごとのデフォルトも可） |
| `created_at` / `updated_at` | TIMESTAMP | YES | |

**制約**

- UNIQUE(`tenant_id`, `feature_key`)
- FK `tenant_id` → `tenants.id` ON DELETE CASCADE

**feature_key 初期セット（現行 `versions` 対応）**

| feature_key | 現行カラム | 意味 |
|-------------|------------|------|
| `tameru` | `tameru` | TAMERU（電子帳簿） |
| `card` | `名刺` | ReadBridge（名刺） |
| `flow` | `フロー` | Rapid（電子申請） |
| `schedule` | `スケジュール` | Skett |
| `transcribe` | `文書` | 文書抽出 |
| `banban` | `BANBAN` | 一括取込 |
| `ichifuji` | `ichifuji` | TAMERU AI |

新規テナント作成時は、契約プランに応じて上記キーを一括 INSERT する。  
未登録キーは「OFF」として扱う（またはマスタ既定値）。

**アプリ側の注意**

- 現行の「`versions` 更新後に `route:cache`」はマルチテナントでは使わない  
- ルートは常時登録し、**ミドルウェア／ゲートで `tenant_features` を参照**する

---

### 3.3 `tenant_ai_settings`（AIエンジン設定）

テナント単位で変更できるのは **TAMERU AIの利用先** と **Geminiモデル** のみ。  
APIキー・接続URL・パス・トークン・タイムアウト・トレース設定等は全テナント共通とし、`.env` で管理する。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| `id` | BIGINT UNSIGNED PK | NO | |
| `tenant_id` | BIGINT UNSIGNED UNIQUE | NO | 1テナント1行（必要なら用途別に分割可） |
| `ledger_ocr_provider` | VARCHAR(32) | NO | `gemini` / `internal`（画面表示は「Gemini」/「社内AI」） |
| `gemini_model` | VARCHAR(128) | YES | 例: `gemini-2.0-flash`。NULL ならアプリ既定 |
| `created_at` / `updated_at` | TIMESTAMP | YES | |

**制約**

- UNIQUE(`tenant_id`)
- FK `tenant_id` → `tenants.id` ON DELETE CASCADE

**方針**

- 管理画面には `ledger_ocr_provider` と `gemini_model` だけを表示する
- `GEMINI_API_KEY` / `GEMINI_API_BASE` / `INTERNAL_AI_OCR_*` / `TAMERU_AI_OCR_TRACE` は `.env` の共通値を使う
- 旧カラムは互換性のため当面スキーマに残すが、アプリからは読み書きせず、移行マイグレーションで値を消去する

---

### 3.4 `tenant_settings`（その他キーバリュー）

頻繁にカラム化したくない設定用の逃げ道。  
機能フラグ・AI・接続情報は専用テーブルを優先し、ここは「たまに変わる運用設定」向け。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| `id` | BIGINT UNSIGNED PK | NO | |
| `tenant_id` | BIGINT UNSIGNED | NO | |
| `key` | VARCHAR(128) | NO | 例: `demo_mode`, `login_redirect` |
| `value` | JSON | YES | 任意JSON |
| `created_at` / `updated_at` | TIMESTAMP | YES | |

**制約**

- UNIQUE(`tenant_id`, `key`)
- FK `tenant_id` → `tenants.id` ON DELETE CASCADE

---

### 3.5 `tenant_migration_runs`（マイグレーションオーケストレーション履歴）

全テナントへの migrate 実行状況を追う。  
「新規開発時はオーケストレーションで全DBへ反映」する運用の正本。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| `id` | BIGINT UNSIGNED PK | NO | |
| `tenant_id` | BIGINT UNSIGNED | NO | |
| `batch_id` | VARCHAR(64) | NO | 1回の一括デプロイ／一括migrateを識別（UUID等） |
| `target_migration` | VARCHAR(255) | YES | 狙った最新マイグレーション名（任意） |
| `status` | VARCHAR(32) | NO | `pending` / `running` / `succeeded` / `failed` / `skipped` |
| `from_migration` | VARCHAR(255) | YES | 実行前の最終適用名（分かれば） |
| `to_migration` | VARCHAR(255) | YES | 実行後の最終適用名 |
| `attempts` | INT UNSIGNED | NO | 試行回数。既定 0 |
| `started_at` | TIMESTAMP | YES | |
| `finished_at` | TIMESTAMP | YES | |
| `error_message` | TEXT | YES | 失敗時メッセージ |
| `created_at` / `updated_at` | TIMESTAMP | YES | |

**制約・インデックス**

- FK `tenant_id` → `tenants.id` ON DELETE CASCADE  
- INDEX(`batch_id`)  
- INDEX(`tenant_id`, `status`)  
- INDEX(`status`)

**運用イメージ**

1. コードをデプロイ（マイグレーションファイル追加）
2. `tenants:migrate`（仮）が `active`（必要なら `provisioning`）テナントを列挙
3. 各テナントDBに接続して `migrate`
4. 成否を本テーブルに記録
5. 失敗テナントだけ再実行・停止判断

テナントDB側の `migrations` テーブルは従来どおり各DBに残す（Laravel標準）。  
制御DB側は **横断監視・再実行・進捗** 用。

---

### 3.6 `platform_admins`（スーパーユーザー）

テナント内の `users`（`Auth::id() === 1` や `管理`）とは分離する。  
制御平面専用の管理者。詳細な入場フローは §13。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| `id` | BIGINT UNSIGNED PK | NO | |
| `name` | VARCHAR(255) | NO | ログイン名 |
| `display_name` | VARCHAR(255) | YES | 表示名 |
| `email` | VARCHAR(255) | YES | |
| `password` | VARCHAR(255) | NO | ハッシュ |
| `is_active` | BOOLEAN | NO | 既定 true |
| `can_provision` | BOOLEAN | NO | テナント作成・取込。既定 false でも可（運用で付与） |
| `can_impersonate` | BOOLEAN | NO | 顧客ユーザーへの代理ログイン（L2）。既定 false でも可 |
| `last_login_at` | TIMESTAMP | YES | |
| `created_at` / `updated_at` | TIMESTAMP | YES | |

**制約**

- UNIQUE(`name`)

**権限の考え方（初期）**

- L0: テナント作成／停止／機能変更／AI設定／migrate状況（`can_provision` 等で細分化可）  
- L1/L2: テナント画面へのサポート入場・ユーザー代理（§13）。業務は通常ログインとは別セッション  
- ロール表が必要になるまで、上記 bool で足りる想定

---

## 4. テナントDB側に残すもの／移すもの

| 対象 | 扱い |
|------|------|
| `users` / `groups` / `files` / `cards` 等 | **テナントDBに残す**（現行どおり） |
| `versions` | **新規テナントでは作らない／使わない**。制御DB `tenant_features` へ |
| `documents.ocr_settings` | テナントDBのまま（書類マスタ固有） |
| 表示用アプリバージョン `APP_DISPLAY_VERSION` | コード／env（全テナント共通）。制御DB不要 |
| `config/prefix.php` | 廃止方向 → `tenants` |
| AI関連 `.env` | テナント固有は `tenant_ai_settings`。共通フォールバックのみ env |

既存顧客DBを取り込む過渡期は、テナントDBに古い `versions` が残っていてもよい。  
アプリは制御DBを優先し、移行完了後に `versions` 参照を削除する。

---

## 5. 新規テナント作成時の標準手順（DB観点）

1. `tenants` に行追加（`status = provisioning`）
2. `CREATE DATABASE {db_database}`
3. 最新まで migrate（オーケストレーション経由）
4. 初期シード（指定なしグループ、初期管理者ユーザー等）
5. `tenant_features` を契約内容で INSERT
6. `tenant_ai_settings` を INSERT（未設定なら provider 既定のみ）
7. `tenants.status = active`, `provisioned_at` 設定

解約時:

1. `status = suspended` → 必要なら `archived`
2. テナントDB dump ＋ストレージアーカイブ
3. DB drop は運用判断（即時 or 保管後）

---

## 6. ER概要

```text
platform_admins
    （独立）

tenants 1───* tenant_features
        1───1 tenant_ai_settings
        1───* tenant_settings
        1───* tenant_migration_runs
```

---

## 7. マイグレーションオーケストレーション仕様

新規開発でテーブル定義が変わったとき、**1回のコマンドで全テナントDBへ migrate を適用**する。  
適用状況は制御DBの `tenant_migration_runs` に残す。

### 7.1 前提（接続モデル）

| 接続名 | 用途 |
|--------|------|
| `control` | 制御DB（`binder_control`）。常時固定 |
| `tenant` | 動的。実行中テナントの `db_database` を差し替えて使う |
| `mysql`（default） | 開発用フォールバック。オーケストレーション本体では使わない |

テナント接続の切替（擬似コード）:

```text
1. control から Tenant を取得
2. config(['database.connections.tenant.database' => $tenant->db_database])
3. DB::purge('tenant'); DB::reconnect('tenant');
4. その接続で Artisan::call('migrate', ['--database' => 'tenant', ...])
```

- ホスト／ユーザー／パスワードは当面 **制御DBと同じMySQL** を共有してよい  
- 将来テナントごとにホストを分ける場合は `tenants` に `db_host` 等を追加する（今は不要）

制御DB自身のスキーマ変更は通常の:

```bash
php artisan migrate --database=control --path=database/migrations/control
```

テナント用マイグレーションは現行どおり `database/migrations`（または将来 `database/migrations/tenant` に分離）。

**推奨ディレクトリ分け（実装時）**

| パス | 対象 |
|------|------|
| `database/migrations/control/` | 制御DB専用（tenants 等） |
| `database/migrations/tenant/` | 各テナントDB用（現行業務テーブル） |

過渡期は既存ファイルを `tenant` 配下へ移すか、`--path` で明示する。

### 7.2 コマンド一覧

| コマンド | 役割 |
|----------|------|
| `tenants:migrate` | 対象テナントへ未適用マイグレーションを実行 |
| `tenants:migrate-status` | 各テナントの適用状況を一覧 |
| `tenants:provision` | 新規テナントのDB作成〜初期設定まで |
| `tenants:seed` | 既存テナントへ初期／追加シード（必要時） |

いずれも `app/Console/Commands` 配下に実装する想定。

---

### 7.3 `tenants:migrate`

**署名（案）**

```text
tenants:migrate
  {--tenant= : テナント code または prefix（省略時は対象ステータス全件）}
  {--status=active,provisioning : 対象 status（カンマ区切り）}
  {--batch= : バッチID。省略時は UUID を新規発行}
  {--path=database/migrations/tenant : マイグレーションパス}
  {--force : 本番相当でも確認なし実行}
  {--dry-run : 実行せず対象テナントと未適用有無だけ表示}
  {--stop-on-error : 1件失敗したら以降を中断（既定は続行）}
```

**処理フロー**

```text
1. batch_id を決定（指定 or 新規UUID）
2. 対象テナントを control から取得
   - --tenant あり → その1件
   - なし → status が --status に含まれるもの（archived は既定除外）
3. 各テナントについて:
   a. tenant_migration_runs に status=running で1行 INSERT
   b. 接続を tenant DB に切替
   c. 実行前の最終 migration 名を取得（from_migration）
   d. php artisan migrate --database=tenant --path=... --force
   e. 成功 → status=succeeded, to_migration 更新, finished_at
      失敗 → status=failed, error_message, attempts++, finished_at
   f. 接続をクリア（次テナントへ汚染しない）
4. サマリ出力（成功数 / 失敗数 / batch_id）
5. 失敗が1件でもあれば終了コード 1
```

**対象ステータス**

| status | migrate 対象（既定） |
|--------|----------------------|
| `active` | ○ |
| `provisioning` | ○（作成直後の初回適用） |
| `suspended` | ×（データは残すがスキーマ更新は運用判断。`--status=` で明示すれば可） |
| `archived` | × |

**失敗時ポリシー**

- 既定: **他テナントは続行**（1社の失敗で全体を止めない）  
- `--stop-on-error`: デプロイパイプラインで「全部成功が必須」のときに使う  
- 失敗テナントは `tenant_migration_runs.status = failed` のまま残る  
- 再実行は同じコマンドで `--tenant=xxx`（または失敗のみ抽出する運用）

**再実行**

```bash
# 失敗したテナントだけ（運用者が code を指定）
php artisan tenants:migrate --tenant=astec --force

# またはバッチ内の failed を再実行する将来オプション
# php artisan tenants:migrate --retry-batch={batch_id}
```

初期実装は `--tenant` 手動指定で足りる。`--retry-batch` は余力で追加。

**dry-run**

- テナント一覧と、可能なら各DBの「未適用 migration があるか」を表示  
- `tenant_migration_runs` には書かない

**ログ**

- コンソールにテナント code / DB名 / 成否を1行ずつ  
- Laravel log にも `tenant_code`, `batch_id` を付けて残す

---

### 7.4 `tenants:migrate-status`

**署名（案）**

```text
tenants:migrate-status
  {--tenant= : 絞り込み}
  {--status=active,provisioning,suspended : 対象}
```

**出力例（イメージ）**

```text
code       db_database     tenant_status  last_migration              last_run      run_status
astec      binder_astec    active         2026_07_28_000001_...       2 min ago     succeeded
foo        binder_foo      active         2026_05_01_120001_...       2 min ago     failed
```

- `last_migration` はテナントDBの `migrations` テーブルから取得  
- `last_run` / `run_status` は制御DBの最新 `tenant_migration_runs`  
- アプリが期待する最新 migration 名と差分があるテナントを強調表示できるとよい

---

### 7.5 `tenants:provision`

新規テナントを制御DB登録から利用開始まで通す。

**署名（案）**

```text
tenants:provision
  {code : テナントコード}
  {--name= : 表示名（省略時は code）}
  {--prefix= : URLプレフィックス（省略時は code）}
  {--db= : DB名（省略時は binder_{code}）}
  {--storage=cloud : cloud|onpre}
  {--features=tameru,card : 有効化する feature_key（カンマ区切り）}
  {--admin-name= : 初期テナント管理者ログイン名}
  {--admin-password= : 初期パスワード（未指定なら交互入力 or ランダム生成して表示）}
  {--force : 確認スキップ}
```

**処理フロー**

```text
1. code / prefix / db_database の重複チェック（control）
2. tenants に INSERT（status=provisioning）
3. CREATE DATABASE IF NOT EXISTS {db_database}
   ※ 接続ユーザーに CREATE 権限が必要。無い場合は手順書で手動作成 → 本コマンドは migrate 以降のみ
4. tenants:migrate --tenant={code} 相当を実行（同一 batch でも可）
5. tenants:seed（初期データ）
6. tenant_features を --features に基づき INSERT（指定外は enabled=false で全キー作成でも可）
7. tenant_ai_settings をデフォルト1行 INSERT
8. status=active, provisioned_at=now()
9. 失敗時は status を provisioning のまま残し、エラーを表示
   （自動 DROP DATABASE はしない。誤削除防止）
```

**初期シードで入れるもの（現行運用に合わせる）**

| 内容 | 備考 |
|------|------|
| `groups` 「指定なし」（id=100000） | 現行ロジック依存 |
| 初期管理者 `users` | `--admin-name` / password |
| 固有グループ＋`group_user` | ユーザー登録時と同じ規則 |
| 必要なら documents 等マスタ | 既存シード／SQLがあれば流用 |

シード内容は別途 `TenantDatabaseSeeder` に固定し、provision と `tenants:seed` の両方から呼ぶ。

---

### 7.6 `tenants:seed`

```text
tenants:seed
  {--tenant= : 必須（誤爆防止）}
  {--class=Database\\Seeders\\TenantDatabaseSeeder}
  {--force}
```

- provision 後のやり直しや、マスタ追加投入用  
- `--tenant` 必須（全テナント一括シードは初期はサポートしない）

---

### 7.7 デプロイ時の標準手順

```text
1. アプリコードをデプロイ（migration ファイルを含む）
2. 制御DBを更新
   php artisan migrate --database=control --path=database/migrations/control --force
3. 全テナントへ適用
   php artisan tenants:migrate --force
4. tenants:migrate-status で失敗ゼロを確認
5. 失敗があれば
   php artisan tenants:migrate --tenant={code} --force
6. （任意）ルート／設定キャッシュ等はテナント非依存のものだけ
```

**やってはいけないこと**

- デプロイ機で素の `php artisan migrate`（default接続の1DBだけ更新して終わる）を本番の正にしない  
- 旧 `VersionController` のように機能変更のたびに `route:cache` でルートをテナント依存にしない

CI/CD がある場合、ステップ3の終了コード非0でパイプラインを落とす。

---

### 7.8 実装時の内部サービス案

コマンドから直接 DB をいじりすぎないよう、薄いサービスに寄せる。

| クラス（案） | 責任 |
|--------------|------|
| `App\Support\Tenancy\TenantContext` | 現在テナントの保持 |
| `App\Support\Tenancy\TenantDatabaseManager` | 接続の purge / reconnect / CREATE DATABASE |
| `App\Support\Tenancy\TenantMigrator` | 1テナント migrate＋`tenant_migration_runs` 更新 |
| `App\Support\Tenancy\TenantProvisioner` | provision 全体のオーケストレーション |

HTTPリクエスト側の「プレフィックスでテナント解決→接続切替」も、後で同じ `TenantDatabaseManager` を再利用する。

---

### 7.9 権限・安全策

| 項目 | 方針 |
|------|------|
| CREATE DATABASE | デプロイ用DBユーザーに限定。アプリ実行ユーザーは権限を絞ってもよい |
| `--force` | 本番（`APP_ENV=production`）では Laravel 標準と同様に必要 |
| archived | 既定で migrate 対象外 |
| 自動DROP | provision 失敗時も DB は落とさない |
| 同時実行 | 同じ batch の多重起動は避ける。簡易に `cache` ロック or `batch_id` 運用ルールで十分（初期） |

---

## 8. プレフィックス解決とDB接続切替

現行は `routes/web.php` 起動時に `config('prefix.prefix')` を読み、`Route::prefix($prefix)` で固定している。  
マルチテナントでは **リクエストごとにプレフィックスからテナントを特定し、DB接続と設定を切り替える**。

### 8.1 現行の問題点（なぜ変えるか）

| 現行 | 問題 |
|------|------|
| `config/prefix.php` がデプロイ単位で1件 | 1プロセスで複数顧客を扱えない |
| `Route::prefix(config('prefix.prefix'))` | ルートキャッシュすると特定プレフィックスに固定される |
| `Version::where(...)->first()` でルート登録自体を分岐 | ブート時にDBを見ており、テナントごとにルート集合を変えられない／`route:cache` と相性が悪い |
| Blade / Controller が `config('prefix.*')` を直接参照 | リクエスト文脈が無い |

### 8.2 URL設計

当面は現行踏襲の **パスプレフィックス方式**。

```text
https://{host}/{prefix}/login
https://{host}/{prefix}/card
https://{host}/{prefix}/css/style.css
```

| パス | 扱い |
|------|------|
| `/` | テナント未特定。案内ページ or 404（本番は顧客URLを直接配布する想定） |
| `/{prefix}/...` | テナント解決対象 |
| `/platform/...`（将来） | スーパーユーザー画面。テナントDBには接続しない |

`prefix` は制御DB `tenants.prefix` と一致。空文字プレフィックスはサポートしない（衝突・誤解決を避ける）。

テナント解決は **URLプレフィックス方式で固定**（サブドメインは採用しない）。

### 8.3 ルート登録の変更方針

```text
# 現行（起動時固定）
$prefix = config('prefix.prefix');
Route::prefix($prefix)->group(...);
if (Version::where('名刺', true)->first()) { Route::... }

# 変更後
Route::prefix('{tenant_prefix}')
    ->where(['tenant_prefix' => '[A-Za-z0-9_\-]+'])
    ->middleware(['tenant.resolve', /* 必要なら tenant.feature */])
    ->group(function () {
        // 全プロダクトのルートを常時登録
        // 機能OFFはミドルウェア／Gateで拒否
    });
```

- **ルートは常時登録**（名刺・フロー・スケジュール・文書・BANBAN・ichifuji 含む）  
- `route:cache` はテナント非依存のまま利用可能になる  
- 機能のON/OFFは `tenant_features` を見るミドルウェアへ移す（§8.6）

ルート名（`topGet` 等）は維持してよいが、URL生成時は必ず現在テナントの prefix を渡す／`TenantContext` から取る。

### 8.4 ミドルウェア構成

| エイリアス | クラス（案） | 役割 |
|------------|--------------|------|
| `tenant.resolve` | `InitializeTenancyByPrefix` | prefix → テナント特定 → DB切替 → 設定反映 |
| `tenant.active` | `EnsureTenantIsActive` | `active` 以外は拒否（resolve 内でも可） |
| `tenant.feature` | `EnsureTenantFeature` | 機能キー必須のルートに付与 |
| `platform.auth` | （将来） | 制御平面のスーパーユーザー認証 |

**推奨登録場所**

- `tenant.resolve` は `{tenant_prefix}` グループに必須  
- `web` グローバル全体には付けない（`/` や将来の `/platform` を壊さない）

**実行順（1リクエスト）**

```text
1. ルーティングが {tenant_prefix} をマッチ
2. tenant.resolve
   - control DB で prefix 検索（キャッシュ可）
   - status 判定
   - TenantContext にセット
   - tenant 接続へ切替（default も寄せる）
   - config 互換レイヤを上書き（後述）
   - AI設定を反映（任意）
3. StartSession / auth など既存 web ミドルウェア
4. コントローラ
5. 終了時に接続・Context をクリア（汚染防止。キュー併用時は特に重要）
```

Session 開始より前にテナントを確定したい場合、`tenant.resolve` をグループ先頭に置き、セッションキーをテナント単位で分離する（§8.8）。

### 8.5 `InitializeTenancyByPrefix` 詳細

**入力**

- ルートパラメータ `tenant_prefix`  
（フォールバックとして `request()->segment(1)` でも可だが、ルートパラメータを正とする）

**処理**

```text
1. prefix = route('tenant_prefix')
2. tenant = TenantRepository::findActiveByPrefix(prefix)
   - 見つからない → 404
   - status = provisioning → 503（準備中）
   - status = suspended → 403（停止中）
   - status = archived → 404
3. TenantContext::set(tenant)
4. TenantDatabaseManager::connect(tenant)
   - config database.connections.tenant.database = tenant.db_database
   - purge / reconnect
   - 業務コード互換のため DB::setDefaultConnection('tenant') も推奨
5. 互換 config をリクエスト単位で上書き
   - config(['prefix.prefix' => tenant.prefix])
   - config(['prefix.server' => tenant.storage_mode])
   - config(['custom.file_upload_path' => tenant.file_upload_path])
6. （任意）tenant_ai_settings を読み ai_ocr / gemini 相当 config を上書き
7. View::share / Blade 用に prefix, server を共有してもよい
8. $next($request)
9. finally: TenantContext::clear() と接続リセット（実装方針次第）
```

**キャッシュ**

- `tenants` + `tenant_features` + `tenant_ai_settings` を `tenant:prefix:{prefix}` でキャッシュ（TTL 60〜300秒）  
- スーパーユーザーが設定変更したら該当キーを消す  
- キャッシュに入れるのは制御DBのメタのみ。業務データはキャッシュしない

**制御DBへの接続**

- `tenant.resolve` 内のテナント検索だけ `connection = control`  
- 切替後の Eloquent は default/`tenant` を見る  
- `Tenant` モデルは `$connection = 'control'` を明示

### 8.6 機能ガード（`versions` 分岐の置き換え）

現行 `routes/web.php` の条件付き登録をやめる。

| 現行 | 変更後 |
|------|--------|
| `if (Version::where('名刺', true)) { routes }` | ルート常時登録 + `->middleware('tenant.feature:card')` |
| `if (... BANBAN)` | `tenant.feature:banban` |
| `if (... ichifuji)` | `tenant.feature:ichifuji` |
| `if (... フロー)` | `tenant.feature:flow` |
| `if (... スケジュール)` | `tenant.feature:schedule` |
| `if (... 文書)` | `tenant.feature:transcribe` |
| TAMERU本体ルート | `tenant.feature:tameru`（またはトップ導線だけガード） |

`**EnsureTenantFeature` 挙動**

- `TenantContext` の features を参照（resolve 時にまとめて読んでおく）  
- OFF なら 404（存在秘匿）または 403。初期は **404 推奨**  
- UI（`app_launcher` 等）も同じ features を見てボタン非表示

**Helper（案）**

```text
feature_enabled('card')  // bool
tenant()                 // 現在の Tenant DTO / Model
tenant_prefix()          // 文字列
```

管理画面の「機能設定」（現行 `VersionController`）は、将来 **platform 管理 or テナント管理者UIが control の `tenant_features` を更新**する形へ移す。`route:cache` 呼び出しは削除。

### 8.7 `config('prefix.*')` 互換レイヤ

一気に全 Controller / Blade の参照を書き換えないための暫定策。

| キー | 供給元 |
|------|--------|
| `prefix.prefix` | `tenants.prefix`（resolve 時に config 上書き） |
| `prefix.server` | `tenants.storage_mode` |
| `custom.file_upload_path` | `tenants.file_upload_path` |

これにより既存の `config('prefix.prefix')` / `@prefix` / `@server` は、**リクエスト中は正しいテナント値**を返す。  
中長期では `tenant()` ヘルパへ寄せ、互換レイヤを縮小する。

注意: `config()` の上書きはプロセス内グローバルなので、**Octane / 常駐ワーカーを使う場合はリクエスト終了時のクリアが必須**。通常の PHP-FPM ならリクエスト単位破棄で実害は少ないが、明示クリアを推奨。

### 8.8 セッション・認証の境界

| 項目 | 方針 |
|------|------|
| セッションCookie | Cookieは共有するが、ログイン時に `auth_tenant_id` をセッションへ必須保存 |
| ログイン済みユーザー | Laravelの認証セッションはユーザーIDだけではテナントを識別できないため、`auth_tenant_id` が無い既存セッションも許可しない |
| なりすまし防止 | `EnsureTenantSessionIsolation` が現在の tenant と `auth_tenant_id` を毎リクエスト照合。不一致時はユーザーを別DBから復元せず、web認証・remember Cookie・サポート状態を破棄して再ログイン |
| Auth::id()===1 | テナント内特権判定としては廃止方向。テナント管理者は `users.管理`、横断管理は `platform_admins` |

### 8.9 静的アセット

現行どおり `/{prefix}/css/...` 等は同一グループ内の `AssetController` で配信。  
`tenant.resolve` 後でもアセット自体はディスク上の共通 `public/` を読めばよい（テナントDB不要な処理は早期returnしてもよいが、初期はresolve共通で単純化する）。

### 8.10 ジョブ・Artisan との関係

HTTP以外ではプレフィックスが無い。

| 実行経路 | テナントの与え方 |
|----------|------------------|
| HTTP | URL prefix → middleware |
| `tenants:migrate` 等 | `--tenant=` / 台帳列挙 |
| キュージョブ | ジョブ generat時に `tenant_id` を載せ、`handle` 冒頭で `TenantDatabaseManager::connect` |

OCR・一括取込を非同期化するときは、この契約を必須にする。

### 8.11 クラス配置（HTTP側）

| クラス（案） | 責任 |
|--------------|------|
| `App\Http\Middleware\InitializeTenancyByPrefix` | §8.5 |
| `App\Http\Middleware\EnsureTenantFeature` | §8.6 |
| `App\Support\Tenancy\TenantContext` | 現在テナント＋features の保持 |
| `App\Support\Tenancy\TenantDatabaseManager` | 接続切替（コマンドと共用） |
| `App\Support\Tenancy\TenantResolver` | prefix / id / code での検索＋キャッシュ |
| `App\Models\Control\Tenant` 等 | `$connection = 'control'` |

### 8.12 段階的導入手順

```text
1. control 接続と Tenant モデルを追加
2. 開発環境にテナント1件を台帳登録（既存DBを紐付け）
3. routes を {tenant_prefix} 化 + tenant.resolve を導入
4. config 互換レイヤで既存画面を通す
5. Version 条件付きルートを tenant.feature に置換
6. VersionController の route:cache を削除
7. 複数テナントDBを載せて疎通確認
```

---

## 9. Storage アダプタ化（onpre / cloud 差の解消）

ファイル保存は現状、Controller / JS の随所で `prefix.server` 分岐している。  
マルチテナント化では **テナントの `storage_mode` に応じたアダプタ1系統** に集約し、業務コードから分岐を消す。

### 9.1 移行前の分岐（記録）

※実装後は `tenant_storage()` + `storage.js`。下表は移行前の負債の記録。

| 経路 | cloud | onpre |
|------|-------|-------|
| 帳簿 単票登録 | JSが `/objectURL` で署名URL取得 → S3へ直接PUT → `/regist/cloud` でDB登録（旧 `cloud.js`） | フォームでサーバ受信 → `custom.file_upload_path` へ `copy`（旧 `onpre.js` + `registPost`） |
| 帳簿 一括（BANBAN） | Controller内で `Storage::disk('s3')->put` | `copy` でローカル |
| 帳簿 DL / プレビュー | `Storage::disk('s3')->get` | ローカル絶対パスを `response()->download` / `file` |
| 名刺画像 | S3 `put` / `putFileAs` とローカル `copy` が混在 | 同上。一部 `\\` 直書き |
| フロント | `layouts` が旧 `onpre.js` / `cloud.js` を切替 | 大半の処理は重複 |

**DBに入るパスの違い（重要）**

- cloud: `ファイルパス` に **prefix 付き**（例: `astec/20260101120000_xxxx`）が多く、実体キーは `{パス}.{拡張子}`  
- onpre: `ファイルパス` は **相対名のみ**（例: `20260101120000_xxxx`）、実体は `{file_upload_path}\{パス}.{拡張子}`

この不統一が、アダプタ化時に最も注意するポイント。

**その他の負債**

- `RegistController::registURL` にバケット名 `astdocs.com` がハードコード  
- Windows 区切り `\\` 前提（Linux onpre で壊れやすい）  
- ~~`wasabi` disk の残骸~~ → **削除済み**（S3 disk のみ。Wasabi は使わない）  
- S3操作が `Storage` と生 `S3Client` で二重

### 9.2 目標

```text
業務コード（Controller）
  → TenantFileStorage（インタフェース）
      → CloudObjectStorage（S3）
      → LocalPathStorage（onpre）
```

- 分岐はアダプタ生成時（テナント設定）だけ  
- DBには **論理相対パス** を統一して保存する（下記）  
- JSは可能なら1ファイル化し、アップロード戦略はサーバが指示する

### 9.3 パス規約（論理キー）

アダプタ化後の正:

| 概念 | 内容 |
|------|------|
| `storage_prefix` | テナントのルート区画（`tenants.storage_prefix`。通常は URL prefix と同じ） |
| `logical_path` | DBに保存する値。**prefix を含まない**相対パス（例: `20260101120000_abcd` や `cards/xxx.jpg`） |
| `object_key` / 実体パス | アダプタが組み立てる実体位置 |

```text
cloud 実体キー  = {storage_prefix}/{logical_path}[.{ext}]
onpre 実体パス = {file_upload_path}/{storage_prefix}/{logical_path}[.{ext}
                 または {file_upload_path}/{logical_path}[.{ext}]
```

**onpre のディレクトリ**

- 推奨: `file_upload_path / storage_prefix / ...` でテナント分離を明示  
- 既存顧客で「upload_path 直下にファイルが既にある」場合は、移行時に `storage_prefix` なし配置を許す互換フラグ（`tenant_settings.storage_flat=true` 等）を用意

**拡張子**

- 現行どおり `ファイル形式` カラムと分離してよい  
- アダプタ API は `(logical_path, extension)` または「拡張子込み logical」のどちらかに揃える。初期は現行踏襲で `(path, ext)` を推奨

**既存データの読み取り互換**

アダプタの `resolve()` で次を吸収する:

1. DB値がすでに `astec/...` のように prefix 付き → そのまま cloud キーとして扱う（二重に付けない）
2. prefix 無し → `storage_prefix` を前置（cloud）／upload_path 配下へ結合（onpre）
3. 区切りは常に `/` で正規化し、ローカルだけ `DIRECTORY_SEPARATOR` に変換

### 9.4 インタフェース（案）

```text
App\Contracts\TenantFileStorage

- put(logicalPath, ext, contents|UploadedFile|resource): void
- get(logicalPath, ext): string|resource
- exists(logicalPath, ext): bool
- delete(logicalPath, ext): void
- downloadResponse(logicalPath, ext, downloadName): BinaryFileResponse|StreamedResponse
- inlineResponse(logicalPath, ext, contentType): Response
- temporaryUploadUrl(logicalPath, ext, ttl): ?string
    cloud: 署名付きPUT URL
    onpre: null（サーバ受信アップロードを使う）
- mode(): 'cloud'|'onpre'
- absoluteKey(logicalPath, ext): string   // 調査・ログ用
```

生成:

```text
TenantStorageFactory::for(Tenant $tenant): TenantFileStorage
  storage_mode=cloud → S3 実装（disk設定 + storage_prefix）
  storage_mode=onpre → Local 実装（file_upload_path + storage_prefix）
```

HTTPでは `tenant.resolve` 後にコンテナへ bind（または `app(TenantFileStorage::class)` が Context を見る）。  
Artisan / Job では `connect(tenant)` 後に同じ Factory を使う。

### 9.5 Laravel disk との関係

| 用途 | 方針 |
|------|------|
| S3認証・バケット | `config/filesystems.php` の `s3`（env）。**顧客ごとのバケットは初期不要**（同一バケット＋prefix隔離） |
| テナント隔離 | キー先頭の `storage_prefix` |
| onpre | 独自にローカルパスを組み立てる。無理に `local` disk の root を毎回書き換えなくてもよいが、実装しやすさなら動的 disk `tenant_local` でも可 |
| Wasabi | **使わない**。disk / env / 旧コメントコードは削除済み |

バケット名・リージョンのハードコードは廃止し、env / config のみとする。

### 9.6 アップロードUXの統一

二系統JSをやめ、戦略をサーバから返す。

**案A（推奨・段階導入しやすい）**

1. フロントは常に「アップロード開始API」を呼ぶ（例: `POST /{prefix}/upload/intent`）
2. 応答:

```json
{
  "strategy": "direct_s3" | "server",
  "upload_url": "https://...",   // direct_s3 のとき
  "logical_path": "20260101120000_abcd",
  "complete_url": "/{prefix}/regist/cloud"
}
```

3. `direct_s3` → 現行 cloud と同様にブラウザからPUT → complete
4. `server` → 通常の multipart をサーバへ（現行 onpre）

**案B（さらに単純）**

- すべてサーバ受信に寄せる（S3もサーバが `put`）  
- UX差は消えるが、大容量・同時アップロードではサーバ負荷が増える

初期は **案A**（現行cloudの署名URLをアダプタの `temporaryUploadUrl` に閉じ込める）が移行コスト低い。

JSは `storage.js` 1本（実装済み）。layouts の `onpre.js`/`cloud.js` 分岐読込は廃止し、旧ファイルも削除済み。  
共通のバリデーション等は既存 `all.js` 側へ残す。

### 9.7 Controller からの利用イメージ

```text
// 登録
$storage = app(TenantFileStorage::class);
$logical = $timestamp . '_' . $pastId;
$storage->put($logical, $extension, $uploadedFile);
$file->ファイルパス = $logical;   // prefix は付けない（新規）
$file->ファイル形式 = $extension;

// ダウンロード
return $storage->downloadResponse($file->ファイルパス, $file->ファイル形式, $name);

// プレビュー
return $storage->inlineResponse(...);
```

`TopController::download` / `imgget`、`RegistController`、`CardController` の `if (server == cloud)` 塊を順次置換する。

### 9.8 テナント設定との対応

| `tenants` カラム | Storageでの意味 |
|------------------|-----------------|
| `storage_mode` | アダプタ選択 |
| `storage_prefix` | オブジェクトキー／サブディレクトリ |
| `file_upload_path` | onpre のルート。cloud は NULL |

プラットフォーム全体が cloud でも、特定テナントだけ onpre ディレクトリ、は技術的には可能。  
ただし運用を単純にするなら、**当面は環境全体で mode を揃え、テナント差は prefix のみ**でもよい。  
カラムはテナント単位に持たせておき、将来の混在に備える。

### 9.9 解約・バックアップ

| mode | データ抽出 |
|------|------------|
| cloud | `storage_prefix/` 配下を S3 sync / 一覧削除 |
| onpre | `{file_upload_path}/{storage_prefix}/` をアーカイブ |

DB dump とセットで「テナント完了パッケージ」にする（取り込み手順メモで再掲）。

### 9.10 段階的移行手順

```text
1. TenantFileStorage インタフェース + S3/Local 実装を追加
2. 新規コード・BANBAN など比較的まとまった箇所から置換
3. download / imgget をアダプタ化（読み取り互換レイヤをここで実装）
4. registURL + cloud/onpre 登録を upload/intent に統合
5. JS を1系統化、layout の分岐読込を削除
6. 生 S3Client / ハードコードバケット / wasabi 参照を削除（完了）
7. 新規保存は logical_path に prefix を含めないルールへ統一
   （既存行は resolve 互換のまま残す）
```

### 9.11 受け入れ条件（完了の定義）

- Controller に `config('prefix.server')` によるファイルI/O分岐が残っていない  
- `onpre.js` / `cloud.js` は削除済み。`storage.js` のみ  
- Wasabi disk / 環境変数は削除済み。クラウドは `s3` のみ  
- 同一テナント設定で local でも S3 でも登録・プレビュー・DLが通る  
- パス結合に `\\` 直書きがない  
- テナントAの `storage_prefix` 配下にテナントBが書き込めない（キー強制）

---

## 10. スーパーユーザーGUIによるテナント追加とDB作成権限

目指す運用は、**厳重にアクセス管理されたスーパーユーザーがGUIから顧客（テナント）を増やせる**こと。  
その操作の裏で「制御DBへの行追加」だけでなく **MySQL上にテナントDBを新規作成**する必要がある。

結論から言うと:

| 問い | 答え |
|------|------|
| MySQL的に GUI から DB を増やすことは可能か | **可能**（実行ユーザーに `CREATE` 権限があれば `CREATE DATABASE` できる） |
| 通常のWebアプリ用DBユーザーにその権限を持たせるべきか | **持たせるべきではない** |
| 推奨 | **アプリ通常接続と、プロビジョニング専用接続を分離**し、GUIは「依頼」→特権ジョブだけがDB作成する |

### 10.1 やりたい操作の内訳

スーパーユーザーがGUIで「顧客追加」したときに起きること:

```text
1. 制御DB: tenants / features / ai_settings を登録（status=provisioning）
2. MySQL: CREATE DATABASE binder_{code}
3. 新DBへ最新 migrate + 初期シード
4. （任意）ストレージ区画の用意（S3 prefix / ローカルディレクトリ）
5. status=active
```

既存EC2顧客の取り込みは、2〜3が「空DB作成＋migrate」ではなく **ダンプ復元（または復元＋不足migrate）** になる点が違う。  
権限設計はどちらも同じ考え方でよい。

### 10.2 MySQL権限の事実整理

| 権限 | 意味 | テナント追加で必要か |
|------|------|----------------------|
| 特定DBへの SELECT/INSERT/UPDATE/DELETE | そのDB内のCRUD | 通常アプリに必要 |
| `CREATE`（グローバルまたは作成対象） | `CREATE DATABASE` / テーブル作成 | **新規テナントDB作成時に必要** |
| `DROP` | DBやテーブル削除 | 解約・失敗ロールバックで欲しくなるが **非常に危険** |
| `CREATE USER` / `GRANT` | DBユーザー発行 | テナントごとにDBユーザーを分けるなら必要（初期は不要でも可） |

ポイント:

- 「GUIでDBを作る」こと自体は、**PHPから `CREATE DATABASE` を発行できるMySQLユーザーがいれば技術的に問題なく可能**  
- 危険なのは「可能かどうか」ではなく、**その強い権限をどのプロセス・どの認証経路が持つか**

現行 `.env.example` は `DB_USERNAME=root` だが、集約後の本番では **アプリ＝root は避ける**。

### 10.3 権限分離モデル（推奨）

接続を最低2系統に分ける。

| 接続名（案） | 用途 | MySQL権限 |
|--------------|------|-----------|
| `control` | 制御DBの読み書き、テナント台帳 | `binder_control.*` のみ |
| `tenant` | リクエスト中の業務CRUD | **既存テナントDBのみ**（`binder_%` への SELECT/INSERT/UPDATE/DELETE 等）。`CREATE DATABASE` なし |
| `provision`（追加） | テナント作成・migrate・取込だけ | `CREATE`（必要なら対象DBへの ALL）。可能なら `DROP DATABASE` は無し |

```text
[ブラウザ]
  → /platform （platform_admins のみ）
      → 「テナント作成」API
          → 制御DBに provisioning 行を書く（control 接続）
          → キューに ProvisionTenantJob を投げる
              → provision 接続だけで CREATE DATABASE / migrate / seed
```

**通常のHTTPワーカー（一般顧客向け）は `provision` 接続設定を読まない／使わない。**  
スーパーユーザーGUIも「DB作成SQLを直接叩かず」、ジョブへ委譲するのが安全。

### 10.4 なぜWebプロセスに CREATE を持たせないか

スーパーユーザーを厳重にしても、次のリスクが残る。

1. **アプリ脆弱性**（SQLインジェクション、RCE、依存ライブラリ）が「CREATE DATABASE付きユーザー」で動くと被害が拡大する
2. 一般向けPHP-FPMと管理画面が同一アプリ同一権限だと、**テナント機能のバグ＝インフラ権限**になる
3. 誤操作やバグで意図しないDB名を作る／（DROPまで持つと）消す

スーパーユーザー認証を固めても、「認証の固さ」と「DBユーザー権限の広さ」は別問題。  
**両方必要**だが、権限は最小化する。

### 10.5 実装パターン比較

| パターン | 概要 | 評価 |
|----------|------|------|
| A. アプリ通常ユーザーに CREATE | GUIから即 `CREATE DATABASE` | 簡単だが本番非推奨 |
| B. provision 専用接続＋キュージョブ | GUIは依頼、ワーカーだけ特権 | **推奨** |
| C. 管理用CLIのみ | GUIは台帳だけ作り、SSHで `tenants:provision` | 安全だが「GUIで完結」に弱い |
| D. MySQL DEFINER付きストアド | `CALL create_tenant_db(?)` だけ許可 | 有効。アプリに広いCREATEを渡さずに済む |
| E. 別ホストの Provision サービス | localhost / VPN のみの内部API | 規模が大きくなったら検討 |

**初期推奨は B（必要なら D を併用）。**  
「GUIで増やせる」は満たしつつ、特権は短命ジョブに閉じる。

### 10.6 GUI（`/platform`）側の要件

スーパーユーザー画面はテナント業務画面と完全分離する。

| 項目 | 方針 |
|------|------|
| 認証 | `platform_admins`（テナント `users` とは別）。MFA推奨 |
| 到達制限 | IP許可・VPN・SSO・Cloudflare Access 等で入口を絞る |
| 監査 | 誰がいつどのテナントを作ったか（`platform_audit_logs` を将来追加してよい） |
| 操作 | 作成・停止・機能変更・AI設定・取込開始・migrate状況閲覧 |
| 非操作 | 任意SQL実行、任意パスへのシェル、他テナントDBの直接ブラウズは初期は出さない |

作成フォーム例:

- code / name / prefix  
- storage_mode  
- 有効機能（features）  
- 新規 or 既存取込（後述）  
- 初期管理者名

送信後は非同期で進行状況を表示（`provisioning` → `active` / 失敗）。

### 10.7 DBユーザー設計の現実的な落とし所

**確定方針（実装済みの接続分離）**

- テナントごとの MySQL ユーザーは **作らない**
- プロジェクトでは **2ユーザー** に固定する:

| 役割 | MySQLユーザー例 | `.env` | 権限 |
|------|-----------------|--------|------|
| 共通アプリ | `binder_app` | `DB_*` / `CONTROL_DB_*` / `TENANT_DB_*` | 既存DBの CRUD（＋migrate用DDL）。**CREATE DATABASE なし** |
| プロビジョニング | `binder_provision` | `PROVISION_DB_*` | `CREATE DATABASE` 可。`DROP DATABASE` は付けない |

確認コマンド:

```bash
php artisan tenancy:db-check
php artisan tenancy:db-check --create-probe   # binder_provision_probe を作成（DROPしない）
```

コード: `App\Support\Tenancy\ProvisionDatabaseManager`（CREATE DATABASE はここ経由のみ）。

MySQL側の作成例（共通ホストで一度だけ）:

```sql
CREATE USER 'binder_app'@'%' IDENTIFIED BY '...';
CREATE USER 'binder_provision'@'%' IDENTIFIED BY '...';

-- 制御DB＋既存/将来のテナントDB（必要に応じて DB 名を追加 GRANT）
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
  ON binder_control.* TO 'binder_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
  ON denshichobo2.* TO 'binder_app'@'%';
-- パターン運用するなら: GRANT ... ON `binder_%`.* TO 'binder_app'@'%';

GRANT CREATE ON *.* TO 'binder_provision'@'%';
-- 作成後の初期 migrate を provision で行うなら対象DBへの権限も付与
FLUSH PRIVILEGES;
```

失敗した空DBの掃除は自動DROPせず、スーパーユーザーの明示操作 or 運用手順にする。

### 10.8 「GUIでDB作成」はインフラ的に許可されるか

AWS / 会社ポリシー側の観点:

| 環境 | 確認ポイント |
|------|--------------|
| 自前EC2上のMySQL | 自分たちで GRANT すれば技術的に可能。ポリシーで root 相当をアプリに禁止しているなら provision 専用に限定 |
| RDS | マスターユーザーで `CREATE DATABASE` 可能。アプリ用に限定ユーザーを切るのが普通。RDSは「DB作成＝インスタンス作成」ではない（同一インスタンス内の schema/database 追加） |
| 権限申請 | 「アプリ常時ユーザー」ではなく「プロビジョニング用ジョブユーザー」として申請すると通りやすい |

つまり **RDS/EC2どちらでも「同一MySQLインスタンス内にDB名を増やす」ことは一般的で可能**。  
不可になるのは、組織が「スキーマ追加も変更管理チケット必須」としている場合で、そのときはGUIは申請＋半自動（承認後ジョブ）にする。

---

## 11. 既存EC2顧客の取り込み手順

既存顧客はすでに **1顧客＝1DB（＋ファイル）** なので、空provisionではなく **移設** になる。  
スーパーユーザーGUIからは「新規作成」と「既存取込」を選べるようにする。

### 11.1 取込の全体像

```text
[旧] 顧客EC2
  MySQL: denshichobo 等
  ファイル: ローカル or その環境のS3 prefix
        ↓ dump / sync
[新] 集約サーバ
  制御DB: tenants 行を追加
  テナントDB: binder_{code} として復元
  ストレージ: storage_prefix 配下へ配置
  migrate: 集約版スキーマとの差分だけ適用
```

### 11.2 GUI「既存取込」フロー（案）

1. スーパーユーザーが code / prefix / 表示名 / storage を入力
2. 取込モード = `import`
3. 制御DBに `status=provisioning` で登録
4. 運用者が用意した成果物を指定（またはサーバ上の作業ディレクトリ）
   - DBダンプファイル（`.sql` / `.sql.gz`）  
   - （任意）ファイルアーカイブ
5. `ImportTenantJob`（provision 接続）が:
   - `CREATE DATABASE`（なければ）  
   - dump をリストア  
   - `tenants:migrate --tenant=` で差分吸収  
   - features / ai_settings をGUI入力どおりに作成（旧 `versions` があれば初期値に変換可）  
   - ストレージを配置
6. スモークチェック後 `active`
7. 旧EC2はDNS/LB切替後に退役

ダンプのアップロードをブラウザから直接受けると大きいので、初期は:

- サーバの所定ディレクトリに SCP/S3 配置 → GUIではパス選択  
- または S3 に置いたオブジェクトキーをGUIに貼る

が現実的。

### 11.3 手動（CLI）でも同じ裏処理

GUIがまだない段階、または緊急時:

```bash
# 台帳登録＋DB作成＋リストア＋migrate を一式
php artisan tenants:import \
  --code=astec \
  --prefix=astec \
  --dump=/var/tmp/imports/astec.sql.gz \
  --storage-mode=cloud \
  --features=tameru,card
```

GUIは最終的にこの処理をジョブ経由で呼ぶだけ、にすると権限境界が一本化される。

### 11.4 スキーマ差分の扱い

旧顧客DBはマイグレーション適用状況がバラバラな想定。

| 方針 | 内容 |
|------|------|
| 正 | 集約アプリの `database/migrations/tenant` を正とする |
| 取込後 | 必ず `tenants:migrate` を当て、足りない列・表を追加 |
| 旧 `versions` | 読めれば `tenant_features` の初期値にマップ。以降は制御DBが正 |
| 失敗 | `tenant_migration_runs` に失敗を残し、`active` にしない |

「dumpを流しただけで最新」とみなさない。

### 11.5 ストレージ移設

| 旧環境 | 新環境での作業 |
|--------|----------------|
| onpre ローカル | ディレクトリを `{file_upload_path}/{storage_prefix}/` へコピー。DBパス互換は Storage resolve に任せる |
| 旧S3 prefix | 新バケット or 同一バケットの `storage_prefix/` へ sync。キーを変えたらDBの `ファイルパス` 変換が必要か判定 |
| 混在 | 顧客ごとに手順チェックリストをGUIの `notes` に残す |

### 11.6 切替とロールバック

```text
1. 取込・migrate・動作確認（active だが LB 未切替でも可）
2. メンテナンス告知
3. 最終差分（取込後に旧側へ書いた分）を再sync（必要なら）
4. LB / DNS を集約サーバの /{prefix} へ
5. 旧EC2は読み取り専用期間を置いてから停止
```

失敗時:

- 制御DBを `suspended` / `provisioning` に戻す  
- 新DBは DROP せず残す（権限的にもDROPは持たない運用）  
- 切替前なら旧EC2のまま継続可能

### 11.7 新規作成と既存取込の権限まとめ

| 操作 | 必要な特権 | 実行場所 |
|------|------------|----------|
| 台帳・機能・AIの更新 | `control` のみ | GUI同期処理で可 |
| `CREATE DATABASE` | `provision` | ジョブのみ |
| dump リストア | `provision`（対象DBの強い権限） | ジョブのみ |
| migrate | `provision` または migrate 可能な app ユーザー | ジョブのみ |
| 通常の帳票CRUD | `tenant`（CREATE DATABASE不可） | 一般リクエスト |

---

## 12. AI接続情報（決定：`.env` で共通管理）

Gemini APIキー、Gemini API URL、社内AIのURL・帳簿パス・トークン・タイムアウト等は、テナント別に保持せず `.env` で共通管理する。

| 区分 | 管理場所 |
|------|----------|
| TAMERU AIの利用先（Gemini / 社内AI） | `tenant_ai_settings.ledger_ocr_provider` |
| Geminiモデル | `tenant_ai_settings.gemini_model`（NULLなら `GEMINI_MODEL`） |
| Gemini APIキー・API URL | `.env` の `GEMINI_API_KEY` / `GEMINI_API_BASE` |
| 社内AI URL・パス・トークン・タイムアウト | `.env` の `INTERNAL_AI_OCR_*` |
| トレース設定 | `.env` の `TAMERU_AI_OCR_TRACE` |

管理画面には利用先とGeminiモデルだけを表示する。秘密情報を入力・表示するフォームは設けない。

---

## 13. platform とサポート入場（現行 asstec 相当）

### 13.1 現状の asstec が担っていること

| 役割 | 現状 |
|------|------|
| 顧客環境へのログイン | 各顧客DBに `astec` ユーザーがおり、問い合わせ時に見た目・挙動を確認 |
| 機能設定など横断管理 | しばしば `Auth::id() === 1` 前提 |

集約後はこの2つを分離する。

| 役割 | 新しい置き場 |
|------|----------------|
| テナント作成・機能・AI・取込 | **platform**（`platform_admins`） |
| 顧客画面の見た目・挙動確認 | **platform → テナント選択 → サポート入場／ユーザー代理ログイン** |

各テナントDBに共有パスワードの `astec` を配り続ける方式は、集約後は非推奨（パスワード横断・監査しづらい）。

### 13.2 目指す操作感（決定方針）

```text
1. スーパーユーザーが /platform にログイン
2. テナント一覧から対象顧客を選択
3. 必要なら「このテナントの画面を開く」
4. さらに「このユーザーとして見る」を選ぶと、
   お客様画面をそのユーザー権限・見た目で操作できる
5. 終了で platform に戻る
```

つまり **platform 認証を維持したまま、テナントアプリ側では選択した `users` になりすます（impersonation）**。

### 13.3 入場レベルの整理

| レベル | 名前 | 挙動 | 用途 |
|--------|------|------|------|
| L0 | platform のみ | 制御DBの管理画面。顧客UIは見ない | テナント追加、機能、AI |
| L1 | テナント入場 | `/{prefix}/...` を開く。テナントDB接続。ログイン主体はサポート用 | 画面全体の確認、機能ON/OFF後の疎通 |
| L2 | ユーザー代理 | 特定の `users.id` として Auth する | 「この人の権限だとどう見えるか」の問い合わせ対応 |

ご要望の「スーパーユーザー → テナント選択 → そのユーザーとしてログイン」は **L2**。  
L1だけでも従来の asstec 入場の多くはカバーできるが、**L2まで持てるようにする**。

### 13.4 L1（テナント入場）のやり方

推奨はどちらか（初期は A）。

**A. 代理ログイン専用の短い署名付き入場（推奨）**

- platform がワンタイムトークンを発行（制御DB or signed URL、短命）  
- `/{prefix}/support/enter?token=...` で消費し、セッション開始  
- テナントDBに常設の asstec パスワードを知らなくても入れる

**B. テナントDB内のサポートユーザーをシード**

- provision 時に `name=support` 等を作成し、パスワードはランダム＋platformだけがローテ可能  
- 従来に近いが、管理コストとパスワード漏洩面で A より劣る

取込済み顧客に古い `astec` が残っていてもよいが、**正規ルートは platform 経由**にする。

L1の「誰として入るか」の既定:

- テナントにサポート専用ユーザーがいればそれ  
- いなければ、**実ユーザーを作らず**セッション上だけ `Auth::login($supportActor)` 相当の Impersonation Guard でもよい  
- ただし現行コードが `users` 行前提（`Auth::user()->管理` 等）なので、初期は **実在ユーザー（サポート用）を1人シード**する方が改修が少ない

### 13.5 L2（ユーザー代理ログイン）

```text
platform 上でテナントの users 一覧を表示（制御DB接続切替 or 読み取り専用）
  → 「このユーザーで開く」
  → セッションに以下を保存して /{prefix}/ へリダイレクト
       impersonator_platform_admin_id
       impersonated_tenant_id
       impersonated_user_id
  → Auth::login(対象User)
```

| ルール | 内容 |
|--------|------|
| 権限 | `platform_admins` に `can_impersonate`（bool）を持たせ、OFFの人は L0/L1 まで |
| 画面表示 | 常時バナー「platform: {name} が {user} として閲覧中」＋「終了」ボタン |
| 終了 | Auth を破棄し `/platform/tenants/{id}` へ戻す |
| 禁止 | 代理中に platform の危険操作（テナント削除等）は不可。代理セッションと platform セッションを分ける |
| パスワード | お客様のパスワードは不要・表示しない |

`platform_admins` に追加するカラム案:

| カラム | 意味 |
|--------|------|
| `can_impersonate` | L2 可否 |
| `can_provision` | テナント作成・取込可否（既存構想と整合） |

### 13.6 セッション設計

顧客向けセッションと platform セッションは分ける。

| Cookie（案） | 用途 |
|--------------|------|
| `binder_platform_session` | `/platform` 用 |
| `binder_session_{prefix}` または共通＋`tenant_id` 検証 | テナントアプリ用 |

代理中のテナントセッションに必須で入れるもの:

- `platform_admin_id`（誰が入っているか）  
- `impersonated_user_id`  
- `impersonation_started_at`

§8.8 の「セッションの tenant_id と resolve 結果が一致しない場合ログアウト」は、代理中も適用する。

### 13.7 監査（必須）

制御DBに簡易ログを持つ（名称は `platform_audit_logs`）。

| カラム案 | 内容 |
|----------|------|
| `platform_admin_id` | 誰が |
| `tenant_id` | どの顧客へ |
| `action` | `tenant_enter` / `impersonate_start` / `impersonate_end` / `tenant_create` 等 |
| `target_user_id` | L2のとき対象 users.id |
| `ip` / `user_agent` | 補足 |
| `created_at` | いつ |

問い合わせ対応の説明責任と、不正利用の抑止のため **L1/L2 は必ず記録**する。

### 13.8 旧 asstec / `Auth::id()===1` からの移行

| 旧 | 新 |
|----|----|
| 各DBの asstec でログイン | platform → テナント入場 |
| id=1 だけ機能設定 | platform の機能設定UI（`tenant_features`） |
| 顧客の「管理」ユーザー | 従来どおりテナント内管理者（代理で確認も可） |

過渡期:

1. 取込DBに asstec が残っていても削除強制しない
2. ただしドキュメント・社内手順は platform 経由に切り替え
3. コードから `Auth::id() === 1` を順次除去

### 13.9 `/platform` 初期画面範囲（この方針に合わせた案）

| 画面 | 内容 |
|------|------|
| ログイン | platform_admins |
| テナント一覧 | 検索、status、prefix、機能概要 |
| テナント詳細 | 機能編集、AI利用先・Geminiモデル、migrate状況 |
| 作成 / 取込 | §10–11 のキック |
| サポート | ユーザー一覧 → L1入場 / L2代理 |
| 監査ログ | 入場・代理の履歴 |

### 13.10 具体的なやり方（決定：トークン入場 ＋ 実ユーザーで Auth）

現行アプリは `Auth::user()` / `users.管理` 前提のため、**「トークンで入場し、テナントDB上の実ユーザーとして login する」** 方式にする。

| 選択 | 決定 |
|------|------|
| 入場手段 | platform が発行する **短命ワンタイムトークン（署名付きURL）** |
| L1の主体 | 各テナントDBにシードするサポートユーザー（例: `name = platform_support`） |
| L2の主体 | お客様の任意の `users` 行 |
| お客様パスワード | 使わない・知らない |

「トークンだけ／ユーザー無し」は既存コード改修が大きいので初期は採用しない。

---

#### 運用者（スーパーユーザー）がやること

問い合わせ対応の手順は次のとおり。

```text
1. /platform に自分のアカウントでログイン
2. テナント一覧から顧客を開く
3. 画面確認だけしたい
     →「テナント画面を開く」（L1）を押す
4. 「この人の画面で見たい」
     → ユーザー一覧から対象を選び「このユーザーで開く」（L2）
     ※ can_impersonate がある人だけ
5. お客様画面（/{prefix}/...）が別タブまたは同ウィンドウで開く
6. 画面上部に「platform: ○○ が △△ として閲覧中」バナーが出る
7. 終わったら「サポート終了」→ /platform のテナント詳細に戻る
```

旧のように各顧客の `astec` パスワードを覚えてログインする必要はない。

---

#### システムが裏でやること（L1）

```text
[platform]
  POST /platform/tenants/{id}/support/enter
    1. platform_admins 認証チェック
    2. 対象テナントが active か確認
    3. ワンタイムトークンを保存（制御DB）
         - token（ランダム）
         - tenant_id
         - platform_admin_id
         - target_user_id = NULL（L1は後でサポートユーザーを解決）
         - expires_at = now+60秒〜5分
         - used_at = NULL
    4. 監査ログ: tenant_enter
    5. リダイレクト先を返す
         /{prefix}/support/callback?token=...

[テナントアプリ]
  GET /{prefix}/support/callback?token=...
    1. tenant.resolve でDB接続
    2. 制御DBで token を検証（未使用・未期限・tenant一致）
    3. used_at を立てて使い捨て
    4. テナントDBの platform_support ユーザーを取得
       （無ければ作成してもよいが、原則 provision/seed 済み）
    5. Auth::login(platform_support)
    6. セッションに platform_admin_id 等を保存
    7. /{prefix}/ （トップ等）へ
```

#### システムが裏でやること（L2）

L1と同じだが、トークン作成時に `target_user_id = 選んだ users.id` を入れ、callback ではそのユーザーで `Auth::login` する。  
`can_impersonate=false` なら platform 側でボタン自体を出さない／403。

---

#### 事前準備（一度だけ／テナント作成時）

1. **制御DB**
   - `platform_admins`（本人アカウント、必要なら `can_impersonate=true`）
   - `platform_support_tokens`（下記）
   - `platform_audit_logs`
2. **各テナントDB（provision / 取込後シード）**
   - サポートユーザーを1人作る

| 項目 | 推奨値 |
|------|--------|
| `name` | `platform_support`（固定。ログインフォームからは使わせない） |
| `表示名` | `サポート（platform）` |
| `管理` | `管理`（全体確認しやすくする。厳しければ `一般`＋必要グループ） |
| `password` | ランダムハッシュ（**誰も知らない**。パスワードログイン不可でも可） |
| `削除` | 空 |

3. テナントの通常ログイン画面で `platform_support` を拒否する（name 一致で弾く）。

   入場は callback 経由の `Auth::login` のみ。

---

#### 制御DBテーブル追加（トークン）

`platform_support_tokens`

| カラム | 説明 |
|--------|------|
| `id` | PK |
| `token_hash` | トークンのハッシュ（生トークンはURLにだけ載せ、DBにはhash） |
| `tenant_id` | 対象テナント |
| `platform_admin_id` | 発行者 |
| `target_user_id` | L2のときのみ。L1は NULL |
| `expires_at` | 有効期限 |
| `used_at` | 使用日時（NULL=未使用） |
| `created_at` | |

---

#### 画面に必ず出すもの

代理／サポート中はテナントアプリのレイアウトにバナー:

```text
[サポート中] platformユーザー「山田」が「田中太郎」として表示しています  [サポート終了]
```

- L1なら対象名は `サポート（platform）`  
- 「サポート終了」で `Auth::logout`、テナントセッション破棄、`/platform/tenants/{id}` へ  
- 監査に `impersonate_end` / `tenant_exit`

---

#### 実装時のファイル／部品（段階5で実装済み）

| 部品 | 役割 |
|------|------|
| `Platform\SupportController` | 入場ボタン → トークン発行 → リダイレクト |
| `SupportAccessController` | callback で token検証 → Auth::login → セッション記録／exit |
| `SupportEntryService` | トークン発行・消費・サポートユーザー確保・監査 |
| `LoginController::loginPost` | 通常loginで `platform_support` を拒否 |
| `partials/support_banner.blade.php` | `session('platform_support.active')` でバナー表示 |
| `tenants:ensure-support-user` | 各テナントDBへ `platform_support` を作成／修復 |

---

#### やってはいけないこと

- お客様に asstec / platform_support のパスワードを共有する  
- トークンを長寿命にする（目安: 数分、使い捨て）  
- トークンをログに生で残す  
- 代理中だと気づかないUI（バナー必須）  
- `Auth::id()===1` のまま機能設定を残す（platform UIへ移す）

---

#### 旧 asstec との関係（移行の具体策）

| 時期 | やること |
|------|----------|
| 取込直後 | 旧 `astec` は残っていてよい。社内手順は platform 入場に変更 |
| 慣れたら | 旧 asstec を無効化（`削除`）またはパスワード無効化 |
| コード | `Auth::id()===1` を削除し、機能設定は `/platform` のみ |

---

## 14. 実装着手順（決定）

この順で進める。前の段階が動いてから次へ進む。

| 段階 | 内容 | 完了の目安 |
|------|------|------------|
| **1** | 制御DB接続と台帳 | `control` 接続、`database/migrations/control`、`tenants` 等の主要テーブル、Tenant モデル |
| **2** | テナント解決ミドルウェア | `{tenant_prefix}` ルート、`tenant.resolve`、DB切替、`config('prefix.*')` 互換。既存1テナントで画面が通る |
| **3** | マイグレーションオーケストレーション | `tenants:migrate` / `migrate-status`（必要なら `provision` の最小版）。複数DB名へ一括適用できる |
| **4** | platform ログインとテナント一覧 | `/platform` 認証（`platform_admins`）、一覧・詳細（機能・AIは閲覧／簡易編集まで可） |
| **5** | サポート入場 | トークン発行、L1/L2、バナー、監査ログ。問い合わせ対応の代替が platform 経由でできる |
| **6** | Storage アダプタ | `TenantFileStorage`、登録／DL／プレビューの分岐解消、JS一本化は段階的で可 |
| **7** | AI設定の配線 | 利用先・Geminiモデルはテナント設定、APIキー・URL・パス・トークン等は `.env` の共通値を読む |

### 各段階で一緒にやる付帯作業

| 段階 | 付帯 |
|------|------|
| 1 | `.env` に `CONTROL_DB_*` / 将来の `PROVISION_DB_*` 枠を用意。アプリ用と特権用の分離方針は守る（CREATEはまだジョブ化前でも接続定義だけ先に） |
| 2 | `versions` 条件付きルートをやめ始め、`tenant.feature` へ置換（一気でなく主要機能から） |
| 3 | 既存顧客DBを台帳に手動登録して2件以上で検証 |
| 4 | 機能設定の `route:cache` 依存・`Auth::id()===1` を platform 側へ寄せ始める |
| 5 | テナントへ `platform_support` シード、通常ログイン拒否 |
| 6–7 | 互換レイヤを残しつつ新規保存ルールへ寄せる |

### 後回し（初期スコープ外） / 不採用

- ~~フェーズ2: テナントごとの MySQL ユーザー分割~~ → **採用しない**  
  （開発MySQLは社内Linuxでプロジェクト横断の共通ホスト。同一サーバー上では漏洩時の隔離実益が薄く、ユーザー乱立だけが増えるため。  
  方針: 共通 `app` ユーザー ＋ 必要なら `CREATE DATABASE` 可の provision ユーザーを `.env` に記述）
- 必要に応じて `.env` の秘密情報を AWS Secrets Manager 等へ移行  
- platform UI の作り込み（グラフ・高度な権限マトリクス等）  

※ サブドメイン方式のテナント解決は **採用しない**（プレフィックス方式で固定）。

### 実装開始時の最初のPRイメージ

```text
PR1: control 接続 + tenants テーブル + 開発用に既存DBを1件台帳登録
PR2: {tenant_prefix} + InitializeTenancyByPrefix（1テナント動作維持）
PR3: tenants:migrate
PR4: /platform ログイン + 一覧
...
```

設計の正本はこのファイル。実装中に決まった差分があれば、都度このメモの該当節と変更履歴を更新する。

---

## 15. 次のアクション

- **段階1〜7 完了**（初期スコープ）  
- **片付け完了**: Wasabi 廃止、旧 JS 削除、旧コメントWasabi経路削除  
- **不採用**: テナントごと MySQL ユーザー分割、サブドメイン解決  
- **Docker / PHP上げ**: 開発用 Linux サーバー側で実施（下記 §17 構想メモ）。**現行 XAMPP では上げない**

### この XAMPP 環境下でまだできること（Docker不要）

| 優先 | 内容 | 備考 |
|------|------|------|
| 高 | `tenants:provision` 実装 | `ProvisionDatabaseManager` で CREATE → migrate → seed → `platform_support` |
| 済 | app / provision 接続分離 | `.env.example` + `tenancy:db-check` + `ProvisionDatabaseManager` |
| 高 | 2テナント以上の疎通 | 2つ目DBを台帳登録し、prefix切替・migrate・サポート入場・storage を確認 |
| 中 | 既存EC2取込手順の実地メモ更新 | dump復元 → `tenants:register` → migrate-status |
| 中 | `Auth::id()===1` / `VersionController` の `route:cache` 寄せ | 機能フラグは platform の `tenant_features` へ |
| 中 | platform UI の最低限強化 | テナント作成フォーム、provisioning 進捗、監査ログ一覧 |
| 低 | 旧 asstec 無効化手順 | サポート入場に慣れたら `削除` or パスワード無効 |
| 低 | Secrets Manager | `.env` 運用で不足すると判断した時点で移行 |

※ PHPバージョンや Docker 本体の構築は **Linux 開発サーバー側**（§17）。ここ（XAMPP）ではアプリ機能・マルチテナント完成度を上げる。

### 段階1〜7の使い方（実装済み）

```bash
# 制御DB作成（初回のみ）
mysql -u root -e "CREATE DATABASE IF NOT EXISTS binder_control CHARACTER SET utf8 COLLATE utf8_general_ci;"

# 制御DBマイグレーション
php artisan control:migrate

# 既存業務DBを台帳へ登録
php artisan tenants:register --code=local --prefix=local --db=denshichobo2 --storage=onpre --features=tameru,card

# 全テナントへ業務マイグレーション適用
php artisan tenants:migrate --force
php artisan tenants:migrate-status

# platform 管理者作成
php artisan platform:admin --name=platform --password=platform123 --can-provision --can-impersonate --force

# サポートユーザーを各テナントへ用意
php artisan tenants:ensure-support-user
# または 1件だけ: php artisan tenants:ensure-support-user --tenant=local
```

- 顧客アプリ: `/{prefix}/...`（例: `/local/login`）。`/` は先頭 active テナントのログインへ  
- Platform: `/platform/login`（予約語のため `tenant_prefix=platform` は不可）  
- 静的ファイル: `tenant_asset('css/...')`。`$prefix` / `$server` は `tenant.resolve` が share  
- Platform ではテナント一覧・基本情報・機能フラグ・AI利用先・Geminiモデルの設定が可能  
- サポート入場: `/platform/tenants/{id}` から L1（テナント画面）／L2（ユーザー代理）。callback は `/{prefix}/support/callback`。終了はバナー「サポート終了」  
- `platform_support` は通常ログイン不可。入場はワンタイムトークンのみ（制御DB `platform_support_tokens` + 監査 `platform_audit_logs`）  
- ファイルI/O: `tenant_storage()` / `TenantFileStorage`（cloud=S3 / onpre=ローカル）。帳簿・名刺・フローの永続I/Oはアダプタ経由  
- フロント: layouts は `js/storage.js` 一本。`#server` が `cloud` のときのみ署名PUT、それ以外は multipart  
- onpre 既定は flat（`file_upload_path` 直下）。`tenant_settings.storage_flat=false` で `{upload}/{storage_prefix}/...` 配置に切替可  
- AI: `TenantAiConfigApplier` が `tenant.resolve` 時に `tenant_ai_settings` を復号して `gemini.*` / `ai_ocr.*` / `version.gemini_*` を上書き（NULLは.envフォールバック）。ジョブ `ProcessUploadedCard` も同様  
- MySQLユーザー: 共通 app ＋（任意）provision。テナントごとユーザーは作らない

---

## 16. 変更履歴

| 日付 | 内容 |
|------|------|
| 2026-11-12 | 初版。制御DB方針とテーブル定義を記載 |
| 2026-11-12 | `tenants:migrate` / `provision` 等のオーケストレーション仕様を追加 |
| 2026-11-12 | プレフィックス解決・DB切替ミドルウェアと機能ガード方針を追加 |
| 2026-11-12 | Storage アダプタ化（onpre/cloud・パス規約・JS統一）を追加 |
| 2026-11-12 | GUIテナント追加とCREATE DATABASE権限分離、EC2取込手順を追加 |
| 2026-11-12 | 用語「APIキー暗号化」「platform」を追記 |
| 2026-11-12 | APIキーは Crypt 採用を決定。platform サポート入場・ユーザー代理を追加 |
| 2026-11-12 | サポート入場の具体手順（トークン＋実ユーザー）を決定・追記 |
| 2026-11-12 | 実装着手順（段階1〜7）を決定 |
| 2026-11-12 | 段階1実装: control接続・台帳migrate・tenants:register |
| 2026-11-12 | 段階2実装: tenant.resolve / feature ガード / ルート常時登録 |
| 2026-11-12 | Blade 静的参照を `tenant_asset()` に統一。`$prefix`/`$server` を view share |
| 2026-11-12 | 段階3実装: `tenants:migrate` / `tenants:migrate-status` |
| 2026-11-12 | 段階4実装: `/platform` ログイン・テナント一覧/詳細・`platform:admin` |
| 2026-11-12 | 段階5実装: サポート入場 L1/L2・バナー・通常ログイン拒否・監査ログ |
| 2026-11-12 | 段階6実装: `TenantFileStorage` + 帳簿経路（登録/DL/プレビュー/objectURL）を配線。名刺・フロー・JS一本化は残 |
| 2026-11-12 | 段階6完了: 名刺/フローを `tenant_storage()` 化、`storage.js` 一本化、Wasabi一時保存をテナントtempへ |
| 2026-11-12 | 段階7実装: `TenantAiConfigApplier` を middleware/ジョブに配線（Crypt復号→config上書き） |
| 2026-11-12 | AI接続情報の方針変更: APIキー・URL・パス・トークン等は `.env` 共通管理。管理画面は利用先・Geminiモデルのみ |
| 2026-11-12 | Wasabi 廃止（filesystems / `.env`）、旧 `onpre.js`/`cloud.js` 削除 |
| 2026-11-12 | `CardController` 旧コメントWasabi経路削除。サブドメイン方式は不採用と明記 |
| 2026-11-12 | テナントごと MySQL ユーザーは不採用と決定。Docker構想を §17 にメモ。XAMPPで可能な次作業を列挙 |
| 2026-11-12 | app/provision 接続分離を必須化: `.env.example`、`ProvisionDatabaseManager`、`tenancy:db-check` |

---

## 17. Docker / 開発用 Linux 構想メモ（未着手）

> **現状**: 個人PCは XAMPP。PHP上げ・Docker化は **開発用 Linux サーバー** で進める。  
> この節は構想のみ。リポジトリへの `Dockerfile` 追加は Linux 側着手時。

### 17.1 ねらい

- プロジェクト単位で **PHP（と必要なら nginx/apache）をコンテナ化**し、バージョンを上げやすくする  
- **MySQL は共通ホスト**のまま（他プロジェクトと同じ）。コンテナ内に MySQL を抱え込まない  
- Binder だけ特別扱いせず、社内の「アプリはDocker・DBは共通」に揃える

### 17.2 構成イメージ

```text
[開発用 Linux]
  ├── MySQL（共通・既存）
  │     users: app（CRUD）, provision（CREATE DATABASE 可・任意）
  │     DBs:   binder_control, binder_{code}, （他プロジェクトのDB）
  │
  └── binder（このプロジェクト）Docker
        ├── app (php-fpm … 上げたいPHPバージョン)
        ├── web (nginx または apache) ※1コンテナにまとめても可
        └── .env
              DB_HOST / CONTROL_DB_HOST / TENANT_DB_HOST
                → 共通 MySQL のホスト名（docker bridge から見えるIP/DNS）
              DB_USERNAME=app
              PROVISION_DB_USERNAME=provision   # または開発中は app と同じ
```

ポイント:

- コンテナから共通 MySQL へは **ホストネットワーク / extra_hosts / 固定IP** のいずれかで到達できればよい  
- ファイル保存（onpre）はボリュームマウント（既存 `file_upload_path` 相当の共有パス）  
- queue / scheduler が必要なら同一イメージの別サービス、またはホスト cron で `artisan` 実行

### 17.3 `.env` ユーザー方針（確定）

| 接続 | ユーザー | 権限 |
|------|----------|------|
| `mysql` / `control` / `tenant` | 共通 `binder_app`（`DB_*` 等） | 既存DBへの DML（＋migrate用DDL）。CREATE DATABASE なし |
| `provision` | `binder_provision`（`PROVISION_DB_*`） | `CREATE DATABASE` 可（DROP DATABASE なし） |

テナントごとに MySQL ユーザーは **作らない**。  
確認: `php artisan tenancy:db-check`
### 17.4 移行ステップ（Linux側・案）

1. 共通 MySQL に `binder_control` と検証用テナントDBがあることを確認  
2. このリポジトリ用 `Dockerfile` + `compose`（PHP上げ）を追加  
3. `.env` のホストを共通 MySQL 向けに設定し、`control:migrate` / `tenants:migrate` が通ることを確認  
4. 個人 XAMPP は参照・緊急用に残し、日常開発は Linux Docker へ切替  
5. その後 `tenants:provision` を provision ユーザーで検証

### 17.5 いまやらないこと

- 個人PCの XAMPP 上での PHP バージョンアップ  
- コンテナ内 MySQL の新規構築（共通DB方針と矛盾）  
- テナントごと DB ユーザー自動発行  
