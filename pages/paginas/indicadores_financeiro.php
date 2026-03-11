<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../classe/env.php';
loadEnvFile(__DIR__ . '/../../.env');

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$token = $_SESSION['token'] ?? '';
$empresaId = $_SESSION['empresa_id'] ?? '';
$errorMessage = '';
$successMessage = '';

$role = strtolower((string)($_SESSION['user_role'] ?? ''));
$role = str_replace(['-', ' '], '_', $role);
if (strpos($role, 'master') !== false) {
  $role = 'admin_master';
} elseif (strpos($role, 'admin') !== false) {
  $role = 'admin';
}
$nameLower = strtolower((string)($_SESSION['user_name'] ?? ''));
$emailLower = strtolower((string)($_SESSION['user_email'] ?? ''));
if ($role !== 'admin_master' && (strpos($nameLower, 'master') !== false || $emailLower === 'admin@clickresto.com')) {
  $role = 'admin_master';
}
$canAccess = ($role === 'admin' || $role === 'admin_master');

$periodo = trim((string)($_GET['periodo'] ?? 'dia'));
$data = trim((string)($_GET['data'] ?? ''));
$mes = trim((string)($_GET['mes'] ?? ''));
$ano = trim((string)($_GET['ano'] ?? ''));
$dataInicio = trim((string)($_GET['data_inicio'] ?? ''));
$dataFim = trim((string)($_GET['data_fim'] ?? ''));
$statusFiltro = trim((string)($_GET['status'] ?? ''));

if ($data === '') {
  try {
    $data = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d');
  } catch (Exception $e) {
    $data = date('Y-m-d');
  }
}

function formatMoneyIndicadores($value) {
  if ($value === null || $value === '') {
    return '0,00';
  }
  if (is_string($value)) {
    $value = str_replace(',', '.', $value);
  }
  $number = is_numeric($value) ? (float)$value : 0.0;
  return number_format($number, 2, ',', '.');
}

function apiRequestIndicadores($method, $url, $token, &$httpCode = null, $empresaId = '') {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  $headers = [
    'Accept: application/json',
    'Authorization: Bearer ' . $token,
  ];
  if ($empresaId !== '') {
    $headers[] = 'X-Empresa-Id: ' . $empresaId;
  }
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curlError = curl_error($ch);
  $ch = null;

  if ($response === false) {
    return ['error' => 'Falha ao conectar na API. ' . $curlError];
  }
  $data = json_decode($response, true);
  return is_array($data) ? $data : ['raw' => $response];
}

$dashboard = [
  'total_apurado' => 0,
  'total_pedidos' => 0,
  'ticket_medio' => 0,
  'total_troco' => 0,
  'total_taxa_entrega' => 0,
  'totais_por_forma_pagamento' => [],
  'serie_diaria' => [],
  'serie_diaria_por_forma' => [],
  'pedidos' => [],
];

$queryParams = [
  'periodo' => $periodo,
];
if ($periodo === 'dia') {
  $queryParams['data'] = $data;
} elseif ($periodo === 'mes') {
  $queryParams['mes'] = $mes !== '' ? $mes : substr($data, 0, 7);
} elseif ($periodo === 'ano') {
  $queryParams['ano'] = $ano !== '' ? $ano : substr($data, 0, 4);
} elseif ($periodo === 'intervalo') {
  if ($dataInicio !== '') {
    $queryParams['data_inicio'] = $dataInicio;
  }
  if ($dataFim !== '') {
    $queryParams['data_fim'] = $dataFim;
  }
}
if ($statusFiltro !== '') {
  $queryParams['status'] = $statusFiltro;
}

if ($canAccess && $apiBase !== '' && $token !== '') {
  $url = $apiBase . '/relatorios/pagamentos-dashboard';
  $query = http_build_query($queryParams);
  if ($query !== '') {
    $url .= '?' . $query;
  }
  $code = null;
  $resp = apiRequestIndicadores('GET', $url, $token, $code, $empresaId);
  if ($code >= 200 && $code < 300) {
    $dashboard['total_apurado'] = (float)($resp['total_apurado'] ?? 0);
    $dashboard['total_pedidos'] = (int)($resp['total_pedidos'] ?? 0);
    $dashboard['ticket_medio'] = (float)($resp['ticket_medio'] ?? 0);
    $dashboard['total_troco'] = (float)($resp['total_troco'] ?? 0);
    $dashboard['total_taxa_entrega'] = (float)($resp['total_taxa_entrega'] ?? 0);
    $dashboard['totais_por_forma_pagamento'] = is_array($resp['totais_por_forma_pagamento'] ?? null) ? $resp['totais_por_forma_pagamento'] : [];
    $dashboard['serie_diaria'] = is_array($resp['serie_diaria'] ?? null) ? $resp['serie_diaria'] : [];
    $dashboard['serie_diaria_por_forma'] = is_array($resp['serie_diaria_por_forma'] ?? null) ? $resp['serie_diaria_por_forma'] : [];
    $dashboard['pedidos'] = is_array($resp['pedidos'] ?? null) ? $resp['pedidos'] : [];
  } else {
    $errorMessage = $resp['message'] ?? 'Nao foi possivel carregar indicadores financeiros.';
  }
} elseif (!$canAccess) {
  $errorMessage = 'Acesso restrito ao perfil admin.';
} else {
  $errorMessage = 'Token ou API_BASE_URL nao configurados.';
}

$exportQuery = $queryParams;
$exportUrlCsv = $apiBase . '/relatorios/pagamentos-dashboard/export';
$exportUrlXls = $apiBase . '/relatorios/pagamentos-dashboard/export';
if ($exportQuery) {
  $exportUrlCsv .= '?' . http_build_query($exportQuery);
  $exportQuery['formato'] = 'excel';
  $exportUrlXls .= '?' . http_build_query($exportQuery);
}

$serieLabels = [];
$serieTotais = [];
foreach ($dashboard['serie_diaria'] as $row) {
  if (is_object($row)) {
    $row = (array)$row;
  }
  if (!is_array($row)) {
    continue;
  }
  $serieLabels[] = $row['data'] ?? '';
  $serieTotais[] = (float)($row['total_apurado'] ?? 0);
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
?>
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-sm-12">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <div>
            <h3 class="mb-1">Indicadores Financeiros</h3>
            <p class="text-muted mb-0">Resumo de pagamentos e vendas.</p>
          </div>
          <div class="btn-wrapper d-flex gap-2">
            <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars((string)$exportUrlCsv, ENT_QUOTES, 'UTF-8'); ?>">Exportar CSV</a>
            <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars((string)$exportUrlXls, ENT_QUOTES, 'UTF-8'); ?>">Exportar Excel</a>
          </div>
        </div>
      </div>
    </div>

    <?php if ($errorMessage !== ''): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if ($canAccess): ?>
    <div class="card card-rounded mb-4">
      <div class="card-body">
        <form class="row g-2 align-items-end" method="GET" action="">
          <input type="hidden" name="paginas" value="indicadores_financeiro">
          <div class="col-md-2">
            <label class="form-label">Periodo</label>
            <select name="periodo" id="periodoFinanceiro" class="form-select">
              <option value="dia" <?php echo $periodo === 'dia' ? 'selected' : ''; ?>>Dia</option>
              <option value="mes" <?php echo $periodo === 'mes' ? 'selected' : ''; ?>>Mes</option>
              <option value="ano" <?php echo $periodo === 'ano' ? 'selected' : ''; ?>>Ano</option>
              <option value="intervalo" <?php echo $periodo === 'intervalo' ? 'selected' : ''; ?>>Intervalo</option>
            </select>
          </div>
          <div class="col-md-2" id="filtroDataDia">
            <label class="form-label">Data</label>
            <input type="date" name="data" class="form-control" value="<?php echo htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="col-md-2" id="filtroMes">
            <label class="form-label">Mes</label>
            <input type="month" name="mes" class="form-control" value="<?php echo htmlspecialchars((string)$mes, ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="col-md-2" id="filtroAno">
            <label class="form-label">Ano</label>
            <input type="number" name="ano" class="form-control" min="2000" max="2100" value="<?php echo htmlspecialchars((string)$ano, ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="col-md-2" id="filtroInicio">
            <label class="form-label">Inicio</label>
            <input type="date" name="data_inicio" class="form-control" value="<?php echo htmlspecialchars((string)$dataInicio, ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="col-md-2" id="filtroFim">
            <label class="form-label">Fim</label>
            <input type="date" name="data_fim" class="form-control" value="<?php echo htmlspecialchars((string)$dataFim, ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Status pedidos (opcional)</label>
            <input type="text" name="status" class="form-control" placeholder="fechado,entregue" value="<?php echo htmlspecialchars((string)$statusFiltro, ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Atualizar</button>
          </div>
        </form>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Total apurado</p>
            <h3 class="mb-0">R$ <?php echo htmlspecialchars(formatMoneyIndicadores($dashboard['total_apurado']), ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Total de pedidos</p>
            <h3 class="mb-0"><?php echo htmlspecialchars((string)$dashboard['total_pedidos'], ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Ticket medio</p>
            <h3 class="mb-0">R$ <?php echo htmlspecialchars(formatMoneyIndicadores($dashboard['ticket_medio']), ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Troco / Taxa entrega</p>
            <h3 class="mb-0">R$ <?php echo htmlspecialchars(formatMoneyIndicadores($dashboard['total_troco']), ENT_QUOTES, 'UTF-8'); ?>
              <small class="text-muted">/ <?php echo htmlspecialchars(formatMoneyIndicadores($dashboard['total_taxa_entrega']), ENT_QUOTES, 'UTF-8'); ?></small>
            </h3>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <h4 class="mb-3">Evolucao diaria</h4>
            <canvas id="serieDiariaChart" height="120"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-4 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <h4 class="mb-3">Totais por forma</h4>
            <canvas id="formasChart" height="200"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <h4 class="mb-3">Totais por forma de pagamento</h4>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>Forma</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($dashboard['totais_por_forma_pagamento'])): ?>
                    <tr><td colspan="2">Sem dados no periodo.</td></tr>
                  <?php else: ?>
                    <?php foreach ($dashboard['totais_por_forma_pagamento'] as $row): ?>
                      <?php
                        if (is_object($row)) {
                          $row = (array)$row;
                        }
                        if (!is_array($row)) {
                          continue;
                        }
                        $nome = $row['nome'] ?? $row['forma_pagamento'] ?? '';
                        $total = $row['total'] ?? $row['total_apurado'] ?? 0;
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>R$ <?php echo htmlspecialchars(formatMoneyIndicadores($total), ENT_QUOTES, 'UTF-8'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
      function togglePeriodoFields() {
        const periodo = document.getElementById('periodoFinanceiro').value;
        document.getElementById('filtroDataDia').style.display = periodo === 'dia' ? 'block' : 'none';
        document.getElementById('filtroMes').style.display = periodo === 'mes' ? 'block' : 'none';
        document.getElementById('filtroAno').style.display = periodo === 'ano' ? 'block' : 'none';
        document.getElementById('filtroInicio').style.display = periodo === 'intervalo' ? 'block' : 'none';
        document.getElementById('filtroFim').style.display = periodo === 'intervalo' ? 'block' : 'none';
      }
      document.getElementById('periodoFinanceiro').addEventListener('change', togglePeriodoFields);
      togglePeriodoFields();

      const serieLabels = <?php echo json_encode($serieLabels, JSON_UNESCAPED_UNICODE); ?>;
      const serieTotais = <?php echo json_encode($serieTotais, JSON_UNESCAPED_UNICODE); ?>;
      const formasLabels = <?php echo json_encode($formasLabels, JSON_UNESCAPED_UNICODE); ?>;
      const formasTotais = <?php echo json_encode($formasTotais, JSON_UNESCAPED_UNICODE); ?>;

      if (window.Chart && document.getElementById('serieDiariaChart')) {
        new Chart(document.getElementById('serieDiariaChart'), {
          type: 'line',
          data: {
            labels: serieLabels,
            datasets: [{
              label: 'Total apurado',
              data: serieTotais,
              borderColor: '#1f3bb3',
              backgroundColor: 'rgba(31,59,179,0.1)',
              fill: true,
              tension: 0.3
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
              y: { beginAtZero: true }
            }
          }
        });
      }

      if (window.Chart && document.getElementById('formasChart')) {
        new Chart(document.getElementById('formasChart'), {
          type: 'doughnut',
          data: {
            labels: formasLabels,
            datasets: [{
              data: formasTotais,
              backgroundColor: ['#1f3bb3', '#f6c23e', '#e74a3b', '#1cc88a', '#36b9cc', '#858796']
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
          }
        });
      }
    </script>
    <?php endif; ?>
  </div>
</div>
