# Imagem oficial base PHP 8.4 com Apache
FROM php:8.4-apache

# Metadados da Imagem
LABEL maintainer="Projeto Web Seguro - DevSecOps"
LABEL description="Contêiner de Aplicação PHP 8.4 + Laravel 12 isolado"

# Variáveis de ambiente de build
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV COMPOSER_ALLOW_SUPERUSER=1

# Instalação de bibliotecas essenciais e extensões PHP necessárias para Laravel 12
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install -j$(nproc) bcmath opcache zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Hardening e módulos do Apache interno
RUN a2enmod rewrite headers \
    && sed -ri -e 's!ServerTokens OS!ServerTokens Prod!g' /etc/apache2/conf-available/security.conf \
    && sed -ri -e 's!ServerSignature On!ServerSignature Off!g' /etc/apache2/conf-available/security.conf \
    && a2enconf security

# Copia a configuração do VirtualHost com DocumentRoot em /var/www/html/public
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Instalação do Composer 2 oficial via cópia de binário
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Diretório de trabalho padrão
WORKDIR /var/www/html

# Copia arquivos de definição do Composer primeiro (otimização de cache de camadas)
COPY composer.json composer.lock ./

# Instala dependências de produção
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# Copia os arquivos da aplicação
COPY . .

# Finaliza autoload otimizado de produção
RUN composer dump-autoload --optimize --no-dev

# Garante permissões rigorosas para www-data nos diretórios de escrita
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# A porta 80 é interna ao contêiner
EXPOSE 80

# Healthcheck interno do contêiner Docker
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://127.0.0.1/health || exit 1

# Inicialização padrão do Apache no contêiner
CMD ["apache2-foreground"]
