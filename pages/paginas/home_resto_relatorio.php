<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../classe/env.php';
require_once __DIR__ . '/home_resto_analytics.php';

loadEnvFile(__DIR__ . '/../../.env');

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
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'message' => 'Acesso negado.',
    ]);
    exit;
}

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$token = $_SESSION['token'] ?? '';
$empresaId = (string)($_SESSION['empresa_id'] ?? '');
$codigo = strtolower(trim((string)($_GET['codigo'] ?? '')));
$catalog = homeresto_report_catalog();

if (!isset($catalog[$codigo])) {
    http_response_code(404);
    echo json_encode([
        'ok' => false,
        'message' => 'Relatorio nao encontrado.',
    ]);
    exit;
}

if ($apiBase === '' || $token === '' || $empresaId === '') {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'message' => 'Sessao ou configuracao da API indisponivel.',
    ]);
    exit;
}

$filters = homeresto_resolve_filters($_GET);
$report = homeresto_fetch_report($apiBase, $token, $empresaId, $codigo, $filters);

http_response_code($report['ok'] ? 200 : ($report['http_code'] > 0 ? $report['http_code'] : 400));

echo json_encode([
    'ok' => $report['ok'],
    'report' => $report,
    'active_label' => homeresto_describe_filters($filters),
    'query' => homeresto_filter_query_params($filters),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
