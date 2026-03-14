<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$homeRestoCanRender = false;

$role = strtolower((string)($_SESSION['user_role'] ?? ''));
$role = str_replace(['-', ' '], '_', $role);
if (strpos($role, 'master') !== false) {
    $role = 'admin_master';
} elseif (strpos($role, 'admin') !== false) {
    $role = 'admin';
} else {
    $role = 'admin';
}

if ($role === 'admin_master') {
    $nameLower = strtolower((string)($_SESSION['user_name'] ?? ''));
    $emailLower = strtolower((string)($_SESSION['user_email'] ?? ''));
    if (!(strpos($nameLower, 'master') !== false || $emailLower === 'admin@clickresto.com')) {
        $role = 'admin';
    }
}

if ($role !== 'admin') {
    return;
}

require_once __DIR__ . '/../../classe/env.php';
require_once __DIR__ . '/home_resto_analytics.php';

loadEnvFile(__DIR__ . '/../../.env');

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$token = $_SESSION['token'] ?? '';
$empresaId = (string)($_SESSION['empresa_id'] ?? '');
$jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

$esc = static function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$normalizeDate = static function ($value, $fallback) {
    $value = trim((string)$value);
    if ($value === '') {
        return $fallback;
    }
    $timestamp = strtotime($value);
    return $timestamp === false ? $fallback : date('Y-m-d', $timestamp);
};

$toFloat = static function ($value) {
    if (is_string($value)) {
        $value = str_replace(',', '.', $value);
    }

    return is_numeric($value) ? (float)$value : 0.0;
};

$now = homeresto_now();
$today = $now->format('Y-m-d');
$firstDayMonth = (clone $now)->modify('first day of this month')->format('Y-m-d');
$last7Start = (clone $now)->modify('-6 days')->format('Y-m-d');

$periodo = strtolower(trim((string)($_GET['periodo'] ?? 'dia')));
if ($periodo === 'intervalo') {
    $periodo = 'periodo';
}

$allowedPeriodos = ['dia', 'semana', 'mes', 'ano', 'periodo'];
if (!in_array($periodo, $allowedPeriodos, true)) {
    $periodo = 'dia';
}

$data = $normalizeDate($_GET['data'] ?? '', $today);
$mes = trim((string)($_GET['mes'] ?? substr($today, 0, 7)));
$ano = trim((string)($_GET['ano'] ?? substr($today, 0, 4)));
$defaultDataInicio = $periodo === 'semana' ? $last7Start : $firstDayMonth;
$dataInicio = $normalizeDate($_GET['data_inicio'] ?? '', $defaultDataInicio);
$dataFim = $normalizeDate($_GET['data_fim'] ?? '', $today);

$errors = [];
$warnings = [];
$operational = [];
$dashboard = [
    'periodo' => $periodo,
    'intervalo' => ['inicio' => $dataInicio, 'fim' => $dataFim],
    'total_apurado' => 0,
    'total_pedidos' => 0,
    'ticket_medio' => 0,
    'total_troco' => 0,
    'total_taxa_entrega' => 0,
    'serie_diaria' => [],
    'serie_diaria_por_forma' => [],
    'totais_por_forma_pagamento' => [],
];

$queryParams = ['periodo' => $periodo];
if ($periodo === 'dia') {
    $queryParams['data'] = $data;
} elseif ($periodo === 'mes') {
    $queryParams['mes'] = $mes !== '' ? $mes : substr($today, 0, 7);
} elseif ($periodo === 'ano') {
    $queryParams['ano'] = preg_match('/^\d{4}$/', $ano) ? $ano : substr($today, 0, 4);
} else {
    $queryParams['periodo'] = 'intervalo';
    $queryParams['data_inicio'] = $dataInicio;
    $queryParams['data_fim'] = $dataFim;
    if (strtotime($dataInicio) > strtotime($dataFim)) {
        $errors[] = 'Periodo invalido';
    }
}

if ($apiBase === '' || $token === '' || $empresaId === '') {
    $errors[] = 'Token, empresa ou API_BASE_URL nao configurados.';
} else {
    $operationalResponse = homeresto_fetch_operational($apiBase, $token, $empresaId);
    if ($operationalResponse['ok']) {
        $operational = $operationalResponse['data'];
    } else {
        $warnings[] = $operationalResponse['message'];
    }

    if (empty($errors)) {
        $dashboardUrl = $apiBase . '/relatorios/pagamentos-dashboard?' . http_build_query($queryParams);
        $dashboardResponse = homeresto_api_get_json($dashboardUrl, $token, $empresaId);

        if ($dashboardResponse['ok']) {
            $resp = $dashboardResponse['json'];
            $dashboard['periodo'] = $resp['periodo'] ?? $queryParams['periodo'];
            $dashboard['intervalo'] = is_array($resp['intervalo'] ?? null) ? $resp['intervalo'] : $dashboard['intervalo'];
            $dashboard['total_apurado'] = (float)($resp['total_apurado'] ?? 0);
            $dashboard['total_pedidos'] = (int)($resp['total_pedidos'] ?? 0);
            $dashboard['ticket_medio'] = (float)($resp['ticket_medio'] ?? 0);
            $dashboard['total_troco'] = (float)($resp['total_troco'] ?? 0);
            $dashboard['total_taxa_entrega'] = (float)($resp['total_taxa_entrega'] ?? 0);
            $dashboard['serie_diaria'] = is_array($resp['serie_diaria'] ?? null) ? $resp['serie_diaria'] : [];
            $dashboard['serie_diaria_por_forma'] = is_array($resp['serie_diaria_por_forma'] ?? null) ? $resp['serie_diaria_por_forma'] : [];
            $dashboard['totais_por_forma_pagamento'] = is_array($resp['totais_por_forma_pagamento'] ?? null) ? $resp['totais_por_forma_pagamento'] : [];
        } else {
            $errors[] = $dashboardResponse['message'] !== '' ? $dashboardResponse['message'] : 'Nao foi possivel carregar o dashboard.';
        }
    }
}

$intervaloInicio = $dashboard['intervalo']['inicio'] ?? $dataInicio;
$intervaloFim = $dashboard['intervalo']['fim'] ?? $dataFim;
$periodLabel = date('d/m/Y', strtotime($intervaloInicio));
if ($intervaloInicio !== $intervaloFim) {
    $periodLabel .= ' a ' . date('d/m/Y', strtotime($intervaloFim));
}
if ($periodo === 'semana') {
    $periodLabel = 'Semana: ' . date('d/m/Y', strtotime($intervaloInicio)) . ' a ' . date('d/m/Y', strtotime($intervaloFim));
}
if ($periodo === 'mes') {
    $periodLabel = substr($queryParams['mes'], 5, 2) . '/' . substr($queryParams['mes'], 0, 4);
}
if ($periodo === 'ano') {
    $periodLabel = (string)$queryParams['ano'];
}

$pedidosDia = (int)($operational['pedidos_dia'] ?? 0);
$pedidosDelta = $operational['pedidos_delta_percent'] ?? null;
$ticketMedioOperacional = (float)($operational['ticket_medio'] ?? 0);
$mesasOcupadas = (int)($operational['mesas_ocupadas'] ?? 0);
$totalMesas = (int)($operational['total_mesas'] ?? 0);
$entregasRota = (int)($operational['entregas_rota'] ?? 0);
$itensCozinha = (int)($operational['itens_cozinha'] ?? 0);
$estoqueCritico = (int)($operational['estoque_critico'] ?? 0);
$faturamentoDia = (float)($operational['faturamento_dia'] ?? 0);
$metaPercent = (float)($operational['meta_percent'] ?? 0);
$cancelamentos = (int)($operational['cancelamentos'] ?? 0);
$itensAtrasados = isset($operational['itens_atrasados']) ? (int)$operational['itens_atrasados'] : ($itensCozinha > 0 ? min($itensCozinha, 6) : 0);

$operationalCards = [
    ['tone' => 'sky', 'icon' => 'mdi-cart-outline', 'label' => 'Pedidos do dia', 'value' => homeresto_format_number($pedidosDia), 'meta' => $pedidosDelta === null ? 'Sem comparativo' : (($pedidosDelta >= 0 ? '+' : '') . homeresto_format_number($pedidosDelta, 1) . '% vs ontem')],
    ['tone' => 'teal', 'icon' => 'mdi-currency-usd', 'label' => 'Ticket medio', 'value' => homeresto_format_money($ticketMedioOperacional), 'meta' => 'Salao + delivery'],
    ['tone' => 'amber', 'icon' => 'mdi-table-chair', 'label' => 'Mesas ocupadas', 'value' => homeresto_format_number($mesasOcupadas) . ' / ' . homeresto_format_number($totalMesas > 0 ? $totalMesas : 4), 'meta' => 'Pico de movimento'],
    ['tone' => 'mint', 'icon' => 'mdi-bike-fast', 'label' => 'Entregas em rota', 'value' => homeresto_format_number($entregasRota), 'meta' => 'Delivery ativo'],
    ['tone' => 'coral', 'icon' => 'mdi-fire', 'label' => 'Itens na cozinha', 'value' => homeresto_format_number($itensCozinha), 'meta' => $itensAtrasados > 0 ? homeresto_format_number($itensAtrasados) . ' atrasados' : 'Fila sob controle'],
    ['tone' => 'rose', 'icon' => 'mdi-alert-outline', 'label' => 'Estoque critico', 'value' => homeresto_format_number($estoqueCritico) . ' itens', 'meta' => 'Repor hoje'],
    ['tone' => 'indigo', 'icon' => 'mdi-cash-multiple', 'label' => 'Faturamento dia', 'value' => homeresto_format_money($faturamentoDia), 'meta' => 'Meta ' . homeresto_format_percent($metaPercent, 0)],
    ['tone' => 'slate', 'icon' => 'mdi-close-circle-outline', 'label' => 'Cancelamentos', 'value' => homeresto_format_number($cancelamentos), 'meta' => 'Ultimas 24h'],
];

$financialCards = [
    ['tone' => 'navy', 'label' => 'Total apurado', 'value' => homeresto_format_money($dashboard['total_apurado']), 'meta' => $periodLabel],
    ['tone' => 'ocean', 'label' => 'Total de pedidos', 'value' => homeresto_format_number($dashboard['total_pedidos']), 'meta' => 'No periodo selecionado'],
    ['tone' => 'moss', 'label' => 'Ticket medio', 'value' => homeresto_format_money($dashboard['ticket_medio']), 'meta' => 'Media por pedido'],
    ['tone' => 'sand', 'label' => 'Troco total', 'value' => homeresto_format_money($dashboard['total_troco']), 'meta' => 'Operacao financeira'],
    ['tone' => 'violet', 'label' => 'Taxa de entrega', 'value' => homeresto_format_money($dashboard['total_taxa_entrega']), 'meta' => 'Receita de delivery'],
];

$serieLabels = [];
$serieTotais = [];
$serieTickets = [];
foreach ($dashboard['serie_diaria'] as $row) {
    if (is_object($row)) {
        $row = (array)$row;
    }
    if (!is_array($row)) {
        continue;
    }
    $serieLabels[] = $row['data'] ?? '';
    $serieTotais[] = (float)($row['total_apurado'] ?? 0);
    $serieTickets[] = (float)($row['ticket_medio'] ?? 0);
}

$formasLabels = [];
$formasTotais = [];
foreach ($dashboard['totais_por_forma_pagamento'] as $row) {
    if (is_object($row)) {
        $row = (array)$row;
    }
    if (!is_array($row)) {
        continue;
    }
    $formasLabels[] = $row['nome'] ?? $row['forma_pagamento'] ?? '';
    $formasTotais[] = (float)($row['total'] ?? $row['total_apurado'] ?? 0);
}

$quickLinks = [
    ['label' => 'Hoje', 'url' => 'index.php?paginas=HomeResto&periodo=dia&data=' . $today],
    ['label' => 'Semana', 'url' => 'index.php?paginas=HomeResto&periodo=semana&data_inicio=' . $last7Start . '&data_fim=' . $today],
    ['label' => 'Mes atual', 'url' => 'index.php?paginas=HomeResto&periodo=mes&mes=' . substr($today, 0, 7)],
    ['label' => 'Ano atual', 'url' => 'index.php?paginas=HomeResto&periodo=ano&ano=' . substr($today, 0, 4)],
    ['label' => 'Periodo', 'url' => 'index.php?paginas=HomeResto&periodo=periodo&data_inicio=' . $firstDayMonth . '&data_fim=' . $today],
];

$analyticsInput = [
    'tipo_filtro' => 'periodo',
    'tipo_agrupamento' => $periodo === 'ano' ? 'mes' : 'dia',
    'dia_ref' => $intervaloFim,
    'data_inicio' => $intervaloInicio,
    'data_fim' => $intervaloFim,
];

if ($periodo === 'dia') {
    $analyticsInput['tipo_filtro'] = 'dia';
    $analyticsInput['dia_ref'] = $data;
    $analyticsInput['data_inicio'] = $data;
    $analyticsInput['data_fim'] = $data;
} elseif ($periodo === 'mes') {
    $monthRef = preg_match('/^\d{4}-\d{2}$/', $queryParams['mes'] ?? '') ? ($queryParams['mes'] . '-01') : (substr($today, 0, 7) . '-01');
    $analyticsInput['tipo_filtro'] = 'mes';
    $analyticsInput['dia_ref'] = date('Y-m-d', strtotime($monthRef));
    $analyticsInput['data_inicio'] = date('Y-m-01', strtotime($monthRef));
    $analyticsInput['data_fim'] = date('Y-m-t', strtotime($monthRef));
} elseif ($periodo === 'ano') {
    $yearRef = preg_match('/^\d{4}$/', $queryParams['ano'] ?? '') ? (string)$queryParams['ano'] : substr($today, 0, 4);
    $analyticsInput['tipo_filtro'] = 'ano';
    $analyticsInput['dia_ref'] = $yearRef . '-01-01';
    $analyticsInput['data_inicio'] = $yearRef . '-01-01';
    $analyticsInput['data_fim'] = $yearRef . '-12-31';
}

$analyticsFilters = homeresto_resolve_filters($analyticsInput);
$userReports = [
    'r16' => ['ok' => false, 'dados' => [], 'message' => ''],
    'r17' => ['ok' => false, 'dados' => [], 'message' => ''],
    'r18' => ['ok' => false, 'dados' => [], 'message' => ''],
];
$userIndicatorsNotice = '';
$missingUserRoutes = [];

if ($apiBase !== '' && $token !== '' && $empresaId !== '' && empty($errors)) {
    foreach (['r16', 'r17', 'r18'] as $reportCode) {
        $reportResponse = homeresto_fetch_report($apiBase, $token, $empresaId, $reportCode, $analyticsFilters);
        $userReports[$reportCode] = $reportResponse;
        if (!$reportResponse['ok']) {
            $messageLower = strtolower((string)$reportResponse['message']);
            $isMissingRoute = (int)$reportResponse['http_code'] === 404 && strpos($messageLower, 'route') !== false;

            if ($isMissingRoute) {
                $missingUserRoutes[] = strtoupper($reportCode);
                continue;
            }

            $warnings[] = $reportResponse['message'];
        }
    }
}

if (!empty($missingUserRoutes)) {
    $userIndicatorsNotice = 'A API atual nao publicou ' . implode(', ', $missingUserRoutes) . '. A secao sera liberada automaticamente quando essas rotas estiverem disponiveis no backend.';
}

$r16Rows = $userReports['r16']['dados'] ?? [];
$r17Rows = $userReports['r17']['dados'] ?? [];
$r18Rows = $userReports['r18']['dados'] ?? [];

$topUser = [];
$topUserRevenue = 0.0;
foreach ($r16Rows as $row) {
    if (!is_array($row)) {
        continue;
    }

    $rowRevenue = $toFloat($row['faturamento'] ?? 0);
    if ($rowRevenue > $topUserRevenue) {
        $topUserRevenue = $rowRevenue;
        $topUser = $row;
    }
}

$totalUserRevenue = homeresto_sum_column($r16Rows, 'faturamento');
$totalUserTables = homeresto_sum_column($r16Rows, 'mesas_atendidas');
$totalUserOrders = homeresto_sum_column($r16Rows, 'pedidos_fechados');
$avgUserCycle = !empty($r16Rows) ? homeresto_sum_column($r16Rows, 'ciclo_medio_min') / count($r16Rows) : 0;
$avgUserTicket = !empty($r16Rows) ? homeresto_sum_column($r16Rows, 'ticket_medio') / count($r16Rows) : 0;

$userCards = [
    [
        'tone' => 'navy',
        'icon' => 'mdi-account-star-outline',
        'label' => 'Usuario destaque',
        'value' => $topUser['usuario'] ?? 'Sem dados',
        'meta' => $topUserRevenue > 0 ? homeresto_format_money($topUserRevenue) : 'Sem faturamento',
    ],
    [
        'tone' => 'ocean',
        'icon' => 'mdi-cash-register',
        'label' => 'Faturamento por usuario',
        'value' => homeresto_format_money($totalUserRevenue),
        'meta' => homeresto_format_number($totalUserOrders) . ' pedidos fechados',
    ],
    [
        'tone' => 'moss',
        'icon' => 'mdi-table-furniture',
        'label' => 'Mesas atendidas',
        'value' => homeresto_format_number($totalUserTables),
        'meta' => 'Leitura consolidada do R18',
    ],
    [
        'tone' => 'sand',
        'icon' => 'mdi-timer-outline',
        'label' => 'Ciclo medio',
        'value' => homeresto_format_number($avgUserCycle, 1) . ' min',
        'meta' => 'Ticket medio ' . homeresto_format_money($avgUserTicket),
    ],
];

$r16ChartLabels = [];
$r16ChartRevenue = [];
$r16ChartTables = [];
foreach (array_slice($r16Rows, 0, 8) as $row) {
    if (!is_array($row)) {
        continue;
    }

    $r16ChartLabels[] = $row['usuario'] ?? 'Sem usuario';
    $r16ChartRevenue[] = $toFloat($row['faturamento'] ?? 0);
    $r16ChartTables[] = $toFloat($row['mesas_atendidas'] ?? 0);
}

$r17Periods = [];
$r17TotalsByUser = [];
foreach ($r17Rows as $row) {
    if (!is_array($row)) {
        continue;
    }

    $periodKey = (string)($row['periodo'] ?? '');
    $userKey = (string)($row['usuario'] ?? 'Sem usuario');
    if ($periodKey === '') {
        continue;
    }

    $r17Periods[$periodKey] = true;
    if (!isset($r17TotalsByUser[$userKey])) {
        $r17TotalsByUser[$userKey] = ['total' => 0.0, 'points' => []];
    }

    $value = $toFloat($row['faturamento'] ?? 0);
    $r17TotalsByUser[$userKey]['total'] += $value;
    $r17TotalsByUser[$userKey]['points'][$periodKey] = $value;
}

$r17ChartLabels = array_keys($r17Periods);
sort($r17ChartLabels);
$r17ChartLabelsFormatted = [];
foreach ($r17ChartLabels as $label) {
    $timestamp = strtotime($label);
    $r17ChartLabelsFormatted[] = $timestamp === false ? $label : date('d/m', $timestamp);
}

uasort($r17TotalsByUser, static function ($left, $right) {
    return $right['total'] <=> $left['total'];
});

$userTrendPalette = ['#0f6cbd', '#0891b2', '#0f766e', '#c27b0a'];
$r17ChartDatasets = [];
$datasetIndex = 0;
foreach ($r17TotalsByUser as $userName => $dataSet) {
    if ($datasetIndex >= 4) {
        break;
    }

    $color = $userTrendPalette[$datasetIndex % count($userTrendPalette)];
    $points = [];
    foreach ($r17ChartLabels as $periodKey) {
        $points[] = $dataSet['points'][$periodKey] ?? 0;
    }

    $r17ChartDatasets[] = [
        'label' => $userName,
        'data' => $points,
        'borderColor' => $color,
        'backgroundColor' => $color . '1A',
    ];
    $datasetIndex++;
}

$homeRestoCanRender = true;
