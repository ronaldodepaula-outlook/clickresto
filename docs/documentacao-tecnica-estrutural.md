# Documentação Técnica Estrutural do Projeto

## Estrutura de Pastas
- `index.php`: bootstrap da aplicação, valida sessão e carrega páginas.
- `login.php`: autenticação e criação da sessão.
- `logout.php`: finaliza sessão.
- `.env`: configuração de ambiente (API).
- `classe/`: utilitários e roteador de páginas.
- `pages/`: componentes e telas.
- `assets/`, `dist/`: dependências visuais e JS/CSS.
- `docs/`: documentação do projeto.

## Arquivos Principais
- `index.php`: valida token, consulta `/auth/me`, define perfil e carrega layout.
- `classe/env.php`: carregamento de variáveis de ambiente.
- `classe/verURL.php`: roteamento interno por `?paginas=`.

## Componentes de Layout
Local: `pages/componentes/`
- `head.php`: metadados e CSS base.
- `navbar.php`: topo.
- `sidebar.php`: menu lateral.

## Telas Operacionais (principais)
Local: `pages/paginas/`
- `categorias.php`
- `produtos.php`
- `mesas.php`
- `comandas.php`
- `pedidos.php`
- `cozinha.php`
- `pagamentos.php`
- `caixa.php`
- `clientes.php`
- `produto_opcoes.php`
- `produto_opcao_itens.php`

## Fluxo de Carregamento
1. `index.php` valida sessão e token.
2. `verURL.php` resolve a tela por `?paginas=`.
3. A tela chama a API e renderiza o HTML com modais e tabelas.

## Convenções de Dados
- Token JWT em sessão `$_SESSION['token']`.
- Empresa e usuário guardados em `$_SESSION['empresa_id']` e `$_SESSION['user_id']`.
- Mensagens de erro/sucesso renderizadas como alertas.

