#!/usr/bin/env bash
# ==============================================================================
# PROJETO WEB SEGURO — SECURE BY DESIGN / SECURE BY DEFAULT
# Script de Provisionamento Completo do HOST (Debian 13)
# Instala e configura: Apache 2.4, Docker, Certbot, Fail2Ban, UFW e SSH Hardening
# ==============================================================================

set -euo pipefail

echo "======================================================================"
echo "      PROJETO WEB SEGURO — PROVISIONAMENTO DO DEBIAN 13 HOST          "
echo "======================================================================"

# 1. Atualização do Sistema Operacional
echo "===> [1/8] Atualizando repositórios e pacotes do sistema..."
sudo apt-get update && sudo apt-get upgrade -y

# 2. Instalação de dependências essenciais e ferramentas de segurança
echo "===> [2/8] Instalando Apache 2.4, Certbot, Fail2Ban, UFW, Git e utilitários..."
sudo apt-get install -y --no-install-recommends \
    apache2 \
    certbot \
    python3-certbot-apache \
    fail2ban \
    ufw \
    curl \
    git \
    ca-certificates \
    gnupg \
    lsb-release

# 3. Habilitação dos módulos necessários do Apache no HOST (Requisito 25 e 47)
echo "===> [3/8] Habilitando módulos requeridos do Apache (proxy, proxy_http, ssl, headers, rewrite)..."
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod ssl
sudo a2enmod headers
sudo a2enmod rewrite

# 4. Instalação oficial do Docker e Docker Compose (Requisito 30 e 31)
echo "===> [4/8] Instalando Docker Engine e Docker Compose oficial..."
sudo install -m 0755 -d /etc/apt/keyrings
if [ ! -f /etc/apt/keyrings/docker.asc ]; then
    sudo curl -fsSL https://download.docker.com/linux/debian/gpg -o /etc/apt/keyrings/docker.asc
    sudo chmod a+r /etc/apt/keyrings/docker.asc
fi

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/debian \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

sudo systemctl enable docker
sudo systemctl start docker

# 5. Configuração do Fail2Ban (Requisito 41)
echo "===> [5/8] Configurando Fail2Ban para proteção contra força bruta no SSH..."
if [ -f "$(dirname "$0")/../fail2ban/jail.local" ]; then
    sudo cp "$(dirname "$0")/../fail2ban/jail.local" /etc/fail2ban/jail.local
fi
sudo systemctl enable fail2ban
sudo systemctl restart fail2ban

# 6. Configuração do Firewall UFW (Requisitos 7, 42 e 43)
echo "===> [6/8] Configurando Firewall UFW (22, 80 e 443 liberadas)..."
if [ -f "$(dirname "$0")/../ufw/setup-ufw.sh" ]; then
    bash "$(dirname "$0")/../ufw/setup-ufw.sh"
fi

# 7. Configuração do diretório de implantação /opt/secure-web (Requisito 39)
echo "===> [7/8] Criando diretório de produção /opt/secure-web..."
sudo mkdir -p /opt/secure-web
sudo chown -R "$USER":"$USER" /opt/secure-web

# 8. Validação de Sintaxe do Apache Host
echo "===> [8/8] Validando sintaxe da configuração do Apache..."
sudo apache2ctl configtest

echo "======================================================================"
echo " [OK] Provisionamento concluído com sucesso!"
echo " Próximos passos:"
echo " 1. Clonar o repositório em /opt/secure-web"
echo " 2. Configurar o domínio e SSL com ./deploy/scripts/setup-ssl.sh"
echo " 3. Iniciar contêineres: docker compose up -d"
echo "======================================================================"
