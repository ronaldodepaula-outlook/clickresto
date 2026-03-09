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
$planos = [];

function parseMoney($value) {
  $value = str_replace('.', '', (string)$value);
  $value = str_replace(',', '.', $value);
  return is_numeric($value) ? (float)$value : 0.0;
}

function apiRequest($method, $url, $token, $payload = null, &$httpCode = null) {
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
  if ($action === 'create') {
    $payload = [
      'nome' => trim((string)($_POST['nome'] ?? '')),
      'limite_usuarios' => (int)($_POST['limite_usuarios'] ?? 0),
      'limite_produtos' => (int)($_POST['limite_produtos'] ?? 0),
      'valor' => parseMoney($_POST['valor'] ?? 0),
      'ativo' => isset($_POST['ativo']) ? true : false,
    ];
    $code = null;
    $resp = apiRequest('POST', $apiBase . '/planos', $token, $payload, $code);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Plano criado com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar plano.';
    }
  } elseif ($action === 'update') {
    $id = (string)($_POST['plano_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'nome' => trim((string)($_POST['nome'] ?? '')),
        'limite_usuarios' => (int)($_POST['limite_usuarios'] ?? 0),
        'limite_produtos' => (int)($_POST['limite_produtos'] ?? 0),
        'valor' => parseMoney($_POST['valor'] ?? 0),
        'ativo' => isset($_POST['ativo']) ? true : false,
      ];
      $code = null;
      $resp = apiRequest('PUT', $apiBase . '/planos/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Plano atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar plano.';
      }
    } else {
      $errorMessage = 'Plano invalido.';
    }
  } elseif ($action === 'delete') {
    $id = (string)($_POST['plano_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequest('DELETE', $apiBase . '/planos/' . urlencode($id), $token, null, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Plano removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover plano.';
      }
    } else {
      $errorMessage = 'Plano invalido.';
    }
  } elseif ($action === 'toggle') {
    $id = (string)($_POST['plano_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'nome' => trim((string)($_POST['nome'] ?? '')),
        'limite_usuarios' => (int)($_POST['limite_usuarios'] ?? 0),
        'limite_produtos' => (int)($_POST['limite_produtos'] ?? 0),
        'valor' => parseMoney($_POST['valor'] ?? 0),
        'ativo' => isset($_POST['ativo']) ? false : true,
      ];
      $code = null;
      $resp = apiRequest('PUT', $apiBase . '/planos/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Status do plano atualizado.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar status.';
      }
    } else {
      $errorMessage = 'Plano invalido.';
    }
  }
}

if ($apiBase !== '' && $token !== '') {
  $code = null;
  $resp = apiRequest('GET', $apiBase . '/planos', $token, null, $code);
  if ($code >= 200 && $code < 300) {
    $planos = $resp['data'] ?? $resp;
    if (!is_array($planos)) {
      $planos = [];
    }
  } else {
    $errorMessage = $resp['message'] ?? 'Nao foi possivel carregar os planos.';
  }
}

$totalPlanos = count($planos);
$ativos = 0;
$valorTotal = 0.0;
foreach ($planos as $plano) {
  if (!empty($plano['ativo'])) {
    $ativos++;
  }
  $valorTotal += (float)($plano['valor'] ?? 0);
}
$mediaValor = $totalPlanos > 0 ? ($valorTotal / $totalPlanos) : 0;
?>
<div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                  <div>
                    <h3 class="mb-1">Gestao de planos</h3>
                    <p class="text-muted mb-0">Cadastro, edicao e exclusao com base nos endpoints da API.</p>
                  </div>
                  <div class="btn-wrapper">
                    <button class="btn btn-primary text-white" type="button" data-bs-toggle="modal" data-bs-target="#modalPlanoNovo">
                      <i class="mdi mdi-plus"></i> Novo plano
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
                    <p class="text-muted mb-1">Planos cadastrados</p>
                    <h3 class="mb-0"><?php echo (int)$totalPlanos; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Planos ativos</p>
                    <h3 class="mb-0"><?php echo (int)$ativos; ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Planos inativos</p>
                    <h3 class="mb-0"><?php echo (int)($totalPlanos - $ativos); ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Valor medio</p>
                    <h3 class="mb-0">R$ <?php echo number_format($mediaValor, 2, ',', '.'); ?></h3>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <div class="d-sm-flex justify-content-between align-items-start">
                      <div>
                        <h4 class="card-title card-title-dash">Lista de planos</h4>
                        <p class="card-subtitle card-subtitle-dash">Endpoints: GET /planos, POST /planos, PUT /planos/{id}, DELETE /planos/{id}</p>
                      </div>
                    </div>
                    <div class="table-responsive mt-3">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Plano</th>
                            <th>Valor</th>
                            <th>Recursos</th>
                            <th>Status</th>
                            <th>Acoes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($planos)): ?>
                            <tr><td colspan="5">Nenhum plano encontrado.</td></tr>
                          <?php else: ?>
                            <?php foreach ($planos as $plano): ?>
                              <?php
                                $planoId = $plano['id'] ?? $plano['plano_id'] ?? '';
                                $nome = $plano['nome'] ?? 'Plano';
                                $valor = (float)($plano['valor'] ?? 0);
                                $limUsuarios = (int)($plano['limite_usuarios'] ?? 0);
                                $limProdutos = (int)($plano['limite_produtos'] ?? 0);
                                $ativo = !empty($plano['ativo']);
                              ?>
                              <tr>
                                <td>
                                  <h6><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></h6>
                                </td>
                                <td>R$ <?php echo number_format($valor, 2, ',', '.'); ?></td>
                                <td>
                                  <div class="d-flex flex-wrap gap-1">
                                    <span class="badge badge-opacity-info"><?php echo $limUsuarios; ?> usuarios</span>
                                    <span class="badge badge-opacity-info"><?php echo $limProdutos; ?> produtos</span>
                                  </div>
                                </td>
                                <td>
                                  <?php if ($ativo): ?>
                                    <div class="badge badge-opacity-success">Ativo</div>
                                  <?php else: ?>
                                    <div class="badge badge-opacity-secondary">Inativo</div>
                                  <?php endif; ?>
                                </td>
                                <td>
                                  <button class="btn btn-outline-primary btn-sm me-2 btn-edit-plano"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalPlanoEditar"
                                    data-id="<?php echo htmlspecialchars((string)$planoId, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-nome="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-valor="<?php echo htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-usuarios="<?php echo $limUsuarios; ?>"
                                    data-produtos="<?php echo $limProdutos; ?>"
                                    data-ativo="<?php echo $ativo ? '1' : '0'; ?>">
                                    <i class="mdi mdi-pencil"></i> Editar
                                  </button>
                                  <form class="d-inline" method="POST" action="">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="plano_id" value="<?php echo htmlspecialchars((string)$planoId, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="nome" value="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="valor" value="<?php echo htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="limite_usuarios" value="<?php echo $limUsuarios; ?>">
                                    <input type="hidden" name="limite_produtos" value="<?php echo $limProdutos; ?>">
                                    <?php if ($ativo): ?>
                                      <input type="hidden" name="ativo" value="1">
                                      <button class="btn btn-outline-warning btn-sm me-2" type="submit"><i class="mdi mdi-lock-outline"></i> Desativar</button>
                                    <?php else: ?>
                                      <button class="btn btn-outline-success btn-sm me-2" type="submit"><i class="mdi mdi-check"></i> Ativar</button>
                                    <?php endif; ?>
                                  </form>
                                  <button class="btn btn-outline-danger btn-sm btn-delete-plano"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalPlanoExcluir"
                                    data-id="<?php echo htmlspecialchars((string)$planoId, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-nome="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="mdi mdi-delete"></i> Excluir
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

          <!-- Modal Novo -->
          <div class="modal fade" id="modalPlanoNovo" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="create">
                  <div class="modal-header">
                    <h5 class="modal-title">Novo plano</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Valor</label>
                        <input type="text" name="valor" class="form-control" placeholder="99,90" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Limite usuarios</label>
                        <input type="number" name="limite_usuarios" class="form-control" min="0" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Limite produtos</label>
                        <input type="number" name="limite_produtos" class="form-control" min="0" required>
                      </div>
                      <div class="col-md-12 mb-3">
                        <label class="form-label">Recursos incluidos</label>
                        <textarea class="form-control" name="recursos_display" rows="2" placeholder="Ex: Suporte, API, Relatorios"></textarea>
                      </div>
                      <div class="col-md-12">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="ativo" id="novoAtivo" checked>
                          <label class="form-check-label" for="novoAtivo">Ativo</label>
                        </div>
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

          <!-- Modal Editar -->
          <div class="modal fade" id="modalPlanoEditar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="plano_id" id="editarPlanoId">
                  <div class="modal-header">
                    <h5 class="modal-title">Editar plano</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" id="editarNome" class="form-control" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Valor</label>
                        <input type="text" name="valor" id="editarValor" class="form-control" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Limite usuarios</label>
                        <input type="number" name="limite_usuarios" id="editarUsuarios" class="form-control" min="0" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Limite produtos</label>
                        <input type="number" name="limite_produtos" id="editarProdutos" class="form-control" min="0" required>
                      </div>
                      <div class="col-md-12 mb-3">
                        <label class="form-label">Recursos incluidos</label>
                        <textarea class="form-control" name="recursos_display" rows="2" placeholder="Ex: Suporte, API, Relatorios"></textarea>
                      </div>
                      <div class="col-md-12">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="ativo" id="editarAtivo">
                          <label class="form-check-label" for="editarAtivo">Ativo</label>
                        </div>
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

          <!-- Modal Excluir -->
          <div class="modal fade" id="modalPlanoExcluir" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="plano_id" id="excluirPlanoId">
                  <div class="modal-header">
                    <h5 class="modal-title">Excluir plano</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Confirma a exclusao do plano <strong id="excluirPlanoNome"></strong>?</p>
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
            document.querySelectorAll('.btn-edit-plano').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.getElementById('editarPlanoId').value = this.dataset.id || '';
                document.getElementById('editarNome').value = this.dataset.nome || '';
                document.getElementById('editarValor').value = (this.dataset.valor || '').toString().replace('.', ',');
                document.getElementById('editarUsuarios').value = this.dataset.usuarios || 0;
                document.getElementById('editarProdutos').value = this.dataset.produtos || 0;
                document.getElementById('editarAtivo').checked = this.dataset.ativo === '1';
              });
            });

            document.querySelectorAll('.btn-delete-plano').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.getElementById('excluirPlanoId').value = this.dataset.id || '';
                document.getElementById('excluirPlanoNome').textContent = this.dataset.nome || '';
              });
            });
          </script>
        </div>

