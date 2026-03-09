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
$estacoes = [];
$itens = [];
$pedidosCozinha = [];

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

function apiRequestCozinha($method, $url, $token, $payload = null, &$httpCode = null, $empresaId = '') {
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
  if ($action === 'estacao_create') {
    $payload = [
      'nome' => trim((string)($_POST['nome'] ?? '')),
    ];
    $code = null;
    $resp = apiRequestCozinha('POST', $apiBase . '/cozinha-estacoes', $token, $payload, $code);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Estacao criada com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar estacao.';
    }
  } elseif ($action === 'estacao_update') {
    $id = (string)($_POST['estacao_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'nome' => trim((string)($_POST['nome'] ?? '')),
      ];
      $code = null;
      $resp = apiRequestCozinha('PUT', $apiBase . '/cozinha-estacoes/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Estacao atualizada com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar estacao.';
      }
    } else {
      $errorMessage = 'Estacao invalida.';
    }
  } elseif ($action === 'estacao_delete') {
    $id = (string)($_POST['estacao_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestCozinha('DELETE', $apiBase . '/cozinha-estacoes/' . urlencode($id), $token, null, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Estacao removida com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover estacao.';
      }
    } else {
      $errorMessage = 'Estacao invalida.';
    }
  } elseif ($action === 'item_create') {
    $payload = [
      'pedido_item_id' => (int)($_POST['pedido_item_id'] ?? 0),
      'estacao_id' => (int)($_POST['estacao_id'] ?? 0),
      'status' => trim((string)($_POST['status'] ?? 'recebido')),
    ];
    $code = null;
    $resp = apiRequestCozinha('POST', $apiBase . '/cozinha-itens', $token, $payload, $code);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Item de cozinha criado com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar item.';
    }
  } elseif ($action === 'item_update') {
    $id = (string)($_POST['item_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'pedido_item_id' => (int)($_POST['pedido_item_id'] ?? 0),
        'estacao_id' => (int)($_POST['estacao_id'] ?? 0),
        'status' => trim((string)($_POST['status'] ?? 'recebido')),
      ];
      $code = null;
      $resp = apiRequestCozinha('PUT', $apiBase . '/cozinha-itens/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Item de cozinha atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar item.';
      }
    } else {
      $errorMessage = 'Item invalido.';
    }
  } elseif ($action === 'item_status') {
    $id = (string)($_POST['item_id'] ?? '');
    $status = trim((string)($_POST['status'] ?? ''));
    if ($id !== '' && $status !== '') {
      $payload = ['status' => $status];
      $code = null;
      $resp = apiRequestCozinha('PATCH', $apiBase . '/cozinha-itens/' . urlencode($id) . '/status', $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Status atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar status.';
      }
    } else {
      $errorMessage = 'Item ou status invalido.';
    }
  } elseif ($action === 'item_delete') {
    $id = (string)($_POST['item_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestCozinha('DELETE', $apiBase . '/cozinha-itens/' . urlencode($id), $token, null, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Item removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover item.';
      }
    } else {
      $errorMessage = 'Item invalido.';
    }
  } elseif ($action === 'pedido_status') {
    $pedidoId = (string)($_POST['pedido_id'] ?? '');
    $novoStatus = trim((string)($_POST['status'] ?? ''));
    if ($pedidoId !== '' && $novoStatus !== '') {
      $payload = ['status' => $novoStatus];
      $code = null;
      $resp = apiRequestCozinha('PATCH', $apiBase . '/pedidos/' . urlencode($pedidoId) . '/status', $token, $payload, $code);
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
          $respNotificacao = apiRequestCozinha('POST', $urlNotificacoes, $token, $payloadNotificacao, $codeNotificacao, $empresaId);
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

$statusFiltro = trim((string)($_GET['status'] ?? ''));
$estacaoFiltro = trim((string)($_GET['estacao_id'] ?? ''));
$pedidoStatusFiltro = trim((string)($_GET['pedido_status'] ?? ''));
$dataFiltro = trim((string)($_GET['data'] ?? ''));
$orderByFiltro = trim((string)($_GET['order_by'] ?? ''));

if ($apiBase !== '' && $token !== '') {
  $code = null;
  $respEstacoes = apiRequestCozinha('GET', $apiBase . '/cozinha-estacoes', $token, null, $code);
  if ($code >= 200 && $code < 300) {
    $estacoes = $respEstacoes['data'] ?? $respEstacoes;
    if (is_array($estacoes) && isset($estacoes['data']) && is_array($estacoes['data'])) {
      $estacoes = $estacoes['data'];
    }
    if (!is_array($estacoes)) {
      $estacoes = [];
    }
  } else {
    $errorMessage = $respEstacoes['message'] ?? 'Nao foi possivel carregar estacoes.';
  }

  $codeItens = null;
  $urlItens = $apiBase . '/cozinha-itens';
  if ($statusFiltro !== '') {
    $urlItens .= '?status=' . urlencode($statusFiltro);
  }
  $respItens = apiRequestCozinha('GET', $urlItens, $token, null, $codeItens);
  if ($codeItens >= 200 && $codeItens < 300) {
    $itens = $respItens['data'] ?? $respItens;
    if (is_array($itens) && isset($itens['data']) && is_array($itens['data'])) {
      $itens = $itens['data'];
    }
    if (!is_array($itens)) {
      $itens = [];
    }
  } else {
    $errorMessage = $respItens['message'] ?? 'Nao foi possivel carregar itens de cozinha.';
  }

  $codePedidos = null;
  $filtrosPedidos = [];
  if ($statusFiltro !== '') {
    $filtrosPedidos[] = 'status=' . urlencode($statusFiltro);
  }
  if ($estacaoFiltro !== '') {
    $filtrosPedidos[] = 'estacao_id=' . urlencode($estacaoFiltro);
  }
  if ($pedidoStatusFiltro !== '') {
    $filtrosPedidos[] = 'pedido_status=' . urlencode($pedidoStatusFiltro);
  }
  if ($dataFiltro !== '') {
    $filtrosPedidos[] = 'data=' . urlencode($dataFiltro);
  }
  if ($orderByFiltro !== '') {
    $filtrosPedidos[] = 'order_by=' . urlencode($orderByFiltro);
  }
  $urlPedidos = $apiBase . '/cozinha-itens/pedidos';
  if (!empty($filtrosPedidos)) {
    $urlPedidos .= '?' . implode('&', $filtrosPedidos);
  }
  $respPedidos = apiRequestCozinha('GET', $urlPedidos, $token, null, $codePedidos);
  if ($codePedidos >= 200 && $codePedidos < 300) {
    $pedidosCozinha = $respPedidos['data'] ?? $respPedidos;
    if (is_array($pedidosCozinha) && isset($pedidosCozinha['data']) && is_array($pedidosCozinha['data'])) {
      $pedidosCozinha = $pedidosCozinha['data'];
    }
    if (!is_array($pedidosCozinha)) {
      $pedidosCozinha = [];
    }
  } else {
    $errorMessage = $respPedidos['message'] ?? 'Nao foi possivel carregar pedidos da cozinha.';
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
            <h3 class="mb-1">Cozinha</h3>
            <p class="text-muted mb-0">Estacoes e itens em preparo.</p>
          </div>
          <div class="btn-wrapper">
            <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="modal" data-bs-target="#modalEstacaoNova">
              <i class="mdi mdi-plus"></i> Nova estacao
            </button>
            <button class="btn btn-primary text-white" type="button" data-bs-toggle="modal" data-bs-target="#modalItemNovo">
              <i class="mdi mdi-plus"></i> Novo item
            </button>
          </div>
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
      <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Pedidos na cozinha</h4>
                <p class="text-muted mb-0">Pedidos agrupados por itens e estacao.</p>
              </div>
              <form class="d-flex flex-wrap gap-2" method="GET" action="">
                <input type="hidden" name="paginas" value="cozinha">
                <select name="status" class="form-select form-select-sm">
                  <option value="">Status item</option>
                  <option value="recebido" <?php echo $statusFiltro === 'recebido' ? 'selected' : ''; ?>>Recebido</option>
                  <option value="preparo" <?php echo $statusFiltro === 'preparo' ? 'selected' : ''; ?>>Preparo</option>
                  <option value="pronto" <?php echo $statusFiltro === 'pronto' ? 'selected' : ''; ?>>Pronto</option>
                </select>
                <select name="estacao_id" class="form-select form-select-sm">
                  <option value="">Estacao</option>
                  <?php foreach ($estacoes as $estacao): ?>
                    <?php
                      if (is_object($estacao)) {
                        $estacao = (array)$estacao;
                      }
                      if (!is_array($estacao)) {
                        continue;
                      }
                      $estacaoId = $estacao['id'] ?? '';
                      $estacaoNome = $estacao['nome'] ?? '';
                    ?>
                    <option value="<?php echo htmlspecialchars((string)$estacaoId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((string)$estacaoId === (string)$estacaoFiltro) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars((string)$estacaoNome, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <select name="pedido_status" class="form-select form-select-sm">
                  <option value="">Status pedido</option>
                  <option value="aberto" <?php echo $pedidoStatusFiltro === 'aberto' ? 'selected' : ''; ?>>Aberto</option>
                  <option value="preparo" <?php echo $pedidoStatusFiltro === 'preparo' ? 'selected' : ''; ?>>Preparo</option>
                  <option value="pronto" <?php echo $pedidoStatusFiltro === 'pronto' ? 'selected' : ''; ?>>Pronto</option>
                  <option value="entregue" <?php echo $pedidoStatusFiltro === 'entregue' ? 'selected' : ''; ?>>Entregue</option>
                  <option value="fechado" <?php echo $pedidoStatusFiltro === 'fechado' ? 'selected' : ''; ?>>Fechado</option>
                </select>
                <input type="date" name="data" class="form-control form-control-sm" value="<?php echo htmlspecialchars((string)$dataFiltro, ENT_QUOTES, 'UTF-8'); ?>">
                <select name="order_by" class="form-select form-select-sm">
                  <option value="">Ordenar</option>
                  <option value="estacao" <?php echo $orderByFiltro === 'estacao' ? 'selected' : ''; ?>>Estacao</option>
                  <option value="status" <?php echo $orderByFiltro === 'status' ? 'selected' : ''; ?>>Status item</option>
                </select>
                <button class="btn btn-outline-secondary btn-sm" type="submit">Aplicar</button>
              </form>
            </div>

            <?php if (empty($pedidosCozinha)): ?>
              <div class="alert alert-info mb-0">Nenhum pedido encontrado com os filtros atuais.</div>
            <?php else: ?>
              <?php foreach ($pedidosCozinha as $grupo): ?>
                <?php
                  if (is_object($grupo)) {
                    $grupo = (array)$grupo;
                  }
                  if (!is_array($grupo)) {
                    continue;
                  }
                  $pedido = $grupo['pedido'] ?? [];
                  $itensPedido = $grupo['itens'] ?? [];
                  if (is_object($pedido)) {
                    $pedido = (array)$pedido;
                  }
                  if (!is_array($pedido)) {
                    $pedido = [];
                  }
                  $pedidoId = $pedido['id'] ?? '';
                  $pedidoTipo = $pedido['tipo'] ?? '';
                  $pedidoStatus = $pedido['status'] ?? '';
                  $pedidoTotal = $pedido['total'] ?? '0';
                  $pedidoCriado = $pedido['criado_em'] ?? '';
                  [$pedidoStatusLabel, $pedidoBadge] = pedidoStatusBadgeCozinha($pedidoStatus);
                  $pedidoStatusLower = strtolower((string)$pedidoStatus);
                  $mesaInfo = $pedido['mesa'] ?? null;
                  $comandaInfo = $pedido['comanda'] ?? null;
                  $clienteInfo = $pedido['cliente'] ?? null;
                  $mesaIdForm = '';
                  $mesaNumeroForm = '';
                  if (is_array($mesaInfo)) {
                    $mesaIdForm = $mesaInfo['id'] ?? $mesaInfo['mesa_id'] ?? '';
                    $mesaNumeroForm = $mesaInfo['numero'] ?? '';
                  }
                ?>
                <div class="border rounded p-3 mb-3">
                  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                      <h5 class="mb-1">Pedido #<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?></h5>
                      <p class="text-muted mb-0">Tipo: <?php echo htmlspecialchars((string)$pedidoTipo, ENT_QUOTES, 'UTF-8'); ?> · Criado: <?php echo htmlspecialchars(formatDateTimeCozinha($pedidoCriado), ENT_QUOTES, 'UTF-8'); ?></p>
                      <p class="text-muted mb-0">
                        <?php if (is_array($mesaInfo)): ?>
                          Mesa <?php echo htmlspecialchars((string)($mesaInfo['numero'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars((string)($mesaInfo['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>)
                        <?php endif; ?>
                        <?php if (is_array($comandaInfo)): ?>
                          · Comanda <?php echo htmlspecialchars((string)($comandaInfo['numero'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars((string)($comandaInfo['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>)
                        <?php endif; ?>
                        <?php if (is_array($clienteInfo)): ?>
                          · Cliente <?php echo htmlspecialchars((string)($clienteInfo['nome'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                      </p>
                    </div>
                    <div class="text-end">
                      <div class="badge <?php echo $pedidoBadge; ?> mb-2"><?php echo htmlspecialchars((string)$pedidoStatusLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                      <div class="mb-2"><strong>R$ <?php echo htmlspecialchars((string)$pedidoTotal, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                      <?php if ($pedidoId !== ''): ?>
                        <?php if ($pedidoStatusLower === 'aberto'): ?>
                          <form method="POST" action="">
                            <input type="hidden" name="action" value="pedido_status">
                            <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="status" value="preparo">
                            <button class="btn btn-outline-warning btn-sm" type="submit">
                              <i class="mdi mdi-fire"></i> Enviar para preparo
                            </button>
                          </form>
                        <?php elseif ($pedidoStatusLower === 'preparo'): ?>
                          <form method="POST" action="">
                            <input type="hidden" name="action" value="pedido_status">
                            <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="usuario_id" value="<?php echo htmlspecialchars((string)($pedido['usuario_id'] ?? $usuarioIdSessao), ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="mesa_id" value="<?php echo htmlspecialchars((string)$mesaIdForm, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="mesa_numero" value="<?php echo htmlspecialchars((string)$mesaNumeroForm, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="status" value="pronto">
                            <button class="btn btn-outline-success btn-sm" type="submit">
                              <i class="mdi mdi-check"></i> Marcar pronto
                            </button>
                          </form>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="table-responsive mt-3">
                    <table class="table select-table">
                      <thead>
                        <tr>
                          <th>Produto</th>
                          <th>Qtd</th>
                          <th>Preco</th>
                          <th>Observacao</th>
                          <th>Estacao</th>
                          <th>Status cozinha</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($itensPedido)): ?>
                          <tr><td colspan="6">Nenhum item neste pedido.</td></tr>
                        <?php else: ?>
                          <?php foreach ($itensPedido as $item): ?>
                            <?php
                              if (is_object($item)) {
                                $item = (array)$item;
                              }
                              if (!is_array($item)) {
                                continue;
                              }
                              $produtoNome = $item['produto_nome'] ?? $item['produto_id'] ?? '';
                              $quantidade = $item['quantidade'] ?? '';
                              $preco = $item['preco'] ?? '';
                              $observacao = $item['observacao'] ?? '';
                              $cozinhaItem = $item['cozinha_item'] ?? [];
                              if (is_object($cozinhaItem)) {
                                $cozinhaItem = (array)$cozinhaItem;
                              }
                              $statusCozinhaItem = $cozinhaItem['status'] ?? '';
                              [$statusItemLabel, $statusItemBadge] = statusBadgeCozinha($statusCozinhaItem);
                              $estacao = $item['estacao'] ?? [];
                              if (is_object($estacao)) {
                                $estacao = (array)$estacao;
                              }
                              $estacaoNome = $estacao['nome'] ?? $estacao['id'] ?? '';
                            ?>
                            <tr>
                              <td><?php echo htmlspecialchars((string)$produtoNome, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars((string)$quantidade, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td>R$ <?php echo htmlspecialchars((string)$preco, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars((string)$observacao, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars((string)$estacaoNome, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><div class="badge <?php echo $statusItemBadge; ?>"><?php echo htmlspecialchars((string)$statusItemLabel, ENT_QUOTES, 'UTF-8'); ?></div></td>
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
                <h4 class="mb-1">Estacoes</h4>
                <p class="text-muted mb-0">Gerencie as estacoes de preparo.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalEstacaoNova">
                <i class="mdi mdi-plus"></i> Nova estacao
              </button>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($estacoes)): ?>
                    <tr><td colspan="4">Nenhuma estacao encontrada.</td></tr>
                  <?php else: ?>
                    <?php foreach ($estacoes as $estacao): ?>
                      <?php
                        if (is_object($estacao)) {
                          $estacao = (array)$estacao;
                        }
                        if (!is_array($estacao)) {
                          continue;
                        }
                        $estacaoId = $estacao['id'] ?? '';
                        $nome = $estacao['nome'] ?? '';
                        $criado = formatDateTimeCozinha($estacao['created_at'] ?? '');
                      ?>
                      <tr>
                        <td>#<?php echo htmlspecialchars((string)$estacaoId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$criado, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-estacao" type="button" data-bs-toggle="modal" data-bs-target="#modalEstacaoEditar"
                            data-id="<?php echo htmlspecialchars((string)$estacaoId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-nome="<?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-estacao" type="button" data-bs-toggle="modal" data-bs-target="#modalEstacaoExcluir"
                            data-id="<?php echo htmlspecialchars((string)$estacaoId, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-delete"></i>
                          </button>
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
    </div>

    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Itens de cozinha</h4>
                <p class="text-muted mb-0">Acompanhe o status dos itens.</p>
              </div>
              <form class="d-flex" method="GET" action="">
                <input type="hidden" name="paginas" value="cozinha">
                <select name="status" class="form-select form-select-sm me-2">
                  <option value="">Todos</option>
                  <option value="recebido" <?php echo $statusFiltro === 'recebido' ? 'selected' : ''; ?>>Recebido</option>
                  <option value="preparo" <?php echo $statusFiltro === 'preparo' ? 'selected' : ''; ?>>Preparo</option>
                  <option value="pronto" <?php echo $statusFiltro === 'pronto' ? 'selected' : ''; ?>>Pronto</option>
                </select>
                <button class="btn btn-outline-secondary btn-sm" type="submit">Filtrar</button>
              </form>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Pedido Item</th>
                    <th>Estacao</th>
                    <th>Status</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($itens)): ?>
                    <tr><td colspan="6">Nenhum item encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($itens as $item): ?>
                      <?php
                        if (is_object($item)) {
                          $item = (array)$item;
                        }
                        if (!is_array($item)) {
                          continue;
                        }
                        $itemId = $item['id'] ?? '';
                        $pedidoItemId = $item['pedido_item_id'] ?? '';
                        $estacaoId = $item['estacao_id'] ?? '';
                        $status = $item['status'] ?? '';
                        $criado = formatDateTimeCozinha($item['created_at'] ?? '');
                        [$statusLabel, $badgeClass] = statusBadgeCozinha($status);
                      ?>
                      <tr>
                        <td>#<?php echo htmlspecialchars((string)$itemId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$pedidoItemId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$estacaoId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><div class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars((string)$statusLabel, ENT_QUOTES, 'UTF-8'); ?></div></td>
                        <td><?php echo htmlspecialchars((string)$criado, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-item" type="button" data-bs-toggle="modal" data-bs-target="#modalItemEditar"
                            data-id="<?php echo htmlspecialchars((string)$itemId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-pedido-item="<?php echo htmlspecialchars((string)$pedidoItemId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-estacao="<?php echo htmlspecialchars((string)$estacaoId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-status="<?php echo htmlspecialchars((string)$status, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-secondary btn-sm me-1 btn-status-item" type="button" data-bs-toggle="modal" data-bs-target="#modalItemStatus"
                            data-id="<?php echo htmlspecialchars((string)$itemId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-status="<?php echo htmlspecialchars((string)$status, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-refresh"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-item" type="button" data-bs-toggle="modal" data-bs-target="#modalItemExcluir"
                            data-id="<?php echo htmlspecialchars((string)$itemId, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-delete"></i>
                          </button>
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
    </div>
  </div>
  <div class="modal fade" id="modalEstacaoNova" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="estacao_create">
          <div class="modal-header">
            <h5 class="modal-title">Nova estacao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEstacaoEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formEstacaoEditar">
          <input type="hidden" name="action" value="estacao_update">
          <input type="hidden" name="estacao_id" id="editarEstacaoId">
          <div class="modal-header">
            <h5 class="modal-title">Editar estacao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" id="editarEstacaoNome" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEstacaoExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="estacao_delete">
          <input type="hidden" name="estacao_id" id="excluirEstacaoId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir estacao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao da estacao selecionada?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Excluir</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalItemNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="item_create">
          <div class="modal-header">
            <h5 class="modal-title">Novo item de cozinha</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Pedido Item</label>
              <input type="number" name="pedido_item_id" class="form-control" min="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Estacao</label>
              <input type="number" name="estacao_id" class="form-control" min="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select" required>
                <option value="recebido">Recebido</option>
                <option value="preparo">Preparo</option>
                <option value="pronto">Pronto</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalItemEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formItemEditar">
          <input type="hidden" name="action" value="item_update">
          <input type="hidden" name="item_id" id="editarItemId">
          <div class="modal-header">
            <h5 class="modal-title">Editar item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Pedido Item</label>
              <input type="number" name="pedido_item_id" id="editarItemPedido" class="form-control" min="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Estacao</label>
              <input type="number" name="estacao_id" id="editarItemEstacao" class="form-control" min="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status" id="editarItemStatus" class="form-select" required>
                <option value="recebido">Recebido</option>
                <option value="preparo">Preparo</option>
                <option value="pronto">Pronto</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalItemStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="item_status">
          <input type="hidden" name="item_id" id="statusItemId">
          <div class="modal-header">
            <h5 class="modal-title">Atualizar status</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status" id="statusItemValor" class="form-select" required>
                <option value="recebido">Recebido</option>
                <option value="preparo">Preparo</option>
                <option value="pronto">Pronto</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Atualizar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalItemExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="item_delete">
          <input type="hidden" name="item_id" id="excluirItemId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao do item selecionado?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Excluir</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-edit-estacao').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarEstacaoId').value = this.dataset.id || '';
        document.getElementById('editarEstacaoNome').value = this.dataset.nome || '';
      });
    });

    document.querySelectorAll('.btn-delete-estacao').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirEstacaoId').value = this.dataset.id || '';
      });
    });

    document.querySelectorAll('.btn-edit-item').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarItemId').value = this.dataset.id || '';
        document.getElementById('editarItemPedido').value = this.dataset.pedidoItem || '';
        document.getElementById('editarItemEstacao').value = this.dataset.estacao || '';
        document.getElementById('editarItemStatus').value = this.dataset.status || 'recebido';
      });
    });

    document.querySelectorAll('.btn-status-item').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('statusItemId').value = this.dataset.id || '';
        document.getElementById('statusItemValor').value = this.dataset.status || 'recebido';
      });
    });

    document.querySelectorAll('.btn-delete-item').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirItemId').value = this.dataset.id || '';
      });
    });
  </script>
</div>
