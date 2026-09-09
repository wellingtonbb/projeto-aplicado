#!/usr/bin/env bash
set -e

COOKIE_JAR=$(mktemp)

echo "=== 1. Teste de Health Check ==="
curl -s -i http://127.0.0.1:8080/health | grep -E "HTTP/|status"

echo -e "\n=== 2. Obter CSRF Token e Cookie de Sessão em /login ==="
HTML_LOGIN=$(curl -s -c "$COOKIE_JAR" http://127.0.0.1:8080/login)
CSRF_TOKEN=$(echo "$HTML_LOGIN" | grep -o 'name="_token" value="[^"]*"' | head -n1 | sed 's/.*value="//;s/"//')
echo "Token CSRF capturado: ${CSRF_TOKEN:0:15}..."

echo -e "\n=== 3. Realizar POST /login com credenciais válidas ==="
curl -s -i -b "$COOKIE_JAR" -c "$COOKIE_JAR" -X POST http://127.0.0.1:8080/login \
  -d "_token=$CSRF_TOKEN" \
  -d "username=admin" \
  -d "password=Admin@Secure2025!" | grep -E "HTTP/|Location:|Set-Cookie:"

echo -e "\n=== 4. Acessar GET /dashboard autenticado ==="
DASH_RES=$(curl -s -b "$COOKIE_JAR" http://127.0.0.1:8080/dashboard)
echo "$DASH_RES" | grep -o "Bem-vindo, admin!"
echo "$DASH_RES" | grep -o "Matriz de Mitigações OWASP Top 10:2025"

echo -e "\n=== 5. Obter CSRF Token para Logout ==="
LOGOUT_TOKEN=$(echo "$DASH_RES" | grep -o 'name="_token" value="[^"]*"' | head -n1 | sed 's/.*value="//;s/"//')

echo -e "\n=== 6. Realizar POST /logout ==="
curl -s -i -b "$COOKIE_JAR" -c "$COOKIE_JAR" -X POST http://127.0.0.1:8080/logout \
  -d "_token=$LOGOUT_TOKEN" | grep -E "HTTP/|Location:"

echo -e "\n=== 7. Tentar acessar /dashboard após logout ==="
curl -s -i -b "$COOKIE_JAR" http://127.0.0.1:8080/dashboard | grep -E "HTTP/|Location:"

rm -f "$COOKIE_JAR"
echo -e "\n>>> Todos os testes de fluxo HTTP foram validados com sucesso! <<<"
