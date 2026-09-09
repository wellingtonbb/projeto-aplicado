#!/usr/bin/env bash
# ==============================================================================
# PROJETO WEB SEGURO — SECURE BY DESIGN / SECURE BY DEFAULT
# Configuração do Firewall UFW no HOST (Ubuntu 24.04 LTS)
# Requisitos 7, 42 e 43: Permitir somente 22, 80 e 443 / Bloquear 8080 publicamente
# ==============================================================================

set -euo pipefail

echo "===> [UFW] Iniciando configuração de Firewall com Menor Privilégio..."

# 1. Resetar regras do UFW para estado limpo
sudo ufw --force reset

# 2. Política padrão de Menor Privilégio (Default Deny Inbound, Allow Outbound)
sudo ufw default deny incoming
sudo ufw default allow outgoing

# 3. Liberar estritamente as portas autorizadas no Requisito 7
echo "===> [UFW] Liberando porta 22/tcp (SSH)..."
sudo ufw allow 22/tcp comment 'SSH Acesso Seguro'

echo "===> [UFW] Liberando porta 80/tcp (HTTP para redirecionamento 301)..."
sudo ufw allow 80/tcp comment 'HTTP Let Encrypt e Redirecionamento'

echo "===> [UFW] Liberando porta 443/tcp (HTTPS Apache Host)..."
sudo ufw allow 443/tcp comment 'HTTPS Reverse Proxy Seguro'

# 4. Bloqueio explícito preventivo de portas desnecessárias e da porta do contêiner
echo "===> [UFW] Bloqueando expressamente acesso externo à porta 8080..."
sudo ufw deny 8080 comment 'Bloqueio estrito de acesso externo a porta 8080'
sudo ufw deny 3306 comment 'Sem banco de dados'
sudo ufw deny 5432 comment 'Sem banco de dados'
sudo ufw deny 6379 comment 'Sem redis externo'

# 5. Habilitar o Firewall
echo "===> [UFW] Ativando o UFW..."
sudo ufw --force enable

# 6. Exibir status consolidado
echo "===> [UFW] Status das regras ativas:"
sudo ufw status verbose
echo "===> [UFW] Configuração concluída com sucesso!"
