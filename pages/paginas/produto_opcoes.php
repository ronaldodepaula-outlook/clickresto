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
$opcoes = [];
$produtos = [];

function apiRequestProdutoOpcoes($method, $url, $token, $payload = null, &$httpCode = null) {
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
  if ($action === 'opcao_create') {
    $payload = [
      'produto_id' => (int)($_POST['produto_id'] ?? 0),
      'nome' => trim((string)($_POST['nome'] ?? '')),
    ];
    $code = null;
    $resp = apiRequestProdutoOpcoes('POST', $apiBase . '/produto-opcoes', $token, $payload, $code);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Opcao criada com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar opcao.';
    }
  } elseif ($action === 'opcao_update') {
    $id = (string)($_POST['opcao_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'produto_id' => (int)($_POST['produto_id'] ?? 0),
        'nome' => trim((string)($_POST['nome'] ?? '')),
      ];
      $code = null;
      $resp = apiRequestProdutoOpcoes('PUT', $apiBase . '/produto-opcoes/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Opcao atualizada com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar opcao.';
      }
    } else {
      $errorMessage = 'Opcao invalida.';
    }
  } elseif ($action === 'opcao_delete') {
    $id = (string)($_POST['opcao_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestProdutoOpcoes('DELETE', $apiBase . '/produto-opcoes/' . urlencode($id), $token, null, $code);
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

$produtoFiltro = (int)($_GET['produto_id'] ?? 0);

if ($apiBase !== '' && $token !== '') {
  $codeProd = null;
  $respProdutos = apiRequestProdutoOpcoes('GET', $apiBase . '/produtos?per_page=100', $token, null, $codeProd);
  if ($codeProd >= 200 && $codeProd < 300) {
    $produtos = $respProdutos['data'] ?? $respProdutos;
    if (is_array($produtos) && isset($produtos['data']) && is_array($produtos['data'])) {
      $produtos = $produtos['data'];
    }
    if (!is_array($produtos)) {
      $produtos = [];
    }
    if ($produtoFiltro === 0 && !empty($produtos)) {
      $first = $produtos[0];
      if (is_object($first)) {
        $first = (array)$first;
      }
      $produtoFiltro = (int)($first['id'] ?? 0);
    }
  }

  $code = null;
  $urlOpcoes = $apiBase . '/produto-opcoes';
  if ($produtoFiltro > 0) {
    $urlOpcoes .= '?produto_id=' . $produtoFiltro;
  }
  $respOpcoes = apiRequestProdutoOpcoes('GET', $urlOpcoes, $token, null, $code);
  if ($code >= 200 && $code < 300) {
    $opcoes = $respOpcoes['data'] ?? $respOpcoes;
    if (is_array($opcoes) && isset($opcoes['data']) && is_array($opcoes['data'])) {
      $opcoes = $opcoes['data'];
    }
    if (!is_array($opcoes)) {
      $opcoes = [];
    }
  } else {
    $errorMessage = $respOpcoes['message'] ?? 'Nao foi possivel carregar opcoes.';
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
            <h3 class="mb-1">Opcoes de produto</h3>
            <p class="text-muted mb-0">Tamanhos, sabores e adicionais.</p>
          </div>
          <div class="btn-wrapper">
            <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="modal" data-bs-target="#modalOpcaoNova">
              <i class="mdi mdi-plus"></i> Nova opcao
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
                <h4 class="mb-1">Opcoes</h4>
                <p class="text-muted mb-0">Filtre por produto.</p>
              </div>
              <form class="d-flex" method="GET" action="">
                <input type="hidden" name="paginas" value="produto_opcoes">
                <select name="produto_id" class="form-select form-select-sm me-2">
                  <?php foreach ($produtos as $produto): ?>
                    <?php
                      if (is_object($produto)) {
                        $produto = (array)$produto;
                      }
                      if (!is_array($produto)) {
                        continue;
                      }
                      $produtoId = $produto['id'] ?? '';
                      $produtoNome = $produto['nome'] ?? $produto['produto'] ?? '';
                    ?>
                    <option value="<?php echo htmlspecialchars((string)$produtoId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$produtoId === (int)$produtoFiltro) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars((string)$produtoNome, ENT_QUOTES, 'UTF-8'); ?>
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
                    <th>Produto</th>
                    <th>Opcao</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($opcoes)): ?>
                    <tr><td colspan="4">Nenhuma opcao encontrada.</td></tr>
                  <?php else: ?>
                    <?php foreach ($opcoes as $opcao): ?>
                      <?php
                        if (is_object($opcao)) {
                          $opcao = (array)$opcao;
                        }
                        if (!is_array($opcao)) {
                          continue;
                        }
                        $opcaoId = $opcao['id'] ?? '';
                        $produtoId = $opcao['produto_id'] ?? '';
                        $nome = $opcao['nome'] ?? '';
                      ?>
                      <tr>
                        <td>#<?php echo htmlspecialchars((string)$opcaoId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$produtoId, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-opcao" type="button" data-bs-toggle="modal" data-bs-target="#modalOpcaoEditar"
                            data-id="<?php echo htmlspecialchars((string)$opcaoId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-produto="<?php echo htmlspecialchars((string)$produtoId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-nome="<?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?>">
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

  <div class="modal fade" id="modalOpcaoNova" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="opcao_create">
          <div class="modal-header">
            <h5 class="modal-title">Nova opcao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Produto</label>
              <select name="produto_id" class="form-select" required>
                <?php foreach ($produtos as $produto): ?>
                  <?php
                    if (is_object($produto)) {
                      $produto = (array)$produto;
                    }
                    if (!is_array($produto)) {
                      continue;
                    }
                    $produtoId = $produto['id'] ?? '';
                    $produtoNome = $produto['nome'] ?? $produto['produto'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$produtoId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$produtoId === (int)$produtoFiltro) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$produtoNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
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

  <div class="modal fade" id="modalOpcaoEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formOpcaoEditar">
          <input type="hidden" name="action" value="opcao_update">
          <input type="hidden" name="opcao_id" id="editarOpcaoId">
          <div class="modal-header">
            <h5 class="modal-title">Editar opcao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Produto</label>
              <select name="produto_id" id="editarOpcaoProduto" class="form-select" required>
                <?php foreach ($produtos as $produto): ?>
                  <?php
                    if (is_object($produto)) {
                      $produto = (array)$produto;
                    }
                    if (!is_array($produto)) {
                      continue;
                    }
                    $produtoId = $produto['id'] ?? '';
                    $produtoNome = $produto['nome'] ?? $produto['produto'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$produtoId, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)$produtoNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" id="editarOpcaoNome" class="form-control" required>
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

  <div class="modal fade" id="modalOpcaoExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="opcao_delete">
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
    document.querySelectorAll('.btn-edit-opcao').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarOpcaoId').value = this.dataset.id || '';
        document.getElementById('editarOpcaoProduto').value = this.dataset.produto || '';
        document.getElementById('editarOpcaoNome').value = this.dataset.nome || '';
      });
    });

    document.querySelectorAll('.btn-delete-opcao').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirOpcaoId').value = this.dataset.id || '';
      });
    });
  </script>
</div>
