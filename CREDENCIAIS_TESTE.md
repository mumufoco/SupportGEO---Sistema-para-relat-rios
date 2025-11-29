# 🔐 Credenciais de Teste - GeoSPT Manager

## Usuário Administrador

**E-mail:** admin@supportsolosondagens.com.br
**Senha:** password

---

## ⚠️ IMPORTANTE

Estas são credenciais de **TESTE/DESENVOLVIMENTO**.

**Antes de colocar em produção:**
1. Alterar a senha do usuário admin
2. Criar novos usuários com senhas seguras
3. Ativar validação forte de senhas
4. Implementar recuperação de senha por e-mail
5. Configurar logs de auditoria

---

## 🔄 Como trocar a senha

### Via SQL (Supabase):
```sql
-- A senha abaixo é "novasenha123"
UPDATE usuarios
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'admin@supportsolosondagens.com.br';
```

### Via PHP (no código):
```php
$password = 'sua_nova_senha';
$hash = password_hash($password, PASSWORD_DEFAULT);
```

---

## 📊 Dados de Teste no Banco

### Empresa
- **ID:** 1
- **Razão Social:** Support Solo Sondagens Ltda
- **CNPJ:** 12.345.678/0001-90

### Projeto
- **ID:** 1
- **Nome:** Projeto Teste
- **Cliente:** Cliente Exemplo

### Obra
- **ID:** 1
- **Nome:** Obra de Teste
- **Município:** São Paulo

### Sondagem
- **ID:** 1
- **Código:** SP-01
- **Status:** rascunho

---

## 🚀 Fluxo de Teste Completo

1. **Login**
   - Acesse: `http://localhost:8080/login`
   - E-mail: admin@supportsolosondagens.com.br
   - Senha: password

2. **Dashboard**
   - Você será redirecionado para `/admin/dashboard`
   - Visualize as estatísticas

3. **Listar Sondagens**
   - Acesse: `/admin/sondagens`
   - Veja a listagem com filtros

4. **Criar Nova Sondagem**
   - Clique em "Nova Sondagem"
   - Preencha o formulário
   - Adicione amostras SPT

5. **Gerar PDF**
   - Na listagem, clique no botão PDF
   - O PDF será gerado conforme NBR 6484:2020

6. **Logout**
   - Clique no menu do usuário > Sair

---

## 🔧 Tipos de Usuário

O sistema suporta 4 tipos de usuário:

1. **admin** - Acesso total ao sistema
2. **engenheiro** - Pode criar, editar e aprovar sondagens
3. **operador** - Pode criar e editar sondagens
4. **visualizador** - Apenas visualização

Para criar novos usuários, use a API:

```bash
POST /api/usuarios
{
  "nome": "Nome do Usuário",
  "email": "usuario@email.com",
  "password": "senha123",
  "tipo_usuario": "operador",
  "empresa_id": 1
}
```

---

**Data:** 29/11/2025
**Versão:** 1.0
