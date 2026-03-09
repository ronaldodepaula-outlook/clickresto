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
$categorias = [];
$detalheCategoria = null;
$detalheProdutos = [];
$detalheProdutosMeta = [];
$totaisPorCategoria = [];

function getFirstValue($data, $keys, $default = null) {
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

function formatMoneyCategoria($value) {
  if ($value === null || $value === '') {
    return '-';
  }
  $number = is_numeric($value) ? (float)$value : (float)str_replace(',', '.', (string)$value);
  return 'R$ ' . number_format($number, 2, ',', '.');
}

function apiRequestCategorias($method, $url, $token, $payload = null, &$httpCode = null, $empresaId = '') {
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
      'nome' => trim((string)($_POST['nome'] ?? '')),
      'descricao' => trim((string)($_POST['descricao'] ?? '')),
      'ativo' => isset($_POST['ativo']) ? true : false,
    ];
    $code = null;
    $resp = apiRequestCategorias('POST', $apiBase . '/categorias', $token, $payload, $code);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Categoria criada com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar categoria.';
    }
  } elseif ($action === 'update_put' || $action === 'update_patch') {
    $id = (string)($_POST['categoria_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'nome' => trim((string)($_POST['nome'] ?? '')),
        'descricao' => trim((string)($_POST['descricao'] ?? '')),
        'ativo' => isset($_POST['ativo']) ? true : false,
      ];
      $method = $action === 'update_patch' ? 'PATCH' : 'PUT';
      $code = null;
      $resp = apiRequestCategorias($method, $apiBase . '/categorias/' . urlencode($id), $token, $payload, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = $method === 'PATCH' ? 'Categoria atualizada (PATCH).' : 'Categoria atualizada (PUT).';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar categoria.';
      }
    } else {
      $errorMessage = 'Categoria invalida.';
    }
  } elseif ($action === 'delete') {
    $id = (string)($_POST['categoria_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestCategorias('DELETE', $apiBase . '/categorias/' . urlencode($id), $token, null, $code);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Categoria removida com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover categoria.';
      }
    } else {
      $errorMessage = 'Categoria invalida.';
    }
  } elseif ($action === 'detail') {
    $id = (string)($_POST['categoria_id'] ?? '');
    if ($id === '') {
      $errorMessage = 'Categoria invalida.';
    } else {
      $code = null;
      $resp = apiRequestCategorias('GET', $apiBase . '/prod_categorias/' . urlencode($id), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300 && is_array($resp)) {
        $detalheCategoria = $resp[0] ?? $resp['categoria'] ?? null;
        $detalheProdutosMeta = $resp[1] ?? $resp['produtos'] ?? [];
        if (is_array($detalheProdutosMeta) && isset($detalheProdutosMeta['data']) && is_array($detalheProdutosMeta['data'])) {
          $detalheProdutos = $detalheProdutosMeta['data'];
        } elseif (is_array($detalheProdutosMeta)) {
          $detalheProdutos = $detalheProdutosMeta;
        }
      } else {
        $errorMessage = $resp['message'] ?? 'Nao foi possivel carregar os produtos da categoria.';
      }
    }
  }
}

if ($apiBase !== '' && $token !== '') {
  $code = null;
  $currentPage = max(1, (int)($_GET['page'] ?? 1));
  $resp = apiRequestCategorias('GET', $apiBase . '/categorias?page=' . $currentPage, $token, null, $code);
  if ($code >= 200 && $code < 300) {
    $categorias = $resp['data'] ?? $resp;
    if (is_array($categorias) && isset($categorias['categorias']) && is_array($categorias['categorias'])) {
      $categorias = $categorias['categorias'];
    }
    if (is_array($categorias) && isset($categorias['data']) && is_array($categorias['data'])) {
      $categorias = $categorias['data'];
    }
    if (!is_array($categorias)) {
      $categorias = [];
    }

    $codeTotais = null;
    $respTotais = apiRequestCategorias('GET', $apiBase . '/categorias/totais', $token, null, $codeTotais);
    if ($codeTotais >= 200 && $codeTotais < 300) {
      $listaTotais = $respTotais['data'] ?? $respTotais;
      if (is_array($listaTotais) && isset($listaTotais['data']) && is_array($listaTotais['data'])) {
        $listaTotais = $listaTotais['data'];
      }
      if (is_array($listaTotais)) {
        foreach ($listaTotais as $item) {
          if (is_object($item)) {
            $item = (array)$item;
          }
          if (!is_array($item)) {
            continue;
          }
          if (isset($item['categoria']) && is_array($item['categoria'])) {
            $item = array_merge($item['categoria'], $item);
          }
          $catId = getFirstValue($item, ['id', 'categoria_id', 'categoriaId'], '');
          $total = getFirstValue($item, ['itens', 'itens_count', 'produtos_count', 'quantidade_itens', 'total_itens', 'total', 'qtd_itens', 'qtd_produtos', 'total_produtos'], null);
          if ($catId !== '' && $total !== null) {
            $totaisPorCategoria[(string)$catId] = (int)$total;
          }
        }
      }
    }
  } else {
    $errorMessage = $resp['message'] ?? 'Nao foi possivel carregar as categorias.';
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
                    <h3 class="mb-1">Categorias</h3>
                    <p class="text-muted mb-0">Organizacao do cardapio por categorias.</p>
                  </div>
                  <div class="btn-wrapper">
                    <button class="btn btn-outline-secondary me-2"><i class="mdi mdi-filter"></i> Filtros</button>
                    <button class="btn btn-primary text-white" type="button" data-bs-toggle="modal" data-bs-target="#modalCategoriaNova">
                      <i class="mdi mdi-plus"></i> Nova categoria
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
                            <th>Categoria</th>
                            <th>Descricao</th>
                            <th>Itens</th>
                            <th>Status</th>
                            <th>Acoes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($categorias)): ?>
                            <tr><td colspan="5">Nenhuma categoria encontrada.</td></tr>
                          <?php else: ?>
                            <?php foreach ($categorias as $categoria): ?>
                              <?php
                                if (is_object($categoria)) {
                                  $categoria = (array)$categoria;
                                }
                                if (!is_array($categoria)) {
                                  continue;
                                }
                                $categoriaData = $categoria;
                                if (isset($categoria['categoria']) && is_array($categoria['categoria'])) {
                                  $categoriaData = array_merge($categoria['categoria'], $categoria);
                                }
                                $catId = getFirstValue($categoriaData, ['id', 'categoria_id', 'categoriaId'], '');
                                $nome = getFirstValue($categoriaData, ['nome', 'name', 'categoria', 'titulo', 'title'], 'Categoria');
                                $descricao = getFirstValue($categoriaData, ['descricao', 'description', 'descricao_curta', 'observacao', 'obs'], '');
                                if (!is_scalar($nome)) {
                                  $nome = 'Categoria';
                                }
                                if (!is_scalar($descricao)) {
                                  $descricao = '';
                                }
                                $itens = getFirstValue($categoriaData, ['itens', 'itens_count', 'produtos_count', 'quantidade_itens', 'total_itens', 'total', 'qtd_itens', 'qtd_produtos', 'total_produtos'], null);
                                if ($itens === null && isset($categoriaData['produtos']) && is_array($categoriaData['produtos'])) {
                                  $itens = count($categoriaData['produtos']);
                                }
                                if ($itens === null && $catId !== '' && isset($totaisPorCategoria[(string)$catId])) {
                                  $itens = $totaisPorCategoria[(string)$catId];
                                }
                                $statusRaw = $categoriaData['status'] ?? null;
                                $ativo = null;
                                if (isset($categoriaData['ativo'])) {
                                  $ativo = (bool)$categoriaData['ativo'];
                                } elseif (is_string($statusRaw)) {
                                  $statusLower = strtolower($statusRaw);
                                  $ativo = in_array($statusLower, ['ativo', 'ativa', 'publicado'], true);
                                }
                                $statusLabel = $ativo === null ? 'Indefinido' : ($ativo ? 'Ativa' : 'Inativa');
                                $badgeClass = $ativo === null ? 'badge-opacity-secondary' : ($ativo ? 'badge-opacity-success' : 'badge-opacity-warning');
                                $descricaoDisplay = $descricao !== '' ? $descricao : '-';
                                $itensDisplay = $itens !== null ? (int)$itens : 0;
                              ?>
                              <tr>
                                <td>
                                  <h6><?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?></h6>
                                  <small class="text-muted">ID: <?php echo htmlspecialchars((string)$catId, ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars((string)$descricaoDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $itensDisplay; ?></td>
                                <td><div class="badge <?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></div></td>
                                <td>
                                  <form class="d-inline" method="POST" action="">
                                    <input type="hidden" name="action" value="detail">
                                    <input type="hidden" name="categoria_id" value="<?php echo htmlspecialchars((string)$catId, ENT_QUOTES, 'UTF-8'); ?>">
                                    <button class="btn btn-outline-info btn-sm me-1" type="submit">
                                      <i class="mdi mdi-eye"></i>
                                    </button>
                                  </form>
                                  <button class="btn btn-outline-primary btn-sm me-1 btn-edit-categoria"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalCategoriaEditar"
                                    data-id="<?php echo htmlspecialchars((string)$catId, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-nome="<?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-descricao="<?php echo htmlspecialchars((string)$descricao, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-ativo="<?php echo $ativo ? '1' : '0'; ?>">
                                    <i class="mdi mdi-pencil"></i>
                                  </button>
                                  <button class="btn btn-outline-danger btn-sm btn-delete-categoria"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalCategoriaExcluir"
                                    data-id="<?php echo htmlspecialchars((string)$catId, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-nome="<?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?>">
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
          <!-- Modal Nova -->
          <div class="modal fade" id="modalCategoriaNova" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="create">
                  <div class="modal-header">
                    <h5 class="modal-title">Nova categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Descricao</label>
                        <input type="text" name="descricao" class="form-control">
                      </div>
                      <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="ativo" id="novaCategoriaAtiva" checked>
                          <label class="form-check-label" for="novaCategoriaAtiva">Ativa</label>
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
          <div class="modal fade" id="modalCategoriaEditar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="" id="formCategoriaEditar">
                  <input type="hidden" name="action" id="categoriaAction" value="update_put">
                  <input type="hidden" name="categoria_id" id="editarCategoriaId">
                  <div class="modal-header">
                    <h5 class="modal-title">Editar categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" id="editarCategoriaNome" class="form-control" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Descricao</label>
                        <input type="text" name="descricao" id="editarCategoriaDescricao" class="form-control">
                      </div>
                      <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="ativo" id="editarCategoriaAtiva">
                          <label class="form-check-label" for="editarCategoriaAtiva">Ativa</label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" data-action="update_put">Salvar (PUT)</button>
                    <button type="submit" class="btn btn-outline-primary" data-action="update_patch">Salvar (PATCH)</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Modal Excluir -->
          <div class="modal fade" id="modalCategoriaExcluir" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="categoria_id" id="excluirCategoriaId">
                  <div class="modal-header">
                    <h5 class="modal-title">Excluir categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Confirma a exclusao da categoria <strong id="excluirCategoriaNome"></strong>?</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Modal Detalhe Categoria -->
          <div class="modal fade" id="modalCategoriaDetalhe" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Detalhe da categoria</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?php
                    $detCategoria = $detalheCategoria;
                    if (is_object($detCategoria)) {
                      $detCategoria = (array)$detCategoria;
                    }
                    $detNome = is_array($detCategoria) ? (getFirstValue($detCategoria, ['nome', 'categoria', 'titulo', 'name'], 'Categoria')) : 'Categoria';
                    $detDescricao = is_array($detCategoria) ? (getFirstValue($detCategoria, ['descricao', 'description', 'descricao_curta', 'observacao', 'obs'], '')) : '';
                    $detAtivo = is_array($detCategoria) && isset($detCategoria['ativo']) ? (bool)$detCategoria['ativo'] : null;
                    $detStatus = $detAtivo === null ? 'Indefinido' : ($detAtivo ? 'Ativa' : 'Inativa');
                    $detTotal = is_array($detalheProdutosMeta) ? (int)($detalheProdutosMeta['total'] ?? count($detalheProdutos)) : count($detalheProdutos);
                  ?>
                  <div class="row">
                    <div class="col-md-8">
                      <h6 class="mb-1"><?php echo htmlspecialchars((string)$detNome, ENT_QUOTES, 'UTF-8'); ?></h6>
                      <p class="text-muted mb-2"><?php echo $detDescricao !== '' ? htmlspecialchars((string)$detDescricao, ENT_QUOTES, 'UTF-8') : 'Sem descricao'; ?></p>
                    </div>
                    <div class="col-md-4 text-md-end">
                      <div class="badge <?php echo $detAtivo ? 'badge-opacity-success' : 'badge-opacity-warning'; ?>"><?php echo htmlspecialchars((string)$detStatus, ENT_QUOTES, 'UTF-8'); ?></div>
                      <div class="text-muted mt-2">Itens: <?php echo (int)$detTotal; ?></div>
                    </div>
                  </div>
                  <div class="table-responsive mt-3">
                    <table class="table select-table">
                      <thead>
                        <tr>
                          <th>Produto</th>
                          <th>Descricao</th>
                          <th>Preco</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($detalheProdutos)): ?>
                          <tr><td colspan="4">Nenhum produto encontrado para esta categoria.</td></tr>
                        <?php else: ?>
                          <?php foreach ($detalheProdutos as $produto): ?>
                            <?php
                              if (is_object($produto)) {
                                $produto = (array)$produto;
                              }
                              if (!is_array($produto)) {
                                continue;
                              }
                              $prodNome = getFirstValue($produto, ['nome', 'produto', 'title'], 'Produto');
                              $prodDescricao = getFirstValue($produto, ['descricao', 'description'], '');
                              $prodPreco = getFirstValue($produto, ['preco', 'valor'], '');
                              $prodAtivo = isset($produto['ativo']) ? (bool)$produto['ativo'] : null;
                              $prodStatus = $prodAtivo === null ? 'Indefinido' : ($prodAtivo ? 'Ativo' : 'Inativo');
                              $prodBadge = $prodAtivo ? 'badge-opacity-success' : 'badge-opacity-warning';
                            ?>
                            <tr>
                              <td><?php echo htmlspecialchars((string)$prodNome, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo $prodDescricao !== '' ? htmlspecialchars((string)$prodDescricao, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                              <td><?php echo htmlspecialchars(formatMoneyCategoria($prodPreco), ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><div class="badge <?php echo $prodBadge; ?>"><?php echo htmlspecialchars((string)$prodStatus, ENT_QUOTES, 'UTF-8'); ?></div></td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
              </div>
            </div>
          </div>

          <script>
            document.querySelectorAll('.btn-edit-categoria').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.getElementById('editarCategoriaId').value = this.dataset.id || '';
                document.getElementById('editarCategoriaNome').value = this.dataset.nome || '';
                document.getElementById('editarCategoriaDescricao').value = this.dataset.descricao || '';
                document.getElementById('editarCategoriaAtiva').checked = this.dataset.ativo === '1';
                document.getElementById('categoriaAction').value = 'update_put';
              });
            });

            document.querySelectorAll('#formCategoriaEditar button[data-action]').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.getElementById('categoriaAction').value = this.dataset.action;
              });
            });

            document.querySelectorAll('.btn-delete-categoria').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.getElementById('excluirCategoriaId').value = this.dataset.id || '';
                document.getElementById('excluirCategoriaNome').textContent = this.dataset.nome || '';
              });
            });
          </script>
          <?php if (!empty($detalheCategoria)): ?>
            <script>
              document.addEventListener('DOMContentLoaded', function () {
                var modalEl = document.getElementById('modalCategoriaDetalhe');
                if (modalEl && typeof bootstrap !== 'undefined') {
                  var modal = new bootstrap.Modal(modalEl);
                  modal.show();
                }
              });
            </script>
          <?php endif; ?>
        </div>
