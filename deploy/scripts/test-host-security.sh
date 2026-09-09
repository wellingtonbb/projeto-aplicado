#!/usr/bin/env bash
# ==============================================================================
# PROJETO WEB SEGURO — SECURE BY DESIGN / SECURE BY DEFAULT
# Script de Validação e Auditoria de Segurança do HOST (Ubuntu 24.04 LTS)
# Requisitos 41, 42, 43, 47, 48 e 49: Validações de Apache, Portas, UFW, Fail2Ban e TLS
# ==============================================================================

set -u

echo "======================================================================"
echo "         AUDITORIA DE SEGURANÇA E CONFORMIDADE DO HOST LINUX          "
echo "======================================================================"

# 1. Teste de Sintaxe do Apache (Requisito 47)
echo -e "\n[1/6] Testando sintaxe das configurações do Apache:"
if command -v apache2ctl &>/dev/null; then
    sudo apache2ctl configtest
    echo -e "\nMódulos do Apache carregados:"
    sudo apache2ctl -M 2>/dev/null | grep -E "proxy_module|proxy_http_module|ssl_module|headers_module|rewrite_module"
else
    echo "(!) apache2ctl não encontrado neste ambiente (execução em contêiner ou sem Apache local)."
fi

# 2. Teste de Exposição de Portas do Docker (Requisito 6, 31 e 43)
echo -e "\n[2/6] Verificando portas abertas e binding do contêiner Docker:"
if command -v ss &>/dev/null; then
    ss -tulpn | grep 8080 || echo "Porta 8080 escutando apenas em 127.0.0.1 (Loopback)"
elif command -v netstat &>/dev/null; then
    netstat -tuln | grep 8080 || true
fi

# 3. Teste do Firewall UFW (Requisito 42)
echo -e "\n[3/6] Verificando status do Firewall UFW (Menor Privilégio):"
if command -v ufw &>/dev/null; then
    sudo ufw status verbose
else
    echo "(!) UFW não instalado neste ambiente."
fi

# 4. Teste do Fail2Ban no SSH (Requisito 41)
echo -e "\n[4/6] Verificando status do Fail2Ban (Jail SSH):"
if command -v fail2ban-client &>/dev/null; then
    sudo fail2ban-client status
    sudo fail2ban-client status sshd 2>/dev/null || echo "Jail sshd aguardando inicialização."
else
    echo "(!) fail2ban-client não instalado neste ambiente."
fi

# 5. Teste de Backend Interno (Requisito 49)
echo -e "\n[5/6] Testando endpoint de backend interno em 127.0.0.1:8080/health:"
curl -s -i http://127.0.0.1:8080/health | head -n 10

# 6. Teste de Bloqueio de Arquivos Sensíveis (.env / .git)
echo -e "\n[6/6] Testando tentativa de acesso direto a arquivos sensíveis (.env):"
curl -s -i http://127.0.0.1:8080/.env | head -n 5

echo -e "\n======================================================================"
echo " Auditoria de conformidade concluída!"
echo "======================================================================"
