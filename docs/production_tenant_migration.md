# 既存テナント本番移行メモ

## 1. 方針

既存顧客の移行では、管理画面の「テナント追加」を使用しない。

現在の「テナント追加」は、空のDBを作成して最新のマイグレーションを適用する新規顧客向けの機能である。既存DBのdumpをそのDBへ復元すると、テーブルの重複やマイグレーション履歴の不整合が起きる可能性がある。

既存顧客は、次の順番で移行する。

```text
旧MySQLからdump
    ↓
新環境に空DBを作成
    ↓
dumpを復元
    ↓
制御DBへ「準備中」で登録
    ↓
不足分のマイグレーションを適用
    ↓
ファイル移行・動作確認
    ↓
「利用中」へ変更してALBを切り替え
```

最初から全顧客を移行せず、1テナントでリハーサルを完了してから横展開する。

---

## 2. 作業前に顧客ごとに記録する項目

- 顧客名
- テナントコード
- URLプレフィックス
- 旧EC2の識別子・IP
- 旧MySQLのホスト、ポート、DB名、MySQLバージョン
- 新しいDB名（例: `binder_customer_a`）
- 文字コード・照合順序
- 利用機能
  - TAMERU: `tameru`
  - 名刺: `card`
  - フロー: `flow`
  - スケジュール: `schedule`
  - 文書: `transcribe`
  - BANBAN: `banban`
  - TAMERU AI: `ichifuji`
- ファイル保存方式と現在の保存先
- 旧環境の`APP_KEY`が必要な暗号化データの有無
- 移行予定日時と確認担当者

パスワードや秘密鍵そのものは、このメモや作業ログへ記載しない。

---

## 3. 事前確認

### 3.1 新環境

```bash
php artisan tenancy:db-check
php artisan tenants:migrate-status
```

確認事項:

- 制御DBへ接続できる
- テナントDB用ユーザーとDB作成用ユーザーが分離されている
- 新しいDB名、テナントコード、URLプレフィックスが未使用
- 新環境のコードが移行対象の最新バージョン
- migrationファイルが過去分を含めて揃っている
- DBとファイルのバックアップ先に十分な空き容量がある

### 3.2 旧環境

```sql
SELECT VERSION();
SHOW CREATE DATABASE 旧DB名;
SELECT COUNT(*) FROM migrations;
SELECT migration, batch FROM migrations ORDER BY id DESC LIMIT 10;
```

次の場合は、そのまま一括migrationを実行しない。

- `migrations`テーブルが存在しない
- migration履歴と実際のテーブル・カラムが一致していない
- 過去に手動でテーブルやカラムを変更している
- 新旧MySQLで互換性のないバージョン差がある

この場合は、既存migrationを書き換えず、`Schema::hasTable()`や`Schema::hasColumn()`を使用した補正migrationを別途用意する。

---

## 4. リハーサル移行

リハーサル中は旧環境を稼働させたままでよい。ただし、リハーサル後に追加されたデータは最終移行時に再取得する。

以下の変数は、対象テナントに合わせて読み替える。

```text
OLD_DB=旧環境のDB名
NEW_DB=binder_customer_a
TENANT_CODE=customer_a
TENANT_PREFIX=customer_a
DUMP_FILE=customer_a.sql
```

### 4.1 旧DBのdump取得

旧EC2上、または旧MySQLへ安全に接続できる端末で実行する。

```bash
mysqldump \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  --events \
  --hex-blob \
  --default-character-set=utf8mb4 \
  --no-tablespaces \
  -h 旧DBホスト \
  -P 3306 \
  -u 旧DBユーザー \
  -p \
  旧DB名 > customer_a.sql
```

注意:

- `migrations`テーブルを除外しない
- `mysql`、`information_schema`等のシステムDBはdumpしない
- パスワードをコマンド引数へ直接書かない
- 主にInnoDBを使用していることを確認する
- MyISAM等がある場合、`--single-transaction`だけでは完全な整合性を保証できない
- `--routines`等の権限がない場合は、対象オブジェクトが存在しないことを確認してからオプションを外す

dump取得後に、ファイルサイズとチェックサムを記録する。

```bash
ls -lh customer_a.sql
sha256sum customer_a.sql
```

### 4.2 新環境へdumpを転送

SSH/SCPや社内で承認された転送方法を使用する。dumpには顧客情報が含まれるため、外部の一時共有サービスへアップロードしない。

転送後に再度チェックサムを確認する。

```bash
sha256sum customer_a.sql
```

転送前後の値が一致しない場合は復元しない。

### 4.3 新しい空DBを作成

旧DBの文字コード・照合順序を確認し、同じ設定または事前に決定した統一設定で作成する。

```bash
mysql \
  -h 新DBホスト \
  -P 3306 \
  -u DB作成用ユーザー \
  -p \
  -e "CREATE DATABASE \`binder_customer_a\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

作成前に同名DBが存在しないことを必ず確認する。

```sql
SELECT SCHEMA_NAME
FROM INFORMATION_SCHEMA.SCHEMATA
WHERE SCHEMA_NAME = 'binder_customer_a';
```

### 4.4 dumpを復元

```bash
mysql \
  -h 新DBホスト \
  -P 3306 \
  -u DB取込用ユーザー \
  -p \
  binder_customer_a < customer_a.sql
```

復元後に最低限以下を確認する。

```sql
USE binder_customer_a;
SHOW TABLES;
SELECT COUNT(*) FROM migrations;
SELECT migration, batch FROM migrations ORDER BY id DESC LIMIT 10;
SELECT COUNT(*) FROM users;
```

主要テーブルの件数は、旧DB側の件数と比較して記録する。

### 4.5 制御DBへ「準備中」で登録

```bash
php artisan tenants:register \
  --code=customer_a \
  --name="顧客A" \
  --prefix=customer_a \
  --db=binder_customer_a \
  --storage=cloud \
  --features=tameru,card,flow \
  --status=provisioning
```

オンプレ保存の場合:

```bash
php artisan tenants:register \
  --code=customer_a \
  --name="顧客A" \
  --prefix=customer_a \
  --db=binder_customer_a \
  --storage=onpre \
  --upload-path="/実際の保存先" \
  --features=tameru,card,flow \
  --status=provisioning
```

注意:

- `--features`には実際に有効な機能だけを指定する
- 有効機能が1つもない場合は、登録後に管理画面で全機能を無効化する
- 登録時は必ず`--status=provisioning`を指定する
- `--force`は既存登録を更新するため、内容を確認せず使用しない

### 4.6 migrationの確認と実行

```bash
php artisan tenants:migrate-status --tenant=customer_a
php artisan tenants:migrate --tenant=customer_a --dry-run
```

`dry-run`は未適用件数を確認するもので、SQLが実際に成功することまでは保証しない。

DBバックアップが取得できていることを再確認してから実行する。

```bash
php artisan tenants:migrate --tenant=customer_a --force
php artisan tenants:migrate-status --tenant=customer_a
```

失敗した場合:

- テナントは「準備中」のままにする
- 管理画面のDB更新履歴と`laravel.log`を確認する
- エラーを確認せず同じコマンドを繰り返さない
- MySQLのDDLは暗黙コミットされる場合があり、migration失敗時に完全にはロールバックされない
- dumpからDBを作り直すか、状態を確認した上で補正migrationを作成する

### 4.7 ファイルを移行

DBとは別に、旧EC2やS3に保存されているファイルを移行する。

- 旧ローカル保存先から新しい保存先へコピー
- S3の場合はテナントの`storage_prefix`配下へ配置
- ファイル数・総容量・代表ファイルのチェックサムを比較
- 管理画面の「接続テスト」で新しい保存先の読み書きを確認
- 画像、PDF、添付ファイル等を画面から実際に開く

### 4.8 パスワード・暗号化データ

- `Hash::make()`で保存されたログインパスワードは、そのまま移行できる
- `Hash::check()`は`APP_KEY`に依存しない
- セッションやCookieは移行せず、切替後に再ログインする
- `Crypt::encryptString()`で暗号化した値は旧環境の`APP_KEY`に依存する

本システムではメール設定のパスワードに`Crypt`を使用している。旧EC2と新環境の`APP_KEY`が異なる場合は、旧環境で復号し、新環境の鍵で再暗号化する専用処理が必要になる。

平文のパスワードをファイル、DB、ログへ一時保存しない。

---

## 5. リハーサル後の確認

テナントを「利用中」にする前に確認する。

- テナントコード、URLプレフィックス、接続先DB名が正しい
- 別テナントのデータが表示されない
- 一般ユーザーでログインできる
- 管理者ユーザーでログインできる
- 主要画面が開く
- 登録・更新・削除がテストデータで正常に動作する
- TAMERU、名刺、フロー等の契約機能が正しい
- 無効な機能が表示されない
- ファイルの登録・表示・ダウンロードができる
- メール送信設定が必要な場合は再暗号化後に確認する
- `tenants:migrate-status`の未適用件数が0
- `tenant_migration_runs`の最新状態が`succeeded`
- LaravelログとMySQLログに重大なエラーがない

リハーサルで作成したデータは、本番切替前に削除するか、最終dumpでDB全体を作り直す。

---

## 6. 本番切替

### 6.1 切替直前

1. 旧環境のバックアップを取得する
2. 新環境のリハーサルDBも必要に応じてバックアップする
3. 旧環境をメンテナンス状態にして書込みを停止する
4. バッチ、キュー、定期処理も停止する
5. 旧DBの接続数や更新日時を確認し、書込みが止まったことを確認する
6. 最終dumpを取得する
7. 旧EC2上のファイルについて最終同期を行う

書込み停止を確認せずに最終dumpを取得しない。

### 6.2 最終DB復元

リハーサルDBをそのまま本番データとして使わず、原則として最終dumpから作り直す。

1. 対象テナントが「準備中」であることを確認
2. リハーサルDBのバックアップを取得
3. 対象DB名を再確認
4. 対象DBだけを削除して再作成
5. 最終dumpを復元
6. migrationを実行
7. 件数と動作を再確認

`DROP DATABASE`は対象名を二人で確認してから実行する。制御DBや別テナントDBを対象にしない。

### 6.3 切替

1. 管理画面でテナント設定と機能を最終確認
2. テナント状態を「利用中」に変更
3. ALBの向け先を共通EC2へ切り替える
4. 一般ユーザーでログインする
5. 代表的な参照・登録・ファイル操作を確認する
6. エラーログ、CPU、メモリ、MySQL接続数を監視する

旧EC2はすぐに削除せず、ロールバック期間中は書込み停止状態で保持する。

---

## 7. ロールバック

切替前に問題が見つかった場合:

- ALBは旧環境のまま維持する
- 新テナントは「準備中」のままにする
- 原因を修正して再度リハーサルする

切替直後に問題が見つかった場合:

1. 新環境への書込みを停止する
2. 新環境で書込みが発生したか確認する
3. 書込みがない場合のみ、ALBを旧環境へ戻す
4. 書込みがある場合はデータ差分を確認し、単純に旧環境へ戻さない

新旧両方で同時に書込みを受け付ける状態にしない。

---

## 8. 顧客ごとの作業記録

```text
顧客名:
テナントコード:
URLプレフィックス:
旧DB名:
新DB名:
旧MySQLバージョン:
新MySQLバージョン:
文字コード・照合順序:

リハーサルdump取得日時:
リハーサルdump SHA-256:
リハーサルmigration結果:
リハーサル確認担当:

本番書込み停止日時:
最終dump取得日時:
最終dump SHA-256:
最終migration結果:
ファイル同期完了日時:
ALB切替日時:
最終確認担当:

ロールバック期限:
旧EC2削除予定日:
備考:
```

---

## 9. 完了条件

- 全対象テナントが制御DBへ登録されている
- 全テナントのmigrationが成功している
- テナントごとのDB分離を確認している
- DB件数とファイル件数の照合が完了している
- ログインパスワードが継続利用できる
- `Crypt`対象データの再暗号化または再設定が完了している
- ALB切替後の動作確認が完了している
- バックアップとロールバック期限が記録されている
- 旧環境の削除はロールバック期限後に別作業として実施する
