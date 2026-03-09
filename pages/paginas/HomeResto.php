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
loadEnvFile(__DIR__ . '/../../.env');

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$token = $_SESSION['token'] ?? '';
$errorMessage = '';
$operational = [];

if ($apiBase !== '' && $token !== '') {
  $ch = curl_init($apiBase . '/dashboard/operational');
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
      $operational = $data['data'] ?? $data;
      if (!is_array($operational)) {
        $operational = [];
      }
    } else {
      $errorMessage = $data['message'] ?? 'Nao foi possivel carregar o dashboard operacional.';
    }
  }
} else {
  $errorMessage = 'Token ou API_BASE_URL nao configurados.';
}

$pedidosDia = (int)($operational['pedidos_dia'] ?? 0);
$pedidosDelta = $operational['pedidos_delta_percent'] ?? null;
$ticketMedio = (float)($operational['ticket_medio'] ?? 0);
$mesasOcupadas = (int)($operational['mesas_ocupadas'] ?? 0);
$totalMesas = (int)($operational['total_mesas'] ?? 0);
$entregasRota = (int)($operational['entregas_rota'] ?? 0);
$itensCozinha = (int)($operational['itens_cozinha'] ?? 0);
$estoqueCritico = (int)($operational['estoque_critico'] ?? 0);
$faturamentoDia = (float)($operational['faturamento_dia'] ?? 0);
$metaPercent = (int)($operational['meta_percent'] ?? 0);
$cancelamentos = (int)($operational['cancelamentos'] ?? 0);

$canais = $operational['canais'] ?? [];
$canaisLabels = [];
$canaisValues = [];
if (is_array($canais)) {
  foreach ($canais as $canal) {
    if (!is_array($canal)) {
      continue;
    }
    $label = $canal['canal'] ?? $canal['nome'] ?? $canal['label'] ?? null;
    $valor = $canal['percent'] ?? $canal['valor'] ?? $canal['total'] ?? null;
    if ($label !== null && $valor !== null) {
      $canaisLabels[] = (string)$label;
      $canaisValues[] = (float)$valor;
    }
  }
}
if (empty($canaisLabels)) {
  $canaisLabels = ['Salao', 'Delivery', 'Retirada'];
  $canaisValues = [45, 38, 17];
}

$receitaHoraLabels = [];
$receitaHoraValues = [];
if (isset($operational['receita_hora']) && is_array($operational['receita_hora'])) {
  foreach ($operational['receita_hora'] as $item) {
    if (!is_array($item)) {
      continue;
    }
    $hora = $item['hora'] ?? $item['label'] ?? null;
    $valor = $item['valor'] ?? $item['total'] ?? null;
    if ($hora !== null && $valor !== null) {
      $receitaHoraLabels[] = (string)$hora;
      $receitaHoraValues[] = (float)$valor;
    }
  }
}
if (empty($receitaHoraLabels)) {
  $receitaHoraLabels = ['10h', '12h', '14h', '16h', '18h', '20h', '22h'];
  $receitaHoraValues = [350, 720, 640, 880, 1200, 980, 540];
}
?>
<div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                  <div>
                    <h3 class="mb-1">Dashboard Operacional</h3>
                    <p class="text-muted mb-0">Restaurantes, pizzarias e churrascarias no mesmo fluxo operacional.</p>
                  </div>
                  <div class="btn-wrapper">
                    <a href="#" class="btn btn-primary text-white me-2"><i class="mdi mdi-plus"></i> Novo pedido</a>
                    <a href="#" class="btn btn-outline-secondary me-2"><i class="mdi mdi-silverware-fork-knife"></i> Abrir mesa</a>
                    <a href="#" class="btn btn-outline-secondary"><i class="mdi mdi-cash"></i> Fechar caixa</a>
                  </div>
                </div>
              </div>
            </div>

            <?php if ($errorMessage !== ''): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="row">
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Pedidos do dia</p>
                    <h3 class="mb-0"><?php echo $pedidosDia; ?></h3>
                    <?php if ($pedidosDelta === null): ?>
                      <p class="text-muted mb-0">Sem comparativo</p>
                    <?php else: ?>
                      <?php $deltaClass = ($pedidosDelta >= 0) ? 'text-success' : 'text-danger'; ?>
                      <p class="<?php echo $deltaClass; ?> mb-0"><?php echo $pedidosDelta; ?>% vs ontem</p>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Ticket medio</p>
                    <h3 class="mb-0">R$ <?php echo number_format($ticketMedio, 2, ',', '.'); ?></h3>
                    <p class="text-muted mb-0">Salão + delivery</p>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Mesas ocupadas</p>
                    <h3 class="mb-0"><?php echo $mesasOcupadas; ?> / <?php echo $totalMesas; ?></h3>
                    <p class="text-warning mb-0">Pico de movimento</p>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Entregas em rota</p>
                    <h3 class="mb-0"><?php echo $entregasRota; ?></h3>
                    <p class="text-muted mb-0">Delivery ativo</p>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Itens na cozinha</p>
                    <h3 class="mb-0"><?php echo $itensCozinha; ?></h3>
                    <p class="text-danger mb-0">6 atrasados</p>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Estoque critico</p>
                    <h3 class="mb-0"><?php echo $estoqueCritico; ?> itens</h3>
                    <p class="text-danger mb-0">Repor hoje</p>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Faturamento dia</p>
                    <h3 class="mb-0">R$ <?php echo number_format($faturamentoDia, 2, ',', '.'); ?></h3>
                    <p class="text-success mb-0">Meta <?php echo $metaPercent; ?>%</p>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Cancelamentos</p>
                    <h3 class="mb-0"><?php echo $cancelamentos; ?></h3>
                    <p class="text-muted mb-0">Ultimas 24h</p>
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
                        <h4 class="card-title card-title-dash">Receita diaria</h4>
                        <p class="card-subtitle card-subtitle-dash">Monitoramento do caixa por hora.</p>
                      </div>
                      <div class="btn-wrapper">
                        <button class="btn btn-outline-secondary btn-sm">Hoje</button>
                      </div>
                    </div>
                    <div class="chartjs-bar-wrapper mt-3">
                      <canvas id="chartReceitaResto"></canvas>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-4 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Canais de venda</h4>
                    <p class="card-subtitle card-subtitle-dash">Salao, delivery e retirada.</p>
                    <div class="mt-3">
                      <canvas id="chartCanaisResto"></canvas>
                    </div>
                    <div class="mt-4">
                      <?php foreach ($canaisLabels as $idx => $label): ?>
                        <div class="d-flex justify-content-between">
                          <span class="text-muted"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                          <span><?php echo (int)$canaisValues[$idx]; ?>%</span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-6 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Cozinha em producao</h4>
                    <p class="card-subtitle card-subtitle-dash">Pedidos aguardando preparo.</p>
                    <div class="table-responsive mt-3">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Mesa/Pedido</th>
                            <th>Itens</th>
                            <th>Tempo</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>#512 • Mesa 12</td>
                            <td>Pizza calabresa, refri</td>
                            <td>12 min</td>
                            <td><div class="badge badge-opacity-warning">preparando</div></td>
                          </tr>
                          <tr>
                            <td>#518 • Delivery</td>
                            <td>Churrasco misto, arroz</td>
                            <td>18 min</td>
                            <td><div class="badge badge-opacity-danger">atrasado</div></td>
                          </tr>
                          <tr>
                            <td>#520 • Mesa 04</td>
                            <td>Risoto, suco</td>
                            <td>6 min</td>
                            <td><div class="badge badge-opacity-success">novo</div></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-6 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Mesas e atendimento</h4>
                    <p class="card-subtitle card-subtitle-dash">Status do salao.</p>
                    <div class="table-responsive mt-3">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Mesa</th>
                            <th>Garcom</th>
                            <th>Consumo</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>Mesa 08</td>
                            <td>Paula</td>
                            <td>R$ 186,40</td>
                            <td><div class="badge badge-opacity-success">em consumo</div></td>
                          </tr>
                          <tr>
                            <td>Mesa 15</td>
                            <td>Rodrigo</td>
                            <td>R$ 82,00</td>
                            <td><div class="badge badge-opacity-warning">aguardando conta</div></td>
                          </tr>
                          <tr>
                            <td>Mesa 21</td>
                            <td>Bruna</td>
                            <td>R$ 0,00</td>
                            <td><div class="badge badge-opacity-secondary">livre</div></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-7 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Top produtos</h4>
                    <p class="card-subtitle card-subtitle-dash">Itens mais vendidos do dia.</p>
                    <div class="table-responsive mt-3">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Vendas</th>
                            <th>Receita</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>Pizza calabresa</td>
                            <td>Pizzaria</td>
                            <td>42</td>
                            <td>R$ 3.108</td>
                          </tr>
                          <tr>
                            <td>Picanha completa</td>
                            <td>Churrascaria</td>
                            <td>26</td>
                            <td>R$ 2.964</td>
                          </tr>
                          <tr>
                            <td>Frango parmegiana</td>
                            <td>Restaurante</td>
                            <td>18</td>
                            <td>R$ 1.260</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-5 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Estoque baixo</h4>
                    <p class="card-subtitle card-subtitle-dash">Itens criticos do dia.</p>
                    <div class="table-responsive mt-3">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Item</th>
                            <th>Saldo</th>
                            <th>Acao</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>Carvao</td>
                            <td>8 sacos</td>
                            <td><button class="btn btn-outline-secondary btn-sm">Repor</button></td>
                          </tr>
                          <tr>
                            <td>Queijo mussarela</td>
                            <td>4 kg</td>
                            <td><button class="btn btn-outline-secondary btn-sm">Repor</button></td>
                          </tr>
                          <tr>
                            <td>Molho especial</td>
                            <td>2 litros</td>
                            <td><button class="btn btn-outline-secondary btn-sm">Repor</button></td>
                          </tr>
                        </tbody>
                      </table>
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
              const receitaCtx = document.getElementById('chartReceitaResto');
              if (receitaCtx) {
                new Chart(receitaCtx, {
                  type: 'line',
                  data: {
                    labels: <?php echo json_encode($receitaHoraLabels); ?>,
                    datasets: [{
                      label: 'Receita',
                      data: <?php echo json_encode($receitaHoraValues); ?>,
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

              const canaisCtx = document.getElementById('chartCanaisResto');
              if (canaisCtx) {
                new Chart(canaisCtx, {
                  type: 'doughnut',
                  data: {
                    labels: <?php echo json_encode($canaisLabels); ?>,
                    datasets: [{
                      data: <?php echo json_encode($canaisValues); ?>,
                      backgroundColor: ['#4b7bec', '#20bf6b', '#f6b93b'],
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

