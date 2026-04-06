# Sistema de Pedidos RHC - Documentacao Completa

## 1. Visao Geral

**Pedidos RHC** e um sistema interno de pedidos hospitalares que gerencia o ciclo completo de compras: da solicitacao pela unidade, passando por aprovacao, compra, entrega e recebimento. Tambem gerencia transferencias entre unidades e conciliacao de notas fiscais (NFe).

**Stack:** Laravel (PHP), Blade Templates, Bootstrap 5.3, Font Awesome 6, TomSelect, JavaScript vanilla.
**Banco:** MySQL (XAMPP). Todas as chaves primarias sao UUIDs.
**Autenticacao:** Sessao customizada (sem Laravel Auth nativo). Senhas em texto puro.

---

## 2. Estrutura do Banco de Dados (11 tabelas customizadas)

| Tabela | Funcao | Colunas principais |
|---|---|---|
| `unidades` | Unidades hospitalares | `id` (UUID), `nome` |
| `usuarios` | Usuarios do sistema | `id`, `username`, `password_hash`, `nome`, `role`, `unidade_id` (FK nullable), `permissoes` (JSON) |
| `itens` | Catalogo de produtos | `id`, `codigo`, `referencia`, `nome`, `tipo` |
| `pedidos` | Pedidos de compra | `id`, `numero_pedido` (unique), `status`, `unidade_id` (FK), `usuario_id` (FK), `fornecedor`, `atendido_por` (FK nullable), `created_at`, `updated_at` |
| `pedidos_itens` | Itens do pedido | `id`, `pedido_id` (FK cascade), `item_id` (FK), `quantidade`, `quantidade_atendida`, `quantidade_recebida`, `fornecedor`, `valor_unitario`, `observacao`, `observacao_recebimento`, `item_recebido_id` (FK nullable) |
| `aprovacoes` | Registros de aprovacao | `id`, `pedido_id` (FK cascade), `usuario_id` (FK), `created_at` |
| `pedido_alteracoes` | Trilha de auditoria | `id`, `pedido_id` (FK cascade), `usuario_id` (FK nullable), `usuario_nome`, `tipo`, `item_nome`, `item_codigo`, `valor_anterior`, `valor_novo`, `created_at` |
| `remanejamentos` | Transferencias entre pedidos | `id`, `pedido_item_origem_id` (FK cascade), `pedido_destino_id` (FK cascade), `item_id` (FK), `quantidade`, `quantidade_recebida`, `created_at` |
| `notificacoes` | Notificacoes (preparado, nao implementado na UI) | `id`, `usuario_id` (FK cascade), `pedido_id` (FK nullable), `tipo`, `mensagem`, `lida` (boolean), `created_at` |
| `notas_fiscais` | Notas fiscais (NFe) | `id`, `pedido_id` (FK cascade), `numero`, `serie`, `chave_acesso`, `data_emissao`, `valor_total`, `fornecedor_nome`, `fornecedor_cnpj`, `pdf_path`, `xml_path`, `status`, `uploaded_by` (FK nullable) |
| `notas_fiscais_itens` | Itens da nota fiscal | `id`, `nota_fiscal_id` (FK cascade), `pedido_item_id` (FK nullable), `codigo`, `descricao`, `quantidade`, `valor_unitario`, `valor_total`, `confronto` |

---

## 3. Autenticacao e Autorizacao

### 3.1 Autenticacao
- Middleware customizado `CheckAuth` (`app/Http/Middleware/CheckAuth.php`), registrado como `auth.custom`.
- Verifica `session('usuario')`; redireciona para `/login` se ausente.
- Compartilha `$usuario` com todas as views via `View::share`.
- Comparacao de senha em texto puro: `$request->password !== $user->password_hash`.
- No login, dados do usuario + unidade carregada sao armazenados na sessao como `stdClass` com propriedades: `id`, `nome`, `username`, `role`, `unidade_id`, `unidade_nome`, `permissoes`.

### 3.2 Roles (4 papeis)

| Role | Scope | Permissoes de Modulos |
|---|---|---|
| **admin** | `admin` | criar_pedido, pedidos, historico, itens, relatorios, transferencias, usuarios, notas_fiscais |
| **comprador** | `admin` | criar_pedido, pedidos, historico, itens, relatorios, transferencias |
| **aprovador** | `admin` | pedidos |
| **solicitante** | `operador` | criar_pedido, pedidos, historico, transferencias |

### 3.3 Aplicacao de Permissoes
As permissoes sao aplicadas em duas camadas:
1. **Visibilidade na navegacao:** O layout (`app.blade.php`) verifica `$usuario->permissoes['modulos']` para exibir/ocultar links do menu.
2. **Verificacao no controller:** Controllers verificam `$usuario->role` diretamente (ex: `in_array($usuario->role, ['comprador', 'admin'])`).

### 3.4 Escopo de Dados no Dashboard (o que cada role ve)
- **admin:** Ve TODOS os pedidos, sem filtro.
- **comprador:** Ve todos os pedidos EXCETO os com status "Aguardando Aprovacao".
- **aprovador:** Ve pedidos com status "Aguardando Aprovacao" + pedidos da sua propria unidade.
- **solicitante:** Ve apenas seus proprios pedidos + pedidos que possuem remanejamentos originados da sua unidade.

---

## 4. Ciclo de Vida Completo do Pedido

### 4.1 Status do Pedido (5 status)

```
Aguardando Aprovacao --> Pendente --> Em Cotacao --> Realizado --> Recebido
```

### 4.2 Transicoes de Status

| De | Para | Acionado por | Mecanismo |
|---|---|---|---|
| (novo) | Aguardando Aprovacao | Qualquer usuario com `criar_pedido` | Automatico na criacao |
| Aguardando Aprovacao | Pendente | Aprovador ou Admin | Automatico ao atingir `REQUIRED_APPROVALS` (= 1) |
| Pendente | Em Cotacao | Comprador ou Admin | Alteracao manual de status |
| Em Cotacao | Realizado | Comprador ou Admin | Alteracao manual de status |
| Realizado | Recebido | Solicitante (automatico) | Auto quando TODOS os itens tem `quantidade_recebida >= quantidade_atendida` |
| Qualquer | Qualquer | Comprador ou Admin | Via dropdown de status na pagina do pedido |

### 4.3 Fluxo Detalhado

**Etapa 1 - Criacao:**
- Usuario navega para `/pedidos/novo` (requer permissao `criar_pedido`).
- Seleciona uma `unidade` no dropdown. A unidade do usuario e pre-selecionada.
- Busca itens via autocomplete AJAX (`/itens/buscar?q=...`) que retorna ate 20 resultados.
- Adiciona itens com quantidades em tabela dinamica (JavaScript).
- Ao submeter: cria `Pedido` (status = "Aguardando Aprovacao", `numero_pedido` = null), cria entradas em `PedidoItem` (quantidade_atendida=0, quantidade_recebida=0), cria entrada de auditoria `PedidoAlteracao` tipo `pedido_criado`.

**Etapa 2 - Aprovacao:**
- Aprovador ou Admin ve o pedido no dashboard.
- Na pagina de detalhe (`/pedidos/{id}`), um alerta de aprovacao e exibido com botao.
- Ao aprovar: cria registro em `Aprovacao`. Se contagem total >= `REQUIRED_APPROVALS` (constante = 1), status muda automaticamente para "Pendente" e `PedidoAlteracao` e registrada.
- Regra: Cada usuario so pode aprovar uma vez por pedido.

**Etapa 3 - Compra (Acoes do Comprador):**
- Comprador ve o pedido (agora "Pendente") no dashboard.
- Pode alterar status para "Em Cotacao" via dropdown. Ao definir "Em Cotacao" ou "Realizado", `atendido_por` e preenchido com o usuario atual.
- Pode definir `numero_pedido` e `fornecedor` durante a alteracao de status.
- Pode "atender" itens individualmente: define `quantidade_atendida`, `valor_unitario` e `fornecedor` por item via `PedidoController::atenderItem`.

**Etapa 4 - Recebimento (Acoes do Solicitante):**
- Quando status = "Realizado", o solicitante pode receber itens.
- `canReceiveItems` e verdadeiro quando `role == 'solicitante' && status == 'Realizado'`.
- Para cada item, define `quantidade_recebida`, `observacao_recebimento` opcional, e `item_recebido_id` opcional (se o item recebido for diferente do solicitado).
- Regra de auto-conclusao: Apos cada recebimento, o sistema verifica se TODOS os itens tem `quantidade_recebida > 0 && quantidade_recebida >= quantidade_atendida`. Se sim, status muda automaticamente para "Recebido".

### 4.4 Edicao de Pedido
- Apenas **admin** e **aprovador** podem acessar a pagina de edicao (`/pedidos/{id}/editar`).
- Permite: modificar quantidades de itens existentes, adicionar novos itens, remover itens (via checkbox).
- Todas as alteracoes sao registradas em `pedido_alteracoes` com tipos: `quantidade_alterada`, `item_adicionado`, `item_removido`.

### 4.5 Adicionar/Remover Itens na Pagina de Detalhe
- Qualquer usuario autenticado pode adicionar itens via `PedidoController::addItem`.
- Qualquer usuario autenticado pode remover itens via `PedidoController::removeItem`.

---

## 5. Modulos - Documentacao Detalhada

### 5.1 Dashboard

- **Rota:** `GET /` -> `DashboardController::index`
- **View:** `resources/views/dashboard/index.blade.php`
- **Acesso:** Todos os usuarios autenticados

**Funcionalidades:**
- Cards de status no topo com contadores: Todos, Aguardando Aprovacao, Pendente, Em Cotacao, Realizado, Recebido. Cada card tem icone, numero, label e barra de progresso colorida.
- Cards de acesso rapido aos modulos (condicional por permissoes): Novo Pedido, Itens, Transferencias, Relatorios, Usuarios.
- Barra de busca: pesquisa por `numero_pedido`.
- Filtro por unidade (visivel apenas para admin e comprador).
- Tabela de pedidos: colunas = No Pedido, Status, Unidade, Solicitante, Itens (contagem), Valor Total (soma de `valor_unitario * quantidade_atendida`), Data, Acoes.
- Paginacao (20 por pagina).

---

### 5.2 Pedidos

**Controller:** `app/Http/Controllers/PedidoController.php`
**Views:** `pedidos/create.blade.php`, `pedidos/show.blade.php`, `pedidos/edit.blade.php`

**Rotas:**

| Metodo | URL | Acao | Descricao |
|---|---|---|---|
| GET | `/pedidos/novo` | create | Formulario de novo pedido |
| POST | `/pedidos` | store | Criar pedido |
| GET | `/pedidos/{id}` | show | Detalhe do pedido |
| GET | `/pedidos/{id}/editar` | edit | Formulario de edicao |
| PUT | `/pedidos/{id}` | update | Atualizar pedido |
| POST | `/pedidos/{id}/aprovar` | aprovar | Aprovar pedido |
| PUT | `/pedidos/{id}/status` | updateStatus | Alterar status |
| POST | `/pedidos/{id}/itens` | addItem | Adicionar item |
| DELETE | `/pedidos/{pedidoId}/itens/{itemId}` | removeItem | Remover item |
| PUT | `/pedidos/{pedidoId}/itens/{itemId}/receber` | receberItem | Registrar recebimento |
| PUT | `/pedidos/{pedidoId}/itens/{itemId}/atender` | atenderItem | Atender item |

**Formulario de Criacao:**
- `unidade_id` (obrigatorio, select) - pre-seleciona unidade do usuario
- Tabela de itens (dinamica, busca AJAX):
  - `itens[N][item_id]` (hidden, da selecao de busca)
  - `itens[N][quantidade]` (numero, min 1, padrao 1)

**Pagina de Detalhe exibe:**
- Resumo: numero_pedido, status, unidade, valor total estimado, solicitante, data de criacao, atendido por, contagem de aprovacoes (X/1)
- Stepper visual mostrando progresso pelos 5 status
- Alerta de aprovacao (se usuario pode aprovar)
- Formulario de alteracao de status (se comprador/admin) com dropdown
- Tabela de itens: Codigo/Referencia, Produto, Qtd Solicitada, Qtd Atendida, Qtd Recebida, Fornecedor, Valor Total
- Indicador de troca de item (quando `item_recebido_id` difere de `item_id`)
- Timeline/auditoria (de `pedido_alteracoes`, ordem decrescente)
- Secao de Notas Fiscais com botao de upload (comprador/admin apenas)
- Botao de edicao (admin/aprovador apenas)

**Formulario de Edicao:**
- Para cada item existente: `itens[{id}][quantidade]` (numero), checkbox `remover_itens[]`
- Secao de novos itens: `novos_itens[N][item_id]` (select com TomSelect), `novos_itens[N][quantidade]`

---

### 5.3 Catalogo de Itens

- **Rotas:** `GET /itens`, `POST /itens`, `GET /itens/buscar`
- **Controller:** `ItemController`
- **View:** `itens/index.blade.php`
- **Acesso:** Usuarios com permissao `itens` (admin, comprador)

**Funcionalidades:**
- Listar todos os itens com busca (por nome, codigo ou referencia) e filtro por tipo
- Paginacao (50 por pagina)
- Botao "Novo Item" (admin/comprador) abre modal
- Campos do formulario: `codigo` (obrigatorio), `nome` (obrigatorio), `tipo` (obrigatorio, com datalist dos tipos existentes), `referencia` (opcional)
- Endpoint AJAX (`/itens/buscar?q=...`) retorna array JSON de ate 20 itens

**Dados atuais:** 1.600 produtos em 7 categorias:
- B.BRAUN: 48 itens
- FRALDAS: 9 itens
- LIFETEX-SURGITEXTIL: 53 itens
- MAT. MED. HOSPITALAR: 755 itens
- MED. ONCO: 36 itens
- MED. ONCO CONTR. LIBBS.: 42 itens
- MEDICAMENTOS: 657 itens

---

### 5.4 Gerenciamento de Usuarios

- **Rotas:** `GET /usuarios`, `POST /usuarios`, `PUT /usuarios/{id}`, `DELETE /usuarios/{id}`
- **Controller:** `UsuarioController`
- **View:** `usuarios/index.blade.php`
- **Acesso:** Apenas admin

**Funcionalidades:**
- Listar todos os usuarios: nome, username, unidade, role
- Criar usuario (modal): `nome`, `username`, `password` (min 4 chars), `role` (select: admin/comprador/aprovador/solicitante), `unidade_id` (opcional)
- Editar usuario (modal por usuario): mesmos campos, senha opcional
- Excluir usuario (nao pode excluir a si mesmo) com confirmacao
- Na criacao/atualizacao, `permissoes` JSON e auto-construido por `buildPermissoes()` baseado no role

---

### 5.5 Historico

- **Rota:** `GET /historico`
- **Controller:** `HistoricoController`
- **View:** `historico/index.blade.php`
- **Acesso:** Usuarios com permissao `historico` (admin, comprador, solicitante)

**Funcionalidades:**
- Historico completo com filtros: busca (numero_pedido ou nome do item), status, unidade, data_inicio, data_fim
- Sem filtro de escopo por role - todos com acesso veem todos os pedidos
- Colunas: No Pedido, Data Solicitacao, Status, Unidade, Solicitante, Qtd. Itens, Acoes
- Paginacao (20 por pagina)

---

### 5.6 Relatorios

- **Rota:** `GET /relatorios`
- **Controller:** `RelatorioController`
- **View:** `relatorios/index.blade.php`
- **Acesso:** Usuarios com permissao `relatorios` (admin, comprador)

**Funcionalidades:**
- Cards de KPI:
  - Pedidos Realizados (contagem Realizado + Recebido)
  - Itens Divergentes (itens com `item_recebido_id` definido ou `observacao_recebimento` definido)
  - Taxa de Atendimento (total_atendido / total_solicitado em percentual)
  - Valor Total Movimentado (soma de `quantidade * valor_unitario`)
- Pedidos por Status: lista com contagens
- Top 10 Itens Mais Solicitados: tabela com codigo, nome, total_quantidade
- Volume por Unidade: barras de progresso horizontais com contagem de pedidos por unidade
- Estatisticas de Remanejamentos: total de transferencias e total de unidades transferidas
- Botao de imprimir

---

### 5.7 Transferencias (Remanejamentos)

- **Rotas:** `GET /transferencias`, `POST /transferencias`
- **Controller:** `TransferenciaController`
- **View:** `transferencias/index.blade.php`
- **Acesso:** Usuarios com permissao `transferencias` (admin, comprador, solicitante)

**Escopo de dados:** Solicitantes veem apenas transferencias envolvendo sua unidade (como origem ou destino). Admin/comprador veem todas.

**Funcionalidades:**
- Listar remanejamentos: Data, Pedido Origem (com unidade), Pedido Destino (com unidade), Item Transportado, Qtd. Solicitada
- Botao "Nova Transferencia" (admin/comprador) abre modal grande
- Formulario de transferencia (modal):
  - **Origem:** Selecionar pedido origem (TomSelect, exclui Recebido/Cancelado), selecionar item do pedido (carregado dinamicamente via atributo `data-itens`), informar quantidade
  - **Destino:** Selecionar pedido destino (TomSelect, exclui Recebido/Cancelado)
- Ao submeter: cria registro `Remanejamento` vinculando o `pedido_item` origem ao `pedido` destino
- Pedidos com status "Recebido" ou "Cancelado" sao excluidos das opcoes

**Observacao:** A transferencia NAO cria automaticamente um `PedidoItem` no pedido destino nem reduz quantidades no pedido origem. E um mecanismo de registro/rastreamento.

---

### 5.8 Notas Fiscais (dentro do Pedido)

- **Rotas:** `POST /pedidos/{id}/notas-fiscais`, `PUT /notas-fiscais/{id}/status`, `POST /notas-fiscais/parse-xml`
- **Controller:** `NotaFiscalController`
- **Acesso:** Exibido na pagina de detalhe do pedido. Upload visivel para comprador/admin.

**Funcionalidades:**
- Upload e parsing de XML de NFe brasileira
- Dados extraidos: numero, serie, chave_acesso (do atributo `infNFe@Id`), data_emissao, valor_total, fornecedor_nome, fornecedor_cnpj
- Extracao de itens dos elementos `<det><prod>`: cProd, xProd, qCom, vUnCom, vProd
- Auto-confrontacao: compara itens da NF com itens do pedido por `codigo` (case-insensitive, trimmed):
  - `conforme` - quantidades batem (NF qty == quantidade_atendida ou quantidade)
  - `divergente_qtd` - codigo bate mas quantidades diferem
  - `nao_encontrado` - codigo nao encontrado no pedido
- Status da NF: `pendente` -> `conferida` ou `divergente` (alterado manualmente)
- XML armazenado em `storage/app/notas_fiscais/xml/`
- Endpoint parse-only: `POST /notas-fiscais/parse-xml` retorna dados parseados como JSON sem persistir
- Multiplas NFs podem ser vinculadas a um pedido

---

### 5.9 Bionexo (Integracao)

- **Rotas:** `GET /bionexo`, `POST /bionexo/convert`, `POST /bionexo/export`
- **Controller:** `BionexoController`
- **View:** `bionexo/index.blade.php`
- **Acesso:** Funcionalidade integrada ao fluxo do pedido (sem link no menu)

**Funcionalidades:**
- Upload de PDF via drag-and-drop ou clique
- Envia PDF para microservico Python externo em `http://localhost:5000/converter`
- Exibe resultados em tabela: Codigo, Quantidade, Fornecedor, Valor Unitario
- Botao exportar para Excel: envia dados para `http://localhost:5000/export`, baixa arquivo `.xlsx`
- **Dependencia:** Requer servico Flask/Python separado rodando na porta 5000

---

### 5.10 Ajuda

- **Rota:** `GET /ajuda`
- **Controller:** `AjudaController`
- **View:** `ajuda/index.blade.php`
- **Acesso:** Todos os usuarios autenticados (sempre visivel no menu)

**Funcionalidades:**
- FAQ em accordion com 6 topicos:
  1. Como criar um pedido
  2. Fluxo de aprovacao
  3. Como receber itens
  4. Como fazer remanejamento
  5. Relatorios e exportacoes
  6. Notas fiscais
- Card de contato de suporte com email para `suporte@redecasa.com.br`

---

## 6. Fluxo de Aprovacao em Detalhe

1. Pedido e criado com status "Aguardando Aprovacao".
2. Usuarios com role `aprovador` ou `admin` veem banner de alerta de aprovacao na pagina de detalhe.
3. Flag `canApprove` e verdadeiro quando: role e aprovador/admin E usuario nao aprovou este pedido E status e "Aguardando Aprovacao".
4. Ao clicar "Aprovar Pedido", POST para `/pedidos/{id}/aprovar` cria registro `Aprovacao`.
5. Sistema verifica se `aprovacoes.count() >= REQUIRED_APPROVALS` (constante = 1).
6. Se atingido, status muda automaticamente para "Pendente" e entrada de auditoria e criada.
7. Cada usuario so pode aprovar uma vez (verificacao de duplicata por `usuario_id`).

**Constante importante:** `PedidoController::REQUIRED_APPROVALS = 1` - apenas uma aprovacao e necessaria. Pode ser alterada para exigir multiplas aprovacoes.

---

## 7. Sistema de Notificacoes

O banco inclui tabela `notificacoes` com campos: `usuario_id`, `pedido_id`, `tipo`, `mensagem`, `lida`. O model `Notificacao` existe com relacionamentos definidos, e `Usuario` tem relacao `notificacoes()`. Porem, **nao existe controller, rota ou view que crie, leia ou exiba notificacoes**. Funcionalidade preparada mas nao implementada na interface.

---

## 8. Trilha de Auditoria

Toda acao significativa em um pedido e registrada em `pedido_alteracoes`:

| Tipo (`tipo`) | Quando criado | Dados armazenados |
|---|---|---|
| `pedido_criado` | Pedido criado | - |
| `status_alterado` | Status alterado (aprovacao, manual, auto-recebimento) | `valor_anterior` = status antigo, `valor_novo` = status novo |
| `item_adicionado` | Item adicionado ao pedido | `item_nome`, `item_codigo`, `valor_novo` = quantidade |
| `quantidade_alterada` | Quantidade de item editada | `item_nome`, `item_codigo`, `valor_anterior`, `valor_novo` |
| `item_removido` | Item removido do pedido | `item_nome`, `item_codigo` |

Todas as entradas incluem `usuario_id`, `usuario_nome` e `created_at`. Exibidas como timeline na pagina de detalhe do pedido.

---

## 9. Dados Atuais no Banco

| Tabela | Registros |
|---|---|
| Unidades | 10 hospitais Casa |
| Usuarios | 2 (admin + comprador) |
| Itens | 1.600 produtos (7 categorias) |
| Pedidos | 0 (pronto para uso) |

### Unidades

| Nome |
|---|
| HOSPITAL CASA EVANGELICO |
| HOSPITAL CASA SAO BERNARDO |
| HOSPITAL CASA DE PORTUGAL |
| HOSPITAL CASA MENSSANA |
| HOSPITAL CASA ILHA DO GOVERNADOR |
| HOSPITAL CASA RIO LARANJEIRAS |
| HOSPITAL CASA RIO BOTAFOGO |
| OFTALMOCASA |
| HOSPITAL CASA SANTA CRUZ |
| HOSPITAL CASA PREMIUM |

### Credenciais

| Usuario | Senha | Nome | Role |
|---|---|---|---|
| `admin` | `Rc2026#@` | Administrador do Sistema | admin |
| `comprador` | `comprador123` | Comprador RHC | comprador |

### Categorias de Itens

| Tipo | Quantidade |
|---|---|
| B.BRAUN | 48 |
| FRALDAS | 9 |
| LIFETEX-SURGITEXTIL | 53 |
| MAT. MED. HOSPITALAR | 755 |
| MED. ONCO | 36 |
| MED. ONCO CONTR. LIBBS. | 42 |
| MEDICAMENTOS | 657 |
| **Total** | **1.600** |

---

## 10. Indice Completo de Arquivos

### Controllers (12 arquivos)
- `app/Http/Controllers/Controller.php` - Base abstrata
- `app/Http/Controllers/AuthController.php` - Login/logout
- `app/Http/Controllers/DashboardController.php` - Dashboard principal
- `app/Http/Controllers/PedidoController.php` - CRUD de pedidos + acoes
- `app/Http/Controllers/ItemController.php` - Catalogo de produtos
- `app/Http/Controllers/UsuarioController.php` - Gerenciamento de usuarios
- `app/Http/Controllers/RelatorioController.php` - Relatorios
- `app/Http/Controllers/TransferenciaController.php` - Transferencias
- `app/Http/Controllers/BionexoController.php` - Integracao Bionexo
- `app/Http/Controllers/NotaFiscalController.php` - Gerenciamento de NFs
- `app/Http/Controllers/AjudaController.php` - Ajuda/FAQ
- `app/Http/Controllers/HistoricoController.php` - Historico de pedidos

### Models (12 arquivos)
- `app/Models/User.php` - User padrao Laravel (nao utilizado)
- `app/Models/Usuario.php` - Model de usuario ativo
- `app/Models/Unidade.php` - Unidade hospitalar
- `app/Models/Item.php` - Item do catalogo
- `app/Models/Pedido.php` - Pedido de compra
- `app/Models/PedidoItem.php` - Item do pedido
- `app/Models/Aprovacao.php` - Registro de aprovacao
- `app/Models/PedidoAlteracao.php` - Entrada de auditoria
- `app/Models/Remanejamento.php` - Registro de transferencia
- `app/Models/Notificacao.php` - Notificacao (model existe, nao usado na UI)
- `app/Models/NotaFiscal.php` - Cabecalho da nota fiscal
- `app/Models/NotaFiscalItem.php` - Item da nota fiscal

### Views (15 arquivos)
- `resources/views/layouts/app.blade.php` - Layout principal com navbar
- `resources/views/components/flash-messages.blade.php` - Vazio (tratado no layout)
- `resources/views/auth/login.blade.php` - Pagina de login (standalone)
- `resources/views/dashboard/index.blade.php` - Dashboard
- `resources/views/pedidos/create.blade.php` - Formulario de novo pedido
- `resources/views/pedidos/show.blade.php` - Detalhe do pedido
- `resources/views/pedidos/edit.blade.php` - Formulario de edicao
- `resources/views/historico/index.blade.php` - Historico de pedidos
- `resources/views/itens/index.blade.php` - Catalogo de itens
- `resources/views/usuarios/index.blade.php` - Gerenciamento de usuarios
- `resources/views/relatorios/index.blade.php` - Relatorios
- `resources/views/transferencias/index.blade.php` - Transferencias
- `resources/views/bionexo/index.blade.php` - Conversor Bionexo
- `resources/views/ajuda/index.blade.php` - Pagina de ajuda
- `resources/views/welcome.blade.php` - Welcome padrao Laravel (nao usado)

### Middleware
- `app/Http/Middleware/CheckAuth.php` - Verificacao de autenticacao por sessao

### Rotas
- `routes/web.php` - Todas as rotas (3 publicas de auth + 22 protegidas)

### Migrations (11 customizadas + 3 padrao Laravel)
- `database/migrations/2024_01_01_000001` ate `000011` - Todas as tabelas customizadas

---

## 11. Regras de Negocio e Observacoes Importantes

1. **Senhas em texto puro:** Autenticacao compara senhas como strings. Sem bcrypt ou hashing.
2. **Limite de aprovacao configuravel:** A constante `REQUIRED_APPROVALS = 1` pode ser alterada para exigir mais aprovacoes.
3. **Status auto-recebido:** "Recebido" e o unico status que pode ser acionado automaticamente (quando todos os itens sao totalmente recebidos).
4. **Numero do pedido nao e auto-gerado:** `numero_pedido` e criado como `null` e deve ser definido manualmente via formulario de alteracao de status.
5. **Sistema de notificacoes e um stub:** Tabela e model existem mas nenhum codigo cria ou le notificacoes.
6. **Bionexo depende de servico externo:** Requer microservico Python na porta 5000.
7. **Transferencias sao apenas registro:** Criar um remanejamento nao modifica quantidades nos pedidos origem ou destino.
8. **Historico nao tem escopo de dados:** Diferente do dashboard, a pagina de historico mostra todos os pedidos para qualquer usuario com acesso.
9. **Adicionar/remover itens na pagina show nao tem verificacao de role:** Qualquer usuario autenticado pode adicionar ou remover itens de qualquer pedido que possa visualizar.
10. **Todos os IDs sao UUIDs:** Toda tabela usa chave primaria UUID com `$incrementing = false`.
11. **Calculo de valor:** Valor total do pedido = SUM(`valor_unitario * quantidade_atendida`) - representa valor atendido, nao valor solicitado.

---

## 12. Como Executar

1. Iniciar MySQL no XAMPP Control Panel
2. No terminal, na pasta do projeto (`pedidos_rhc_php`):
   ```
   php artisan serve --host=127.0.0.1 --port=8000
   ```
3. Acessar `http://127.0.0.1:8000/login`
4. Login: `admin` / `Rc2026#@`
