# 🚀 Como Usar o GeoSPT Manager - Guia Rápido

## 🎯 Acesso Rápido

1. **Abra seu navegador**
2. Acesse: `http://localhost:8080`
3. Faça login com:
   - **E-mail:** admin@supportsolosondagens.com.br
   - **Senha:** password

---

## 📝 Fluxo Básico de Uso

### 1️⃣ Login
![Login](docs/login.png)

1. Acesse `/login`
2. Digite e-mail e senha
3. Clique em "Entrar"
4. Você será redirecionado ao Dashboard

### 2️⃣ Dashboard
![Dashboard](docs/dashboard.png)

**O que você vê:**
- Total de sondagens cadastradas
- Sondagens aprovadas
- Sondagens pendentes
- Obras ativas
- Últimas sondagens
- Score de conformidade NBR

**Ações rápidas:**
- Nova Sondagem
- Importar Excel
- Baixar Template
- Ver Relatórios

### 3️⃣ Criar Nova Sondagem

#### Passo a Passo:

1. **Clique em "Nova Sondagem"**

2. **Preencha os Dados Básicos:**
   - Código da Sondagem (ex: SP-01)
   - Obra (selecione da lista)
   - Data de Execução
   - Nome do Sondador
   - Identificação do Cliente (opcional)

3. **Coordenadas e Cotas:**
   - Coordenada Este (UTM)
   - Coordenada Norte (UTM)
   - Cota da Boca do Furo
   - Profundidade Final
   - Profundidade do Trado
   - Revestimento

4. **Nível d'Água:**
   - Selecione "Ausente" ou "Presente"
   - Se presente, informe a profundidade

5. **Adicionar Amostras SPT:**
   - Clique em "+ Adicionar"
   - Preencha para cada amostra:
     - Tipo (CR ou TH)
     - Profundidade inicial
     - Golpes 1ª penetração
     - Golpes 2ª penetração
     - Golpes 3ª penetração
   - O N30 é calculado automaticamente
   - Adicione quantas amostras forem necessárias

6. **Equipamento NBR:**
   - Já vem pré-configurado conforme NBR 6484:2020
   - Peso: 65 kgf
   - Altura: 75 cm
   - Sistema: Manual ou Mecânico

7. **Observações:**
   - Adicione observações gerais
   - Motivo de paralisação (se houver)

8. **Salvar:**
   - Clique em "Salvar Sondagem"
   - Aguarde confirmação
   - Você será redirecionado para a listagem

### 4️⃣ Visualizar Sondagens

1. **Acesse "Sondagens" no menu**

2. **Use os filtros:**
   - Todos os Status
   - Rascunho
   - Em Análise
   - Aprovado
   - Rejeitado

3. **Buscar:**
   - Use o campo de busca do DataTables
   - Digite código, obra, projeto, etc.

4. **Ações por sondagem:**
   - 👁️ **Ver** - Visualizar detalhes
   - ✏️ **Editar** - Modificar dados
   - 📄 **PDF** - Gerar relatório
   - 🗑️ **Excluir** - Remover sondagem

### 5️⃣ Gerar PDF

**Opção 1: Da listagem**
1. Localize a sondagem
2. Clique no botão PDF (verde)
3. O PDF será aberto em nova aba

**Opção 2: Da visualização**
1. Entre na sondagem
2. Clique em "Gerar PDF"
3. O PDF será aberto

**O PDF contém:**
- Dados da empresa
- Dados da obra
- Localização (coordenadas)
- Perfil estratigráfico
- Tabela de SPT detalhada
- Classificação de solos
- Observações
- Assinatura do responsável técnico

### 6️⃣ Upload de Fotos

1. **Entre na sondagem**
2. Clique em "Adicionar Fotos"
3. Selecione as fotos
4. O sistema extrai automaticamente:
   - Data/hora
   - GPS (latitude/longitude)
   - Altitude
   - Converte para UTM
5. As fotos aparecem na visualização

### 7️⃣ Importar Excel

#### Preparar o arquivo:

1. **Baixe o template:**
   - Clique em "Template Excel" no menu
   - Um arquivo .xlsx será baixado

2. **Preencha o Excel:**
   - Siga exatamente a estrutura
   - Uma linha = uma sondagem
   - Colunas obrigatórias:
     - codigo_sondagem
     - data_execucao
     - coordenada_este
     - coordenada_norte
     - profundidade_final
     - obra_id

3. **Importar:**
   - Menu → Importar Excel
   - Selecione o arquivo
   - Clique em Upload
   - Aguarde processamento
   - Veja o resultado

### 8️⃣ Logout

1. Clique no seu nome no canto superior direito
2. Selecione "Sair"
3. Você será redirecionado ao login

---

## 🔍 Funcionalidades Avançadas

### Aprovar/Rejeitar Sondagem

```bash
# Via API (para engenheiros)
POST /api/sondagens/:id/aprovar
POST /api/sondagens/:id/rejeitar
```

### Duplicar Sondagem

Útil para criar sondagens semelhantes:

```bash
POST /api/sondagens/:id/duplicar
```

### Validar Conformidade NBR

```bash
GET /api/sondagens/:id/validar
GET /api/sondagens/:id/conformidade
```

### Buscar por Coordenadas

Encontre sondagens próximas:

```bash
GET /api/sondagens?este=487801&norte=7666164&raio=1000
```

---

## 💡 Dicas e Truques

### 1. Atalhos de Teclado
- `Tab` - Navegar entre campos
- `Enter` - Enviar formulário
- `Esc` - Fechar modais

### 2. Busca Rápida
A busca do DataTables procura em todos os campos:
- Código
- Obra
- Projeto
- Data
- Status

### 3. Ordenação
Clique nos cabeçalhos das colunas para ordenar.

### 4. Paginação
Escolha quantos registros por página:
- 10, 25, 50, 100, ou Todos

### 5. Exportar Dados
Use os botões no topo da tabela (se configurado):
- Copy
- Excel
- PDF
- Print

### 6. Filtros Avançados
Na listagem, use os filtros:
- Por status
- Por obra
- Por projeto
- Por data

---

## ❓ Problemas Comuns

### "Sessão expirada"
**Solução:** Faça login novamente

### "Erro ao salvar"
**Causas possíveis:**
- Campos obrigatórios não preenchidos
- Coordenadas inválidas
- Profundidade negativa

**Solução:** Verifique os campos em vermelho

### "PDF não gerou"
**Causas:**
- Sondagem sem amostras
- Dados incompletos

**Solução:** Complete os dados da sondagem

### "Upload falhou"
**Causas:**
- Arquivo muito grande (max 10MB)
- Formato não suportado
- Sem espaço em disco

**Solução:** Reduza o tamanho ou converta o formato

---

## 📞 Precisa de Ajuda?

### Suporte Técnico
- **E-mail:** suporte@supportsolosondagens.com.br
- **Telefone:** (XX) XXXX-XXXX
- **Horário:** Seg-Sex, 8h-18h

### Documentação Completa
Consulte os arquivos:
- `SISTEMA_COMPLETO.md` - Documentação técnica
- `STATUS_INTERFACE.md` - Status da implementação
- `CREDENCIAIS_TESTE.md` - Dados de teste
- `README_GEOSPT.md` - README principal

---

## 🎓 Tutorial em Vídeo

> 📹 **Em breve:** Vídeo tutorial completo

---

## ✅ Checklist de Primeiros Passos

- [ ] Fazer login no sistema
- [ ] Explorar o dashboard
- [ ] Criar sua primeira sondagem
- [ ] Adicionar amostras SPT
- [ ] Gerar o primeiro PDF
- [ ] Fazer upload de uma foto
- [ ] Baixar o template Excel
- [ ] Importar dados via Excel
- [ ] Visualizar relatórios
- [ ] Fazer logout

---

**Pronto para começar? Acesse agora:** `http://localhost:8080` 🚀
