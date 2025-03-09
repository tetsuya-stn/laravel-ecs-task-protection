FROM php:8.2.27-fpm-bookworm

# タイムゾーンを東京に設定
ENV TZ=Asia/Tokyo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 必要なパッケージのインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    zip \
    procps \
    mariadb-client

# PHP拡張機能のインストール
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    soap \
    sockets

# キャッシュのクリア
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Composerのインストール
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# PHPの設定
RUN echo "date.timezone = Asia/Tokyo" > $PHP_INI_DIR/conf.d/timezone.ini
RUN echo "memory_limit = 256M" > $PHP_INI_DIR/conf.d/memory-limit.ini

# Supervisorの設定ディレクトリを作成
RUN mkdir -p /etc/supervisor/conf.d

# 作業ディレクトリの設定
WORKDIR /var/www/html/

COPY ./laravel/ /var/www/html/

# スクリプトの追加
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# エントリーポイントの設定
ENTRYPOINT ["docker-entrypoint.sh"]

# デフォルトコマンドをPHP-FPMに設定
CMD ["php-fpm"]
