# Desafio API de Assinaturas (Laravel)

Este é um projeto de **back-end** desenvolvido em **Laravel** que simula um sistema de gerenciamento de assinaturas de planos.  
A API permite **contratar planos**, **trocar de plano com cálculo de crédito (proration)** e mantém um **histórico de pagamentos simulados (PIX)**.  

Este projeto foi desenvolvido como parte de um **desafio técnico**.

---

## Sumário

- [Requisitos](#requisitos)
- [Como Executar o Projeto](#como-executar-o-projeto)
  - [1. Clonar o Repositório](#1-clonar-o-repositório)
  - [2. Instalar Dependências](#2-instalar-dependências)
  - [3. Configurar Ambiente](#3-configurar-ambiente)
  - [4. Gerar Chave da Aplicação](#4-gerar-chave-da-aplicação)
  - [5. Criar o Banco de Dados (SQLite)](#5-criar-o-banco-de-dados-sqlite)
  - [6. Rodar as Migrations](#6-rodar-as-migrations)
  - [7. Popular o Banco (Seeders)](#7-popular-o-banco-seeders)
  - [8. Iniciar o Servidor](#8-iniciar-o-servidor)
- [Documentação da API](#documentação-da-api)
  - [1. Informações do Usuário](#1-informações-do-usuário)
  - [2. Listar Planos](#2-listar-planos)
  - [3. Ver Contrato Ativo](#3-ver-contrato-ativo)
  - [4. Contratar um Plano](#4-contratar-um-plano)
  - [5. Trocar de Plano](#5-trocar-de-plano)

---

## Requisitos

- **PHP** (versão 8.3+)
- **Composer** (versão 2+)
- Um ambiente de desenvolvimento local (recomenda-se **Laragon** para Windows)
- Uma ferramenta de teste de API (como **Postman**)

---

## Como Executar o Projeto

Siga os passos abaixo para configurar e rodar o projeto localmente.

### 1. Clonar o Repositório

Clone este repositório para sua máquina local (recomenda-se a pasta `www` do Laragon).  
Lembre-se de trocar `[SEU_USUARIO_GITHUB]` pelo seu nome de usuário.

```bash
git clone https://github.com/[SEU_USUARIO_GITHUB]/desafio-api.git
cd desafio-api
```

---

### 2. Instalar Dependências

Instale as dependências do PHP usando o Composer:

```bash
composer install
```

---

### 3. Configurar Ambiente

O Laravel usa um arquivo `.env` para configurações.  
O projeto já vem configurado para usar **SQLite** por padrão.

Basta copiar o arquivo de exemplo:

```bash
copy .env.example .env
```

---

### 4. Gerar Chave da Aplicação

Gere a chave de segurança única do Laravel:

```bash
php artisan key:generate
```

---

### 5. Criar o Banco de Dados (SQLite)

Crie o arquivo de banco de dados SQLite vazio na pasta `database`:

```bash
touch database/database.sqlite
```

---

### 6. Rodar as Migrations

Execute as migrations para criar as tabelas:

```bash
php artisan migrate
```

---

### 7. Popular o Banco (Seeders)

Popule o banco com dados de exemplo (usuário fixo e 3 planos):

```bash
php artisan db:seed
```

---

### 8. Iniciar o Servidor

Inicie o servidor de desenvolvimento do Laravel:

```bash
php artisan serve
```

A API estará disponível em:  
**http://127.0.0.1:8000**

---

## Documentação da API

**Base URL:** `http://127.0.0.1:8000/api`  
**Autenticação:** não é necessária (usuário fixo ID 1).

---

### 1. Informações do Usuário

**Método:** `GET`  
**Endpoint:** `/user`

Retorna as informações do usuário fixo.

**Exemplo de Resposta (200 OK):**
```json
{
  "id": 1,
  "name": "Usuário Fixo",
  "email": "usuario@fixo.com",
  "email_verified_at": null,
  "created_at": "2025-10-23T02:30:00.000000Z",
  "updated_at": "2025-10-23T02:30:00.000000Z"
}
```

---

### 2. Listar Planos

**Método:** `GET`  
**Endpoint:** `/plans`

Retorna todos os planos disponíveis.

**Exemplo de Resposta (200 OK):**
```json
[
  {
    "id": 1,
    "name": "Plano Básico",
    "price": "50.00",
    "quotas": 10,
    "storage_limit_gb": 20
  },
  {
    "id": 2,
    "name": "Plano Pro",
    "price": "100.00",
    "quotas": 50,
    "storage_limit_gb": 100
  },
  {
    "id": 3,
    "name": "Plano Mega",
    "price": "200.00",
    "quotas": 200,
    "storage_limit_gb": 500
  }
]
```

---

### 3. Ver Contrato Ativo

**Método:** `GET`  
**Endpoint:** `/subscription`

Retorna o plano atualmente contratado.

**Exemplo de Resposta (200 OK):**
```json
{
  "id": 1,
  "user_id": 1,
  "plan_id": 2,
  "original_subscription_date": "2025-10-23",
  "current_plan_starts_at": "2025-10-23",
  "next_billing_date": "2025-11-23",
  "is_active": 1,
  "plan": {
    "id": 2,
    "name": "Plano Pro",
    "price": "100.00"
  }
}
```

**Exemplo (404 Not Found):**
```json
{
  "message": "Nenhum plano contratado."
}
```

---

### 4. Contratar um Plano

**Método:** `POST`  
**Endpoint:** `/subscribe`

Permite a contratação de um plano com pagamento simulado (PIX).

**Parâmetros (form-data):**
| Key | Obrigatório | Descrição |
|-----|--------------|------------|
| plan_id | Sim | ID do plano a contratar |

**Exemplo de Requisição:**
```
POST /api/subscribe
plan_id=2
```

**Exemplo de Resposta (201 Created):**
```json
{
  "id": 1,
  "user_id": 1,
  "plan_id": 2,
  "is_active": true,
  "plan": {
    "id": 2,
    "name": "Plano Pro"
  }
}
```

**Exemplo (400 Bad Request):**
```json
{
  "message": "Usuário já possui um plano ativo. Use a rota /api/switch-plan para trocar."
}
```

---

### 5. Trocar de Plano

**Método:** `POST`  
**Endpoint:** `/switch-plan`

Permite trocar o plano ativo com cálculo proporcional (crédito pelos dias restantes).

**Parâmetros (form-data):**
| Key | Obrigatório | Descrição |
|-----|--------------|------------|
| new_plan_id | Sim | ID do novo plano |

**Exemplo de Requisição:**
```
POST /api/switch-plan
new_plan_id=3
```

**Exemplo de Resposta (200 OK):**
```json
{
  "id": 1,
  "user_id": 1,
  "plan_id": 3,
  "original_subscription_date": "2025-10-23",
  "current_plan_starts_at": "2025-10-24",
  "next_billing_date": "2025-11-23",
  "is_active": 1,
  "plan": {
    "id": 3,
    "name": "Plano Mega"
  }
}
```