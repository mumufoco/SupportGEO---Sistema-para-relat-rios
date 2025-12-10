# DOCUMENTAÇÃO DA API REST - GeoSPT Manager

## Visão Geral

Base URL: `https://geospt.supportsondagens.com.br/api`

Todas as requisições (exceto login) requerem autenticação JWT via header:
```
Authorization: Bearer {token}
```

---

## Autenticação

### Login

```http
POST /auth/login
Content-Type: application/json

{
  "email": "usuario@email.com",
  "password": "senha123"
}
```

**Response 200:**
```json
{
  "sucesso": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "usuario": {
    "id": 1,
    "nome": "Administrador",
    "email": "admin@supportsondagens.com.br",
    "tipo": "admin"
  },
  "expira_em": 28800
}
```

### Refresh Token

```http
POST /auth/refresh
Authorization: Bearer {token}
```

---

## Sondagens

### Listar Sondagens

```http
GET /api/sondagens
```

**Query Parameters:**
| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| page | int | Página atual (default: 1) |
| per_page | int | Itens por página (default: 20) |
| obra_id | int | Filtrar por obra |
| status | string | rascunho, em_analise, aprovado, rejeitado |
| data_inicio | date | Data inicial (YYYY-MM-DD) |
| data_fim | date | Data final (YYYY-MM-DD) |
| busca | string | Buscar por código |

**Response 200:**
```json
{
  "sucesso": true,
  "dados": [
    {
      "id": 1,
      "codigo_sondagem": "SP-01",
      "data_execucao": "2025-08-17",
      "status": "aprovado",
      "profundidade_final": 12.45
    }
  ],
  "paginacao": {
    "total": 50,
    "pagina_atual": 1,
    "por_pagina": 20,
    "total_paginas": 3
  }
}
```

### Obter Sondagem

```http
GET /api/sondagens/{id}
```

**Response 200:**
```json
{
  "sucesso": true,
  "dados": {
    "sondagem": {
      "id": 1,
      "codigo_sondagem": "SP-01",
      "data_execucao": "2025-08-17",
      "sondador": "Henrique Luiz da Silva",
      "coordenada_este": 487801.00,
      "coordenada_norte": 7666164.00,
      "profundidade_final": 12.45,
      "peso_martelo": 65.00,
      "altura_queda": 75.00
    },
    "obra": {...},
    "projeto": {...},
    "empresa": {...},
    "responsavel": {...},
    "camadas": [...],
    "amostras": [...],
    "fotos": [...],
    "estatisticas": {
      "n30_maximo": 53,
      "n30_minimo": 4,
      "n30_medio": 18.5
    },
    "conformidade": {
      "conforme": true,
      "score": 100
    }
  }
}
```

### Criar Sondagem

```http
POST /api/sondagens
Content-Type: application/json

{
  "obra_id": 1,
  "codigo_sondagem": "SP-02",
  "data_execucao": "2025-08-18",
  "sondador": "João Silva",
  "coordenada_este": 487850.00,
  "coordenada_norte": 7666200.00,
  "profundidade_final": 10.00,
  "identificacao_cliente": "Araxá Eng."
}
```

**Response 201:**
```json
{
  "sucesso": true,
  "mensagem": "Sondagem criada com sucesso",
  "dados": {...}
}
```

### Atualizar Sondagem

```http
PUT /api/sondagens/{id}
Content-Type: application/json

{
  "profundidade_final": 15.00,
  "observacoes_paralisacao": "Atualizado"
}
```

### Excluir Sondagem

```http
DELETE /api/sondagens/{id}
```

### Verificar Conformidade NBR

```http
GET /api/sondagens/{id}/conformidade
```

**Response 200:**
```json
{
  "sucesso": true,
  "sondagem_id": 1,
  "codigo": "SP-01",
  "conformidade": {
    "conforme": true,
    "score": 100,
    "erros": [],
    "avisos": [],
    "total_erros": 0,
    "total_avisos": 0
  }
}
```

### Aprovar Sondagem

```http
POST /api/sondagens/{id}/aprovar
```

### Rejeitar Sondagem

```http
POST /api/sondagens/{id}/rejeitar
Content-Type: application/json

{
  "motivo": "Faltam fotos obrigatórias"
}
```

---

## Amostras

### Listar Amostras de Sondagem

```http
GET /api/sondagens/{id}/amostras
```

**Response 200:**
```json
{
  "sucesso": true,
  "sondagem_id": 1,
  "total": 13,
  "dados": [
    {
      "id": 1,
      "numero_amostra": 1,
      "tipo_perfuracao": "TH",
      "profundidade_inicial": 0.00,
      "golpes_1a": null,
      "golpes_2a": null,
      "golpes_3a": null,
      "nspt_1a_2a": null,
      "nspt_2a_3a": null
    },
    {
      "id": 2,
      "numero_amostra": 2,
      "tipo_perfuracao": "CR",
      "profundidade_inicial": 1.00,
      "golpes_1a": 5,
      "golpes_2a": 4,
      "golpes_3a": 2,
      "nspt_1a_2a": 9,
      "nspt_2a_3a": 6
    }
  ]
}
```

### Criar Amostra

```http
POST /api/sondagens/{id}/amostras
Content-Type: application/json

{
  "numero_amostra": 3,
  "tipo_perfuracao": "CR",
  "profundidade_inicial": 2.00,
  "golpes_1a": 4,
  "golpes_2a": 5,
  "golpes_3a": 6
}
```

### Criar Amostras em Lote

```http
POST /api/sondagens/{id}/amostras/batch
Content-Type: application/json

{
  "amostras": [
    {
      "numero_amostra": 4,
      "tipo_perfuracao": "CR",
      "profundidade_inicial": 3.00,
      "golpes_1a": 5,
      "golpes_2a": 5,
      "golpes_3a": 4
    },
    {
      "numero_amostra": 5,
      "tipo_perfuracao": "CR",
      "profundidade_inicial": 4.00,
      "golpes_1a": 6,
      "golpes_2a": 6,
      "golpes_3a": 6
    }
  ]
}
```

---

## Camadas

### Listar Camadas de Sondagem

```http
GET /api/sondagens/{id}/camadas
```

### Criar Camada

```http
POST /api/sondagens/{id}/camadas
Content-Type: application/json

{
  "numero_camada": 1,
  "profundidade_inicial": 0.00,
  "profundidade_final": 1.00,
  "classificacao_principal": "vegetacao",
  "descricao_completa": "Vegetação, cor verde clara (Expurgo).",
  "cor": "verde clara",
  "origem": "SR"
}
```

---

## Fotos

### Listar Fotos de Sondagem

```http
GET /api/sondagens/{id}/fotos
```

### Upload de Fotos

```http
POST /api/sondagens/{id}/fotos
Content-Type: multipart/form-data

fotos[]: arquivo1.jpg
fotos[]: arquivo2.jpg
tipo_foto: ensaio_spt
```

**Response 201:**
```json
{
  "sucesso": true,
  "mensagem": "3 foto(s) enviada(s) com sucesso",
  "fotos": [
    {
      "id": 1,
      "arquivo": "abc123.jpg",
      "tipo_foto": "ensaio_spt",
      "latitude": -21.123456,
      "longitude": -45.654321,
      "altitude": 820.1,
      "data_hora_exif": "2025-08-17 08:47:23",
      "coordenada_este": 487805.00,
      "coordenada_norte": 7666179.00,
      "zona_utm": "23K"
    }
  ],
  "erros": []
}
```

### Excluir Foto

```http
DELETE /api/fotos/{id}
```

---

## Relatórios

### Gerar PDF

```http
GET /api/reports/sondagem/{id}/pdf
```

**Response:** Arquivo PDF para download

### Verificar Conformidade

```http
GET /api/reports/sondagem/{id}/conformidade
```

### Gerar PDFs em Lote

```http
POST /api/reports/sondagens/batch
Content-Type: application/json

{
  "ids": [1, 2, 3]
}
```

**Response 200:**
```json
{
  "sucesso": true,
  "estatisticas": {
    "total": 3,
    "sucesso": 3,
    "falhas": 0
  },
  "resultados": [
    {"id": 1, "sucesso": true, "arquivo": "SPT_SP-01_20250817.pdf"},
    {"id": 2, "sucesso": true, "arquivo": "SPT_SP-02_20250817.pdf"},
    {"id": 3, "sucesso": true, "arquivo": "SPT_SP-03_20250817.pdf"}
  ]
}
```

---

## Importação

### Importar Excel

```http
POST /api/import/excel
Content-Type: multipart/form-data

arquivo: planilha.xlsx
obra_id: 1
```

**Response 200:**
```json
{
  "sucesso": true,
  "mensagem": "Importação concluída: 5 sondagens, 50 amostras",
  "detalhes": {
    "sondagens_criadas": 5,
    "amostras_criadas": 50,
    "erros": [],
    "avisos": []
  }
}
```

### Baixar Template

```http
GET /api/import/template
```

**Response:** Arquivo Excel (.xlsx) para download

---

## Health Check

```http
GET /api/health
```

**Response 200:**
```json
{
  "status": "ok",
  "timestamp": "2025-08-17 10:30:00",
  "version": "1.0.0",
  "services": {
    "database": "ok",
    "uploads": "ok",
    "logs": "ok"
  }
}
```

---

## Códigos de Erro

| Código | Descrição |
|--------|-----------|
| 200 | Sucesso |
| 201 | Criado com sucesso |
| 400 | Requisição inválida |
| 401 | Não autenticado |
| 403 | Sem permissão |
| 404 | Não encontrado |
| 422 | Erro de validação |
| 500 | Erro interno |

**Formato de erro:**
```json
{
  "erro": "Mensagem de erro",
  "mensagem": "Detalhes adicionais",
  "erros": {
    "campo": "Mensagem de validação"
  }
}
```

---

## Rate Limiting

- 100 requisições por minuto por IP
- 1000 requisições por hora por usuário autenticado

---

## Exemplos cURL

### Login
```bash
curl -X POST https://geospt.supportsondagens.com.br/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@supportsondagens.com.br","password":"admin123"}'
```

### Listar Sondagens
```bash
curl -X GET https://geospt.supportsondagens.com.br/api/sondagens \
  -H "Authorization: Bearer {token}"
```

### Criar Sondagem
```bash
curl -X POST https://geospt.supportsondagens.com.br/api/sondagens \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "obra_id": 1,
    "codigo_sondagem": "SP-02",
    "data_execucao": "2025-08-18",
    "sondador": "João Silva",
    "coordenada_este": 487850.00,
    "coordenada_norte": 7666200.00,
    "profundidade_final": 10.00
  }'
```

### Gerar PDF
```bash
curl -X GET https://geospt.supportsondagens.com.br/api/reports/sondagem/1/pdf \
  -H "Authorization: Bearer {token}" \
  -o relatorio_sp01.pdf
```

---

**© 2025 Support Solo Sondagens Ltda**
