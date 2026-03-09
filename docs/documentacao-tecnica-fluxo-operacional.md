# Documentação Técnica – Fluxo Operacional

## 1. Autenticação
1. Usuário acessa `login.php`.
2. Sistema envia `POST /auth/login`.
3. Token é salvo em sessão.
4. Sistema consulta `GET /auth/me` para dados do usuário.
5. Caso token expire, redireciona para `login.php`.

## 2. Cadastro do Cardápio
1. Criar categorias em `Categorias`.
2. Criar produtos vinculando à categoria em `Produtos`.
3. Definir opções e itens de opção quando necessário.

## 3. Operação de Salão
1. Criar mesas.
2. Criar comandas (numeração automática por dia via API).
3. Abrir pedido com tipo `mesa`, `balcao`, `delivery`, `comanda` ou `retirada`.

## 4. Pedido e Cozinha
1. Adicionar itens ao pedido.
2. Enviar pedido para cozinha.
3. Na tela de cozinha, visualizar pedidos agrupados por estação.
4. Na tela de cozinha, mover status do pedido de `aberto` para `preparo`.
5. Na tela de cozinha, mover status do pedido de `preparo` para `pronto`.

## 5. Pagamento
1. Selecionar forma de pagamento.
2. Registrar pagamento no pedido.
3. Fechar pedido.

## 6. Caixa
1. Abrir caixa com saldo inicial.
2. Registrar movimentos de entrada e saída.
3. Fechar caixa com saldo final.
