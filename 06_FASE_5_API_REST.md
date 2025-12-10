# FASE 5: API REST COMPLETA

**Tempo estimado:** 5-7 dias  
**Objetivo:** Implementar API RESTful completa para gerenciamento de sondagens

---

## 🎯 Objetivos

- Criar controllers da API RESTful
- Implementar autenticação JWT
- Criar filtros de autenticação
- Documentar endpoints

---

## 📝 COMANDOS INICIAIS

```bash
# Comando 1: Criar controllers da API
php spark make:controller Api/SondagemController
php spark make:controller Api/ProjetoController
php spark make:controller Api/ObraController
php spark make:controller Api/EmpresaController
php spark make:controller Api/FotoController
php spark make:controller Api/CamadaController
php spark make:controller Api/AmostraController
php spark make:controller Auth/AuthController

# Comando 2: Criar filtros
php spark make:filter JWTFilter
php spark make:filter CorsFilter
```

---

## 🔐 FILTRO JWT

Criar `app/Filters/JWTFilter.php`:

```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');
        
        if (empty($header)) {
            return Services::response()
                ->setJSON(['erro' => 'Token não fornecido'])
                ->setStatusCode(401);
        }

        // Extrair token do header "Bearer {token}"
        $token = null;
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            return Services::response()
                ->setJSON(['erro' => 'Formato de token inválido'])
                ->setStatusCode(401);
        }

        try {
            $secretKey = getenv('JWT_SECRET_KEY') ?: 'default_secret_key';
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            
            // Armazenar dados do usuário na requisição
            $request->user = $decoded;
            
        } catch (\Firebase\JWT\ExpiredException $e) {
            return Services::response()
                ->setJSON(['erro' => 'Token expirado'])
                ->setStatusCode(401);
        } catch (\Exception $e) {
            return Services::response()
                ->setJSON(['erro' => 'Token inválido: ' . $e->getMessage()])
                ->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nada a fazer após a requisição
    }
}
```

---

## 🔐 CONTROLLER DE AUTENTICAÇÃO

Criar `app/Controllers/Auth/AuthController.php`:

```php
<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use Firebase\JWT\JWT;

class AuthController extends BaseController
{
    protected UsuarioModel $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Login - Gerar token JWT
     * POST /auth/login
     */
    public function attemptLogin()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setJSON(['erro' => 'Dados inválidos', 'erros' => $this->validator->getErrors()])
                ->setStatusCode(400);
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $usuario = $this->usuarioModel->where('email', $email)->first();

        if (!$usuario) {
            return $this->response
                ->setJSON(['erro' => 'Usuário não encontrado'])
                ->setStatusCode(401);
        }

        if (!password_verify($password, $usuario['password_hash'])) {
            return $this->response
                ->setJSON(['erro' => 'Senha incorreta'])
                ->setStatusCode(401);
        }

        if (!$usuario['ativo']) {
            return $this->response
                ->setJSON(['erro' => 'Usuário inativo'])
                ->setStatusCode(401);
        }

        // Gerar token JWT
        $token = $this->gerarToken($usuario);

        // Atualizar último login
        $this->usuarioModel->update($usuario['id'], [
            'ultimo_login' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'sucesso' => true,
            'mensagem' => 'Login realizado com sucesso',
            'token' => $token,
            'usuario' => [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'tipo' => $usuario['tipo_usuario'],
            ],
            'expira_em' => getenv('JWT_TIME_TO_LIVE') ?: 28800,
        ]);
    }

    /**
     * Gerar token JWT
     */
    protected function gerarToken(array $usuario): string
    {
        $secretKey = getenv('JWT_SECRET_KEY') ?: 'default_secret_key';
        $ttl = getenv('JWT_TIME_TO_LIVE') ?: 28800; // 8 horas

        $payload = [
            'iss' => base_url(),
            'aud' => base_url(),
            'iat' => time(),
            'exp' => time() + $ttl,
            'data' => [
                'id' => $usuario['id'],
                'email' => $usuario['email'],
                'tipo' => $usuario['tipo_usuario'],
                'empresa_id' => $usuario['empresa_id'],
            ]
        ];

        return JWT::encode($payload, $secretKey, 'HS256');
    }

    /**
     * Logout (invalida sessão web)
     * GET /auth/logout
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('mensagem', 'Logout realizado com sucesso');
    }

    /**
     * Refresh token
     * POST /auth/refresh
     */
    public function refresh()
    {
        // Obter usuário do token atual
        $user = $this->request->user ?? null;
        
        if (!$user) {
            return $this->response
                ->setJSON(['erro' => 'Usuário não autenticado'])
                ->setStatusCode(401);
        }

        $usuario = $this->usuarioModel->find($user->data->id);
        
        if (!$usuario) {
            return $this->response
                ->setJSON(['erro' => 'Usuário não encontrado'])
                ->setStatusCode(401);
        }

        $novoToken = $this->gerarToken($usuario);

        return $this->response->setJSON([
            'sucesso' => true,
            'token' => $novoToken,
            'expira_em' => getenv('JWT_TIME_TO_LIVE') ?: 28800,
        ]);
    }

    /**
     * Página de login (web)
     */
    public function login()
    {
        return view('auth/login');
    }
}
```

---

## 📡 CONTROLLER DE SONDAGENS (API)

Criar `app/Controllers/Api/SondagemController.php`:

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Repositories\SondagemRepository;
use App\Libraries\NBRValidator;
use App\Models\SondagemModel;
use App\Models\CamadaModel;
use App\Models\AmostraModel;

class SondagemController extends ResourceController
{
    protected $modelName = 'App\Models\SondagemModel';
    protected $format = 'json';
    
    protected SondagemRepository $repository;
    protected NBRValidator $validator;

    public function __construct()
    {
        $this->repository = new SondagemRepository();
        $this->validator = new NBRValidator();
    }

    /**
     * Listar sondagens com filtros e paginação
     * GET /api/sondagens
     */
    public function index()
    {
        try {
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;
            
            $filtros = [
                'obra_id' => $this->request->getGet('obra_id'),
                'status' => $this->request->getGet('status'),
                'data_inicio' => $this->request->getGet('data_inicio'),
                'data_fim' => $this->request->getGet('data_fim'),
                'busca' => $this->request->getGet('busca'),
            ];

            $resultado = $this->repository->listar($filtros, $page, $perPage);

            return $this->respond([
                'sucesso' => true,
                'dados' => $resultado['dados'],
                'paginacao' => $resultado['paginacao'],
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Obter sondagem específica com todos os dados
     * GET /api/sondagens/{id}
     */
    public function show($id = null)
    {
        try {
            $sondagem = $this->repository->getSondagemComDados($id);
            
            if (!$sondagem) {
                return $this->failNotFound('Sondagem não encontrada');
            }

            // Adicionar estatísticas
            $sondagem['estatisticas'] = $this->repository->getEstatisticas($id);

            // Adicionar validação NBR
            $sondagem['conformidade'] = $this->validator->validarSondagem($sondagem);

            return $this->respond([
                'sucesso' => true,
                'dados' => $sondagem,
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Criar nova sondagem
     * POST /api/sondagens
     */
    public function create()
    {
        try {
            $dados = $this->request->getJSON(true);

            // Validar dados básicos
            $rules = [
                'obra_id' => 'required|integer',
                'codigo_sondagem' => 'required|max_length[20]',
                'data_execucao' => 'required|valid_date',
                'sondador' => 'required|max_length[100]',
                'coordenada_este' => 'required|decimal',
                'coordenada_norte' => 'required|decimal',
                'profundidade_final' => 'required|decimal',
            ];

            if (!$this->validate($rules)) {
                return $this->fail($this->validator->getErrors(), 400);
            }

            // Verificar duplicidade de código na obra
            $existe = $this->model
                ->where('obra_id', $dados['obra_id'])
                ->where('codigo_sondagem', $dados['codigo_sondagem'])
                ->first();

            if ($existe) {
                return $this->fail('Código de sondagem já existe nesta obra', 400);
            }

            // Criar sondagem
            $id = $this->model->insert($dados);

            if (!$id) {
                return $this->fail($this->model->errors(), 400);
            }

            // Buscar sondagem criada
            $sondagem = $this->repository->getSondagemComDados($id);

            return $this->respondCreated([
                'sucesso' => true,
                'mensagem' => 'Sondagem criada com sucesso',
                'dados' => $sondagem,
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Atualizar sondagem
     * PUT /api/sondagens/{id}
     */
    public function update($id = null)
    {
        try {
            $sondagem = $this->model->find($id);
            
            if (!$sondagem) {
                return $this->failNotFound('Sondagem não encontrada');
            }

            // Verificar se está aprovada (não pode editar)
            if ($sondagem['status'] === 'aprovado') {
                return $this->fail('Sondagem aprovada não pode ser editada', 400);
            }

            $dados = $this->request->getJSON(true);

            // Atualizar
            if (!$this->model->update($id, $dados)) {
                return $this->fail($this->model->errors(), 400);
            }

            $sondagemAtualizada = $this->repository->getSondagemComDados($id);

            return $this->respond([
                'sucesso' => true,
                'mensagem' => 'Sondagem atualizada com sucesso',
                'dados' => $sondagemAtualizada,
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Excluir sondagem (soft delete)
     * DELETE /api/sondagens/{id}
     */
    public function delete($id = null)
    {
        try {
            $sondagem = $this->model->find($id);
            
            if (!$sondagem) {
                return $this->failNotFound('Sondagem não encontrada');
            }

            // Verificar se está aprovada
            if ($sondagem['status'] === 'aprovado') {
                return $this->fail('Sondagem aprovada não pode ser excluída', 400);
            }

            if (!$this->model->delete($id)) {
                return $this->fail('Erro ao excluir sondagem', 500);
            }

            return $this->respondDeleted([
                'sucesso' => true,
                'mensagem' => 'Sondagem excluída com sucesso',
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Verificar conformidade NBR
     * GET /api/sondagens/{id}/conformidade
     */
    public function conformidade($id = null)
    {
        try {
            $sondagem = $this->repository->getSondagemComDados($id);
            
            if (!$sondagem) {
                return $this->failNotFound('Sondagem não encontrada');
            }

            $resultado = $this->validator->validarSondagem($sondagem);

            return $this->respond([
                'sucesso' => true,
                'sondagem_id' => $id,
                'codigo' => $sondagem['sondagem']['codigo_sondagem'],
                'conformidade' => $resultado,
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Aprovar sondagem
     * POST /api/sondagens/{id}/aprovar
     */
    public function aprovar($id = null)
    {
        try {
            $sondagem = $this->repository->getSondagemComDados($id);
            
            if (!$sondagem) {
                return $this->failNotFound('Sondagem não encontrada');
            }

            // Verificar conformidade antes de aprovar
            $validacao = $this->validator->validarSondagem($sondagem);

            if (!$validacao['conforme']) {
                return $this->fail([
                    'erro' => 'Sondagem não conforme com NBR 6484:2020',
                    'detalhes' => $validacao,
                ], 400);
            }

            // Obter ID do usuário logado
            $usuarioId = $this->request->user->data->id ?? 1;

            // Aprovar
            $this->model->aprovar($id, $usuarioId);

            return $this->respond([
                'sucesso' => true,
                'mensagem' => 'Sondagem aprovada com sucesso',
                'conformidade' => $validacao,
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Rejeitar sondagem
     * POST /api/sondagens/{id}/rejeitar
     */
    public function rejeitar($id = null)
    {
        try {
            $sondagem = $this->model->find($id);
            
            if (!$sondagem) {
                return $this->failNotFound('Sondagem não encontrada');
            }

            $motivo = $this->request->getJSON(true)['motivo'] ?? '';

            $this->model->rejeitar($id, $motivo);

            return $this->respond([
                'sucesso' => true,
                'mensagem' => 'Sondagem rejeitada',
                'motivo' => $motivo,
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
```

---

## 📡 CONTROLLER DE AMOSTRAS

Criar `app/Controllers/Api/AmostraController.php`:

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\AmostraModel;
use App\Models\SondagemModel;

class AmostraController extends ResourceController
{
    protected $modelName = 'App\Models\AmostraModel';
    protected $format = 'json';

    /**
     * Listar amostras de uma sondagem
     * GET /api/sondagens/{sondagemId}/amostras
     */
    public function index($sondagemId = null)
    {
        $amostras = $this->model->getBySondagem($sondagemId);
        
        return $this->respond([
            'sucesso' => true,
            'sondagem_id' => $sondagemId,
            'total' => count($amostras),
            'dados' => $amostras,
        ]);
    }

    /**
     * Criar amostra
     * POST /api/sondagens/{sondagemId}/amostras
     */
    public function create($sondagemId = null)
    {
        try {
            // Verificar se sondagem existe
            $sondagemModel = new SondagemModel();
            $sondagem = $sondagemModel->find($sondagemId);
            
            if (!$sondagem) {
                return $this->failNotFound('Sondagem não encontrada');
            }

            $dados = $this->request->getJSON(true);
            $dados['sondagem_id'] = $sondagemId;

            // Validar
            $rules = [
                'numero_amostra' => 'required|integer',
                'tipo_perfuracao' => 'required|in_list[TH,CR]',
                'profundidade_inicial' => 'required|decimal',
                'golpes_2a' => 'required|integer',
                'golpes_3a' => 'required|integer',
            ];

            if (!$this->validate($rules)) {
                return $this->fail($this->validator->getErrors(), 400);
            }

            $id = $this->model->insert($dados);

            if (!$id) {
                return $this->fail($this->model->errors(), 400);
            }

            $amostra = $this->model->find($id);

            return $this->respondCreated([
                'sucesso' => true,
                'mensagem' => 'Amostra criada com sucesso',
                'dados' => $amostra,
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Atualizar amostra
     * PUT /api/amostras/{id}
     */
    public function update($id = null)
    {
        try {
            $amostra = $this->model->find($id);
            
            if (!$amostra) {
                return $this->failNotFound('Amostra não encontrada');
            }

            $dados = $this->request->getJSON(true);

            if (!$this->model->update($id, $dados)) {
                return $this->fail($this->model->errors(), 400);
            }

            $amostraAtualizada = $this->model->find($id);

            return $this->respond([
                'sucesso' => true,
                'mensagem' => 'Amostra atualizada',
                'dados' => $amostraAtualizada,
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Excluir amostra
     * DELETE /api/amostras/{id}
     */
    public function delete($id = null)
    {
        try {
            $amostra = $this->model->find($id);
            
            if (!$amostra) {
                return $this->failNotFound('Amostra não encontrada');
            }

            $this->model->delete($id);

            return $this->respondDeleted([
                'sucesso' => true,
                'mensagem' => 'Amostra excluída',
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Criar amostras em lote
     * POST /api/sondagens/{sondagemId}/amostras/batch
     */
    public function batch($sondagemId = null)
    {
        try {
            $amostras = $this->request->getJSON(true)['amostras'] ?? [];
            
            if (empty($amostras)) {
                return $this->fail('Nenhuma amostra fornecida', 400);
            }

            $criadas = [];
            $erros = [];

            foreach ($amostras as $index => $amostra) {
                $amostra['sondagem_id'] = $sondagemId;
                
                $id = $this->model->insert($amostra);
                
                if ($id) {
                    $criadas[] = $this->model->find($id);
                } else {
                    $erros[] = [
                        'index' => $index,
                        'erros' => $this->model->errors(),
                    ];
                }
            }

            return $this->respondCreated([
                'sucesso' => true,
                'criadas' => count($criadas),
                'erros' => count($erros),
                'dados' => $criadas,
                'falhas' => $erros,
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
```

---

## ⚙️ CONFIGURAR ROTAS DA API

Adicionar em `app/Config/Routes.php`:

```php
<?php

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
    $routes->get('sondagens', 'SondagemController::index');
    $routes->get('sondagens/(:num)', 'SondagemController::show/$1');
    $routes->post('sondagens', 'SondagemController::create');
    $routes->put('sondagens/(:num)', 'SondagemController::update/$1');
    $routes->delete('sondagens/(:num)', 'SondagemController::delete/$1');
    $routes->get('sondagens/(:num)/conformidade', 'SondagemController::conformidade/$1');
    $routes->post('sondagens/(:num)/aprovar', 'SondagemController::aprovar/$1');
    $routes->post('sondagens/(:num)/rejeitar', 'SondagemController::rejeitar/$1');
    
    // Camadas
    $routes->get('sondagens/(:num)/camadas', 'CamadaController::index/$1');
    $routes->post('sondagens/(:num)/camadas', 'CamadaController::create/$1');
    $routes->put('camadas/(:num)', 'CamadaController::update/$1');
    $routes->delete('camadas/(:num)', 'CamadaController::delete/$1');
    
    // Amostras
    $routes->get('sondagens/(:num)/amostras', 'AmostraController::index/$1');
    $routes->post('sondagens/(:num)/amostras', 'AmostraController::create/$1');
    $routes->post('sondagens/(:num)/amostras/batch', 'AmostraController::batch/$1');
    $routes->put('amostras/(:num)', 'AmostraController::update/$1');
    $routes->delete('amostras/(:num)', 'AmostraController::delete/$1');
    
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

// Auth routes (sem JWT)
$routes->group('auth', ['namespace' => 'App\Controllers\Auth'], function($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::attemptLogin');
    $routes->post('refresh', 'AuthController::refresh');
    $routes->get('logout', 'AuthController::logout');
});
```

---

## ⚙️ REGISTRAR FILTROS

Editar `app/Config/Filters.php`:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'     => \CodeIgniter\Filters\CSRF::class,
        'toolbar'  => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot' => \CodeIgniter\Filters\Honeypot::class,
        'jwt'      => \App\Filters\JWTFilter::class,
        'cors'     => \App\Filters\CorsFilter::class,
        'auth'     => \App\Filters\AuthFilter::class,
    ];

    public array $globals = [
        'before' => [
            'cors',
        ],
        'after' => [
            'toolbar',
        ],
    ];

    public array $methods = [];

    public array $filters = [];
}
```

---

## ✅ CHECKLIST FASE 5

- [ ] JWTFilter criado e funcionando
- [ ] AuthController com login/refresh
- [ ] SondagemController com CRUD completo
- [ ] AmostraController com batch insert
- [ ] CamadaController implementado
- [ ] FotoController implementado
- [ ] Rotas da API configuradas
- [ ] Filtros registrados
- [ ] Testes de endpoints via Postman/Insomnia

---

## 🔄 PRÓXIMO PASSO

➡️ **[Fase 6 - Interface Web](07_FASE_6_INTERFACE.md)**

---

**© 2025 Support Solo Sondagens Ltda**
