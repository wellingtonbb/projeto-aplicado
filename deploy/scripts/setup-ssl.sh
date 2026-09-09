#!/usr/bin/env bash
# ==============================================================================
# PROJETO WEB SEGURO — SECURE BY DESIGN / SECURE BY DEFAULT
# Script de Emissão e Renovação de Certificado SSL/TLS via Certbot (HOST)
# Requisito 28: Certbot instalado exclusivamente no HOST, gerenciando Let's Encrypt
# ==============================================================================

set -euo pipefail

if [ $# -lt 1 ]; then
    echo "Uso: $0 <seu-dominio.com.br> [email@exemplo.com]"
    echo "Exemplo: $0 app.meudominio.com admin@meudominio.com"
    exit 1
fi

DOMAIN="$1"
EMAIL="${2:-admin@$DOMAIN}"
APACHE_CONF="/etc/apache2/sites-available/secure-web.conf"

echo "======================================================================"
echo "    CONFIGURAÇÃO DE CERTIFICADO SSL/TLS LET'S ENCRYPT COM CERTBOT    "
echo "======================================================================"
echo "Domínio alvo: $DOMAIN"
echo "E-mail de contato: $EMAIL"

# 1. Habilitação do módulo SSL e headers no Apache Host
sudo a2enmod ssl headers rewrite proxy proxy_http

# 2. Copia a configuração do VirtualHost ajustando o domínio
echo "===> [1/4] Copiando e ajustando a configuração do Apache Host..."
sudo cp "$(dirname "$0")/../apache/secure-web.conf" "$APACHE_CONF"
sudo sed -i "s/SEU_DOMINIO_OU_IP/$DOMAIN/g" "$APACHE_CONF"

# 3. Emissão do Certificado Let's Encrypt pelo Certbot no HOST
echo "===> [2/4] Executando Certbot no HOST para emissão do certificado TLS..."
sudo certbot --apache \
    --non-interactive \
    --agree-tos \
    --email "$EMAIL" \
    --domains "$DOMAIN" \
    --redirect

# 4. Habilitar o VirtualHost e reiniciar Apache
echo "===> [3/4] Habilitando VirtualHost seguro e recarregando o Apache..."
sudo a2ensite secure-web.conf
sudo apache2ctl configtest
sudo systemctl reload apache2

# 5. Teste de Renovação Automática (Dry-Run) - Requisito 28
echo "===> [4/4] Executando teste de renovação automática (dry-run)..."
sudo certbot renew --dry-run

echo "======================================================================"
echo " [OK] Certificado SSL emitido e testado com sucesso para $DOMAIN!"
echo " A aplicação agora está acessível via HTTPS público com redirecionamento 301."
echo "======================================================================"
