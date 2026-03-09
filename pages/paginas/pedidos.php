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
$pedidos = [];
$pedidoItens = [];
$pedidoOpcoes = [];
$produtos = [];
$totalPedidos = 0;
$pedidosAbertos = 0;
$pedidosPreparando = 0;
$pedidosFinalizados = 0;
$pedidosFechados = 0;

function getFirstValuePedido($data, $keys, $default = null) {
  if (!is_array($data)) {
    return $default;
  }
  foreach ($keys as $key) {
    if (array_key_exists($key, $data) && $data[$key] !== null) {
      return $data[$key];
    }
  }
  return $default;
}

function formatMoneyPedido($value) {
  if ($value === null || $value === '') {
    return '0,00';
  }
  if (is_string($value)) {
    $value = str_replace(',', '.', $value);
  }
  $number = is_numeric($value) ? (float)$value : 0.0;
  return number_format($number, 2, ',', '.');
}

function formatDateTimePedido($value) {
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

function pedidoStatusBadge($status) {
  $statusLower = strtolower((string)$status);
  if ($statusLower === 'aberto') {
    return ['Aberto', 'badge-opacity-success'];
  }
  if ($statusLower === 'preparando') {
    return ['Preparando', 'badge-opacity-warning'];
  }
  if ($statusLower === 'finalizado') {
    return ['Finalizado', 'badge-opacity-info'];
  }
  if ($statusLower === 'fechado') {
    return ['Fechado', 'badge-opacity-secondary'];
  }
  return [ucfirst($statusLower), 'badge-opacity-secondary'];
}

function extractUserIdFromTokenPedido($token) {
  $parts = explode('.', (string)$token);
  if (count($parts) < 2) {
    return '';
  }
  $payload = strtr($parts[1], '-_', '+/');
  $padding = strlen($payload) % 4;
  if ($padding > 0) {
    $payload .= str_repeat('=', 4 - $padding);
  }
  $decoded = base64_decode($payload, true);
  if ($decoded === false) {
    return '';
  }
  $data = json_decode($decoded, true);
  if (!is_array($data)) {
    return '';
  }
  $userId = $data['sub'] ?? $data['user_id'] ?? $data['usuario_id'] ?? '';
  return is_scalar($userId) ? (string)$userId : '';
}

function apiRequestPedidos($method, $url, $token, $payload = null, &$httpCode = null, $empresaId = '') {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  $headers = [
    'Accept: application/json',
    'Authorization: Bearer ' . $token,
  ];
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

if ($usuarioIdSessao === '' && $token !== '') {
  $usuarioIdSessao = extractUserIdFromTokenPedido($token);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $apiBase !== '' && $token !== '') {
  $action = $_POST['action'] ?? '';
  if ($action === 'create' || $action === 'update' || $action === 'abrir') {
    $payload = [];
    $usuarioId = trim((string)($_POST['usuario_id'] ?? ''));
    if ($action === 'abrir') {
      $usuarioIdFinal = $usuarioIdSessao !== '' ? $usuarioIdSessao : extractUserIdFromTokenPedido($token);
      if ($usuarioIdFinal === '') {
        $errorMessage = 'Usuario nao identificado na sessao.';
      } else {
        $payload['usuario_id'] = (int)$usuarioIdFinal;
      }
    } elseif ($usuarioId !== '') {
      $payload['usuario_id'] = (int)$usuarioId;
    }
    $mesaId = trim((string)($_POST['mesa_id'] ?? ''));
    if ($mesaId !== '') {
      $payload['mesa_id'] = (int)$mesaId;
    }
    $comandaId = trim((string)($_POST['comanda_id'] ?? ''));
    if ($comandaId !== '') {
      $payload['comanda_id'] = (int)$comandaId;
    }
    $clienteId = trim((string)($_POST['cliente_id'] ?? ''));
    if ($clienteId !== '') {
      $payload['cliente_id'] = (int)$clienteId;
    }
    $payload['tipo'] = trim((string)($_POST['tipo'] ?? 'mesa'));
    if ($action !== 'abrir') {
      $payload['status'] = trim((string)($_POST['status'] ?? 'aberto'));
      $total = trim((string)($_POST['total'] ?? '0'));
      $payload['total'] = is_numeric($total) ? (float)$total : 0.0;
    }
    if ($action === 'create') {
      $code = null;
      $resp = apiRequestPedidos('POST', $apiBase . '/pedidos', $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Pedido criado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao criar pedido.';
      }
    } elseif ($action === 'update') {
      $pedidoId = (string)($_POST['pedido_id'] ?? '');
      if ($pedidoId !== '') {
        $code = null;
        $resp = apiRequestPedidos('PUT', $apiBase . '/pedidos/' . urlencode($pedidoId), $token, $payload, $code, $empresaId);
        if ($code >= 200 && $code < 300) {
          $successMessage = 'Pedido atualizado com sucesso.';
        } else {
          $errorMessage = $resp['message'] ?? 'Erro ao atualizar pedido.';
        }
      } else {
        $errorMessage = 'Pedido invalido.';
      }
    } else {
      if ($errorMessage === '') {
        $code = null;
        $resp = apiRequestPedidos('POST', $apiBase . '/pedidos/abrir', $token, $payload, $code, $empresaId);
        if ($code >= 200 && $code < 300) {
          $successMessage = 'Pedido aberto com sucesso.';
        } else {
          $errorMessage = $resp['message'] ?? 'Erro ao abrir pedido.';
        }
      }
    }
  } elseif ($action === 'status') {
    $pedidoId = (string)($_POST['pedido_id'] ?? '');
    $status = trim((string)($_POST['status'] ?? ''));
    if ($pedidoId !== '' && $status !== '') {
      $payload = ['status' => $status];
      $code = null;
      $resp = apiRequestPedidos('PATCH', $apiBase . '/pedidos/' . urlencode($pedidoId) . '/status', $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Status do pedido atualizado.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar status.';
      }
    } else {
      $errorMessage = 'Pedido ou status invalidos.';
    }
  } elseif ($action === 'enviar_cozinha') {
    $pedidoId = (string)($_POST['pedido_id'] ?? '');
    $estacaoId = trim((string)($_POST['estacao_id'] ?? ''));
    if ($pedidoId !== '' && $estacaoId !== '') {
      $payload = ['estacao_id' => (int)$estacaoId];
      $code = null;
      $resp = apiRequestPedidos('POST', $apiBase . '/pedidos/' . urlencode($pedidoId) . '/enviar-cozinha', $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Pedido enviado para cozinha.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao enviar pedido para cozinha.';
      }
    } else {
      $errorMessage = 'Pedido ou estacao invalida.';
    }
  } elseif ($action === 'fechar') {
    $pedidoId = (string)($_POST['pedido_id'] ?? '');
    if ($pedidoId !== '') {
      $code = null;
      $resp = apiRequestPedidos('POST', $apiBase . '/pedidos/' . urlencode($pedidoId) . '/fechar', $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Pedido fechado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao fechar pedido.';
      }
    } else {
      $errorMessage = 'Pedido invalido.';
    }
  } elseif ($action === 'delete') {
    $pedidoId = (string)($_POST['pedido_id'] ?? '');
    if ($pedidoId !== '') {
      $code = null;
      $resp = apiRequestPedidos('DELETE', $apiBase . '/pedidos/' . urlencode($pedidoId), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Pedido removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover pedido.';
      }
    } else {
      $errorMessage = 'Pedido invalido.';
    }
  } elseif ($action === 'add_item') {
    $pedidoId = (string)($_POST['pedido_id'] ?? '');
    $produtoId = trim((string)($_POST['produto_id'] ?? ''));
    $quantidade = trim((string)($_POST['quantidade'] ?? '1'));
    $preco = trim((string)($_POST['preco'] ?? '0'));
    if ($pedidoId !== '' && $produtoId !== '') {
      $payload = [
        'produto_id' => (int)$produtoId,
        'quantidade' => is_numeric($quantidade) ? (int)$quantidade : 1,
        'preco' => is_numeric($preco) ? (float)$preco : 0.0,
      ];
      $code = null;
      $resp = apiRequestPedidos('POST', $apiBase . '/pedidos/' . urlencode($pedidoId) . '/itens', $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Item adicionado ao pedido.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao adicionar item.';
      }
    } else {
      $errorMessage = 'Pedido ou produto invalido.';
    }
  } elseif ($action === 'create_item') {
    $pedidoId = trim((string)($_POST['pedido_id'] ?? ''));
    $produtoId = trim((string)($_POST['produto_id'] ?? ''));
    $quantidade = trim((string)($_POST['quantidade'] ?? '1'));
    $preco = trim((string)($_POST['preco'] ?? '0'));
    if ($pedidoId !== '' && $produtoId !== '') {
      $payload = [
        'pedido_id' => (int)$pedidoId,
        'produto_id' => (int)$produtoId,
        'quantidade' => is_numeric($quantidade) ? (int)$quantidade : 1,
        'preco' => is_numeric($preco) ? (float)$preco : 0.0,
      ];
      $code = null;
      $resp = apiRequestPedidos('POST', $apiBase . '/pedido-itens', $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Item criado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao criar item.';
      }
    } else {
      $errorMessage = 'Pedido ou produto invalido.';
    }
  } elseif ($action === 'update_item') {
    $itemId = (string)($_POST['item_id'] ?? '');
    $quantidade = trim((string)($_POST['quantidade'] ?? '1'));
    $preco = trim((string)($_POST['preco'] ?? '0'));
    if ($itemId !== '') {
      $payload = [
        'quantidade' => is_numeric($quantidade) ? (int)$quantidade : 1,
        'preco' => is_numeric($preco) ? (float)$preco : 0.0,
      ];
      $code = null;
      $resp = apiRequestPedidos('PUT', $apiBase . '/pedido-itens/' . urlencode($itemId), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Item atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar item.';
      }
    } else {
      $errorMessage = 'Item invalido.';
    }
  } elseif ($action === 'delete_item') {
    $itemId = (string)($_POST['item_id'] ?? '');
    if ($itemId !== '') {
      $code = null;
      $resp = apiRequestPedidos('DELETE', $apiBase . '/pedido-itens/' . urlencode($itemId), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Item removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover item.';
      }
    } else {
      $errorMessage = 'Item invalido.';
    }
  } elseif ($action === 'create_opcao') {
    $pedidoItemId = trim((string)($_POST['pedido_item_id'] ?? ''));
    $opcaoItemId = trim((string)($_POST['opcao_item_id'] ?? ''));
    if ($pedidoItemId !== '' && $opcaoItemId !== '') {
      $payload = [
        'pedido_item_id' => (int)$pedidoItemId,
        'opcao_item_id' => (int)$opcaoItemId,
      ];
      $code = null;
      $resp = apiRequestPedidos('POST', $apiBase . '/pedido-item-opcoes', $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Opcao criada com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao criar opcao.';
      }
    } else {
      $errorMessage = 'Dados invalidos para opcao.';
    }
  } elseif ($action === 'update_opcao') {
    $opcaoId = (string)($_POST['opcao_id'] ?? '');
    $opcaoItemId = trim((string)($_POST['opcao_item_id'] ?? ''));
    if ($opcaoId !== '' && $opcaoItemId !== '') {
      $payload = [
        'opcao_item_id' => (int)$opcaoItemId,
      ];
      $code = null;
      $resp = apiRequestPedidos('PUT', $apiBase . '/pedido-item-opcoes/' . urlencode($opcaoId), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Opcao atualizada com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar opcao.';
      }
    } else {
      $errorMessage = 'Opcao invalida.';
    }
  } elseif ($action === 'delete_opcao') {
    $opcaoId = (string)($_POST['opcao_id'] ?? '');
    if ($opcaoId !== '') {
      $code = null;
      $resp = apiRequestPedidos('DELETE', $apiBase . '/pedido-item-opcoes/' . urlencode($opcaoId), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Opcao removida com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover opcao.';
      }
    } else {
      $errorMessage = 'Opcao invalida.';
    }
  }
}

if ($apiBase !== '' && $token !== '') {
  $code = null;
  $currentPage = max(1, (int)($_GET['page'] ?? 1));
  $resp = apiRequestPedidos('GET', $apiBase . '/pedidos?per_page=15&page=' . $currentPage, $token, null, $code, $empresaId);
  if ($code >= 200 && $code < 300) {
    $pedidos = $resp['data'] ?? $resp;
    if (is_array($pedidos) && isset($pedidos['data']) && is_array($pedidos['data'])) {
      $pedidos = $pedidos['data'];
    }
    if (!is_array($pedidos)) {
      $pedidos = [];
    }
    $totalPedidos = (int)($resp['total'] ?? count($pedidos));
    foreach ($pedidos as $pedido) {
      if (is_object($pedido)) {
        $pedido = (array)$pedido;
      }
      if (!is_array($pedido)) {
        continue;
      }
      $status = strtolower((string)getFirstValuePedido($pedido, ['status'], ''));
      if ($status === 'aberto') {
        $pedidosAbertos++;
      } elseif ($status === 'preparando') {
        $pedidosPreparando++;
      } elseif ($status === 'finalizado') {
        $pedidosFinalizados++;
      } elseif ($status === 'fechado') {
        $pedidosFechados++;
      }
    }
  } else {
    $errorMessage = $resp['message'] ?? 'Nao foi possivel carregar os pedidos.';
  }

  $itensResp = apiRequestPedidos('GET', $apiBase . '/pedido-itens?per_page=15', $token, null, $code, $empresaId);
  if ($code >= 200 && $code < 300) {
    $pedidoItens = $itensResp['data'] ?? $itensResp;
    if (is_array($pedidoItens) && isset($pedidoItens['data']) && is_array($pedidoItens['data'])) {
      $pedidoItens = $pedidoItens['data'];
    }
    if (!is_array($pedidoItens)) {
      $pedidoItens = [];
    }
  }

  $opcaoResp = apiRequestPedidos('GET', $apiBase . '/pedido-item-opcoes?per_page=15', $token, null, $code, $empresaId);
  if ($code >= 200 && $code < 300) {
    $pedidoOpcoes = $opcaoResp['data'] ?? $opcaoResp;
    if (is_array($pedidoOpcoes) && isset($pedidoOpcoes['data']) && is_array($pedidoOpcoes['data'])) {
      $pedidoOpcoes = $pedidoOpcoes['data'];
    }
    if (!is_array($pedidoOpcoes)) {
      $pedidoOpcoes = [];
    }
  }

  $prodResp = apiRequestPedidos('GET', $apiBase . '/produtos?per_page=100', $token, null, $code, $empresaId);
  if ($code >= 200 && $code < 300) {
    $produtos = $prodResp['data'] ?? $prodResp;
    if (is_array($produtos) && isset($produtos['data']) && is_array($produtos['data'])) {
      $produtos = $produtos['data'];
    }
    if (!is_array($produtos)) {
      $produtos = [];
    }
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
            <h3 class="mb-1">Pedidos</h3>
            <p class="text-muted mb-0">Gestao completa de pedidos, itens e opcoes.</p>
          </div>
          <div class="btn-wrapper">
            <button class="btn btn-outline-secondary me-2"><i class="mdi mdi-filter"></i> Filtros</button>
            <button class="btn btn-primary text-white" type="button" data-bs-toggle="modal" data-bs-target="#modalPedidoNovo">
              <i class="mdi mdi-plus"></i> Novo pedido
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
      <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Pedidos abertos</p>
            <h3 class="mb-0"><?php echo (int)$pedidosAbertos; ?></h3>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Preparando</p>
            <h3 class="mb-0 text-warning"><?php echo (int)$pedidosPreparando; ?></h3>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Finalizados</p>
            <h3 class="mb-0 text-info"><?php echo (int)$pedidosFinalizados; ?></h3>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Fechados</p>
            <h3 class="mb-0 text-secondary"><?php echo (int)$pedidosFechados; ?></h3>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>Pedido</th>
                    <th>Tipo</th>
                    <th>Mesa</th>
                    <th>Comanda</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($pedidos)): ?>
                    <tr><td colspan="8">Nenhum pedido encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($pedidos as $pedido): ?>
                      <?php
                        if (is_object($pedido)) {
                          $pedido = (array)$pedido;
                        }
                        if (!is_array($pedido)) {
                          continue;
                        }
                        $pedidoId = getFirstValuePedido($pedido, ['id', 'pedido_id'], '');
                        $tipo = getFirstValuePedido($pedido, ['tipo'], '');
                        $mesaId = getFirstValuePedido($pedido, ['mesa_id'], '');
                        $comandaId = getFirstValuePedido($pedido, ['comanda_id'], '');
                        $total = getFirstValuePedido($pedido, ['total'], '0');
                        $status = getFirstValuePedido($pedido, ['status'], '');
                        $criadoEm = getFirstValuePedido($pedido, ['criado_em', 'created_at'], '');
                        [$statusLabel, $badgeClass] = pedidoStatusBadge($status);
                      ?>
                      <tr>
                        <td>#<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$tipo, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $mesaId !== '' ? 'Mesa ' . htmlspecialchars((string)$mesaId, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                        <td><?php echo $comandaId !== '' ? 'C' . htmlspecialchars((string)$comandaId, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                        <td>R$ <?php echo htmlspecialchars(formatMoneyPedido($total), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><div class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars((string)$statusLabel, ENT_QUOTES, 'UTF-8'); ?></div></td>
                        <td><?php echo htmlspecialchars(formatDateTimePedido($criadoEm), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-pedido" type="button" data-bs-toggle="modal" data-bs-target="#modalPedidoEditar"
                            data-id="<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-usuario="<?php echo htmlspecialchars((string)getFirstValuePedido($pedido, ['usuario_id'], ''), ENT_QUOTES, 'UTF-8'); ?>"
                            data-mesa="<?php echo htmlspecialchars((string)$mesaId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-comanda="<?php echo htmlspecialchars((string)$comandaId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-cliente="<?php echo htmlspecialchars((string)getFirstValuePedido($pedido, ['cliente_id'], ''), ENT_QUOTES, 'UTF-8'); ?>"
                            data-tipo="<?php echo htmlspecialchars((string)$tipo, ENT_QUOTES, 'UTF-8'); ?>"
                            data-status="<?php echo htmlspecialchars((string)$status, ENT_QUOTES, 'UTF-8'); ?>"
                            data-total="<?php echo htmlspecialchars((string)$total, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-secondary btn-sm me-1 btn-status-pedido" type="button" data-bs-toggle="modal" data-bs-target="#modalPedidoStatus"
                            data-id="<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-status="<?php echo htmlspecialchars((string)$status, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-refresh"></i>
                          </button>
                          <button class="btn btn-outline-success btn-sm me-1 btn-add-item" type="button" data-bs-toggle="modal" data-bs-target="#modalPedidoItem"
                            data-id="<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-plus"></i>
                          </button>
                          <button class="btn btn-outline-warning btn-sm me-1 btn-enviar-cozinha" type="button" data-bs-toggle="modal" data-bs-target="#modalPedidoCozinha"
                            data-id="<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-fire"></i>
                          </button>
                          <form class="d-inline" method="POST" action="">
                            <input type="hidden" name="action" value="fechar">
                            <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?>">
                            <button class="btn btn-outline-dark btn-sm me-1" type="submit">
                              <i class="mdi mdi-lock"></i>
                            </button>
                          </form>
                          <button class="btn btn-outline-danger btn-sm btn-delete-pedido" type="button" data-bs-toggle="modal" data-bs-target="#modalPedidoExcluir"
                            data-id="<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?>">
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
                <h4 class="mb-1">Itens do pedido</h4>
                <p class="text-muted mb-0">Ultimos itens registrados.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalItemNovo">
                <i class="mdi mdi-plus"></i> Novo item
              </button>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Pedido</th>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preco</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($pedidoItens)): ?>
                    <tr><td colspan="7">Nenhum item encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($pedidoItens as $item): ?>
                      <?php
                        if (is_object($item)) {
                          $item = (array)$item;
                        }
                        if (!is_array($item)) {
                          continue;
                        }
                        $itemId = getFirstValuePedido($item, ['id', 'pedido_item_id'], '');
                        $pedidoId = getFirstValuePedido($item, ['pedido_id'], '');
                        $produtoId = getFirstValuePedido($item, ['produto_id'], '');
                        $quantidade = getFirstValuePedido($item, ['quantidade'], '');
                        $preco = getFirstValuePedido($item, ['preco'], '0');
                        $criado = formatDateTimePedido(getFirstValuePedido($item, ['created_at'], ''));
                      ?>
                      <tr>
                        <td>#<?php echo htmlspecialchars((string)$itemId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$produtoId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$quantidade, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>R$ <?php echo htmlspecialchars(formatMoneyPedido($preco), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$criado, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-item" type="button" data-bs-toggle="modal" data-bs-target="#modalItemEditar"
                            data-id="<?php echo htmlspecialchars((string)$itemId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-quantidade="<?php echo htmlspecialchars((string)$quantidade, ENT_QUOTES, 'UTF-8'); ?>"
                            data-preco="<?php echo htmlspecialchars((string)$preco, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
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

    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Opcoes dos itens</h4>
                <p class="text-muted mb-0">Ajustes e adicionais vinculados aos itens.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalOpcaoNova">
                <i class="mdi mdi-plus"></i> Nova opcao
              </button>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Pedido Item</th>
                    <th>Opcao Item</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($pedidoOpcoes)): ?>
                    <tr><td colspan="5">Nenhuma opcao encontrada.</td></tr>
                  <?php else: ?>
                    <?php foreach ($pedidoOpcoes as $opcao): ?>
                      <?php
                        if (is_object($opcao)) {
                          $opcao = (array)$opcao;
                        }
                        if (!is_array($opcao)) {
                          continue;
                        }
                        $opcaoId = getFirstValuePedido($opcao, ['id', 'pedido_item_opcao_id'], '');
                        $pedidoItemId = getFirstValuePedido($opcao, ['pedido_item_id'], '');
                        $opcaoItemId = getFirstValuePedido($opcao, ['opcao_item_id'], '');
                        $criadoOpcao = formatDateTimePedido(getFirstValuePedido($opcao, ['created_at'], ''));
                      ?>
                      <tr>
                        <td>#<?php echo htmlspecialchars((string)$opcaoId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$pedidoItemId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$opcaoItemId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$criadoOpcao, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-opcao" type="button" data-bs-toggle="modal" data-bs-target="#modalOpcaoEditar"
                            data-id="<?php echo htmlspecialchars((string)$opcaoId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-opcao-item="<?php echo htmlspecialchars((string)$opcaoItemId, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-opcao" type="button" data-bs-toggle="modal" data-bs-target="#modalOpcaoExcluir"
                            data-id="<?php echo htmlspecialchars((string)$opcaoId, ENT_QUOTES, 'UTF-8'); ?>">
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
  <!-- Modal Novo Pedido -->
  <div class="modal fade" id="modalPedidoNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <form method="POST" action="" id="formPedidoNovo">
          <input type="hidden" name="action" id="pedidoAction" value="create">
          <div class="modal-header">
            <h5 class="modal-title">Novo pedido</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Usuario (sessao)</label>
                <input type="number" name="usuario_id" class="form-control" min="1" value="<?php echo htmlspecialchars((string)$usuarioIdSessao, ENT_QUOTES, 'UTF-8'); ?>" readonly required>
              </div>
              <div class="col-md-6 mb-3" id="novoPedidoMesaWrap">
                <label class="form-label">Mesa (opcional)</label>
                <input type="number" name="mesa_id" id="novoPedidoMesa" class="form-control" min="1">
              </div>
              <div class="col-md-6 mb-3" id="novoPedidoComandaWrap">
                <label class="form-label">Comanda (opcional)</label>
                <input type="number" name="comanda_id" id="novoPedidoComanda" class="form-control" min="1">
              </div>
              <div class="col-md-6 mb-3" id="novoPedidoClienteWrap">
                <label class="form-label">Cliente (opcional)</label>
                <input type="number" name="cliente_id" id="novoPedidoCliente" class="form-control" min="1">
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" id="novoPedidoTipo" class="form-select" required>
                  <option value="mesa">Mesa</option>
                  <option value="delivery">Delivery</option>
                  <option value="balcao">Balcao</option>
                  <option value="comanda">Comanda</option>
                  <option value="retirada">Retirada</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Status</label>
                <select name="status" id="novoPedidoStatus" class="form-select">
                  <option value="aberto">Aberto</option>
                  <option value="preparando">Preparando</option>
                  <option value="finalizado">Finalizado</option>
                  <option value="fechado">Fechado</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Total</label>
                <input type="number" step="0.01" name="total" id="novoPedidoTotal" class="form-control" value="0.00">
              </div>
            </div>
            <small class="text-muted">Use "Abrir pedido" para iniciar com status aberto e total zero (atualiza conforme itens).</small>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" data-action="create">Criar pedido</button>
            <button type="submit" class="btn btn-success" data-action="abrir">Abrir pedido</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Editar Pedido -->
  <div class="modal fade" id="modalPedidoEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <form method="POST" action="" id="formPedidoEditar">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="pedido_id" id="editarPedidoId">
          <div class="modal-header">
            <h5 class="modal-title">Editar pedido</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Usuario</label>
                <input type="number" name="usuario_id" id="editarPedidoUsuario" class="form-control" min="1" required>
              </div>
              <div class="col-md-6 mb-3" id="editarPedidoMesaWrap">
                <label class="form-label">Mesa (opcional)</label>
                <input type="number" name="mesa_id" id="editarPedidoMesa" class="form-control" min="1">
              </div>
              <div class="col-md-6 mb-3" id="editarPedidoComandaWrap">
                <label class="form-label">Comanda (opcional)</label>
                <input type="number" name="comanda_id" id="editarPedidoComanda" class="form-control" min="1">
              </div>
              <div class="col-md-6 mb-3" id="editarPedidoClienteWrap">
                <label class="form-label">Cliente (opcional)</label>
                <input type="number" name="cliente_id" id="editarPedidoCliente" class="form-control" min="1">
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" id="editarPedidoTipo" class="form-select" required>
                  <option value="mesa">Mesa</option>
                  <option value="delivery">Delivery</option>
                  <option value="balcao">Balcao</option>
                  <option value="comanda">Comanda</option>
                  <option value="retirada">Retirada</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Status</label>
                <select name="status" id="editarPedidoStatus" class="form-select">
                  <option value="aberto">Aberto</option>
                  <option value="preparando">Preparando</option>
                  <option value="finalizado">Finalizado</option>
                  <option value="fechado">Fechado</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Total</label>
                <input type="number" step="0.01" name="total" id="editarPedidoTotal" class="form-control" value="0.00">
              </div>
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

  <!-- Modal Status Pedido -->
  <div class="modal fade" id="modalPedidoStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="status">
          <input type="hidden" name="pedido_id" id="statusPedidoId">
          <div class="modal-header">
            <h5 class="modal-title">Atualizar status</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status" id="statusPedidoValor" class="form-select" required>
                <option value="aberto">Aberto</option>
                <option value="preparando">Preparando</option>
                <option value="finalizado">Finalizado</option>
                <option value="fechado">Fechado</option>
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

  <!-- Modal Enviar Cozinha -->
  <div class="modal fade" id="modalPedidoCozinha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="enviar_cozinha">
          <input type="hidden" name="pedido_id" id="cozinhaPedidoId">
          <div class="modal-header">
            <h5 class="modal-title">Enviar para cozinha</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Estacao</label>
              <input type="number" name="estacao_id" class="form-control" min="1" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning">Enviar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Adicionar Item -->
  <div class="modal fade" id="modalPedidoItem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formPedidoItem">
          <input type="hidden" name="action" value="add_item">
          <input type="hidden" name="pedido_id" id="itemPedidoId">
          <div class="modal-header">
            <h5 class="modal-title">Adicionar item ao pedido</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Produto</label>
              <select name="produto_id" id="itemProdutoSelect" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($produtos as $produto): ?>
                  <?php
                    if (is_object($produto)) {
                      $produto = (array)$produto;
                    }
                    if (!is_array($produto)) {
                      continue;
                    }
                    $produtoId = getFirstValuePedido($produto, ['id', 'produto_id'], '');
                    $produtoNome = getFirstValuePedido($produto, ['nome', 'produto'], '');
                    $produtoPreco = getFirstValuePedido($produto, ['preco'], '0');
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$produtoId, ENT_QUOTES, 'UTF-8'); ?>" data-preco="<?php echo htmlspecialchars((string)$produtoPreco, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)$produtoNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Quantidade</label>
              <input type="number" name="quantidade" class="form-control" min="1" value="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Preco</label>
              <input type="number" step="0.01" name="preco" id="itemPreco" class="form-control" value="0.00" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Adicionar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Excluir Pedido -->
  <div class="modal fade" id="modalPedidoExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="pedido_id" id="excluirPedidoId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir pedido</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao do pedido selecionado?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Excluir</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Novo Item -->
  <div class="modal fade" id="modalItemNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formItemNovo">
          <input type="hidden" name="action" value="create_item">
          <div class="modal-header">
            <h5 class="modal-title">Novo item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Pedido</label>
              <input type="number" name="pedido_id" class="form-control" min="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Produto</label>
              <select name="produto_id" id="itemProdutoNovoSelect" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($produtos as $produto): ?>
                  <?php
                    if (is_object($produto)) {
                      $produto = (array)$produto;
                    }
                    if (!is_array($produto)) {
                      continue;
                    }
                    $produtoId = getFirstValuePedido($produto, ['id', 'produto_id'], '');
                    $produtoNome = getFirstValuePedido($produto, ['nome', 'produto'], '');
                    $produtoPreco = getFirstValuePedido($produto, ['preco'], '0');
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$produtoId, ENT_QUOTES, 'UTF-8'); ?>" data-preco="<?php echo htmlspecialchars((string)$produtoPreco, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)$produtoNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Quantidade</label>
              <input type="number" name="quantidade" class="form-control" min="1" value="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Preco</label>
              <input type="number" step="0.01" name="preco" id="itemNovoPreco" class="form-control" value="0.00" required>
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

  <!-- Modal Editar Item -->
  <div class="modal fade" id="modalItemEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formItemEditar">
          <input type="hidden" name="action" value="update_item">
          <input type="hidden" name="item_id" id="editarItemId">
          <div class="modal-header">
            <h5 class="modal-title">Editar item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Quantidade</label>
              <input type="number" name="quantidade" id="editarItemQuantidade" class="form-control" min="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Preco</label>
              <input type="number" step="0.01" name="preco" id="editarItemPreco" class="form-control" required>
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

  <!-- Modal Excluir Item -->
  <div class="modal fade" id="modalItemExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="delete_item">
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
  <!-- Modal Nova Opcao -->
  <div class="modal fade" id="modalOpcaoNova" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="create_opcao">
          <div class="modal-header">
            <h5 class="modal-title">Nova opcao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Pedido Item</label>
              <input type="number" name="pedido_item_id" class="form-control" min="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Opcao Item</label>
              <input type="number" name="opcao_item_id" class="form-control" min="1" required>
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

  <!-- Modal Editar Opcao -->
  <div class="modal fade" id="modalOpcaoEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formOpcaoEditar">
          <input type="hidden" name="action" value="update_opcao">
          <input type="hidden" name="opcao_id" id="editarOpcaoId">
          <div class="modal-header">
            <h5 class="modal-title">Editar opcao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Opcao Item</label>
              <input type="number" name="opcao_item_id" id="editarOpcaoItem" class="form-control" min="1" required>
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

  <!-- Modal Excluir Opcao -->
  <div class="modal fade" id="modalOpcaoExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="delete_opcao">
          <input type="hidden" name="opcao_id" id="excluirOpcaoId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir opcao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao da opcao selecionada?</p>
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
    (function () {
      var formPedidoNovo = document.getElementById('formPedidoNovo');
      var modalPedidoNovo = document.getElementById('modalPedidoNovo');
      var resetNovoPedido = function () {
        var actionField = document.getElementById('pedidoAction');
        var statusField = document.getElementById('novoPedidoStatus');
        var totalField = document.getElementById('novoPedidoTotal');
        if (actionField) {
          actionField.value = 'create';
        }
        if (statusField) {
          statusField.removeAttribute('disabled');
        }
        if (totalField) {
          totalField.removeAttribute('disabled');
        }
      };
      if (modalPedidoNovo) {
        modalPedidoNovo.addEventListener('show.bs.modal', resetNovoPedido);
      }
      if (formPedidoNovo) {
        formPedidoNovo.querySelectorAll('button[type="submit"]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var actionField = document.getElementById('pedidoAction');
            if (actionField) {
              actionField.value = this.dataset.action || 'create';
            }
            var statusField = document.getElementById('novoPedidoStatus');
            var totalField = document.getElementById('novoPedidoTotal');
            if (this.dataset.action === 'abrir') {
              if (statusField) {
                statusField.value = 'aberto';
                statusField.setAttribute('disabled', 'disabled');
              }
              if (totalField) {
                totalField.value = '0.00';
                totalField.setAttribute('disabled', 'disabled');
              }
            } else {
              if (statusField) {
                statusField.removeAttribute('disabled');
              }
              if (totalField) {
                totalField.removeAttribute('disabled');
              }
            }
          });
        });
      }

      document.querySelectorAll('.btn-edit-pedido').forEach(function (btn) {
        btn.addEventListener('click', function () {
          document.getElementById('editarPedidoId').value = this.dataset.id || '';
          document.getElementById('editarPedidoUsuario').value = this.dataset.usuario || '';
          document.getElementById('editarPedidoMesa').value = this.dataset.mesa || '';
          document.getElementById('editarPedidoComanda').value = this.dataset.comanda || '';
          document.getElementById('editarPedidoCliente').value = this.dataset.cliente || '';
          document.getElementById('editarPedidoTipo').value = this.dataset.tipo || 'mesa';
          document.getElementById('editarPedidoStatus').value = this.dataset.status || 'aberto';
          document.getElementById('editarPedidoTotal').value = this.dataset.total || '0.00';
        });
      });

      document.querySelectorAll('.btn-status-pedido').forEach(function (btn) {
        btn.addEventListener('click', function () {
          document.getElementById('statusPedidoId').value = this.dataset.id || '';
          document.getElementById('statusPedidoValor').value = this.dataset.status || 'aberto';
        });
      });

      document.querySelectorAll('.btn-enviar-cozinha').forEach(function (btn) {
        btn.addEventListener('click', function () {
          document.getElementById('cozinhaPedidoId').value = this.dataset.id || '';
        });
      });

      document.querySelectorAll('.btn-add-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
          document.getElementById('itemPedidoId').value = this.dataset.id || '';
        });
      });

      document.querySelectorAll('.btn-delete-pedido').forEach(function (btn) {
        btn.addEventListener('click', function () {
          document.getElementById('excluirPedidoId').value = this.dataset.id || '';
        });
      });

      document.querySelectorAll('.btn-edit-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
          document.getElementById('editarItemId').value = this.dataset.id || '';
          document.getElementById('editarItemQuantidade').value = this.dataset.quantidade || '1';
          document.getElementById('editarItemPreco').value = this.dataset.preco || '0.00';
        });
      });

      document.querySelectorAll('.btn-delete-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
          document.getElementById('excluirItemId').value = this.dataset.id || '';
        });
      });

      document.querySelectorAll('.btn-edit-opcao').forEach(function (btn) {
        btn.addEventListener('click', function () {
          document.getElementById('editarOpcaoId').value = this.dataset.id || '';
          document.getElementById('editarOpcaoItem').value = this.dataset.opcaoItem || '';
        });
      });

      document.querySelectorAll('.btn-delete-opcao').forEach(function (btn) {
        btn.addEventListener('click', function () {
          document.getElementById('excluirOpcaoId').value = this.dataset.id || '';
        });
      });

      function bindProdutoPreco(selectId, priceInputId) {
        var select = document.getElementById(selectId);
        var priceInput = document.getElementById(priceInputId);
        if (!select || !priceInput) {
          return;
        }
        var updatePrice = function () {
          var option = select.options[select.selectedIndex];
          var preco = option ? option.getAttribute('data-preco') : '';
          if (preco) {
            var parsed = parseFloat(preco);
            if (!isNaN(parsed)) {
              priceInput.value = parsed.toFixed(2);
            }
          }
        };
        select.addEventListener('change', updatePrice);
        updatePrice();
      }

      bindProdutoPreco('itemProdutoSelect', 'itemPreco');
      bindProdutoPreco('itemProdutoNovoSelect', 'itemNovoPreco');

      function bindTipoSelect(selectId, prefix) {
        var select = document.getElementById(selectId);
        if (!select) {
          return;
        }
        var mesaWrap = document.getElementById(prefix + 'MesaWrap');
        var comandaWrap = document.getElementById(prefix + 'ComandaWrap');
        var mesaInput = document.getElementById(prefix + 'Mesa');
        var comandaInput = document.getElementById(prefix + 'Comanda');

        var update = function () {
          var tipo = select.value;
          var showMesa = tipo === 'mesa';
          var showComanda = tipo === 'comanda';
          if (mesaWrap) {
            mesaWrap.style.display = showMesa ? '' : 'none';
          }
          if (comandaWrap) {
            comandaWrap.style.display = showComanda ? '' : 'none';
          }
          if (!showMesa && mesaInput) {
            mesaInput.value = '';
          }
          if (!showComanda && comandaInput) {
            comandaInput.value = '';
          }
        };

        select.addEventListener('change', update);
        update();
      }

      bindTipoSelect('novoPedidoTipo', 'novoPedido');
      bindTipoSelect('editarPedidoTipo', 'editarPedido');
    })();
  </script>
</div>
