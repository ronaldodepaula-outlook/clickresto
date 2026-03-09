<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../classe/env.php';
loadEnvFile(__DIR__ . '/../../.env');

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$token = $_SESSION['token'] ?? '';
$errorMessage = '';
$overview = [];

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
      $errorMessage = $data['message'] ?? 'Nao foi possivel carregar o relatorio.';
    }
  }
} else {
  $errorMessage = 'Token ou API_BASE_URL nao configurados.';
}

$mrr = (float)($overview['mrr'] ?? 0);
$empresasAtivas = (int)($overview['empresas_ativas'] ?? 0);
$empresasTrial = (int)($overview['empresas_trial'] ?? 0);
$empresasSuspensas = (int)($overview['empresas_suspensas'] ?? 0);
$empresasCanceladas = (int)($overview['empresas_canceladas'] ?? 0);
$usoSistema = (int)($overview['uso_sistema'] ?? 0);
?>
<div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                  <div>
                    <h3 class="mb-1">Relatorios SaaS</h3>
                    <p class="text-muted mb-0">Indicadores estrategicos para decisao rapida.</p>
                  </div>
                  <div class="btn-wrapper">
                    <button class="btn btn-outline-secondary me-2"><i class="mdi mdi-calendar"></i> Periodo</button>
                    <button class="btn btn-primary text-white"><i class="mdi mdi-download"></i> Exportar</button>
                  </div>
                </div>
              </div>
            </div>

            <?php if ($errorMessage !== ''): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="row">
              <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Faturamento mensal</p>
                    <h3 class="mb-0">R$ <?php echo number_format($mrr, 2, ',', '.'); ?></h3>
                    <p class="text-muted mb-0">MRR atual</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Empresas ativas</p>
                    <h3 class="mb-0"><?php echo $empresasAtivas; ?></h3>
                    <p class="text-muted mb-0">Ativas hoje</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Canceladas</p>
                    <h3 class="mb-0 text-danger"><?php echo $empresasCanceladas; ?></h3>
                    <p class="text-muted mb-0">Ultimos 30 dias</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Uso do sistema</p>
                    <h3 class="mb-0"><?php echo $usoSistema; ?>%</h3>
                    <p class="text-muted mb-0">Media semanal</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-7 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Crescimento de clientes</h4>
                    <p class="card-subtitle card-subtitle-dash">Evolucao mensal.</p>
                    <div class="mt-3">
                      <canvas id="chartRelatoriosClientes"></canvas>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-5 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Empresas por status</h4>
                    <p class="card-subtitle card-subtitle-dash">Distribuicao atual.</p>
                    <div class="mt-3">
                      <canvas id="chartRelatoriosStatus"></canvas>
                    </div>
                    <div class="mt-4">
                      <div class="d-flex justify-content-between">
                        <span class="text-muted">Ativas</span>
                        <span><?php echo $empresasAtivas; ?></span>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-muted">Trial</span>
                        <span><?php echo $empresasTrial; ?></span>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-muted">Suspensas</span>
                        <span><?php echo $empresasSuspensas; ?></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <script>
            (function () {
              if (typeof Chart === 'undefined') {
                return;
              }
              const statusCtx = document.getElementById('chartRelatoriosStatus');
              if (statusCtx) {
                new Chart(statusCtx, {
                  type: 'doughnut',
                  data: {
                    labels: ['Ativas', 'Trial', 'Suspensas'],
                    datasets: [{
                      data: [<?php echo $empresasAtivas; ?>, <?php echo $empresasTrial; ?>, <?php echo $empresasSuspensas; ?>],
                      backgroundColor: ['#20bf6b', '#f6b93b', '#eb3b5a'],
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

