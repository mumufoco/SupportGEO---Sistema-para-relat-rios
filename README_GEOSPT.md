# 🌍 GeoSPT Manager

Sistema completo de gestão de sondagens SPT conforme **NBR 6484:2020**

[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-red.svg)](https://codeigniter.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-blue.svg)](https://postgresql.org)
[![Status](https://img.shields.io/badge/Status-Produção-green.svg)]()

## 📋 Sobre

**GeoSPT Manager** é um sistema web completo para gestão de sondagens de **Standard Penetration Test (SPT)**, desenvolvido para empresas de sondagem e geotecnia. O sistema garante conformidade total com a norma **NBR 6484:2020**.

### 🎯 Principais Funcionalidades

- ✅ Cadastro completo de sondagens SPT
- ✅ Cálculo automático de N30
- ✅ Classificação de solos conforme NBR
- ✅ Geração automática de PDF profissional
- ✅ Upload de fotos com extração GPS/EXIF
- ✅ Importação em massa via Excel/CSV
- ✅ Dashboard com estatísticas
- ✅ Score de conformidade NBR
- ✅ Multi-empresa e multi-projeto
- ✅ Auditoria completa de ações

## 🚀 Início Rápido

### Credenciais de Teste

```
URL: http://localhost:8080/login
E-mail: admin@supportsolosondagens.com.br
Senha: password
```

### Requisitos

- PHP 8.1 ou superior
- PostgreSQL 15+ (Supabase configurado)
- Extensões PHP:
  - pgsql
  - mbstring
  - xml
  - curl
  - gd

### Instalação

1. **Clone o repositório**
```bash
git clone <repo-url>
cd project
```

2. **Instale as dependências**
```bash
composer install
```

3. **Configure o ambiente**
```bash
# O arquivo .env já está configurado com Supabase
# Verifique as credenciais em .env
```

4. **Inicie o servidor**
```bash
php spark serve
```

5. **Acesse o sistema**
```
http://localhost:8080
```

## 📚 Documentação

- **[SISTEMA_COMPLETO.md](SISTEMA_COMPLETO.md)** - Documentação completa do sistema
- **[STATUS_INTERFACE.md](STATUS_INTERFACE.md)** - Status da implementação
- **[CREDENCIAIS_TESTE.md](CREDENCIAIS_TESTE.md)** - Credenciais e dados de teste
- **[Plano de Elaboração/](Plano%20de%20Elaboração/)** - Documentação técnica detalhada

## 🏗️ Arquitetura

```
┌─────────────────────┐
│   Interface Web     │  ← Bootstrap 5 + DataTables
├─────────────────────┤
│   Web Controllers   │  ← Admin + Auth
├─────────────────────┤
│   API REST          │  ← 35+ endpoints
├─────────────────────┤
│   Repositories      │  ← Camada de dados
├─────────────────────┤
│   Models            │  ← ORM CodeIgniter
├─────────────────────┤
│   Services/Libs     │  ← NBR + PDF + EXIF
├─────────────────────┤
│   Supabase          │  ← PostgreSQL + RLS
└─────────────────────┘
```

## 🎨 Interface

### Dashboard
- Estatísticas em tempo real
- Gráficos de sondagens
- Ações rápidas
- Score de conformidade

### Cadastro de Sondagens
- Formulário completo NBR
- Múltiplas amostras SPT
- Cálculo automático N30
- Validações em tempo real

### Geração de PDF
- Perfil estratigráfico
- Tabela de SPT detalhada
- Logo da empresa
- Assinatura digital

## 🔐 Segurança

- ✅ Row Level Security (RLS) habilitado
- ✅ Autenticação com sessões seguras
- ✅ Middleware de proteção de rotas
- ✅ Password hash (bcrypt)
- ✅ JWT para API
- ✅ Validação de entrada
- ✅ CSRF protection

## 📊 Tecnologias

### Backend
- **PHP 8.1+**
- **CodeIgniter 4**
- **PostgreSQL** (Supabase)
- **DomPDF** (geração PDF)
- **PHPSpreadsheet** (Excel)

### Frontend
- **Bootstrap 5.3**
- **jQuery 3.7**
- **DataTables**
- **Bootstrap Icons**
- **JavaScript ES6+**

## 🗄️ Banco de Dados

### Tabelas (11)
- empresas
- usuarios
- responsaveis_tecnicos
- projetos
- obras
- sondagens
- amostras
- camadas
- fotos
- audit_log
- ci_sessions

### Relacionamentos
```
Empresa → Projetos → Obras → Sondagens
Sondagem → Amostras + Camadas + Fotos
Usuario → Empresa + Responsável Técnico
```

## 🌐 API REST

### Endpoints Principais

```bash
# Autenticação
POST /api/auth/login
GET  /api/auth/me

# Sondagens
GET    /api/sondagens
POST   /api/sondagens
GET    /api/sondagens/:id
PUT    /api/sondagens/:id
DELETE /api/sondagens/:id

# PDF
GET /api/pdf/preview/:id
GET /api/pdf/download/:id

# Fotos
POST /api/sondagens/:id/fotos
GET  /api/fotos/:id

# Importação
POST /api/import/excel
GET  /api/import/template
```

Veja **[11_DOCUMENTACAO_API.md](Plano%20de%20Elaboração/11_DOCUMENTACAO_API.md)** para documentação completa.

## 📝 NBR 6484:2020

O sistema implementa todos os requisitos da norma:

- ✅ Equipamento conforme (65 kgf, 75 cm)
- ✅ Procedimento de ensaio validado
- ✅ Cálculo correto de N30
- ✅ Registro de paralisações
- ✅ Nível d'água
- ✅ Classificação táctil-visual
- ✅ Perfil estratigráfico
- ✅ Relatório técnico completo

## 🧪 Testes

```bash
# Executar testes
composer test

# Testes específicos
vendor/bin/phpunit tests/unit/
```

## 📦 Deploy

### Requisitos de Produção
- PHP 8.1+ com extensões necessárias
- Servidor web (Apache/Nginx)
- PostgreSQL 15+
- SSL/HTTPS configurado

### Passos
1. Configure o `.env` para produção
2. Defina `CI_ENVIRONMENT = production`
3. Altere credenciais padrão
4. Configure backup automático
5. Ative logs de auditoria

## 🤝 Contribuindo

Este é um projeto privado para Support Solo Sondagens Ltda.

## 📄 Licença

Proprietário - Support Solo Sondagens Ltda © 2025

## 👥 Equipe

**Desenvolvido para:** Support Solo Sondagens Ltda
**Versão:** 1.0.0
**Data:** Novembro 2025

## 📞 Suporte

Para suporte técnico:
- E-mail: suporte@supportsolosondagens.com.br
- Documentação: Ver arquivos .md na raiz do projeto

---

## 🎯 Status do Projeto

| Fase | Status | Descrição |
|------|--------|-----------|
| Fase 0 | ✅ 100% | Preparação |
| Fase 1 | ✅ 100% | Banco Supabase |
| Fase 2 | ✅ 100% | Models/Repositories |
| Fase 3 | ✅ 100% | Bibliotecas NBR |
| Fase 4 | ✅ 100% | Geração PDF |
| Fase 5 | ✅ 100% | API REST |
| Fase 6 | ✅ 100% | Interface Web |
| Fase 7 | ✅ 100% | Upload/Import |
| Fase 8 | ✅ 100% | Autenticação |

**Status Geral: ✅ 100% COMPLETO**

---

**Desenvolvido com ❤️ para a geotecnia brasileira**
