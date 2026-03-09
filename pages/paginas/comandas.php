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
$comandas = [];
$totalComandas = 0;
$comandasAbertas = 0;
$comandasFechadas = 0;
$statusSet = [];
$nextComandaNumero = 'C001';

function getFirstValueComanda($data, $keys, $default = null) {
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

function formatDateTimeComanda($value) {
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

function extractComandaSequence($numero) {
  if (!is_string($numero)) {
    return null;
  }
  if (preg_match('/(\d+)/', $numero, $matches)) {
    return (int)$matches[1];
  }
  return null;
}

function comandaStatusBadge($status) {
  $statusLower = strtolower((string)$status);
  if ($statusLower === 'aberta') {
    return ['Aberta', 'badge-opacity-success'];
  }
  if ($statusLower === 'fechada') {
    return ['Fechada', 'badge-opacity-secondary'];
  }
  return [ucfirst((string)$statusLower), 'badge-opacity-warning'];
}

function apiRequestComandas($method, $url, $token, $payload = null, &$httpCode = null, $empresaId = '') {
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
      'numero' => trim((string)($_POST['numero'] ?? '')),
      'status' => trim((string)($_POST['status'] ?? 'aberta')),
    ];
    $code = null;
    $resp = apiRequestComandas('POST', $apiBase . '/comandas', $token, $payload, $code, $empresaId);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Comanda criada com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar comanda.';
    }
  } elseif ($action === 'update') {
    $id = (string)($_POST['comanda_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'numero' => trim((string)($_POST['numero'] ?? '')),
        'status' => trim((string)($_POST['status'] ?? 'aberta')),
      ];
      $code = null;
      $resp = apiRequestComandas('PUT', $apiBase . '/comandas/' . urlencode($id), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Comanda atualizada com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar comanda.';
      }
    } else {
      $errorMessage = 'Comanda invalida.';
    }
  } elseif ($action === 'patch_status') {
    $id = (string)($_POST['comanda_id'] ?? '');
    $status = trim((string)($_POST['status'] ?? ''));
    if ($id !== '' && $status !== '') {
      $payload = ['status' => $status];
      $code = null;
      $resp = apiRequestComandas('PATCH', $apiBase . '/comandas/' . urlencode($id), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Status da comanda atualizado.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar status.';
      }
    } else {
      $errorMessage = 'Comanda ou status invalidos.';
    }
  } elseif ($action === 'delete') {
    $id = (string)($_POST['comanda_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestComandas('DELETE', $apiBase . '/comandas/' . urlencode($id), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Comanda removida com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover comanda.';
      }
    } else {
      $errorMessage = 'Comanda invalida.';
    }
  }
}

if ($apiBase !== '' && $token !== '') {
  $code = null;
  $currentPage = max(1, (int)($_GET['page'] ?? 1));
  $resp = apiRequestComandas('GET', $apiBase . '/comandas?per_page=15&page=' . $currentPage, $token, null, $code, $empresaId);
  if ($code >= 200 && $code < 300) {
    $comandas = $resp['data'] ?? $resp;
    if (is_array($comandas) && isset($comandas['data']) && is_array($comandas['data'])) {
      $comandas = $comandas['data'];
    }
    if (!is_array($comandas)) {
      $comandas = [];
    }
    $totalComandas = (int)($resp['total'] ?? count($comandas));
    $todayLocal = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d');
    $maxSeqToday = 0;
    foreach ($comandas as $comanda) {
      if (is_object($comanda)) {
        $comanda = (array)$comanda;
      }
      if (!is_array($comanda)) {
        continue;
      }
      $status = strtolower((string)(getFirstValueComanda($comanda, ['status'], '')));
      if ($status === 'aberta') {
        $comandasAbertas++;
      } elseif ($status === 'fechada') {
        $comandasFechadas++;
      }
      if ($status !== '') {
        $statusSet[$status] = true;
      }
      $createdAt = getFirstValueComanda($comanda, ['created_at'], '');
      if ($createdAt !== '') {
        try {
          $createdDate = new DateTime($createdAt);
          $createdDate->setTimezone(new DateTimeZone('America/Sao_Paulo'));
          if ($createdDate->format('Y-m-d') === $todayLocal) {
            $seq = extractComandaSequence((string)getFirstValueComanda($comanda, ['numero'], ''));
            if ($seq !== null && $seq > $maxSeqToday) {
              $maxSeqToday = $seq;
            }
          }
        } catch (Exception $e) {
          // ignore parsing errors
        }
      }
    }
    $nextSeq = $maxSeqToday + 1;
    $nextComandaNumero = 'C' . str_pad((string)$nextSeq, 3, '0', STR_PAD_LEFT);
  } else {
    $errorMessage = $resp['message'] ?? 'Nao foi possivel carregar as comandas.';
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
                    <h3 class="mb-1">Comandas</h3>
                    <p class="text-muted mb-0">Controle de consumo por mesa e atendimento.</p>
                  </div>
                  <div class="btn-wrapper">
                    <button class="btn btn-outline-secondary me-2"><i class="mdi mdi-filter"></i> Filtros</button>
                    <button class="btn btn-primary text-white" type="button" data-bs-toggle="modal" data-bs-target="#modalComandaNova">
                      <i class="mdi mdi-plus"></i> Abrir comanda
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
                    <p class="text-muted mb-1">Comandas abertas</p>
                    <h3 class="mb-0"><?php echo (int)$comandasAbertas; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Comandas fechadas</p>
                    <h3 class="mb-0 text-success"><?php echo (int)$comandasFechadas; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Total de comandas</p>
                    <h3 class="mb-0"><?php echo (int)$totalComandas; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Status diferentes</p>
                    <h3 class="mb-0"><?php echo (int)count($statusSet); ?></h3>
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
                            <th>Comanda</th>
                            <th>Status</th>
                            <th>Criado</th>
                            <th>Atualizado</th>
                            <th>Acoes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($comandas)): ?>
                            <tr><td colspan="5">Nenhuma comanda encontrada.</td></tr>
                          <?php else: ?>
                            <?php foreach ($comandas as $comanda): ?>
                              <?php
                                if (is_object($comanda)) {
                                  $comanda = (array)$comanda;
                                }
                                if (!is_array($comanda)) {
                                  continue;
                                }
                                $comandaId = getFirstValueComanda($comanda, ['id', 'comanda_id'], '');
                                $numero = getFirstValueComanda($comanda, ['numero'], '');
                                $status = getFirstValueComanda($comanda, ['status'], '');
                                $criacao = formatDateTimeComanda(getFirstValueComanda($comanda, ['created_at'], ''));
                                $atualizacao = formatDateTimeComanda(getFirstValueComanda($comanda, ['updated_at'], ''));
                                [$statusLabel, $badgeClass] = comandaStatusBadge($status);
                                $statusLower = strtolower((string)$status);
                                $nextStatus = $statusLower === 'aberta' ? 'fechada' : 'aberta';
                                $canToggle = in_array($statusLower, ['aberta', 'fechada'], true);
                              ?>
                              <tr>
                                <td><?php echo htmlspecialchars((string)$numero, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><div class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars((string)$statusLabel, ENT_QUOTES, 'UTF-8'); ?></div></td>
                                <td><?php echo htmlspecialchars((string)$criacao, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string)$atualizacao, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                  <button class="btn btn-outline-primary btn-sm me-1 btn-edit-comanda"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalComandaEditar"
                                    data-id="<?php echo htmlspecialchars((string)$comandaId, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-numero="<?php echo htmlspecialchars((string)$numero, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-status="<?php echo htmlspecialchars((string)$status, ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="mdi mdi-pencil"></i>
                                  </button>
                                  <?php if ($canToggle): ?>
                                    <form class="d-inline" method="POST" action="">
                                      <input type="hidden" name="action" value="patch_status">
                                      <input type="hidden" name="comanda_id" value="<?php echo htmlspecialchars((string)$comandaId, ENT_QUOTES, 'UTF-8'); ?>">
                                      <input type="hidden" name="status" value="<?php echo htmlspecialchars((string)$nextStatus, ENT_QUOTES, 'UTF-8'); ?>">
                                      <button class="btn btn-outline-secondary btn-sm me-1" type="submit">
                                        <i class="mdi mdi-refresh"></i>
                                      </button>
                                    </form>
                                  <?php endif; ?>
                                  <button class="btn btn-outline-danger btn-sm btn-delete-comanda"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalComandaExcluir"
                                    data-id="<?php echo htmlspecialchars((string)$comandaId, ENT_QUOTES, 'UTF-8'); ?>"
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

          <!-- Modal Nova Comanda -->
          <div class="modal fade" id="modalComandaNova" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="create">
                  <div class="modal-header">
                    <h5 class="modal-title">Abrir comanda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Numero</label>
                      <input type="text" name="numero" class="form-control" value="<?php echo htmlspecialchars((string)$nextComandaNumero, ENT_QUOTES, 'UTF-8'); ?>" readonly required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Status</label>
                      <select name="status" class="form-select" required>
                        <option value="aberta">Aberta</option>
                        <option value="fechada">Fechada</option>
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

          <!-- Modal Editar Comanda -->
          <div class="modal fade" id="modalComandaEditar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="" id="formComandaEditar">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="comanda_id" id="editarComandaId">
                  <div class="modal-header">
                    <h5 class="modal-title">Editar comanda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Numero</label>
                      <input type="text" name="numero" id="editarComandaNumero" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Status</label>
                      <select name="status" id="editarComandaStatus" class="form-select" required>
                        <option value="aberta">Aberta</option>
                        <option value="fechada">Fechada</option>
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

          <!-- Modal Excluir Comanda -->
          <div class="modal fade" id="modalComandaExcluir" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="comanda_id" id="excluirComandaId">
                  <div class="modal-header">
                    <h5 class="modal-title">Excluir comanda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Confirma a exclusao da comanda <strong id="excluirComandaNumero"></strong>?</p>
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
            document.querySelectorAll('.btn-edit-comanda').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.getElementById('editarComandaId').value = this.dataset.id || '';
                document.getElementById('editarComandaNumero').value = this.dataset.numero || '';
                document.getElementById('editarComandaStatus').value = this.dataset.status || 'aberta';
              });
            });

            document.querySelectorAll('.btn-delete-comanda').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.getElementById('excluirComandaId').value = this.dataset.id || '';
                document.getElementById('excluirComandaNumero').textContent = this.dataset.numero || '';
              });
            });
          </script>
        </div>
