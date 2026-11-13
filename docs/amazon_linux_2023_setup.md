# Amazon Linux 2023 新規サーバー構築メモ

対象は電子帳簿（denshichobo）の新しい本番サーバー。アプリは Docker、MySQL は同じサーバーのホストに置く。S3 バケットは既存のものをそのまま使う。

公開する待受はアプリの 80 番だけにする。phpMyAdmin は `127.0.0.1:11000` とし、インターネットへは開けない。

## 1. このサーバーに作るもの

| パス | 役割 |
|---|---|
| `/var/www/denshichobo` | アプリケーション一式 |
| `/var/www/denshichobo/docker-compose.prod.yml` | アプリ用 compose。リポジトリに含まれている |
| `/var/www/denshichobo/.env.production` | 本番の接続情報。Git に入れない |
| `/var/www/phpmyadmin/docker-compose.yml` | phpMyAdmin 用 compose。このサーバーで新規作成 |
| `/etc/my.cnf.d/denshichobo.cnf` | MySQL の待受と文字コード |
| `/var/lib/mysql` | MySQL のデータ本体（パッケージの既定） |

ホストには Apache も nginx も入れない。80 番はアプリコンテナが使う。

データベースの役割:

| 名前 | 用途 |
|---|---|
| `denshichobo_control` | テナント台帳と管理画面 |
| `denshichobo_` で始まる名前 | テナントごとの業務データ。初期構築では作らない |
| `denshichobo_app` | 通常接続とマイグレーション |
| `denshichobo_provision` | テナント用データベースの作成と削除 |

旧来の単一データベース `denshichobo` は作らない。

## 2. EC2 を作った直後

セキュリティグループ:

- 22 番は作業元の IP だけ
- 80 番は公開する相手（ALB、または直接公開する範囲）
- 3306 番と 11000 番は開けない

IAM ロールをインスタンスに付ける。そのロールには、既存バケットへの取得・保存・削除を許可する。アクセスキーは使わない。

アプリはコンテナの中からロールを読む。メタデータのホップ上限が 1 のままだとコンテナから届かないので、作業用 PC で次を実行する。`i-xxxxxxxx` は対象インスタンス ID に置き換える。

```bash
aws ec2 modify-instance-metadata-options \
  --instance-id i-xxxxxxxx \
  --http-endpoint enabled \
  --http-tokens required \
  --http-put-response-hop-limit 2
```

## 3. OS と Docker

サーバーへ `ec2-user` で入る。

```bash
sudo timedatectl set-timezone Asia/Tokyo
sudo dnf update -y
sudo dnf install -y docker git
sudo systemctl enable --now docker
sudo usermod -aG docker ec2-user
```

一度ログアウトして入り直し、次で compose が入っていることを確認する。

```bash
docker version
docker compose version
```

`docker compose` が無いときだけ、プラグインを追加する。

```bash
sudo mkdir -p /usr/local/lib/docker/cli-plugins
sudo curl -SL https://github.com/docker/compose/releases/download/v2.40.3/docker-compose-linux-x86_64 \
  -o /usr/local/lib/docker/cli-plugins/docker-compose
sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
docker compose version
```

## 4. MySQL 8.4

Amazon Linux 2023 向けに、MySQL 公式の el9 リポジトリを使う。旧本番の dump が MySQL のため、OS 標準の MariaDB は入れない。

```bash
sudo dnf install -y https://dev.mysql.com/get/mysql84-community-release-el9-1.noarch.rpm
sudo dnf install -y mysql-community-server
```

`/etc/my.cnf.d/denshichobo.cnf` を作る。

```ini
[mysqld]
bind-address = 0.0.0.0
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
```

```bash
sudo systemctl enable --now mysqld
sudo grep 'temporary password' /var/log/mysqld.log
```

表示された一時パスワードで入り、root のパスワードを置き換える。

```bash
mysql -u root -p --connect-expired-password
```

```sql
ALTER USER 'root'@'localhost' IDENTIFIED BY 'ここにrootの新しいパスワード';
```

続けて制御DBとアプリ用ユーザーを作る。パスワードはここで生成し、`.env.production` 以外には残さない。

```bash
openssl rand -hex 24
openssl rand -hex 24
```

```sql
CREATE DATABASE denshichobo_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'denshichobo_app'@'%' IDENTIFIED BY '1つ目のパスワード';
CREATE USER 'denshichobo_provision'@'%' IDENTIFIED BY '2つ目のパスワード';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
  ON denshichobo_control.* TO 'denshichobo_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
  ON `denshichobo\_%`.* TO 'denshichobo_app'@'%';

GRANT CREATE, DROP ON *.* TO 'denshichobo_provision'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
  ON denshichobo_control.* TO 'denshichobo_provision'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
  ON `denshichobo\_%`.* TO 'denshichobo_provision'@'%';

FLUSH PRIVILEGES;
```

`denshichobo\_%` のアンダースコアは、1文字のワイルドカードにしないための指定である。このまま実行する。

## 5. アプリの配置

```bash
sudo mkdir -p /var/www
sudo chown ec2-user:ec2-user /var/www
git clone <リポジトリURL> /var/www/denshichobo
cd /var/www/denshichobo
```

`/var/www/denshichobo/.env.production` を作る。`docker/env.production.example` が元になる。このファイルは compose の変数展開と、コンテナへ渡す環境変数の両方に使う。

```bash
cp docker/env.production.example .env.production
chmod 600 .env.production
```

埋める値:

```text
DENSHICHOBO_HTTP_PORT=80
DENSHICHOBO_APP_URL=https://公開するURL
DENSHICHOBO_APP_KEY=base64:（下のコマンドで作る）
DENSHICHOBO_DB_HOST=host.docker.internal
DENSHICHOBO_DB_PORT=3306
DENSHICHOBO_DB_DATABASE=denshichobo_control
DENSHICHOBO_CONTROL_DB_DATABASE=denshichobo_control
DENSHICHOBO_DB_USERNAME=denshichobo_app
DENSHICHOBO_DB_PASSWORD=denshichobo_app のパスワード
DENSHICHOBO_PROVISION_DB_USERNAME=denshichobo_provision
DENSHICHOBO_PROVISION_DB_PASSWORD=denshichobo_provision のパスワード
```

`APP_KEY` は次で作る。出力を `DENSHICHOBO_APP_KEY` にそのまま入れる。

```bash
echo "base64:$(openssl rand -base64 32)"
```

同じファイルの末尾に、Laravel が直接読む項目を足す。アクセスキーの行は書かない。`null` と書くと IAM ロールが使われない。

```text
APP_DISPLAY_VERSION=8.0.0
GEMINI_MODEL=gemini-3.8-flash
GEMINI_API_KEY=
GEMINI_API_BASE=https://generativelanguage.googleapis.com/v1beta
TAMERU_AI_OCR_PROVIDER=gemini
GHOSTSCRIPT_PATH=/usr/bin/gs
LOG_CHANNEL=stack
LOG_LEVEL=info
TENANCY_REQUIRE_SEPARATE_PROVISION_USER=true
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=1200
QUEUE_CONNECTION=sync
AWS_DEFAULT_REGION=バケットのリージョン
AWS_BUCKET=既存のバケット名
```

`AWS_DEFAULT_REGION` はバケットの実際のリージョンにする。`us-east-1` は例であり、東京のバケットでは使わない。

## 6. アプリの起動

```bash
cd /var/www/denshichobo
docker compose -f docker-compose.prod.yml --env-file .env.production up --build -d
docker compose -f docker-compose.prod.yml --env-file .env.production ps
```

コンテナ起動時に `control:migrate` が走る。管理画面のユーザーは起動後に作る。

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production exec app \
  php artisan platform:admin \
  --name=platform \
  --password='管理画面のパスワード' \
  --can-provision \
  --can-impersonate
```

確認:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production exec app php artisan tenancy:db-check
curl -sS -o /dev/null -w '%{http_code}\n' http://127.0.0.1/platform/login
```

4つの接続が成功し、ログイン画面が `200` ならここまでは完了。管理画面は `http://サーバー/platform/login`。

`.env.production` を変えたあとは、設定がキャッシュされているのでコンテナを作り直す。

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --force-recreate
```

## 7. phpMyAdmin

`/var/www/phpmyadmin/docker-compose.yml` を新規作成する。アプリの compose には入れない。

```yaml
services:
  phpmyadmin:
    image: phpmyadmin:5.2.3
    restart: unless-stopped
    ports:
      - "127.0.0.1:11000:80"
    environment:
      PMA_HOST: host.docker.internal
      PMA_PORT: "3306"
      UPLOAD_LIMIT: 64M
      PMA_ABSOLUTE_URI: http://127.0.0.1:11000/
    extra_hosts:
      - "host.docker.internal:host-gateway"
```

```bash
cd /var/www/phpmyadmin
docker compose up -d
docker compose ps
```

作業用 PC から SSH トンネルを張って開く。

```bash
ssh -i "example.pem" -L 11000:127.0.0.1:11000 ec2-user@サーバーのホスト
```

ブラウザは `http://127.0.0.1:11000/`。ログインは MySQL のユーザー名とパスワード。

画面からの SQL 取り込みは 64MB まで。それより大きい dump は phpMyAdmin を使わず、サーバー上で次のように入れる。データベースは先に空で作っておく。

```bash
sudo mysql -e "CREATE DATABASE denshichobo_顧客名 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql denshichobo_顧客名 < /path/to/customer.sql
```

## 8. 止めるときと上げるとき

```bash
cd /var/www/denshichobo
docker compose -f docker-compose.prod.yml --env-file .env.production down

cd /var/www/phpmyadmin
docker compose down
```

起動はそれぞれのディレクトリで `docker compose up -d`。アプリ側は `--env-file .env.production` と `-f docker-compose.prod.yml` を付ける。MySQL は `sudo systemctl start mysqld`。OS 起動時は `enable` 済みなら自動で上がる。
