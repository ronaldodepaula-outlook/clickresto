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
$mesas = [];
$totalMesas = 0;
$mesasLivres = 0;
$mesasOcupadas = 0;
$mesasReservadas = 0;

function getFirstValueMesa($data, $keys, $default = null) {
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

function formatDateTimeMesa($value) {
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

function mesaStatusBadge($status) {
  $statusLower = strtolower((string)$status);
  if ($statusLower === 'livre') {
    return ['Livre', 'badge-opacity-success'];
  }
  if ($statusLower === 'ocupada') {
    return ['Ocupada', 'badge-opacity-warning'];
  }
  if ($statusLower === 'reservada') {
    return ['Reservada', 'badge-opacity-info'];
  }
  return [ucfirst((string)$statusLower), 'badge-opacity-secondary'];
}

function apiRequestMesas($method, $url, $token, $payload = null, &$httpCode = null, $empresaId = '') {
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
  if ($action === 'create') {
    $payload = [
      'numero' => (int)($_POST['numero'] ?? 0),
      'status' => trim((string)($_POST['status'] ?? 'livre')),
    ];
    $code = null;
    $resp = apiRequestMesas('POST', $apiBase . '/mesas', $token, $payload, $code, $empresaId);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Mesa criada com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar mesa.';
    }
  } elseif ($action === 'update') {
    $id = (string)($_POST['mesa_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'numero' => (int)($_POST['numero'] ?? 0),
        'status' => trim((string)($_POST['status'] ?? 'livre')),
      ];
      $code = null;
      $resp = apiRequestMesas('PUT', $apiBase . '/mesas/' . urlencode($id), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Mesa atualizada com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar mesa.';
      }
    } else {
      $errorMessage = 'Mesa invalida.';
    }
  } elseif ($action === 'patch_status') {
    $id = (string)($_POST['mesa_id'] ?? '');
    $status = trim((string)($_POST['status'] ?? ''));
    if ($id !== '' && $status !== '') {
      $payload = ['status' => $status];
      $code = null;
      $resp = apiRequestMesas('PATCH', $apiBase . '/mesas/' . urlencode($id), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Status da mesa atualizado.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar status.';
      }
    } else {
      $errorMessage = 'Mesa ou status invalidos.';
    }
  } elseif ($action === 'delete') {
    $id = (string)($_POST['mesa_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestMesas('DELETE', $apiBase . '/mesas/' . urlencode($id), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Mesa removida com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover mesa.';
      }
    } else {
      $errorMessage = 'Mesa invalida.';
    }
  }
}

if ($apiBase !== '' && $token !== '') {
  $code = null;
  $currentPage = max(1, (int)($_GET['page'] ?? 1));
  $resp = apiRequestMesas('GET', $apiBase . '/mesas?per_page=15&page=' . $currentPage, $token, null, $code, $empresaId);
  if ($code >= 200 && $code < 300) {
    $mesas = $resp['data'] ?? $resp;
    if (is_array($mesas) && isset($mesas['data']) && is_array($mesas['data'])) {
      $mesas = $mesas['data'];
    }
    if (!is_array($mesas)) {
      $mesas = [];
    }
    $totalMesas = (int)($resp['total'] ?? count($mesas));
    foreach ($mesas as $mesa) {
      if (is_object($mesa)) {
        $mesa = (array)$mesa;
      }
      if (!is_array($mesa)) {
        continue;
      }
      $status = strtolower((string)(getFirstValueMesa($mesa, ['status'], '')));
      if ($status === 'livre') {
        $mesasLivres++;
      } elseif ($status === 'ocupada') {
        $mesasOcupadas++;
      } elseif ($status === 'reservada') {
        $mesasReservadas++;
      }
    }
  } else {
    $errorMessage = $resp['message'] ?? 'Nao foi possivel carregar as mesas.';
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
                    <h3 class="mb-1">Mesas</h3>
                    <p class="text-muted mb-0">Visualizacao rapida do salao e disponibilidade.</p>
                  </div>
                  <div class="btn-wrapper">
                    <button class="btn btn-outline-secondary me-2"><i class="mdi mdi-filter"></i> Filtros</button>
                    <button class="btn btn-primary text-white" type="button" data-bs-toggle="modal" data-bs-target="#modalMesaNova">
                      <i class="mdi mdi-plus"></i> Nova mesa
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
                    <p class="text-muted mb-1">Mesas ocupadas</p>
                    <h3 class="mb-0"><?php echo (int)$mesasOcupadas; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Mesas livres</p>
                    <h3 class="mb-0 text-success"><?php echo (int)$mesasLivres; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Reservadas</p>
                    <h3 class="mb-0 text-warning"><?php echo (int)$mesasReservadas; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Total de mesas</p>
                    <h3 class="mb-0"><?php echo (int)$totalMesas; ?></h3>
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
                            <th>Mesa</th>
                            <th>Status</th>
                            <th>Criado</th>
                            <th>Atualizado</th>
                            <th>Acoes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($mesas)): ?>
                            <tr><td colspan="5">Nenhuma mesa encontrada.</td></tr>
                          <?php else: ?>
                            <?php foreach ($mesas as $mesa): ?>
                              <?php
                                if (is_object($mesa)) {
                                  $mesa = (array)$mesa;
                                }
                                if (!is_array($mesa)) {
                                  continue;
                                }
                                $mesaId = getFirstValueMesa($mesa, ['id', 'mesa_id'], '');
                                $numero = getFirstValueMesa($mesa, ['numero'], '');
                                $status = getFirstValueMesa($mesa, ['status'], '');
                                $criacao = formatDateTimeMesa(getFirstValueMesa($mesa, ['created_at'], ''));
                                $atualizacao = formatDateTimeMesa(getFirstValueMesa($mesa, ['updated_at'], ''));
                                [$statusLabel, $badgeClass] = mesaStatusBadge($status);
                                $statusLower = strtolower((string)$status);
                                $nextStatus = $statusLower === 'livre' ? 'ocupada' : 'livre';
                                $canToggle = in_array($statusLower, ['livre', 'ocupada'], true);
                              ?>
                              <tr>
                                <td>Mesa <?php echo htmlspecialchars((string)$numero, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><div class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars((string)$statusLabel, ENT_QUOTES, 'UTF-8'); ?></div></td>
                                <td><?php echo htmlspecialchars((string)$criacao, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string)$atualizacao, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                  <button class="btn btn-outline-primary btn-sm me-1 btn-edit-mesa"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalMesaEditar"
                                    data-id="<?php echo htmlspecialchars((string)$mesaId, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-numero="<?php echo htmlspecialchars((string)$numero, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-status="<?php echo htmlspecialchars((string)$status, ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="mdi mdi-pencil"></i>
                                  </button>
                                  <?php if ($canToggle): ?>
                                    <form class="d-inline" method="POST" action="">
                                      <input type="hidden" name="action" value="patch_status">
                                      <input type="hidden" name="mesa_id" value="<?php echo htmlspecialchars((string)$mesaId, ENT_QUOTES, 'UTF-8'); ?>">
                                      <input type="hidden" name="status" value="<?php echo htmlspecialchars((string)$nextStatus, ENT_QUOTES, 'UTF-8'); ?>">
                                      <button class="btn btn-outline-secondary btn-sm me-1" type="submit">
                                        <i class="mdi mdi-refresh"></i>
                                      </button>
                                    </form>
                                  <?php endif; ?>
                                  <button class="btn btn-outline-danger btn-sm btn-delete-mesa"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalMesaExcluir"
                                    data-id="<?php echo htmlspecialchars((string)$mesaId, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-numero="<?php echo htmlspecialchars((string)$numero, ENT_QUOTES, 'UTF-8'); ?>">
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

          <!-- Modal Nova Mesa -->
          <div class="modal fade" id="modalMesaNova" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="create">
                  <div class="modal-header">
                    <h5 class="modal-title">Nova mesa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Numero</label>
                      <input type="number" name="numero" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Status</label>
                      <select name="status" class="form-select" required>
                        <option value="livre">Livre</option>
                        <option value="ocupada">Ocupada</option>
                        <option value="reservada">Reservada</option>
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

          <!-- Modal Editar Mesa -->
          <div class="modal fade" id="modalMesaEditar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="" id="formMesaEditar">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="mesa_id" id="editarMesaId">
                  <div class="modal-header">
                    <h5 class="modal-title">Editar mesa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Numero</label>
                      <input type="number" name="numero" id="editarMesaNumero" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Status</label>
                      <select name="status" id="editarMesaStatus" class="form-select" required>
                        <option value="livre">Livre</option>
                        <option value="ocupada">Ocupada</option>
                        <option value="reservada">Reservada</option>
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

          <!-- Modal Excluir Mesa -->
          <div class="modal fade" id="modalMesaExcluir" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="mesa_id" id="excluirMesaId">
                  <div class="modal-header">
                    <h5 class="modal-title">Excluir mesa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Confirma a exclusao da mesa <strong id="excluirMesaNumero"></strong>?</p>
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
            document.querySelectorAll('.btn-edit-mesa').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.getElementById('editarMesaId').value = this.dataset.id || '';
                document.getElementById('editarMesaNumero').value = this.dataset.numero || '';
                document.getElementById('editarMesaStatus').value = this.dataset.status || 'livre';
              });
            });

            document.querySelectorAll('.btn-delete-mesa').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.getElementById('excluirMesaId').value = this.dataset.id || '';
                document.getElementById('excluirMesaNumero').textContent = this.dataset.numero || '';
              });
            });
          </script>
        </div>
