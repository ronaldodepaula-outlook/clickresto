<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../classe/env.php';
loadEnvFile(__DIR__ . '/../../.env');

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$token = $_SESSION['token'] ?? '';
$errorMessage = '';
$empresas = [];

function apiRequest($method, $url, $token, &$httpCode = null) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Authorization: Bearer ' . $token,
  ]);
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

if ($apiBase !== '' && $token !== '') {
  $code = null;
  $resp = apiRequest('GET', $apiBase . '/empresas/admin-list', $token, $code);
  if ($code >= 200 && $code < 300) {
    $empresas = $resp['data'] ?? $resp;
    if (!is_array($empresas)) {
      $empresas = [];
    }
  } else {
    $errorMessage = $resp['message'] ?? 'Nao foi possivel carregar as empresas.';
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
                    <h3 class="mb-1">Gestao de empresas</h3>
                    <p class="text-muted mb-0">Controle rapido de empresas com foco em status e planos.</p>
                  </div>
                  <div class="btn-wrapper">
                    <a href="index.php?paginas=empresa_form" class="btn btn-primary text-white me-2"><i class="mdi mdi-plus"></i> Criar empresa</a>
                    <a href="index.php?paginas=relatorios" class="btn btn-outline-secondary"><i class="mdi mdi-chart-areaspline"></i> Ver relatorios</a>
                  </div>
                </div>
              </div>
            </div>

            <?php if ($errorMessage !== ''): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <div class="d-sm-flex justify-content-between align-items-start mb-3">
                      <div>
                        <h4 class="card-title card-title-dash">Empresas cadastradas</h4>
                        <p class="card-subtitle card-subtitle-dash">Endpoint: /api/v1/empresas/admin-list</p>
                      </div>
                    </div>

                    <div class="row g-2 mb-3">
                      <div class="col-sm-6 col-lg-3">
                        <input type="text" class="form-control" placeholder="Nome da empresa">
                      </div>
                      <div class="col-sm-6 col-lg-2">
                        <input type="text" class="form-control" placeholder="CNPJ">
                      </div>
                      <div class="col-sm-6 col-lg-2">
                        <select class="form-select">
                          <option>Status</option>
                          <option>ativo</option>
                          <option>suspenso</option>
                          <option>trial</option>
                          <option>cancelado</option>
                        </select>
                      </div>
                      <div class="col-sm-6 col-lg-2">
                        <select class="form-select">
                          <option>Plano</option>
                          <option>Starter</option>
                          <option>Growth</option>
                          <option>Pro</option>
                          <option>Enterprise</option>
                        </select>
                      </div>
                      <div class="col-sm-6 col-lg-2">
                        <input type="text" class="form-control" placeholder="Cidade">
                      </div>
                      <div class="col-sm-6 col-lg-1">
                        <button class="btn btn-outline-secondary w-100"><i class="mdi mdi-filter"></i></button>
                      </div>
                    </div>

                    <div class="table-responsive">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Empresa</th>
                            <th>Plano</th>
                            <th>Usuarios</th>
                            <th>Status</th>
                            <th>Expiracao</th>
                            <th>Acoes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($empresas)): ?>
                            <tr><td colspan="6">Nenhuma empresa encontrada.</td></tr>
                          <?php else: ?>
                            <?php foreach ($empresas as $empresa): ?>
                              <?php
                                $id = $empresa['id'] ?? $empresa['empresa_id'] ?? '';
                                $nome = $empresa['nome'] ?? '';
                                $fantasia = $empresa['nome_fantasia'] ?? '';
                                $cnpj = $empresa['cnpj'] ?? '';
                                $email = $empresa['email'] ?? '';
                                $telefone = $empresa['telefone'] ?? '';
                                $cidade = $empresa['cidade'] ?? '';
                                $estado = $empresa['estado'] ?? '';
                                $status = $empresa['status'] ?? '';
                                $plano = $empresa['plano'] ?? $empresa['plano_nome'] ?? '';
                                $usuarios = $empresa['usuarios_ativos'] ?? $empresa['usuarios'] ?? '-';
                                $expiracao = $empresa['data_fim'] ?? $empresa['expiracao'] ?? '-';
                                if (is_array($plano)) {
                                  $plano = $plano['nome'] ?? '';
                                }
                              ?>
                              <tr>
                                <td>
                                  <h6><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></h6>
                                  <p class="mb-0 text-muted"><?php echo htmlspecialchars($cnpj, ENT_QUOTES, 'UTF-8'); ?></p>
                                </td>
                                <td><?php echo htmlspecialchars($plano, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string)$usuarios, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                  <?php if ($status === 'ativo'): ?>
                                    <div class="badge badge-opacity-success">Ativo</div>
                                  <?php elseif ($status === 'suspenso'): ?>
                                    <div class="badge badge-opacity-danger">Suspenso</div>
                                  <?php elseif ($status === 'trial'): ?>
                                    <div class="badge badge-opacity-warning">Trial</div>
                                  <?php else: ?>
                                    <div class="badge badge-opacity-secondary"><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></div>
                                  <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars((string)$expiracao, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                  <button class="btn btn-outline-primary btn-sm me-1" title="Visualizar"><i class="mdi mdi-eye"></i></button>
                                  <button class="btn btn-outline-secondary btn-sm me-1" title="Editar"><i class="mdi mdi-pencil"></i></button>
                                  <button class="btn btn-outline-danger btn-sm me-1" title="Suspender"><i class="mdi mdi-lock"></i></button>
                                  <button class="btn btn-outline-info btn-sm me-1" title="Alterar plano"><i class="mdi mdi-credit-card-outline"></i></button>
                                  <button class="btn btn-outline-secondary btn-sm me-1" title="Ver assinatura"><i class="mdi mdi-file-document-outline"></i></button>
                                  <button class="btn btn-outline-secondary btn-sm me-1" title="Gerenciar usuarios"><i class="mdi mdi-account-multiple-outline"></i></button>
                                  <button class="btn btn-outline-dark btn-sm" title="Acessar como empresa"><i class="mdi mdi-login"></i></button>
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
        </div>

