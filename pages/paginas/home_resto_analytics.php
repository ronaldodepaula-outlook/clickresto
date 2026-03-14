<?php

if (!function_exists('homeresto_now')) {
    function homeresto_now()
    {
        try {
            return new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
        } catch (Exception $e) {
            return new DateTime();
        }
    }
}

if (!function_exists('homeresto_default_filters')) {
    function homeresto_default_filters()
    {
        $now = homeresto_now();

        return [
            'tipo_filtro' => 'periodo',
            'tipo_agrupamento' => 'dia',
            'dia_ref' => $now->format('Y-m-d'),
            'data_inicio' => $now->format('Y-m-01'),
            'data_fim' => $now->format('Y-m-d'),
        ];
    }
}

if (!function_exists('homeresto_allowed_types')) {
    function homeresto_allowed_types()
    {
        return ['dia', 'semana', 'mes', 'ano', 'periodo'];
    }
}

if (!function_exists('homeresto_normalize_choice')) {
    function homeresto_normalize_choice($value, $default)
    {
        $value = strtolower(trim((string)$value));
        return in_array($value, homeresto_allowed_types(), true) ? $value : $default;
    }
}

if (!function_exists('homeresto_normalize_date')) {
    function homeresto_normalize_date($value, $fallback)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return $fallback;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $fallback;
        }

        return date('Y-m-d', $timestamp);
    }
}

if (!function_exists('homeresto_resolve_filters')) {
    function homeresto_resolve_filters($input)
    {
        $defaults = homeresto_default_filters();
        $filters = [
            'tipo_filtro' => homeresto_normalize_choice($input['tipo_filtro'] ?? $defaults['tipo_filtro'], $defaults['tipo_filtro']),
            'tipo_agrupamento' => homeresto_normalize_choice($input['tipo_agrupamento'] ?? $defaults['tipo_agrupamento'], $defaults['tipo_agrupamento']),
            'dia_ref' => homeresto_normalize_date($input['dia_ref'] ?? $defaults['dia_ref'], $defaults['dia_ref']),
            'data_inicio' => homeresto_normalize_date($input['data_inicio'] ?? $defaults['data_inicio'], $defaults['data_inicio']),
            'data_fim' => homeresto_normalize_date($input['data_fim'] ?? $defaults['data_fim'], $defaults['data_fim']),
        ];

        if (strtotime($filters['data_inicio']) > strtotime($filters['data_fim'])) {
            $swap = $filters['data_inicio'];
            $filters['data_inicio'] = $filters['data_fim'];
            $filters['data_fim'] = $swap;
        }

        $diaRefTs = strtotime($filters['dia_ref']);
        if ($diaRefTs === false) {
            $diaRefTs = strtotime($defaults['dia_ref']);
        }

        $filters['ano_ref'] = (int)date('Y', $diaRefTs);
        $filters['mes_ref'] = (int)date('n', $diaRefTs);
        $filters['semana_ref'] = (int)date('W', $diaRefTs);

        return $filters;
    }
}

if (!function_exists('homeresto_filter_query_params')) {
    function homeresto_filter_query_params($filters)
    {
        return [
            'tipo_filtro' => $filters['tipo_filtro'] ?? 'periodo',
            'tipo_agrupamento' => $filters['tipo_agrupamento'] ?? 'dia',
            'dia_ref' => $filters['dia_ref'] ?? '',
            'data_inicio' => $filters['data_inicio'] ?? '',
            'data_fim' => $filters['data_fim'] ?? '',
        ];
    }
}

if (!function_exists('homeresto_describe_filters')) {
    function homeresto_describe_filters($filters)
    {
        $tipo = $filters['tipo_filtro'] ?? 'periodo';
        $diaRef = homeresto_normalize_date($filters['dia_ref'] ?? '', date('Y-m-d'));
        $dataInicio = homeresto_normalize_date($filters['data_inicio'] ?? '', $diaRef);
        $dataFim = homeresto_normalize_date($filters['data_fim'] ?? '', $diaRef);

        if ($tipo === 'dia') {
            return date('d/m/Y', strtotime($diaRef));
        }

        if ($tipo === 'semana') {
            $semana = (int)date('W', strtotime($diaRef));
            $ano = (int)date('Y', strtotime($diaRef));
            return 'Semana ' . $semana . '/' . $ano;
        }

        if ($tipo === 'mes') {
            return date('m/Y', strtotime($diaRef));
        }

        if ($tipo === 'ano') {
            return date('Y', strtotime($diaRef));
        }

        return date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim));
    }
}

if (!function_exists('homeresto_api_get_json')) {
    function homeresto_api_get_json($url, $token, $empresaId = '')
    {
        $httpCode = 0;
        $curlError = '';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
            'X-Empresa-Id: ' . $empresaId,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false) {
            $curlError = curl_error($ch);
        }
        curl_close($ch);

        if ($response === false) {
            return [
                'ok' => false,
                'http_code' => $httpCode,
                'message' => 'Falha ao conectar na API. ' . $curlError,
                'json' => [],
                'raw' => '',
            ];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        return [
            'ok' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'message' => $decoded['message'] ?? '',
            'json' => $decoded,
            'raw' => $response,
        ];
    }
}

if (!function_exists('homeresto_fetch_operational')) {
    function homeresto_fetch_operational($apiBase, $token, $empresaId)
    {
        $response = homeresto_api_get_json(rtrim((string)$apiBase, '/') . '/dashboard/operational', $token, $empresaId);
        $payload = [];

        if ($response['ok']) {
            $payload = $response['json']['data'] ?? $response['json'];
            if (!is_array($payload)) {
                $payload = [];
            }
        }

        return [
            'ok' => $response['ok'],
            'http_code' => $response['http_code'],
            'message' => $response['ok'] ? '' : ($response['message'] !== '' ? $response['message'] : 'Nao foi possivel carregar o dashboard operacional.'),
            'data' => $payload,
        ];
    }
}

if (!function_exists('homeresto_build_report_query')) {
    function homeresto_build_report_query($filters)
    {
        return [
            'tipo_filtro' => $filters['tipo_filtro'] ?? 'periodo',
            'tipo_agrupamento' => $filters['tipo_agrupamento'] ?? 'dia',
            'dia_ref' => $filters['dia_ref'] ?? '',
            'ano_ref' => $filters['ano_ref'] ?? '',
            'mes_ref' => $filters['mes_ref'] ?? '',
            'semana_ref' => $filters['semana_ref'] ?? '',
            'data_inicio' => $filters['data_inicio'] ?? '',
            'data_fim' => $filters['data_fim'] ?? '',
        ];
    }
}

if (!function_exists('homeresto_build_report_url')) {
    function homeresto_build_report_url($apiBase, $codigo, $filters)
    {
        $query = http_build_query(homeresto_build_report_query($filters));
        return rtrim((string)$apiBase, '/') . '/relatorios/analiticos/' . strtolower($codigo) . ($query !== '' ? '?' . $query : '');
    }
}

if (!function_exists('homeresto_normalize_rows')) {
    function homeresto_normalize_rows($rows)
    {
        $normalized = [];
        if (!is_array($rows)) {
            return $normalized;
        }

        foreach ($rows as $row) {
            if (is_object($row)) {
                $row = (array)$row;
            }
            if (is_array($row)) {
                $normalized[] = $row;
            }
        }

        return $normalized;
    }
}

if (!function_exists('homeresto_fetch_report')) {
    function homeresto_fetch_report($apiBase, $token, $empresaId, $codigo, $filters)
    {
        $catalog = homeresto_report_catalog();
        $meta = $catalog[$codigo] ?? [
            'title' => strtoupper($codigo),
            'group' => 'Relatorios',
            'description' => 'Relatorio analitico.',
            'icon' => 'mdi-chart-box-outline',
        ];

        $response = homeresto_api_get_json(homeresto_build_report_url($apiBase, $codigo, $filters), $token, $empresaId);
        $rows = [];
        $parametros = homeresto_build_report_query($filters);

        if ($response['ok']) {
            $parametros = isset($response['json']['parametros']) && is_array($response['json']['parametros'])
                ? $response['json']['parametros']
                : $parametros;
            $rows = homeresto_normalize_rows($response['json']['dados'] ?? []);
        }

        return [
            'ok' => $response['ok'],
            'http_code' => $response['http_code'],
            'codigo' => strtoupper($codigo),
            'meta' => $meta,
            'parametros' => $parametros,
            'dados' => $rows,
            'message' => $response['ok'] ? '' : ($response['message'] !== '' ? $response['message'] : 'Nao foi possivel carregar o relatorio ' . strtoupper($codigo) . '.'),
        ];
    }
}

if (!function_exists('homeresto_first_row')) {
    function homeresto_first_row($rows)
    {
        return !empty($rows) && is_array($rows[0]) ? $rows[0] : [];
    }
}

if (!function_exists('homeresto_latest_row')) {
    function homeresto_latest_row($rows)
    {
        if (!is_array($rows) || empty($rows)) {
            return [];
        }

        $last = $rows[count($rows) - 1];
        return is_array($last) ? $last : [];
    }
}

if (!function_exists('homeresto_sum_column')) {
    function homeresto_sum_column($rows, $column)
    {
        $total = 0.0;
        if (!is_array($rows)) {
            return $total;
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $value = $row[$column] ?? 0;
            if (is_string($value)) {
                $value = str_replace(',', '.', $value);
            }
            if (is_numeric($value)) {
                $total += (float)$value;
            }
        }

        return $total;
    }
}

if (!function_exists('homeresto_format_number')) {
    function homeresto_format_number($value, $decimals = 0)
    {
        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
        }
        $number = is_numeric($value) ? (float)$value : 0.0;
        return number_format($number, (int)$decimals, ',', '.');
    }
}

if (!function_exists('homeresto_format_money')) {
    function homeresto_format_money($value)
    {
        return 'R$ ' . homeresto_format_number($value, 2);
    }
}

if (!function_exists('homeresto_format_percent')) {
    function homeresto_format_percent($value, $decimals = 1)
    {
        return homeresto_format_number($value, $decimals) . '%';
    }
}

if (!function_exists('homeresto_report_catalog')) {
    function homeresto_report_catalog()
    {
        return [
            'r01' => ['title' => 'Panorama de vendas', 'group' => 'Vendas', 'description' => 'Pedidos fechados, faturamento, ticket medio e ciclo.', 'icon' => 'mdi-chart-line'],
            'r02' => ['title' => 'Vendas por tipo', 'group' => 'Vendas', 'description' => 'Comparativo de faturamento e ticket por tipo de pedido.', 'icon' => 'mdi-storefront-outline'],
            'r03' => ['title' => 'Pedidos por status', 'group' => 'Vendas', 'description' => 'Distribuicao operacional por status e idade media.', 'icon' => 'mdi-timer-sand'],
            'r04' => ['title' => 'Curva horaria', 'group' => 'Vendas', 'description' => 'Picos de pedidos por hora do dia.', 'icon' => 'mdi-clock-outline'],
            'r05' => ['title' => 'Dia da semana', 'group' => 'Vendas', 'description' => 'Leitura de faturamento e ticket por dia da semana.', 'icon' => 'mdi-calendar-week'],
            'r06' => ['title' => 'Top produtos por volume', 'group' => 'Produtos', 'description' => 'Ranking dos itens mais vendidos.', 'icon' => 'mdi-food-outline'],
            'r07' => ['title' => 'Top produtos por receita', 'group' => 'Produtos', 'description' => 'Itens com maior faturamento estimado.', 'icon' => 'mdi-silverware-fork-knife'],
            'r08' => ['title' => 'Mix por categoria', 'group' => 'Produtos', 'description' => 'Participacao de categorias no periodo.', 'icon' => 'mdi-shape-outline'],
            'r09' => ['title' => 'Margem por produto', 'group' => 'Produtos', 'description' => 'Receita, custo e margem estimada por item.', 'icon' => 'mdi-chart-areaspline'],
            'r10' => ['title' => 'Margem por categoria', 'group' => 'Produtos', 'description' => 'Margem consolidada por categoria.', 'icon' => 'mdi-chart-donut-variant'],
            'r11' => ['title' => 'Itens por pedido', 'group' => 'Produtos', 'description' => 'Media de itens e receita por pedido.', 'icon' => 'mdi-format-list-numbered'],
            'r12' => ['title' => 'Mix de pagamentos', 'group' => 'Pagamentos', 'description' => 'Peso financeiro de cada forma de pagamento.', 'icon' => 'mdi-credit-card-outline'],
            'r13' => ['title' => 'Pagamentos por periodo', 'group' => 'Pagamentos', 'description' => 'Evolucao do liquido estimado por forma.', 'icon' => 'mdi-chart-bar-stacked'],
            'r14' => ['title' => 'Troco por forma', 'group' => 'Pagamentos', 'description' => 'Monitoramento de troco total e medio.', 'icon' => 'mdi-cash-refund'],
            'r15' => ['title' => 'Conciliacao pedidos x pagamentos', 'group' => 'Pagamentos', 'description' => 'Diferenca entre total dos pedidos e total pago.', 'icon' => 'mdi-compare-horizontal'],
            'r16' => ['title' => 'Garcons por faturamento', 'group' => 'Atendimento', 'description' => 'Performance de atendimento em mesas.', 'icon' => 'mdi-account-tie-outline'],
            'r17' => ['title' => 'Evolucao por garcom', 'group' => 'Atendimento', 'description' => 'Pedidos e faturamento por periodo e garcom.', 'icon' => 'mdi-account-multiple-outline'],
            'r18' => ['title' => 'Mesas por garcom', 'group' => 'Atendimento', 'description' => 'Distribuicao de faturamento por mesa atendida.', 'icon' => 'mdi-table-chair'],
            'r19' => ['title' => 'Mesas por periodo', 'group' => 'Atendimento', 'description' => 'Faturamento por mesa ao longo do periodo.', 'icon' => 'mdi-table-furniture'],
            'r20' => ['title' => 'Performance por mesa', 'group' => 'Atendimento', 'description' => 'Ticket e ciclo medio por mesa.', 'icon' => 'mdi-table-large'],
            'r21' => ['title' => 'Comandas por periodo', 'group' => 'Atendimento', 'description' => 'Faturamento por comanda em cada periodo.', 'icon' => 'mdi-receipt-text-outline'],
            'r22' => ['title' => 'Clientes por faturamento', 'group' => 'Clientes', 'description' => 'Ranking de clientes com maior receita.', 'icon' => 'mdi-account-star-outline'],
            'r23' => ['title' => 'Novos clientes', 'group' => 'Clientes', 'description' => 'Captacao de novos clientes no periodo.', 'icon' => 'mdi-account-plus-outline'],
            'r24' => ['title' => 'Recorrencia', 'group' => 'Clientes', 'description' => 'Clientes de compra unica x recorrentes.', 'icon' => 'mdi-account-convert-outline'],
            'r25' => ['title' => 'Ultima compra por cliente', 'group' => 'Clientes', 'description' => 'Clientes, faturamento e ultima compra.', 'icon' => 'mdi-history'],
            'r26' => ['title' => 'Status do delivery', 'group' => 'Delivery', 'description' => 'Pedidos por status operacional e de entrega.', 'icon' => 'mdi-bike-fast'],
            'r27' => ['title' => 'Evolucao do delivery', 'group' => 'Delivery', 'description' => 'Pedidos, faturamento e ticket medio do delivery.', 'icon' => 'mdi-map-marker-path'],
            'r28' => ['title' => 'Entregadores', 'group' => 'Delivery', 'description' => 'Performance individual por entregador.', 'icon' => 'mdi-motorbike'],
            'r29' => ['title' => 'Estacoes de cozinha', 'group' => 'Cozinha', 'description' => 'Tempo medio e volume por estacao.', 'icon' => 'mdi-stove'],
            'r30' => ['title' => 'Status da cozinha', 'group' => 'Cozinha', 'description' => 'Itens por status em cada periodo.', 'icon' => 'mdi-chef-hat'],
            'r31' => ['title' => 'Estacao por periodo', 'group' => 'Cozinha', 'description' => 'Volume e tempo medio por estacao ao longo do periodo.', 'icon' => 'mdi-fire-circle'],
            'r32' => ['title' => 'Evolucao de caixas', 'group' => 'Caixa', 'description' => 'Saldo inicial, final e variacao por periodo.', 'icon' => 'mdi-cash-register'],
            'r33' => ['title' => 'Caixas por usuario', 'group' => 'Caixa', 'description' => 'Responsabilidade por abertura e fechamento.', 'icon' => 'mdi-account-cash-outline'],
            'r34' => ['title' => 'Movimentos de caixa', 'group' => 'Caixa', 'description' => 'Entradas e saidas por tipo no periodo.', 'icon' => 'mdi-arrow-left-right-bold-outline'],
            'r35' => ['title' => 'Movimentos por usuario', 'group' => 'Caixa', 'description' => 'Fluxo de caixa por operador e tipo.', 'icon' => 'mdi-account-switch-outline'],
            'r36' => ['title' => 'Posicao de estoque', 'group' => 'Estoque', 'description' => 'Saldo atual e valor de estoque a custo.', 'icon' => 'mdi-package-variant-closed'],
            'r37' => ['title' => 'Movimentos de estoque', 'group' => 'Estoque', 'description' => 'Volume movimentado por tipo e periodo.', 'icon' => 'mdi-warehouse'],
            'r38' => ['title' => 'Movimentos por produto', 'group' => 'Estoque', 'description' => 'Historico de movimentacao por item.', 'icon' => 'mdi-package-up'],
            'r39' => ['title' => 'Divergencia pedido x itens', 'group' => 'Auditoria', 'description' => 'Pedidos com total diferente da soma dos itens.', 'icon' => 'mdi-alert-decagram-outline'],
            'r40' => ['title' => 'Divergencia pedido x pagamentos', 'group' => 'Auditoria', 'description' => 'Pedidos com total diferente do valor pago.', 'icon' => 'mdi-shield-alert-outline'],
        ];
    }
}
