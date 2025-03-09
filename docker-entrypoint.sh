#!/bin/bash
set -e

# スクリプトが実行されたことを表示
echo "Running entrypoint script..."

# 開発モードかどうかを環境変数から判断
if [ "$APP_ENV" = "local" ] || [ "$APP_ENV" = "development" ]; then
  echo "Running in development mode..."

  # Laravelのストレージディレクトリの権限を設定
  chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache || true

  # Composerの依存関係が最新かチェック
  if [ -f "composer.json" ]; then
    composer install --no-interaction --optimize-autoloader
  fi

  # キャッシュをクリア
  php artisan optimize:clear

else
  echo "Running in production mode..."

  # プロダクション環境ではキャッシュを生成
  if [ -f "composer.json" ]; then
    composer install --no-interaction --no-dev --optimize-autoloader
  fi

  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
fi

# コマンドを実行（通常はphp-fpm）
exec "$@"
