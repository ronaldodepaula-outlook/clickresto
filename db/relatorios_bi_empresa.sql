/*
Script de relatorios analiticos para a base resto_saas

Como usar
1. Ajuste os parametros abaixo.
2. Execute o script inteiro no phpMyAdmin ou no cliente MySQL.
3. O filtro por empresa e obrigatorio em todos os blocos.

Regras de negocio adotadas
- Faturamento oficial: tb_pedidos.total apenas para pedidos com status = 'fechado'.
- Data de venda: COALESCE(tb_pedidos.updated_at, tb_pedidos.update_at, tb_pedidos.criado_em).
- Data operacional do pedido: tb_pedidos.criado_em.
- Receita por item: tb_pedido_itens.quantidade * tb_pedido_itens.preco.
- Margem estimada: receita do item menos quantidade * custo atual do produto.
- Pagamento liquido estimado: tb_pagamentos.valor - tb_pagamentos.troco.
- Atendimento de mesa por usuario: usuarios ativos da empresa com perfil tb_perfis.nome = 'user'.

Limitacoes do modelo
- tb_entregas nao possui timestamps proprios. Os relatorios de delivery usam a data do pedido.
- tb_caixas.aberto_em e tb_caixas.fechado_em usam ON UPDATE CURRENT_TIMESTAMP. Para analise historica, prefira created_at.
- Existem indicios de divergencia entre pedidos, itens e pagamentos em alguns registros. Por isso:
  * faturamento oficial usa tb_pedidos.total;
  * mix de produto usa tb_pedido_itens;
  * mix de pagamento usa tb_pagamentos.
*/

-- =====================================================================
-- PARAMETROS
-- =====================================================================

SET @empresa_id := 4;

-- dia | semana | mes | ano | periodo
SET @tipo_filtro := 'periodo';

-- dia | semana | mes | ano | periodo
-- Use 'periodo' para consolidar tudo em uma unica linha.
SET @tipo_agrupamento := 'dia';

-- Parametros de referencia
SET @dia_ref := CURDATE();
SET @ano_ref := YEAR(CURDATE());
SET @mes_ref := MONTH(CURDATE());
SET @semana_ref := WEEK(CURDATE(), 1);

-- Quando @tipo_filtro = 'periodo'
SET @data_inicio := CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-01'), ' 00:00:00');
SET @data_fim := CONCAT(CURDATE(), ' 23:59:59');

-- =====================================================================
-- TABELAS TEMPORARIAS DE APOIO
-- =====================================================================

DROP TEMPORARY TABLE IF EXISTS tmp_pedidos_empresa;
CREATE TEMPORARY TABLE tmp_pedidos_empresa AS
SELECT
    p.id,
    p.empresa_id,
    p.usuario_id,
    p.mesa_id,
    p.comanda_id,
    p.cliente_id,
    p.tipo,
    p.status,
    COALESCE(p.total, 0) AS total,
    p.criado_em AS data_pedido,
    COALESCE(p.updated_at, p.update_at, p.criado_em) AS data_fechamento,
    DATE(p.criado_em) AS dia_pedido,
    YEAR(p.criado_em) AS ano_pedido,
    MONTH(p.criado_em) AS mes_pedido,
    WEEK(p.criado_em, 1) AS semana_pedido,
    DATE(COALESCE(p.updated_at, p.update_at, p.criado_em)) AS dia_fechamento,
    YEAR(COALESCE(p.updated_at, p.update_at, p.criado_em)) AS ano_fechamento,
    MONTH(COALESCE(p.updated_at, p.update_at, p.criado_em)) AS mes_fechamento,
    WEEK(COALESCE(p.updated_at, p.update_at, p.criado_em), 1) AS semana_fechamento,
    CASE
        WHEN @tipo_agrupamento = 'dia' THEN DATE_FORMAT(p.criado_em, '%Y-%m-%d')
        WHEN @tipo_agrupamento = 'semana' THEN CONCAT(YEAR(p.criado_em), '-S', LPAD(WEEK(p.criado_em, 1), 2, '0'))
        WHEN @tipo_agrupamento = 'mes' THEN DATE_FORMAT(p.criado_em, '%Y-%m')
        WHEN @tipo_agrupamento = 'ano' THEN DATE_FORMAT(p.criado_em, '%Y')
        ELSE 'PERIODO'
    END AS periodo_pedido,
    CASE
        WHEN @tipo_agrupamento = 'dia' THEN DATE_FORMAT(COALESCE(p.updated_at, p.update_at, p.criado_em), '%Y-%m-%d')
        WHEN @tipo_agrupamento = 'semana' THEN CONCAT(YEAR(COALESCE(p.updated_at, p.update_at, p.criado_em)), '-S', LPAD(WEEK(COALESCE(p.updated_at, p.update_at, p.criado_em), 1), 2, '0'))
        WHEN @tipo_agrupamento = 'mes' THEN DATE_FORMAT(COALESCE(p.updated_at, p.update_at, p.criado_em), '%Y-%m')
        WHEN @tipo_agrupamento = 'ano' THEN DATE_FORMAT(COALESCE(p.updated_at, p.update_at, p.criado_em), '%Y')
        ELSE 'PERIODO'
    END AS periodo_fechamento
FROM tb_pedidos p
WHERE p.empresa_id = @empresa_id;

DROP TEMPORARY TABLE IF EXISTS tmp_pedidos_venda;
CREATE TEMPORARY TABLE tmp_pedidos_venda AS
SELECT *
FROM tmp_pedidos_empresa p
WHERE p.status = 'fechado'
  AND (
      (@tipo_filtro = 'dia' AND p.dia_fechamento = @dia_ref)
      OR (@tipo_filtro = 'semana' AND p.ano_fechamento = @ano_ref AND p.semana_fechamento = @semana_ref)
      OR (@tipo_filtro = 'mes' AND p.ano_fechamento = @ano_ref AND p.mes_fechamento = @mes_ref)
      OR (@tipo_filtro = 'ano' AND p.ano_fechamento = @ano_ref)
      OR (@tipo_filtro = 'periodo' AND p.data_fechamento >= @data_inicio AND p.data_fechamento <= @data_fim)
  );

DROP TEMPORARY TABLE IF EXISTS tmp_pedidos_operacao;
CREATE TEMPORARY TABLE tmp_pedidos_operacao AS
SELECT *
FROM tmp_pedidos_empresa p
WHERE (
      (@tipo_filtro = 'dia' AND p.dia_pedido = @dia_ref)
      OR (@tipo_filtro = 'semana' AND p.ano_pedido = @ano_ref AND p.semana_pedido = @semana_ref)
      OR (@tipo_filtro = 'mes' AND p.ano_pedido = @ano_ref AND p.mes_pedido = @mes_ref)
      OR (@tipo_filtro = 'ano' AND p.ano_pedido = @ano_ref)
      OR (@tipo_filtro = 'periodo' AND p.data_pedido >= @data_inicio AND p.data_pedido <= @data_fim)
  );

DROP TEMPORARY TABLE IF EXISTS tmp_itens_venda;
CREATE TEMPORARY TABLE tmp_itens_venda AS
SELECT
    p.periodo_fechamento AS periodo,
    p.id AS pedido_id,
    p.usuario_id,
    p.mesa_id,
    p.comanda_id,
    p.cliente_id,
    pr.id AS produto_id,
    pr.nome AS produto,
    c.id AS categoria_id,
    c.nome AS categoria,
    COALESCE(pi.quantidade, 0) AS quantidade,
    COALESCE(pi.preco, 0) AS preco_unitario,
    COALESCE(pi.quantidade, 0) * COALESCE(pi.preco, 0) AS receita_item,
    COALESCE(pi.quantidade, 0) * COALESCE(pr.custo, 0) AS custo_estimado_item
FROM tmp_pedidos_venda p
JOIN tb_pedido_itens pi ON pi.pedido_id = p.id
LEFT JOIN tb_produtos pr ON pr.id = pi.produto_id
LEFT JOIN tb_categorias c ON c.id = pr.categoria_id;

DROP TEMPORARY TABLE IF EXISTS tmp_pagamentos_venda;
CREATE TEMPORARY TABLE tmp_pagamentos_venda AS
SELECT
    p.periodo_fechamento AS periodo,
    p.id AS pedido_id,
    pg.id AS pagamento_id,
    fp.id AS forma_pagamento_id,
    fp.nome AS forma_pagamento,
    COALESCE(pg.valor, 0) AS valor_bruto,
    COALESCE(pg.troco, 0) AS troco,
    GREATEST(COALESCE(pg.valor, 0) - COALESCE(pg.troco, 0), 0) AS valor_liquido
FROM tmp_pedidos_venda p
JOIN tb_pagamentos pg ON pg.pedido_id = p.id
LEFT JOIN tb_formas_pagamento fp ON fp.id = pg.forma_pagamento_id;

DROP TEMPORARY TABLE IF EXISTS tmp_usuarios_user;
CREATE TEMPORARY TABLE tmp_usuarios_user AS
SELECT DISTINCT
    u.id,
    u.nome,
    u.email
FROM tb_usuarios u
JOIN tb_usuario_perfis up ON up.usuario_id = u.id
JOIN tb_perfis pf ON pf.id = up.perfil_id
WHERE u.empresa_id = @empresa_id
  AND u.ativo = 1
  AND pf.nome = 'user';

DROP TEMPORARY TABLE IF EXISTS tmp_pedidos_user_mesa;
CREATE TEMPORARY TABLE tmp_pedidos_user_mesa AS
SELECT
    p.periodo_fechamento AS periodo,
    p.id,
    p.usuario_id,
    u.nome AS usuario,
    p.mesa_id,
    p.comanda_id,
    p.total,
    p.data_pedido,
    p.data_fechamento
FROM tmp_pedidos_venda p
JOIN tmp_usuarios_user u ON u.id = p.usuario_id
WHERE p.tipo = 'mesa'
  AND p.mesa_id IS NOT NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_clientes_cadastro;
CREATE TEMPORARY TABLE tmp_clientes_cadastro AS
SELECT
    c.id,
    c.nome,
    c.telefone,
    c.email,
    c.created_at,
    CASE
        WHEN @tipo_agrupamento = 'dia' THEN DATE_FORMAT(c.created_at, '%Y-%m-%d')
        WHEN @tipo_agrupamento = 'semana' THEN CONCAT(YEAR(c.created_at), '-S', LPAD(WEEK(c.created_at, 1), 2, '0'))
        WHEN @tipo_agrupamento = 'mes' THEN DATE_FORMAT(c.created_at, '%Y-%m')
        WHEN @tipo_agrupamento = 'ano' THEN DATE_FORMAT(c.created_at, '%Y')
        ELSE 'PERIODO'
    END AS periodo
FROM tb_clientes c
WHERE c.empresa_id = @empresa_id
  AND (
      (@tipo_filtro = 'dia' AND DATE(c.created_at) = @dia_ref)
      OR (@tipo_filtro = 'semana' AND YEAR(c.created_at) = @ano_ref AND WEEK(c.created_at, 1) = @semana_ref)
      OR (@tipo_filtro = 'mes' AND YEAR(c.created_at) = @ano_ref AND MONTH(c.created_at) = @mes_ref)
      OR (@tipo_filtro = 'ano' AND YEAR(c.created_at) = @ano_ref)
      OR (@tipo_filtro = 'periodo' AND c.created_at >= @data_inicio AND c.created_at <= @data_fim)
  );

DROP TEMPORARY TABLE IF EXISTS tmp_delivery_operacao;
CREATE TEMPORARY TABLE tmp_delivery_operacao AS
SELECT
    p.periodo_pedido AS periodo,
    p.id AS pedido_id,
    p.status AS status_pedido,
    p.total,
    p.data_pedido,
    e.entregador_id,
    COALESCE(e.taxa, 0) AS taxa_entrega,
    COALESCE(e.status, 'sem_registro') AS status_entrega
FROM tmp_pedidos_operacao p
LEFT JOIN tb_entregas e ON e.pedido_id = p.id
WHERE p.tipo = 'delivery';

DROP TEMPORARY TABLE IF EXISTS tmp_cozinha_periodo;
CREATE TEMPORARY TABLE tmp_cozinha_periodo AS
SELECT
    CASE
        WHEN @tipo_agrupamento = 'dia' THEN DATE_FORMAT(ci.created_at, '%Y-%m-%d')
        WHEN @tipo_agrupamento = 'semana' THEN CONCAT(YEAR(ci.created_at), '-S', LPAD(WEEK(ci.created_at, 1), 2, '0'))
        WHEN @tipo_agrupamento = 'mes' THEN DATE_FORMAT(ci.created_at, '%Y-%m')
        WHEN @tipo_agrupamento = 'ano' THEN DATE_FORMAT(ci.created_at, '%Y')
        ELSE 'PERIODO'
    END AS periodo,
    ped.id AS pedido_id,
    pi.id AS pedido_item_id,
    ce.id AS estacao_id,
    ce.nome AS estacao,
    ci.status,
    ci.created_at,
    ci.updated_at,
    TIMESTAMPDIFF(MINUTE, ci.created_at, ci.updated_at) AS tempo_ciclo_min
FROM tb_cozinha_itens ci
JOIN tb_pedido_itens pi ON pi.id = ci.pedido_item_id
JOIN tb_pedidos ped ON ped.id = pi.pedido_id
LEFT JOIN tb_cozinha_estacoes ce ON ce.id = ci.estacao_id
WHERE ped.empresa_id = @empresa_id
  AND (
      (@tipo_filtro = 'dia' AND DATE(ci.created_at) = @dia_ref)
      OR (@tipo_filtro = 'semana' AND YEAR(ci.created_at) = @ano_ref AND WEEK(ci.created_at, 1) = @semana_ref)
      OR (@tipo_filtro = 'mes' AND YEAR(ci.created_at) = @ano_ref AND MONTH(ci.created_at) = @mes_ref)
      OR (@tipo_filtro = 'ano' AND YEAR(ci.created_at) = @ano_ref)
      OR (@tipo_filtro = 'periodo' AND ci.created_at >= @data_inicio AND ci.created_at <= @data_fim)
  );

DROP TEMPORARY TABLE IF EXISTS tmp_caixa_periodo;
CREATE TEMPORARY TABLE tmp_caixa_periodo AS
SELECT
    CASE
        WHEN @tipo_agrupamento = 'dia' THEN DATE_FORMAT(c.created_at, '%Y-%m-%d')
        WHEN @tipo_agrupamento = 'semana' THEN CONCAT(YEAR(c.created_at), '-S', LPAD(WEEK(c.created_at, 1), 2, '0'))
        WHEN @tipo_agrupamento = 'mes' THEN DATE_FORMAT(c.created_at, '%Y-%m')
        WHEN @tipo_agrupamento = 'ano' THEN DATE_FORMAT(c.created_at, '%Y')
        ELSE 'PERIODO'
    END AS periodo,
    c.id AS caixa_id,
    c.usuario_id,
    u.nome AS usuario,
    c.aberto_em,
    c.fechado_em,
    COALESCE(c.saldo_inicial, 0) AS saldo_inicial,
    COALESCE(c.saldo_final, 0) AS saldo_final
FROM tb_caixas c
LEFT JOIN tb_usuarios u ON u.id = c.usuario_id
WHERE c.empresa_id = @empresa_id
  AND (
      (@tipo_filtro = 'dia' AND DATE(c.created_at) = @dia_ref)
      OR (@tipo_filtro = 'semana' AND YEAR(c.created_at) = @ano_ref AND WEEK(c.created_at, 1) = @semana_ref)
      OR (@tipo_filtro = 'mes' AND YEAR(c.created_at) = @ano_ref AND MONTH(c.created_at) = @mes_ref)
      OR (@tipo_filtro = 'ano' AND YEAR(c.created_at) = @ano_ref)
      OR (@tipo_filtro = 'periodo' AND c.created_at >= @data_inicio AND c.created_at <= @data_fim)
  );

DROP TEMPORARY TABLE IF EXISTS tmp_caixa_mov_periodo;
CREATE TEMPORARY TABLE tmp_caixa_mov_periodo AS
SELECT
    CASE
        WHEN @tipo_agrupamento = 'dia' THEN DATE_FORMAT(cm.criado_em, '%Y-%m-%d')
        WHEN @tipo_agrupamento = 'semana' THEN CONCAT(YEAR(cm.criado_em), '-S', LPAD(WEEK(cm.criado_em, 1), 2, '0'))
        WHEN @tipo_agrupamento = 'mes' THEN DATE_FORMAT(cm.criado_em, '%Y-%m')
        WHEN @tipo_agrupamento = 'ano' THEN DATE_FORMAT(cm.criado_em, '%Y')
        ELSE 'PERIODO'
    END AS periodo,
    c.id AS caixa_id,
    c.usuario_id,
    u.nome AS usuario,
    cm.tipo,
    COALESCE(cm.valor, 0) AS valor,
    cm.descricao,
    cm.criado_em
FROM tb_caixa_movimentos cm
JOIN tb_caixas c ON c.id = cm.caixa_id
LEFT JOIN tb_usuarios u ON u.id = c.usuario_id
WHERE c.empresa_id = @empresa_id
  AND (
      (@tipo_filtro = 'dia' AND DATE(cm.criado_em) = @dia_ref)
      OR (@tipo_filtro = 'semana' AND YEAR(cm.criado_em) = @ano_ref AND WEEK(cm.criado_em, 1) = @semana_ref)
      OR (@tipo_filtro = 'mes' AND YEAR(cm.criado_em) = @ano_ref AND MONTH(cm.criado_em) = @mes_ref)
      OR (@tipo_filtro = 'ano' AND YEAR(cm.criado_em) = @ano_ref)
      OR (@tipo_filtro = 'periodo' AND cm.criado_em >= @data_inicio AND cm.criado_em <= @data_fim)
  );

DROP TEMPORARY TABLE IF EXISTS tmp_estoque_mov_periodo;
CREATE TEMPORARY TABLE tmp_estoque_mov_periodo AS
SELECT
    CASE
        WHEN @tipo_agrupamento = 'dia' THEN DATE_FORMAT(em.criado_em, '%Y-%m-%d')
        WHEN @tipo_agrupamento = 'semana' THEN CONCAT(YEAR(em.criado_em), '-S', LPAD(WEEK(em.criado_em, 1), 2, '0'))
        WHEN @tipo_agrupamento = 'mes' THEN DATE_FORMAT(em.criado_em, '%Y-%m')
        WHEN @tipo_agrupamento = 'ano' THEN DATE_FORMAT(em.criado_em, '%Y')
        ELSE 'PERIODO'
    END AS periodo,
    pr.id AS produto_id,
    pr.nome AS produto,
    em.tipo,
    COALESCE(em.quantidade, 0) AS quantidade,
    em.criado_em
FROM tb_estoque_movimentos em
JOIN tb_produtos pr ON pr.id = em.produto_id
WHERE pr.empresa_id = @empresa_id
  AND (
      (@tipo_filtro = 'dia' AND DATE(em.criado_em) = @dia_ref)
      OR (@tipo_filtro = 'semana' AND YEAR(em.criado_em) = @ano_ref AND WEEK(em.criado_em, 1) = @semana_ref)
      OR (@tipo_filtro = 'mes' AND YEAR(em.criado_em) = @ano_ref AND MONTH(em.criado_em) = @mes_ref)
      OR (@tipo_filtro = 'ano' AND YEAR(em.criado_em) = @ano_ref)
      OR (@tipo_filtro = 'periodo' AND em.criado_em >= @data_inicio AND em.criado_em <= @data_fim)
  );

-- =====================================================================
-- BLOCO 1 - RESUMO EXECUTIVO DE VENDAS
-- =====================================================================

-- R01. Resumo executivo por periodo
SELECT
    p.periodo_fechamento AS periodo,
    COUNT(*) AS pedidos_fechados,
    SUM(p.total) AS faturamento,
    ROUND(AVG(p.total), 2) AS ticket_medio,
    ROUND(AVG(TIMESTAMPDIFF(MINUTE, p.data_pedido, p.data_fechamento)), 2) AS ciclo_medio_min
FROM tmp_pedidos_venda p
GROUP BY p.periodo_fechamento
ORDER BY p.periodo_fechamento;

-- R02. Vendas por tipo de pedido
SELECT
    p.periodo_fechamento AS periodo,
    p.tipo,
    COUNT(*) AS pedidos,
    SUM(p.total) AS faturamento,
    ROUND(AVG(p.total), 2) AS ticket_medio
FROM tmp_pedidos_venda p
GROUP BY p.periodo_fechamento, p.tipo
ORDER BY p.periodo_fechamento, faturamento DESC;

-- R03. Pedidos operacionais por status
SELECT
    p.periodo_pedido AS periodo,
    p.status,
    COUNT(*) AS quantidade_pedidos,
    ROUND(AVG(TIMESTAMPDIFF(MINUTE, p.data_pedido, NOW())), 2) AS idade_media_min
FROM tmp_pedidos_operacao p
GROUP BY p.periodo_pedido, p.status
ORDER BY p.periodo_pedido, quantidade_pedidos DESC;

-- R04. Curva de pedidos por hora
SELECT
    HOUR(p.data_pedido) AS hora,
    COUNT(*) AS quantidade_pedidos,
    SUM(CASE WHEN p.status = 'fechado' THEN p.total ELSE 0 END) AS faturamento_fechado
FROM tmp_pedidos_operacao p
GROUP BY HOUR(p.data_pedido)
ORDER BY hora;

-- R05. Faturamento por dia da semana
SELECT
    DAYNAME(p.data_fechamento) AS dia_semana,
    COUNT(*) AS pedidos_fechados,
    SUM(p.total) AS faturamento,
    ROUND(AVG(p.total), 2) AS ticket_medio
FROM tmp_pedidos_venda p
GROUP BY DAYNAME(p.data_fechamento)
ORDER BY faturamento DESC;

-- =====================================================================
-- BLOCO 2 - ITENS, PRODUTOS E CATEGORIAS
-- =====================================================================

-- R06. Top produtos por quantidade
SELECT
    i.produto_id,
    i.produto,
    SUM(i.quantidade) AS quantidade_vendida,
    SUM(i.receita_item) AS faturamento_estimado
FROM tmp_itens_venda i
GROUP BY i.produto_id, i.produto
ORDER BY quantidade_vendida DESC, faturamento_estimado DESC;

-- R07. Top produtos por faturamento
SELECT
    i.produto_id,
    i.produto,
    SUM(i.quantidade) AS quantidade_vendida,
    SUM(i.receita_item) AS faturamento_estimado
FROM tmp_itens_venda i
GROUP BY i.produto_id, i.produto
ORDER BY faturamento_estimado DESC, quantidade_vendida DESC;

-- R08. Vendas por categoria
SELECT
    i.periodo,
    COALESCE(i.categoria, 'sem_categoria') AS categoria,
    SUM(i.quantidade) AS quantidade_vendida,
    SUM(i.receita_item) AS faturamento_estimado
FROM tmp_itens_venda i
GROUP BY i.periodo, COALESCE(i.categoria, 'sem_categoria')
ORDER BY i.periodo, faturamento_estimado DESC;

-- R09. Margem estimada por produto
SELECT
    i.produto_id,
    i.produto,
    SUM(i.receita_item) AS receita_estimada,
    SUM(i.custo_estimado_item) AS custo_estimado,
    SUM(i.receita_item - i.custo_estimado_item) AS margem_estimada,
    ROUND(
        CASE
            WHEN SUM(i.receita_item) = 0 THEN 0
            ELSE (SUM(i.receita_item - i.custo_estimado_item) / SUM(i.receita_item)) * 100
        END,
        2
    ) AS margem_percentual
FROM tmp_itens_venda i
GROUP BY i.produto_id, i.produto
ORDER BY margem_estimada DESC;

-- R10. Margem estimada por categoria
SELECT
    COALESCE(i.categoria, 'sem_categoria') AS categoria,
    SUM(i.receita_item) AS receita_estimada,
    SUM(i.custo_estimado_item) AS custo_estimado,
    SUM(i.receita_item - i.custo_estimado_item) AS margem_estimada,
    ROUND(
        CASE
            WHEN SUM(i.receita_item) = 0 THEN 0
            ELSE (SUM(i.receita_item - i.custo_estimado_item) / SUM(i.receita_item)) * 100
        END,
        2
    ) AS margem_percentual
FROM tmp_itens_venda i
GROUP BY COALESCE(i.categoria, 'sem_categoria')
ORDER BY margem_estimada DESC;

-- R11. Itens por pedido e ticket medio de itens
SELECT
    i.periodo,
    COUNT(DISTINCT i.pedido_id) AS pedidos_com_itens,
    SUM(i.quantidade) AS total_itens,
    ROUND(SUM(i.quantidade) / NULLIF(COUNT(DISTINCT i.pedido_id), 0), 2) AS media_itens_por_pedido,
    ROUND(SUM(i.receita_item) / NULLIF(COUNT(DISTINCT i.pedido_id), 0), 2) AS media_receita_itens_por_pedido
FROM tmp_itens_venda i
GROUP BY i.periodo
ORDER BY i.periodo;

-- =====================================================================
-- BLOCO 3 - PAGAMENTOS
-- =====================================================================

-- R12. Mix de pagamentos por forma
SELECT
    COALESCE(pg.forma_pagamento, 'sem_forma_pagamento') AS forma_pagamento,
    COUNT(*) AS quantidade_lancamentos,
    SUM(pg.valor_bruto) AS valor_bruto,
    SUM(pg.troco) AS troco,
    SUM(pg.valor_liquido) AS valor_liquido_estimado
FROM tmp_pagamentos_venda pg
GROUP BY COALESCE(pg.forma_pagamento, 'sem_forma_pagamento')
ORDER BY valor_liquido_estimado DESC;

-- R13. Mix de pagamentos por periodo
SELECT
    pg.periodo,
    COALESCE(pg.forma_pagamento, 'sem_forma_pagamento') AS forma_pagamento,
    COUNT(*) AS quantidade_lancamentos,
    SUM(pg.valor_liquido) AS valor_liquido_estimado
FROM tmp_pagamentos_venda pg
GROUP BY pg.periodo, COALESCE(pg.forma_pagamento, 'sem_forma_pagamento')
ORDER BY pg.periodo, valor_liquido_estimado DESC;

-- R14. Troco por forma de pagamento
SELECT
    COALESCE(pg.forma_pagamento, 'sem_forma_pagamento') AS forma_pagamento,
    SUM(pg.troco) AS troco_total,
    ROUND(AVG(pg.troco), 2) AS troco_medio
FROM tmp_pagamentos_venda pg
GROUP BY COALESCE(pg.forma_pagamento, 'sem_forma_pagamento')
ORDER BY troco_total DESC;

-- R15. Conciliacao de pagamentos versus pedidos
SELECT
    p.periodo_fechamento AS periodo,
    COUNT(*) AS pedidos,
    SUM(p.total) AS total_pedidos,
    SUM(COALESCE(pg.total_pago_liquido, 0)) AS total_pago_liquido,
    SUM(COALESCE(pg.total_pago_bruto, 0)) AS total_pago_bruto,
    SUM(COALESCE(pg.total_pago_liquido, 0) - p.total) AS diferenca_liquida
FROM tmp_pedidos_venda p
LEFT JOIN (
    SELECT
        pedido_id,
        SUM(valor_bruto) AS total_pago_bruto,
        SUM(valor_liquido) AS total_pago_liquido
    FROM tmp_pagamentos_venda
    GROUP BY pedido_id
) pg ON pg.pedido_id = p.id
GROUP BY p.periodo_fechamento
ORDER BY p.periodo_fechamento;

-- =====================================================================
-- BLOCO 4 - ATENDIMENTO E USUARIOS COM PERFIL USER
-- =====================================================================

-- R16. Faturamento por usuario com perfil user no atendimento de mesa
SELECT
    u.usuario_id,
    u.usuario,
    COUNT(*) AS pedidos_fechados,
    COUNT(DISTINCT u.mesa_id) AS mesas_atendidas,
    SUM(u.total) AS faturamento,
    ROUND(AVG(u.total), 2) AS ticket_medio,
    ROUND(AVG(TIMESTAMPDIFF(MINUTE, u.data_pedido, u.data_fechamento)), 2) AS ciclo_medio_min
FROM tmp_pedidos_user_mesa u
GROUP BY u.usuario_id, u.usuario
ORDER BY faturamento DESC, pedidos_fechados DESC;

-- R17. Faturamento por usuario com perfil user por periodo
SELECT
    u.periodo,
    u.usuario_id,
    u.usuario,
    COUNT(*) AS pedidos_fechados,
    SUM(u.total) AS faturamento,
    ROUND(AVG(u.total), 2) AS ticket_medio
FROM tmp_pedidos_user_mesa u
GROUP BY u.periodo, u.usuario_id, u.usuario
ORDER BY u.periodo, faturamento DESC;

-- R18. Mesas atendidas por usuario com perfil user
SELECT
    u.usuario_id,
    u.usuario,
    u.mesa_id,
    COUNT(*) AS pedidos_fechados,
    SUM(u.total) AS faturamento,
    ROUND(AVG(u.total), 2) AS ticket_medio
FROM tmp_pedidos_user_mesa u
GROUP BY u.usuario_id, u.usuario, u.mesa_id
ORDER BY u.usuario, faturamento DESC;

-- =====================================================================
-- BLOCO 5 - MESAS E COMANDAS
-- =====================================================================

-- R19. Faturamento por mesa
SELECT
    p.periodo_fechamento AS periodo,
    m.numero AS mesa,
    COUNT(*) AS pedidos_fechados,
    SUM(p.total) AS faturamento,
    ROUND(AVG(p.total), 2) AS ticket_medio
FROM tmp_pedidos_venda p
JOIN tb_mesas m ON m.id = p.mesa_id
GROUP BY p.periodo_fechamento, m.numero
ORDER BY p.periodo_fechamento, faturamento DESC;

-- R20. Giro de mesa
SELECT
    m.numero AS mesa,
    COUNT(*) AS pedidos_fechados,
    ROUND(AVG(TIMESTAMPDIFF(MINUTE, p.data_pedido, p.data_fechamento)), 2) AS ciclo_medio_min,
    SUM(p.total) AS faturamento
FROM tmp_pedidos_venda p
JOIN tb_mesas m ON m.id = p.mesa_id
GROUP BY m.numero
ORDER BY faturamento DESC;

-- R21. Faturamento por comanda
SELECT
    p.periodo_fechamento AS periodo,
    c.numero AS comanda,
    COUNT(*) AS pedidos_fechados,
    SUM(p.total) AS faturamento,
    ROUND(AVG(p.total), 2) AS ticket_medio
FROM tmp_pedidos_venda p
JOIN tb_comandas c ON c.id = p.comanda_id
GROUP BY p.periodo_fechamento, c.numero
ORDER BY p.periodo_fechamento, faturamento DESC;

-- =====================================================================
-- BLOCO 6 - CLIENTES
-- =====================================================================

-- R22. Faturamento por cliente identificado
SELECT
    c.id AS cliente_id,
    c.nome AS cliente,
    COUNT(*) AS pedidos_fechados,
    SUM(p.total) AS faturamento,
    ROUND(AVG(p.total), 2) AS ticket_medio
FROM tmp_pedidos_venda p
JOIN tb_clientes c ON c.id = p.cliente_id
GROUP BY c.id, c.nome
ORDER BY faturamento DESC, pedidos_fechados DESC;

-- R23. Clientes novos no periodo
SELECT
    c.periodo,
    COUNT(*) AS clientes_novos
FROM tmp_clientes_cadastro c
GROUP BY c.periodo
ORDER BY c.periodo;

-- R24. Clientes recorrentes versus clientes de compra unica
SELECT
    COUNT(CASE WHEN x.qtd_pedidos = 1 THEN 1 END) AS clientes_compra_unica,
    COUNT(CASE WHEN x.qtd_pedidos > 1 THEN 1 END) AS clientes_recorrentes,
    COUNT(*) AS clientes_identificados
FROM (
    SELECT
        p.cliente_id,
        COUNT(*) AS qtd_pedidos
    FROM tmp_pedidos_venda p
    WHERE p.cliente_id IS NOT NULL
    GROUP BY p.cliente_id
) x;

-- R25. Ranking de clientes por faturamento
SELECT
    c.id AS cliente_id,
    c.nome AS cliente,
    COUNT(*) AS pedidos_fechados,
    SUM(p.total) AS faturamento,
    MAX(p.data_fechamento) AS ultima_compra
FROM tmp_pedidos_venda p
JOIN tb_clientes c ON c.id = p.cliente_id
GROUP BY c.id, c.nome
ORDER BY faturamento DESC;

-- =====================================================================
-- BLOCO 7 - DELIVERY
-- =====================================================================

-- R26. Pedidos delivery por status do pedido e da entrega
SELECT
    d.periodo,
    d.status_pedido,
    d.status_entrega,
    COUNT(*) AS quantidade_pedidos,
    SUM(d.total) AS valor_pedidos,
    SUM(d.taxa_entrega) AS taxa_entrega_total
FROM tmp_delivery_operacao d
GROUP BY d.periodo, d.status_pedido, d.status_entrega
ORDER BY d.periodo, quantidade_pedidos DESC;

-- R27. Resumo financeiro de delivery
SELECT
    d.periodo,
    COUNT(*) AS pedidos_delivery,
    SUM(d.total) AS faturamento_pedidos,
    SUM(d.taxa_entrega) AS receita_taxa_entrega,
    ROUND(AVG(d.total), 2) AS ticket_medio_delivery
FROM tmp_delivery_operacao d
GROUP BY d.periodo
ORDER BY d.periodo;

-- R28. Performance por entregador
SELECT
    COALESCE(e.nome, 'sem_entregador') AS entregador,
    COUNT(*) AS pedidos_delivery,
    SUM(d.total) AS faturamento_pedidos,
    SUM(d.taxa_entrega) AS taxa_entrega_total
FROM tmp_delivery_operacao d
LEFT JOIN tb_entregadores e ON e.id = d.entregador_id
GROUP BY COALESCE(e.nome, 'sem_entregador')
ORDER BY faturamento_pedidos DESC;

-- =====================================================================
-- BLOCO 8 - COZINHA
-- =====================================================================

-- R29. Desempenho por estacao
SELECT
    COALESCE(c.estacao, 'sem_estacao') AS estacao,
    COUNT(*) AS quantidade_itens,
    ROUND(AVG(c.tempo_ciclo_min), 2) AS tempo_medio_min,
    MIN(c.tempo_ciclo_min) AS menor_tempo_min,
    MAX(c.tempo_ciclo_min) AS maior_tempo_min
FROM tmp_cozinha_periodo c
GROUP BY COALESCE(c.estacao, 'sem_estacao')
ORDER BY quantidade_itens DESC;

-- R30. Status dos itens de cozinha por periodo
SELECT
    c.periodo,
    c.status,
    COUNT(*) AS quantidade_itens
FROM tmp_cozinha_periodo c
GROUP BY c.periodo, c.status
ORDER BY c.periodo, quantidade_itens DESC;

-- R31. Desempenho por estacao e periodo
SELECT
    c.periodo,
    COALESCE(c.estacao, 'sem_estacao') AS estacao,
    COUNT(*) AS quantidade_itens,
    ROUND(AVG(c.tempo_ciclo_min), 2) AS tempo_medio_min
FROM tmp_cozinha_periodo c
GROUP BY c.periodo, COALESCE(c.estacao, 'sem_estacao')
ORDER BY c.periodo, quantidade_itens DESC;

-- =====================================================================
-- BLOCO 9 - CAIXA
-- =====================================================================

-- R32. Resumo de caixas abertos no periodo
SELECT
    c.periodo,
    COUNT(*) AS quantidade_caixas,
    SUM(c.saldo_inicial) AS saldo_inicial_total,
    SUM(c.saldo_final) AS saldo_final_total,
    SUM(c.saldo_final - c.saldo_inicial) AS variacao_total
FROM tmp_caixa_periodo c
GROUP BY c.periodo
ORDER BY c.periodo;

-- R33. Faturamento de caixa por usuario responsavel
SELECT
    COALESCE(c.usuario, 'sem_usuario') AS usuario,
    COUNT(*) AS quantidade_caixas,
    SUM(c.saldo_inicial) AS saldo_inicial_total,
    SUM(c.saldo_final) AS saldo_final_total,
    SUM(c.saldo_final - c.saldo_inicial) AS variacao_total
FROM tmp_caixa_periodo c
GROUP BY COALESCE(c.usuario, 'sem_usuario')
ORDER BY variacao_total DESC;

-- R34. Movimentos de caixa por tipo
SELECT
    m.periodo,
    m.tipo,
    COUNT(*) AS quantidade_movimentos,
    SUM(m.valor) AS valor_total
FROM tmp_caixa_mov_periodo m
GROUP BY m.periodo, m.tipo
ORDER BY m.periodo, valor_total DESC;

-- R35. Movimentos de caixa por usuario
SELECT
    COALESCE(m.usuario, 'sem_usuario') AS usuario,
    m.tipo,
    COUNT(*) AS quantidade_movimentos,
    SUM(m.valor) AS valor_total
FROM tmp_caixa_mov_periodo m
GROUP BY COALESCE(m.usuario, 'sem_usuario'), m.tipo
ORDER BY usuario, valor_total DESC;

-- =====================================================================
-- BLOCO 10 - ESTOQUE
-- =====================================================================

-- R36. Posicao atual de estoque
SELECT
    pr.id AS produto_id,
    pr.nome AS produto,
    COALESCE(c.nome, 'sem_categoria') AS categoria,
    COALESCE(e.quantidade, 0) AS estoque_atual,
    COALESCE(pr.custo, 0) AS custo_unitario_atual,
    COALESCE(e.quantidade, 0) * COALESCE(pr.custo, 0) AS valor_estoque_custo
FROM tb_estoque e
JOIN tb_produtos pr ON pr.id = e.produto_id
LEFT JOIN tb_categorias c ON c.id = pr.categoria_id
WHERE e.empresa_id = @empresa_id
ORDER BY estoque_atual ASC, produto;

-- R37. Movimentos de estoque por tipo
SELECT
    m.periodo,
    m.tipo,
    COUNT(*) AS quantidade_movimentos,
    SUM(m.quantidade) AS quantidade_total
FROM tmp_estoque_mov_periodo m
GROUP BY m.periodo, m.tipo
ORDER BY m.periodo, quantidade_total DESC;

-- R38. Movimentos de estoque por produto
SELECT
    m.produto_id,
    m.produto,
    m.tipo,
    SUM(m.quantidade) AS quantidade_total
FROM tmp_estoque_mov_periodo m
GROUP BY m.produto_id, m.produto, m.tipo
ORDER BY m.produto, m.tipo;

-- =====================================================================
-- BLOCO 11 - QUALIDADE E CONCILIACAO DE DADOS
-- =====================================================================

-- R39. Divergencia entre total do pedido e soma dos itens
SELECT
    p.id AS pedido_id,
    p.periodo_fechamento AS periodo,
    p.total AS total_pedido,
    COALESCE(i.total_itens, 0) AS total_itens,
    COALESCE(i.total_itens, 0) - p.total AS diferenca
FROM tmp_pedidos_venda p
LEFT JOIN (
    SELECT
        pedido_id,
        SUM(receita_item) AS total_itens
    FROM tmp_itens_venda
    GROUP BY pedido_id
) i ON i.pedido_id = p.id
WHERE ROUND(COALESCE(i.total_itens, 0) - p.total, 2) <> 0
ORDER BY ABS(COALESCE(i.total_itens, 0) - p.total) DESC, p.id;

-- R40. Divergencia entre total do pedido e pagamentos liquidos
SELECT
    p.id AS pedido_id,
    p.periodo_fechamento AS periodo,
    p.total AS total_pedido,
    COALESCE(pg.total_pago_liquido, 0) AS total_pago_liquido,
    COALESCE(pg.total_pago_liquido, 0) - p.total AS diferenca
FROM tmp_pedidos_venda p
LEFT JOIN (
    SELECT
        pedido_id,
        SUM(valor_liquido) AS total_pago_liquido
    FROM tmp_pagamentos_venda
    GROUP BY pedido_id
) pg ON pg.pedido_id = p.id
WHERE ROUND(COALESCE(pg.total_pago_liquido, 0) - p.total, 2) <> 0
ORDER BY ABS(COALESCE(pg.total_pago_liquido, 0) - p.total) DESC, p.id;

-- =====================================================================
-- LIMPEZA OPCIONAL
-- =====================================================================
-- Se quiser encerrar a sessao sem manter tabelas temporarias, descomente:
--
-- DROP TEMPORARY TABLE IF EXISTS tmp_pedidos_empresa;
-- DROP TEMPORARY TABLE IF EXISTS tmp_pedidos_venda;
-- DROP TEMPORARY TABLE IF EXISTS tmp_pedidos_operacao;
-- DROP TEMPORARY TABLE IF EXISTS tmp_itens_venda;
-- DROP TEMPORARY TABLE IF EXISTS tmp_pagamentos_venda;
-- DROP TEMPORARY TABLE IF EXISTS tmp_usuarios_user;
-- DROP TEMPORARY TABLE IF EXISTS tmp_pedidos_user_mesa;
-- DROP TEMPORARY TABLE IF EXISTS tmp_clientes_cadastro;
-- DROP TEMPORARY TABLE IF EXISTS tmp_delivery_operacao;
-- DROP TEMPORARY TABLE IF EXISTS tmp_cozinha_periodo;
-- DROP TEMPORARY TABLE IF EXISTS tmp_caixa_periodo;
-- DROP TEMPORARY TABLE IF EXISTS tmp_caixa_mov_periodo;
-- DROP TEMPORARY TABLE IF EXISTS tmp_estoque_mov_periodo;
