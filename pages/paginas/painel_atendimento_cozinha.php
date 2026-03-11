<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../classe/env.php';
loadEnvFile(__DIR__ . '/../../.env');

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$token = $_SESSION['token'] ?? '';
$empresaId = $_SESSION['empresa_id'] ?? '';
$usuarioIdSessao = $_SESSION['user_id'] ?? '';
$errorMessage = '';
$successMessage = '';
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

function formatDateTimeCozinhaPainel($value) {
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

function statusBadgeCozinhaPainel($status) {
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

function pedidoStatusBadgePainel($status) {
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

function apiRequestCozinhaPainel($method, $url, $token, $payload = null, &$httpCode = null, $empresaId = '') {
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
  if ($payload !== null) {
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $apiBase !== '' && $token !== '') {
  $action = $_POST['action'] ?? '';
  if ($action === 'pedido_status') {
    $pedidoId = (string)($_POST['pedido_id'] ?? '');
    $novoStatus = trim((string)($_POST['status'] ?? ''));
    if ($pedidoId !== '' && $novoStatus !== '') {
      $payload = ['status' => $novoStatus];
      $code = null;
      $resp = apiRequestCozinhaPainel('PATCH', $apiBase . '/pedidos/' . urlencode($pedidoId) . '/status', $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Status do pedido atualizado.';
        if ($novoStatus === 'pronto') {
          $mesaIdNotificacao = (int)($_POST['mesa_id'] ?? 0);
          $mesaNumeroNotificacao = trim((string)($_POST['mesa_numero'] ?? ''));
          $usuarioNotificacao = (int)($_POST['usuario_id'] ?? $usuarioIdSessao ?? 0);
          $mensagemNotificacao = 'Pedido ' . $pedidoId . ' pronto';
          if ($mesaNumeroNotificacao !== '') {
            $mensagemNotificacao .= ' para entrega na mesa ' . $mesaNumeroNotificacao;
          }
          $payloadNotificacao = [
            'destino' => 'operacao',
            'usuario_id' => $usuarioNotificacao,
            'tipo' => 'pedido_status',
            'status' => 'pendente',
            'pedido_id' => (int)$pedidoId,
            'titulo' => 'Pedido pronto',
            'mensagem' => $mensagemNotificacao,
            'payload' => [
              'status_anterior' => 'preparo',
              'status_novo' => 'pronto',
            ],
          ];
          if ($empresaId !== '') {
            $payloadNotificacao['empresa_id'] = (int)$empresaId;
          }
          if ($mesaIdNotificacao > 0) {
            $payloadNotificacao['mesa_id'] = $mesaIdNotificacao;
          }
          $urlNotificacoes = $apiBase;
          if (preg_match('#/api/v1$#', $urlNotificacoes)) {
            $urlNotificacoes .= '/notificacoes';
          } elseif (preg_match('#/v1$#', $urlNotificacoes)) {
            $urlNotificacoes .= '/notificacoes';
          } elseif (preg_match('#/api$#', $urlNotificacoes)) {
            $urlNotificacoes .= '/v1/notificacoes';
          } else {
            $urlNotificacoes .= '/api/v1/notificacoes';
          }
          $codeNotificacao = null;
          $respNotificacao = apiRequestCozinhaPainel('POST', $urlNotificacoes, $token, $payloadNotificacao, $codeNotificacao, $empresaId);
          if (!($codeNotificacao >= 200 && $codeNotificacao < 300)) {
            $errorMessage = $respNotificacao['message'] ?? 'Status atualizado, mas nao foi possivel enviar a notificacao.';
          }
        }
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar status do pedido.';
      }
    } else {
      $errorMessage = 'Pedido ou status invalido.';
    }
  }
}

if ($apiBase !== '' && $token !== '') {
  $urlIndicadores = $apiBase . '/cozinha/indicadores';
  if ($dataFiltro !== '') {
    $urlIndicadores .= '?data=' . urlencode($dataFiltro);
  }
  $codeIndicadores = null;
  $respIndicadores = apiRequestCozinhaPainel('GET', $urlIndicadores, $token, null, $codeIndicadores, $empresaId);
  if ($codeIndicadores >= 200 && $codeIndicadores < 300) {
    $pedidosEmPreparo = $respIndicadores['pedidos_em_preparo'] ?? [];
    $pedidosProntos = $respIndicadores['pedidos_prontos'] ?? [];
    if (!is_array($pedidosEmPreparo)) {
      $pedidosEmPreparo = [];
    }
    if (!is_array($pedidosProntos)) {
      $pedidosProntos = [];
    }
  } else {
    $errorMessage = $respIndicadores['message'] ?? 'Nao foi possivel carregar pedidos da cozinha.';
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
            <h3 class="mb-1">Painel Atendimento - Cozinha</h3>
            <p class="text-muted mb-0">Acompanhe os pedidos em preparo e prontos.</p>
          </div>
          <form class="d-flex gap-2 align-items-center" method="GET" action="">
            <input type="hidden" name="paginas" value="painel_atendimento_cozinha">
            <input type="date" name="data" class="form-control form-control-sm" value="<?php echo htmlspecialchars((string)$dataFiltro, ENT_QUOTES, 'UTF-8'); ?>">
            <button class="btn btn-outline-secondary btn-sm" type="submit">Filtrar</button>
          </form>
        </div>
      </div>
    </div>

    <?php if ($errorMessage !== ''): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($successMessage !== ''): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="row">
      <div class="col-lg-6 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Em preparo</h4>
                <p class="text-muted mb-0">Pedidos que estao em producao.</p>
              </div>
              <span class="badge badge-opacity-warning"><?php echo htmlspecialchars((string)count($pedidosEmPreparo), ENT_QUOTES, 'UTF-8'); ?></span>
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
                  $pedidoStatus = strtolower(trim((string)($pedido['status'] ?? '')));
                  $pedidoStatusNormalized = str_replace(['_', '-'], ' ', $pedidoStatus);
                  $isPreparo = strpos($pedidoStatusNormalized, 'preparo') !== false;
                  if ($pedidoStatus === 'fechado') {
                    continue;
                  }
                  $itens = $grupo['itens'] ?? [];
                  if (!is_array($itens)) {
                    $itens = [];
                  }
                  $pedidoId = $pedido['id'] ?? '';
                  [$pedidoStatusLabel, $pedidoBadge] = pedidoStatusBadgePainel($pedidoStatus);
                  $mesa = $pedido['mesa'] ?? [];
                  if (is_object($mesa)) {
                    $mesa = (array)$mesa;
                  }
                  $mesaId = $mesa['id'] ?? '';
                  $mesaNumero = $mesa['numero'] ?? '';
                  $comanda = $pedido['comanda'] ?? [];
                  if (is_object($comanda)) {
                    $comanda = (array)$comanda;
                  }
                  $comandaNumero = $comanda['numero'] ?? '';
                  $tipo = $pedido['tipo'] ?? '';
                  $criado = formatDateTimeCozinhaPainel($pedido['criado_em'] ?? $pedido['created_at'] ?? '');
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
                                    <?php if ($isPreparo && $pedidoId !== ''): ?>
                    <form class="mb-2" method="POST" action="">
                      <input type="hidden" name="action" value="pedido_status">
                      <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?>">
                      <input type="hidden" name="usuario_id" value="<?php echo htmlspecialchars((string)($pedido['usuario_id'] ?? $usuarioIdSessao), ENT_QUOTES, 'UTF-8'); ?>">
                      <input type="hidden" name="mesa_id" value="<?php echo htmlspecialchars((string)$mesaId, ENT_QUOTES, 'UTF-8'); ?>">
                      <input type="hidden" name="mesa_numero" value="<?php echo htmlspecialchars((string)$mesaNumero, ENT_QUOTES, 'UTF-8'); ?>">
                      <input type="hidden" name="status" value="pronto">
                      <button class="btn btn-outline-success btn-sm" type="submit">
                        <i class="mdi mdi-check"></i> Marcar pronto
                      </button>
                    </form>
                  <?php endif; ?>
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
                              [$statusLabel, $statusBadge] = statusBadgeCozinhaPainel($statusItem);
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

      <div class="col-lg-6 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Prontos</h4>
                <p class="text-muted mb-0">Pedidos finalizados para entrega.</p>
              </div>
              <span class="badge badge-opacity-success"><?php echo htmlspecialchars((string)count($pedidosProntos), ENT_QUOTES, 'UTF-8'); ?></span>
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
                  $pedidoStatus = strtolower(trim((string)($pedido['status'] ?? '')));
                  if ($pedidoStatus === 'fechado') {
                    continue;
                  }
                  $itens = $grupo['itens'] ?? [];
                  if (!is_array($itens)) {
                    $itens = [];
                  }
                  $pedidoId = $pedido['id'] ?? '';
                  [$pedidoStatusLabel, $pedidoBadge] = pedidoStatusBadgePainel($pedidoStatus);
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
                  $criado = formatDateTimeCozinhaPainel($pedido['criado_em'] ?? $pedido['created_at'] ?? '');
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
                              [$statusLabel, $statusBadge] = statusBadgeCozinhaPainel($statusItem);
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
