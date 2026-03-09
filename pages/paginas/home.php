<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$role = strtolower((string)($_SESSION['user_role'] ?? ''));
$role = str_replace(['-', ' '], '_', $role);
if (strpos($role, 'master') !== false) {
  $role = 'admin_master';
} elseif (strpos($role, 'admin') !== false) {
  $role = 'admin';
} else {
  $role = 'admin';
}
if ($role !== 'admin_master') {
  $nameLower = strtolower((string)($_SESSION['user_name'] ?? ''));
  $emailLower = strtolower((string)($_SESSION['user_email'] ?? ''));
  if (strpos($nameLower, 'master') !== false || $emailLower === 'admin@clickresto.com') {
    $role = 'admin_master';
  }
}
if ($role !== 'admin_master') {
  return;
}
require_once __DIR__ . '/../../classe/env.php';
loadEnvFile(__DIR__ . '/../../.env');

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$token = $_SESSION['token'] ?? '';
$errorMessage = '';
$overview = [];

function normalizeChartSeries($data, $labelKeys, $valueKeys, $fallback) {
  if (is_array($data) && isset($data['labels'], $data['values']) && is_array($data['labels']) && is_array($data['values'])) {
    return [
      'labels' => array_values($data['labels']),
      'values' => array_map('floatval', $data['values']),
    ];
  }
  $labels = [];
  $values = [];
  if (is_array($data)) {
    foreach ($data as $item) {
      if (!is_array($item)) {
        continue;
      }
      $label = null;
      foreach ($labelKeys as $key) {
        if (isset($item[$key])) {
          $label = $item[$key];
          break;
        }
      }
      $value = null;
      foreach ($valueKeys as $key) {
        if (isset($item[$key])) {
          $value = $item[$key];
          break;
        }
      }
      if ($label !== null && $value !== null) {
        $labels[] = (string)$label;
        $values[] = (float)$value;
      }
    }
  }
  if (empty($labels)) {
    return $fallback;
  }
  return ['labels' => $labels, 'values' => $values];
}

if ($apiBase !== '' && $token !== '') {
  $ch = curl_init($apiBase . '/saas/overview');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
  ]);
  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curlError = curl_error($ch);
  $ch = null;

  if ($response === false) {
    $errorMessage = 'Falha ao conectar na API. ' . $curlError;
  } else {
    $data = json_decode($response, true);
    if ($httpCode >= 200 && $httpCode < 300) {
      $overview = $data['data'] ?? $data;
      if (!is_array($overview)) {
        $overview = [];
      }
    } else {
      $errorMessage = $data['message'] ?? 'Nao foi possivel carregar o dashboard.';
    }
  }
} else {
  $errorMessage = 'Token ou API_BASE_URL nao configurados.';
}

$stats = [
  'total_empresas' => $overview['total_empresas'] ?? $overview['empresas_total'] ?? 0,
  'empresas_ativas' => $overview['empresas_ativas'] ?? 0,
  'empresas_suspensas' => $overview['empresas_suspensas'] ?? 0,
  'empresas_trial' => $overview['empresas_trial'] ?? $overview['empresas_teste'] ?? 0,
  'mrr' => $overview['mrr'] ?? $overview['receita_mensal'] ?? 0,
  'licencas_ativas' => $overview['licencas_ativas'] ?? 0,
];

$charts = $overview['charts'] ?? [];
$empresasSeries = normalizeChartSeries(
  $charts['empresas'] ?? $charts['crescimento_empresas'] ?? $overview['crescimento_empresas'] ?? [],
  ['mes', 'label', 'periodo'],
  ['total', 'value', 'quantidade'],
  ['labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'], 'values' => [12, 18, 26, 30, 38, 44]]
);
$receitaSeries = normalizeChartSeries(
  $charts['receita'] ?? $charts['receita_mensal'] ?? $overview['receita_mensal'] ?? [],
  ['mes', 'label', 'periodo'],
  ['valor', 'value', 'total'],
  ['labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'], 'values' => [42, 55, 61, 68, 75, 84]]
);
$cadastroSeries = normalizeChartSeries(
  $charts['cadastros'] ?? $charts['novos_cadastros'] ?? $overview['novos_cadastros'] ?? [],
  ['origem', 'label', 'canal'],
  ['total', 'value', 'quantidade'],
  ['labels' => ['Trial', 'Indicado', 'Pago'], 'values' => [48, 32, 20]]
);

$recentes = $overview['empresas_recentes'] ?? $overview['recentes'] ?? [];
?>
<div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                  <div>
                    <h3 class="mb-1">Dashboard SaaS</h3>
                    <p class="text-muted mb-0">Visao geral do ecossistema do SaaS para admin_master.</p>
                  </div>
                  <div class="btn-wrapper">
                    <a href="index.php?paginas=empresa_form" class="btn btn-primary text-white me-2"><i class="mdi mdi-plus"></i> Criar empresa</a>
                    <a href="index.php?paginas=relatorios" class="btn btn-outline-secondary me-2"><i class="mdi mdi-chart-areaspline"></i> Ver relatorios</a>
                    <a href="index.php?paginas=configuracoes" class="btn btn-outline-secondary"><i class="mdi mdi-cog-outline"></i> Configuracoes</a>
                  </div>
                </div>
              </div>
            </div>

            <?php if ($errorMessage !== ''): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="row">
              <div class="col-sm-6 col-xl-2 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Empresas cadastradas</p>
                    <h3 class="mb-0"><?php echo (int)$stats['total_empresas']; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-2 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Empresas ativas</p>
                    <h3 class="mb-0 text-success"><?php echo (int)$stats['empresas_ativas']; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-2 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Empresas suspensas</p>
                    <h3 class="mb-0 text-danger"><?php echo (int)$stats['empresas_suspensas']; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-2 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Em periodo de teste</p>
                    <h3 class="mb-0 text-warning"><?php echo (int)$stats['empresas_trial']; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-2 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">MRR</p>
                    <h3 class="mb-0">R$ <?php echo number_format((float)$stats['mrr'], 2, ',', '.'); ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-2 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Licencas ativas</p>
                    <h3 class="mb-0"><?php echo (int)$stats['licencas_ativas']; ?></h3>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-8 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <div class="d-sm-flex justify-content-between align-items-start">
                      <div>
                        <h4 class="card-title card-title-dash">Crescimento de empresas</h4>
                        <p class="card-subtitle card-subtitle-dash">Novos clientes mes a mes.</p>
                      </div>
                      <div class="btn-wrapper">
                        <button class="btn btn-outline-secondary btn-sm">Ultimos 12 meses</button>
                      </div>
                    </div>
                    <div class="chartjs-bar-wrapper mt-3">
                      <canvas id="chartEmpresas"></canvas>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-4 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Receita mensal</h4>
                    <p class="card-subtitle card-subtitle-dash">MRR e crescimento.</p>
                    <div class="mt-3">
                      <canvas id="chartReceita"></canvas>
                    </div>
                    <div class="mt-4">
                      <div class="d-flex justify-content-between">
                        <span class="text-muted">Crescimento</span>
                        <span class="text-success">+12.4%</span>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-muted">Churn</span>
                        <span class="text-danger">-2.1%</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-7 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Empresas recentes</h4>
                    <p class="card-subtitle card-subtitle-dash">Cadastros mais recentes no sistema.</p>
                    <div class="table-responsive mt-3">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Empresa</th>
                            <th>Plano</th>
                            <th>Status</th>
                            <th>Cadastro</th>
                            <th>Acoes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($recentes)): ?>
                            <tr><td colspan="5">Sem registros recentes.</td></tr>
                          <?php else: ?>
                            <?php foreach ($recentes as $empresa): ?>
                              <?php
                                $nome = $empresa['nome'] ?? $empresa['empresa'] ?? '';
                                $plano = $empresa['plano'] ?? $empresa['plano_nome'] ?? '';
                                if (is_array($plano)) {
                                  $plano = $plano['nome'] ?? '';
                                }
                                $status = $empresa['status'] ?? 'ativo';
                                $cadastro = $empresa['data_cadastro'] ?? $empresa['created_at'] ?? '-';
                              ?>
                              <tr>
                                <td><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string)$plano, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                  <?php if ($status === 'ativo'): ?>
                                    <div class="badge badge-opacity-success">Ativa</div>
                                  <?php elseif ($status === 'trial'): ?>
                                    <div class="badge badge-opacity-warning">Trial</div>
                                  <?php elseif ($status === 'suspenso'): ?>
                                    <div class="badge badge-opacity-danger">Suspensa</div>
                                  <?php else: ?>
                                    <div class="badge badge-opacity-secondary"><?php echo htmlspecialchars((string)$status, ENT_QUOTES, 'UTF-8'); ?></div>
                                  <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars((string)$cadastro, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                  <button class="btn btn-outline-primary btn-sm me-2"><i class="mdi mdi-eye"></i></button>
                                  <button class="btn btn-outline-secondary btn-sm"><i class="mdi mdi-login"></i></button>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-5 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Novos cadastros</h4>
                    <p class="card-subtitle card-subtitle-dash">Crescimento por origem.</p>
                    <div class="mt-3">
                      <canvas id="chartCadastros"></canvas>
                    </div>
                    <div class="mt-4">
                      <?php foreach ($cadastroSeries['labels'] as $idx => $label): ?>
                        <div class="d-flex justify-content-between">
                          <span class="text-muted"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                          <span><?php echo (int)$cadastroSeries['values'][$idx]; ?>%</span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- content-wrapper ends -->
          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Premium <a href="https://www.bootstrapdash.com/" target="_blank">Bootstrap admin template</a> from BootstrapDash.</span>
              <span class="float-none float-sm-end d-block mt-1 mt-sm-0 text-center">Copyright Â© 2023. All rights reserved.</span>
            </div>
          </footer>
          <script>
            (function () {
              if (typeof Chart === 'undefined') {
                return;
              }
              const empresasCtx = document.getElementById('chartEmpresas');
              if (empresasCtx) {
                new Chart(empresasCtx, {
                  type: 'bar',
                  data: {
                    labels: <?php echo json_encode($empresasSeries['labels']); ?>,
                    datasets: [{
                      label: 'Empresas',
                      data: <?php echo json_encode($empresasSeries['values']); ?>,
                      backgroundColor: 'rgba(102, 126, 234, 0.7)',
                      borderRadius: 6,
                    }]
                  },
                  options: {
                    responsive: true,
                    plugins: { legend: { display: false } }
                  }
                });
              }

              const receitaCtx = document.getElementById('chartReceita');
              if (receitaCtx) {
                new Chart(receitaCtx, {
                  type: 'line',
                  data: {
                    labels: <?php echo json_encode($receitaSeries['labels']); ?>,
                    datasets: [{
                      label: 'MRR',
                      data: <?php echo json_encode($receitaSeries['values']); ?>,
                      borderColor: '#667eea',
                      backgroundColor: 'rgba(102, 126, 234, 0.15)',
                      tension: 0.35,
                      fill: true,
                    }]
                  },
                  options: {
                    responsive: true,
                    plugins: { legend: { display: false } }
                  }
                });
              }

              const cadastroCtx = document.getElementById('chartCadastros');
              if (cadastroCtx) {
                new Chart(cadastroCtx, {
                  type: 'doughnut',
                  data: {
                    labels: <?php echo json_encode($cadastroSeries['labels']); ?>,
                    datasets: [{
                      data: <?php echo json_encode($cadastroSeries['values']); ?>,
                      backgroundColor: ['#667eea', '#f6b93b', '#20bf6b'],
                    }]
                  },
                  options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                  }
                });
              }
            })();
          </script>
        </div>

