# Documentação Técnica do Desenvolvedor

## Visão Geral
Este projeto é a interface administrativa do ClickResto (PHP + Bootstrap), consumindo uma API REST. O fluxo de autenticação usa JWT retornado por `/auth/login`, armazenado na sessão PHP.

## Requisitos
- PHP com extensão cURL habilitada.
- Servidor web com suporte a sessão PHP.
- Acesso à API ClickResto.

## Configuração
1. Configure o arquivo `.env` na raiz do projeto.
```env
API_BASE_URL=http://localhost/clickresto-api/public/api/v1
```
2. Acesse `login.php` e autentique com e-mail e senha.

## Autenticação e Sessão
- Login chama `POST /auth/login`.
- Token é salvo em `$_SESSION['token']`.
- Dados do usuário são obtidos via `GET /auth/me`.
- Empresa e usuário são extraídos do token e persistidos na sessão.
- Se a API retornar `Unauthenticated.` ou HTTP 401, o sistema encerra a sessão e redireciona para `login.php` (ver `index.php`).

## Padrão de Integração com API
Cada tela em `pages/paginas/*.php` contém:
- Função local `apiRequest...()` com cURL.
- Tratamento de resposta e mensagens de sucesso/erro.
- Renderização de tabela e modais de CRUD.

Exemplo de headers padrão:
```txt
Accept: application/json
Authorization: Bearer <token>
```

## Estrutura de Páginas
As páginas são carregadas via `index.php` usando o roteador em `classe/verURL.php`. A query `?paginas=` define qual tela é exibida.

## Padrões de CRUD
- Listagem: `GET /recurso`
- Criação: `POST /recurso`
- Atualização: `PUT/PATCH /recurso/{id}`
- Exclusão: `DELETE /recurso/{id}`

## Fluxos Especiais
- Pedidos: `POST /pedidos/abrir`, envio para cozinha, fechamento.
- Cozinha: listagem de itens e pedidos agrupados.
- Comanda: numeração automática por dia (servidor).
- Caixa: abertura e fechamento com saldo inicial/final.

## Dicas para Evolução
- Evite lógica de API em JavaScript. O padrão atual usa PHP + POST/GET.
- Mantenha os modais com `data-*` para preencher edição/exclusão.
- Sempre validar dados antes de enviar à API.

