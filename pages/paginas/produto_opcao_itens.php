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
$itens = [];
$opcoes = [];

function formatMoneyOpcaoItem($value) {
  if ($value === null || $value === '') {
    return '0,00';
  }
  if (is_string($value)) {
    $value = str_replace(',', '.', $value);
  }
  $number = is_numeric($value) ? (float)$value : 0.0;
  return number_format($number, 2, ',', '.');
}

function apiRequestOpcaoItens($method, $url, $token, $payload = null, &$httpCode = null) {
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
  if ($action === 'item_create') {
    $payload = [
      'opcao_id' => (int)($_POST['opcao_id'] ?? 0),
      'nome' => trim((string)($_POST['nome'] ?? '')),
      'preco_adicional' => (float)($_POST['preco_adicional'] ?? 0),
    ];
    $code = null;
    $resp = apiRequestOpcaoItens('POST', $apiBase . '/produto-opcao-itens', $token, $payload, $code);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Item criado com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar item.';
    }
  } elseif ($action === 'item_update') {
    $id = (string)($_POST['item_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'opcao_id' => (int)($_POST['opcao_id'] ?? 0),
        'nome' => trim((string)($_POST['nome'] ?? '')),
        'preco_adicional' => (float)($_POST['preco_adicional'] ?? 0),
      ];
      $code = null;
      $resp = apiRequestOpcaoItens('PUT', $apiBase . '/produto-opcao-itens/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Item atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar item.';
      }
    } else {
      $errorMessage = 'Item invalido.';
    }
  } elseif ($action === 'item_delete') {
    $id = (string)($_POST['item_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestOpcaoItens('DELETE', $apiBase . '/produto-opcao-itens/' . urlencode($id), $token, null, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Item removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover item.';
      }
    } else {
      $errorMessage = 'Item invalido.';
    }
  }
}

$opcaoFiltro = (int)($_GET['opcao_id'] ?? 0);

if ($apiBase !== '' && $token !== '') {
  $codeOpcoes = null;
  $respOpcoes = apiRequestOpcaoItens('GET', $apiBase . '/produto-opcoes', $token, null, $codeOpcoes);
  if ($codeOpcoes >= 200 && $codeOpcoes < 300) {
    $opcoes = $respOpcoes['data'] ?? $respOpcoes;
    if (is_array($opcoes) && isset($opcoes['data']) && is_array($opcoes['data'])) {
      $opcoes = $opcoes['data'];
    }
    if (!is_array($opcoes)) {
      $opcoes = [];
    }
    if ($opcaoFiltro === 0 && !empty($opcoes)) {
      $first = $opcoes[0];
      if (is_object($first)) {
        $first = (array)$first;
      }
      $opcaoFiltro = (int)($first['id'] ?? 0);
    }
  }

  $code = null;
  $urlItens = $apiBase . '/produto-opcao-itens';
  if ($opcaoFiltro > 0) {
    $urlItens .= '?opcao_id=' . $opcaoFiltro;
  }
  $respItens = apiRequestOpcaoItens('GET', $urlItens, $token, null, $code);
  if ($code >= 200 && $code < 300) {
    $itens = $respItens['data'] ?? $respItens;
    if (is_array($itens) && isset($itens['data']) && is_array($itens['data'])) {
      $itens = $itens['data'];
    }
    if (!is_array($itens)) {
      $itens = [];
    }
  } else {
    $errorMessage = $respItens['message'] ?? 'Nao foi possivel carregar itens.';
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
            <h3 class="mb-1">Itens de opcoes</h3>
            <p class="text-muted mb-0">Valores e regras de cada opcao.</p>
          </div>
          <div class="btn-wrapper">
            <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="modal" data-bs-target="#modalItemNovo">
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
                <h4 class="mb-1">Itens</h4>
                <p class="text-muted mb-0">Filtre por opcao.</p>
              </div>
              <form class="d-flex" method="GET" action="">
                <input type="hidden" name="paginas" value="produto_opcao_itens">
                <select name="opcao_id" class="form-select form-select-sm me-2">
                  <?php foreach ($opcoes as $opcao): ?>
                    <?php
                      if (is_object($opcao)) {
                        $opcao = (array)$opcao;
                      }
                      if (!is_array($opcao)) {
                        continue;
                      }
                      $opcaoId = $opcao['id'] ?? '';
                      $opcaoNome = $opcao['nome'] ?? '';
                    ?>
                    <option value="<?php echo htmlspecialchars((string)$opcaoId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$opcaoId === (int)$opcaoFiltro) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars((string)$opcaoNome, ENT_QUOTES, 'UTF-8'); ?>
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
                    <th>ID</th>
                    <th>Opcao</th>
                    <th>Item</th>
                    <th>Preco adicional</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($itens)): ?>
                    <tr><td colspan="5">Nenhum item encontrado.</td></tr>
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
                        $opcaoId = $item['opcao_id'] ?? '';
                        $nome = $item['nome'] ?? '';
                        $preco = $item['preco_adicional'] ?? '0';
                      ?>
                      <tr>
                        <td>#<?php echo htmlspecialchars((string)$itemId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$opcaoId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>R$ <?php echo htmlspecialchars(formatMoneyOpcaoItem($preco), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-item" type="button" data-bs-toggle="modal" data-bs-target="#modalItemEditar"
                            data-id="<?php echo htmlspecialchars((string)$itemId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-opcao="<?php echo htmlspecialchars((string)$opcaoId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-nome="<?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?>"
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
  </div>

  <div class="modal fade" id="modalItemNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="item_create">
          <div class="modal-header">
            <h5 class="modal-title">Novo item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Opcao</label>
              <select name="opcao_id" class="form-select" required>
                <?php foreach ($opcoes as $opcao): ?>
                  <?php
                    if (is_object($opcao)) {
                      $opcao = (array)$opcao;
                    }
                    if (!is_array($opcao)) {
                      continue;
                    }
                    $opcaoId = $opcao['id'] ?? '';
                    $opcaoNome = $opcao['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$opcaoId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$opcaoId === (int)$opcaoFiltro) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$opcaoNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Preco adicional</label>
              <input type="number" step="0.01" name="preco_adicional" class="form-control" value="0.00" required>
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
              <label class="form-label">Opcao</label>
              <select name="opcao_id" id="editarItemOpcao" class="form-select" required>
                <?php foreach ($opcoes as $opcao): ?>
                  <?php
                    if (is_object($opcao)) {
                      $opcao = (array)$opcao;
                    }
                    if (!is_array($opcao)) {
                      continue;
                    }
                    $opcaoId = $opcao['id'] ?? '';
                    $opcaoNome = $opcao['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$opcaoId, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)$opcaoNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" id="editarItemNome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Preco adicional</label>
              <input type="number" step="0.01" name="preco_adicional" id="editarItemPreco" class="form-control" required>
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
    document.querySelectorAll('.btn-edit-item').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarItemId').value = this.dataset.id || '';
        document.getElementById('editarItemOpcao').value = this.dataset.opcao || '';
        document.getElementById('editarItemNome').value = this.dataset.nome || '';
        document.getElementById('editarItemPreco').value = this.dataset.preco || '0.00';
      });
    });

    document.querySelectorAll('.btn-delete-item').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirItemId').value = this.dataset.id || '';
      });
    });
  </script>
</div>
