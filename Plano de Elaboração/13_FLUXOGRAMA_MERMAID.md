## Fluxograma 1: Visão Geral do Sistema

```mermaid
flowchart TB
    subgraph USUARIOS["👥 USUÁRIOS"]
        U1[🖥️ Browser Web]
        U2[📱 Mobile App]
        U3[🔧 API Client]
        U4[📊 Excel Import]
    end

    subgraph NGINX["🌐 NGINX"]
        N1[Load Balancer]
        N2[SSL/TLS]
        N3[Rate Limiting]
    end

    subgraph APP["🚀 CODEIGNITER 4"]
        subgraph FILTERS["Filtros"]
            F1[JWT Auth]
            F2[CORS]
        end
        
        subgraph CONTROLLERS["Controllers"]
            C1[API Controllers]
            C2[Web Controllers]
            C3[Auth Controller]
        end
        
        subgraph SERVICES["Services"]
            S1[PDFService]
            S2[ExifService]
            S3[ImportService]
        end
        
        subgraph LIBRARIES["Libraries NBR"]
            L1[SPTCalculator]
            L2[NBRValidator]
            L3[SoloClassificador]
        end
        
        subgraph MODELS["Models"]
            M1[SondagemModel]
            M2[AmostraModel]
            M3[CamadaModel]
            M4[FotoModel]
        end
        
        subgraph REPOS["Repositories"]
            R1[SondagemRepository]
        end
    end

    subgraph DATABASE["🗄️ MySQL 8.0"]
        DB1[(empresas)]
        DB2[(projetos)]
        DB3[(obras)]
        DB4[(sondagens)]
        DB5[(amostras)]
        DB6[(camadas)]
        DB7[(fotos)]
        DB8[(audit_log)]
    end

    U1 & U2 & U3 & U4 --> NGINX
    NGINX --> FILTERS
    FILTERS --> CONTROLLERS
    CONTROLLERS --> SERVICES
    CONTROLLERS --> LIBRARIES
    CONTROLLERS --> MODELS
    MODELS --> REPOS
    REPOS --> DATABASE

    style USUARIOS fill:#e1f5fe
    style NGINX fill:#fff3e0
    style APP fill:#e8f5e9
    style DATABASE fill:#fce4ec
```

---

## Fluxograma 2: Autenticação JWT

```mermaid
sequenceDiagram
    participant U as 👤 Usuário
    participant A as 🔐 AuthController
    participant M as 📋 UsuarioModel
    participant J as 🎫 JWT
    participant DB as 🗄️ Database

    U->>A: POST /auth/login {email, password}
    A->>M: findByEmail(email)
    M->>DB: SELECT * FROM usuarios
    DB-->>M: Dados do usuário
    M-->>A: Usuario encontrado
    
    A->>A: password_verify()
    
    alt Credenciais válidas
        A->>J: encode(payload, secret)
        J-->>A: Token JWT
        A-->>U: {token, usuario, expira_em}
    else Credenciais inválidas
        A-->>U: 401 Unauthorized
    end

    Note over U,A: Requisições subsequentes

    U->>A: GET /api/sondagens<br/>Authorization: Bearer {token}
    A->>J: decode(token, secret)
    
    alt Token válido
        J-->>A: Payload decodificado
        A-->>U: 200 {dados}
    else Token inválido/expirado
        J-->>A: Exception
        A-->>U: 401 Token inválido
    end
```

---

## Fluxograma 3: Cadastro de Sondagem

```mermaid
flowchart TD
    A[🏠 Dashboard] --> B[➕ Nova Sondagem]
    B --> C{📝 Formulário}
    
    C --> D[Dados Básicos<br/>Código, Obra, Data, Sondador]
    C --> E[Coordenadas<br/>Este, Norte, Cota, Profundidade]
    C --> F[Equipamento NBR<br/>Peso: 65kgf, Altura: 75cm]
    
    D & E & F --> G[💾 Salvar]
    G --> H{✅ Validação}
    
    H -->|Válido| I[📥 INSERT MySQL]
    H -->|Inválido| J[❌ Erro 400<br/>Mensagens de validação]
    J --> C
    
    I --> K[📋 Callbacks]
    K --> L[setDefaults<br/>version = 1<br/>status = rascunho]
    L --> M[logAudit<br/>Registra criação]
    
    M --> N[✅ Response 201]
    N --> O[🔄 Redireciona<br/>/admin/sondagens/ID]
    
    style A fill:#e3f2fd
    style N fill:#c8e6c9
    style J fill:#ffcdd2
```

---

## Fluxograma 4: Cadastro de Amostras SPT

```mermaid
flowchart TD
    A[📋 Sondagem Criada] --> B[📊 Tabela de Amostras]
    
    B --> C[Linha 1: TH - 0,00m]
    B --> D[Linha 2: CR - 1,00m<br/>Golpes: 5, 4, 2]
    B --> E[Linha 3: CR - 2,00m<br/>Golpes: 4, 5, 6]
    B --> F[...]
    
    C & D & E & F --> G[💾 Salvar Todas]
    G --> H[POST /amostras/batch]
    
    H --> I{🔄 Loop cada amostra}
    
    I --> J[✅ Validar dados]
    J --> K[🧮 Calcular NSPT]
    K --> L[nspt_1a_2a = golpes_1a + golpes_2a<br/>nspt_2a_3a = golpes_2a + golpes_3a]
    L --> M[📥 INSERT MySQL]
    M --> I
    
    I -->|Fim| N[📊 Resultado]
    N --> O[✅ Criadas: 13<br/>❌ Erros: 0]
    
    style A fill:#e3f2fd
    style L fill:#fff9c4
    style O fill:#c8e6c9
```

---

## Fluxograma 5: Upload de Fotos com EXIF

```mermaid
flowchart TD
    A[📷 Upload Fotos] --> B{📁 Validar arquivos}
    
    B -->|Válido| C[🚚 Mover para<br/>writable/uploads/fotos/]
    B -->|Inválido| D[❌ Erro:<br/>Tipo/tamanho inválido]
    
    C --> E[🔍 ExifService]
    
    E --> F[exif_read_data]
    F --> G[📍 GPS Latitude/Longitude]
    F --> H[⛰️ Altitude]
    F --> I[🚗 Velocidade]
    F --> J[📅 Data/Hora]
    
    G --> K[🔄 Converter para Decimal]
    K --> L[🗺️ Converter para UTM<br/>SIRGAS2000]
    
    L --> M[Este: 487805.00<br/>Norte: 7666179.00<br/>Zona: 23K]
    
    H & I & J & M --> N[📥 INSERT MySQL<br/>tabela: fotos]
    
    N --> O[✅ Response<br/>3 fotos enviadas]
    
    style A fill:#e3f2fd
    style E fill:#fff9c4
    style L fill:#e1bee7
    style O fill:#c8e6c9
```

---

## Fluxograma 6: Validação NBR 6484:2020

```mermaid
flowchart TD
    A[🔍 Verificar Conformidade] --> B[NBRValidator]
    
    B --> C[1️⃣ Equipamento<br/>Peso: 20%]
    B --> D[2️⃣ Coordenadas<br/>Peso: 15%]
    B --> E[3️⃣ Camadas<br/>Peso: 15%]
    B --> F[4️⃣ Amostras<br/>Peso: 20%]
    B --> G[5️⃣ Fotos<br/>Peso: 15%]
    B --> H[6️⃣ Responsável<br/>Peso: 10%]
    
    C --> C1{peso = 65kgf?<br/>altura = 75cm?<br/>∅ext = 50.8±0.2?}
    D --> D1{Este OK?<br/>Norte OK?<br/>Datum OK?}
    E --> E1{≥1 camada?<br/>Continuidade?}
    F --> F1{≥1 amostra?<br/>NSPT correto?}
    G --> G1{Foto ensaio?<br/>Foto amostrador?<br/>Foto amostra?}
    H --> H1{Nome OK?<br/>CREA válido?}
    
    C1 & D1 & E1 & F1 & G1 & H1 --> I[🧮 Calcular Score Total]
    
    I --> J{Score = 100?}
    
    J -->|Sim| K[✅ CONFORME<br/>PDF liberado]
    J -->|Não| L[❌ NÃO CONFORME<br/>Lista de erros]
    
    style A fill:#e3f2fd
    style I fill:#fff9c4
    style K fill:#c8e6c9
    style L fill:#ffcdd2
```

---

## Fluxograma 7: Geração de PDF

```mermaid
flowchart TD
    A[📄 Gerar PDF] --> B[Carregar dados completos]
    B --> C{✅ Score = 100?}
    
    C -->|Não| D[❌ Erro 400<br/>Não conforme NBR]
    C -->|Sim| E[PDFService::gerarRelatorio]
    
    E --> F[📄 Página 1]
    E --> G[📄 Página 2]
    E --> H[📄 Páginas 3+]
    
    F --> F1[Cabeçalho<br/>Logo + Empresa]
    F --> F2[Dados Técnicos<br/>Equipamento + Coords]
    F --> F3[Gráfico N30<br/>+ Perfil Estratigráfico]
    F --> F4[Rodapé<br/>Observações + RT]
    
    G --> G1[Cabeçalho]
    G --> G2[Nível d'Água]
    G --> G3[Tabela Completa<br/>de Amostras]
    G --> G4[Rodapé]
    
    H --> H1[Cabeçalho<br/>Memorial Fotográfico]
    H --> H2[Foto + Metadados<br/>Data, Coords, Altitude]
    H --> H3[Rodapé]
    
    F1 & F2 & F3 & F4 & G1 & G2 & G3 & G4 & H1 & H2 & H3 --> I[💾 Salvar PDF]
    
    I --> J[SPT_SP-01_20250817.pdf]
    J --> K[📥 Download]
    
    style A fill:#e3f2fd
    style D fill:#ffcdd2
    style K fill:#c8e6c9
```

---

## Fluxograma 8: Ciclo de Vida da Sondagem

```mermaid
stateDiagram-v2
    [*] --> RASCUNHO: Criar sondagem
    
    RASCUNHO --> RASCUNHO: Editar dados
    RASCUNHO --> EM_ANALISE: Completar + Score=100
    
    EM_ANALISE --> APROVADO: Engenheiro aprova
    EM_ANALISE --> REJEITADO: Engenheiro rejeita
    
    REJEITADO --> RASCUNHO: Corrigir erros
    
    APROVADO --> ARQUIVADO: Admin arquiva
    APROVADO --> [*]: PDF disponível
    
    note right of RASCUNHO
        • Editável
        • Sem PDF
    end note
    
    note right of EM_ANALISE
        • Editável
        • Aguarda aprovação
    end note
    
    note right of APROVADO
        • Bloqueado
        • PDF liberado
        • Versão final
    end note
    
    note right of REJEITADO
        • Editável
        • Motivo informado
    end note
```

---

## Fluxograma 9: Processo Completo de Aprovação

```mermaid
sequenceDiagram
    participant O as 👷 Operador
    participant S as 🖥️ Sistema
    participant E as 👨‍💼 Engenheiro

    O->>S: Cadastra sondagem
    O->>S: Adiciona amostras
    O->>S: Upload fotos
    O->>S: Adiciona camadas
    
    O->>S: Solicita verificação
    
    S->>S: NBRValidator<br/>Validação automática
    
    alt Score < 100
        S-->>O: Lista de erros
        O->>S: Corrige erros
        O->>S: Reenvia
    else Score = 100
        S->>S: Status: em_analise
        S->>E: Notificação
        
        E->>S: Revisa sondagem
        
        alt Aprova
            E->>S: POST /aprovar
            S->>S: Status: aprovado
            S-->>O: PDF disponível
        else Rejeita
            E->>S: POST /rejeitar<br/>{motivo: "..."}
            S->>S: Status: rejeitado
            S-->>O: Notificação + motivo
            O->>S: Corrige e reenvia
        end
    end
```

---

## Fluxograma 10: Diagrama ER Simplificado

```mermaid
erDiagram
    EMPRESAS ||--o{ PROJETOS : possui
    EMPRESAS ||--o{ USUARIOS : emprega
    EMPRESAS ||--o{ RESPONSAVEIS_TECNICOS : contrata
    
    PROJETOS ||--o{ OBRAS : contem
    
    OBRAS ||--o{ SONDAGENS : possui
    
    RESPONSAVEIS_TECNICOS ||--o{ SONDAGENS : assina
    
    SONDAGENS ||--o{ CAMADAS : possui
    SONDAGENS ||--o{ AMOSTRAS : possui
    SONDAGENS ||--o{ FOTOS : possui
    
    USUARIOS ||--o{ AUDIT_LOG : gera
    
    EMPRESAS {
        int id PK
        string razao_social
        string cnpj
        string endereco
    }
    
    SONDAGENS {
        int id PK
        int obra_id FK
        int resp_tecnico_id FK
        string codigo
        date data_execucao
        decimal coord_este
        decimal coord_norte
        decimal prof_final
        string status
        int score_nbr
    }
    
    AMOSTRAS {
        int id PK
        int sondagem_id FK
        int numero
        string tipo_perf
        decimal prof_inicial
        int golpes_1a
        int golpes_2a
        int golpes_3a
        int nspt_2a_3a
    }
    
    FOTOS {
        int id PK
        int sondagem_id FK
        string arquivo
        string tipo_foto
        decimal latitude
        decimal longitude
        decimal coord_este
        decimal coord_norte
    }
```

---

## Como Visualizar os Diagramas Mermaid

1. **VS Code**: Instale a extensão "Markdown Preview Mermaid Support"
2. **Online**: Cole o código em [mermaid.live](https://mermaid.live)
3. **GitHub**: Os diagramas renderizam automaticamente em arquivos .md
4. **Notion**: Suporta blocos Mermaid nativamente

---

**© 2025 Support Solo Sondagens Ltda**
