<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../classe/env.php';
loadEnvFile(__DIR__ . '/../../.env');

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$token = $_SESSION['token'] ?? '';
$usuarioIdSessao = $_SESSION['user_id'] ?? '';
$errorMessage = '';
$successMessage = '';
$caixas = [];
$movimentos = [];
$entradasTotal = 0.0;
$saidasTotal = 0.0;

function formatMoneyCaixa($value) {
  if ($value === null || $value === '') {
    return '0,00';
  }
  if (is_string($value)) {
    $value = str_replace(',', '.', $value);
  }
  $number = is_numeric($value) ? (float)$value : 0.0;
  return number_format($number, 2, ',', '.');
}

function formatDateTimeCaixa($value) {
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

function apiRequestCaixa($method, $url, $token, $payload = null, &$httpCode = null) {
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
  if ($action === 'caixa_create') {
    $saldoInicial = trim((string)($_POST['saldo_inicial'] ?? '0'));
    $payload = [
      'usuario_id' => (int)($_POST['usuario_id'] ?? 0),
      'saldo_inicial' => is_numeric($saldoInicial) ? (float)$saldoInicial : 0.0,
    ];
    $code = null;
    $resp = apiRequestCaixa('POST', $apiBase . '/caixas', $token, $payload, $code);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Caixa criado com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar caixa.';
    }
  } elseif ($action === 'caixa_abrir') {
    $saldoInicial = trim((string)($_POST['saldo_inicial'] ?? '0'));
    $payload = [
      'saldo_inicial' => is_numeric($saldoInicial) ? (float)$saldoInicial : 0.0,
    ];
    $code = null;
    $resp = apiRequestCaixa('POST', $apiBase . '/caixas/abrir', $token, $payload, $code);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Caixa aberto com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao abrir caixa.';
    }
  } elseif ($action === 'caixa_fechar') {
    $id = (string)($_POST['caixa_id'] ?? '');
    $saldoFinal = trim((string)($_POST['saldo_final'] ?? '0'));
    if ($id !== '') {
      $payload = [
        'saldo_final' => is_numeric($saldoFinal) ? (float)$saldoFinal : 0.0,
      ];
      $code = null;
      $resp = apiRequestCaixa('POST', $apiBase . '/caixas/' . urlencode($id) . '/fechar', $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Caixa fechado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao fechar caixa.';
      }
    } else {
      $errorMessage = 'Caixa invalido.';
    }
  } elseif ($action === 'caixa_update') {
    $id = (string)($_POST['caixa_id'] ?? '');
    if ($id !== '') {
      $saldoInicial = trim((string)($_POST['saldo_inicial'] ?? '0'));
      $saldoFinal = trim((string)($_POST['saldo_final'] ?? '0'));
      $payload = [
        'usuario_id' => (int)($_POST['usuario_id'] ?? 0),
        'saldo_inicial' => is_numeric($saldoInicial) ? (float)$saldoInicial : 0.0,
        'saldo_final' => is_numeric($saldoFinal) ? (float)$saldoFinal : 0.0,
      ];
      $code = null;
      $resp = apiRequestCaixa('PUT', $apiBase . '/caixas/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Caixa atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar caixa.';
      }
    } else {
      $errorMessage = 'Caixa invalido.';
    }
  } elseif ($action === 'caixa_delete') {
    $id = (string)($_POST['caixa_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestCaixa('DELETE', $apiBase . '/caixas/' . urlencode($id), $token, null, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Caixa removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover caixa.';
      }
    } else {
      $errorMessage = 'Caixa invalido.';
    }
  } elseif ($action === 'mov_create') {
    $caixaId = trim((string)($_POST['caixa_id'] ?? ''));
    $tipo = trim((string)($_POST['tipo'] ?? 'entrada'));
    $valor = trim((string)($_POST['valor'] ?? '0'));
    $descricao = trim((string)($_POST['descricao'] ?? ''));
    if ($caixaId !== '') {
      $payload = [
        'caixa_id' => (int)$caixaId,
        'tipo' => $tipo,
        'valor' => is_numeric($valor) ? (float)$valor : 0.0,
        'descricao' => $descricao,
      ];
      $code = null;
      $resp = apiRequestCaixa('POST', $apiBase . '/caixa-movimentos', $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Movimento criado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao criar movimento.';
      }
    } else {
      $errorMessage = 'Caixa invalido para movimento.';
    }
  } elseif ($action === 'mov_update') {
    $id = (string)($_POST['mov_id'] ?? '');
    if ($id !== '') {
      $caixaId = trim((string)($_POST['caixa_id'] ?? ''));
      $tipo = trim((string)($_POST['tipo'] ?? 'entrada'));
      $valor = trim((string)($_POST['valor'] ?? '0'));
      $descricao = trim((string)($_POST['descricao'] ?? ''));
      $payload = [
        'caixa_id' => (int)$caixaId,
        'tipo' => $tipo,
        'valor' => is_numeric($valor) ? (float)$valor : 0.0,
        'descricao' => $descricao,
      ];
      $code = null;
      $resp = apiRequestCaixa('PUT', $apiBase . '/caixa-movimentos/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Movimento atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar movimento.';
      }
    } else {
      $errorMessage = 'Movimento invalido.';
    }
  } elseif ($action === 'mov_delete') {
    $id = (string)($_POST['mov_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestCaixa('DELETE', $apiBase . '/caixa-movimentos/' . urlencode($id), $token, null, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Movimento removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover movimento.';
      }
    } else {
      $errorMessage = 'Movimento invalido.';
    }
  }
}

$caixaFiltro = (int)($_GET['caixa_id'] ?? 0);

if ($apiBase !== '' && $token !== '') {
  $code = null;
  $respCaixas = apiRequestCaixa('GET', $apiBase . '/caixas', $token, null, $code);
  if ($code >= 200 && $code < 300) {
    $caixas = $respCaixas['data'] ?? $respCaixas;
    if (is_array($caixas) && isset($caixas['data']) && is_array($caixas['data'])) {
      $caixas = $caixas['data'];
    }
    if (!is_array($caixas)) {
      $caixas = [];
    }
    if ($caixaFiltro === 0 && !empty($caixas)) {
      $first = $caixas[0];
      if (is_object($first)) {
        $first = (array)$first;
      }
      $caixaFiltro = (int)($first['id'] ?? 0);
    }
  } else {
    $errorMessage = $respCaixas['message'] ?? 'Nao foi possivel carregar os caixas.';
  }

  if ($caixaFiltro > 0) {
    $codeMov = null;
    $respMov = apiRequestCaixa('GET', $apiBase . '/caixa-movimentos?caixa_id=' . $caixaFiltro, $token, null, $codeMov);
    if ($codeMov >= 200 && $codeMov < 300) {
      $movimentos = $respMov['data'] ?? $respMov;
      if (is_array($movimentos) && isset($movimentos['data']) && is_array($movimentos['data'])) {
        $movimentos = $movimentos['data'];
      }
      if (!is_array($movimentos)) {
        $movimentos = [];
      }
      foreach ($movimentos as $mov) {
        if (is_object($mov)) {
          $mov = (array)$mov;
        }
        if (!is_array($mov)) {
          continue;
        }
        $tipo = strtolower((string)($mov['tipo'] ?? ''));
        $valor = $mov['valor'] ?? 0;
        $valorNum = is_numeric($valor) ? (float)$valor : 0.0;
        if ($tipo === 'entrada') {
          $entradasTotal += $valorNum;
        } elseif ($tipo === 'saida') {
          $saidasTotal += $valorNum;
        }
      }
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
            <h3 class="mb-1">Caixa</h3>
            <p class="text-muted mb-0">Abertura, fechamento e movimentos diarios.</p>
          </div>
          <div class="btn-wrapper">
            <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="modal" data-bs-target="#modalCaixaAbrir">
              <i class="mdi mdi-cash-register"></i> Abrir caixa
            </button>
            <button class="btn btn-primary text-white" type="button" data-bs-toggle="modal" data-bs-target="#modalMovimentoNovo">
              <i class="mdi mdi-plus"></i> Novo movimento
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
            <p class="text-muted mb-1">Entradas</p>
            <h3 class="mb-0 text-success">R$ <?php echo htmlspecialchars(formatMoneyCaixa($entradasTotal), ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Saidas</p>
            <h3 class="mb-0 text-danger">R$ <?php echo htmlspecialchars(formatMoneyCaixa($saidasTotal), ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Saldo</p>
            <h3 class="mb-0">R$ <?php echo htmlspecialchars(formatMoneyCaixa($entradasTotal - $saidasTotal), ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <p class="text-muted mb-1">Caixa selecionado</p>
            <h3 class="mb-0">#<?php echo (int)$caixaFiltro; ?></h3>
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
                <h4 class="mb-1">Caixas</h4>
                <p class="text-muted mb-0">Historico de caixas cadastrados.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalCaixaNova">
                <i class="mdi mdi-plus"></i> Novo caixa
              </button>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Saldo inicial</th>
                    <th>Saldo final</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($caixas)): ?>
                    <tr><td colspan="6">Nenhum caixa encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($caixas as $caixa): ?>
                      <?php
                        if (is_object($caixa)) {
                          $caixa = (array)$caixa;
                        }
                        if (!is_array($caixa)) {
                          continue;
                        }
                        $caixaId = $caixa['id'] ?? '';
                        $usuarioId = $caixa['usuario_id'] ?? '';
                        $saldoInicial = $caixa['saldo_inicial'] ?? '0';
                        $saldoFinal = $caixa['saldo_final'] ?? '0';
                        $criado = formatDateTimeCaixa($caixa['created_at'] ?? '');
                      ?>
                      <tr>
                        <td>#<?php echo htmlspecialchars((string)$caixaId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$usuarioId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>R$ <?php echo htmlspecialchars(formatMoneyCaixa($saldoInicial), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>R$ <?php echo htmlspecialchars(formatMoneyCaixa($saldoFinal), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$criado, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-caixa" type="button" data-bs-toggle="modal" data-bs-target="#modalCaixaEditar"
                            data-id="<?php echo htmlspecialchars((string)$caixaId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-usuario="<?php echo htmlspecialchars((string)$usuarioId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-saldo-inicial="<?php echo htmlspecialchars((string)$saldoInicial, ENT_QUOTES, 'UTF-8'); ?>"
                            data-saldo-final="<?php echo htmlspecialchars((string)$saldoFinal, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-secondary btn-sm me-1 btn-fechar-caixa" type="button" data-bs-toggle="modal" data-bs-target="#modalCaixaFechar"
                            data-id="<?php echo htmlspecialchars((string)$caixaId, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-lock"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-caixa" type="button" data-bs-toggle="modal" data-bs-target="#modalCaixaExcluir"
                            data-id="<?php echo htmlspecialchars((string)$caixaId, ENT_QUOTES, 'UTF-8'); ?>">
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
                <h4 class="mb-1">Movimentos</h4>
                <p class="text-muted mb-0">Entradas e saidas do caixa selecionado.</p>
              </div>
              <form class="d-flex" method="GET" action="">
                <input type="hidden" name="paginas" value="caixa">
                <input type="number" name="caixa_id" class="form-control form-control-sm me-2" placeholder="Caixa ID" value="<?php echo htmlspecialchars((string)$caixaFiltro, ENT_QUOTES, 'UTF-8'); ?>">
                <button class="btn btn-outline-secondary btn-sm" type="submit">Filtrar</button>
              </form>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Descricao</th>
                    <th>Valor</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($movimentos)): ?>
                    <tr><td colspan="6">Nenhum movimento encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($movimentos as $mov): ?>
                      <?php
                        if (is_object($mov)) {
                          $mov = (array)$mov;
                        }
                        if (!is_array($mov)) {
                          continue;
                        }
                        $movId = $mov['id'] ?? '';
                        $tipo = $mov['tipo'] ?? '';
                        $descricao = $mov['descricao'] ?? '';
                        $valor = $mov['valor'] ?? '0';
                        $criado = formatDateTimeCaixa($mov['created_at'] ?? '');
                      ?>
                      <tr>
                        <td>#<?php echo htmlspecialchars((string)$movId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$tipo, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$descricao, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>R$ <?php echo htmlspecialchars(formatMoneyCaixa($valor), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$criado, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-mov" type="button" data-bs-toggle="modal" data-bs-target="#modalMovimentoEditar"
                            data-id="<?php echo htmlspecialchars((string)$movId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-caixa="<?php echo htmlspecialchars((string)($mov['caixa_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            data-tipo="<?php echo htmlspecialchars((string)$tipo, ENT_QUOTES, 'UTF-8'); ?>"
                            data-valor="<?php echo htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); ?>"
                            data-descricao="<?php echo htmlspecialchars((string)$descricao, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-mov" type="button" data-bs-toggle="modal" data-bs-target="#modalMovimentoExcluir"
                            data-id="<?php echo htmlspecialchars((string)$movId, ENT_QUOTES, 'UTF-8'); ?>">
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
  <div class="modal fade" id="modalCaixaNova" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="caixa_create">
          <div class="modal-header">
            <h5 class="modal-title">Novo caixa</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Usuario</label>
              <input type="number" name="usuario_id" class="form-control" min="1" value="<?php echo htmlspecialchars((string)$usuarioIdSessao, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Saldo inicial</label>
              <input type="number" step="0.01" name="saldo_inicial" class="form-control" value="0.00" required>
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

  <div class="modal fade" id="modalCaixaAbrir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="caixa_abrir">
          <div class="modal-header">
            <h5 class="modal-title">Abrir caixa</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Saldo inicial</label>
              <input type="number" step="0.01" name="saldo_inicial" class="form-control" value="0.00" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Abrir</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalCaixaEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formCaixaEditar">
          <input type="hidden" name="action" value="caixa_update">
          <input type="hidden" name="caixa_id" id="editarCaixaId">
          <div class="modal-header">
            <h5 class="modal-title">Editar caixa</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Usuario</label>
              <input type="number" name="usuario_id" id="editarCaixaUsuario" class="form-control" min="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Saldo inicial</label>
              <input type="number" step="0.01" name="saldo_inicial" id="editarCaixaSaldoInicial" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Saldo final</label>
              <input type="number" step="0.01" name="saldo_final" id="editarCaixaSaldoFinal" class="form-control" value="0.00" required>
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

  <div class="modal fade" id="modalCaixaFechar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formCaixaFechar">
          <input type="hidden" name="action" value="caixa_fechar">
          <input type="hidden" name="caixa_id" id="fecharCaixaId">
          <div class="modal-header">
            <h5 class="modal-title">Fechar caixa</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Saldo final</label>
              <input type="number" step="0.01" name="saldo_final" class="form-control" value="0.00" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Fechar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalCaixaExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="caixa_delete">
          <input type="hidden" name="caixa_id" id="excluirCaixaId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir caixa</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao do caixa selecionado?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Excluir</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalMovimentoNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="mov_create">
          <div class="modal-header">
            <h5 class="modal-title">Novo movimento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Caixa</label>
              <input type="number" name="caixa_id" class="form-control" min="1" value="<?php echo htmlspecialchars((string)$caixaFiltro, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Tipo</label>
              <select name="tipo" class="form-select" required>
                <option value="entrada">Entrada</option>
                <option value="saida">Saida</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Valor</label>
              <input type="number" step="0.01" name="valor" class="form-control" value="0.00" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Descricao</label>
              <input type="text" name="descricao" class="form-control" required>
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

  <div class="modal fade" id="modalMovimentoEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formMovimentoEditar">
          <input type="hidden" name="action" value="mov_update">
          <input type="hidden" name="mov_id" id="editarMovId">
          <div class="modal-header">
            <h5 class="modal-title">Editar movimento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Caixa</label>
              <input type="number" name="caixa_id" id="editarMovCaixa" class="form-control" min="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Tipo</label>
              <select name="tipo" id="editarMovTipo" class="form-select" required>
                <option value="entrada">Entrada</option>
                <option value="saida">Saida</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Valor</label>
              <input type="number" step="0.01" name="valor" id="editarMovValor" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Descricao</label>
              <input type="text" name="descricao" id="editarMovDescricao" class="form-control" required>
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

  <div class="modal fade" id="modalMovimentoExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="mov_delete">
          <input type="hidden" name="mov_id" id="excluirMovId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir movimento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao do movimento selecionado?</p>
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
    document.querySelectorAll('.btn-edit-caixa').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarCaixaId').value = this.dataset.id || '';
        document.getElementById('editarCaixaUsuario').value = this.dataset.usuario || '';
        document.getElementById('editarCaixaSaldoInicial').value = this.dataset.saldoInicial || '0.00';
        document.getElementById('editarCaixaSaldoFinal').value = this.dataset.saldoFinal || '0.00';
      });
    });

    document.querySelectorAll('.btn-fechar-caixa').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('fecharCaixaId').value = this.dataset.id || '';
      });
    });

    document.querySelectorAll('.btn-delete-caixa').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirCaixaId').value = this.dataset.id || '';
      });
    });

    document.querySelectorAll('.btn-edit-mov').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarMovId').value = this.dataset.id || '';
        document.getElementById('editarMovCaixa').value = this.dataset.caixa || '';
        document.getElementById('editarMovTipo').value = this.dataset.tipo || 'entrada';
        document.getElementById('editarMovValor').value = this.dataset.valor || '0.00';
        document.getElementById('editarMovDescricao').value = this.dataset.descricao || '';
      });
    });

    document.querySelectorAll('.btn-delete-mov').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirMovId').value = this.dataset.id || '';
      });
    });
  </script>
</div>
