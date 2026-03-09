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
$produtosListagem = [];
$produtosCrud = [];
$produtosById = [];
$categorias = [];
$categoriasByName = [];

function getFirstValueProduto($data, $keys, $default = null) {
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

function formatMoneyProduto($value) {
  if ($value === null || $value === '') {
    return '-';
  }
  $number = is_numeric($value) ? (float)$value : (float)str_replace(',', '.', (string)$value);
  return 'R$ ' . number_format($number, 2, ',', '.');
}

function apiRequestProdutos($method, $url, $token, $payload = null, &$httpCode = null, $empresaId = '') {
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
      'categoria_id' => (int)($_POST['categoria_id'] ?? 0),
      'nome' => trim((string)($_POST['nome'] ?? '')),
      'descricao' => trim((string)($_POST['descricao'] ?? '')),
      'preco' => (float)($_POST['preco'] ?? 0),
      'custo' => (float)($_POST['custo'] ?? 0),
      'codigo_barras' => trim((string)($_POST['codigo_barras'] ?? '')),
      'ativo' => isset($_POST['ativo']) ? true : false,
    ];
    $code = null;
    $resp = apiRequestProdutos('POST', $apiBase . '/produtos', $token, $payload, $code, $empresaId);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Produto criado com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar produto.';
    }
  } elseif ($action === 'update') {
    $id = (string)($_POST['produto_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'categoria_id' => (int)($_POST['categoria_id'] ?? 0),
        'nome' => trim((string)($_POST['nome'] ?? '')),
        'descricao' => trim((string)($_POST['descricao'] ?? '')),
        'preco' => (float)($_POST['preco'] ?? 0),
        'custo' => (float)($_POST['custo'] ?? 0),
        'codigo_barras' => trim((string)($_POST['codigo_barras'] ?? '')),
        'ativo' => isset($_POST['ativo']) ? true : false,
      ];
      $code = null;
      $resp = apiRequestProdutos('PUT', $apiBase . '/produtos/' . urlencode($id), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Produto atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar produto.';
      }
    } else {
      $errorMessage = 'Produto invalido.';
    }
  } elseif ($action === 'toggle') {
    $id = (string)($_POST['produto_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'ativo' => ($_POST['ativo'] ?? '') === '1',
      ];
      $code = null;
      $resp = apiRequestProdutos('PATCH', $apiBase . '/produtos/' . urlencode($id), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Status do produto atualizado.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar status.';
      }
    } else {
      $errorMessage = 'Produto invalido.';
    }
  } elseif ($action === 'delete') {
    $id = (string)($_POST['produto_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestProdutos('DELETE', $apiBase . '/produtos/' . urlencode($id), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Produto removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover produto.';
      }
    } else {
      $errorMessage = 'Produto invalido.';
    }
  }
}

if ($apiBase !== '' && $token !== '') {
  $code = null;
  $respListagem = apiRequestProdutos('GET', $apiBase . '/produtos/listagem', $token, null, $code, $empresaId);
  if ($code >= 200 && $code < 300) {
    $produtosListagem = is_array($respListagem) ? $respListagem : [];
    if (isset($produtosListagem['data']) && is_array($produtosListagem['data'])) {
      $produtosListagem = $produtosListagem['data'];
    }
  } else {
    $errorMessage = $respListagem['message'] ?? 'Nao foi possivel carregar a listagem de produtos.';
  }

  $codeCrud = null;
  $respCrud = apiRequestProdutos('GET', $apiBase . '/produtos', $token, null, $codeCrud, $empresaId);
  if ($codeCrud >= 200 && $codeCrud < 300) {
    $produtosCrud = $respCrud['data'] ?? $respCrud;
    if (is_array($produtosCrud) && isset($produtosCrud['data']) && is_array($produtosCrud['data'])) {
      $produtosCrud = $produtosCrud['data'];
    }
    if (is_array($produtosCrud)) {
      foreach ($produtosCrud as $produto) {
        if (is_object($produto)) {
          $produto = (array)$produto;
        }
        if (!is_array($produto)) {
          continue;
        }
        $id = getFirstValueProduto($produto, ['id', 'produto_id'], '');
        if ($id !== '') {
          $produtosById[(string)$id] = $produto;
        }
      }
    }
  }

  $codeCategorias = null;
  $respCategorias = apiRequestProdutos('GET', $apiBase . '/categorias', $token, null, $codeCategorias, $empresaId);
  if ($codeCategorias >= 200 && $codeCategorias < 300) {
    $categorias = $respCategorias['data'] ?? $respCategorias;
    if (is_array($categorias) && isset($categorias['data']) && is_array($categorias['data'])) {
      $categorias = $categorias['data'];
    }
    if (is_array($categorias)) {
      foreach ($categorias as $categoria) {
        if (is_object($categoria)) {
          $categoria = (array)$categoria;
        }
        if (!is_array($categoria)) {
          continue;
        }
        $catId = getFirstValueProduto($categoria, ['id', 'categoria_id'], '');
        $catNome = getFirstValueProduto($categoria, ['nome', 'categoria', 'titulo', 'name'], '');
        if ($catNome !== '') {
          $categoriasByName[strtolower((string)$catNome)] = $catId;
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
                    <h3 class="mb-1">Produtos</h3>
                    <p class="text-muted mb-0">Gestao do cardapio e precos.</p>
                  </div>
                  <div class="btn-wrapper">
                    <button class="btn btn-outline-secondary me-2"><i class="mdi mdi-filter"></i> Filtros</button>
                    <button class="btn btn-primary text-white" type="button" data-bs-toggle="modal" data-bs-target="#modalProdutoNovo">
                      <i class="mdi mdi-plus"></i> Novo produto
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
                    <div class="table-responsive">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preco</th>
                            <th>Status</th>
                            <th>Estoque</th>
                            <th>Acoes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($produtosListagem)): ?>
                            <tr><td colspan="6">Nenhum produto encontrado.</td></tr>
                          <?php else: ?>
                            <?php foreach ($produtosListagem as $item): ?>
                              <?php
                                if (is_object($item)) {
                                  $item = (array)$item;
                                }
                                if (!is_array($item)) {
                                  continue;
                                }
                                $id = getFirstValueProduto($item, ['id', 'produto_id'], '');
                                $produtoNome = getFirstValueProduto($item, ['produto', 'nome'], 'Produto');
                                $categoriaNome = getFirstValueProduto($item, ['categoria', 'categoria_nome', 'categoriaNome'], '-');
                                $preco = getFirstValueProduto($item, ['preco', 'valor'], 0);
                                $status = getFirstValueProduto($item, ['status'], '');
                                $estoque = getFirstValueProduto($item, ['estoque'], '-');
                                $estoqueQtd = getFirstValueProduto($item, ['estoque_quantidade', 'quantidade_estoque'], null);
                                $acoes = $item['acoes'] ?? [];

                                $crud = $produtosById[(string)$id] ?? [];
                                $categoriaId = getFirstValueProduto($crud, ['categoria_id'], '');
                                if ($categoriaId === '' && $categoriaNome !== '-' && isset($categoriasByName[strtolower((string)$categoriaNome)])) {
                                  $categoriaId = $categoriasByName[strtolower((string)$categoriaNome)];
                                }
                                $nomeCrud = getFirstValueProduto($crud, ['nome'], $produtoNome);
                                $descricaoCrud = getFirstValueProduto($crud, ['descricao'], '');
                                $precoCrud = getFirstValueProduto($crud, ['preco'], $preco);
                                $custoCrud = getFirstValueProduto($crud, ['custo'], '');
                                $codigoCrud = getFirstValueProduto($crud, ['codigo_barras'], '');
                                $ativoCrud = isset($crud['ativo']) ? (bool)$crud['ativo'] : (strtolower((string)$status) === 'ativo');

                                $statusLabel = $status !== '' ? $status : ($ativoCrud ? 'Ativo' : 'Inativo');
                                $badgeClass = strtolower((string)$statusLabel) === 'ativo' ? 'badge-opacity-success' : 'badge-opacity-warning';
                                $estoqueLabel = $estoqueQtd !== null ? $estoque . ' (' . (int)$estoqueQtd . ')' : $estoque;
                                $podeEditar = empty($acoes) || in_array('editar', $acoes, true);
                                $podeExcluir = empty($acoes) || in_array('excluir', $acoes, true);
                                $podeAtivar = in_array('ativar', $acoes, true);
                                $podeInativar = in_array('inativar', $acoes, true);
                              ?>
                              <tr>
                                <td>
                                  <h6><?php echo htmlspecialchars((string)$produtoNome, ENT_QUOTES, 'UTF-8'); ?></h6>
                                  <small class="text-muted">ID: <?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars((string)$categoriaNome, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(formatMoneyProduto($preco), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><div class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars((string)$statusLabel, ENT_QUOTES, 'UTF-8'); ?></div></td>
                                <td><?php echo htmlspecialchars((string)$estoqueLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                  <?php if ($podeEditar): ?>
                                    <button class="btn btn-outline-primary btn-sm me-1 btn-edit-produto"
                                      type="button"
                                      data-bs-toggle="modal"
                                      data-bs-target="#modalProdutoEditar"
                                      data-id="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>"
                                      data-categoria-id="<?php echo htmlspecialchars((string)$categoriaId, ENT_QUOTES, 'UTF-8'); ?>"
                                      data-nome="<?php echo htmlspecialchars((string)$nomeCrud, ENT_QUOTES, 'UTF-8'); ?>"
                                      data-descricao="<?php echo htmlspecialchars((string)$descricaoCrud, ENT_QUOTES, 'UTF-8'); ?>"
                                      data-preco="<?php echo htmlspecialchars((string)$precoCrud, ENT_QUOTES, 'UTF-8'); ?>"
                                      data-custo="<?php echo htmlspecialchars((string)$custoCrud, ENT_QUOTES, 'UTF-8'); ?>"
                                      data-codigo="<?php echo htmlspecialchars((string)$codigoCrud, ENT_QUOTES, 'UTF-8'); ?>"
                                      data-ativo="<?php echo $ativoCrud ? '1' : '0'; ?>">
                                      <i class="mdi mdi-pencil"></i>
                                    </button>
                                  <?php endif; ?>
                                  <?php if ($podeAtivar || $podeInativar): ?>
                                    <form class="d-inline" method="POST" action="">
                                      <input type="hidden" name="action" value="toggle">
                                      <input type="hidden" name="produto_id" value="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>">
                                      <input type="hidden" name="ativo" value="<?php echo $podeAtivar ? '1' : '0'; ?>">
                                      <?php if ($podeAtivar): ?>
                                        <button class="btn btn-outline-success btn-sm me-1" type="submit"><i class="mdi mdi-check"></i></button>
                                      <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-sm me-1" type="submit"><i class="mdi mdi-eye-off"></i></button>
                                      <?php endif; ?>
                                    </form>
                                  <?php endif; ?>
                                  <?php if ($podeExcluir): ?>
                                    <button class="btn btn-outline-danger btn-sm btn-delete-produto"
                                      type="button"
                                      data-bs-toggle="modal"
                                      data-bs-target="#modalProdutoExcluir"
                                      data-id="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>"
                                      data-nome="<?php echo htmlspecialchars((string)$produtoNome, ENT_QUOTES, 'UTF-8'); ?>">
                                      <i class="mdi mdi-delete"></i>
                                    </button>
                                  <?php endif; ?>
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

          <!-- Modal Novo Produto -->
          <div class="modal fade" id="modalProdutoNovo" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="create">
                  <div class="modal-header">
                    <h5 class="modal-title">Novo produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Categoria</label>
                        <select name="categoria_id" class="form-select" required>
                          <option value="">Selecione</option>
                          <?php foreach ($categorias as $categoria): ?>
                            <?php
                              if (is_object($categoria)) {
                                $categoria = (array)$categoria;
                              }
                              if (!is_array($categoria)) {
                                continue;
                              }
                              $catId = getFirstValueProduto($categoria, ['id', 'categoria_id'], '');
                              $catNome = getFirstValueProduto($categoria, ['nome', 'categoria', 'titulo', 'name'], '');
                              if ($catId === '' || $catNome === '') {
                                continue;
                              }
                            ?>
                            <option value="<?php echo htmlspecialchars((string)$catId, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string)$catNome, ENT_QUOTES, 'UTF-8'); ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                      </div>
                      <div class="col-md-12 mb-3">
                        <label class="form-label">Descricao</label>
                        <input type="text" name="descricao" class="form-control">
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Preco</label>
                        <input type="number" step="0.01" name="preco" class="form-control" required>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Custo</label>
                        <input type="number" step="0.01" name="custo" class="form-control">
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Codigo de barras</label>
                        <input type="text" name="codigo_barras" class="form-control">
                      </div>
                      <div class="col-md-12">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="ativo" id="novoProdutoAtivo" checked>
                          <label class="form-check-label" for="novoProdutoAtivo">Ativo</label>
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

          <!-- Modal Editar Produto -->
          <div class="modal fade" id="modalProdutoEditar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="" id="formProdutoEditar">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="produto_id" id="editarProdutoId">
                  <div class="modal-header">
                    <h5 class="modal-title">Editar produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Categoria</label>
                        <select name="categoria_id" id="editarCategoriaId" class="form-select" required>
                          <option value="">Selecione</option>
                          <?php foreach ($categorias as $categoria): ?>
                            <?php
                              if (is_object($categoria)) {
                                $categoria = (array)$categoria;
                              }
                              if (!is_array($categoria)) {
                                continue;
                              }
                              $catId = getFirstValueProduto($categoria, ['id', 'categoria_id'], '');
                              $catNome = getFirstValueProduto($categoria, ['nome', 'categoria', 'titulo', 'name'], '');
                              if ($catId === '' || $catNome === '') {
                                continue;
                              }
                            ?>
                            <option value="<?php echo htmlspecialchars((string)$catId, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string)$catNome, ENT_QUOTES, 'UTF-8'); ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" id="editarProdutoNome" class="form-control" required>
                      </div>
                      <div class="col-md-12 mb-3">
                        <label class="form-label">Descricao</label>
                        <input type="text" name="descricao" id="editarProdutoDescricao" class="form-control">
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Preco</label>
                        <input type="number" step="0.01" name="preco" id="editarProdutoPreco" class="form-control" required>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Custo</label>
                        <input type="number" step="0.01" name="custo" id="editarProdutoCusto" class="form-control">
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Codigo de barras</label>
                        <input type="text" name="codigo_barras" id="editarProdutoCodigo" class="form-control">
                      </div>
                      <div class="col-md-12">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="ativo" id="editarProdutoAtivo">
                          <label class="form-check-label" for="editarProdutoAtivo">Ativo</label>
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

          <!-- Modal Excluir Produto -->
          <div class="modal fade" id="modalProdutoExcluir" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="produto_id" id="excluirProdutoId">
                  <div class="modal-header">
                    <h5 class="modal-title">Excluir produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Confirma a exclusao do produto <strong id="excluirProdutoNome"></strong>?</p>
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
            document.querySelectorAll('.btn-edit-produto').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.getElementById('editarProdutoId').value = this.dataset.id || '';
                document.getElementById('editarCategoriaId').value = this.dataset.categoriaId || '';
                document.getElementById('editarProdutoNome').value = this.dataset.nome || '';
                document.getElementById('editarProdutoDescricao').value = this.dataset.descricao || '';
                document.getElementById('editarProdutoPreco').value = this.dataset.preco || '';
                document.getElementById('editarProdutoCusto').value = this.dataset.custo || '';
                document.getElementById('editarProdutoCodigo').value = this.dataset.codigo || '';
                document.getElementById('editarProdutoAtivo').checked = this.dataset.ativo === '1';
              });
            });

            document.querySelectorAll('.btn-delete-produto').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.getElementById('excluirProdutoId').value = this.dataset.id || '';
                document.getElementById('excluirProdutoNome').textContent = this.dataset.nome || '';
              });
            });
          </script>
        </div>
