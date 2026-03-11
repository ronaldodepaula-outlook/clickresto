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
$role = strtolower((string)($_SESSION['user_role'] ?? ''));
$role = str_replace(['-', ' '], '_', $role);
if (strpos($role, 'master') !== false) {
  $role = 'admin_master';
} elseif (strpos($role, 'admin') !== false) {
  $role = 'admin';
} else {
  $role = 'admin';
}
$nameLower = strtolower((string)($_SESSION['user_name'] ?? ''));
$emailLower = strtolower((string)($_SESSION['user_email'] ?? ''));
if ($role !== 'admin_master' && (strpos($nameLower, 'master') !== false || $emailLower === 'admin@clickresto.com')) {
  $role = 'admin_master';
}
$canManagePerfis = ($role === 'admin_master' || $role === 'admin');
$canManagePermissoes = ($role === 'admin_master');

$usuarios = [];
$perfis = [];
$permissoes = [];
$usuarioPerfis = [];
$perfilPermissoes = [];

function formatDateTimeGestao($value) {
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

function normalizeListGestao($data) {
  if (!is_array($data)) {
    return [];
  }
  if (isset($data['data']) && is_array($data['data'])) {
    $data = $data['data'];
  }
  if (isset($data['data']) && is_array($data['data'])) {
    $data = $data['data'];
  }
  return is_array($data) ? $data : [];
}

function apiRequestGestaoUsuarios($method, $url, $token, $payload = null, &$httpCode = null, $empresaId = null) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  $headers = [
    'Accept: application/json',
  ];
  if ($token !== '') {
    $headers[] = 'Authorization: Bearer ' . $token;
  }
  if ($empresaId !== null && $empresaId !== '') {
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
  $masterOnlyActions = [
    'perfil_create',
    'perfil_update',
    'perfil_delete',
    'permissao_create',
    'permissao_update',
    'permissao_delete',
    'perfil_permissao_create',
    'perfil_permissao_update',
    'perfil_permissao_delete',
  ];
  $perfilActions = [
    'usuario_perfil_create',
    'usuario_perfil_update',
    'usuario_perfil_delete',
  ];
  if (!$canManagePermissoes && in_array($action, $masterOnlyActions, true)) {
    $errorMessage = 'Acesso restrito ao admin_master.';
  } elseif (!$canManagePerfis && in_array($action, $perfilActions, true)) {
    $errorMessage = 'Acesso restrito.';
  } elseif ($action === 'usuario_create') {
    $payload = [
      'nome' => trim((string)($_POST['nome'] ?? '')),
      'email' => trim((string)($_POST['email'] ?? '')),
      'senha' => (string)($_POST['senha'] ?? ''),
      'ativo' => isset($_POST['ativo']) ? true : false,
    ];
    $code = null;
    $resp = apiRequestGestaoUsuarios('POST', $apiBase . '/usuarios', $token, $payload, $code, $empresaId);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Usuario criado com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar usuario.';
    }
  } elseif ($action === 'usuario_update') {
    $id = (string)($_POST['usuario_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'nome' => trim((string)($_POST['nome'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'ativo' => isset($_POST['ativo']) ? true : false,
      ];
      $senha = (string)($_POST['senha'] ?? '');
      if ($senha !== '') {
        $payload['senha'] = $senha;
      }
      $code = null;
      $resp = apiRequestGestaoUsuarios('PUT', $apiBase . '/usuarios/' . urlencode($id), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Usuario atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar usuario.';
      }
    } else {
      $errorMessage = 'Usuario invalido.';
    }
  } elseif ($action === 'usuario_delete') {
    $id = (string)($_POST['usuario_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestGestaoUsuarios('DELETE', $apiBase . '/usuarios/' . urlencode($id), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Usuario removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover usuario.';
      }
    } else {
      $errorMessage = 'Usuario invalido.';
    }
  } elseif ($action === 'perfil_create') {
    $payload = [
      'nome' => trim((string)($_POST['nome'] ?? '')),
      'descricao' => trim((string)($_POST['descricao'] ?? '')),
    ];
    $code = null;
    $resp = apiRequestGestaoUsuarios('POST', $apiBase . '/perfis', $token, $payload, $code, $empresaId);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Perfil criado com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar perfil.';
    }
  } elseif ($action === 'perfil_update') {
    $id = (string)($_POST['perfil_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'nome' => trim((string)($_POST['nome'] ?? '')),
        'descricao' => trim((string)($_POST['descricao'] ?? '')),
      ];
      $code = null;
      $resp = apiRequestGestaoUsuarios('PUT', $apiBase . '/perfis/' . urlencode($id), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Perfil atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar perfil.';
      }
    } else {
      $errorMessage = 'Perfil invalido.';
    }
  } elseif ($action === 'perfil_delete') {
    $id = (string)($_POST['perfil_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestGestaoUsuarios('DELETE', $apiBase . '/perfis/' . urlencode($id), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Perfil removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover perfil.';
      }
    } else {
      $errorMessage = 'Perfil invalido.';
    }
  } elseif ($action === 'permissao_create') {
    $payload = [
      'nome' => trim((string)($_POST['nome'] ?? '')),
      'descricao' => trim((string)($_POST['descricao'] ?? '')),
    ];
    $code = null;
    $resp = apiRequestGestaoUsuarios('POST', $apiBase . '/permissoes', $token, $payload, $code, $empresaId);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Permissao criada com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar permissao.';
    }
  } elseif ($action === 'permissao_update') {
    $id = (string)($_POST['permissao_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'nome' => trim((string)($_POST['nome'] ?? '')),
        'descricao' => trim((string)($_POST['descricao'] ?? '')),
      ];
      $code = null;
      $resp = apiRequestGestaoUsuarios('PUT', $apiBase . '/permissoes/' . urlencode($id), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Permissao atualizada com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar permissao.';
      }
    } else {
      $errorMessage = 'Permissao invalida.';
    }
  } elseif ($action === 'permissao_delete') {
    $id = (string)($_POST['permissao_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestGestaoUsuarios('DELETE', $apiBase . '/permissoes/' . urlencode($id), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Permissao removida com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover permissao.';
      }
    } else {
      $errorMessage = 'Permissao invalida.';
    }
  } elseif ($action === 'usuario_perfil_create') {
    $payload = [
      'usuario_id' => (int)($_POST['usuario_id'] ?? 0),
      'perfil_id' => (int)($_POST['perfil_id'] ?? 0),
    ];
    $code = null;
    $resp = apiRequestGestaoUsuarios('POST', $apiBase . '/usuario-perfis', $token, $payload, $code, $empresaId);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Vinculo criado com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao criar vinculo.';
    }
  } elseif ($action === 'usuario_perfil_update') {
    $id = (string)($_POST['usuario_perfil_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'usuario_id' => (int)($_POST['usuario_id'] ?? 0),
        'perfil_id' => (int)($_POST['perfil_id'] ?? 0),
      ];
      $code = null;
      $resp = apiRequestGestaoUsuarios('PUT', $apiBase . '/usuario-perfis/' . urlencode($id), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Vinculo atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar vinculo.';
      }
    } else {
      $errorMessage = 'Vinculo invalido.';
    }
  } elseif ($action === 'usuario_perfil_delete') {
    $id = (string)($_POST['usuario_perfil_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestGestaoUsuarios('DELETE', $apiBase . '/usuario-perfis/' . urlencode($id), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Vinculo removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover vinculo.';
      }
    } else {
      $errorMessage = 'Vinculo invalido.';
    }
  } elseif ($action === 'perfil_permissao_create') {
    $payload = [
      'perfil_id' => (int)($_POST['perfil_id'] ?? 0),
      'permissao_id' => (int)($_POST['permissao_id'] ?? 0),
    ];
    $code = null;
    $resp = apiRequestGestaoUsuarios('POST', $apiBase . '/perfil-permissoes', $token, $payload, $code, $empresaId);
    if ($code >= 200 && $code < 300) {
      $successMessage = 'Permissao vinculada com sucesso.';
    } else {
      $errorMessage = $resp['message'] ?? 'Erro ao vincular permissao.';
    }
  } elseif ($action === 'perfil_permissao_update') {
    $id = (string)($_POST['perfil_permissao_id'] ?? '');
    if ($id !== '') {
      $payload = [
        'perfil_id' => (int)($_POST['perfil_id'] ?? 0),
        'permissao_id' => (int)($_POST['permissao_id'] ?? 0),
      ];
      $code = null;
      $resp = apiRequestGestaoUsuarios('PUT', $apiBase . '/perfil-permissoes/' . urlencode($id), $token, $payload, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Vinculo atualizado com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao atualizar vinculo.';
      }
    } else {
      $errorMessage = 'Vinculo invalido.';
    }
  } elseif ($action === 'perfil_permissao_delete') {
    $id = (string)($_POST['perfil_permissao_id'] ?? '');
    if ($id !== '') {
      $code = null;
      $resp = apiRequestGestaoUsuarios('DELETE', $apiBase . '/perfil-permissoes/' . urlencode($id), $token, null, $code, $empresaId);
      if ($code >= 200 && $code < 300) {
        $successMessage = 'Vinculo removido com sucesso.';
      } else {
        $errorMessage = $resp['message'] ?? 'Erro ao remover vinculo.';
      }
    } else {
      $errorMessage = 'Vinculo invalido.';
    }
  }
}

$usuarioFiltro = (int)($_GET['usuario_id'] ?? 0);
$perfilFiltro = (int)($_GET['perfil_id'] ?? 0);

if ($apiBase !== '' && $token !== '') {
  $codeUsuarios = null;
  $respUsuarios = apiRequestGestaoUsuarios('GET', $apiBase . '/usuarios?per_page=100', $token, null, $codeUsuarios, $empresaId);
  if ($codeUsuarios >= 200 && $codeUsuarios < 300) {
    $usuarios = normalizeListGestao($respUsuarios);
  } else {
    $errorMessage = $errorMessage !== '' ? $errorMessage : ($respUsuarios['message'] ?? 'Nao foi possivel carregar usuarios.');
  }

  if ($canManagePerfis) {
    $codePerfis = null;
    $respPerfis = apiRequestGestaoUsuarios('GET', $apiBase . '/perfis?per_page=100', $token, null, $codePerfis, $empresaId);
    if ($codePerfis >= 200 && $codePerfis < 300) {
      $perfis = normalizeListGestao($respPerfis);
    } else {
      $errorMessage = $errorMessage !== '' ? $errorMessage : ($respPerfis['message'] ?? 'Nao foi possivel carregar perfis.');
    }
  }

  if ($canManagePermissoes) {
    $codePermissoes = null;
    $respPermissoes = apiRequestGestaoUsuarios('GET', $apiBase . '/permissoes?per_page=100', $token, null, $codePermissoes, $empresaId);
    if ($codePermissoes >= 200 && $codePermissoes < 300) {
      $permissoes = normalizeListGestao($respPermissoes);
    } else {
      $errorMessage = $errorMessage !== '' ? $errorMessage : ($respPermissoes['message'] ?? 'Nao foi possivel carregar permissoes.');
    }
  }

  if ($usuarioFiltro === 0 && !empty($usuarios)) {
    $first = $usuarios[0];
    if (is_object($first)) {
      $first = (array)$first;
    }
    $usuarioFiltro = (int)($first['id'] ?? 0);
  }

  if ($perfilFiltro === 0 && !empty($perfis)) {
    $first = $perfis[0];
    if (is_object($first)) {
      $first = (array)$first;
    }
    $perfilFiltro = (int)($first['id'] ?? 0);
  }

  if ($usuarioFiltro > 0 && $canManagePerfis) {
    $codeUsuarioPerfis = null;
    $respUsuarioPerfis = apiRequestGestaoUsuarios('GET', $apiBase . '/usuario-perfis?usuario_id=' . $usuarioFiltro, $token, null, $codeUsuarioPerfis, $empresaId);
    if ($codeUsuarioPerfis >= 200 && $codeUsuarioPerfis < 300) {
      $usuarioPerfis = normalizeListGestao($respUsuarioPerfis);
    }
  }

  if ($perfilFiltro > 0 && $canManagePermissoes) {
    $codePerfilPerm = null;
    $respPerfilPerm = apiRequestGestaoUsuarios('GET', $apiBase . '/perfil-permissoes?perfil_id=' . $perfilFiltro, $token, null, $codePerfilPerm, $empresaId);
    if ($codePerfilPerm >= 200 && $codePerfilPerm < 300) {
      $perfilPermissoes = normalizeListGestao($respPerfilPerm);
    }
  }
} else {
  $errorMessage = 'Token ou API_BASE_URL nao configurados.';
}

$usuarioMap = [];
foreach ($usuarios as $usuario) {
  if (is_object($usuario)) {
    $usuario = (array)$usuario;
  }
  if (!is_array($usuario)) {
    continue;
  }
  $id = $usuario['id'] ?? null;
  if ($id !== null) {
    $usuarioMap[(string)$id] = $usuario['nome'] ?? ('Usuario ' . $id);
  }
}
$perfilMap = [];
foreach ($perfis as $perfil) {
  if (is_object($perfil)) {
    $perfil = (array)$perfil;
  }
  if (!is_array($perfil)) {
    continue;
  }
  $id = $perfil['id'] ?? null;
  if ($id !== null) {
    $perfilMap[(string)$id] = $perfil['nome'] ?? ('Perfil ' . $id);
  }
}
$permissaoMap = [];
foreach ($permissoes as $permissao) {
  if (is_object($permissao)) {
    $permissao = (array)$permissao;
  }
  if (!is_array($permissao)) {
    continue;
  }
  $id = $permissao['id'] ?? null;
  if ($id !== null) {
    $permissaoMap[(string)$id] = $permissao['nome'] ?? ('Permissao ' . $id);
  }
}
?>
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row" id="usuario-perfis-section">
      <div class="col-sm-12">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <div>
            <h3 class="mb-1">Gestao de usuarios</h3>
            <p class="text-muted mb-0">Usuarios, perfis, permissoes e vinculos da empresa.</p>
          </div>
          <div class="btn-wrapper">
            <button class="btn btn-primary text-white" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuarioNovo">
              <i class="mdi mdi-plus"></i> Novo usuario
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
    <?php if (!$canManagePermissoes): ?>
      <div class="alert alert-info">
        Permissoes e vinculos avancados (perfil x permissao) estao disponiveis apenas para admin_master.
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Usuarios da empresa</h4>
                <p class="text-muted mb-0">Cadastro de usuarios vinculados a empresa.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuarioNovo">
                <i class="mdi mdi-plus"></i> Novo usuario
              </button>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Ativo</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($usuarios)): ?>
                    <tr><td colspan="5">Nenhum usuario encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                      <?php
                        if (is_object($usuario)) {
                          $usuario = (array)$usuario;
                        }
                        if (!is_array($usuario)) {
                          continue;
                        }
                        $usuarioId = $usuario['id'] ?? '';
                        $nome = $usuario['nome'] ?? '';
                        $email = $usuario['email'] ?? '';
                        $ativo = $usuario['ativo'] ?? false;
                        $criado = formatDateTimeGestao($usuario['created_at'] ?? '');
                        $ativoLabel = $ativo ? 'Sim' : 'Nao';
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$ativoLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$criado, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <?php if ($canManagePerfis): ?>
                            <button class="btn btn-outline-info btn-sm me-1 btn-manage-permissoes" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuarioPerfilNovo"
                              data-usuario="<?php echo htmlspecialchars((string)$usuarioId, ENT_QUOTES, 'UTF-8'); ?>"
                              data-nome="<?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?>">
                              <i class="mdi mdi-shield-account"></i>
                            </button>
                          <?php endif; ?>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-usuario" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuarioEditar"
                            data-id="<?php echo htmlspecialchars((string)$usuarioId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-nome="<?php echo htmlspecialchars((string)$nome, ENT_QUOTES, 'UTF-8'); ?>"
                            data-email="<?php echo htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8'); ?>"
                            data-ativo="<?php echo $ativo ? '1' : '0'; ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-usuario" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuarioExcluir"
                            data-id="<?php echo htmlspecialchars((string)$usuarioId, ENT_QUOTES, 'UTF-8'); ?>">
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
    <?php if ($canManagePerfis): ?>
    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Usuario x Perfis</h4>
                <p class="text-muted mb-0">Vinculo de usuarios aos perfis.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuarioPerfilNovo">
                <i class="mdi mdi-link-plus"></i> Novo vinculo
              </button>
            </div>
            <form class="d-flex align-items-center mb-3" method="GET" action="">
              <input type="hidden" name="paginas" value="usuarios_empresa">
              <label class="me-2">Usuario</label>
              <select name="usuario_id" id="filtroUsuarioPerfil" class="form-select form-select-sm me-2" style="max-width: 260px;">
                <?php foreach ($usuarios as $usuario): ?>
                  <?php
                    if (is_object($usuario)) {
                      $usuario = (array)$usuario;
                    }
                    if (!is_array($usuario)) {
                      continue;
                    }
                    $uId = $usuario['id'] ?? '';
                    $uNome = $usuario['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$uId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$uId === (int)$usuarioFiltro) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$uNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <label class="me-2">Perfil</label>
              <select name="perfil_id" class="form-select form-select-sm me-2" style="max-width: 200px;">
                <?php foreach ($perfis as $perfil): ?>
                  <?php
                    if (is_object($perfil)) {
                      $perfil = (array)$perfil;
                    }
                    if (!is_array($perfil)) {
                      continue;
                    }
                    $pId = $perfil['id'] ?? '';
                    $pNome = $perfil['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$pId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$pId === (int)$perfilFiltro) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$pNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-outline-secondary btn-sm" type="submit">Filtrar</button>
            </form>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>Usuario</th>
                    <th>Perfil</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($usuarioPerfis)): ?>
                    <tr><td colspan="4">Nenhum vinculo encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($usuarioPerfis as $item): ?>
                      <?php
                        if (is_object($item)) {
                          $item = (array)$item;
                        }
                        if (!is_array($item)) {
                          continue;
                        }
                        $id = $item['id'] ?? '';
                        $uId = $item['usuario_id'] ?? '';
                        $pId = $item['perfil_id'] ?? '';
                        $created = formatDateTimeGestao($item['created_at'] ?? '');
                        $uNome = $usuarioMap[(string)$uId] ?? ('Usuario ' . $uId);
                        $pNome = $perfilMap[(string)$pId] ?? ('Perfil ' . $pId);
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars((string)$uNome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$pNome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$created, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-usuario-perfil" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuarioPerfilEditar"
                            data-id="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>"
                            data-usuario="<?php echo htmlspecialchars((string)$uId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-perfil="<?php echo htmlspecialchars((string)$pId, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-usuario-perfil" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuarioPerfilExcluir"
                            data-id="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>">
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
      <?php if ($canManagePermissoes): ?>
      <div class="col-lg-6 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Perfis</h4>
                <p class="text-muted mb-0">Perfis de acesso.</p>
              </div>
              <?php if ($canManagePermissoes): ?>
                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalPerfilNovo">
                  <i class="mdi mdi-plus"></i> Novo perfil
                </button>
              <?php endif; ?>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Descricao</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($perfis)): ?>
                    <tr><td colspan="3">Nenhum perfil encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($perfis as $perfil): ?>
                      <?php
                        if (is_object($perfil)) {
                          $perfil = (array)$perfil;
                        }
                        if (!is_array($perfil)) {
                          continue;
                        }
                        $perfilId = $perfil['id'] ?? '';
                        $perfilNome = $perfil['nome'] ?? '';
                        $perfilDescricao = $perfil['descricao'] ?? '';
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars((string)$perfilNome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$perfilDescricao, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <?php if ($canManagePermissoes): ?>
                            <button class="btn btn-outline-primary btn-sm me-1 btn-edit-perfil" type="button" data-bs-toggle="modal" data-bs-target="#modalPerfilEditar"
                              data-id="<?php echo htmlspecialchars((string)$perfilId, ENT_QUOTES, 'UTF-8'); ?>"
                              data-nome="<?php echo htmlspecialchars((string)$perfilNome, ENT_QUOTES, 'UTF-8'); ?>"
                              data-descricao="<?php echo htmlspecialchars((string)$perfilDescricao, ENT_QUOTES, 'UTF-8'); ?>">
                              <i class="mdi mdi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm btn-delete-perfil" type="button" data-bs-toggle="modal" data-bs-target="#modalPerfilExcluir"
                              data-id="<?php echo htmlspecialchars((string)$perfilId, ENT_QUOTES, 'UTF-8'); ?>">
                              <i class="mdi mdi-delete"></i>
                            </button>
                          <?php else: ?>
                            <span class="text-muted">Somente leitura</span>
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

      <div class="col-lg-6 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Permissoes</h4>
                <p class="text-muted mb-0">Permissoes disponiveis.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalPermissaoNovo">
                <i class="mdi mdi-plus"></i> Nova permissao
              </button>
            </div>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Descricao</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($permissoes)): ?>
                    <tr><td colspan="3">Nenhuma permissao encontrada.</td></tr>
                  <?php else: ?>
                    <?php foreach ($permissoes as $permissao): ?>
                      <?php
                        if (is_object($permissao)) {
                          $permissao = (array)$permissao;
                        }
                        if (!is_array($permissao)) {
                          continue;
                        }
                        $permissaoId = $permissao['id'] ?? '';
                        $permissaoNome = $permissao['nome'] ?? '';
                        $permissaoDescricao = $permissao['descricao'] ?? '';
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars((string)$permissaoNome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$permissaoDescricao, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-permissao" type="button" data-bs-toggle="modal" data-bs-target="#modalPermissaoEditar"
                            data-id="<?php echo htmlspecialchars((string)$permissaoId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-nome="<?php echo htmlspecialchars((string)$permissaoNome, ENT_QUOTES, 'UTF-8'); ?>"
                            data-descricao="<?php echo htmlspecialchars((string)$permissaoDescricao, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-permissao" type="button" data-bs-toggle="modal" data-bs-target="#modalPermissaoExcluir"
                            data-id="<?php echo htmlspecialchars((string)$permissaoId, ENT_QUOTES, 'UTF-8'); ?>">
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
      <?php endif; ?>
    </div>
    <?php if ($canManagePermissoes): ?>
    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="mb-1">Perfil x Permissoes</h4>
                <p class="text-muted mb-0">Vinculo de permissoes aos perfis.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalPerfilPermissaoNovo">
                <i class="mdi mdi-link-plus"></i> Novo vinculo
              </button>
            </div>
            <form class="d-flex align-items-center mb-3" method="GET" action="">
              <input type="hidden" name="paginas" value="usuarios_empresa">
              <label class="me-2">Perfil</label>
              <select name="perfil_id" class="form-select form-select-sm me-2" style="max-width: 240px;">
                <?php foreach ($perfis as $perfil): ?>
                  <?php
                    if (is_object($perfil)) {
                      $perfil = (array)$perfil;
                    }
                    if (!is_array($perfil)) {
                      continue;
                    }
                    $pId = $perfil['id'] ?? '';
                    $pNome = $perfil['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$pId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$pId === (int)$perfilFiltro) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$pNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <label class="me-2">Usuario</label>
              <select name="usuario_id" class="form-select form-select-sm me-2" style="max-width: 240px;">
                <?php foreach ($usuarios as $usuario): ?>
                  <?php
                    if (is_object($usuario)) {
                      $usuario = (array)$usuario;
                    }
                    if (!is_array($usuario)) {
                      continue;
                    }
                    $uId = $usuario['id'] ?? '';
                    $uNome = $usuario['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$uId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$uId === (int)$usuarioFiltro) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$uNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-outline-secondary btn-sm" type="submit">Filtrar</button>
            </form>
            <div class="table-responsive">
              <table class="table select-table">
                <thead>
                  <tr>
                    <th>Perfil</th>
                    <th>Permissao</th>
                    <th>Criado</th>
                    <th>Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($perfilPermissoes)): ?>
                    <tr><td colspan="4">Nenhum vinculo encontrado.</td></tr>
                  <?php else: ?>
                    <?php foreach ($perfilPermissoes as $item): ?>
                      <?php
                        if (is_object($item)) {
                          $item = (array)$item;
                        }
                        if (!is_array($item)) {
                          continue;
                        }
                        $id = $item['id'] ?? '';
                        $pId = $item['perfil_id'] ?? '';
                        $permId = $item['permissao_id'] ?? '';
                        $created = formatDateTimeGestao($item['created_at'] ?? '');
                        $pNome = $perfilMap[(string)$pId] ?? ('Perfil ' . $pId);
                        $permNome = $permissaoMap[(string)$permId] ?? ('Permissao ' . $permId);
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars((string)$pNome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$permNome, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)$created, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <button class="btn btn-outline-primary btn-sm me-1 btn-edit-perfil-permissao" type="button" data-bs-toggle="modal" data-bs-target="#modalPerfilPermissaoEditar"
                            data-id="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>"
                            data-perfil="<?php echo htmlspecialchars((string)$pId, ENT_QUOTES, 'UTF-8'); ?>"
                            data-permissao="<?php echo htmlspecialchars((string)$permId, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="mdi mdi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm btn-delete-perfil-permissao" type="button" data-bs-toggle="modal" data-bs-target="#modalPerfilPermissaoExcluir"
                            data-id="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>">
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
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Modais Usuarios -->
  <div class="modal fade" id="modalUsuarioNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="usuario_create">
          <div class="modal-header">
            <h5 class="modal-title">Novo usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Senha</label>
              <input type="password" name="senha" class="form-control" required>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="ativo" id="novoUsuarioAtivo" checked>
              <label class="form-check-label" for="novoUsuarioAtivo">Usuario ativo</label>
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

  <div class="modal fade" id="modalUsuarioEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formUsuarioEditar">
          <input type="hidden" name="action" value="usuario_update">
          <input type="hidden" name="usuario_id" id="editarUsuarioId">
          <div class="modal-header">
            <h5 class="modal-title">Editar usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" id="editarUsuarioNome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" id="editarUsuarioEmail" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Senha (opcional)</label>
              <input type="password" name="senha" class="form-control" placeholder="Deixe em branco para manter">
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="ativo" id="editarUsuarioAtivo">
              <label class="form-check-label" for="editarUsuarioAtivo">Usuario ativo</label>
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

  <div class="modal fade" id="modalUsuarioExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="usuario_delete">
          <input type="hidden" name="usuario_id" id="excluirUsuarioId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao do usuario selecionado?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Excluir</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php if ($canManagePerfis): ?>
  <?php if ($canManagePermissoes): ?>
  <!-- Modais Perfil -->
  <div class="modal fade" id="modalPerfilNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="perfil_create">
          <div class="modal-header">
            <h5 class="modal-title">Novo perfil</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Descricao</label>
              <input type="text" name="descricao" class="form-control">
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

  <div class="modal fade" id="modalPerfilEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formPerfilEditar">
          <input type="hidden" name="action" value="perfil_update">
          <input type="hidden" name="perfil_id" id="editarPerfilId">
          <div class="modal-header">
            <h5 class="modal-title">Editar perfil</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" id="editarPerfilNome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Descricao</label>
              <input type="text" name="descricao" id="editarPerfilDescricao" class="form-control">
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

  <div class="modal fade" id="modalPerfilExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="perfil_delete">
          <input type="hidden" name="perfil_id" id="excluirPerfilId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir perfil</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao do perfil selecionado?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Excluir</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modais Permissao -->
  <div class="modal fade" id="modalPermissaoNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="permissao_create">
          <div class="modal-header">
            <h5 class="modal-title">Nova permissao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Descricao</label>
              <input type="text" name="descricao" class="form-control">
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

  <div class="modal fade" id="modalPermissaoEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formPermissaoEditar">
          <input type="hidden" name="action" value="permissao_update">
          <input type="hidden" name="permissao_id" id="editarPermissaoId">
          <div class="modal-header">
            <h5 class="modal-title">Editar permissao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" id="editarPermissaoNome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Descricao</label>
              <input type="text" name="descricao" id="editarPermissaoDescricao" class="form-control">
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

  <div class="modal fade" id="modalPermissaoExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="permissao_delete">
          <input type="hidden" name="permissao_id" id="excluirPermissaoId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir permissao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao da permissao selecionada?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Excluir</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php endif; ?>
  <!-- Modais Usuario-Perfil -->
  <div class="modal fade" id="modalUsuarioPerfilNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="usuario_perfil_create">
          <div class="modal-header">
            <h5 class="modal-title">Novo vinculo usuario/perfil</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Usuario</label>
              <select name="usuario_id" id="novoUsuarioPerfilUsuario" class="form-select" required>
                <?php foreach ($usuarios as $usuario): ?>
                  <?php
                    if (is_object($usuario)) {
                      $usuario = (array)$usuario;
                    }
                    if (!is_array($usuario)) {
                      continue;
                    }
                    $uId = $usuario['id'] ?? '';
                    $uNome = $usuario['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$uId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$uId === (int)$usuarioFiltro) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$uNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php $semPerfis = empty($perfis); ?>
            <div class="mb-3">
              <label class="form-label">Perfil</label>
              <select name="perfil_id" class="form-select" required>
                <?php if ($semPerfis): ?>
                  <option value="">Nenhum perfil disponivel</option>
                <?php else: ?>
                  <?php foreach ($perfis as $perfil): ?>
                    <?php
                      if (is_object($perfil)) {
                        $perfil = (array)$perfil;
                      }
                      if (!is_array($perfil)) {
                        continue;
                      }
                      $pId = $perfil['id'] ?? '';
                      $pNome = $perfil['nome'] ?? '';
                    ?>
                    <option value="<?php echo htmlspecialchars((string)$pId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$pId === (int)$perfilFiltro) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars((string)$pNome, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
              <?php if ($semPerfis): ?>
                <small class="text-muted">Nenhum perfil disponivel para vincular.</small>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" <?php echo $semPerfis ? 'disabled' : ''; ?>>Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalUsuarioPerfilEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formUsuarioPerfilEditar">
          <input type="hidden" name="action" value="usuario_perfil_update">
          <input type="hidden" name="usuario_perfil_id" id="editarUsuarioPerfilId">
          <div class="modal-header">
            <h5 class="modal-title">Editar vinculo usuario/perfil</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Usuario</label>
              <select name="usuario_id" id="editarUsuarioPerfilUsuario" class="form-select" required>
                <?php foreach ($usuarios as $usuario): ?>
                  <?php
                    if (is_object($usuario)) {
                      $usuario = (array)$usuario;
                    }
                    if (!is_array($usuario)) {
                      continue;
                    }
                    $uId = $usuario['id'] ?? '';
                    $uNome = $usuario['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$uId, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)$uNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php $semPerfisEditar = empty($perfis); ?>
            <div class="mb-3">
              <label class="form-label">Perfil</label>
              <select name="perfil_id" id="editarUsuarioPerfilPerfil" class="form-select" required>
                <?php if ($semPerfisEditar): ?>
                  <option value="">Nenhum perfil disponivel</option>
                <?php else: ?>
                  <?php foreach ($perfis as $perfil): ?>
                    <?php
                      if (is_object($perfil)) {
                        $perfil = (array)$perfil;
                      }
                      if (!is_array($perfil)) {
                        continue;
                      }
                      $pId = $perfil['id'] ?? '';
                      $pNome = $perfil['nome'] ?? '';
                    ?>
                    <option value="<?php echo htmlspecialchars((string)$pId, ENT_QUOTES, 'UTF-8'); ?>">
                      <?php echo htmlspecialchars((string)$pNome, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
              <?php if ($semPerfisEditar): ?>
                <small class="text-muted">Nenhum perfil disponivel para vincular.</small>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" <?php echo $semPerfisEditar ? 'disabled' : ''; ?>>Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalUsuarioPerfilExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="usuario_perfil_delete">
          <input type="hidden" name="usuario_perfil_id" id="excluirUsuarioPerfilId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir vinculo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao do vinculo selecionado?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Excluir</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modais Perfil-Permissao -->
  <div class="modal fade" id="modalPerfilPermissaoNovo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="perfil_permissao_create">
          <div class="modal-header">
            <h5 class="modal-title">Novo vinculo perfil/permissao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Perfil</label>
              <select name="perfil_id" class="form-select" required>
                <?php foreach ($perfis as $perfil): ?>
                  <?php
                    if (is_object($perfil)) {
                      $perfil = (array)$perfil;
                    }
                    if (!is_array($perfil)) {
                      continue;
                    }
                    $pId = $perfil['id'] ?? '';
                    $pNome = $perfil['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$pId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((int)$pId === (int)$perfilFiltro) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars((string)$pNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Permissao</label>
              <select name="permissao_id" class="form-select" required>
                <?php foreach ($permissoes as $permissao): ?>
                  <?php
                    if (is_object($permissao)) {
                      $permissao = (array)$permissao;
                    }
                    if (!is_array($permissao)) {
                      continue;
                    }
                    $permId = $permissao['id'] ?? '';
                    $permNome = $permissao['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$permId, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)$permNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
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

  <div class="modal fade" id="modalPerfilPermissaoEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="" id="formPerfilPermissaoEditar">
          <input type="hidden" name="action" value="perfil_permissao_update">
          <input type="hidden" name="perfil_permissao_id" id="editarPerfilPermissaoId">
          <div class="modal-header">
            <h5 class="modal-title">Editar vinculo perfil/permissao</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Perfil</label>
              <select name="perfil_id" id="editarPerfilPermissaoPerfil" class="form-select" required>
                <?php foreach ($perfis as $perfil): ?>
                  <?php
                    if (is_object($perfil)) {
                      $perfil = (array)$perfil;
                    }
                    if (!is_array($perfil)) {
                      continue;
                    }
                    $pId = $perfil['id'] ?? '';
                    $pNome = $perfil['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$pId, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)$pNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Permissao</label>
              <select name="permissao_id" id="editarPerfilPermissaoPermissao" class="form-select" required>
                <?php foreach ($permissoes as $permissao): ?>
                  <?php
                    if (is_object($permissao)) {
                      $permissao = (array)$permissao;
                    }
                    if (!is_array($permissao)) {
                      continue;
                    }
                    $permId = $permissao['id'] ?? '';
                    $permNome = $permissao['nome'] ?? '';
                  ?>
                  <option value="<?php echo htmlspecialchars((string)$permId, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars((string)$permNome, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
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

  <div class="modal fade" id="modalPerfilPermissaoExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="">
          <input type="hidden" name="action" value="perfil_permissao_delete">
          <input type="hidden" name="perfil_permissao_id" id="excluirPerfilPermissaoId">
          <div class="modal-header">
            <h5 class="modal-title">Excluir vinculo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Confirma a exclusao do vinculo selecionado?</p>
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
    document.querySelectorAll('.btn-edit-usuario').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarUsuarioId').value = this.dataset.id || '';
        document.getElementById('editarUsuarioNome').value = this.dataset.nome || '';
        document.getElementById('editarUsuarioEmail').value = this.dataset.email || '';
        document.getElementById('editarUsuarioAtivo').checked = this.dataset.ativo === '1';
      });
    });

    document.querySelectorAll('.btn-delete-usuario').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirUsuarioId').value = this.dataset.id || '';
      });
    });

    document.querySelectorAll('.btn-manage-permissoes').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const usuarioId = this.dataset.usuario || '';
        const selectNovo = document.getElementById('novoUsuarioPerfilUsuario');
        if (selectNovo && usuarioId !== '') {
          selectNovo.value = usuarioId;
        }
        const filtro = document.getElementById('filtroUsuarioPerfil');
        if (filtro && usuarioId !== '') {
          filtro.value = usuarioId;
        }
        const section = document.getElementById('usuario-perfis-section');
        if (section) {
          section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });

    document.querySelectorAll('.btn-edit-perfil').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarPerfilId').value = this.dataset.id || '';
        document.getElementById('editarPerfilNome').value = this.dataset.nome || '';
        document.getElementById('editarPerfilDescricao').value = this.dataset.descricao || '';
      });
    });

    document.querySelectorAll('.btn-delete-perfil').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirPerfilId').value = this.dataset.id || '';
      });
    });

    document.querySelectorAll('.btn-edit-permissao').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarPermissaoId').value = this.dataset.id || '';
        document.getElementById('editarPermissaoNome').value = this.dataset.nome || '';
        document.getElementById('editarPermissaoDescricao').value = this.dataset.descricao || '';
      });
    });

    document.querySelectorAll('.btn-delete-permissao').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirPermissaoId').value = this.dataset.id || '';
      });
    });

    document.querySelectorAll('.btn-edit-usuario-perfil').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarUsuarioPerfilId').value = this.dataset.id || '';
        document.getElementById('editarUsuarioPerfilUsuario').value = this.dataset.usuario || '';
        document.getElementById('editarUsuarioPerfilPerfil').value = this.dataset.perfil || '';
      });
    });

    document.querySelectorAll('.btn-delete-usuario-perfil').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirUsuarioPerfilId').value = this.dataset.id || '';
      });
    });

    document.querySelectorAll('.btn-edit-perfil-permissao').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('editarPerfilPermissaoId').value = this.dataset.id || '';
        document.getElementById('editarPerfilPermissaoPerfil').value = this.dataset.perfil || '';
        document.getElementById('editarPerfilPermissaoPermissao').value = this.dataset.permissao || '';
      });
    });

    document.querySelectorAll('.btn-delete-perfil-permissao').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('excluirPerfilPermissaoId').value = this.dataset.id || '';
      });
    });
  </script>
  <?php endif; ?>
</div>
