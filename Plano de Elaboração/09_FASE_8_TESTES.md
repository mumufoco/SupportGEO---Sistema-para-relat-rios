# FASE 8: TESTES AUTOMATIZADOS

**Tempo estimado:** 5-7 dias  
**Objetivo:** Implementar testes unitários e de integração com PHPUnit

---

## 🎯 Objetivos

- Testes unitários para bibliotecas NBR
- Testes de integração para API
- Testes de validação de conformidade
- Cobertura de código > 80%

---

## 📝 COMANDOS INICIAIS

```bash
# Comando 1: Criar estrutura de testes
mkdir -p tests/Unit/Libraries
mkdir -p tests/Unit/Models
mkdir -p tests/Unit/Services
mkdir -p tests/Integration/Api
mkdir -p tests/Integration/Database

# Comando 2: Criar arquivos de teste
touch tests/Unit/Libraries/SPTCalculatorTest.php
touch tests/Unit/Libraries/NBRValidatorTest.php
touch tests/Unit/Models/SondagemModelTest.php
touch tests/Integration/Api/SondagemApiTest.php
```

---

## 🧪 TESTE: SPTCalculator

Criar `tests/Unit/Libraries/SPTCalculatorTest.php`:

```php
<?php

namespace Tests\Unit\Libraries;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\SPTCalculator;

class SPTCalculatorTest extends CIUnitTestCase
{
    /**
     * Testar cálculo de N30
     */
    public function testCalcularN30()
    {
        // Caso normal
        $this->assertEquals(9, SPTCalculator::calcularN30(5, 4));
        $this->assertEquals(15, SPTCalculator::calcularN30(8, 7));
        $this->assertEquals(53, SPTCalculator::calcularN30(48, 5));
        
        // Casos limite
        $this->assertEquals(0, SPTCalculator::calcularN30(0, 0));
        $this->assertEquals(100, SPTCalculator::calcularN30(50, 50));
    }

    /**
     * Testar classificação de consistência (solos argilosos)
     */
    public function testClassificarConsistencia()
    {
        $this->assertEquals('muito_mole', SPTCalculator::classificarConsistencia(2));
        $this->assertEquals('mole', SPTCalculator::classificarConsistencia(3));
        $this->assertEquals('mole', SPTCalculator::classificarConsistencia(4));
        $this->assertEquals('media', SPTCalculator::classificarConsistencia(5));
        $this->assertEquals('media', SPTCalculator::classificarConsistencia(8));
        $this->assertEquals('rija', SPTCalculator::classificarConsistencia(10));
        $this->assertEquals('rija', SPTCalculator::classificarConsistencia(15));
        $this->assertEquals('muito_rija', SPTCalculator::classificarConsistencia(20));
        $this->assertEquals('muito_rija', SPTCalculator::classificarConsistencia(30));
        $this->assertEquals('dura', SPTCalculator::classificarConsistencia(35));
        $this->assertEquals('dura', SPTCalculator::classificarConsistencia(50));
    }

    /**
     * Testar classificação de compacidade (solos arenosos)
     */
    public function testClassificarCompacidade()
    {
        $this->assertEquals('fofa', SPTCalculator::classificarCompacidade(4));
        $this->assertEquals('pouco_compacta', SPTCalculator::classificarCompacidade(5));
        $this->assertEquals('pouco_compacta', SPTCalculator::classificarCompacidade(8));
        $this->assertEquals('medianamente_compacta', SPTCalculator::classificarCompacidade(10));
        $this->assertEquals('medianamente_compacta', SPTCalculator::classificarCompacidade(18));
        $this->assertEquals('compacta', SPTCalculator::classificarCompacidade(25));
        $this->assertEquals('compacta', SPTCalculator::classificarCompacidade(40));
        $this->assertEquals('muito_compacta', SPTCalculator::classificarCompacidade(45));
    }

    /**
     * Testar constantes NBR
     */
    public function testConstantesNBR()
    {
        $this->assertEquals(65.0, SPTCalculator::PESO_MARTELO_NBR);
        $this->assertEquals(75.0, SPTCalculator::ALTURA_QUEDA_NBR);
        $this->assertEquals(50.8, SPTCalculator::DIAMETRO_EXTERNO_NBR);
        $this->assertEquals(34.9, SPTCalculator::DIAMETRO_INTERNO_NBR);
        $this->assertEquals(0.2, SPTCalculator::TOLERANCIA_DIAMETRO);
        $this->assertEquals(50, SPTCalculator::LIMITE_GOLPES);
    }

    /**
     * Testar verificação de impenetrável
     */
    public function testIsImpenetravel()
    {
        // Impenetrável: 50 golpes e penetração < 15cm
        $this->assertTrue(SPTCalculator::isImpenetravel(50, 10));
        $this->assertTrue(SPTCalculator::isImpenetravel(55, 5));
        
        // Não impenetrável
        $this->assertFalse(SPTCalculator::isImpenetravel(30, 15));
        $this->assertFalse(SPTCalculator::isImpenetravel(50, 15));
        $this->assertFalse(SPTCalculator::isImpenetravel(45, 10));
    }

    /**
     * Testar cálculo de N60 (correção de energia)
     */
    public function testCalcularN60()
    {
        $n30 = 20;
        $eficiencia = 0.72; // 72% típico para sistema manual brasileiro
        
        $n60 = SPTCalculator::calcularN60($n30, $eficiencia);
        
        // N60 = 20 * (72/60) = 24
        $this->assertEquals(24.0, $n60);
    }

    /**
     * Testar cálculo de profundidade
     */
    public function testCalcularProfundidade()
    {
        $profInicial = 1.00;
        
        $this->assertEquals(1.15, SPTCalculator::calcularProfundidade($profInicial, 1));
        $this->assertEquals(1.30, SPTCalculator::calcularProfundidade($profInicial, 2));
        $this->assertEquals(1.45, SPTCalculator::calcularProfundidade($profInicial, 3));
    }

    /**
     * Testar estimativa de coesão
     */
    public function testEstimarCoesao()
    {
        // Su ≈ 6 * N30
        $this->assertEquals(60.0, SPTCalculator::estimarCoesao(10));
        $this->assertEquals(120.0, SPTCalculator::estimarCoesao(20));
        $this->assertEquals(300.0, SPTCalculator::estimarCoesao(50));
    }

    /**
     * Testar estimativa de ângulo de atrito
     */
    public function testEstimarAnguloAtrito()
    {
        // φ ≈ 28 + 0.4*N30 (limitado a 45°)
        $this->assertEquals(32.0, SPTCalculator::estimarAnguloAtrito(10)); // 28 + 4
        $this->assertEquals(36.0, SPTCalculator::estimarAnguloAtrito(20)); // 28 + 8
        $this->assertEquals(45.0, SPTCalculator::estimarAnguloAtrito(50)); // Limite
    }
}
```

---

## 🧪 TESTE: NBRValidator

Criar `tests/Unit/Libraries/NBRValidatorTest.php`:

```php
<?php

namespace Tests\Unit\Libraries;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\NBRValidator;

class NBRValidatorTest extends CIUnitTestCase
{
    protected NBRValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new NBRValidator();
    }

    /**
     * Testar validação de sondagem conforme
     */
    public function testSondagemConforme()
    {
        $dados = $this->getSondagemCompleta();
        
        $resultado = $this->validator->validarSondagem($dados);
        
        $this->assertTrue($resultado['conforme']);
        $this->assertGreaterThanOrEqual(90, $resultado['score']);
        $this->assertEmpty($resultado['erros']);
    }

    /**
     * Testar validação com equipamento fora do padrão
     */
    public function testEquipamentoForaPadrao()
    {
        $dados = $this->getSondagemCompleta();
        $dados['sondagem']['peso_martelo'] = 60.0; // Errado: deveria ser 65
        $dados['sondagem']['altura_queda'] = 70.0; // Errado: deveria ser 75
        
        $resultado = $this->validator->validarSondagem($dados);
        
        $this->assertFalse($resultado['conforme']);
        $this->assertLessThan(100, $resultado['score']);
        $this->assertNotEmpty($resultado['erros']);
        
        // Verificar mensagens específicas
        $mensagensErro = array_column($resultado['erros'], 'mensagem');
        $this->assertStringContainsString('65 kgf', implode(' ', $mensagensErro));
    }

    /**
     * Testar validação sem coordenadas
     */
    public function testSemCoordenadas()
    {
        $dados = $this->getSondagemCompleta();
        $dados['sondagem']['coordenada_este'] = null;
        $dados['sondagem']['coordenada_norte'] = null;
        
        $resultado = $this->validator->validarSondagem($dados);
        
        $this->assertFalse($resultado['conforme']);
        
        $categorias = array_column($resultado['erros'], 'categoria');
        $this->assertContains('coordenadas', $categorias);
    }

    /**
     * Testar validação sem camadas
     */
    public function testSemCamadas()
    {
        $dados = $this->getSondagemCompleta();
        $dados['camadas'] = [];
        
        $resultado = $this->validator->validarSondagem($dados);
        
        $this->assertFalse($resultado['conforme']);
        
        $categorias = array_column($resultado['erros'], 'categoria');
        $this->assertContains('camadas', $categorias);
    }

    /**
     * Testar validação sem amostras
     */
    public function testSemAmostras()
    {
        $dados = $this->getSondagemCompleta();
        $dados['amostras'] = [];
        
        $resultado = $this->validator->validarSondagem($dados);
        
        $this->assertFalse($resultado['conforme']);
        
        $categorias = array_column($resultado['erros'], 'categoria');
        $this->assertContains('amostras', $categorias);
    }

    /**
     * Testar validação sem fotos obrigatórias
     */
    public function testSemFotosObrigatorias()
    {
        $dados = $this->getSondagemCompleta();
        $dados['fotos'] = []; // Sem fotos
        
        $resultado = $this->validator->validarSondagem($dados);
        
        $this->assertFalse($resultado['conforme']);
        
        $categorias = array_column($resultado['erros'], 'categoria');
        $this->assertContains('fotos', $categorias);
    }

    /**
     * Testar validação sem responsável técnico
     */
    public function testSemResponsavel()
    {
        $dados = $this->getSondagemCompleta();
        $dados['responsavel'] = null;
        
        $resultado = $this->validator->validarSondagem($dados);
        
        $this->assertFalse($resultado['conforme']);
        
        $categorias = array_column($resultado['erros'], 'categoria');
        $this->assertContains('responsavel', $categorias);
    }

    /**
     * Testar NSPT incorreto
     */
    public function testNSPTIncorreto()
    {
        $dados = $this->getSondagemCompleta();
        // Golpes 2a=5, 3a=4, mas nspt_2a_3a está como 10 (deveria ser 9)
        $dados['amostras'][0]['nspt_2a_3a'] = 10;
        
        $resultado = $this->validator->validarSondagem($dados);
        
        $this->assertFalse($resultado['conforme']);
        
        $mensagens = array_column($resultado['erros'], 'mensagem');
        $this->assertStringContainsString('NSPT incorreto', implode(' ', $mensagens));
    }

    /**
     * Dados de sondagem completa para testes
     */
    protected function getSondagemCompleta(): array
    {
        return [
            'sondagem' => [
                'id' => 1,
                'codigo_sondagem' => 'SP-01',
                'data_execucao' => '2025-08-17',
                'sondador' => 'João Silva',
                'coordenada_este' => 487801.00,
                'coordenada_norte' => 7666164.00,
                'cota_boca_furo' => 0.00,
                'profundidade_final' => 12.45,
                'peso_martelo' => 65.00,
                'altura_queda' => 75.00,
                'diametro_amostrador_externo' => 50.80,
                'diametro_amostrador_interno' => 34.90,
                'nivel_agua_inicial' => 'ausente',
            ],
            'camadas' => [
                [
                    'numero_camada' => 1,
                    'profundidade_inicial' => 0.00,
                    'profundidade_final' => 1.00,
                    'classificacao_principal' => 'vegetacao',
                    'descricao_completa' => 'Vegetação, cor verde clara (Expurgo).',
                    'cor' => 'verde clara',
                    'origem' => 'SR',
                ],
                [
                    'numero_camada' => 2,
                    'profundidade_inicial' => 1.00,
                    'profundidade_final' => 3.00,
                    'classificacao_principal' => 'silte_arenoso',
                    'descricao_completa' => 'Silte arenoso, cores vermelha e amarela, compacidade fofa.',
                    'cor' => 'vermelha e amarela',
                    'origem' => 'SR',
                ],
            ],
            'amostras' => [
                [
                    'numero_amostra' => 1,
                    'tipo_perfuracao' => 'TH',
                    'profundidade_inicial' => 0.00,
                    'golpes_1a' => null,
                    'golpes_2a' => null,
                    'golpes_3a' => null,
                    'nspt_1a_2a' => null,
                    'nspt_2a_3a' => null,
                ],
                [
                    'numero_amostra' => 2,
                    'tipo_perfuracao' => 'CR',
                    'profundidade_inicial' => 1.00,
                    'golpes_1a' => 5,
                    'golpes_2a' => 4,
                    'golpes_3a' => 2,
                    'nspt_1a_2a' => 9,
                    'nspt_2a_3a' => 6,
                ],
            ],
            'fotos' => [
                ['tipo_foto' => 'ensaio_spt', 'latitude' => -21.123, 'longitude' => -45.456],
                ['tipo_foto' => 'amostrador', 'latitude' => -21.123, 'longitude' => -45.456],
                ['tipo_foto' => 'amostra', 'latitude' => -21.123, 'longitude' => -45.456],
            ],
            'responsavel' => [
                'nome' => 'Murillo Gomes Abreu',
                'crea' => 'GO 22994/D',
                'cargo' => 'Engenheiro Civil',
            ],
        ];
    }
}
```

---

## 🧪 TESTE DE INTEGRAÇÃO: API

Criar `tests/Integration/Api/SondagemApiTest.php`:

```php
<?php

namespace Tests\Integration\Api;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

class SondagemApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $seed = 'Tests\Support\Database\Seeds\TestDataSeeder';
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = $this->getAuthToken();
    }

    /**
     * Obter token de autenticação para testes
     */
    protected function getAuthToken(): string
    {
        $result = $this->post('auth/login', [
            'email' => 'admin@supportsondagens.com.br',
            'password' => 'admin123',
        ]);
        
        $response = json_decode($result->getJSON(), true);
        return $response['token'] ?? '';
    }

    /**
     * Testar listagem de sondagens
     */
    public function testListarSondagens()
    {
        $result = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->get('api/sondagens');

        $result->assertStatus(200);
        $result->assertJSONFragment(['sucesso' => true]);
        
        $response = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('dados', $response);
        $this->assertArrayHasKey('paginacao', $response);
    }

    /**
     * Testar criação de sondagem
     */
    public function testCriarSondagem()
    {
        $dados = [
            'obra_id' => 1,
            'codigo_sondagem' => 'SP-TEST-01',
            'data_execucao' => '2025-08-20',
            'sondador' => 'Teste Automatizado',
            'coordenada_este' => 500000.00,
            'coordenada_norte' => 7500000.00,
            'profundidade_final' => 10.00,
        ];

        $result = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->post('api/sondagens', $dados);

        $result->assertStatus(201);
        $result->assertJSONFragment(['sucesso' => true]);
        
        $response = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('dados', $response);
        $this->assertEquals('SP-TEST-01', $response['dados']['sondagem']['codigo_sondagem']);
    }

    /**
     * Testar visualização de sondagem
     */
    public function testVisualizarSondagem()
    {
        $result = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->get('api/sondagens/1');

        $result->assertStatus(200);
        $result->assertJSONFragment(['sucesso' => true]);
        
        $response = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('sondagem', $response['dados']);
        $this->assertArrayHasKey('camadas', $response['dados']);
        $this->assertArrayHasKey('amostras', $response['dados']);
    }

    /**
     * Testar verificação de conformidade
     */
    public function testVerificarConformidade()
    {
        $result = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->get('api/sondagens/1/conformidade');

        $result->assertStatus(200);
        
        $response = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('conformidade', $response);
        $this->assertArrayHasKey('score', $response['conformidade']);
        $this->assertArrayHasKey('erros', $response['conformidade']);
    }

    /**
     * Testar acesso sem autenticação
     */
    public function testAcessoSemAutenticacao()
    {
        $result = $this->get('api/sondagens');

        $result->assertStatus(401);
    }

    /**
     * Testar token inválido
     */
    public function testTokenInvalido()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer token_invalido',
        ])->get('api/sondagens');

        $result->assertStatus(401);
    }

    /**
     * Testar sondagem não encontrada
     */
    public function testSondagemNaoEncontrada()
    {
        $result = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->get('api/sondagens/99999');

        $result->assertStatus(404);
    }

    /**
     * Testar validação de campos obrigatórios
     */
    public function testValidacaoCamposObrigatorios()
    {
        $dados = [
            'obra_id' => 1,
            // Faltando campos obrigatórios
        ];

        $result = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->post('api/sondagens', $dados);

        $result->assertStatus(400);
    }
}
```

---

## ⚙️ CONFIGURAÇÃO DO PHPUNIT

Criar/Editar `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/codeigniter4/framework/system/Test/bootstrap.php"
         colors="true"
         cacheResult="false"
         failOnRisky="true"
         failOnWarning="true">
    
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory suffix="Test.php">./tests/Integration</directory>
        </testsuite>
    </testsuites>

    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            <directory suffix=".php">./app/Views</directory>
            <directory suffix=".php">./app/Config</directory>
        </exclude>
        <report>
            <html outputDirectory="tests/_coverage" />
            <clover outputFile="tests/_coverage/clover.xml" />
        </report>
    </coverage>

    <php>
        <server name="app.baseURL" value="http://localhost:8080/" />
        <env name="CI_ENVIRONMENT" value="testing" />
        <env name="database.tests.hostname" value="localhost" />
        <env name="database.tests.database" value="geospt_test" />
        <env name="database.tests.username" value="geospt_user" />
        <env name="database.tests.password" value="SenhaSegura@2025" />
        <env name="database.tests.DBDriver" value="MySQLi" />
    </php>
</phpunit>
```

---

## 📝 COMANDOS DE EXECUÇÃO

```bash
# Comando 3: Executar todos os testes
./vendor/bin/phpunit

# Comando 4: Apenas testes unitários
./vendor/bin/phpunit --testsuite Unit

# Comando 5: Apenas testes de integração
./vendor/bin/phpunit --testsuite Integration

# Comando 6: Com cobertura de código
./vendor/bin/phpunit --coverage-html tests/_coverage

# Comando 7: Teste específico
./vendor/bin/phpunit --filter testCalcularN30

# Comando 8: Verbose
./vendor/bin/phpunit -v
```

---

## ✅ CHECKLIST FASE 8

- [ ] Testes SPTCalculator (10+ casos)
- [ ] Testes NBRValidator (8+ casos)
- [ ] Testes de integração API
- [ ] Cobertura > 80%
- [ ] phpunit.xml configurado
- [ ] Banco de testes separado
- [ ] CI/CD configurado para testes

---

## 🔄 PRÓXIMO PASSO

➡️ **[Fase 9 - Deploy e Produção](10_FASE_9_DEPLOY.md)**

---

**© 2025 Support Solo Sondagens Ltda**
