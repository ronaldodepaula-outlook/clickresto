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
$clientes = [];
$enderecos = [];

function formatDateTimeCliente($value) {
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

function apiRequestClientes($method, $url, $token, $payload = null, &$httpCode = null) {
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
  if ($action === 'cliente_create') {
    $payload = [
      'nome' => trim((string)($_POST['nome'] ?? '')),
      'telefone' => trim((string)($_POST['telefone'] ?? '')),
      'email' => trim((string)($_POST['email'] ?? '')),
    ];
    $code = null;
    $resp = apiRequestClientes('POST', $apiBase . '/clientes', $token, $payload, $code);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Cliente criado com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar cliente.';
    }
  } elseif ($action === 'cliente_update') {
    $id = (string)($_POST['cliente_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'nome' => trim((string)($_POST['nome'] ?? '')),
        'telefone' => trim((string)($_POST['telefone'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
      ];
      $code = null;
      $resp = apiRequestClientes('PUT', $apiBase . '/clientes/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Cliente atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar cliente.';
      }
    } else {
      $errorMessage = 'Cliente invalido.';
    }
  } elseif ($action === 'cliente_delete') {
    $id = (string)($_POST['cliente_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestClientes('DELETE', $apiBase . '/clientes/' . urlencode($id), $token, null, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Cliente removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover cliente.';
      }
    } else {
      $errorMessage = 'Cliente invalido.';
    }
  } elseif ($action === 'endereco_create') {
    $payload = [
      'cliente_id' => (int)($_POST['cliente_id'] ?? 0),
      'endereco' => trim((string)($_POST['endereco'] ?? '')),
      'numero' => trim((string)($_POST['numero'] ?? '')),
      'bairro' => trim((string)($_POST['bairro'] ?? '')),
      'cidade' => trim((string)($_POST['cidade'] ?? '')),
      'referencia' => trim((string)($_POST['referencia'] ?? '')),
    ];
    $code = null;
    $resp = apiRequestClientes('POST', $apiBase . '/cliente-enderecos', $token, $payload, $code);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Endereco criado com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar endereco.';
    }
  } elseif ($action === 'endereco_update') {
    $id = (string)($_POST['endereco_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'cliente_id' => (int)($_POST['cliente_id'] ?? 0),
        'endereco' => trim((string)($_POST['endereco'] ?? '')),
        'numero' => trim((string)($_POST['numero'] ?? '')),
        'bairro' => trim((string)($_POST['bairro'] ?? '')),
        'cidade' => trim((string)($_POST['cidade'] ?? '')),
        'referencia' => trim((string)($_POST['referencia'] ?? '')),
      ];
      $code = null;
      $resp = apiRequestClientes('PUT', $apiBase . '/cliente-enderecos/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Endereco atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar endereco.';
      }
    } else {
      $errorMessage = 'Endereco invalido.';
    }
  } elseif ($action === 'endereco_delete') {
    $id = (string)($_POST['endereco_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestClientes('DELETE', $apiBase . '/cliente-enderecos/' . urlencode($id), $token, null, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Endereco removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover endereco.';
      }
    } else {
      $errorMessage = 'Endereco invalido.';
    }
  }
}

$clienteFiltro = (int)($_GET['cliente_id'] ?? 0);

if ($apiBase !== '' && $token !== '') {
  $code = null;
  $respClientes = apiRequestClientes('GET', $apiBase . '/clientes', $token, null, $code);
  if ($code >= 200 && $code < 300) {
    $clientes = $respClientes['data'] ?? $respClientes;
    if (is_array($clientes) && isset($clientes['data']) && is_array($clientes['data'])) {
      $clientes = $clientes['data'];
    }
    if (!is_array($clientes)) {
      $clientes = [];
    }
    if ($clienteFiltro === 0 && !empty($clientes)) {
      $first = $clientes[0];
      if (is_object($first)) {
        $first = (array)$first;
      }
      $clienteFiltro = (int)($first['id'] ?? 0);
    }
  } else {
    $errorMessage = $respClientes['message'] ?? 'Nao foi possivel carregar os clientes.';
  }

  if ($clienteFiltro > 0) {
    $codeEnd = null;
    $respEnd = apiRequestClientes('GET', $apiBase . '/cliente-enderecos?cliente_id=' . $clienteFiltro, $token, null, $codeEnd);
    if ($codeEnd >= 200 && $codeEnd < 300) {
      $enderecos = $respEnd['data'] ?? $respEnd;
      if (is_array($enderecos) && isset($enderecos['data']) && is_array($enderecos['data'])) {
        $enderecos = $enderecos['data'];
      }
      if (!is_array($enderecos)) {
        $enderecos = [];
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
            <h3 class="mb-1">Clientes</h3>
            <p class="text-muted mb-0">Cadastro e enderecos vinculados.</p>
          </div>
          <div class="btn-wrapper">
            <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="modal" data-bs-target="#modalEnderecoNovo">
              <i class="mdi mdi-map-marker"></i> Novo endereco
            </button>
            <button class="btn btn-primary text-white" type="button" data-bs-toggle="modal" data-bs-target="#modalClienteNovo">
              <i class="mdi mdi-plus"></i> Novo cliente
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
                <h4 class="mb-1">Clientes</h4>
                <p class="text-muted mb-0">Lista de clientes cadastrados.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalClienteNovo">
                <i class="mdi mdi-plus"></i> Novo cliente
              </button>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>Cliente</th>
                    <th>Telefone</th>
                    <th>Email</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($clientes)): ?>
                    <tr><td colspan="5">Nenhum cliente encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($clientes as $cliente): ?>
                      <?php
                        if (is_object($cliente)) {
                          $cliente = (array)$cliente;
                        }
                        if (!is_array($cliente)) {
                          continue;
                        }
                        $clienteId = $cliente['id'] ?? '';
                        $nome = $cliente['nome'] ?? '';
                        $telefone = $cliente['telefone'] ?? '';
                        $email = $cliente['email'] ?? '';
                        $criado = formatDateTimeCliente($cliente['created_at'] ?? '');
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$telefone, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$criado, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-cliente" type="button" data-bs-toggle="modal" data-bs-target="#modalClienteEditar"
                            data-id="<?php echo htmlspecialchars((string)$clienteId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-nome="<?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?>"
                            data-telefone="<?php echo htmlspecialchars((string)$telefone, ENT_QUOTES, 'UTF-8'); ?>"
                            data-email="<?php echo htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-cliente" type="button" data-bs-toggle="modal" data-bs-target="#modalClienteExcluir"
                            data-id="<?php echo htmlspecialchars((string)$clienteId, ENT_QUOTES, 'UTF-8'); ?>">
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
                <h4 class="mb-1">Enderecos</h4>
                <p class="text-muted mb-0">Enderecos do cliente selecionado.</p>
              </div>
              <form class="d-flex" method="GET" action="">
                <input type="hidden" name="paginas" value="clientes">
                <select name="cliente_id" class="form-select form-select-sm me-2">
                  <?php foreach ($clientes as $cliente): ?>
                    <?php
                      if (is_object($cliente)) {
                        $cliente = (array)$cliente;
                      }
                      if (!is_array($cliente)) {
                        continue;
                      }
                      $clienteId = $cliente['id'] ?? '';
                      $clienteNome = $cliente['nome'] ?? '';
                    ?>
                    <option value="<?php echo htmlspecialchars((string)$clienteId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$clienteId === (int)$clienteFiltro) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars((string)$clienteNome, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button class="btn btn-outline-secondary btn-sm" type="submit">Filtrar</button>
              </form>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>Endereco</th>
                    <th>Numero</th>
                    <th>Bairro</th>
                    <th>Cidade</th>
                    <th>Referencia</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($enderecos)): ?>
                    <tr><td colspan="6">Nenhum endereco encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($enderecos as $endereco): ?>
                      <?php
                        if (is_object($endereco)) {
                          $endereco = (array)$endereco;
                        }
                        if (!is_array($endereco)) {
                          continue;
                        }
                        $endId = $endereco['id'] ?? '';
                        $clienteId = $endereco['cliente_id'] ?? '';
                        $rua = $endereco['endereco'] ?? '';
                        $numero = $endereco['numero'] ?? '';
                        $bairro = $endereco['bairro'] ?? '';
                        $cidade = $endereco['cidade'] ?? '';
                        $referencia = $endereco['referencia'] ?? '';
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars((string)$rua, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$numero, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$bairro, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$cidade, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$referencia, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-endereco" type="button" data-bs-toggle="modal" data-bs-target="#modalEnderecoEditar"
                            data-id="<?php echo htmlspecialchars((string)$endId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-cliente="<?php echo htmlspecialchars((string)$clienteId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-endereco="<?php echo htmlspecialchars((string)$rua, ENT_QUOTES, 'UTF-8'); ?>"
                            data-numero="<?php echo htmlspecialchars((string)$numero, ENT_QUOTES, 'UTF-8'); ?>"
                            data-bairro="<?php echo htmlspecialchars((string)$bairro, ENT_QUOTES, 'UTF-8'); ?>"
                            data-cidade="<?php echo htmlspecialchars((string)$cidade, ENT_QUOTES, 'UTF-8'); ?>"
                            data-referencia="<?php echo htmlspecialchars((string)$referencia, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-endereco" type="button" data-bs-toggle="modal" data-bs-target="#modalEnderecoExcluir"
                            data-id="<?php echo htmlspecialchars((string)$endId, ENT_QUOTES, 'UTF-8'); ?>">
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

  <!-- Modais Cliente -->
  <div class="modal fade" id="modalClienteNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="cliente_create">
          <div class="modal-header">
            <h5 class="modal-title">Novo cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Telefone</label>
              <input type="text" name="telefone" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control">
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

  <div class="modal fade" id="modalClienteEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formClienteEditar">
          <input type="hidden" name="action" value="cliente_update">
          <input type="hidden" name="cliente_id" id="editarClienteId">
          <div class="modal-header">
            <h5 class="modal-title">Editar cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" id="editarClienteNome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Telefone</label>
              <input type="text" name="telefone" id="editarClienteTelefone" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" id="editarClienteEmail" class="form-control">
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

  <div class="modal fade" id="modalClienteExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="cliente_delete">
          <input type="hidden" name="cliente_id" id="excluirClienteId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao do cliente selecionado?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Excluir</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modais Endereco -->
  <div class="modal fade" id="modalEnderecoNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="endereco_create">
          <div class="modal-header">
            <h5 class="modal-title">Novo endereco</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Cliente</label>
              <select name="cliente_id" class="form-select" required>
                <?php foreach ($clientes as $cliente): ?>
                  <?php
                    if (is_object($cliente)) {
                      $cliente = (array)$cliente;
                    }
                    if (!is_array($cliente)) {
                      continue;
                    }
                    $clienteId = $cliente['id'] ?? '';
                    $clienteNome = $cliente['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$clienteId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$clienteId === (int)$clienteFiltro) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$clienteNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Endereco</label>
              <input type="text" name="endereco" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Numero</label>
              <input type="text" name="numero" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Bairro</label>
              <input type="text" name="bairro" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Cidade</label>
              <input type="text" name="cidade" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Referencia</label>
              <input type="text" name="referencia" class="form-control">
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

  <div class="modal fade" id="modalEnderecoEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formEnderecoEditar">
          <input type="hidden" name="action" value="endereco_update">
          <input type="hidden" name="endereco_id" id="editarEnderecoId">
          <div class="modal-header">
            <h5 class="modal-title">Editar endereco</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Cliente</label>
              <select name="cliente_id" id="editarEnderecoCliente" class="form-select" required>
                <?php foreach ($clientes as $cliente): ?>
                  <?php
                    if (is_object($cliente)) {
                      $cliente = (array)$cliente;
                    }
                    if (!is_array($cliente)) {
                      continue;
                    }
                    $clienteId = $cliente['id'] ?? '';
                    $clienteNome = $cliente['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$clienteId, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)$clienteNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Endereco</label>
              <input type="text" name="endereco" id="editarEnderecoRua" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Numero</label>
              <input type="text" name="numero" id="editarEnderecoNumero" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Bairro</label>
              <input type="text" name="bairro" id="editarEnderecoBairro" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Cidade</label>
              <input type="text" name="cidade" id="editarEnderecoCidade" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Referencia</label>
              <input type="text" name="referencia" id="editarEnderecoReferencia" class="form-control">
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

  <div class="modal fade" id="modalEnderecoExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="endereco_delete">
          <input type="hidden" name="endereco_id" id="excluirEnderecoId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir endereco</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao do endereco selecionado?</p>
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
    document.querySelectorAll('.btn-edit-cliente').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarClienteId').value = this.dataset.id || '';
        document.getElementById('editarClienteNome').value = this.dataset.nome || '';
        document.getElementById('editarClienteTelefone').value = this.dataset.telefone || '';
        document.getElementById('editarClienteEmail').value = this.dataset.email || '';
      });
    });

    document.querySelectorAll('.btn-delete-cliente').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirClienteId').value = this.dataset.id || '';
      });
    });

    document.querySelectorAll('.btn-edit-endereco').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarEnderecoId').value = this.dataset.id || '';
        document.getElementById('editarEnderecoCliente').value = this.dataset.cliente || '';
        document.getElementById('editarEnderecoRua').value = this.dataset.endereco || '';
        document.getElementById('editarEnderecoNumero').value = this.dataset.numero || '';
        document.getElementById('editarEnderecoBairro').value = this.dataset.bairro || '';
        document.getElementById('editarEnderecoCidade').value = this.dataset.cidade || '';
        document.getElementById('editarEnderecoReferencia').value = this.dataset.referencia || '';
      });
    });

    document.querySelectorAll('.btn-delete-endereco').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirEnderecoId').value = this.dataset.id || '';
      });
    });
  </script>
</div>
