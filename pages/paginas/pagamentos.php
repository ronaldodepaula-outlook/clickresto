<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../classe/env.php';
loadEnvFile(__DIR__ . '/../../.env');

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$token = $_SESSION['token'] ?? '';
$errorMessage = '';
$successMessage = '';
$formas = [];
$pagamentos = [];
$formasMap = [];

function formatMoneyPagamento($value) {
  if ($value === null || $value === '') {
    return '0,00';
  }
  if (is_string($value)) {
    $value = str_replace(',', '.', $value);
  }
  $number = is_numeric($value) ? (float)$value : 0.0;
  return number_format($number, 2, ',', '.');
}

function formatDateTimePagamento($value) {
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

function apiRequestPagamentos($method, $url, $token, $payload = null, &$httpCode = null) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $apiBase !== '' && $token !== '') {
  $action = $_POST['action'] ?? '';
  if ($action === 'forma_create') {
    $payload = [
      'nome' => trim((string)($_POST['nome'] ?? '')),
    ];
    $code = null;
    $resp = apiRequestPagamentos('POST', $apiBase . '/formas-pagamento', $token, $payload, $code);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Forma de pagamento criada com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar forma de pagamento.';
    }
  } elseif ($action === 'forma_update_put' || $action === 'forma_update_patch') {
    $id = (string)($_POST['forma_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'nome' => trim((string)($_POST['nome'] ?? '')),
      ];
      $method = $action === 'forma_update_patch' ? 'PATCH' : 'PUT';
      $code = null;
      $resp = apiRequestPagamentos($method, $apiBase . '/formas-pagamento/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = $method === 'PATCH' ? 'Forma atualizada (PATCH).' : 'Forma atualizada (PUT).';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar forma.';
      }
    } else {
      $errorMessage = 'Forma invalida.';
    }
  } elseif ($action === 'forma_delete') {
    $id = (string)($_POST['forma_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestPagamentos('DELETE', $apiBase . '/formas-pagamento/' . urlencode($id), $token, null, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Forma removida com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover forma.';
      }
    } else {
      $errorMessage = 'Forma invalida.';
    }
  } elseif ($action === 'pagamento_create') {
    $pedidoId = trim((string)($_POST['pedido_id'] ?? ''));
    $formaId = trim((string)($_POST['forma_pagamento_id'] ?? ''));
    $valor = trim((string)($_POST['valor'] ?? '0'));
    if ($pedidoId !== '' && $formaId !== '') {
      $payload = [
        'pedido_id' => (int)$pedidoId,
        'forma_pagamento_id' => (int)$formaId,
        'valor' => is_numeric($valor) ? (float)$valor : 0.0,
      ];
      $code = null;
      $resp = apiRequestPagamentos('POST', $apiBase . '/pagamentos', $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Pagamento criado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao criar pagamento.';
      }
    } else {
      $errorMessage = 'Pedido ou forma invalida.';
    }
  } elseif ($action === 'pagamento_update_put' || $action === 'pagamento_update_patch') {
    $id = (string)($_POST['pagamento_id'] ?? '');
    if ($id !== '') {
      $payload = [];
      if ($action === 'pagamento_update_put') {
        $pedidoId = trim((string)($_POST['pedido_id'] ?? ''));
        $formaId = trim((string)($_POST['forma_pagamento_id'] ?? ''));
        $valor = trim((string)($_POST['valor'] ?? '0'));
        if ($pedidoId !== '') {
          $payload['pedido_id'] = (int)$pedidoId;
        }
        if ($formaId !== '') {
          $payload['forma_pagamento_id'] = (int)$formaId;
        }
        $payload['valor'] = is_numeric($valor) ? (float)$valor : 0.0;
      } else {
        $valor = trim((string)($_POST['valor'] ?? '0'));
        $payload['valor'] = is_numeric($valor) ? (float)$valor : 0.0;
      }
      $method = $action === 'pagamento_update_patch' ? 'PATCH' : 'PUT';
      $code = null;
      $resp = apiRequestPagamentos($method, $apiBase . '/pagamentos/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = $method === 'PATCH' ? 'Pagamento atualizado (PATCH).' : 'Pagamento atualizado (PUT).';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar pagamento.';
      }
    } else {
      $errorMessage = 'Pagamento invalido.';
    }
  } elseif ($action === 'pagamento_delete') {
    $id = (string)($_POST['pagamento_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestPagamentos('DELETE', $apiBase . '/pagamentos/' . urlencode($id), $token, null, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Pagamento removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover pagamento.';
      }
    } else {
      $errorMessage = 'Pagamento invalido.';
    }
  }
}

if ($apiBase !== '' && $token !== '') {
  $code = null;
  $respFormas = apiRequestPagamentos('GET', $apiBase . '/formas-pagamento', $token, null, $code);
  if ($code >= 200 && $code < 300) {
    $formas = $respFormas['data'] ?? $respFormas;
    if (is_array($formas) && isset($formas['data']) && is_array($formas['data'])) {
      $formas = $formas['data'];
    }
    if (!is_array($formas)) {
      $formas = [];
    }
    foreach ($formas as $forma) {
      if (is_object($forma)) {
        $forma = (array)$forma;
      }
      if (!is_array($forma)) {
        continue;
      }
      $formaId = $forma['id'] ?? '';
      $formaNome = $forma['nome'] ?? '';
      if ($formaId !== '') {
        $formasMap[(string)$formaId] = (string)$formaNome;
      }
    }
  } else {
    $errorMessage = $respFormas['message'] ?? 'Nao foi possivel carregar as formas de pagamento.';
  }

  $codePag = null;
  $currentPage = max(1, (int)($_GET['page'] ?? 1));
  $respPag = apiRequestPagamentos('GET', $apiBase . '/pagamentos?per_page=15&page=' . $currentPage, $token, null, $codePag);
  if ($codePag >= 200 && $codePag < 300) {
    $pagamentos = $respPag['data'] ?? $respPag;
    if (is_array($pagamentos) && isset($pagamentos['data']) && is_array($pagamentos['data'])) {
      $pagamentos = $pagamentos['data'];
    }
    if (!is_array($pagamentos)) {
      $pagamentos = [];
    }
  } else {
    $errorMessage = $respPag['message'] ?? 'Nao foi possivel carregar os pagamentos.';
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
            <h3 class="mb-1">Pagamentos</h3>
            <p class="text-muted mb-0">Formas de pagamento e conciliacao.</p>
          </div>
          <div class="btn-wrapper">
            <button class="btn btn-outline-secondary me-2"><i class="mdi mdi-filter"></i> Filtros</button>
            <button class="btn btn-outline-primary me-2" type="button" data-bs-toggle="modal" data-bs-target="#modalFormaNova">
              <i class="mdi mdi-plus"></i> Nova forma
            </button>
            <button class="btn btn-primary text-white" type="button" data-bs-toggle="modal" data-bs-target="#modalPagamentoNovo">
              <i class="mdi mdi-cash"></i> Novo pagamento
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
                <h4 class="mb-1">Formas de pagamento</h4>
                <p class="text-muted mb-0">Cadastre as formas utilizadas no caixa.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalFormaNova">
                <i class="mdi mdi-plus"></i> Nova forma
              </button>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Criado</th>
                    <th>Atualizado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($formas)): ?>
                    <tr><td colspan="4">Nenhuma forma encontrada.</td></tr>
                  <?php else: ?>
                    <?php foreach ($formas as $forma): ?>
                      <?php
                        if (is_object($forma)) {
                          $forma = (array)$forma;
                        }
                        if (!is_array($forma)) {
                          continue;
                        }
                        $formaId = $forma['id'] ?? '';
                        $formaNome = $forma['nome'] ?? '';
                        $criado = formatDateTimePagamento($forma['created_at'] ?? '');
                        $atualizado = formatDateTimePagamento($forma['updated_at'] ?? '');
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars((string)$formaNome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$criado, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$atualizado, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-forma" type="button" data-bs-toggle="modal" data-bs-target="#modalFormaEditar"
                            data-id="<?php echo htmlspecialchars((string)$formaId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-nome="<?php echo htmlspecialchars((string)$formaNome, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-forma" type="button" data-bs-toggle="modal" data-bs-target="#modalFormaExcluir"
                            data-id="<?php echo htmlspecialchars((string)$formaId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-nome="<?php echo htmlspecialchars((string)$formaNome, ENT_QUOTES, 'UTF-8'); ?>">
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
                <h4 class="mb-1">Pagamentos</h4>
                <p class="text-muted mb-0">Registros de pagamentos vinculados aos pedidos.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalPagamentoNovo">
                <i class="mdi mdi-plus"></i> Novo pagamento
              </button>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>Pedido</th>
                    <th>Forma</th>
                    <th>Valor</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($pagamentos)): ?>
                    <tr><td colspan="5">Nenhum pagamento encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($pagamentos as $pagamento): ?>
                      <?php
                        if (is_object($pagamento)) {
                          $pagamento = (array)$pagamento;
                        }
                        if (!is_array($pagamento)) {
                          continue;
                        }
                        $pagamentoId = $pagamento['id'] ?? '';
                        $pedidoId = $pagamento['pedido_id'] ?? '';
                        $formaId = $pagamento['forma_pagamento_id'] ?? '';
                        $valor = $pagamento['valor'] ?? '0';
                        $criado = formatDateTimePagamento($pagamento['created_at'] ?? '');
                        $formaNome = $formasMap[(string)$formaId] ?? $formaId;
                      ?>
                      <tr>
                        <td>#<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$formaNome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>R$ <?php echo htmlspecialchars(formatMoneyPagamento($valor), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$criado, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-pagamento" type="button" data-bs-toggle="modal" data-bs-target="#modalPagamentoEditar"
                            data-id="<?php echo htmlspecialchars((string)$pagamentoId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-pedido="<?php echo htmlspecialchars((string)$pedidoId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-forma="<?php echo htmlspecialchars((string)$formaId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-valor="<?php echo htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-pagamento" type="button" data-bs-toggle="modal" data-bs-target="#modalPagamentoExcluir"
                            data-id="<?php echo htmlspecialchars((string)$pagamentoId, ENT_QUOTES, 'UTF-8'); ?>">
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

  <!-- Modal Forma Nova -->
  <div class="modal fade" id="modalFormaNova" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="forma_create">
          <div class="modal-header">
            <h5 class="modal-title">Nova forma de pagamento</h5>
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

  <!-- Modal Forma Editar -->
  <div class="modal fade" id="modalFormaEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formFormaEditar">
          <input type="hidden" name="action" id="formaAction" value="forma_update_put">
          <input type="hidden" name="forma_id" id="editarFormaId">
          <div class="modal-header">
            <h5 class="modal-title">Editar forma</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" id="editarFormaNome" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" data-action="forma_update_put">Salvar (PUT)</button>
            <button type="submit" class="btn btn-outline-primary" data-action="forma_update_patch">Salvar (PATCH)</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Forma Excluir -->
  <div class="modal fade" id="modalFormaExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="forma_delete">
          <input type="hidden" name="forma_id" id="excluirFormaId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir forma</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao da forma <strong id="excluirFormaNome"></strong>?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Excluir</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Pagamento Novo -->
  <div class="modal fade" id="modalPagamentoNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="pagamento_create">
          <div class="modal-header">
            <h5 class="modal-title">Novo pagamento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Pedido</label>
              <input type="number" name="pedido_id" class="form-control" min="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Forma de pagamento</label>
              <select name="forma_pagamento_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($formas as $forma): ?>
                  <?php
                    if (is_object($forma)) {
                      $forma = (array)$forma;
                    }
                    if (!is_array($forma)) {
                      continue;
                    }
                    $formaId = $forma['id'] ?? '';
                    $formaNome = $forma['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$formaId, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)$formaNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Valor</label>
              <input type="number" step="0.01" name="valor" class="form-control" value="0.00" required>
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

  <!-- Modal Pagamento Editar -->
  <div class="modal fade" id="modalPagamentoEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formPagamentoEditar">
          <input type="hidden" name="action" id="pagamentoAction" value="pagamento_update_put">
          <input type="hidden" name="pagamento_id" id="editarPagamentoId">
          <div class="modal-header">
            <h5 class="modal-title">Editar pagamento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Pedido</label>
              <input type="number" name="pedido_id" id="editarPagamentoPedido" class="form-control" min="1">
            </div>
            <div class="mb-3">
              <label class="form-label">Forma de pagamento</label>
              <select name="forma_pagamento_id" id="editarPagamentoForma" class="form-select">
                <option value="">Selecione</option>
                <?php foreach ($formas as $forma): ?>
                  <?php
                    if (is_object($forma)) {
                      $forma = (array)$forma;
                    }
                    if (!is_array($forma)) {
                      continue;
                    }
                    $formaId = $forma['id'] ?? '';
                    $formaNome = $forma['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$formaId, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)$formaNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Valor</label>
              <input type="number" step="0.01" name="valor" id="editarPagamentoValor" class="form-control" value="0.00" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" data-action="pagamento_update_put">Salvar (PUT)</button>
            <button type="submit" class="btn btn-outline-primary" data-action="pagamento_update_patch">Salvar (PATCH)</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Pagamento Excluir -->
  <div class="modal fade" id="modalPagamentoExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="pagamento_delete">
          <input type="hidden" name="pagamento_id" id="excluirPagamentoId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir pagamento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao do pagamento selecionado?</p>
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
    document.querySelectorAll('.btn-edit-forma').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarFormaId').value = this.dataset.id || '';
        document.getElementById('editarFormaNome').value = this.dataset.nome || '';
      });
    });

    document.querySelectorAll('#formFormaEditar button[type="submit"]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var actionField = document.getElementById('formaAction');
        if (actionField) {
          actionField.value = this.dataset.action || 'forma_update_put';
        }
      });
    });

    document.querySelectorAll('.btn-delete-forma').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirFormaId').value = this.dataset.id || '';
        document.getElementById('excluirFormaNome').textContent = this.dataset.nome || '';
      });
    });

    document.querySelectorAll('.btn-edit-pagamento').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarPagamentoId').value = this.dataset.id || '';
        document.getElementById('editarPagamentoPedido').value = this.dataset.pedido || '';
        document.getElementById('editarPagamentoForma').value = this.dataset.forma || '';
        document.getElementById('editarPagamentoValor').value = this.dataset.valor || '0.00';
      });
    });

    document.querySelectorAll('#formPagamentoEditar button[type="submit"]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var actionField = document.getElementById('pagamentoAction');
        if (actionField) {
          actionField.value = this.dataset.action || 'pagamento_update_put';
        }
      });
    });

    document.querySelectorAll('.btn-delete-pagamento').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirPagamentoId').value = this.dataset.id || '';
      });
    });
  </script>
</div>
