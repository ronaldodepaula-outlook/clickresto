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
$totais = [
  'pedidos_dia' => 0,
  'pedidos_preparo_dia' => 0,
  'pedidos_prontos_dia' => 0,
];
$pedidosEmPreparo = [];
$pedidosProntos = [];

$dataFiltro = trim((string)($_GET['data'] ?? ''));
if ($dataFiltro === '') {
  try {
    $dataFiltro = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d');
  } catch (Exception $e) {
    $dataFiltro = date('Y-m-d');
  }
}

function formatDateTimeCozinha($value) {
  if ($value === null || $value === '') {
    return '-';
  }
  try {
    $dt = new DateTime($value);
    return $dt->format('d/m/Y H:i');
  } catch (Exception $e) {
    return (string)$value;
  }
}

function statusBadgeCozinha($status) {
  $statusLower = strtolower((string)$status);
  if ($statusLower === 'recebido') {
    return ['Recebido', 'badge-opacity-info'];
  }
  if ($statusLower === 'preparo' || $statusLower === 'preparando') {
    return ['Preparo', 'badge-opacity-warning'];
  }
  if ($statusLower === 'pronto') {
    return ['Pronto', 'badge-opacity-success'];
  }
  return [ucfirst($statusLower), 'badge-opacity-secondary'];
}

function pedidoStatusBadgeCozinha($status) {
  $statusLower = strtolower((string)$status);
  if ($statusLower === 'aberto') {
    return ['Aberto', 'badge-opacity-success'];
  }
  if ($statusLower === 'preparo') {
    return ['Preparo', 'badge-opacity-warning'];
  }
  if ($statusLower === 'pronto') {
    return ['Pronto', 'badge-opacity-info'];
  }
  if ($statusLower === 'entregue') {
    return ['Entregue', 'badge-opacity-primary'];
  }
  if ($statusLower === 'fechado') {
    return ['Fechado', 'badge-opacity-secondary'];
  }
  return [ucfirst($statusLower), 'badge-opacity-secondary'];
}

function apiRequestCozinha($method, $url, $token, &$httpCode = null, $empresaId = '') {
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

if ($apiBase !== '' && $token !== '') {
  $urlIndicadores = $apiBase . '/cozinha/indicadores';
  if ($dataFiltro !== '') {
    $urlIndicadores .= '?data=' . urlencode($dataFiltro);
  }
  $codeIndicadores = null;
  $respIndicadores = apiRequestCozinha('GET', $urlIndicadores, $token, $codeIndicadores, $empresaId);
  if ($codeIndicadores >= 200 && $codeIndicadores < 300) {
    $totaisApi = $respIndicadores['totais'] ?? [];
    if (is_array($totaisApi)) {
      $totais['pedidos_dia'] = (int)($totaisApi['pedidos_dia'] ?? 0);
      $totais['pedidos_preparo_dia'] = (int)($totaisApi['pedidos_preparo_dia'] ?? 0);
      $totais['pedidos_prontos_dia'] = (int)($totaisApi['pedidos_prontos_dia'] ?? 0);
    }
    $pedidosEmPreparo = $respIndicadores['pedidos_em_preparo'] ?? [];
    $pedidosProntos = $respIndicadores['pedidos_prontos'] ?? [];
    if (!is_array($pedidosEmPreparo)) {
      $pedidosEmPreparo = [];
    }
    if (!is_array($pedidosProntos)) {
      $pedidosProntos = [];
    }
  } else {
    $errorMessage = $respIndicadores['message'] ?? 'Nao foi possivel carregar indicadores da cozinha.';
  }
} else {
  $errorMessage = 'Token ou API_BASE_URL nao configurados.';
}
?>
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-sm-12">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <div>
            <h3 class="mb-1">Gestao Cozinha</h3>
            <p class="text-muted mb-0">Indicadores e pedidos da cozinha.</p>
          </div>
          <form class="d-flex gap-2 align-items-center" method="GET" action="">
            <input type="hidden" name="paginas" value="gertao_cozinha">
            <input type="date" name="data" class="form-control form-control-sm" value="<?php echo htmlspecialchars((string)$dataFiltro, ENT_QUOTES, 'UTF-8'); ?>">
            <button class="btn btn-outline-secondary btn-sm" type="submit">Filtrar</button>
          </form>
        </div>
      </div>
    </div>

    <?php if ($errorMessage !== ''): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Pedidos do dia</p>
            <h3 class="mb-0"><?php echo htmlspecialchars((string)$totais['pedidos_dia'], ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
        </div>
      </div>
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Em preparo</p>
            <h3 class="mb-0"><?php echo htmlspecialchars((string)$totais['pedidos_preparo_dia'], ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
        </div>
      </div>
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Prontos</p>
            <h3 class="mb-0"><?php echo htmlspecialchars((string)$totais['pedidos_prontos_dia'], ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Pedidos em preparo</h4>
                <p class="text-muted mb-0">Pedidos em andamento na cozinha.</p>
              </div>
            </div>
            <?php if (empty($pedidosEmPreparo)): ?>
              <div class="alert alert-info mb-0">Nenhum pedido em preparo.</div>
            <?php else: ?>
              <?php foreach ($pedidosEmPreparo as $grupo): ?>
                <?php
                  if (is_object($grupo)) {
                    $grupo = (array)$grupo;
                  }
                  if (!is_array($grupo)) {
                    continue;
                  }
                  $pedido = $grupo['pedido'] ?? [];
                  if (is_object($pedido)) {
                    $pedido = (array)$pedido;
                  }
                  if (!is_array($pedido)) {
                    $pedido = [];
                  }
                  $itens = $grupo['itens'] ?? [];
                  if (!is_array($itens)) {
                    $itens = [];
                  }
                  $pedidoId = $pedido['id'] ?? '';
                  $pedidoStatus = $pedido['status'] ?? '';
                  [$pedidoStatusLabel, $pedidoBadge] = pedidoStatusBadgeCozinha($pedidoStatus);
                  $mesa = $pedido['mesa'] ?? [];
                  if (is_object($mesa)) {
                    $mesa = (array)$mesa;
                  }
                  $mesaNumero = $mesa['numero'] ?? '';
                  $comanda = $pedido['comanda'] ?? [];
                  if (is_object($comanda)) {
                    $comanda = (array)$comanda;
                  }
                  $comandaNumero = $comanda['numero'] ?? '';
                  $tipo = $pedido['tipo'] ?? '';
                  $criado = formatDateTimeCozinha($pedido['criado_em'] ?? $pedido['created_at'] ?? '');
                ?>
                <div class="border rounded p-3 mb-3">
                  <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                    <div>
                      <strong>Pedido #<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?></strong>
                      <span class="badge ms-2 <?php echo htmlspecialchars($pedidoBadge, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($pedidoStatusLabel, ENT_QUOTES, 'UTF-8'); ?>
                      </span>
                    </div>
                    <div class="text-muted small">
                      <?php if ($tipo !== ''): ?>Tipo: <?php echo htmlspecialchars((string)$tipo, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                      <?php if ($mesaNumero !== ''): ?> · Mesa <?php echo htmlspecialchars((string)$mesaNumero, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                      <?php if ($comandaNumero !== ''): ?> · Comanda <?php echo htmlspecialchars((string)$comandaNumero, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                      <?php if ($criado !== '-'): ?> · Criado: <?php echo htmlspecialchars((string)$criado, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table select-table">
                      <thead>
                        <tr>
                          <th>Produto</th>
                          <th>Qtde</th>
                          <th>Estacao</th>
                          <th>Status</th>
                          <th>Observacao</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($itens)): ?>
                          <tr><td colspan="5">Nenhum item no pedido.</td></tr>
                        <?php else: ?>
                          <?php foreach ($itens as $item): ?>
                            <?php
                              if (is_object($item)) {
                                $item = (array)$item;
                              }
                              if (!is_array($item)) {
                                continue;
                              }
                              $produtoNome = $item['produto_nome'] ?? '';
                              $quantidade = $item['quantidade'] ?? '';
                              $observacao = $item['observacao'] ?? '';
                              $cozinhaItem = $item['cozinha_item'] ?? [];
                              if (is_object($cozinhaItem)) {
                                $cozinhaItem = (array)$cozinhaItem;
                              }
                              $statusItem = $cozinhaItem['status'] ?? '';
                              [$statusLabel, $statusBadge] = statusBadgeCozinha($statusItem);
                              $estacao = $item['estacao'] ?? [];
                              if (is_object($estacao)) {
                                $estacao = (array)$estacao;
                              }
                              $estacaoNome = $estacao['nome'] ?? '';
                            ?>
                            <tr>
                              <td><?php echo htmlspecialchars((string)$produtoNome, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars((string)$quantidade, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars((string)$estacaoNome, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><span class="badge <?php echo htmlspecialchars($statusBadge, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                              <td><?php echo htmlspecialchars((string)$observacao, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Pedidos prontos</h4>
                <p class="text-muted mb-0">Pedidos finalizados na cozinha.</p>
              </div>
            </div>
            <?php if (empty($pedidosProntos)): ?>
              <div class="alert alert-info mb-0">Nenhum pedido pronto.</div>
            <?php else: ?>
              <?php foreach ($pedidosProntos as $grupo): ?>
                <?php
                  if (is_object($grupo)) {
                    $grupo = (array)$grupo;
                  }
                  if (!is_array($grupo)) {
                    continue;
                  }
                  $pedido = $grupo['pedido'] ?? [];
                  if (is_object($pedido)) {
                    $pedido = (array)$pedido;
                  }
                  if (!is_array($pedido)) {
                    $pedido = [];
                  }
                  $itens = $grupo['itens'] ?? [];
                  if (!is_array($itens)) {
                    $itens = [];
                  }
                  $pedidoId = $pedido['id'] ?? '';
                  $pedidoStatus = $pedido['status'] ?? '';
                  [$pedidoStatusLabel, $pedidoBadge] = pedidoStatusBadgeCozinha($pedidoStatus);
                  $mesa = $pedido['mesa'] ?? [];
                  if (is_object($mesa)) {
                    $mesa = (array)$mesa;
                  }
                  $mesaNumero = $mesa['numero'] ?? '';
                  $comanda = $pedido['comanda'] ?? [];
                  if (is_object($comanda)) {
                    $comanda = (array)$comanda;
                  }
                  $comandaNumero = $comanda['numero'] ?? '';
                  $tipo = $pedido['tipo'] ?? '';
                  $criado = formatDateTimeCozinha($pedido['criado_em'] ?? $pedido['created_at'] ?? '');
                ?>
                <div class="border rounded p-3 mb-3">
                  <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                    <div>
                      <strong>Pedido #<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?></strong>
                      <span class="badge ms-2 <?php echo htmlspecialchars($pedidoBadge, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($pedidoStatusLabel, ENT_QUOTES, 'UTF-8'); ?>
                      </span>
                    </div>
                    <div class="text-muted small">
                      <?php if ($tipo !== ''): ?>Tipo: <?php echo htmlspecialchars((string)$tipo, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                      <?php if ($mesaNumero !== ''): ?> · Mesa <?php echo htmlspecialchars((string)$mesaNumero, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                      <?php if ($comandaNumero !== ''): ?> · Comanda <?php echo htmlspecialchars((string)$comandaNumero, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                      <?php if ($criado !== '-'): ?> · Criado: <?php echo htmlspecialchars((string)$criado, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table select-table">
                      <thead>
                        <tr>
                          <th>Produto</th>
                          <th>Qtde</th>
                          <th>Estacao</th>
                          <th>Status</th>
                          <th>Observacao</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($itens)): ?>
                          <tr><td colspan="5">Nenhum item no pedido.</td></tr>
                        <?php else: ?>
                          <?php foreach ($itens as $item): ?>
                            <?php
                              if (is_object($item)) {
                                $item = (array)$item;
                              }
                              if (!is_array($item)) {
                                continue;
                              }
                              $produtoNome = $item['produto_nome'] ?? '';
                              $quantidade = $item['quantidade'] ?? '';
                              $observacao = $item['observacao'] ?? '';
                              $cozinhaItem = $item['cozinha_item'] ?? [];
                              if (is_object($cozinhaItem)) {
                                $cozinhaItem = (array)$cozinhaItem;
                              }
                              $statusItem = $cozinhaItem['status'] ?? '';
                              [$statusLabel, $statusBadge] = statusBadgeCozinha($statusItem);
                              $estacao = $item['estacao'] ?? [];
                              if (is_object($estacao)) {
                                $estacao = (array)$estacao;
                              }
                              $estacaoNome = $estacao['nome'] ?? '';
                            ?>
                            <tr>
                              <td><?php echo htmlspecialchars((string)$produtoNome, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars((string)$quantidade, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars((string)$estacaoNome, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><span class="badge <?php echo htmlspecialchars($statusBadge, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                              <td><?php echo htmlspecialchars((string)$observacao, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
