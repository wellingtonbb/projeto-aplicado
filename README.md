# Secure Web Application

![Projeto Aplicado](https://img.shields.io/badge/Projeto_Aplicado-Segurança_Web-blue)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)
![AWS](https://img.shields.io/badge/AWS-EC2-232F3E?logo=amazonaws&logoColor=white)

Aplicação web acadêmica desenvolvida com foco em **Secure by Design** e **Secure by Default**. O projeto combina controles de segurança na aplicação, isolamento com Docker, hardening da infraestrutura e automação de testes e implantação com GitHub Actions.

> **Disciplina:** Projeto Aplicado — Práticas de Mercado  
> **Foco:** Arquitetura de Software, DevSecOps e Segurança em Aplicações Web  
> **Aplicação em produção:** [https://projeto.barreto.uk](https://projeto.barreto.uk)  
> **Repositório:** [github.com/wellingtonbb/projeto-aplicado](https://github.com/wellingtonbb/projeto-aplicado)

## Ambiente de produção

| Item | Configuração documentada |
|---|---|
| Provedor de cloud | Amazon Web Services (AWS) |
| Computação | Instância Amazon EC2 |
| Sistema operacional | Debian 13 |
| Servidor web | Apache 2.4 instalado no host |
| Aplicação | Laravel 12 com PHP 8.4 em contêiner Docker |
| Acesso externo | [https://projeto.barreto.uk](https://projeto.barreto.uk) |
| Código-fonte | [GitHub — projeto-aplicado](https://github.com/wellingtonbb/projeto-aplicado) |
| Porta interna da aplicação | `127.0.0.1:8080` — loopback do host |
| Banco de dados | Não utiliza SGBD; sessões e cache em arquivos |

## Arquitetura

A aplicação segue o princípio de **defesa em profundidade**. O Apache recebe o tráfego externo, trata o HTTPS e encaminha as requisições para o contêiner Laravel. A aplicação não publica sua porta diretamente na interface externa.

```text
Navegador / Internet
        │
        ├── HTTP :80 ── redirecionamento para HTTPS
        │
        ▼
Apache 2.4 no host — Debian 13 em instância AWS EC2
        │  Certbot / TLS
        │  Cabeçalhos de segurança
        │  Reverse proxy
        ▼
127.0.0.1:8080
        │
        ▼
Docker — Laravel 12 / PHP 8.4
        │
        └── Sessões e cache em arquivos; sem SGBD
```

A publicação local em `127.0.0.1:8080` evita que clientes externos contornem o Apache e acessem diretamente a aplicação.

## Stack tecnológica

| Camada | Tecnologias |
|---|---|
| Aplicação web | PHP 8.4, Laravel 12, Blade, Bootstrap 5.3 e Bootstrap Icons |
| Dependências e testes | Composer 2, PHPUnit 11 e Laravel Feature Tests |
| Contêineres | Docker Engine e Docker Compose |
| Web / proxy | Apache 2.4 no host e Apache no contêiner |
| HTTPS | Certbot e Let's Encrypt |
| Hardening do host | UFW, OpenSSH por chave pública e Fail2Ban |
| Desenvolvimento assistido por IA | Google Antigravity |
| Versionamento e entrega | Git, GitHub e GitHub Actions |

## Controles de infraestrutura

| Controle | Implementação descrita no projeto |
|---|---|
| Cloud e sistema operacional | AWS EC2 com Debian 13 |
| Exposição de portas | Entrada prevista para SSH `22`, HTTP `80` e HTTPS `443`; aplicação vinculada ao loopback |
| Administração remota | Chave pública SSH; autenticação por senha desabilitada |
| Proteção de SSH | Fail2Ban configurado para 4 falhas e banimento de 24 horas |
| Firewall | UFW com política de entrada restritiva |
| HTTPS | Certbot/Let's Encrypt e redirecionamento HTTP → HTTPS |
| Cabeçalhos HTTP | HSTS, CSP, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` e `Permissions-Policy` |
| Proteção de arquivos | Regras para negar acesso a arquivos e diretórios sensíveis, como `.env` e `.git` |

**Validação TLS/PQC:** a configuração HTTPS do domínio foi avaliada conforme o escopo da disciplina. O vídeo de apresentação registra o resultado do Qualys SSL Labs, incluindo a nota A e a indicação de suporte a criptografia pós-quântica (PQC).

## Segurança da aplicação e OWASP Top 10:2025

O projeto documenta controles para três categorias da OWASP Top 10:2025 e associa-os a trechos do código e a testes automatizados.

| Categoria | Controles implementados | Evidência no projeto |
|---|---|---|
| **A01 — Broken Access Control** | Middleware de autenticação para proteger o dashboard; logout somente por `POST` e invalidação da sessão. | `app/Http/Middleware/EnsureCustomAuthenticated.php`, `routes/web.php` e testes de acesso/logout |
| **A03 — Injection** | Validação estrita dos dados de entrada; arquitetura sem banco de dados, reduzindo a superfície de SQL Injection. | `app/Http/Controllers/AuthController.php` e `SecurityTest::test_login_input_validation_rules` |
| **A07 — Identification and Authentication Failures** | Senhas verificadas com Bcrypt (custo 12), limite de tentativas, mensagens genéricas e regeneração do identificador da sessão. | `app/Http/Controllers/AuthController.php` e testes de autenticação/rate limit |

Outros controles incluem proteção CSRF, escape de saída do Blade para reduzir risco de XSS, cookies de sessão com atributos de segurança, endpoint de integridade sem detalhes sensíveis e auditoria de dependências no pipeline.

## Funcionalidades

- Tela de login com validação de credenciais.
- Dashboard restrito a usuários autenticados.
- Logout por requisição `POST`, com invalidação da sessão.
- Endpoint de integridade `GET /health`, com resposta de status sem expor informações sensíveis.
- Pipeline para executar testes, auditoria de dependências e implantação.

## Executar localmente

### Pré-requisitos

- Git
- Docker Engine
- Docker Compose Plugin

### Inicialização

```bash
git clone https://github.com/wellingtonbb/projeto-aplicado.git
cd projeto-aplicado
cp .env.example .env
```

Confira `.env.example` e preencha apenas as variáveis necessárias ao ambiente local. Não reutilize segredos de produção e não versione `.env`.

```bash
docker compose build
docker compose up -d
docker compose ps
```

Valide a integridade pela interface local do host:

```bash
curl -i http://127.0.0.1:8080/health
```

A execução local pressupõe que Docker e Compose estejam instalados e que o arquivo de ambiente esteja configurado conforme as necessidades da aplicação.

## Testes e verificações

A suíte automatizada registra **14 testes aprovados e 51 assertions**, cobrindo os principais fluxos de autenticação, controle de acesso e proteções da aplicação.

Com PHP, Composer e as dependências do projeto instaladas no ambiente de desenvolvimento:

```bash
php artisan test
composer audit
```

O pipeline contempla testes, auditoria de dependências e verificações de segurança. O resultado da execução automatizada e do deploy é apresentado no vídeo de demonstração.

## CI/CD com GitHub Actions

O workflow está em `.github/workflows/deploy.yml` e, conforme a configuração documentada, é acionado por `push` na branch `main`.

```text
git push origin main
        │
        ▼
GitHub Actions
        │
        ├── Testes automatizados
        ├── Auditoria de dependências e verificação de segredos
        └── Deploy por SSH
                 │
                 ▼
       AWS EC2 — Debian 13 / Docker Compose
                 │
                 └── Verificação do endpoint /health
```

As credenciais de implantação são armazenadas em **GitHub Actions Secrets**, fora do código-fonte. A execução do workflow, incluindo validação e implantação, é demonstrada no vídeo de apresentação.

## Desenvolvimento assistido por IA

O projeto foi desenvolvido com apoio do **Google Antigravity**, ambiente de desenvolvimento assistido por inteligência artificial. A ferramenta integrou o processo de desenvolvimento como apoio à criação e evolução do código. As funcionalidades foram verificadas por meio dos testes automatizados e da execução da aplicação, conforme os procedimentos descritos neste README.

## Requisitos do projeto e validação

O projeto foi organizado conforme os três eixos do documento **Escopo e Elementos Obrigatórios — versão 1.0**. As evidências práticas de execução e validação são apresentadas no vídeo de demonstração do Projeto Aplicado.

### Infraestrutura e cloud

- Aplicação hospedada na **AWS**, em uma instância **Amazon EC2** com **Debian 13**, utilizando Apache 2.4 no host e Docker para executar o Laravel.
- Porta interna da aplicação vinculada exclusivamente a `127.0.0.1:8080`.
- Administração remota protegida por chave SSH, firewall UFW e Fail2Ban.
- HTTPS com Certbot/Let's Encrypt e redirecionamento automático de HTTP para HTTPS.
- Validação externa TLS/PQC demonstrada no vídeo, incluindo o resultado do Qualys SSL Labs e o suporte a PQC exigido para acesso por domínio.

### Repositório e proteção de segredos

- Código-fonte versionado em repositório público no [GitHub](https://github.com/wellingtonbb/projeto-aplicado).
- `.gitignore` configurado para excluir arquivos de ambiente, credenciais, chaves privadas, logs e artefatos locais.
- Variáveis de produção e credenciais de implantação mantidas fora do código-fonte; os segredos usados pelo workflow ficam em GitHub Actions Secrets.

### Aplicação segura

- Tela de login, dashboard protegido e logout seguro.
- Controles documentados para as categorias **A01 — Broken Access Control**, **A03 — Injection** e **A07 — Identification and Authentication Failures** da OWASP Top 10:2025.
- Proteções CSRF, validação de entradas, escape de saída, limitação de tentativas de autenticação e configuração segura de sessão.
- Desenvolvimento assistido por inteligência artificial com **Google Antigravity**, apresentado na demonstração do projeto.

### Integração e entrega contínuas (CI/CD)

- GitHub Actions para executar testes, auditoria de dependências e implantação por SSH após atualização da branch `main`.
- O vídeo de apresentação demonstra a execução bem-sucedida da validação automatizada e do deploy no ambiente de produção.

## Conclusão

O **Secure Web Application** demonstra a aplicação prática de princípios de desenvolvimento seguro e DevSecOps em uma aplicação Laravel conteinerizada. A solução está hospedada na **AWS, utilizando uma instância Amazon EC2**, e integra controles na aplicação, isolamento de rede, hardening do host e automação de testes e implantação.

As evidências de funcionamento da aplicação, das verificações externas de TLS/PQC, do desenvolvimento assistido por Google Antigravity e da execução do pipeline CI/CD compõem a apresentação em vídeo do Projeto Aplicado.
