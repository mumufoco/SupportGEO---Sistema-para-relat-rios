# FASE 0: PREPARAÇÃO DO AMBIENTE

**Tempo estimado:** 1-2 dias  
**Objetivo:** Configurar ambiente completo de desenvolvimento com CodeIgniter 4 e MySQL

---

## 🎯 Objetivos

- Configurar ambiente de desenvolvimento padronizado
- Instalar dependências base via Composer
- Estruturar repositório Git
- Configurar banco de dados MySQL

---

## 📝 COMANDOS PARA EXECUÇÃO

### 1. Criar Projeto Base CodeIgniter 4

```bash
# Comando 1: Criar projeto CodeIgniter 4
composer create-project codeigniter4/appstarter geospt-manager
cd geospt-manager
```

### 2. Instalar Dependências Essenciais

```bash
# Comando 2: Instalar dependências PHP
composer require tecnickcom/tcpdf           # Geração de PDF profissional
composer require phpoffice/phpspreadsheet   # Import Excel/CSV
composer require firebase/php-jwt           # Autenticação JWT
composer require intervention/image         # Manipulação de imagens
composer require smalot/pdfparser          # Parser de PDF (opcional)

# Comando 3: Instalar dependências de desenvolvimento
composer require --dev phpunit/phpunit      # Testes
composer require --dev fakerphp/faker       # Dados fictícios para testes
```

### 3. Configurar MySQL

```bash
# Comando 4: Criar banco de dados MySQL
mysql -u root -p << EOF
CREATE DATABASE geospt_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'geospt_user'@'localhost' IDENTIFIED BY 'SenhaSegura@2025';
GRANT ALL PRIVILEGES ON geospt_db.* TO 'geospt_user'@'localhost';
FLUSH PRIVILEGES;
EOF
```

### 4. Configurar Arquivo .env

```bash
# Comando 5: Copiar arquivo de ambiente
cp env .env
```

Editar o arquivo `.env`:

```ini
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = development

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'http://localhost:8080/'
app.sessionDriver = 'CodeIgniter\Session\Handlers\DatabaseHandler'
app.sessionSavePath = 'ci_sessions'

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = geospt_db
database.default.username = geospt_user
database.default.password = SenhaSegura@2025
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci

#--------------------------------------------------------------------
# JWT
#--------------------------------------------------------------------
JWT_SECRET_KEY = 'sua_chave_jwt_segura_aqui_minimo_32_caracteres'
JWT_TIME_TO_LIVE = 28800

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------
encryption.key = hex2bin:sua_chave_de_criptografia_64_caracteres_hex

#--------------------------------------------------------------------
# UPLOADS
#--------------------------------------------------------------------
UPLOAD_PATH = writable/uploads/
MAX_UPLOAD_SIZE = 10485760
ALLOWED_EXTENSIONS = jpg,jpeg,png,gif,pdf,xlsx,xls,csv
```

### 5. Gerar Chaves de Segurança

```bash
# Comando 6: Gerar chave JWT
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"

# Comando 7: Gerar chave de criptografia
openssl rand -hex 32
```

### 6. Configurar Permissões

```bash
# Comando 8: Configurar permissões de diretórios
chmod -R 777 writable/
mkdir -p writable/uploads/fotos
mkdir -p writable/uploads/imports
mkdir -p writable/uploads/reports
mkdir -p writable/uploads/assinaturas
mkdir -p writable/uploads/logos
chmod -R 777 writable/uploads/
```

### 7. Criar Estrutura de Diretórios

```bash
# Comando 9: Criar estrutura completa de pastas
mkdir -p app/Controllers/Api
mkdir -p app/Controllers/Admin
mkdir -p app/Controllers/Reports
mkdir -p app/Controllers/Auth
mkdir -p app/Repositories
mkdir -p app/Services
mkdir -p app/Libraries
mkdir -p app/Helpers
mkdir -p app/Views/layouts
mkdir -p app/Views/sondagens
mkdir -p app/Views/reports
mkdir -p app/Views/admin
mkdir -p app/Views/auth
mkdir -p app/Views/components
mkdir -p public/assets/css
mkdir -p public/assets/js
mkdir -p public/assets/images
mkdir -p public/assets/fonts
mkdir -p tests/Unit
mkdir -p tests/Integration
mkdir -p tests/Feature
mkdir -p tests/Database
mkdir -p tests/Libraries
mkdir -p tests/Services
mkdir -p tests/Repositories
mkdir -p docs
```

---

## 📁 ESTRUTURA FINAL DE DIRETÓRIOS

```
geospt-manager/
├── app/
│   ├── Config/
│   │   ├── App.php
│   │   ├── Database.php
│   │   ├── Routes.php
│   │   └── Filters.php
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── SondagemController.php
│   │   │   ├── ProjetoController.php
│   │   │   ├── ObraController.php
│   │   │   ├── FotoController.php
│   │   │   └── EmpresaController.php
│   │   ├── Admin/
│   │   │   ├── AdminController.php
│   │   │   ├── UsuarioController.php
│   │   │   └── ConfiguracaoController.php
│   │   ├── Reports/
│   │   │   └── SondagemReportController.php
│   │   ├── Auth/
│   │   │   └── AuthController.php
│   │   └── BaseController.php
│   ├── Models/
│   │   ├── EmpresaModel.php
│   │   ├── ProjetoModel.php
│   │   ├── ObraModel.php
│   │   ├── SondagemModel.php
│   │   ├── CamadaModel.php
│   │   ├── AmostraModel.php
│   │   ├── FotoModel.php
│   │   ├── ResponsavelTecnicoModel.php
│   │   ├── UsuarioModel.php
│   │   └── AuditLogModel.php
│   ├── Repositories/
│   │   ├── BaseRepository.php
│   │   ├── SondagemRepository.php
│   │   ├── CamadaRepository.php
│   │   ├── AmostraRepository.php
│   │   └── FotoRepository.php
│   ├── Services/
│   │   ├── PDFService.php
│   │   ├── ImportService.php
│   │   ├── ValidationService.php
│   │   └── ExifService.php
│   ├── Libraries/
│   │   ├── SPTCalculator.php
│   │   ├── NBRValidator.php
│   │   └── SoloClassificador.php
│   ├── Helpers/
│   │   └── nbr_helper.php
│   ├── Filters/
│   │   ├── JWTFilter.php
│   │   ├── AuthFilter.php
│   │   └── CorsFilter.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── main.php
│   │   │   ├── admin.php
│   │   │   └── pdf.php
│   │   ├── sondagens/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   ├── edit.php
│   │   │   └── show.php
│   │   ├── reports/
│   │   │   └── preview.php
│   │   ├── admin/
│   │   │   ├── dashboard.php
│   │   │   └── usuarios.php
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── register.php
│   │   └── components/
│   │       ├── header.php
│   │       ├── sidebar.php
│   │       └── footer.php
│   └── Database/
│       ├── Migrations/
│       │   └── 2025-01-01-000001_CreateCompleteGeoSPTStructure.php
│       └── Seeds/
│           └── InitialDataSeeder.php
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   │   ├── app.css
│   │   │   └── pdf.css
│   │   ├── js/
│   │   │   └── app.js
│   │   ├── images/
│   │   │   └── logo.png
│   │   └── fonts/
│   └── uploads/ -> ../writable/uploads
├── writable/
│   ├── uploads/
│   │   ├── fotos/
│   │   ├── imports/
│   │   ├── reports/
│   │   ├── assinaturas/
│   │   └── logos/
│   ├── logs/
│   ├── cache/
│   └── session/
├── tests/
│   ├── Unit/
│   ├── Integration/
│   ├── Feature/
│   └── bootstrap.php
├── docs/
│   ├── API.md
│   ├── INSTALLATION.md
│   └── USER_GUIDE.md
├── vendor/
├── .env
├── .gitignore
├── composer.json
├── composer.lock
└── README.md
```

---

## ⚙️ CONFIGURAÇÕES ADICIONAIS

### Configurar Timezone

Editar `app/Config/App.php`:

```php
public string $appTimezone = 'America/Sao_Paulo';
```

### Configurar Sessão em Banco

Editar `app/Config/App.php`:

```php
public string $sessionDriver = DatabaseHandler::class;
public string $sessionCookieName = 'ci_session';
public int $sessionExpiration = 7200;
public string $sessionSavePath = 'ci_sessions';
```

### Configurar Uploads

Criar arquivo `app/Config/Upload.php`:

```php
<?php

namespace Config;

class Upload
{
    public int $maxSize = 10485760; // 10MB
    public array $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xlsx', 'xls', 'csv'];
    public string $uploadPath = WRITEPATH . 'uploads/';
    public int $maxWidth = 4096;
    public int $maxHeight = 4096;
}
```

---

## 🔧 CONFIGURAR ROTAS BASE

Editar `app/Config/Routes.php`:

```php
<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// ============================================
// PUBLIC ROUTES
// ============================================
$routes->get('/', 'Home::index');

// ============================================
// AUTH ROUTES
// ============================================
$routes->group('auth', ['namespace' => 'App\Controllers\Auth'], function($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::attemptLogin');
    $routes->get('logout', 'AuthController::logout');
    $routes->get('register', 'AuthController::register');
    $routes->post('register', 'AuthController::attemptRegister');
});

// ============================================
// API ROUTES (JWT Protected)
// ============================================
$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'jwt'], function($routes) {
    // Empresas
    $routes->resource('empresas', ['controller' => 'EmpresaController']);
    
    // Projetos
    $routes->resource('projetos', ['controller' => 'ProjetoController']);
    
    // Obras
    $routes->resource('obras', ['controller' => 'ObraController']);
    
    // Sondagens
    $routes->resource('sondagens', ['controller' => 'SondagemController']);
    $routes->get('sondagens/(:num)/conformidade', 'SondagemController::conformidade/$1');
    $routes->post('sondagens/(:num)/aprovar', 'SondagemController::aprovar/$1');
    
    // Camadas
    $routes->get('sondagens/(:num)/camadas', 'CamadaController::index/$1');
    $routes->post('sondagens/(:num)/camadas', 'CamadaController::create/$1');
    
    // Amostras
    $routes->get('sondagens/(:num)/amostras', 'AmostraController::index/$1');
    $routes->post('sondagens/(:num)/amostras', 'AmostraController::create/$1');
    
    // Fotos
    $routes->get('sondagens/(:num)/fotos', 'FotoController::index/$1');
    $routes->post('sondagens/(:num)/fotos', 'FotoController::upload/$1');
    $routes->delete('fotos/(:num)', 'FotoController::delete/$1');
    
    // Reports
    $routes->get('reports/sondagem/(:num)/pdf', 'Reports\SondagemReportController::pdf/$1');
    $routes->get('reports/sondagem/(:num)/conformidade', 'Reports\SondagemReportController::conformidade/$1');
    $routes->post('reports/sondagens/batch', 'Reports\SondagemReportController::batch');
    
    // Import
    $routes->post('import/excel', 'ImportController::excel');
    $routes->get('import/template', 'ImportController::template');
});

// ============================================
// WEB ROUTES (Session Protected)
// ============================================
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'auth'], function($routes) {
    $routes->get('/', 'AdminController::index');
    $routes->get('dashboard', 'AdminController::dashboard');
    
    // Sondagens Web
    $routes->get('sondagens', 'SondagemWebController::index');
    $routes->get('sondagens/create', 'SondagemWebController::create');
    $routes->post('sondagens', 'SondagemWebController::store');
    $routes->get('sondagens/(:num)', 'SondagemWebController::show/$1');
    $routes->get('sondagens/(:num)/edit', 'SondagemWebController::edit/$1');
    $routes->put('sondagens/(:num)', 'SondagemWebController::update/$1');
    $routes->delete('sondagens/(:num)', 'SondagemWebController::delete/$1');
    
    // Usuários
    $routes->get('usuarios', 'UsuarioController::index');
    
    // Configurações
    $routes->get('configuracoes', 'ConfiguracaoController::index');
});

// ============================================
// REPORTS WEB (Preview)
// ============================================
$routes->get('reports/sondagem/(:num)/preview', 'Reports\SondagemReportController::preview/$1', ['filter' => 'auth']);
```

---

## ✅ CHECKLIST FASE 0

- [ ] Projeto CodeIgniter 4 criado
- [ ] Dependências Composer instaladas (TCPDF, PHPSpreadsheet, JWT, etc.)
- [ ] Banco de dados MySQL criado
- [ ] Arquivo .env configurado corretamente
- [ ] Chaves de segurança geradas (JWT, Encryption)
- [ ] Diretórios de upload criados com permissões
- [ ] Estrutura de pastas completa
- [ ] Rotas base configuradas
- [ ] Timezone configurado para America/Sao_Paulo
- [ ] Git inicializado (opcional)

---

## 🔄 PRÓXIMO PASSO

Após concluir a Fase 0, prossiga para:

➡️ **[Fase 1 - Estrutura do Banco MySQL](02_FASE_1_BANCO_DADOS.md)**

---

**© 2025 Support Solo Sondagens Ltda**
