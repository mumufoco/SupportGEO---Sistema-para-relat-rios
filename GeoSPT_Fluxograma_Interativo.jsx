import React, { useState } from 'react';

const FluxogramaViewer = () => {
  const [activeTab, setActiveTab] = useState('arquitetura');

  const tabs = [
    { id: 'arquitetura', label: '🏗️ Arquitetura', icon: '🏗️' },
    { id: 'auth', label: '🔐 Autenticação', icon: '🔐' },
    { id: 'sondagem', label: '📋 Sondagem', icon: '📋' },
    { id: 'amostras', label: '🧪 Amostras', icon: '🧪' },
    { id: 'fotos', label: '📷 Fotos', icon: '📷' },
    { id: 'nbr', label: '✅ NBR', icon: '✅' },
    { id: 'pdf', label: '📄 PDF', icon: '📄' },
    { id: 'ciclo', label: '🔄 Ciclo', icon: '🔄' },
  ];

  const ArquiteturaFlow = () => (
    <svg viewBox="0 0 800 600" className="w-full h-full">
      {/* Usuários */}
      <g transform="translate(50, 30)">
        <rect x="0" y="0" width="700" height="60" rx="10" fill="#e3f2fd" stroke="#1976d2" strokeWidth="2"/>
        <text x="350" y="25" textAnchor="middle" fontWeight="bold" fontSize="14">👥 USUÁRIOS</text>
        <text x="100" y="45" textAnchor="middle" fontSize="11">🖥️ Browser</text>
        <text x="250" y="45" textAnchor="middle" fontSize="11">📱 Mobile</text>
        <text x="400" y="45" textAnchor="middle" fontSize="11">🔧 API</text>
        <text x="550" y="45" textAnchor="middle" fontSize="11">📊 Excel</text>
      </g>

      {/* Seta */}
      <path d="M400 95 L400 120" stroke="#666" strokeWidth="2" markerEnd="url(#arrow)"/>

      {/* Nginx */}
      <g transform="translate(50, 120)">
        <rect x="0" y="0" width="700" height="50" rx="10" fill="#fff3e0" stroke="#f57c00" strokeWidth="2"/>
        <text x="350" y="30" textAnchor="middle" fontWeight="bold" fontSize="14">🌐 NGINX - SSL/TLS - Rate Limiting - CORS</text>
      </g>

      {/* Seta */}
      <path d="M400 175 L400 200" stroke="#666" strokeWidth="2" markerEnd="url(#arrow)"/>

      {/* CodeIgniter */}
      <g transform="translate(50, 200)">
        <rect x="0" y="0" width="700" height="220" rx="10" fill="#e8f5e9" stroke="#388e3c" strokeWidth="2"/>
        <text x="350" y="25" textAnchor="middle" fontWeight="bold" fontSize="14">🚀 CODEIGNITER 4</text>
        
        {/* Boxes internos */}
        <rect x="20" y="40" width="150" height="70" rx="5" fill="#c8e6c9" stroke="#388e3c"/>
        <text x="95" y="60" textAnchor="middle" fontSize="11" fontWeight="bold">Filters</text>
        <text x="95" y="80" textAnchor="middle" fontSize="10">JWT Auth</text>
        <text x="95" y="95" textAnchor="middle" fontSize="10">CORS</text>

        <rect x="190" y="40" width="150" height="70" rx="5" fill="#c8e6c9" stroke="#388e3c"/>
        <text x="265" y="60" textAnchor="middle" fontSize="11" fontWeight="bold">Controllers</text>
        <text x="265" y="80" textAnchor="middle" fontSize="10">API / Web</text>
        <text x="265" y="95" textAnchor="middle" fontSize="10">Auth</text>

        <rect x="360" y="40" width="150" height="70" rx="5" fill="#c8e6c9" stroke="#388e3c"/>
        <text x="435" y="60" textAnchor="middle" fontSize="11" fontWeight="bold">Services</text>
        <text x="435" y="80" textAnchor="middle" fontSize="10">PDF / EXIF</text>
        <text x="435" y="95" textAnchor="middle" fontSize="10">Import</text>

        <rect x="530" y="40" width="150" height="70" rx="5" fill="#c8e6c9" stroke="#388e3c"/>
        <text x="605" y="60" textAnchor="middle" fontSize="11" fontWeight="bold">Libraries NBR</text>
        <text x="605" y="80" textAnchor="middle" fontSize="10">SPTCalculator</text>
        <text x="605" y="95" textAnchor="middle" fontSize="10">NBRValidator</text>

        <rect x="20" y="125" width="320" height="70" rx="5" fill="#a5d6a7" stroke="#388e3c"/>
        <text x="180" y="145" textAnchor="middle" fontSize="11" fontWeight="bold">Models</text>
        <text x="180" y="165" textAnchor="middle" fontSize="10">Sondagem | Amostra | Camada | Foto</text>
        <text x="180" y="180" textAnchor="middle" fontSize="10">Validações NBR integradas</text>

        <rect x="360" y="125" width="320" height="70" rx="5" fill="#a5d6a7" stroke="#388e3c"/>
        <text x="520" y="145" textAnchor="middle" fontSize="11" fontWeight="bold">Repository Pattern</text>
        <text x="520" y="165" textAnchor="middle" fontSize="10">SondagemRepository</text>
        <text x="520" y="180" textAnchor="middle" fontSize="10">getSondagemComDados()</text>
      </g>

      {/* Seta */}
      <path d="M400 425 L400 450" stroke="#666" strokeWidth="2" markerEnd="url(#arrow)"/>

      {/* Database */}
      <g transform="translate(50, 450)">
        <rect x="0" y="0" width="700" height="80" rx="10" fill="#fce4ec" stroke="#c2185b" strokeWidth="2"/>
        <text x="350" y="25" textAnchor="middle" fontWeight="bold" fontSize="14">🗄️ MySQL 8.0</text>
        <text x="350" y="50" textAnchor="middle" fontSize="11">empresas | projetos | obras | sondagens | camadas | amostras | fotos | audit_log</text>
        <text x="350" y="70" textAnchor="middle" fontSize="10" fill="#666">Soft deletes | Foreign keys | Índices otimizados</text>
      </g>

      {/* Marker para setas */}
      <defs>
        <marker id="arrow" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto">
          <path d="M0,0 L0,6 L9,3 z" fill="#666"/>
        </marker>
      </defs>
    </svg>
  );

  const AuthFlow = () => (
    <svg viewBox="0 0 800 500" className="w-full h-full">
      {/* Usuário */}
      <g transform="translate(100, 50)">
        <circle cx="0" cy="0" r="30" fill="#e3f2fd" stroke="#1976d2" strokeWidth="2"/>
        <text x="0" y="5" textAnchor="middle" fontSize="24">👤</text>
        <text x="0" y="50" textAnchor="middle" fontSize="12" fontWeight="bold">Usuário</text>
      </g>

      {/* Servidor */}
      <g transform="translate(600, 50)">
        <rect x="-40" y="-30" width="80" height="60" rx="10" fill="#e8f5e9" stroke="#388e3c" strokeWidth="2"/>
        <text x="0" y="5" textAnchor="middle" fontSize="24">🖥️</text>
        <text x="0" y="50" textAnchor="middle" fontSize="12" fontWeight="bold">Servidor</text>
      </g>

      {/* Fluxo 1: Login */}
      <g transform="translate(0, 100)">
        <line x1="130" y1="0" x2="520" y2="0" stroke="#1976d2" strokeWidth="2" markerEnd="url(#arrow)"/>
        <text x="325" y="-10" textAnchor="middle" fontSize="11" fill="#1976d2">1. POST /auth/login {`{email, password}`}</text>
      </g>

      {/* Validação */}
      <g transform="translate(520, 130)">
        <rect x="0" y="0" width="180" height="80" rx="5" fill="#fff9c4" stroke="#fbc02d" strokeWidth="1"/>
        <text x="90" y="20" textAnchor="middle" fontSize="10" fontWeight="bold">2. Validar credenciais</text>
        <text x="90" y="40" textAnchor="middle" fontSize="9">• Buscar usuário</text>
        <text x="90" y="55" textAnchor="middle" fontSize="9">• Verificar senha</text>
        <text x="90" y="70" textAnchor="middle" fontSize="9">• Verificar status</text>
      </g>

      {/* Gerar Token */}
      <g transform="translate(520, 220)">
        <rect x="0" y="0" width="180" height="60" rx="5" fill="#e1bee7" stroke="#7b1fa2" strokeWidth="1"/>
        <text x="90" y="20" textAnchor="middle" fontSize="10" fontWeight="bold">3. Gerar Token JWT</text>
        <text x="90" y="40" textAnchor="middle" fontSize="9">payload: {`{id, email, tipo}`}</text>
        <text x="90" y="55" textAnchor="middle" fontSize="9">exp: +8 horas</text>
      </g>

      {/* Response */}
      <g transform="translate(0, 300)">
        <line x1="520" y1="0" x2="130" y2="0" stroke="#388e3c" strokeWidth="2" markerEnd="url(#arrow)"/>
        <text x="325" y="-10" textAnchor="middle" fontSize="11" fill="#388e3c">4. Response: {`{token, usuario, expira_em}`}</text>
      </g>

      {/* Requisição autenticada */}
      <g transform="translate(0, 350)">
        <line x1="130" y1="0" x2="520" y2="0" stroke="#1976d2" strokeWidth="2" markerEnd="url(#arrow)"/>
        <text x="325" y="-10" textAnchor="middle" fontSize="11" fill="#1976d2">5. GET /api/sondagens | Header: Bearer {`{token}`}</text>
      </g>

      {/* JWT Filter */}
      <g transform="translate(520, 370)">
        <rect x="0" y="0" width="180" height="60" rx="5" fill="#b3e5fc" stroke="#0288d1" strokeWidth="1"/>
        <text x="90" y="20" textAnchor="middle" fontSize="10" fontWeight="bold">6. JWTFilter</text>
        <text x="90" y="40" textAnchor="middle" fontSize="9">• Validar assinatura</text>
        <text x="90" y="55" textAnchor="middle" fontSize="9">• Verificar expiração</text>
      </g>

      {/* Response final */}
      <g transform="translate(0, 450)">
        <line x1="520" y1="0" x2="130" y2="0" stroke="#388e3c" strokeWidth="2" markerEnd="url(#arrow)"/>
        <text x="325" y="-10" textAnchor="middle" fontSize="11" fill="#388e3c">7. Response: {`{dados da API}`}</text>
      </g>

      <defs>
        <marker id="arrow" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto">
          <path d="M0,0 L0,6 L9,3 z" fill="currentColor"/>
        </marker>
      </defs>
    </svg>
  );

  const SondagemFlow = () => (
    <svg viewBox="0 0 800 600" className="w-full h-full">
      {/* Início */}
      <g transform="translate(400, 30)">
        <circle cx="0" cy="0" r="25" fill="#e3f2fd" stroke="#1976d2" strokeWidth="2"/>
        <text x="0" y="5" textAnchor="middle" fontSize="12" fontWeight="bold">Início</text>
      </g>

      <path d="M400 55 L400 80" stroke="#666" strokeWidth="2" markerEnd="url(#arrow)"/>

      {/* Dashboard */}
      <g transform="translate(300, 80)">
        <rect x="0" y="0" width="200" height="40" rx="5" fill="#e8f5e9" stroke="#388e3c" strokeWidth="2"/>
        <text x="100" y="25" textAnchor="middle" fontSize="11">🏠 Dashboard</text>
      </g>

      <path d="M400 120 L400 145" stroke="#666" strokeWidth="2" markerEnd="url(#arrow)"/>

      {/* Nova Sondagem */}
      <g transform="translate(300, 145)">
        <rect x="0" y="0" width="200" height="40" rx="5" fill="#e3f2fd" stroke="#1976d2" strokeWidth="2"/>
        <text x="100" y="25" textAnchor="middle" fontSize="11">➕ Nova Sondagem</text>
      </g>

      <path d="M400 185 L400 210" stroke="#666" strokeWidth="2" markerEnd="url(#arrow)"/>

      {/* Formulário */}
      <g transform="translate(200, 210)">
        <rect x="0" y="0" width="400" height="120" rx="10" fill="#fff9c4" stroke="#fbc02d" strokeWidth="2"/>
        <text x="200" y="20" textAnchor="middle" fontSize="12" fontWeight="bold">📝 Formulário de Sondagem</text>
        
        <text x="20" y="45" fontSize="10">• Código (SP-01)</text>
        <text x="20" y="60" fontSize="10">• Obra</text>
        <text x="20" y="75" fontSize="10">• Data execução</text>
        <text x="20" y="90" fontSize="10">• Sondador</text>
        
        <text x="150" y="45" fontSize="10">• Coord. Este/Norte</text>
        <text x="150" y="60" fontSize="10">• Cota boca furo</text>
        <text x="150" y="75" fontSize="10">• Prof. final</text>
        
        <text x="280" y="45" fontSize="10">• Peso: 65 kgf</text>
        <text x="280" y="60" fontSize="10">• Altura: 75 cm</text>
        <text x="280" y="75" fontSize="10">• ∅ext: 50,8 mm</text>
        <text x="280" y="90" fontSize="10">• ∅int: 34,9 mm</text>
      </g>

      <path d="M400 330 L400 355" stroke="#666" strokeWidth="2" markerEnd="url(#arrow)"/>

      {/* Validação */}
      <g transform="translate(325, 355)">
        <polygon points="75,0 150,40 75,80 0,40" fill="#e1bee7" stroke="#7b1fa2" strokeWidth="2"/>
        <text x="75" y="45" textAnchor="middle" fontSize="11">Validação?</text>
      </g>

      {/* Válido */}
      <path d="M475 395 L550 395" stroke="#388e3c" strokeWidth="2" markerEnd="url(#arrow)"/>
      <text x="510" y="385" fontSize="10" fill="#388e3c">✓ Válido</text>

      <g transform="translate(550, 375)">
        <rect x="0" y="0" width="150" height="40" rx="5" fill="#c8e6c9" stroke="#388e3c" strokeWidth="2"/>
        <text x="75" y="25" textAnchor="middle" fontSize="11">📥 INSERT MySQL</text>
      </g>

      {/* Inválido */}
      <path d="M325 395 L250 395" stroke="#d32f2f" strokeWidth="2" markerEnd="url(#arrow)"/>
      <text x="285" y="385" fontSize="10" fill="#d32f2f">✗ Inválido</text>

      <g transform="translate(100, 375)">
        <rect x="0" y="0" width="150" height="40" rx="5" fill="#ffcdd2" stroke="#d32f2f" strokeWidth="2"/>
        <text x="75" y="25" textAnchor="middle" fontSize="11">❌ Erro 400</text>
      </g>

      {/* Callbacks */}
      <path d="M625 415 L625 450" stroke="#666" strokeWidth="2" markerEnd="url(#arrow)"/>

      <g transform="translate(550, 450)">
        <rect x="0" y="0" width="150" height="60" rx="5" fill="#b3e5fc" stroke="#0288d1" strokeWidth="2"/>
        <text x="75" y="15" textAnchor="middle" fontSize="10" fontWeight="bold">Callbacks</text>
        <text x="75" y="32" textAnchor="middle" fontSize="9">version = 1</text>
        <text x="75" y="45" textAnchor="middle" fontSize="9">status = rascunho</text>
        <text x="75" y="58" textAnchor="middle" fontSize="9">logAudit()</text>
      </g>

      {/* Response */}
      <path d="M625 510 L625 540" stroke="#666" strokeWidth="2" markerEnd="url(#arrow)"/>

      <g transform="translate(550, 540)">
        <rect x="0" y="0" width="150" height="40" rx="5" fill="#c8e6c9" stroke="#388e3c" strokeWidth="2"/>
        <text x="75" y="25" textAnchor="middle" fontSize="11">✅ Response 201</text>
      </g>

      <defs>
        <marker id="arrow" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto">
          <path d="M0,0 L0,6 L9,3 z" fill="#666"/>
        </marker>
      </defs>
    </svg>
  );

  const NBRFlow = () => (
    <svg viewBox="0 0 800 500" className="w-full h-full">
      {/* Título */}
      <g transform="translate(400, 30)">
        <rect x="-100" y="-15" width="200" height="30" rx="5" fill="#1976d2" stroke="#1565c0" strokeWidth="2"/>
        <text x="0" y="5" textAnchor="middle" fontSize="12" fontWeight="bold" fill="white">✅ Validação NBR 6484:2020</text>
      </g>

      {/* Equipamento */}
      <g transform="translate(50, 80)">
        <rect x="0" y="0" width="200" height="100" rx="5" fill="#ffecb3" stroke="#ff8f00" strokeWidth="2"/>
        <text x="100" y="20" textAnchor="middle" fontSize="11" fontWeight="bold">1️⃣ EQUIPAMENTO</text>
        <text x="100" y="40" textAnchor="middle" fontSize="14" fontWeight="bold" fill="#ff8f00">20%</text>
        <text x="100" y="60" textAnchor="middle" fontSize="9">✓ peso = 65 kgf</text>
        <text x="100" y="75" textAnchor="middle" fontSize="9">✓ altura = 75 cm</text>
        <text x="100" y="90" textAnchor="middle" fontSize="9">✓ ∅ext = 50,8 ± 0,2</text>
      </g>

      {/* Coordenadas */}
      <g transform="translate(300, 80)">
        <rect x="0" y="0" width="200" height="100" rx="5" fill="#b3e5fc" stroke="#0288d1" strokeWidth="2"/>
        <text x="100" y="20" textAnchor="middle" fontSize="11" fontWeight="bold">2️⃣ COORDENADAS</text>
        <text x="100" y="40" textAnchor="middle" fontSize="14" fontWeight="bold" fill="#0288d1">15%</text>
        <text x="100" y="60" textAnchor="middle" fontSize="9">✓ Este preenchido</text>
        <text x="100" y="75" textAnchor="middle" fontSize="9">✓ Norte preenchido</text>
        <text x="100" y="90" textAnchor="middle" fontSize="9">✓ Datum informado</text>
      </g>

      {/* Camadas */}
      <g transform="translate(550, 80)">
        <rect x="0" y="0" width="200" height="100" rx="5" fill="#c8e6c9" stroke="#388e3c" strokeWidth="2"/>
        <text x="100" y="20" textAnchor="middle" fontSize="11" fontWeight="bold">3️⃣ CAMADAS</text>
        <text x="100" y="40" textAnchor="middle" fontSize="14" fontWeight="bold" fill="#388e3c">15%</text>
        <text x="100" y="60" textAnchor="middle" fontSize="9">✓ Mínimo 1 camada</text>
        <text x="100" y="75" textAnchor="middle" fontSize="9">✓ Continuidade OK</text>
        <text x="100" y="90" textAnchor="middle" fontSize="9">✓ Classificação NBR</text>
      </g>

      {/* Amostras */}
      <g transform="translate(50, 210)">
        <rect x="0" y="0" width="200" height="100" rx="5" fill="#e1bee7" stroke="#7b1fa2" strokeWidth="2"/>
        <text x="100" y="20" textAnchor="middle" fontSize="11" fontWeight="bold">4️⃣ AMOSTRAS</text>
        <text x="100" y="40" textAnchor="middle" fontSize="14" fontWeight="bold" fill="#7b1fa2">20%</text>
        <text x="100" y="60" textAnchor="middle" fontSize="9">✓ Mínimo 1 amostra</text>
        <text x="100" y="75" textAnchor="middle" fontSize="9">✓ Sequência correta</text>
        <text x="100" y="90" textAnchor="middle" fontSize="9">✓ NSPT = 2ª + 3ª</text>
      </g>

      {/* Fotos */}
      <g transform="translate(300, 210)">
        <rect x="0" y="0" width="200" height="100" rx="5" fill="#ffccbc" stroke="#e64a19" strokeWidth="2"/>
        <text x="100" y="20" textAnchor="middle" fontSize="11" fontWeight="bold">5️⃣ FOTOS</text>
        <text x="100" y="40" textAnchor="middle" fontSize="14" fontWeight="bold" fill="#e64a19">15%</text>
        <text x="100" y="60" textAnchor="middle" fontSize="9">✓ Foto ensaio SPT</text>
        <text x="100" y="75" textAnchor="middle" fontSize="9">✓ Foto amostrador</text>
        <text x="100" y="90" textAnchor="middle" fontSize="9">✓ Foto amostra</text>
      </g>

      {/* Responsável */}
      <g transform="translate(550, 210)">
        <rect x="0" y="0" width="200" height="100" rx="5" fill="#d1c4e9" stroke="#512da8" strokeWidth="2"/>
        <text x="100" y="20" textAnchor="middle" fontSize="11" fontWeight="bold">6️⃣ RESPONSÁVEL</text>
        <text x="100" y="40" textAnchor="middle" fontSize="14" fontWeight="bold" fill="#512da8">10%</text>
        <text x="100" y="60" textAnchor="middle" fontSize="9">✓ Nome preenchido</text>
        <text x="100" y="75" textAnchor="middle" fontSize="9">✓ CREA válido</text>
        <text x="100" y="90" textAnchor="middle" fontSize="9">✓ Formato UF 0000/X</text>
      </g>

      {/* Setas convergindo */}
      <path d="M150 180 L400 350" stroke="#666" strokeWidth="1" strokeDasharray="5,5"/>
      <path d="M400 180 L400 350" stroke="#666" strokeWidth="1" strokeDasharray="5,5"/>
      <path d="M650 180 L400 350" stroke="#666" strokeWidth="1" strokeDasharray="5,5"/>
      <path d="M150 310 L400 350" stroke="#666" strokeWidth="1" strokeDasharray="5,5"/>
      <path d="M400 310 L400 350" stroke="#666" strokeWidth="1" strokeDasharray="5,5"/>
      <path d="M650 310 L400 350" stroke="#666" strokeWidth="1" strokeDasharray="5,5"/>

      {/* Score Total */}
      <g transform="translate(300, 350)">
        <rect x="0" y="0" width="200" height="50" rx="5" fill="#fff9c4" stroke="#fbc02d" strokeWidth="2"/>
        <text x="100" y="20" textAnchor="middle" fontSize="11" fontWeight="bold">🧮 SCORE TOTAL</text>
        <text x="100" y="40" textAnchor="middle" fontSize="12">20 + 15 + 15 + 20 + 15 + 10 = 100</text>
      </g>

      {/* Decisão */}
      <path d="M400 400 L400 420" stroke="#666" strokeWidth="2"/>
      <path d="M400 420 L200 460" stroke="#388e3c" strokeWidth="2" markerEnd="url(#arrow)"/>
      <path d="M400 420 L600 460" stroke="#d32f2f" strokeWidth="2" markerEnd="url(#arrow)"/>

      {/* Conforme */}
      <g transform="translate(100, 460)">
        <rect x="0" y="0" width="200" height="40" rx="5" fill="#c8e6c9" stroke="#388e3c" strokeWidth="2"/>
        <text x="100" y="25" textAnchor="middle" fontSize="11" fontWeight="bold">✅ CONFORME (100)</text>
      </g>

      {/* Não Conforme */}
      <g transform="translate(500, 460)">
        <rect x="0" y="0" width="200" height="40" rx="5" fill="#ffcdd2" stroke="#d32f2f" strokeWidth="2"/>
        <text x="100" y="25" textAnchor="middle" fontSize="11" fontWeight="bold">❌ NÃO CONFORME</text>
      </g>

      <defs>
        <marker id="arrow" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto">
          <path d="M0,0 L0,6 L9,3 z" fill="#666"/>
        </marker>
      </defs>
    </svg>
  );

  const CicloFlow = () => (
    <svg viewBox="0 0 800 400" className="w-full h-full">
      {/* RASCUNHO */}
      <g transform="translate(100, 150)">
        <circle cx="0" cy="0" r="50" fill="#e0e0e0" stroke="#757575" strokeWidth="3"/>
        <text x="0" y="-10" textAnchor="middle" fontSize="10" fontWeight="bold">📝</text>
        <text x="0" y="10" textAnchor="middle" fontSize="10" fontWeight="bold">RASCUNHO</text>
        <text x="0" y="70" textAnchor="middle" fontSize="9">• Editável</text>
        <text x="0" y="85" textAnchor="middle" fontSize="9">• Sem PDF</text>
      </g>

      {/* Seta para EM_ANALISE */}
      <path d="M150 150 L250 150" stroke="#1976d2" strokeWidth="2" markerEnd="url(#arrow)"/>
      <text x="200" y="140" textAnchor="middle" fontSize="9" fill="#1976d2">Score=100</text>

      {/* EM_ANALISE */}
      <g transform="translate(300, 150)">
        <circle cx="0" cy="0" r="50" fill="#bbdefb" stroke="#1976d2" strokeWidth="3"/>
        <text x="0" y="-10" textAnchor="middle" fontSize="10" fontWeight="bold">🔍</text>
        <text x="0" y="10" textAnchor="middle" fontSize="9" fontWeight="bold">EM_ANÁLISE</text>
        <text x="0" y="70" textAnchor="middle" fontSize="9">• Aguarda</text>
        <text x="0" y="85" textAnchor="middle" fontSize="9">• aprovação</text>
      </g>

      {/* Seta para APROVADO */}
      <path d="M350 130 L450 80" stroke="#388e3c" strokeWidth="2" markerEnd="url(#arrow)"/>
      <text x="400" y="90" textAnchor="middle" fontSize="9" fill="#388e3c">Aprovar</text>

      {/* Seta para REJEITADO */}
      <path d="M350 170 L450 220" stroke="#d32f2f" strokeWidth="2" markerEnd="url(#arrow)"/>
      <text x="400" y="210" textAnchor="middle" fontSize="9" fill="#d32f2f">Rejeitar</text>

      {/* APROVADO */}
      <g transform="translate(500, 60)">
        <circle cx="0" cy="0" r="50" fill="#c8e6c9" stroke="#388e3c" strokeWidth="3"/>
        <text x="0" y="-10" textAnchor="middle" fontSize="10" fontWeight="bold">✅</text>
        <text x="0" y="10" textAnchor="middle" fontSize="10" fontWeight="bold">APROVADO</text>
        <text x="0" y="70" textAnchor="middle" fontSize="9">• PDF OK</text>
        <text x="0" y="85" textAnchor="middle" fontSize="9">• Bloqueado</text>
      </g>

      {/* REJEITADO */}
      <g transform="translate(500, 240)">
        <circle cx="0" cy="0" r="50" fill="#ffcdd2" stroke="#d32f2f" strokeWidth="3"/>
        <text x="0" y="-10" textAnchor="middle" fontSize="10" fontWeight="bold">❌</text>
        <text x="0" y="10" textAnchor="middle" fontSize="10" fontWeight="bold">REJEITADO</text>
        <text x="0" y="70" textAnchor="middle" fontSize="9">• Editável</text>
        <text x="0" y="85" textAnchor="middle" fontSize="9">• Motivo</text>
      </g>

      {/* Seta de REJEITADO para RASCUNHO */}
      <path d="M450 260 C300 350, 100 300, 100 200" stroke="#ff9800" strokeWidth="2" strokeDasharray="5,5" markerEnd="url(#arrow)" fill="none"/>
      <text x="250" y="310" textAnchor="middle" fontSize="9" fill="#ff9800">Corrigir</text>

      {/* FIM */}
      <g transform="translate(700, 60)">
        <circle cx="0" cy="0" r="30" fill="#4caf50" stroke="#2e7d32" strokeWidth="3"/>
        <text x="0" y="5" textAnchor="middle" fontSize="16">📄</text>
        <text x="0" y="50" textAnchor="middle" fontSize="9" fontWeight="bold">PDF</text>
      </g>

      <path d="M550 60 L670 60" stroke="#388e3c" strokeWidth="2" markerEnd="url(#arrow)"/>

      <defs>
        <marker id="arrow" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto">
          <path d="M0,0 L0,6 L9,3 z" fill="#666"/>
        </marker>
      </defs>
    </svg>
  );

  const PDFFlow = () => (
    <svg viewBox="0 0 800 500" className="w-full h-full">
      {/* Título */}
      <text x="400" y="30" textAnchor="middle" fontSize="14" fontWeight="bold">📄 Geração de PDF - Estrutura do Relatório</text>

      {/* Página 1 */}
      <g transform="translate(50, 60)">
        <rect x="0" y="0" width="200" height="380" rx="5" fill="#fff" stroke="#1976d2" strokeWidth="2"/>
        <text x="100" y="20" textAnchor="middle" fontSize="11" fontWeight="bold" fill="#1976d2">PÁGINA 1</text>
        
        <rect x="10" y="35" width="180" height="40" rx="3" fill="#e8f5e9" stroke="#388e3c"/>
        <text x="100" y="55" textAnchor="middle" fontSize="9" fontWeight="bold">CABEÇALHO</text>
        <text x="100" y="70" textAnchor="middle" fontSize="8">Logo + Empresa + Cliente</text>

        <rect x="10" y="85" width="180" height="50" rx="3" fill="#e3f2fd" stroke="#1976d2"/>
        <text x="100" y="105" textAnchor="middle" fontSize="9" fontWeight="bold">DADOS TÉCNICOS</text>
        <text x="100" y="120" textAnchor="middle" fontSize="8">∅ Amostrador | Peso | Altura</text>
        <text x="100" y="130" textAnchor="middle" fontSize="8">Coordenadas UTM</text>

        <rect x="10" y="145" width="85" height="180" rx="3" fill="#fff9c4" stroke="#fbc02d"/>
        <text x="52" y="165" textAnchor="middle" fontSize="9" fontWeight="bold">GRÁFICO</text>
        <text x="52" y="180" textAnchor="middle" fontSize="8">N30</text>
        <line x1="25" y1="200" x2="80" y2="200" stroke="#1976d2" strokeWidth="2"/>
        <line x1="25" y1="220" x2="60" y2="220" stroke="#d32f2f" strokeWidth="2"/>
        <line x1="25" y1="240" x2="75" y2="240" stroke="#1976d2" strokeWidth="2"/>
        <line x1="25" y1="260" x2="90" y2="260" stroke="#d32f2f" strokeWidth="2"/>
        <line x1="25" y1="280" x2="95" y2="280" stroke="#1976d2" strokeWidth="2"/>

        <rect x="105" y="145" width="85" height="180" rx="3" fill="#c8e6c9" stroke="#388e3c"/>
        <text x="147" y="165" textAnchor="middle" fontSize="9" fontWeight="bold">PERFIL</text>
        <rect x="115" y="180" width="65" height="25" fill="#8d6e63"/>
        <rect x="115" y="205" width="65" height="35" fill="#ffcc80"/>
        <rect x="115" y="240" width="65" height="40" fill="#ef9a9a"/>
        <rect x="115" y="280" width="65" height="35" fill="#a1887f"/>

        <rect x="10" y="335" width="180" height="35" rx="3" fill="#f5f5f5" stroke="#9e9e9e"/>
        <text x="100" y="350" textAnchor="middle" fontSize="9" fontWeight="bold">RODAPÉ</text>
        <text x="100" y="365" textAnchor="middle" fontSize="8">Obs. + Sondador + RT</text>
      </g>

      {/* Página 2 */}
      <g transform="translate(300, 60)">
        <rect x="0" y="0" width="200" height="380" rx="5" fill="#fff" stroke="#388e3c" strokeWidth="2"/>
        <text x="100" y="20" textAnchor="middle" fontSize="11" fontWeight="bold" fill="#388e3c">PÁGINA 2</text>
        
        <rect x="10" y="35" width="180" height="40" rx="3" fill="#e8f5e9" stroke="#388e3c"/>
        <text x="100" y="55" textAnchor="middle" fontSize="9" fontWeight="bold">CABEÇALHO</text>
        <text x="100" y="70" textAnchor="middle" fontSize="8">Página 2/5</text>

        <rect x="10" y="85" width="180" height="40" rx="3" fill="#b3e5fc" stroke="#0288d1"/>
        <text x="100" y="105" textAnchor="middle" fontSize="9" fontWeight="bold">NÍVEL D'ÁGUA</text>
        <text x="100" y="120" textAnchor="middle" fontSize="8">Inicial: Ausente | Final: Ausente</text>

        <rect x="10" y="135" width="180" height="200" rx="3" fill="#fff" stroke="#757575"/>
        <text x="100" y="155" textAnchor="middle" fontSize="9" fontWeight="bold">TABELA AMOSTRAS</text>
        <line x1="10" y1="165" x2="190" y2="165" stroke="#ccc"/>
        <text x="30" y="180" fontSize="7">Am</text>
        <text x="55" y="180" fontSize="7">Perf</text>
        <text x="85" y="180" fontSize="7">Prof</text>
        <text x="120" y="180" fontSize="7">1ª+2ª</text>
        <text x="155" y="180" fontSize="7">2ª+3ª</text>
        <line x1="10" y1="185" x2="190" y2="185" stroke="#ccc"/>
        <text x="30" y="200" fontSize="7">01</text>
        <text x="55" y="200" fontSize="7">TH</text>
        <text x="85" y="200" fontSize="7">0,00</text>
        <text x="120" y="200" fontSize="7">-</text>
        <text x="155" y="200" fontSize="7">-</text>
        <text x="30" y="215" fontSize="7">02</text>
        <text x="55" y="215" fontSize="7">CR</text>
        <text x="85" y="215" fontSize="7">1,00</text>
        <text x="120" y="215" fontSize="7">9</text>
        <text x="155" y="215" fontSize="7">6</text>
        <text x="30" y="230" fontSize="7">03</text>
        <text x="55" y="230" fontSize="7">CR</text>
        <text x="85" y="230" fontSize="7">2,00</text>
        <text x="120" y="230" fontSize="7">9</text>
        <text x="155" y="230" fontSize="7">11</text>
        <text x="100" y="320" textAnchor="middle" fontSize="8">LIMITE DE SONDAGEM</text>

        <rect x="10" y="340" width="180" height="30" rx="3" fill="#f5f5f5" stroke="#9e9e9e"/>
        <text x="100" y="360" textAnchor="middle" fontSize="9" fontWeight="bold">RODAPÉ</text>
      </g>

      {/* Página 3+ */}
      <g transform="translate(550, 60)">
        <rect x="0" y="0" width="200" height="380" rx="5" fill="#fff" stroke="#ff9800" strokeWidth="2"/>
        <text x="100" y="20" textAnchor="middle" fontSize="11" fontWeight="bold" fill="#ff9800">PÁGINAS 3+</text>
        
        <rect x="10" y="35" width="180" height="40" rx="3" fill="#fff3e0" stroke="#ff9800"/>
        <text x="100" y="55" textAnchor="middle" fontSize="9" fontWeight="bold">MEMORIAL FOTOGRÁFICO</text>
        <text x="100" y="70" textAnchor="middle" fontSize="8">Página 3/5</text>

        <rect x="20" y="85" width="160" height="200" rx="5" fill="#e0e0e0" stroke="#9e9e9e"/>
        <text x="100" y="185" textAnchor="middle" fontSize="40">📷</text>
        <text x="100" y="210" textAnchor="middle" fontSize="9">Ensaio SPT</text>

        <rect x="20" y="295" width="160" height="50" rx="3" fill="#e3f2fd" stroke="#1976d2"/>
        <text x="100" y="310" textAnchor="middle" fontSize="8" fontWeight="bold">Metadados EXIF</text>
        <text x="100" y="325" textAnchor="middle" fontSize="7">17/08/2025 08:47 | 23K 487805 7666179</text>
        <text x="100" y="338" textAnchor="middle" fontSize="7">Alt: 820.1m | Vel: 4.4km/h</text>

        <rect x="10" y="350" width="180" height="20" rx="3" fill="#f5f5f5" stroke="#9e9e9e"/>
        <text x="100" y="365" textAnchor="middle" fontSize="8">Imagem 1 - Ensaio SPT</text>
      </g>
    </svg>
  );

  const renderContent = () => {
    switch (activeTab) {
      case 'arquitetura': return <ArquiteturaFlow />;
      case 'auth': return <AuthFlow />;
      case 'sondagem': return <SondagemFlow />;
      case 'nbr': return <NBRFlow />;
      case 'ciclo': return <CicloFlow />;
      case 'pdf': return <PDFFlow />;
      default: return <ArquiteturaFlow />;
    }
  };

  return (
    <div className="w-full h-screen bg-gray-100 flex flex-col">
      {/* Header */}
      <div className="bg-green-600 text-white p-4 shadow-lg">
        <h1 className="text-xl font-bold text-center">🗺️ GeoSPT Manager - Fluxogramas do Sistema</h1>
        <p className="text-center text-sm opacity-80">Conforme NBR 6484:2020 | Support Solo Sondagens</p>
      </div>

      {/* Tabs */}
      <div className="bg-white shadow-md">
        <div className="flex overflow-x-auto">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`px-4 py-3 text-sm font-medium whitespace-nowrap transition-all ${
                activeTab === tab.id
                  ? 'text-green-600 border-b-2 border-green-600 bg-green-50'
                  : 'text-gray-600 hover:text-green-600 hover:bg-gray-50'
              }`}
            >
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {/* Content */}
      <div className="flex-1 p-4 overflow-auto">
        <div className="bg-white rounded-lg shadow-lg p-4 h-full">
          {renderContent()}
        </div>
      </div>

      {/* Footer */}
      <div className="bg-gray-800 text-white text-center py-2 text-xs">
        © 2025 Support Solo Sondagens Ltda - GeoSPT Manager v1.0
      </div>
    </div>
  );
};

export default FluxogramaViewer;
